<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin - Relatórios</title>
  <style>
    body{font-family:Arial; margin:20px;}
    .top{display:flex; gap:10px; flex-wrap:wrap; align-items:center;}
    input{padding:10px;}
    button{padding:10px 14px; cursor:pointer;}
    table{width:100%; border-collapse:collapse; margin-top:14px;}
    th,td{border-bottom:1px solid #ddd; padding:10px;}
    th{background:#fafafa; text-align:left;}
    .right{text-align:right;}
    .cards{display:grid; grid-template-columns:repeat(4,1fr); gap:10px; margin-top:14px;}
    .card{border:1px solid #eee; border-radius:10px; padding:12px;}
    @media(max-width:900px){ .cards{grid-template-columns:repeat(2,1fr);} }
  </style>
</head>
<body>

<h2>📊 Admin - Relatórios de Vendas</h2>

<div class="top">
  <label>De: <input type="date" id="de"></label>
  <label>Até: <input type="date" id="ate"></label>
  <button onclick="carregar()">Atualizar</button>
  <a href="produtos.php">📦 Produtos</a>
  <a href="estoque_relatorios.php">📦 Relatório de Estoque</a>
</div>

<div class="cards">
  <div class="card"><b>Total vendido</b><div style="font-size:22px;" id="total">R$ 0,00</div></div>
  <div class="card"><b>PIX</b><div style="font-size:22px;" id="pix">R$ 0,00</div></div>
  <div class="card"><b>Dinheiro</b><div style="font-size:22px;" id="dinheiro">R$ 0,00</div></div>
  <div class="card"><b>Cartões</b><div style="font-size:22px;" id="cartoes">R$ 0,00</div></div>
</div>

<table>
  <thead>
    <tr>
      <th>ID</th>
      <th>Data</th>
      <th>Pagamento</th>
      <th class="right">Total</th>
      <th></th>
    </tr>
  </thead>
  <tbody id="lista"></tbody>
</table>

<script>
  function brl(v){ return "R$ " + Number(v||0).toFixed(2).replace(".", ","); }
  function labelPag(p){
    if(p==="PIX") return "PIX";
    if(p==="DINHEIRO") return "Dinheiro";
    if(p==="CARTAO_DEBITO") return "Cartão Débito";
    if(p==="CARTAO_CREDITO") return "Cartão Crédito";
    return "Outros";
  }

  async function carregar(){
    const de = document.getElementById("de").value;
    const ate = document.getElementById("ate").value;

    const res = await fetch(`../api/vendas_listar.php?de=${de}&ate=${ate}`);
    const vendas = await res.json();

    let total=0, pix=0, dinheiro=0, cartoes=0;

    const tbody = document.getElementById("lista");
    tbody.innerHTML = "";

    vendas.forEach(v => {
      const t = Number(v.total||0);
      total += t;

      if(v.forma_pagamento==="PIX") pix += t;
      else if(v.forma_pagamento==="DINHEIRO") dinheiro += t;
      else if(v.forma_pagamento==="CARTAO_DEBITO" || v.forma_pagamento==="CARTAO_CREDITO") cartoes += t;

      const tr = document.createElement("tr");
      tr.innerHTML = `
        <td>#${v.id}</td>
        <td>${new Date(v.data_venda).toLocaleString("pt-BR")}</td>
        <td>${labelPag(v.forma_pagamento)}</td>
        <td class="right">${brl(v.total)}</td>
        <td class="right">
          <button onclick="window.open('../pdv/imprimir.php?id=${v.id}','_blank')">Reimprimir</button>
        </td>
      `;
      tbody.appendChild(tr);
    });

    document.getElementById("total").textContent = brl(total);
    document.getElementById("pix").textContent = brl(pix);
    document.getElementById("dinheiro").textContent = brl(dinheiro);
    document.getElementById("cartoes").textContent = brl(cartoes);
  }

  function hoje(){
    const d = new Date();
    const iso = d.toISOString().slice(0,10);
    document.getElementById("de").value = iso;
    document.getElementById("ate").value = iso;
  }

  hoje();
  carregar();
</script>

</body>
</html>