<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>PDV - Caixa</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f8f9fa; }
    .linha { display: flex; gap: 10px; align-items: center; flex-wrap:wrap; }
    input { padding: 12px; width: 350px; border: 1px solid #ccc; border-radius: 4px; }
    button { padding: 10px 16px; cursor: pointer; border-radius: 4px; border: 1px solid #ccc; }
    button:disabled { opacity:.5; cursor:not-allowed; }
    table { width: 100%; border-collapse: collapse; margin-top: 15px; background: white; }
    th, td { border-bottom: 1px solid #ddd; padding: 12px; text-align: left; }
    .right { text-align: right; }
    .total { font-size: 28px; font-weight: bold; margin-top: 15px; color: #28a745; }
    .sugestoes { border: 1px solid #ddd; max-width: 520px; background: white; position: absolute; z-index: 100; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
    .sugestoes div { padding: 10px; cursor: pointer; border-bottom: 1px solid #eee; }
    .sugestoes div:hover { background: #f2f2f2; }
    .caixaBoxOk { background:#e7f7ed; padding:15px; border-radius:8px; margin-bottom:15px; display:flex; align-items:center; justify-content:space-between; border: 1px solid #c3e6cb; }
    .caixaBoxWarn { background:#fff3cd; padding:15px; border-radius:8px; margin-bottom:15px; display:flex; align-items:center; justify-content:space-between; border: 1px solid #ffeeba; }
    .modal { display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); align-items:center; justify-content:center; padding:20px; z-index: 1000; }
    .modal-content { background:#fff; width:520px; max-width:100%; border-radius:10px; padding:20px; box-shadow: 0 5px 15px rgba(0,0,0,0.3); }
  </style>
</head>
<body>

<h2>🧾 PDV - Caixa</h2>

<div id="boxCaixa"></div>

<div class="linha" style="position: relative;">
  <input id="busca" placeholder="Digite nome ou código de barras..." autofocus autocomplete="off" />
  <button onclick="buscar()" style="background: #007bff; color: white; border: none;">Buscar</button>
  <div id="sugestoes" class="sugestoes" style="display:none;"></div>
</div>

<table>
  <thead>
    <tr>
      <th>Produto</th>
      <th class="right">Qtd</th>
      <th class="right">V. Unit</th>
      <th class="right">Subtotal</th>
      <th></th>
    </tr>
  </thead>
  <tbody id="itens"></tbody>
</table>

<div class="total">Total: R$ <span id="total">0,00</span></div>

<div style="margin-top:20px; display:flex; gap:12px;">
  <button id="btnFinalizar" onclick="abrirFinalizar()" style="padding:15px 25px; font-weight:bold; background:#28a745; color:white; border:none;">
    ✅ Finalizar Venda
  </button>
  <button onclick="limparCarrinho()" style="padding:15px 25px; background:#6c757d; color:white; border:none;">
    🧹 Limpar Carrinho
  </button>
</div>

<div id="modal" class="modal">
  <div class="modal-content">
    <div style="display:flex; justify-content:space-between; align-items:center;">
      <h3 style="margin:0;">Finalizar Venda</h3>
      <button onclick="fecharModal()" style="border:none; background:none; font-size:20px;">&times;</button>
    </div>
    <hr>
    <div style="margin:15px 0;">
      <label><b>Forma de Pagamento</b></label>
      <select id="pagamento" onchange="atualizarPagamento()" style="padding:12px; width:100%; margin-top:8px;">
        <option value="PIX">PIX</option>
        <option value="DINHEIRO">Dinheiro</option>
        <option value="CARTAO_DEBITO">Cartão Débito</option>
        <option value="CARTAO_CREDITO">Cartão Crédito</option>
        <option value="OUTROS">Outros</option>
      </select>
    </div>
    <div id="blocoDinheiro" style="display:none; margin:15px 0;">
      <label><b>Valor Recebido</b></label>
      <input id="recebido" type="number" step="0.01" oninput="calcularTroco()" style="padding:12px; width:100%; margin-top:8px;" placeholder="0,00">
      <div style="margin-top:10px; font-size:18px;"><b>Troco:</b> <span id="troco" style="color:red;">R$ 0,00</span></div>
    </div>
    <div style="font-size:22px; margin-top:15px;"><b>Total a Pagar:</b> <span id="totalModal">R$ 0,00</span></div>
    <div style="display:flex; gap:10px; margin-top:20px; justify-content:flex-end;">
      <button onclick="fecharModal()" style="padding:12px 20px;">Cancelar</button>
      <button onclick="confirmarVenda()" style="padding:12px 20px; font-weight:bold; background:#28a745; color:white; border:none;">Confirmar Venda</button>
    </div>
  </div>
</div>

<div id="modalFechar" class="modal">
  <div class="modal-content">
    <div style="display:flex; justify-content:space-between; align-items:center;">
      <h3 style="margin:0;">Resumo de Fechamento</h3>
      <button onclick="fecharModalFechar()" style="border:none; background:none; font-size:20px;">&times;</button>
    </div>
    <hr>
    <div id="resumoBox" style="line-height:1.6;"></div>
    <div style="margin-top:15px;">
      <label><b>Observação de Fechamento</b></label>
      <input id="obsFechamento" style="width:100%; padding:12px; margin-top:8px;" placeholder="Opcional...">
    </div>
    <div style="display:flex; gap:10px; margin-top:20px; justify-content:flex-end;">
      <button onclick="fecharModalFechar()" style="padding:12px 20px;">Voltar</button>
      <button onclick="confirmarFecharCaixa()" style="padding:12px 20px; font-weight:bold; background:#dc3545; color:white; border:none;">Confirmar Fechamento</button>
    </div>
  </div>
</div>

<iframe id="iframeImpressao" style="display:none;"></iframe>

<script>
  // CONFIGURAÇÕES GERAIS
  const CAIXA_ID = 1;
  let CAIXA_ABERTO = false;
  let sessaoAbertaId = 0;
  const carrinho = [];

  const fmt = (v) => Number(v||0).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

  // 1. STATUS DO CAIXA
  async function verificarCaixa() {
    try {
      const res = await fetch("../api/caixa_resumo.php");
      const json = await res.json();
      const box = document.getElementById("boxCaixa");
      const btnVenda = document.getElementById("btnFinalizar");

      if (json.ok && json.aberto) {
        CAIXA_ABERTO = true;
        sessaoAbertaId = json.sessao.id;
        btnVenda.disabled = false;
        box.innerHTML = `
          <div class="caixaBoxOk">
            <div>🟢 <b>Caixa ABERTO</b> (Sessão #${sessaoAbertaId}) - Operador: <b>${json.sessao.aberto_por}</b></div>
            <button onclick="abrirModalFechar()" style="background:#dc3545; color:white; border:none;">Fechar Caixa</button>
          </div>`;
      } else {
        CAIXA_ABERTO = false;
        btnVenda.disabled = true;
        box.innerHTML = `
          <div class="caixaBoxWarn">
            <div>⚠️ <b>Caixa FECHADO</b></div>
            <button onclick="abrirCaixa()" style="background:#ffc107; border:none; font-weight:bold;">Abrir Caixa Agora</button>
          </div>`;
      }
    } catch (e) { console.error(e); }
  }

  // 2. ABRIR CAIXA
  async function abrirCaixa() {
    const operador = prompt("Nome do Operador:");
    if (!operador) return;
    const valor = prompt("Valor inicial (troco):", "0");
    if (valor === null) return;

    const res = await fetch("../api/caixa_abrir.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ caixa_id: CAIXA_ID, valor_inicial: valor, aberto_por: operador })
    });
    const json = await res.json();
    if (json.ok) { verificarCaixa(); } else { alert(json.erro); }
  }

  // 3. BUSCA DE PRODUTOS
  async function buscar() {
    const q = document.getElementById("busca").value.trim();
    if (!q) return;
    const res = await fetch("../api/produtos_buscar.php?q=" + encodeURIComponent(q));
    const lista = await res.json();
    const box = document.getElementById("sugestoes");
    box.innerHTML = "";
    box.style.display = "block";
    if (lista.length === 0) { box.innerHTML = "<div>Não encontrado</div>"; return; }
    lista.forEach(p => {
      const div = document.createElement("div");
      div.textContent = `${p.nome} - R$ ${fmt(p.preco_venda)}`;
      div.onclick = () => {
        const id = parseInt(p.id);
        const ja = carrinho.find(i => i.id === id);
        if (ja) ja.qtd++; else carrinho.push({ id, nome: p.nome, valor: parseFloat(p.preco_venda), qtd: 1 });
        box.style.display = "none";
        document.getElementById("busca").value = "";
        render();
      };
      box.appendChild(div);
    });
  }

  function render() {
    const tbody = document.getElementById("itens");
    tbody.innerHTML = "";
    let total = 0;
    carrinho.forEach(i => {
      const sub = i.qtd * i.valor;
      total += sub;
      tbody.innerHTML += `<tr>
        <td>${i.nome}</td>
        <td class="right">
          <button onclick="alterarQtd(${i.id},-1)">-</button> 
          <b style="margin:0 10px">${i.qtd}</b>
          <button onclick="alterarQtd(${i.id},1)">+</button>
        </td>
        <td class="right">R$ ${fmt(i.valor)}</td>
        <td class="right">R$ ${fmt(sub)}</td>
        <td class="right"><button onclick="remover(${i.id})">❌</button></td>
      </tr>`;
    });
    document.getElementById("total").textContent = fmt(total);
  }

  function alterarQtd(id, delta) { 
    const i = carrinho.find(x => x.id === id); 
    if (i) { i.qtd += delta; if (i.qtd <= 0) remover(id); render(); }
  }

  function remover(id) { 
    const idx = carrinho.findIndex(x => x.id === id); 
    if (idx >= 0) { carrinho.splice(idx, 1); render(); }
  }

  function limparCarrinho() { carrinho.length = 0; render(); }

  // 4. FINALIZAR VENDA
  function abrirFinalizar() {
    if (carrinho.length === 0) return alert("Carrinho vazio!");
    document.getElementById("modal").style.display = "flex";
    document.getElementById("totalModal").textContent = "R$ " + document.getElementById("total").textContent;
    atualizarPagamento();
  }

  function fecharModal() { document.getElementById("modal").style.display = "none"; }

  function atualizarPagamento() {
    const f = document.getElementById("pagamento").value;
    document.getElementById("blocoDinheiro").style.display = (f === "DINHEIRO" ? "block" : "none");
    calcularTroco();
  }

  function calcularTroco() {
    const t = parseFloat(document.getElementById("total").textContent.replace(".","").replace(",","."));
    const r = parseFloat(document.getElementById("recebido").value || 0);
    document.getElementById("troco").textContent = "R$ " + fmt(Math.max(0, r - t));
  }

  async function confirmarVenda() {
    const total = parseFloat(document.getElementById("total").textContent.replace(".","").replace(",","."));
    const forma = document.getElementById("pagamento").value;
    const recebido = forma === "DINHEIRO" ? parseFloat(document.getElementById("recebido").value || 0) : total;
    if (recebido < total) return alert("Valor insuficiente!");

    const res = await fetch("../api/vendas_salvar.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ caixa_id: CAIXA_ID, forma_pagamento: forma, valor_recebido: recebido, troco: recebido - total, itens: carrinho.map(i => ({ id: i.id, qtd: i.qtd })) })
    });
    const json = await res.json();
    if (json.ok) {
      fecharModal();
      limparCarrinho();
      document.getElementById("iframeImpressao").src = "imprimir.php?id=" + json.venda_id;
      alert("Venda realizada!");
    }
  }

  // 5. FECHAMENTO DE CAIXA
  async function abrirModalFechar() {
    const res = await fetch("../api/caixa_resumo.php");
    const json = await res.json();
    if (!json.ok) return;
    const t = json.totais;
    document.getElementById("resumoBox").innerHTML = `
      <b>Sessão #${json.sessao.id}</b> | Operador: ${json.sessao.aberto_por}<br>
      Abertura: ${json.sessao.aberto_em}<hr>
      💵 Dinheiro: R$ ${fmt(t.dinheiro)}<br>
      📱 PIX: R$ ${fmt(t.pix)}<br>
      💳 Cartão: R$ ${fmt(t.cartao_debito + t.cartao_credito)}<hr>
      <b>Total Geral: R$ ${fmt(t.total_geral)}</b><br>
      Qtd Vendas: ${t.qtd_vendas}`;
    document.getElementById("modalFechar").style.display = "flex";
  }

  function fecharModalFechar() { document.getElementById("modalFechar").style.display = "none"; }

  async function confirmarFecharCaixa() {
    const obs = document.getElementById("obsFechamento").value;
    const res = await fetch("../api/caixa_fechar.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ sessao_id: sessaoAbertaId, obs })
    });
    const json = await res.json();
    if (json.ok) {
      fecharModalFechar();
      verificarCaixa();
      document.getElementById("iframeImpressao").src = "imprimir_fechamento.php?sessao_id=" + json.sessao_id;
    }
  }

  // Enter no campo de busca
  document.getElementById("busca").addEventListener("keypress", (e) => { if (e.key === "Enter") buscar(); });

  verificarCaixa();
</script>
</body>
</html>