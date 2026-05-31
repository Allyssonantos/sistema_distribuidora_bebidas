<?php
session_start();
if (!isset($_SESSION["admin_id"])) { header("Location: login.php"); exit; }
?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin - Estoque</title>
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
    .sugestoes-wrap{position:relative;}
    .sugestoes{position:absolute;top:calc(100% + 4px);left:0;width:360px;background:var(--surface2);border:1px solid var(--border);border-radius:8px;z-index:200;overflow:hidden;box-shadow:var(--shadow);}
    .sugestoes div{padding:10px 14px;cursor:pointer;font-size:13px;border-bottom:1px solid var(--border);transition:background .1s;}
    .sugestoes div:hover{background:var(--accent-dim);}
    .form-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:20px;margin-top:18px;}
    .form-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-top:14px;}
  </style>
</head>
<body>
<div class="topbar">
  <div class="topbar-brand">ADORA BEBIDAS · ADMIN</div>
  <nav class="topbar-links">
    <a href="produtos.php">📦 Produtos</a>
    <a href="categorias.php">🏷️ Categorias</a>
    <a href="estoque.php" class="active">📥 Estoque</a>
    <a href="estoque_relatorios.php">📋 Mov. Estoque</a>
    <a href="relatorios.php">📊 Relatórios</a>
    <a href="lucro.php">📈 Lucro</a>
    <a href="fechamento_caixa.php">🧾 Caixas</a>
  </nav>
</div>
<div class="wrap">
  <div class="page-title">📥 Movimentação de Estoque</div>
  <div class="toolbar">
    <div class="t-left sugestoes-wrap">
      <input id="busca" placeholder="Nome ou código de barras..." style="width:300px;" onkeyup="buscar(event)">
      <div id="sugestoes" class="sugestoes" style="display:none;"></div>
    </div>
  </div>

  <div id="formMov" class="form-card" style="display:none;">
    <div class="page-title" id="p_nome" style="font-size:16px;margin-bottom:0;"></div>
    <div class="form-grid">
      <div class="field"><label>Tipo de Movimentação</label>
        <select id="tipo">
          <option value="ENTRADA">ENTRADA (Compra)</option>
          <option value="PERDA">PERDA (Quebra/Vencimento)</option>
          <option value="AJUSTE">AJUSTE (Inventário)</option>
        </select>
      </div>
      <div class="field"><label>Quantidade</label><input id="quantidade" type="number" step="0.001"></div>
      <div class="field"><label>Valor Unitário (opcional)</label><input id="valor_unit" type="number" step="0.01"></div>
      <div class="field"><label>Observação</label><input id="obs" placeholder="Ex: Fornecedor X"></div>
    </div>
    <div style="text-align:right;margin-top:18px;">
      <button class="btn btn-success" onclick="confirmar()">✅ Confirmar Movimentação</button>
    </div>
  </div>
</div>

<script>
  let produtoId=0;
  async function buscar(e){
    const q=e.target.value.trim();
    if(q.length<2)return document.getElementById("sugestoes").style.display="none";
    const res=await fetch('../api/produtos_buscar.php?q='+encodeURIComponent(q));
    const itens=await res.json();
    const div=document.getElementById("sugestoes");
    div.innerHTML="";div.style.display="block";
    itens.forEach(p=>{
      const d=document.createElement("div");
      d.textContent=p.nome+' (Est: '+p.estoque_atual+')';
      d.onclick=()=>selecionar(p);
      div.appendChild(d);
    });
  }
  function selecionar(p){
    produtoId=p.id;
    document.getElementById("sugestoes").style.display="none";
    document.getElementById("p_nome").textContent="📦 "+p.nome;
    document.getElementById("formMov").style.display="block";
    document.getElementById("quantidade").focus();
  }
  async function confirmar(){
    const payload={produto_id:produtoId,tipo:document.getElementById("tipo").value,quantidade:document.getElementById("quantidade").value,valor_unit:document.getElementById("valor_unit").value,observacao:document.getElementById("obs").value};
    const res=await fetch("../api/estoque_movimentar.php",{method:"POST",headers:{"Content-Type":"application/json"},body:JSON.stringify(payload)});
    const json=await res.json();
    if(json.ok){alert("Movimentação registrada!");location.reload();}else{alert(json.erro);}
  }
</script>
</body>
</html>