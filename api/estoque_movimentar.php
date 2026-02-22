<?php
header("Content-Type: application/json; charset=utf-8");
require_once __DIR__ . "/../config/db.php";

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) { http_response_code(400); echo json_encode(["ok"=>false,"erro"=>"JSON inválido"]); exit; }

$produto_id = (int)($data["produto_id"] ?? 0);
$tipo       = $data["tipo"] ?? ""; // ENTRADA | AJUSTE | PERDA
$modoAjuste = $data["modo_ajuste"] ?? "DELTA"; // DELTA | FINAL
$qtd        = (float)($data["quantidade"] ?? 0); // usado em ENTRADA/PERDA e AJUSTE DELTA
$estoqueFinal = (float)($data["estoque_final"] ?? 0); // usado em AJUSTE FINAL
$valorUnit  = $data["valor_unit"] ?? null; // opcional (geralmente na entrada)
$observacao = trim($data["observacao"] ?? "");

$tiposValidos = ["ENTRADA","AJUSTE","PERDA"];
if ($produto_id <= 0) { http_response_code(400); echo json_encode(["ok"=>false,"erro"=>"Produto inválido"]); exit; }
if (!in_array($tipo, $tiposValidos)) { http_response_code(400); echo json_encode(["ok"=>false,"erro"=>"Tipo inválido"]); exit; }

try {
  $pdo->beginTransaction();

  // trava o produto pra não dar conflito com venda ao mesmo tempo
  $stmtP = $pdo->prepare("SELECT id, nome, estoque_atual, ativo FROM produtos WHERE id = ? FOR UPDATE");
  $stmtP->execute([$produto_id]);
  $p = $stmtP->fetch();

  if (!$p) throw new Exception("Produto não encontrado");
  if ((int)$p["ativo"] !== 1) throw new Exception("Produto está inativo");

  $estoqueAtual = (float)$p["estoque_atual"];

  // calcula delta a aplicar
  $delta = 0.0;
  $origem = "";
  $movTipo = "";

  if ($tipo === "ENTRADA") {
    if ($qtd <= 0) throw new Exception("Quantidade deve ser maior que 0");
    $delta = +$qtd;
    $movTipo = "ENTRADA";
    $origem = "COMPRA";
  }

  if ($tipo === "PERDA") {
    if ($qtd <= 0) throw new Exception("Quantidade deve ser maior que 0");
    $delta = -$qtd;
    $movTipo = "AJUSTE";   // PERDA é um ajuste negativo (mais fácil pro relatório)
    $origem = "PERDA";
  }

  if ($tipo === "AJUSTE") {
    $movTipo = "AJUSTE";
    $origem = "INVENTARIO";

    if ($modoAjuste === "FINAL") {
      // define estoque final e calcula diferença
      $delta = $estoqueFinal - $estoqueAtual;
      if ($estoqueFinal < 0) throw new Exception("Estoque final não pode ser negativo");
    } else {
      // DELTA: + ou - manual
      if ($qtd == 0) throw new Exception("Quantidade do ajuste não pode ser 0");
      $delta = $qtd; // pode ser positivo ou negativo
    }
  }

  $novo = $estoqueAtual + $delta;
  if ($novo < 0) throw new Exception("Operação deixaria o estoque negativo");

  // atualiza estoque
  $stmtU = $pdo->prepare("UPDATE produtos SET estoque_atual = :novo WHERE id = :id");
  $stmtU->execute([":novo"=>$novo, ":id"=>$produto_id]);

  // registra movimentação
  $stmtM = $pdo->prepare("
  INSERT INTO mov_estoque (produto_id, tipo, quantidade, delta, valor_unit, origem, observacao, usuario_id)
  VALUES (:produto_id, :tipo, :quantidade, :delta, :valor_unit, :origem, :obs, :usuario_id)
  ");

  $qtdRegistro = abs($delta);

  $stmtM->execute([
    "produto_id" => $produto_id,
    "tipo" => $movTipo,
    "quantidade" => $qtdRegistro,
    "delta" => $delta,
    "valor_unit" => ($valorUnit === "" || $valorUnit === null) ? null : (float)$valorUnit,
    "origem" => $origem,
    "obs" => $observacao === "" ? null : $observacao,
    "usuario_id" => null
  ]);


  $pdo->commit();

  echo json_encode([
    "ok"=>true,
    "produto" => $p["nome"],
    "estoque_antes"=>$estoqueAtual,
    "delta"=>$delta,
    "estoque_depois"=>$novo
  ]);

} catch (Exception $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  http_response_code(400);
  echo json_encode(["ok"=>false,"erro"=>$e->getMessage()]);
}