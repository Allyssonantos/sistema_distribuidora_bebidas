<?php
session_start();
if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}
?>

<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <title>Admin - Lucro Real</title>
  <style>
    body{font-family:Arial, sans-serif; margin:20px; background-color: #f4f7f6;}
    .toolbar {
      display: flex; justify-content: space-between; align-items: center;
      background: #fff; padding: 15px; border-radius: 8px; margin-bottom: 20px;
      border: 1px solid #dee2e6; box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    .cards { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 20px; }
    .card { background: #fff; padding: 15px; border-radius: 8px; border: 1px solid #dee2e6; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
    .card b { color: #666; font-size: 14px; display: block; margin-bottom: 5px; }
    .card .val { font-size: 24px; font-weight: bold; display: block; }
    .card .muted { font-size: 12px; color: #999; margin-top: 5px; }
    .btn-secondary { background: #f8f9fa; border: 1px solid #ccc; padding: 10px 14px; border-radius: 4px; text-decoration: none; color: #333; font-size: 14px; cursor: pointer; }
    @media(max-width: 1000px){ .cards { grid-template-columns: repeat(2, 1fr); } }
  </style>
</head>
<body>

<h2>📈 Relatório de Lucratividade</h2>

<div class="toolbar">
  <div class="search-group">
    <input type="date" id="de"> até <input type="date" id="ate">
    <button onclick="carregar()" class="btn-secondary">🔎 Atualizar</button>
  </div>
  <div class="btn-group">
    <a href="produtos.php" class="btn-secondary">📦 Produtos</a>
    <a href="relatorios.php" class="btn-secondary">📊 Vendas</a>
  </div>
</div>

<div class="cards">
  <div class="card">
    <b>💰 Faturamento</b>
    <span class="val" id="fat">R$ 0,00</span>
    <div class="muted" id="qv">0 vendas</div>
  </div>
  <div class="card">
    <b>💸 Custo de Compras</b>
    <span class="val" id="comp" style="color: #d9534f;">R$ 0,00</span>
    <div class="muted" id="qc">Qtd: 0</div>
  </div>
  <div class="card">
    <b>💀 Perdas/Quebras</b>
    <span class="val" id="perd" style="color: #f0ad4e;">R$ 0,00</span>
    <div class="muted" id="qp">Qtd: 0</div>
  </div>
  <div class="card">
    <b>📈 Lucro Bruto</b>
    <span class="val" id="luc" style="color: #28a745;">R$ 0,00</span>
    <div class="muted" id="marg">Margem: 0%</div>
  </div>
</div>

<script>
  function brl(v){ return "R$ " + Number(v||0).toFixed(2).replace(".", ","); }

  async function carregar(){
    const de = document.getElementById("de").value;
    const ate = document.getElementById("ate").value;
    const res = await fetch(`../api/lucro_relatorio.php?de=${de}&ate=${ate}`);
    const json = await res.json();

    if(!json.ok) return alert(json.erro);

    document.getElementById("fat").textContent = brl(json.vendas.faturamento);
    document.getElementById("qv").textContent = `${json.vendas.qtd_vendas} vendas`;
    document.getElementById("comp").textContent = brl(json.compras.valor);
    document.getElementById("qc").textContent = `Qtd: ${json.compras.qtd}`;
    document.getElementById("perd").textContent = brl(json.perdas.valor);
    document.getElementById("qp").textContent = `Qtd: ${json.perdas.qtd}`;
    document.getElementById("luc").textContent = brl(json.lucro.bruto);
    document.getElementById("marg").textContent = `Margem: ${Number(json.lucro.margem_pct).toFixed(1)}%`;
  }

  const hoje = new Date().toISOString().split('T')[0];
  document.getElementById("de").value = hoje;
  document.getElementById("ate").value = hoje;
  carregar();
</script>

</body>
</html>