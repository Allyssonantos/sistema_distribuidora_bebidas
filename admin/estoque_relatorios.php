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
  <title>Relatório - Movimentação de Estoque</title>
  <style>
    body{font-family:Arial; margin:20px;}
    .top{display:flex; gap:10px; flex-wrap:wrap; align-items:center;}
    input{padding:10px;}
    button{padding:10px 14px; cursor:pointer;}
    .cards{display:grid; grid-template-columns:repeat(3,1fr); gap:10px; margin-top:14px;}
    .card{border:1px solid #eee; border-radius:12px; padding:12px;}
    @media(max-width:900px){ .cards{grid-template-columns:1fr;} }
    table{width:100%; border-collapse:collapse; margin-top:14px;}
    th,td{border-bottom:1px solid #ddd; padding:10px; vertical-align:top;}
    th{background:#fafafa; text-align:left;}
    .right{text-align:right;}
    .badge{padding:4px 8px; border-radius:999px; font-size:12px; display:inline-block;}
    .bcompra{background:#e7f7ed;}
    .bperda{background:#ffe8e8;}
    .bajuste{background:#fff2cc;}
  </style>
</head>
<body>

<h2>📦 Relatório - Movimentação de Estoque</h2>

<div class="top">
  <label>De: <input type="date" id="de"></label>
  <label>Até: <input type="date" id="ate"></label>
  <button onclick="carregar()">Atualizar</button>

  <a href="estoque.php">📥 Movimentar Estoque</a>
  <a href="produtos.php">📦 Admin - Produtos</a>
  <a href="relatorios.php">📊 Vendas</a>
  <a href="lucro.php">📊 Admin - Lucro</a>
</div>

<div class="cards">
  <div class="cards" style="grid-template-columns:repeat(6,1fr);">
  <div class="card">
    <div><span class="badge bcompra">COMPRA</span> <b>Qtd entrada</b></div>
    <div style="font-size:22px; margin-top:6px;" id="q_compra">0</div>
  </div>

  <div class="card">
    <div><span class="badge bcompra">COMPRA</span> <b>R$ compras</b></div>
    <div style="font-size:22px; margin-top:6px;" id="v_compra">R$ 0,00</div>
  </div>

  <div class="card">
    <div><span class="badge bperda">PERDA</span> <b>Qtd perdas</b></div>
    <div style="font-size:22px; margin-top:6px;" id="q_perda">0</div>
  </div>

  <div class="card">
    <div><span class="badge bperda">PERDA</span> <b>R$ perdas</b></div>
    <div style="font-size:22px; margin-top:6px;" id="v_perda">R$ 0,00</div>
  </div>

  <div class="card">
    <div><span class="badge bajuste">INVENTÁRIO</span> <b>Qtd ajustes</b></div>
    <div style="font-size:22px; margin-top:6px;" id="q_ajuste">0</div>
  </div>

  <div class="card">
    <div><span class="badge bajuste">INVENTÁRIO</span> <b>R$ ajustes</b></div>
    <div style="font-size:22px; margin-top:6px;" id="v_ajuste">R$ 0,00</div>
    <div style="font-size:12px; color:#666; margin-top:6px;">
      ↑ <span id="v_aj_pos">R$ 0,00</span> • ↓ <span id="v_aj_neg">R$ 0,00</span>
    </div>
  </div>
</div>

<style>
  @media(max-width:1200px){ .cards{grid-template-columns:repeat(2,1fr) !important;} }
</style>
</div>

<table>
  <thead>
    <tr>
      <th>Data</th>
      <th>Produto</th>
      <th>Tipo</th>
      <th>Origem</th>
      <th class="right">Qtd</th>
      <th class="right">V. Unit</th>
      <th>Obs</th>
    </tr>
  </thead>
  <tbody id="lista"></tbody>
</table>

<script>
  function fmtQtd(v){
    // mostra 3 casas mas tira zeros finais
    const s = Number(v||0).toFixed(3).replace(".", ",");
    return s.replace(/,?0+$/, "");
  }
  function brl(v){
    if(v === null || v === undefined || v === "") return "-";
    return "R$ " + Number(v||0).toFixed(2).replace(".", ",");
  }
  function badgeOrigem(o){
    if(o === "COMPRA") return `<span class="badge bcompra">COMPRA</span>`;
    if(o === "PERDA") return `<span class="badge bperda">PERDA</span>`;
    return `<span class="badge bajuste">INVENTÁRIO</span>`;
  }

  async function carregar(){
    const de = document.getElementById("de").value;
    const ate = document.getElementById("ate").value;

    const res = await fetch(`../api/mov_estoque_relatorio.php?de=${de}&ate=${ate}`);
    const json = await res.json();

    if(!json.ok){
      alert("Erro ao carregar relatório.");
      return;
    }

    document.getElementById("q_compra").textContent = fmtQtd(json.totais_qtd.compra);
    document.getElementById("q_perda").textContent  = fmtQtd(json.totais_qtd.perda);
    document.getElementById("q_ajuste").textContent = fmtQtd(json.totais_qtd.ajuste);

    document.getElementById("v_compra").textContent = brl(json.totais_valor.compra);
    document.getElementById("v_perda").textContent  = brl(json.totais_valor.perda);
    document.getElementById("v_ajuste").textContent = brl(json.totais_valor.ajuste);

    document.getElementById("v_aj_pos").textContent = brl(json.totais_valor.ajuste_pos);
    document.getElementById("v_aj_neg").textContent = brl(json.totais_valor.ajuste_neg);

    const tbody = document.getElementById("lista");
    tbody.innerHTML = "";

    json.lista.forEach(m => {
      const tr = document.createElement("tr");
      tr.innerHTML = `
        <td>${new Date(m.criado_em).toLocaleString("pt-BR")}</td>
        <td><b>${m.produto_nome}</b><br><span style="color:#666; font-size:12px;">Un: ${m.unidade}</span></td>
        <td>${m.tipo}</td>
        <td>${badgeOrigem(m.origem)}</td>
        <td class="right">${fmtQtd(m.quantidade)}</td>
        <td class="right">${brl(m.valor_unit)}</td>
        <td>${m.observacao ? m.observacao : "-"}</td>
      `;
      tbody.appendChild(tr);
    });
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