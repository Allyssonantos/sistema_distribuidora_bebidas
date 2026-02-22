<?php
header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . "/../config/db.php";

$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

if (!$data) {
  http_response_code(400);
  echo json_encode(["ok" => false, "erro" => "JSON inválido"]);
  exit;
}

$forma = $data["forma_pagamento"] ?? "";
$itens = $data["itens"] ?? [];
$recebido = (float)($data["valor_recebido"] ?? 0);
$troco = (float)($data["troco"] ?? 0);

$formasValidas = ["DINHEIRO","PIX","CARTAO_DEBITO","CARTAO_CREDITO","OUTROS"];
if (!in_array($forma, $formasValidas)) {
  http_response_code(400);
  echo json_encode(["ok" => false, "erro" => "Forma de pagamento inválida"]);
  exit;
}
if (!is_array($itens) || count($itens) === 0) {
  http_response_code(400);
  echo json_encode(["ok" => false, "erro" => "Carrinho vazio"]);
  exit;
}

try {
  $pdo->beginTransaction();

  // (Opcional por enquanto) usuário/caixa
  $usuarioId = null;
  $caixaId = null;

  // Recalcula total no servidor (segurança)
  $subtotal = 0;

  // Validar estoque e pegar preço do banco
  $stmtProd = $pdo->prepare("SELECT id, nome, preco_venda, estoque_atual, ativo FROM produtos WHERE id = ? FOR UPDATE");

  $produtos = [];
  foreach ($itens as $i) {
    $pid = (int)($i["id"] ?? 0);
    $qtd = (float)($i["qtd"] ?? 0);

    if ($pid <= 0 || $qtd <= 0) {
      throw new Exception("Item inválido no carrinho");
    }

    $stmtProd->execute([$pid]);
    $p = $stmtProd->fetch();

    if (!$p || (int)$p["ativo"] !== 1) {
      throw new Exception("Produto não encontrado/ativo (ID $pid)");
    }

    if ((float)$p["estoque_atual"] < $qtd) {
      throw new Exception("Estoque insuficiente: {$p['nome']} (Est: {$p['estoque_atual']})");
    }

    $valorUnit = (float)$p["preco_venda"];
    $sub = $valorUnit * $qtd;
    $subtotal += $sub;

    $produtos[] = [
      "id" => $pid,
      "nome" => $p["nome"],
      "qtd" => $qtd,
      "valor_unit" => $valorUnit,
      "subtotal" => $sub
    ];
  }

  $desconto = 0.00;
  $total = $subtotal - $desconto;

  if ($forma === "DINHEIRO" && $recebido < $total) {
    throw new Exception("Valor recebido menor que o total");
  }
  
  // =============================
// BUSCAR SESSÃO DE CAIXA ABERTA
// =============================
  $caixaId = (int)($data["caixa_id"] ?? 1); // depois vamos pegar do PDV

  $st = $pdo->prepare("
      SELECT id FROM caixa_sessoes
      WHERE caixa_id = :c AND status = 'ABERTO'
      ORDER BY id DESC LIMIT 1
  ");
  $st->execute(["c"=>$caixaId]);

  $sessaoId = (int)$st->fetchColumn();

  if($sessaoId <= 0){
      throw new Exception("Caixa está FECHADO. Abra o caixa antes de vender.");
  }


  // Salva venda
  $stmtVenda = $pdo->prepare("
    INSERT INTO vendas (
    caixa_id,
    caixa_sessao_id,
    usuario_id,
    forma_pagamento,
    subtotal,
    desconto,
    total,
    valor_recebido,
    troco
)
VALUES (
    :caixa_id,
    :caixa_sessao_id,
    :usuario_id,
    :forma,
    :subtotal,
    :desconto,
    :total,
    :recebido,
    :troco
)
  ");
  $stmtVenda->execute([
    ":caixa_id" => $caixaId,
    ":caixa_sessao_id" => $sessaoId,
    ":usuario_id" => $usuarioId,
    ":forma" => $forma,
    ":subtotal" => $subtotal,
    ":desconto" => $desconto,
    ":total" => $total,
    ":recebido" => ($forma === "DINHEIRO" ? $recebido : $total),
    ":troco" => ($forma === "DINHEIRO" ? $troco : 0),
  ]);

  $vendaId = (int)$pdo->lastInsertId();

  // Itens + baixa de estoque + mov_estoque
  $stmtItem = $pdo->prepare("
    INSERT INTO venda_itens (venda_id, produto_id, quantidade, valor_unit, subtotal)
    VALUES (:venda_id, :produto_id, :qtd, :valor_unit, :subtotal)
  ");

  $stmtBaixa = $pdo->prepare("
    UPDATE produtos
    SET estoque_atual = estoque_atual - :qtd
    WHERE id = :produto_id
  ");

  $stmtMov = $pdo->prepare("
    INSERT INTO mov_estoque (produto_id, tipo, quantidade, valor_unit, origem, observacao, usuario_id)
    VALUES (:produto_id, 'SAIDA', :qtd, :valor_unit, 'VENDA', :obs, :usuario_id)
  ");

  foreach ($produtos as $p) {
    $stmtItem->execute([
      ":venda_id" => $vendaId,
      ":produto_id" => $p["id"],
      ":qtd" => $p["qtd"],
      ":valor_unit" => $p["valor_unit"],
      ":subtotal" => $p["subtotal"],
    ]);

    $stmtBaixa->execute([
      ":qtd" => $p["qtd"],
      ":produto_id" => $p["id"],
    ]);

    $stmtMov->execute([
      ":produto_id" => $p["id"],
      ":qtd" => $p["qtd"],
      ":valor_unit" => $p["valor_unit"],
      ":obs" => "Venda #".$vendaId,
      ":usuario_id" => $usuarioId,
    ]);
  }

  $pdo->commit();

  echo json_encode(["ok" => true, "venda_id" => $vendaId]);

} catch (Exception $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  http_response_code(400);
  echo json_encode(["ok" => false, "erro" => $e->getMessage()]);
}