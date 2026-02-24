<?php
require_once __DIR__ . "/../config/db.php";

$sessaoId = (int)($_GET["sessao_id"] ?? 0);
if($sessaoId <= 0){ die("Sessão inválida"); }

$st = $pdo->prepare("SELECT * FROM caixa_sessoes WHERE id=?");
$st->execute([$sessaoId]);
$s = $st->fetch(PDO::FETCH_ASSOC);
if(!$s) die("Sessão não encontrada");

function brl($v){
  return "R$ " . number_format((float)$v, 2, ",", ".");
}
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <title>Resumo do Caixa</title>
  <style>
    body{font-family:Arial; margin:20px;}
    h2{margin:0 0 8px 0;}
    .row{display:flex; justify-content:space-between; margin:6px 0;}
    .box{border:1px solid #ddd; padding:12px; border-radius:10px; max-width:420px;}
    .muted{color:#666; font-size:12px;}
    .big{font-size:18px; font-weight:bold;}
    hr{border:0; border-top:1px solid #ddd; margin:10px 0;}
  </style>
</head>
<body onload="window.print()">

  <div class="box">
    <h2>🧾 Resumo de Fechamento</h2>
    <div class="muted">Sessão #<?= (int)$s["id"] ?> — Caixa físico #<?= (int)$s["caixa_id"] ?></div>
    <div class="muted">Abertura: <?= htmlspecialchars($s["aberto_em"]) ?></div>
    <div class="muted">Fechamento: <?= htmlspecialchars($s["fechado_em"] ?? "-") ?></div>

    <hr>

    <div class="row"><span>Troco inicial</span><span class="big"><?= brl($s["troco_inicial"]) ?></span></div>

    <hr>

    <div class="row"><span>Dinheiro</span><span><?= brl($s["total_dinheiro"]) ?></span></div>
    <div class="row"><span>PIX</span><span><?= brl($s["total_pix"]) ?></span></div>
    <div class="row"><span>Cartão Débito</span><span><?= brl($s["total_cartao_debito"]) ?></span></div>
    <div class="row"><span>Cartão Crédito</span><span><?= brl($s["total_cartao_credito"]) ?></span></div>
    <div class="row"><span>Outros</span><span><?= brl($s["total_outros"]) ?></span></div>

    <hr>

    <div class="row"><span>Total geral</span><span class="big"><?= brl($s["total_geral"]) ?></span></div>
    <div class="muted">Vendas: <?= (int)$s["qtd_vendas"] ?></div>

    <?php if(!empty($s["obs"])): ?>
      <hr>
      <div class="muted">Obs: <?= htmlspecialchars($s["obs"]) ?></div>
    <?php endif; ?>
  </div>

</body>
</html>