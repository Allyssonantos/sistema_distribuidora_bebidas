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
  <title>Admin - Fechamento de Caixa</title>
  <style>
    body{font-family:Arial; margin:20px;}
    .top{display:flex; gap:10px; flex-wrap:wrap; align-items:center;}
    input, select{padding:10px;}
    button{padding:10px 14px; cursor:pointer;}
    .cards{display:grid; grid-template-columns:repeat(5,1fr); gap:10px; margin-top:14px;}
    .card{border:1px solid #eee; border-radius:12px; padding:12px;}
    .big{font-size:22px; margin-top:6px;}
    .muted{color:#666; font-size:12px;}
    table{width:100%; border-collapse:collapse; margin-top:14px;}
    th,td{border-bottom:1px solid #ddd; padding:10px; text-align:left;}
    th{background:#fafafa;}
    .right{text-align:right;}
    @media(max-width:1000px){ .cards{grid-template-columns:repeat(2,1fr);} }
    @media(max-width:600px){ .cards{grid-template-columns:1fr;} }
  </style>
</head>
<body>

<h2>🧾 Admin - Fechamento de Caixa</h2>

<div class="top">
  <label>De: <input type="date" id="de"></label>
  <label>Até: <input type="date" id="ate"></label>

  <label>Caixa (Sessão):
    <select id="caixa_sessao_id"></select>
  </label>

  <button onclick="carregar()">Atualizar</button>
  <button onclick="imprimir()">Imprimir</button>

  <a href="relatorios.php">📊 Relatório de Vendas</a>
  <a href="estoque_relatorios.php">📦 Relatório de Estoque</a>
  <a href="estoque.php">📥 Movimentar Estoque</a>
  <a href="categorias.php">🏷️ Categorias</a>
  <a href="lucro.php">📊 Admin - Lucro</a>
</div>

<!-- Sessão info -->
<div class="cards" style="grid-template-columns:repeat(3,1fr);">
  <div class="card">
    <b>Sessão</b>
    <div class="big" id="s_id">-</div>
    <div class="muted" id="s_status">-</div>
  </div>
  <div class="card">
    <b>Abertura</b>
    <div class="big" id="s_abertura">-</div>
    <div class="muted">Horário que abriu o caixa</div>
  </div>
  <div class="card">
    <b>Fechamento</b>
    <div class="big" id="s_fechamento">-</div>
    <div class="muted">Horário que fechou o caixa</div>
  </div>
</div>

<div class="cards">
  <div class="card">
    <b>Total Geral</b>
    <div class="big" id="t_total">R$ 0,00</div>
    <div class="muted" id="t_qtd">0 vendas</div>
  </div>
  <div class="card">
    <b>Dinheiro</b>
    <div class="big" id="t_din">R$ 0,00</div>
  </div>
  <div class="card">
    <b>PIX</b>
    <div class="big" id="t_pix">R$ 0,00</div>
  </div>
  <div class="card">
    <b>Cartão Débito</b>
    <div class="big" id="t_deb">R$ 0,00</div>
  </div>
  <div class="card">
    <b>Cartão Crédito</b>
    <div class="big" id="t_cred">R$ 0,00</div>
  </div>
</div>

<div class="cards" style="grid-template-columns:repeat(2,1fr);">
  <div class="card">
    <b>Outros</b>
    <div class="big" id="t_outros">R$ 0,00</div>
  </div>
  <div class="card">
    <b>Conferência</b>
    <div class="muted">Compare com o relatório da maquininha e PIX.</div>
  </div>
</div>

<table>
  <thead>
    <tr>
      <th>ID</th>
      <th>Data</th>
      <th>Caixa (Sessão)</th>
      <th>Abertura</th>
      <th>Fechamento</th>
      <th>Pagamento</th>
      <th class="right">Total</th>
    </tr>
  </thead>
  <tbody id="lista"></tbody>
</table>

<script>
  function brl(v){ return "R$ " + Number(v||0).toFixed(2).replace(".", ","); }

  function hojeISO(){
    const d = new Date();
    return d.toISOString().slice(0,10);
  }

  function fmtDataHora(s){
    if(!s) return "-";
    // se vier "2026-02-22 11:54:57" mantém
    return String(s).replace("T"," ").slice(0,19);
  }

  async function carregarSessoes(){
    const sel = document.getElementById("caixa_sessao_id");
    sel.innerHTML = `<option value="0">Todas</option>`;

    // você vai criar esse endpoint (abaixo eu te passo)
    const res = await fetch("../api/caixa_sessoes_listar.php");
    const sessoes = await res.json();

    sessoes.forEach(s => {
      const titulo = `Sessão #${s.id} (Caixa ${s.caixa_id}) - ${fmtDataHora(s.aberto_em)} - ${s.status}`;
      sel.innerHTML += `<option value="${s.id}">${titulo}</option>`;
    });
  }

  async function carregar(){
    const de = document.getElementById("de").value;
    const ate = document.getElementById("ate").value;
    const sessao = document.getElementById("caixa_sessao_id").value;

    const res = await fetch(`../api/fechamento_caixa.php?de=${de}&ate=${ate}&caixa_sessao_id=${sessao}`);
    const json = await res.json();

    if(!json.ok){
      alert("Erro: " + (json.erro || "desconhecido"));
      return;
    }

    // sessão info
    document.getElementById("s_id").textContent = json.sessao?.id ? ("#" + json.sessao.id) : "-";
    document.getElementById("s_status").textContent = json.sessao?.status || "-";
    document.getElementById("s_abertura").textContent = fmtDataHora(json.sessao?.aberto_em);
    document.getElementById("s_fechamento").textContent = fmtDataHora(json.sessao?.fechado_em);

    // totais
    document.getElementById("t_total").textContent = brl(json.totais.total_geral);
    document.getElementById("t_qtd").textContent = `${json.totais.qtd_vendas} vendas`;

    document.getElementById("t_din").textContent = brl(json.totais.dinheiro);
    document.getElementById("t_pix").textContent = brl(json.totais.pix);
    document.getElementById("t_deb").textContent = brl(json.totais.cartao_debito);
    document.getElementById("t_cred").textContent = brl(json.totais.cartao_credito);
    document.getElementById("t_outros").textContent = brl(json.totais.outros);

    // lista
    const tbody = document.getElementById("lista");
    tbody.innerHTML = "";
    json.vendas.forEach(v => {
      const tr = document.createElement("tr");
      tr.innerHTML = `
        <td>${v.id}</td>
        <td>${fmtDataHora(v.data_venda)}</td>
        <td>${v.caixa_sessao_id || "-"}</td>
        <td>${fmtDataHora(v.aberto_em)}</td>
        <td>${fmtDataHora(v.fechado_em)}</td>
        <td>${v.forma_pagamento}</td>
        <td class="right">${brl(v.total)}</td>
      `;
      tbody.appendChild(tr);
    });
  }

  function imprimir(){ window.print(); }

  (async () => {
    document.getElementById("de").value = hojeISO();
    document.getElementById("ate").value = hojeISO();
    await carregarSessoes();
    carregar();
  })();
</script>

</body>
</html>