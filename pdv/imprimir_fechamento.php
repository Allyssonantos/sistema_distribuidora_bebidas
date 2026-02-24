<?php
require_once __DIR__ . "/../config/db.php";

$sessaoId = (int)($_GET["sessao_id"] ?? 0);
if($sessaoId <= 0) die("Sessão inválida");

$st = $pdo->prepare("SELECT * FROM caixa_sessoes WHERE id=?");
$st->execute([$sessaoId]);
$s = $st->fetch();

if(!$s) die("Sessão não encontrada");

// Busca soma de itens
$stI = $pdo->prepare("
  SELECT p.nome, SUM(vi.quantidade) as qtd
  FROM venda_itens vi
  JOIN produtos p ON p.id = vi.produto_id
  JOIN vendas v ON v.id = vi.venda_id
  WHERE v.caixa_sessao_id = ? AND v.status='FINALIZADA'
  GROUP BY p.id, p.nome
  ORDER BY p.nome ASC
");
$stI->execute([$sessaoId]);
$itens = $stI->fetchAll();

function brl($v){ return "R$ " . number_format((float)$v, 2, ",", "."); }
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <title>Fechamento #<?= $sessaoId ?></title>
  <style>
    @page { margin: 5mm; }
    body { font-family: monospace; width: 80mm; font-size: 12px; }
    .line { border-top: 1px dashed #000; margin: 8px 0; }
    .row { display: flex; justify-content: space-between; }
    .center { text-align: center; }
  </style>
</head>
<body onload="window.print(); setTimeout(() => { window.location.href = 'about:blank'; }, 500);">
  <div class="center">
    <b>RESUMO DE FECHAMENTO</b><br>
    Sessão #<?= $s["id"] ?>
  </div>
  <div class="line"></div>
  <div class="row"><span>Abertura:</span> <span><?= $s["aberto_em"] ?></span></div>
  <div class="row"><span>Fechamento:</span> <span><?= $s["fechado_em"] ?></span></div>
  <div class="line"></div>
  <div class="row"><b>Troco Inicial:</b> <b><?= brl($s["troco_inicial"]) ?></b></div>
  <div class="row"><span>Vendas Total:</span> <span><?= brl($s["total_geral"]) ?></span></div>
  <div class="line"></div>
  <b>ITENS VENDIDOS:</b>
  <?php foreach($itens as $it): ?>
    <div class="row">
      <span><?= substr($it["nome"], 0, 20) ?></span>
      <span><?= number_format($it["qtd"], 2, ",", ".") ?></span>
    </div>
  <?php endforeach; ?>
  <div class="line"></div>
  <div class="row"><span>Dinheiro:</span> <span><?= brl($s["total_dinheiro"]) ?></span></div>
  <div class="row"><span>PIX:</span> <span><?= brl($s["total_pix"]) ?></span></div>
  <div class="row"><span>Cartões:</span> <span><?= brl($s["total_cartao_debito"] + $s["total_cartao_credito"]) ?></span></div>
  <div class="line"></div>
  <div class="center">Relatório conferido.</div>
</body>
</html>