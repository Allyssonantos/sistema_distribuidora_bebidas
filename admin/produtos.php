<?php
session_start();
if (!isset($_SESSION["admin_id"])) { header("Location: login.php"); exit; }
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin - Produtos</title>
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
    @media(max-width:1000px){.cards{grid-template-columns:repeat(2,1fr);}}
    @media(max-width:600px){.cards{grid-template-columns:1fr;}}
    .sugestoes-wrap{position:relative;}
    .sugestoes{position:absolute;top:calc(100% + 4px);left:0;right:0;background:var(--surface2);border:1px solid var(--border);border-radius:8px;z-index:200;overflow:hidden;box-shadow:var(--shadow);}
    .sugestoes div{padding:10px 14px;cursor:pointer;font-size:13px;border-bottom:1px solid var(--border);transition:background .1s;}
    .sugestoes div:last-child{border-bottom:none;}
    .sugestoes div:hover{background:var(--accent-dim);}
  </style>
</head>
<body>
<div class="topbar">
  <div class="topbar-brand">ADORA BEBIDAS · ADMIN</div>
  <nav class="topbar-links">
    <a href="produtos.php" class="active">📦 Produtos</a>
    <a href="categorias.php">🏷️ Categorias</a>
    <a href="estoque.php">📥 Estoque</a>
    <a href="relatorios.php">📊 Relatórios</a>
    <a href="lucro.php">📈 Lucro</a>
    <a href="fechamento_caixa.php">🧾 Caixas</a>
  </nav>
</div>

<div class="wrap">
  <div class="page-title">📦 Produtos</div>

  <div class="toolbar">
    <div class="t-left">
      <input id="q" placeholder="Nome ou código de barras..." style="width:240px;">
      <label style="display:flex;align-items:center;gap:6px;font-size:13px;color:var(--text-muted);cursor:pointer;">
        <input type="checkbox" id="somenteLow" onchange="carregar()"> Só acabando
      </label>
      <button class="btn btn-ghost" onclick="carregar()">🔎 Buscar</button>
    </div>
    <div class="t-right">
      <button class="btn btn-success" onclick="novoProduto()">➕ Novo Produto</button>
    </div>
  </div>

  <div class="table-wrap">
    <table>
      <thead>
        <tr>
          <th>Produto</th>
          <th>Categoria</th>
          <th class="right">Estoque</th>
          <th class="right">Mín</th>
          <th class="right">Custo</th>
          <th class="right">Venda</th>
          <th>Status</th>
          <th class="right">Ações</th>
        </tr>
      </thead>
      <tbody id="lista"></tbody>
    </table>
  </div>
</div>

<!-- MODAL -->
<div id="modalbg" class="modal-bg">
  <div class="modal-box">
    <div class="modal-title">
      <span id="tituloModal">Produto</span>
      <button class="modal-close" onclick="fechar()">✕</button>
    </div>
    <div class="grid2">
      <div class="field"><label>Nome</label><input id="nome"></div>
      <div class="field"><label>Categoria</label><select id="categoria_id"></select></div>
      <div class="field"><label>Preço Custo</label><input id="preco_custo" type="number" step="0.01"></div>
      <div class="field"><label>Preço Venda</label><input id="preco_venda" type="number" step="0.01"></div>
      <div class="field"><label>Estoque Atual</label><input id="estoque_atual" type="number"></div>
      <div class="field"><label>Estoque Mínimo</label><input id="estoque_minimo" type="number"></div>
    </div>
    <div class="modal-btns">
      <button class="btn btn-ghost" onclick="fechar()">Cancelar</button>
      <button class="btn btn-primary" onclick="salvar()">Salvar Produto</button>
    </div>
  </div>
</div>

<script>
  let categorias = [], editId = 0;
  function brl(v){ return "R$ " + Number(v||0).toFixed(2).replace(".", ","); }
  async function carregarCategorias(){
    const res = await fetch("../api/categorias_listar.php");
    categorias = await res.json();
    const sel = document.getElementById("categoria_id");
    sel.innerHTML = '<option value="">(Sem categoria)</option>';
    categorias.forEach(c => sel.innerHTML += '<option value="'+c.id+'">'+c.nome+'</option>');
  }
  async function carregar(){
    const q = document.getElementById("q").value.trim();
    const low = document.getElementById("somenteLow").checked ? 1 : 0;
    const res = await fetch('../api/produtos_listar.php?q='+encodeURIComponent(q)+'&low='+low);
    const lista = await res.json();
    const tbody = document.getElementById("lista");
    tbody.innerHTML = "";
    lista.forEach(p => {
      const est = Number(p.estoque_atual), min = Number(p.estoque_minimo);
      const alert = p.ativo == 1 && est <= min;
      const tr = document.createElement("tr");
      if(alert) tr.className = "row-alert";
      tr.innerHTML = '<td><b>'+p.nome+'</b><br><small style="color:var(--text-muted)">'+( p.codigo_barras||'')+'</small></td>'+
        '<td>'+(p.categoria_nome||"-")+'</td>'+
        '<td class="right mono">'+est+'</td>'+
        '<td class="right mono">'+min+'</td>'+
        '<td class="right mono">'+brl(p.preco_custo)+'</td>'+
        '<td class="right mono">'+brl(p.preco_venda)+'</td>'+
        '<td>'+(alert?'<span class="badge badge-warn">ALERTA</span>':'<span class="badge badge-ok">OK</span>')+'</td>'+
        '<td class="right"><button class="btn btn-primary" style="padding:6px 12px;font-size:12px;" onclick=\'editar('+JSON.stringify(p)+')\'>Editar</button></td>';
      tbody.appendChild(tr);
    });
  }
  function abrir(){ document.getElementById("modalbg").style.display="flex"; }
  function fechar(){ document.getElementById("modalbg").style.display="none"; }
  function novoProduto(){
    editId=0;
    document.getElementById("tituloModal").textContent="Novo Produto";
    ["nome","preco_custo","preco_venda","estoque_atual","estoque_minimo"].forEach(id=>document.getElementById(id).value="");
    document.getElementById("categoria_id").value="";
    abrir();
  }
  function editar(p){
    editId=Number(p.id);
    document.getElementById("tituloModal").textContent="Editar Produto";
    document.getElementById("nome").value=p.nome;
    document.getElementById("categoria_id").value=p.categoria_id||"";
    document.getElementById("preco_custo").value=p.preco_custo;
    document.getElementById("preco_venda").value=p.preco_venda;
    document.getElementById("estoque_atual").value=p.estoque_atual;
    document.getElementById("estoque_minimo").value=p.estoque_minimo;
    abrir();
  }
  async function salvar(){
    const payload={id:editId,nome:document.getElementById("nome").value,categoria_id:document.getElementById("categoria_id").value,preco_custo:document.getElementById("preco_custo").value,preco_venda:document.getElementById("preco_venda").value,estoque_atual:document.getElementById("estoque_atual").value,estoque_minimo:document.getElementById("estoque_minimo").value,ativo:1};
    const res=await fetch("../api/produtos_salvar.php",{method:"POST",headers:{"Content-Type":"application/json"},body:JSON.stringify(payload)});
    const json=await res.json();
    if(json.ok){fechar();carregar();}else{alert(json.erro);}
  }
  window.onload=()=>{carregarCategorias();carregar();};
</script>
</body>
</html>