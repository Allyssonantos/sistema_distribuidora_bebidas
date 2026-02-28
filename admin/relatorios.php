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
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin - Relatórios de Vendas</title>
  <style>
    body{font-family:Arial, sans-serif; margin:20px; background-color: #f4f7f6;}
    .toolbar {
      display: flex; justify-content: space-between; align-items: center;
      background: #fff; padding: 15px; border-radius: 8px; margin-bottom: 20px;
      border: 1px solid #dee2e6; box-shadow: 0 2px 4px rgba(0,0,0,0.05);
      flex-wrap: wrap; gap: 15px;
    }
    .search-group { display: flex; align-items: center; gap: 10px; }
    .btn-group { display: flex; gap: 8px; flex-wrap: wrap; }
    input { padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
    .btn-secondary { background: #f8f9fa; border: 1px solid #ccc; padding: 10px 14px; border-radius: 4px; text-decoration: none; color: #333; font-size: 14px; display: inline-flex; align-items: center; cursor: pointer; }
    .btn-secondary:hover { background: #e2e6ea; }
    .cards { display: grid; grid-template-columns: repeat(4, 1fr); gap: 15px; margin-bottom: 20px; }
    .card { background: #fff; padding: 15px; border-radius: 8px; border: 1px solid #dee2e6; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
    .card b { color: #666; font-size: 14px; display: block; margin-bottom: 5px; }
    .card .val { font-size: 22px; font-weight: bold; color: #333; }
    table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
    th, td { padding: 12px; border-bottom: 1px solid #eee; text-align: left; }
    th { background: #f8f9fa; color: #333; }
    .right { text-align: right; }
    @media(max-width: 900px){ .cards { grid-template-columns: repeat(2, 1fr); } }
  </style>
</head>
<body>

<h2>📊 Relatórios de Vendas</h2>

<div class="toolbar">
  <div class="search-group">
    <input type="date" id="de"> 
    <span>até</span>
    <input type="date" id="ate">
    <button onclick="carregar()" class="btn-secondary">🔎 Filtrar</button>
  </div>
  <div class="btn-group">
    <a href="produtos.php" class="btn-secondary">📦 Produtos</a>
    <a href="estoque.php" class="btn-secondary">📥 Estoque</a>
    <a href="lucro.php" class="btn-secondary">📈 Lucro Real</a>
    <a href="fechamento_caixa.php" class="btn-secondary">🧾 Caixas</a>
  </div>
</div>

<div class="cards">
  <div class="card"><b>Total Vendido</b><span class="val" id="total">R$ 0,00</span></div>
  <div class="card"><b>PIX</b><span class="val" id="pix">R$ 0,00</span></div>
  <div class="card"><b>Dinheiro</b><span class="val" id="dinheiro">R$ 0,00</span></div>
  <div class="card"><b>Cartões</b><span class="val" id="cartoes">R$ 0,00</span></div>
</div>

<table>
  <thead>
    <tr>
      <th>ID</th>
      <th>Data/Hora</th>
      <th>Pagamento</th>
      <th class="right">Total</th>
      <th class="right">Ações</th>
    </tr>
  </thead>
  <tbody id="lista"></tbody>
</table>

<script>
  function brl(v){ return "R$ " + Number(v||0).toFixed(2).replace(".", ","); }
  
  async function carregar(){
    const de = document.getElementById("de").value;
    const ate = document.getElementById("ate").value;
    const res = await fetch(`../api/vendas_listar.php?de=${de}&ate=${ate}`);
    const vendas = await res.json();

    let tTotal=0, tPix=0, tDin=0, tCar=0;
    const tbody = document.getElementById("lista");
    tbody.innerHTML = "";

    vendas.forEach(v => {
      const val = Number(v.total||0);
      tTotal += val;
      if(v.forma_pagamento === "PIX") tPix += val;
      else if(v.forma_pagamento === "DINHEIRO") tDin += val;
      else if(v.forma_pagamento.includes("CARTAO")) tCar += val;

      const tr = document.createElement("tr");
      tr.innerHTML = `
        <td>#${v.id}</td>
        <td>${new Date(v.data_venda).toLocaleString("pt-BR")}</td>
        <td>${v.forma_pagamento}</td>
        <td class="right">${brl(v.total)}</td>
        <td class="right">
          <button onclick="window.open('../pdv/imprimir.php?id=${v.id}')" class="btn-secondary" style="padding:5px 10px; font-size:12px;">Reimprimir</button>
        </td>
      `;
      tbody.appendChild(tr);
    });

    document.getElementById("total").textContent = brl(tTotal);
    document.getElementById("pix").textContent = brl(tPix);
    document.getElementById("dinheiro").textContent = brl(tDin);
    document.getElementById("cartoes").textContent = brl(tCar);
  }

  const hoje = new Date().toISOString().split('T')[0];
  document.getElementById("de").value = hoje;
  document.getElementById("ate").value = hoje;
  carregar();
</script>

</body>
</html>