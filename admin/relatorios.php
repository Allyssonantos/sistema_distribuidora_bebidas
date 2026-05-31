<?php
session_start();
if (!isset($_SESSION["admin_id"])) { header("Location: login.php"); exit; }
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin - Relatórios de Vendas</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;600&family=IBM+Plex+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --bg:#0f1117; --surface:#1a1d27; --surface2:#222535; --border:#2e3146;
      --accent:#4f8ef7; --accent-dim:#1e3a6e;
      --green:#22c55e; --green-dim:#052e16;
      --yellow:#f59e0b; --yellow-dim:#451a03;
      --red:#ef4444; --red-dim:#3b0a0a;
      --text:#e8eaf0; --text-muted:#6b7399;
      --mono:"IBM Plex Mono",monospace; --sans:"IBM Plex Sans",sans-serif;
      --radius:10px; --shadow:0 4px 24px rgba(0,0,0,.45);
    }
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
    body{font-family:var(--sans);background:var(--bg);color:var(--text);min-height:100vh;}
    /* TOPBAR */
    .topbar{background:var(--surface);border-bottom:1px solid var(--border);padding:0 24px;height:56px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100;}
    .topbar-brand{font-family:var(--mono);font-size:14px;font-weight:600;letter-spacing:.04em;color:var(--text);}
    .topbar-links{display:flex;gap:4px;}
    .topbar-links a{font-size:12px;color:var(--text-muted);text-decoration:none;padding:6px 11px;border-radius:6px;border:1px solid transparent;transition:.15s;}
    .topbar-links a:hover{color:var(--text);border-color:var(--border);background:var(--surface2);}
    .topbar-links a.active{color:var(--accent);border-color:var(--accent-dim);background:var(--accent-dim);}
    /* LAYOUT */
    .wrap{max-width:1300px;margin:0 auto;padding:24px;}
    .page-title{font-size:20px;font-weight:600;margin-bottom:20px;display:flex;align-items:center;gap:10px;}
    /* TOOLBAR */
    .toolbar{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:14px 18px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:18px;}
    .t-left{display:flex;align-items:center;gap:8px;flex-wrap:wrap;}
    .t-right{display:flex;align-items:center;gap:6px;flex-wrap:wrap;}
    /* INPUTS */
    input,select{background:var(--bg);border:1px solid var(--border);color:var(--text);border-radius:8px;padding:9px 13px;font-size:13px;font-family:var(--sans);outline:none;transition:.15s;}
    input:focus,select:focus{border-color:var(--accent);box-shadow:0 0 0 3px rgba(79,142,247,.12);}
    input::placeholder{color:var(--text-muted);}
    select option{background:var(--surface2);}
    /* BUTTONS */
    .btn{padding:9px 16px;border-radius:8px;font-size:13px;font-weight:600;font-family:var(--sans);cursor:pointer;border:none;transition:.15s;display:inline-flex;align-items:center;gap:6px;}
    .btn:hover{opacity:.85;}
    .btn-primary{background:var(--accent);color:#fff;}
    .btn-success{background:var(--green);color:#000;}
    .btn-ghost{background:none;border:1px solid var(--border);color:var(--text-muted);}
    .btn-ghost:hover{border-color:var(--text-muted);color:var(--text);}
    .btn-danger{background:none;border:1px solid var(--border);color:var(--text-muted);}
    .btn-danger:hover{background:var(--red-dim);border-color:var(--red);color:var(--red);}
    a.btn{text-decoration:none;}
    /* CARDS */
    .cards{display:grid;gap:14px;margin-bottom:20px;}
    .card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:16px 18px;}
    .card-label{font-size:11px;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:var(--text-muted);margin-bottom:6px;}
    .card-val{font-family:var(--mono);font-size:26px;font-weight:600;}
    .card-sub{font-size:12px;color:var(--text-muted);margin-top:4px;}
    /* TABLE */
    .table-wrap{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;}
    table{width:100%;border-collapse:collapse;}
    thead th{padding:10px 14px;font-size:11px;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:var(--text-muted);border-bottom:1px solid var(--border);background:var(--surface2);text-align:left;}
    tbody tr{border-bottom:1px solid var(--border);transition:background .1s;}
    tbody tr:last-child{border-bottom:none;}
    tbody tr:hover{background:var(--surface2);}
    tbody td{padding:12px 14px;font-size:13px;vertical-align:middle;}
    .right{text-align:right;}
    .mono{font-family:var(--mono);}
    /* BADGES */
    .badge{padding:3px 9px;border-radius:999px;font-size:11px;font-weight:600;display:inline-block;}
    .badge-ok{background:var(--green-dim);color:var(--green);border:1px solid #166534;}
    .badge-warn{background:var(--red-dim);color:var(--red);border:1px solid #7f1d1d;}
    .badge-info{background:var(--accent-dim);color:var(--accent);border:1px solid var(--accent-dim);}
    .badge-yellow{background:var(--yellow-dim);color:var(--yellow);border:1px solid #78350f;}
    /* MODAL */
    .modal-bg{display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);backdrop-filter:blur(4px);align-items:center;justify-content:center;padding:20px;z-index:500;}
    .modal-box{background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:24px;box-shadow:var(--shadow);animation:slideUp .2s ease;width:560px;max-width:100%;}
    @keyframes slideUp{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:none}}
    .modal-title{font-size:16px;font-weight:600;margin-bottom:20px;display:flex;justify-content:space-between;align-items:center;}
    .modal-close{background:none;border:1px solid var(--border);color:var(--text-muted);border-radius:6px;width:30px;height:30px;cursor:pointer;font-size:16px;display:flex;align-items:center;justify-content:center;transition:.15s;}
    .modal-close:hover{border-color:var(--red);color:var(--red);}
    .field{margin-bottom:14px;}
    .field label{display:block;font-size:11px;font-weight:600;letter-spacing:.07em;text-transform:uppercase;color:var(--text-muted);margin-bottom:5px;}
    .field input,.field select{width:100%;}
    .modal-btns{display:flex;gap:10px;justify-content:flex-end;margin-top:20px;}
    .grid2{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
    /* ROW ALERTA */
    .row-alert{background:rgba(239,68,68,.06);}
    .cards{grid-template-columns:repeat(4,1fr);}
    @media(max-width:900px){.cards{grid-template-columns:repeat(2,1fr);}}
  </style>
</head>
<body>
<div class="topbar">
  <div class="topbar-brand">ADORA BEBIDAS · ADMIN</div>
  <nav class="topbar-links">
    <a href="produtos.php">📦 Produtos</a>
    <a href="estoque.php">📥 Estoque</a>
    <a href="relatorios.php" class="active">📊 Relatórios</a>
    <a href="lucro.php">📈 Lucro</a>
    <a href="fechamento_caixa.php">🧾 Caixas</a>
  </nav>
</div>
<div class="wrap">
  <div class="page-title">📊 Relatórios de Vendas</div>
  <div class="toolbar">
    <div class="t-left">
      <label style="font-size:13px;color:var(--text-muted);">De</label>
      <input type="date" id="de">
      <label style="font-size:13px;color:var(--text-muted);">Até</label>
      <input type="date" id="ate">
      <button class="btn btn-ghost" onclick="carregar()">🔎 Filtrar</button>
    </div>
  </div>

  <div class="cards" style="margin-bottom:20px;">
    <div class="card"><div class="card-label">Total Vendido</div><div class="card-val mono" id="total" style="color:var(--green);">R$ 0,00</div></div>
    <div class="card"><div class="card-label">PIX</div><div class="card-val mono" id="pix">R$ 0,00</div></div>
    <div class="card"><div class="card-label">Dinheiro</div><div class="card-val mono" id="dinheiro">R$ 0,00</div></div>
    <div class="card"><div class="card-label">Cartões</div><div class="card-val mono" id="cartoes">R$ 0,00</div></div>
  </div>

  <div class="table-wrap">
    <table>
      <thead>
        <tr><th>ID</th><th>Data / Hora</th><th>Pagamento</th><th class="right">Total</th><th class="right">Ações</th></tr>
      </thead>
      <tbody id="lista"></tbody>
    </table>
  </div>
</div>

<script>
  function brl(v){return "R$ "+Number(v||0).toFixed(2).replace(".",",");}
  async function carregar(){
    const de=document.getElementById("de").value, ate=document.getElementById("ate").value;
    const res=await fetch('../api/vendas_listar.php?de='+de+'&ate='+ate);
    const vendas=await res.json();
    let tTotal=0,tPix=0,tDin=0,tCar=0;
    const tbody=document.getElementById("lista");
    tbody.innerHTML="";
    vendas.forEach(v=>{
      const val=Number(v.total||0);
      tTotal+=val;
      if(v.forma_pagamento==="PIX")tPix+=val;
      else if(v.forma_pagamento==="DINHEIRO")tDin+=val;
      else if(v.forma_pagamento.includes("CARTAO"))tCar+=val;
      const tr=document.createElement("tr");
      tr.innerHTML='<td class="mono" style="color:var(--text-muted);">#'+v.id+'</td>'+
        '<td>'+new Date(v.data_venda).toLocaleString("pt-BR")+'</td>'+
        '<td><span class="badge badge-info">'+v.forma_pagamento+'</span></td>'+
        '<td class="right mono">'+brl(v.total)+'</td>'+
        '<td class="right"><button class="btn btn-ghost" style="padding:5px 12px;font-size:12px;" onclick="window.open(\'../pdv/imprimir.php?id='+v.id+'\')">🖨️ Reimprimir</button></td>';
      tbody.appendChild(tr);
    });
    document.getElementById("total").textContent=brl(tTotal);
    document.getElementById("pix").textContent=brl(tPix);
    document.getElementById("dinheiro").textContent=brl(tDin);
    document.getElementById("cartoes").textContent=brl(tCar);
  }
  const hoje=new Date().toISOString().split('T')[0];
  document.getElementById("de").value=hoje;
  document.getElementById("ate").value=hoje;
  carregar();
</script>
</body>
</html>