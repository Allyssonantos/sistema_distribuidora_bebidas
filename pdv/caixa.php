cat > /mnt/user-data/outputs/caixa.php << 'PHPEOF'
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>PDV - Caixa</title>
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
    body{font-family:var(--sans);background:var(--bg);color:var(--text);min-height:100vh;display:flex;flex-direction:column;}

    /* TOPBAR */
    .topbar{background:var(--surface);border-bottom:1px solid var(--border);padding:0 24px;height:56px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100;}
    .topbar-brand{font-family:var(--mono);font-size:15px;font-weight:600;letter-spacing:.04em;color:var(--text);display:flex;align-items:center;gap:10px;}
    .topbar-brand span{display:inline-block;width:8px;height:8px;border-radius:50%;background:var(--green);box-shadow:0 0 8px var(--green);animation:pulse 2s infinite;}
    @keyframes pulse{0%,100%{opacity:1}50%{opacity:.4}}
    .topbar-links{display:flex;gap:6px;}
    .topbar-links a{font-size:12px;color:var(--text-muted);text-decoration:none;padding:6px 12px;border-radius:6px;border:1px solid transparent;transition:.15s;}
    .topbar-links a:hover{color:var(--text);border-color:var(--border);background:var(--surface2);}

    /* STATUS CAIXA */
    #boxCaixa{padding:12px 24px 0;}
    .status-bar{border-radius:var(--radius);padding:12px 18px;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;font-size:14px;}
    .status-bar.open{background:var(--green-dim);border:1px solid #166534;}
    .status-bar.close{background:var(--yellow-dim);border:1px solid #78350f;}
    .status-bar .label{font-weight:600;display:flex;align-items:center;gap:8px;}
    .dot{width:8px;height:8px;border-radius:50%;}
    .dot.green{background:var(--green);box-shadow:0 0 8px var(--green);}
    .dot.yellow{background:var(--yellow);}

    /* LAYOUT TRIPLO: carrinho | totais | vendas recentes */
    .main{flex:1;display:grid;grid-template-columns:1fr 300px 300px;gap:16px;padding:16px 24px;max-width:1500px;width:100%;margin:0 auto;}

    /* PANELS */
    .panel{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;display:flex;flex-direction:column;}
    .panel-header{padding:12px 16px;border-bottom:1px solid var(--border);font-size:11px;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:var(--text-muted);display:flex;align-items:center;justify-content:space-between;gap:8px;flex-shrink:0;}

    /* BUSCA */
    .search-wrap{padding:14px 16px;border-bottom:1px solid var(--border);position:relative;flex-shrink:0;}
    .search-inner{display:flex;gap:8px;}
    .search-input{flex:1;background:var(--bg);border:1px solid var(--border);color:var(--text);border-radius:8px;padding:9px 13px;font-size:14px;font-family:var(--sans);outline:none;transition:.15s;}
    .search-input:focus{border-color:var(--accent);box-shadow:0 0 0 3px rgba(79,142,247,.15);}
    .search-input::placeholder{color:var(--text-muted);}
    .btn-search{background:var(--accent);color:#fff;border:none;padding:9px 16px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;transition:opacity .15s;white-space:nowrap;}
    .btn-search:hover{opacity:.85;}

    /* SUGESTÕES */
    .sugestoes{position:absolute;left:16px;right:16px;top:calc(100% - 6px);background:var(--surface2);border:1px solid var(--border);border-radius:8px;z-index:200;overflow:hidden;box-shadow:var(--shadow);}
    .sugestoes div{padding:10px 14px;cursor:pointer;font-size:13px;border-bottom:1px solid var(--border);transition:background .1s;display:flex;justify-content:space-between;align-items:center;}
    .sugestoes div:last-child{border-bottom:none;}
    .sugestoes div:hover{background:var(--accent-dim);}
    .s-preco{font-family:var(--mono);color:var(--green);font-size:12px;}

    /* TABELA CARRINHO */
    .carrinho-wrap{overflow-x:auto;flex:1;}
    table{width:100%;border-collapse:collapse;}
    thead th{padding:9px 12px;font-size:11px;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:var(--text-muted);border-bottom:1px solid var(--border);background:var(--surface2);}
    tbody tr{border-bottom:1px solid var(--border);transition:background .1s;}
    tbody tr:hover{background:var(--surface2);}
    tbody td{padding:10px 12px;font-size:13px;vertical-align:middle;}
    .right{text-align:right;}
    .prod-nome{font-weight:500;}
    .qtd-ctrl{display:flex;align-items:center;gap:5px;justify-content:flex-end;}
    .qtd-btn{width:26px;height:26px;border-radius:6px;border:1px solid var(--border);background:var(--surface2);color:var(--text);font-size:15px;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:.1s;line-height:1;}
    .qtd-btn:hover{background:var(--accent-dim);border-color:var(--accent);}
    .qtd-input{width:50px;text-align:center;background:var(--bg);border:1px solid var(--border);color:var(--text);border-radius:6px;padding:4px;font-size:12px;font-family:var(--mono);}
    .qtd-input:focus{outline:none;border-color:var(--accent);}
    .val-mono{font-family:var(--mono);font-size:12px;}
    .sub-mono{font-family:var(--mono);font-size:13px;font-weight:600;}
    .btn-rem{background:none;border:1px solid var(--border);color:var(--text-muted);border-radius:6px;width:26px;height:26px;cursor:pointer;font-size:13px;display:flex;align-items:center;justify-content:center;transition:.15s;}
    .btn-rem:hover{background:var(--red-dim);border-color:var(--red);color:var(--red);}
    .empty-msg{padding:32px 16px;text-align:center;color:var(--text-muted);font-size:13px;}

    /* PAINEL DIREITO (totais+ações) */
    .side-panel{display:flex;flex-direction:column;gap:14px;}
    .total-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:18px;}
    .total-label{font-size:11px;font-weight:600;letter-spacing:.1em;text-transform:uppercase;color:var(--text-muted);margin-bottom:6px;}
    .total-val{font-family:var(--mono);font-size:34px;font-weight:600;color:var(--green);letter-spacing:-.01em;}
    .total-prefix{font-size:16px;color:var(--text-muted);margin-right:2px;}
    .actions-card{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);padding:14px;display:flex;flex-direction:column;gap:8px;}
    .btn-finalizar{background:var(--green);color:#000;border:none;border-radius:8px;padding:13px;font-size:14px;font-weight:700;font-family:var(--sans);cursor:pointer;transition:opacity .15s,transform .1s;letter-spacing:.01em;}
    .btn-finalizar:hover:not(:disabled){opacity:.9;transform:translateY(-1px);}
    .btn-finalizar:disabled{opacity:.35;cursor:not-allowed;transform:none;}
    .btn-limpar{background:none;border:1px solid var(--border);color:var(--text-muted);border-radius:8px;padding:9px;font-size:13px;font-family:var(--sans);cursor:pointer;transition:.15s;}
    .btn-limpar:hover{border-color:var(--red);color:var(--red);background:var(--red-dim);}

    /* PAINEL VENDAS RECENTES */
    .vendas-panel{background:var(--surface);border:1px solid var(--border);border-radius:var(--radius);display:flex;flex-direction:column;max-height:calc(100vh - 140px);}
    .vendas-list{overflow-y:auto;flex:1;}
    .venda-item{padding:12px 14px;border-bottom:1px solid var(--border);transition:background .1s;}
    .venda-item:last-child{border-bottom:none;}
    .venda-item:hover{background:var(--surface2);}
    .venda-top{display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;}
    .venda-id{font-family:var(--mono);font-size:12px;color:var(--text-muted);}
    .venda-total{font-family:var(--mono);font-size:14px;font-weight:600;color:var(--green);}
    .venda-meta{font-size:11px;color:var(--text-muted);display:flex;gap:8px;margin-bottom:6px;}
    .badge-pag{background:var(--accent-dim);color:var(--accent);border:1px solid var(--accent-dim);padding:2px 7px;border-radius:999px;font-size:10px;font-weight:600;}
    .badge-cancel{background:var(--red-dim);color:var(--red);border:1px solid #7f1d1d;padding:2px 7px;border-radius:999px;font-size:10px;font-weight:600;}
    .btn-cancelar-venda{background:none;border:1px solid var(--border);color:var(--text-muted);border-radius:6px;padding:4px 10px;font-size:11px;font-family:var(--sans);cursor:pointer;transition:.15s;width:100%;}
    .btn-cancelar-venda:hover{background:var(--red-dim);border-color:var(--red);color:var(--red);}
    .vendas-empty{padding:30px 16px;text-align:center;color:var(--text-muted);font-size:13px;}

    /* MODAL */
    .modal-bg{display:none;position:fixed;inset:0;background:rgba(0,0,0,.6);backdrop-filter:blur(4px);align-items:center;justify-content:center;padding:20px;z-index:500;}
    .modal-box{background:var(--surface);border:1px solid var(--border);width:480px;max-width:100%;border-radius:14px;padding:24px;box-shadow:var(--shadow);animation:slideUp .2s ease;}
    @keyframes slideUp{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:none}}
    .modal-title{font-size:16px;font-weight:600;margin-bottom:18px;display:flex;justify-content:space-between;align-items:center;}
    .modal-close{background:none;border:1px solid var(--border);color:var(--text-muted);border-radius:6px;width:28px;height:28px;cursor:pointer;font-size:15px;display:flex;align-items:center;justify-content:center;transition:.15s;}
    .modal-close:hover{border-color:var(--red);color:var(--red);}
    .field{margin-bottom:14px;}
    .field label{display:block;font-size:11px;font-weight:600;letter-spacing:.06em;text-transform:uppercase;color:var(--text-muted);margin-bottom:5px;}
    .field select,.field input{width:100%;background:var(--bg);border:1px solid var(--border);color:var(--text);border-radius:8px;padding:10px 13px;font-size:14px;font-family:var(--sans);outline:none;transition:.15s;}
    .field select:focus,.field input:focus{border-color:var(--accent);}
    .field select option{background:var(--surface2);}
    .troco-row{background:var(--surface2);border:1px solid var(--border);border-radius:8px;padding:11px 13px;display:flex;justify-content:space-between;align-items:center;margin-top:8px;font-size:13px;}
    .troco-val{font-family:var(--mono);font-size:15px;font-weight:600;color:var(--yellow);}
    .modal-total{background:var(--accent-dim);border:1px solid var(--accent);border-radius:8px;padding:13px;text-align:center;margin:14px 0;}
    .modal-total-label{font-size:11px;letter-spacing:.1em;text-transform:uppercase;color:var(--text-muted);margin-bottom:3px;}
    .modal-total-val{font-family:var(--mono);font-size:26px;font-weight:600;color:var(--accent);}
    .modal-btns{display:flex;gap:10px;justify-content:flex-end;margin-top:4px;}
    .btn-cancel-m{background:none;border:1px solid var(--border);color:var(--text-muted);border-radius:8px;padding:9px 16px;font-family:var(--sans);cursor:pointer;font-size:13px;transition:.15s;}
    .btn-cancel-m:hover{border-color:var(--text-muted);color:var(--text);}
    .btn-confirm{background:var(--accent);color:#fff;border:none;border-radius:8px;padding:9px 20px;font-size:13px;font-weight:600;font-family:var(--sans);cursor:pointer;transition:opacity .15s;}
    .btn-confirm:hover{opacity:.85;}
    .btn-confirm-danger{background:var(--red);color:#fff;}

    /* Alerta senha cancelamento */
    .senha-wrap{background:var(--red-dim);border:1px solid #7f1d1d;border-radius:8px;padding:14px;margin-bottom:14px;}
    .senha-wrap p{font-size:13px;color:var(--red);margin-bottom:10px;}

    /* Resumo fechamento */
    .resumo-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px;font-size:13px;}
    .resumo-item{background:var(--surface2);border:1px solid var(--border);border-radius:8px;padding:10px 12px;}
    .resumo-item .ri-label{color:var(--text-muted);font-size:11px;margin-bottom:2px;}
    .resumo-item .ri-val{font-family:var(--mono);font-weight:600;font-size:14px;}

    @media(max-width:1100px){.main{grid-template-columns:1fr 280px;}.vendas-panel{display:none;}}
    @media(max-width:750px){.main{grid-template-columns:1fr;}}
  </style>
</head>
<body>

<!-- TOPBAR -->
<div class="topbar">
  <div class="topbar-brand">
    <span></span>
    PDV · ADORA BEBIDAS
  </div>
  <nav class="topbar-links">
    <a href="../admin/produtos.php">Produtos</a>
    <a href="../admin/relatorios.php">Relatórios</a>
    <a href="../admin/fechamento_caixa.php">Caixas</a>
    <a href="../admin/estoque.php">Estoque</a>
  </nav>
</div>

<!-- STATUS CAIXA -->
<div id="boxCaixa" style="padding:14px 24px 0;"></div>

<!-- MAIN -->
<div class="main">

  <!-- COL 1: CARRINHO -->
  <div class="panel">
    <div class="panel-header">🛒 Carrinho de Venda</div>
    <div class="search-wrap">
      <div class="search-inner">
        <input id="busca" class="search-input" placeholder="Nome ou código de barras..." autofocus />
        <button class="btn-search" onclick="buscar()">↵ Buscar</button>
      </div>
      <div id="sugestoes" class="sugestoes" style="display:none;"></div>
    </div>
    <div class="carrinho-wrap">
      <table>
        <thead>
          <tr>
            <th>Produto</th>
            <th class="right">Qtd</th>
            <th class="right">Unit.</th>
            <th class="right">Subtotal</th>
            <th></th>
          </tr>
        </thead>
        <tbody id="itens"></tbody>
      </table>
      <div id="emptyMsg" class="empty-msg">Nenhum item adicionado</div>
    </div>
  </div>

  <!-- COL 2: TOTAL + AÇÕES -->
  <div class="side-panel">
    <div class="total-card">
      <div class="total-label">Total da venda</div>
      <div class="total-val"><span class="total-prefix">R$</span><span id="total">0,00</span></div>
    </div>
    <div class="actions-card">
      <button id="btnFinalizar" class="btn-finalizar" onclick="abrirFinalizar()">✅ Finalizar Venda</button>
      <button class="btn-limpar" onclick="limparCarrinho()">🧹 Limpar carrinho</button>
    </div>
  </div>

  <!-- COL 3: VENDAS RECENTES -->
  <div class="vendas-panel panel">
    <div class="panel-header">
      🧾 Vendas Recentes
      <button onclick="carregarVendas()" style="background:none;border:none;color:var(--text-muted);cursor:pointer;font-size:13px;padding:2px 6px;border-radius:4px;" title="Atualizar">↺</button>
    </div>
    <div class="vendas-list" id="vendasList">
      <div class="vendas-empty">Nenhuma venda ainda</div>
    </div>
  </div>

</div>

<!-- ══════════ MODAIS ══════════ -->

<!-- MODAL FINALIZAR VENDA -->
<div id="modal" class="modal-bg">
  <div class="modal-box">
    <div class="modal-title">
      Finalizar venda
      <button class="modal-close" onclick="fecharModal()">✕</button>
    </div>
    <div class="field">
      <label>Forma de pagamento</label>
      <select id="pagamento" onchange="atualizarPagamento()">
        <option value="PIX">PIX</option>
        <option value="DINHEIRO">Dinheiro</option>
        <option value="CARTAO_DEBITO">Cartão Débito</option>
        <option value="CARTAO_CREDITO">Cartão Crédito</option>
        <option value="OUTROS">Outros</option>
      </select>
    </div>
    <div id="blocoDinheiro" style="display:none;">
      <div class="field">
        <label>Valor recebido</label>
        <input id="recebido" type="number" step="0.01" min="0" oninput="calcularTroco()" placeholder="Ex: 50.00" />
      </div>
      <div class="troco-row">
        <span>Troco</span>
        <span class="troco-val">R$ <span id="troco">0,00</span></span>
      </div>
    </div>
    <div class="modal-total">
      <div class="modal-total-label">Total a pagar</div>
      <div class="modal-total-val">R$ <span id="totalModal">0,00</span></div>
    </div>
    <div class="modal-btns">
      <button class="btn-cancel-m" onclick="fecharModal()">Cancelar</button>
      <button class="btn-confirm" onclick="confirmarVenda()">Confirmar pagamento</button>
    </div>
  </div>
</div>

<!-- MODAL CANCELAR VENDA (senha ADM) -->
<div id="modalCancelar" class="modal-bg">
  <div class="modal-box">
    <div class="modal-title">
      ⚠️ Cancelar Venda
      <button class="modal-close" onclick="fecharModalCancelar()">✕</button>
    </div>
    <div class="senha-wrap">
      <p>Esta ação cancela a venda e estorna o estoque. É necessária a senha do administrador.</p>
      <div class="field" style="margin-bottom:0;">
        <label>Senha do ADM</label>
        <input id="senhaAdm" type="password" placeholder="Digite a senha..." />
      </div>
    </div>
    <div style="background:var(--surface2);border:1px solid var(--border);border-radius:8px;padding:12px 14px;margin-bottom:4px;">
      <div style="font-size:11px;color:var(--text-muted);margin-bottom:4px;">Venda a cancelar</div>
      <div id="cancelarVendaInfo" style="font-family:var(--mono);font-size:14px;font-weight:600;"></div>
    </div>
    <div class="modal-btns">
      <button class="btn-cancel-m" onclick="fecharModalCancelar()">Voltar</button>
      <button class="btn-confirm btn-confirm-danger" onclick="confirmarCancelar()">Confirmar cancelamento</button>
    </div>
  </div>
</div>

<!-- MODAL FECHAR CAIXA -->
<div id="modalFechar" class="modal-bg">
  <div class="modal-box">
    <div class="modal-title">
      Resumo do caixa
      <button class="modal-close" onclick="fecharModalFechar()">✕</button>
    </div>
    <div id="resumoBox" style="font-size:14px;margin-bottom:14px;"></div>
    <div class="field">
      <label>Observação (opcional)</label>
      <input id="obsFechamento" placeholder="Ex: conferido com a maquininha..." />
    </div>
    <div class="modal-btns">
      <button class="btn-cancel-m" onclick="fecharModalFechar()">Cancelar</button>
      <button class="btn-confirm" style="background:var(--yellow);color:#000;" onclick="confirmarFecharCaixa()">Confirmar e imprimir</button>
    </div>
  </div>
</div>

<iframe id="iframeImpressao" style="display:none;"></iframe>

<script>
  const CAIXA_ID = 1;
  let CAIXA_ABERTO = false;
  let CAIXA_SESSAO_ID = null;
  let sessaoAbertaId = 0;
  let vendaCancelarId = 0;

  const carrinho = [];

  function fmt(v){ return Number(v||0).toFixed(2).replace(".", ","); }
  function setBotoesVenda(ok){ document.getElementById("btnFinalizar").disabled = !ok; }

  /* ── BUSCA ── */
  async function buscar(){
    const q = document.getElementById("busca").value.trim();
    if(!q) return;
    const res = await fetch("../api/produtos_buscar.php?q=" + encodeURIComponent(q));
    const lista = await res.json();
    const box = document.getElementById("sugestoes");
    box.innerHTML = "";
    if(!lista.length){
      box.style.display = "block";
      box.innerHTML = `<div style="color:var(--text-muted);">Nenhum produto encontrado</div>`;
      return;
    }
    box.style.display = "block";
    lista.forEach(p => {
      const div = document.createElement("div");
      div.innerHTML = `<span>${p.nome}</span><span class="s-preco">R$ ${fmt(p.preco_venda)} · Est: ${p.estoque_atual}</span>`;
      div.onclick = () => adicionar(p);
      box.appendChild(div);
    });
  }

  document.getElementById("busca").addEventListener("keydown", e => { if(e.key === "Enter") buscar(); });
  document.addEventListener("click", e => {
    if(!e.target.closest(".search-wrap")) document.getElementById("sugestoes").style.display = "none";
  });

  /* ── CARRINHO ── */
  function adicionar(p){
    document.getElementById("sugestoes").style.display = "none";
    document.getElementById("busca").value = "";
    document.getElementById("busca").focus();
    const id = parseInt(p.id);
    const ja = carrinho.find(i => i.id === id);
    if(ja) ja.qtd += 1;
    else carrinho.push({ id, nome: p.nome, valor: parseFloat(p.preco_venda), qtd: 1 });
    render();
  }

  function remover(id){ const idx = carrinho.findIndex(i => i.id === id); if(idx >= 0) carrinho.splice(idx, 1); render(); }
  function alterarQtd(id, delta){ const item = carrinho.find(i => i.id === id); if(!item) return; item.qtd += delta; if(item.qtd <= 0) remover(id); else render(); }
  function atualizarQtdDigitada(id, v){
    const item = carrinho.find(i => i.id === id);
    if(!item) return;
    const qtd = parseFloat(v);
    if(isNaN(qtd) || qtd <= 0){ alert("Quantidade inválida."); render(); return; }
    item.qtd = qtd; render();
  }

  function render(){
    const tbody = document.getElementById("itens");
    const empty = document.getElementById("emptyMsg");
    tbody.innerHTML = "";
    let total = 0;
    if(!carrinho.length){ empty.style.display = "block"; document.getElementById("total").textContent = "0,00"; return; }
    empty.style.display = "none";
    carrinho.forEach(i => {
      const sub = i.qtd * i.valor; total += sub;
      const tr = document.createElement("tr");
      tr.innerHTML = `
        <td class="prod-nome">${i.nome}</td>
        <td class="right">
          <div class="qtd-ctrl">
            <button class="qtd-btn" onclick="alterarQtd(${i.id}, -1)">−</button>
            <input class="qtd-input" type="number" value="${i.qtd}" min="1" step="0.001" onchange="atualizarQtdDigitada(${i.id}, this.value)">
            <button class="qtd-btn" onclick="alterarQtd(${i.id}, 1)">+</button>
          </div>
        </td>
        <td class="right val-mono">R$ ${fmt(i.valor)}</td>
        <td class="right sub-mono">R$ ${fmt(sub)}</td>
        <td class="right"><button class="btn-rem" onclick="remover(${i.id})" title="Remover">✕</button></td>
      `;
      tbody.appendChild(tr);
    });
    document.getElementById("total").textContent = fmt(total);
  }

  function limparCarrinho(){ if(carrinho.length && !confirm("Limpar carrinho?")) return; carrinho.length = 0; render(); }

  /* ── MODAL FINALIZAR ── */
  function abrirFinalizar(){
    if(!CAIXA_ABERTO){ alert("Caixa está FECHADO. Abra o caixa para vender."); return; }
    if(!carrinho.length){ alert("Carrinho vazio."); return; }
    document.getElementById("modal").style.display = "flex";
    document.getElementById("totalModal").textContent = document.getElementById("total").textContent;
    document.getElementById("pagamento").value = "PIX";
    atualizarPagamento();
  }
  function fecharModal(){ document.getElementById("modal").style.display = "none"; }
  function atualizarPagamento(){
    const v = document.getElementById("pagamento").value;
    document.getElementById("blocoDinheiro").style.display = v === "DINHEIRO" ? "block" : "none";
    if(v === "DINHEIRO") calcularTroco();
  }
  function calcularTroco(){
    const total = parseFloat(document.getElementById("total").textContent.replace(",", "."));
    const rec = parseFloat(document.getElementById("recebido").value || "0");
    document.getElementById("troco").textContent = fmt(Math.max(0, rec - total));
  }

  async function confirmarVenda(){
    if(!CAIXA_ABERTO){ alert("Caixa FECHADO."); return; }
    const forma = document.getElementById("pagamento").value;
    const total = parseFloat(document.getElementById("total").textContent.replace(",", "."));
    let recebido = total, troco = 0;
    if(forma === "DINHEIRO"){
      recebido = parseFloat(document.getElementById("recebido").value || "0");
      if(recebido < total){ alert("Valor insuficiente."); return; }
      troco = recebido - total;
    }
    const payload = { caixa_id: CAIXA_ID, forma_pagamento: forma, valor_recebido: recebido, troco, itens: carrinho.map(i => ({ id: i.id, qtd: i.qtd })) };
    try {
      const res = await fetch("../api/vendas_salvar.php", { method:"POST", headers:{"Content-Type":"application/json"}, body: JSON.stringify(payload) });
      const json = await res.json();
      if(!json.ok){ alert("Erro: " + json.erro); return; }
      fecharModal();
      limparCarrinho();
      document.getElementById("iframeImpressao").src = "imprimir.php?id=" + json.venda_id;
      carregarVendas(); // atualiza lista lateral
    } catch(e){ alert("Erro de comunicação."); }
  }

  /* ── VENDAS RECENTES ── */
  function pagLabel(p){
    return { PIX:"PIX", DINHEIRO:"Dinheiro", CARTAO_DEBITO:"Débito", CARTAO_CREDITO:"Crédito", OUTROS:"Outros" }[p] || p;
  }

  async function carregarVendas(){
    const list = document.getElementById("vendasList");
    try {
      // Busca as últimas vendas da sessão ativa (ou do dia)
      const res = await fetch("../api/vendas_listar.php?de=" + new Date().toISOString().slice(0,10) + "&ate=" + new Date().toISOString().slice(0,10));
      const vendas = await res.json();

      if(!vendas.length){
        list.innerHTML = '<div class="vendas-empty">Nenhuma venda hoje</div>';
        return;
      }

      // Mostra as 20 mais recentes
      const recentes = vendas.slice(0, 20);
      list.innerHTML = "";

      recentes.forEach(v => {
        const cancelada = v.status === "CANCELADA";
        const div = document.createElement("div");
        div.className = "venda-item";

        const hora = new Date(v.data_venda).toLocaleTimeString("pt-BR", { hour:"2-digit", minute:"2-digit" });

        div.innerHTML = `
          <div class="venda-top">
            <span class="venda-id">#${v.id}</span>
            <span class="venda-total" style="${cancelada ? 'color:var(--text-muted);text-decoration:line-through;' : ''}">R$ ${fmt(v.total)}</span>
          </div>
          <div class="venda-meta">
            <span>${hora}</span>
            ${cancelada
              ? '<span class="badge-cancel">CANCELADA</span>'
              : `<span class="badge-pag">${pagLabel(v.forma_pagamento)}</span>`
            }
          </div>
          ${!cancelada ? `<button class="btn-cancelar-venda" onclick="abrirCancelar(${v.id}, '${fmt(v.total)}')">🚫 Cancelar venda</button>` : ''}
        `;
        list.appendChild(div);
      });
    } catch(e){
      list.innerHTML = '<div class="vendas-empty" style="color:var(--red);">Erro ao carregar</div>';
    }
  }

  /* ── MODAL CANCELAR VENDA ── */
  function abrirCancelar(id, total){
    vendaCancelarId = id;
    document.getElementById("cancelarVendaInfo").textContent = `Venda #${id}  ·  R$ ${total}`;
    document.getElementById("senhaAdm").value = "";
    document.getElementById("modalCancelar").style.display = "flex";
    setTimeout(() => document.getElementById("senhaAdm").focus(), 100);
  }
  function fecharModalCancelar(){
    document.getElementById("modalCancelar").style.display = "none";
    vendaCancelarId = 0;
  }

  async function confirmarCancelar(){
    const senha = document.getElementById("senhaAdm").value;
    if(!senha){ alert("Digite a senha do ADM."); return; }
    if(vendaCancelarId <= 0) return;

    try {
      const res = await fetch("../api/vendas_cancelar.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ venda_id: vendaCancelarId, senha_adm: senha })
      });
      const json = await res.json();
      if(!json.ok){
        alert("❌ " + (json.erro || "Erro ao cancelar."));
        document.getElementById("senhaAdm").value = "";
        document.getElementById("senhaAdm").focus();
        return;
      }
      fecharModalCancelar();
      carregarVendas();
      alert("✅ Venda #" + vendaCancelarId + " cancelada com sucesso.");
    } catch(e){
      alert("Erro de comunicação.");
    }
  }

  // Enter na senha dispara confirmação
  document.getElementById("senhaAdm").addEventListener("keydown", e => { if(e.key === "Enter") confirmarCancelar(); });

  /* ── STATUS CAIXA ── */
  async function verificarCaixa(){
    const box = document.getElementById("boxCaixa");
    try {
      const res = await fetch("../api/caixa_status.php?caixa_id=" + CAIXA_ID);
      const json = await res.json();
      if(json.aberto){
        CAIXA_ABERTO = true; CAIXA_SESSAO_ID = json.sessao_id; setBotoesVenda(true);
        box.innerHTML = `
          <div class="status-bar open">
            <div class="label"><span class="dot green"></span> Caixa ABERTO · Sessão #${json.sessao_id}</div>
            <button onclick="abrirModalFechar()" style="background:var(--green);color:#000;border:none;padding:8px 14px;border-radius:6px;font-weight:600;cursor:pointer;font-size:13px;">Fechar caixa</button>
          </div>`;
      } else {
        CAIXA_ABERTO = false; CAIXA_SESSAO_ID = null; setBotoesVenda(false);
        box.innerHTML = `
          <div class="status-bar close">
            <div class="label" style="color:var(--yellow);"><span class="dot yellow"></span> Caixa FECHADO</div>
            <button onclick="abrirCaixa()" style="background:var(--yellow);color:#000;border:none;padding:8px 14px;border-radius:6px;font-weight:600;cursor:pointer;font-size:13px;">Abrir caixa agora</button>
          </div>`;
      }
    } catch(e){
      box.innerHTML = `<div class="status-bar close"><span style="color:var(--red);">⚠️ Erro de conexão.</span></div>`;
    }
  }

  async function abrirCaixa(){
    const troco = prompt("Troco inicial (R$):", "0.00");
    if(troco === null) return;
    const res = await fetch("../api/caixa_abrir.php", { method:"POST", headers:{"Content-Type":"application/json"}, body: JSON.stringify({ caixa_id: CAIXA_ID, troco_inicial: parseFloat(troco||0) }) });
    const json = await res.json();
    if(!json.ok){ alert("Erro: " + json.erro); return; }
    verificarCaixa();
  }

  async function abrirModalFechar(){
    const res = await fetch("../api/caixa_resumo.php");
    const json = await res.json();
    if(!json.ok){ alert(json.erro || "Erro ao carregar resumo."); return; }
    if(!json.aberto){ alert("Caixa já está fechado."); verificarCaixa(); return; }
    sessaoAbertaId = json.sessao.id;
    const t = json.totais, s = json.sessao;
    const brl = v => "R$ " + Number(v||0).toFixed(2).replace(".", ",");
    document.getElementById("resumoBox").innerHTML = `
      <div class="resumo-grid">
        <div class="resumo-item"><div class="ri-label">Sessão</div><div class="ri-val">#${s.id}</div></div>
        <div class="resumo-item"><div class="ri-label">Abertura</div><div class="ri-val" style="font-size:12px;">${s.aberto_em}</div></div>
        <div class="resumo-item"><div class="ri-label">Troco inicial</div><div class="ri-val">${brl(s.troco_inicial)}</div></div>
        <div class="resumo-item"><div class="ri-label">Total geral</div><div class="ri-val" style="color:var(--green);">${brl(t.total_geral)}</div></div>
        <div class="resumo-item"><div class="ri-label">Dinheiro</div><div class="ri-val">${brl(t.dinheiro)}</div></div>
        <div class="resumo-item"><div class="ri-label">PIX</div><div class="ri-val">${brl(t.pix)}</div></div>
        <div class="resumo-item"><div class="ri-label">Débito</div><div class="ri-val">${brl(t.cartao_debito)}</div></div>
        <div class="resumo-item"><div class="ri-label">Crédito</div><div class="ri-val">${brl(t.cartao_credito)}</div></div>
      </div>
      <div style="margin-top:10px;font-size:12px;color:var(--text-muted);">${t.qtd_vendas} venda(s) finalizada(s)</div>
    `;
    document.getElementById("obsFechamento").value = "";
    document.getElementById("modalFechar").style.display = "flex";
  }
  function fecharModalFechar(){ document.getElementById("modalFechar").style.display = "none"; }

  async function confirmarFecharCaixa(){
    if(sessaoAbertaId <= 0) return;
    const obs = document.getElementById("obsFechamento").value.trim();
    const res = await fetch("../api/caixa_fechar.php", { method:"POST", headers:{"Content-Type":"application/json"}, body: JSON.stringify({ sessao_id: sessaoAbertaId, obs }) });
    const json = await res.json();
    if(!json.ok){ alert(json.erro); return; }
    fecharModalFechar();
    await verificarCaixa();
    document.getElementById("iframeImpressao").src = "imprimir_fechamento.php?sessao_id=" + json.sessao_id;
  }

  // init
  verificarCaixa();
  render();
  carregarVendas();

  // Atualiza vendas a cada 30s automaticamente
  setInterval(carregarVendas, 30000);
</script>
</body>
</html>
