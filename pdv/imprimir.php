<?php
require_once __DIR__ . "/../config/db.php";

$id = (int)($_GET["id"] ?? 0);
if ($id <= 0) {
  die("Venda inválida.");
}

// Busca venda
$stmtVenda = $pdo->prepare("
  SELECT id, data_venda, forma_pagamento, subtotal, desconto, total, valor_recebido, troco, status
  FROM vendas
  WHERE id = ?
  LIMIT 1
");
$stmtVenda->execute([$id]);
$venda = $stmtVenda->fetch();

if (!$venda) {
  die("Venda não encontrada.");
}

// Busca itens
$stmtItens = $pdo->prepare("
  SELECT vi.quantidade, vi.valor_unit, vi.subtotal, p.nome
  FROM venda_itens vi
  JOIN produtos p ON p.id = vi.produto_id
  WHERE vi.venda_id = ?
  ORDER BY vi.id ASC
");
$stmtItens->execute([$id]);
$itens = $stmtItens->fetchAll();

function brl($v){
  return number_format((float)$v, 2, ",", ".");
}

function pagamentoLabel($p){
  return match($p){
    "DINHEIRO" => "Dinheiro",
    "PIX" => "PIX",
    "CARTAO_DEBITO" => "Cartão Débito",
    "CARTAO_CREDITO" => "Cartão Crédito",
    default => "Outros"
  };
}
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Imprimir Cupom</title>
  <style>
    /* Cupom 80mm */
    @page { margin: 6mm; }
    body { font-family: "Courier New", monospace; background:#fff; margin:0; }
    .cupom { width: 80mm; margin: 0 auto; font-size: 12px; }
    .center { text-align:center; }
    .line { border-top: 1px dashed #000; margin: 8px 0; }
    table { width:100%; border-collapse: collapse; }
    td { vertical-align: top; padding: 2px 0; }
    .right { text-align:right; }
    .small { font-size: 11px; }
    .btns { margin: 12px auto; width:80mm; display:flex; gap:8px; }
    button { padding:10px; width:100%; cursor:pointer; }
    @media print {
      .btns { display:none; }
    }
  </style>
</head>
<body>

<div class="cupom">
  <div class="center">
    <b>ADORA BEBIDAS</b><br>
    <span class="small">CNPJ: 00.000.000/0000-00</span><br>
    <span class="small">Endereço: sua rua, nº 0</span><br>
  </div>

  <div class="line"></div>

  <div class="small">
    <b>Venda:</b> #<?= (int)$venda["id"] ?><br>
    <b>Data:</b> <?= date("d/m/Y H:i", strtotime($venda["data_venda"])) ?><br>
    <b>Pagamento:</b> <?= htmlspecialchars(pagamentoLabel($venda["forma_pagamento"])) ?><br>
  </div>

  <div class="line"></div>

  <table>
    <?php foreach($itens as $i): ?>
      <tr>
        <td colspan="2"><?= htmlspecialchars($i["nome"]) ?></td>
      </tr>
      <tr>
        <td class="small">
          <?= rtrim(rtrim(number_format((float)$i["quantidade"], 3, ",", "."), "0"), ",") ?>
          x <?= brl($i["valor_unit"]) ?>
        </td>
        <td class="right"><?= brl($i["subtotal"]) ?></td>
      </tr>
    <?php endforeach; ?>
  </table>

  <div class="line"></div>

  <table>
    <tr><td>Subtotal</td><td class="right"><?= brl($venda["subtotal"]) ?></td></tr>
    <tr><td>Desconto</td><td class="right"><?= brl($venda["desconto"]) ?></td></tr>
    <tr><td><b>TOTAL</b></td><td class="right"><b><?= brl($venda["total"]) ?></b></td></tr>

    <?php if ($venda["forma_pagamento"] === "DINHEIRO"): ?>
      <tr><td>Recebido</td><td class="right"><?= brl($venda["valor_recebido"]) ?></td></tr>
      <tr><td>Troco</td><td class="right"><?= brl($venda["troco"]) ?></td></tr>
    <?php endif; ?>
  </table>

  <div class="line"></div>

  <div class="center small">
    Obrigado pela preferência!<br>
    Volte sempre 😊
  </div>
</div>

<div class="btns">
  <button onclick="window.print()">🖨️ Imprimir</button>
  <button onclick="voltar()">↩️ Voltar</button>
</div>

<script>
  // imprime automático ao abrir
  window.onload = () => {
    setTimeout(() => window.print(), 300);
  };

  function voltar(){
    // volta pro caixa
    window.location.href = "caixa.php";
  }
</script>

</body>
</html>