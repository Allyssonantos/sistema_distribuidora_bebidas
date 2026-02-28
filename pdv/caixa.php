<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>PDV - Caixa</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .linha { display: flex; gap: 10px; align-items: center; flex-wrap:wrap; }
    input { padding: 10px; width: 320px; }
    button { padding: 10px 14px; cursor: pointer; }
    button:disabled { opacity:.5; cursor:not-allowed; }
    table { width: 100%; border-collapse: collapse; margin-top: 15px; }
    th, td { border-bottom: 1px solid #ddd; padding: 10px; text-align: left; }
    .right { text-align: right; }
    .total { font-size: 24px; font-weight: bold; margin-top: 15px; }
    .sugestoes { border: 1px solid #ddd; max-width: 520px; }
    .sugestoes div { padding: 8px 10px; cursor: pointer; }
    .sugestoes div:hover { background: #f2f2f2; }

    .caixaBoxOk{
      background:#e7f7ed; padding:12px 14px; border-radius:8px; margin-bottom:15px;
      display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap;
    }
    .caixaBoxWarn{
      background:#fff3cd; padding:12px 14px; border-radius:8px; margin-bottom:15px;
      display:flex; align-items:center; justify-content:space-between; gap:10px; flex-wrap:wrap;
    }
  </style>
</head>
<body>

<h2>🧾 PDV - Caixa</h2>

<div id="boxCaixa" style="margin-bottom:15px;"></div>

<div class="linha">
  <input id="busca" placeholder="Digite nome ou código de barras..." autofocus />
  <button onclick="buscar()">Buscar</button>
</div>

<div id="sugestoes" class="sugestoes" style="display:none;"></div>

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

<!-- AÇÕES -->
<div style="margin-top:16px; display:flex; gap:10px; flex-wrap:wrap;">
  <button id="btnFinalizar" onclick="abrirFinalizar()" style="padding:12px 18px; font-weight:bold;">
    ✅ Finalizar Venda
  </button>
  <button onclick="limparCarrinho()" style="padding:12px 18px;">
    🧹 Limpar
  </button>
</div>

<!-- MODAL FINALIZAR -->
<div id="modal" style="
  display:none; position:fixed; inset:0; background:rgba(0,0,0,.35);
  align-items:center; justify-content:center; padding:20px;
">
  <div style="background:#fff; width:520px; max-width:100%; border-radius:10px; padding:16px;">
    <div style="display:flex; justify-content:space-between; align-items:center;">
      <h3 style="margin:0;">Finalizar venda</h3>
      <button onclick="fecharModal()">X</button>
    </div>

    <hr>

    <div style="margin:10px 0;">
      <label><b>Forma de pagamento</b></label><br>
      <select id="pagamento" onchange="atualizarPagamento()" style="padding:10px; width:100%; margin-top:6px;">
        <option value="PIX">PIX</option>
        <option value="DINHEIRO">Dinheiro</option>
        <option value="CARTAO_DEBITO">Cartão Débito</option>
        <option value="CARTAO_CREDITO">Cartão Crédito</option>
        <option value="OUTROS">Outros</option>
      </select>
    </div>

    <div id="blocoDinheiro" style="display:none; margin:10px 0;">
      <label><b>Valor recebido (Dinheiro)</b></label><br>
      <input id="recebido" type="number" step="0.01" min="0" oninput="calcularTroco()"
        style="padding:10px; width:100%; margin-top:6px;" placeholder="Ex: 50,00" />
      <div style="margin-top:8px;">
        <b>Troco:</b> R$ <span id="troco">0,00</span>
      </div>
    </div>

    <div style="margin-top:12px; font-size:18px;">
      <b>Total:</b> R$ <span id="totalModal">0,00</span>
    </div>

    <div style="display:flex; gap:10px; margin-top:14px; justify-content:flex-end;">
      <button onclick="fecharModal()" style="padding:10px 14px;">Cancelar</button>
      <button onclick="confirmarVenda()" style="padding:10px 14px; font-weight:bold;">
        Confirmar
      </button>
    </div>

  </div>
</div>

<script>
  // ========= CONFIG =========
  const CAIXA_ID = 1; // <- se tiver mais caixas, muda aqui
  let CAIXA_ABERTO = false;
  let CAIXA_SESSAO_ID = null;

  // ========= CARRINHO =========
  const carrinho = [];

  function fmt(v){ return Number(v||0).toFixed(2).replace(".", ","); }

  function setBotoesVenda(habilitar){
    const btnFinalizar = document.getElementById("btnFinalizar");
    btnFinalizar.disabled = !habilitar;
  }

  async function buscar(){
    const q = document.getElementById("busca").value.trim();
    if(!q) return;

    const res = await fetch("../api/produtos_buscar.php?q=" + encodeURIComponent(q));
    const lista = await res.json();

    const box = document.getElementById("sugestoes");
    box.innerHTML = "";

    if(lista.length === 0){
      box.style.display = "block";
      box.innerHTML = "<div>Nenhum produto encontrado</div>";
      return;
    }

    box.style.display = "block";
    lista.forEach(p => {
      const div = document.createElement("div");
      div.textContent = `${p.nome} — R$ ${fmt(parseFloat(p.preco_venda))} (Est: ${p.estoque_atual})`;
      div.onclick = () => adicionar(p);
      box.appendChild(div);
    });
  }

  function adicionar(p){
    document.getElementById("sugestoes").style.display = "none";
    document.getElementById("busca").value = "";
    document.getElementById("busca").focus();

    const id = parseInt(p.id);
    const ja = carrinho.find(i => i.id === id);
    if(ja){
      ja.qtd += 1;
    } else {
      carrinho.push({
        id,
        nome: p.nome,
        valor: parseFloat(p.preco_venda),
        qtd: 1
      });
    }
    render();
  }

  function remover(id){
    const idx = carrinho.findIndex(i => i.id === id);
    if(idx >= 0) carrinho.splice(idx, 1);
    render();
  }

  function alterarQtd(id, delta){
    const item = carrinho.find(i => i.id === id);
    if(!item) return;
    item.qtd += delta;
    if(item.qtd <= 0) remover(id);
    render();
  }

  function render(){
      const tbody = document.getElementById("itens");
      tbody.innerHTML = "";
      let total = 0;

      carrinho.forEach(i => {
        const sub = i.qtd * i.valor;
        total += sub;

        const tr = document.createElement("tr");
        tr.innerHTML = `
          <td>${i.nome}</td>
          <td class="right">
            <button onclick="alterarQtd(${i.id}, -1)">-</button>
            
            <input type="number" 
                  value="${i.qtd}" 
                  min="1" 
                  step="0.001" 
                  style="width: 60px; text-align: center; font-weight: bold; padding: 5px; margin: 0 5px;"
                  onchange="atualizarQtdDigitada(${i.id}, this.value)">
            
            <button onclick="alterarQtd(${i.id}, 1)">+</button>
          </td>
          <td class="right">R$ ${fmt(i.valor)}</td>
          <td class="right">R$ ${fmt(sub)}</td>
          <td class="right"><button onclick="remover(${i.id})">Remover</button></td>
        `;
        tbody.appendChild(tr);
      });

      document.getElementById("total").textContent = fmt(total);
  }

  // Enter para buscar
  document.getElementById("busca").addEventListener("keydown", (e) => {
    if(e.key === "Enter") buscar();
  });

  // ========= MODAL =========
  function abrirFinalizar(){
    if(!CAIXA_ABERTO){
      alert("Caixa está FECHADO. Abra o caixa para vender.");
      return;
    }
    if(carrinho.length === 0){
      alert("Carrinho vazio.");
      return;
    }
    document.getElementById("modal").style.display = "flex";
    document.getElementById("totalModal").textContent = document.getElementById("total").textContent;

    // reset
    document.getElementById("pagamento").value = "PIX";
    document.getElementById("recebido").value = "";
    document.getElementById("troco").textContent = "0,00";
    atualizarPagamento();
  }

  function fecharModal(){
    document.getElementById("modal").style.display = "none";
  }

  function atualizarPagamento(){
    const forma = document.getElementById("pagamento").value;
    const bloco = document.getElementById("blocoDinheiro");
    bloco.style.display = (forma === "DINHEIRO") ? "block" : "none";
    if(forma !== "DINHEIRO"){
      document.getElementById("troco").textContent = "0,00";
    } else {
      document.getElementById("recebido").focus();
      calcularTroco();
    }
  }

  function calcularTroco(){
    const total = parseFloat(document.getElementById("total").textContent.replace(",", ".") || "0");
    const recebido = parseFloat((document.getElementById("recebido").value || "0"));
    const troco = Math.max(0, recebido - total);
    document.getElementById("troco").textContent = fmt(troco);
  }

  function limparCarrinho(){
    carrinho.splice(0, carrinho.length);
    render();
  }

  async function confirmarVenda(){
    if(!CAIXA_ABERTO){
      alert("Caixa está FECHADO. Abra o caixa para vender.");
      return;
    }

    const forma = document.getElementById("pagamento").value;
    const total = parseFloat(document.getElementById("total").textContent.replace(",", ".") || "0");

    let recebido = total;
    let troco = 0;

    if(forma === "DINHEIRO"){
      recebido = parseFloat((document.getElementById("recebido").value || "0"));
      if(recebido < total){
        alert("Valor recebido não pode ser menor que o total.");
        return;
      }
      troco = recebido - total;
    }

    const payload = {
      caixa_id: CAIXA_ID, // ✅ importante
      forma_pagamento: forma,
      valor_recebido: recebido,
      troco: troco,
      itens: carrinho.map(i => ({ id: i.id, qtd: i.qtd }))
    };

    try {
      const res = await fetch("../api/vendas_salvar.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload)
      });

      const json = await res.json();

      if(!json.ok){
        alert("Erro ao salvar venda: " + (json.erro || "desconhecido"));
        return;
      }

      fecharModal();
      limparCarrinho();

      window.open("imprimir.php?id=" + json.venda_id, "_blank");

    } catch (err) {
      alert("Falha na comunicação com o servidor.");
      console.error(err);
    }
  }

  // ========= CAIXA (ABRIR/STATUS) =========
  async function verificarCaixa(){
    const box = document.getElementById("boxCaixa");

    try{
      const res = await fetch(`../api/caixa_status.php?caixa_id=${CAIXA_ID}`);
      const json = await res.json();

      if(!json.ok){
        CAIXA_ABERTO = false;
        CAIXA_SESSAO_ID = null;
        setBotoesVenda(false);
        box.innerHTML = `<div class="caixaBoxWarn">⚠️ Erro ao verificar caixa: ${json.erro || "desconhecido"}</div>`;
        return;
      }

      if(json.aberto){
        CAIXA_ABERTO = true;
        CAIXA_SESSAO_ID = json.sessao_id || null;
        setBotoesVenda(true);

        box.innerHTML = `
            <div style="background:#e7f7ed;padding:15px;border-radius:8px;display:flex;justify-content:space-between;align-items:center;">
              <div>🟢 Caixa ABERTO</div>
              <button onclick="abrirModalFechar()">Fechar caixa</button>
            </div>
        `;
      }else{
        CAIXA_ABERTO = false;
        CAIXA_SESSAO_ID = null;
        setBotoesVenda(false);

        box.innerHTML = `
          <div class="caixaBoxWarn">
            <div>⚠️ Caixa FECHADO (Caixa: ${CAIXA_ID})</div>
            <button onclick="abrirCaixa()">Abrir caixa</button>
          </div>
        `;
      }
    }catch(e){
      console.error(e);
      CAIXA_ABERTO = false;
      CAIXA_SESSAO_ID = null;
      setBotoesVenda(false);
      box.innerHTML = `<div class="caixaBoxWarn">⚠️ Falha ao verificar caixa.</div>`;
    }
  }

async function abrirCaixa() {
    const operador = prompt("Digite seu nome (Operador):");
    if (!operador) {
        alert("O nome do operador é obrigatório para abrir o caixa.");
        return;
    }

    const valor = prompt("Valor inicial do caixa (troco):", "0");
    if (valor === null) return;

    try {
        const res = await fetch("../api/caixa_abrir.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                caixa_id: CAIXA_ID,
                valor_inicial: valor,
                aberto_por: operador 
            })
        });

        const json = await res.json();

        if (json.ok) {
            await verificarCaixa();
            alert("Caixa aberto com sucesso!");
        } else {
            alert("Erro: " + (json.erro || "desconhecido"));
        }
    } catch (err) {
        console.error(err);
        alert("Falha na comunicação com o servidor.");
    }
}
  
// Garanta que a função verificarCaixa atualize o estado global
async function verificarCaixa(){
    const box = document.getElementById("boxCaixa");
    try {
        const res = await fetch(`../api/caixa_status.php?caixa_id=${CAIXA_ID}`);
        const json = await res.json();

        if(json.ok && json.aberto){
            CAIXA_ABERTO = true;
            CAIXA_SESSAO_ID = json.sessao_id;
            setBotoesVenda(true); // Habilita botões de venda

            box.innerHTML = `
                <div style="background:#e7f7ed; padding:15px; border-radius:8px; display:flex; justify-content:space-between; align-items:center; border: 1px solid #c3e6cb;">
                  <div><strong style="color: #155724;">🟢 Caixa ABERTO</strong> (Sessão #${json.sessao_id})</div>
                  <button onclick="abrirModalFechar()" style="background:#28a745; color:white; border:none; padding:8px 12px; border-radius:4px;">Fechar caixa</button>
                </div>
            `;
        } else {
            CAIXA_ABERTO = false;
            CAIXA_SESSAO_ID = null;
            setBotoesVenda(false); // Desabilita botões de venda

            box.innerHTML = `
              <div class="caixaBoxWarn" style="background:#fff3cd; padding:15px; border-radius:8px; display:flex; justify-content:space-between; align-items:center; border: 1px solid #ffeeba;">
                <div style="color: #856404;">⚠️ Caixa FECHADO</div>
                <button onclick="abrirCaixa()" style="background:#ffc107; border:none; padding:8px 12px; border-radius:4px; cursor:pointer;">Abrir caixa agora</button>
              </div>
            `;
        }
    } catch(e) {
        box.innerHTML = `<div class="caixaBoxWarn">⚠️ Erro de conexão com o servidor.</div>`;
    }
}

  async function fecharCaixa(){
    if(!confirm("Deseja realmente FECHAR o caixa?"))
      return;

    const res = await fetch("../api/caixa_fechar.php",{
      method:"POST",
      headers:{"Content-Type":"application/json"},
      body: JSON.stringify({ caixa_id: CAIXA_ID })
    });

    const json = await res.json();

    if(!json.ok){
      alert("Erro: " + json.erro);
      return;
    }

    alert(
      "Caixa fechado!\n\n" +
      "Total Geral: R$ " + fmt(json.totais.geral || 0) + "\n" +
      "Vendas: " + (json.totais.vendas || 0)
    );

    verificarCaixa(); // atualiza tela
}

  // init
  verificarCaixa();

  let sessaoAbertaId = 0;

  async function abrirModalFechar(){
  // pega resumo do caixa aberto
  const res = await fetch("../api/caixa_resumo.php");
  const json = await res.json();

  if(!json.ok){
    alert(json.erro || "Erro ao carregar resumo.");
    return;
  }
  if(!json.aberto){
    alert("Caixa já está fechado.");
    verificarCaixa();
    return;
  }

  sessaoAbertaId = json.sessao.id;

  const t = json.totais;
  const s = json.sessao;

  const brl = (v)=> "R$ " + Number(v||0).toFixed(2).replace(".", ",");

  document.getElementById("resumoBox").innerHTML = `
    <div><b>Sessão:</b> #${s.id}</div>
    <div><b>Abertura:</b> ${s.aberto_em}</div>
    <div style="margin-top:8px;"><b>Troco inicial:</b> ${brl(s.troco_inicial)}</div>

    <hr>

    <div><b>Dinheiro:</b> ${brl(t.dinheiro)}</div>
    <div><b>PIX:</b> ${brl(t.pix)}</div>
    <div><b>Cartão Débito:</b> ${brl(t.cartao_debito)}</div>
    <div><b>Cartão Crédito:</b> ${brl(t.cartao_credito)}</div>
    <div><b>Outros:</b> ${brl(t.outros)}</div>

    <hr>

    <div style="font-size:16px;"><b>Total Geral:</b> ${brl(t.total_geral)}</div>
    <div><b>Vendas:</b> ${t.qtd_vendas}</div>
  `;

  document.getElementById("obsFechamento").value = "";
  document.getElementById("modalFechar").style.display = "flex";
  }

  function fecharModalFechar(){
  document.getElementById("modalFechar").style.display = "none";
  }

  async function confirmarFecharCaixa(){
  if(sessaoAbertaId <= 0){
    alert("Sessão inválida.");
    return;
  }

  const obs = document.getElementById("obsFechamento").value.trim();

  const res = await fetch("../api/caixa_fechar.php", {
    method:"POST",
    headers:{"Content-Type":"application/json"},
    body: JSON.stringify({ sessao_id: sessaoAbertaId, obs })
  });

  const json = await res.json();
  if(!json.ok){
    alert(json.erro || "Erro ao fechar caixa.");
    return;
  }

  fecharModalFechar();

  // atualiza status na tela (sem F5)
  await verificarCaixa();

  // abre impressão automática do resumo
  window.open("imprimir_fechamento.php?sessao_id=" + json.sessao_id, "_blank");
  }
</script>

<div id="modalFechar" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.35); align-items:center; justify-content:center; padding:20px;">
  <div style="background:#fff; width:520px; max-width:100%; border-radius:10px; padding:16px;">
    <div style="display:flex; justify-content:space-between; align-items:center;">
      <h3 style="margin:0;">Resumo do caixa</h3>
      <button onclick="fecharModalFechar()">X</button>
    </div>
    <hr>

    <div id="resumoBox" style="font-size:14px;"></div>

    <div style="margin-top:10px;">
      <label><b>Observação (opcional)</b></label>
      <input id="obsFechamento" style="width:100%; padding:10px; margin-top:6px;" placeholder="Ex: conferido com maquininha...">
    </div>

    <div style="display:flex; gap:10px; margin-top:14px; justify-content:flex-end;">
      <button onclick="fecharModalFechar()">Cancelar</button>
      <button onclick="confirmarFecharCaixa()" style="font-weight:bold;">Confirmar e imprimir</button>
    </div>
  </div>
</div>

<iframe id="iframeImpressao" style="display:none;"></iframe>

<script>
  // Modifique a função confirmarVenda para o seguinte:
  async function confirmarVenda() {
    if(!CAIXA_ABERTO) { alert("Caixa FECHADO."); return; }
    
    const forma = document.getElementById("pagamento").value;
    const total = parseFloat(document.getElementById("total").textContent.replace(",", "."));
    let recebido = total;
    let troco = 0;

    if(forma === "DINHEIRO") {
      recebido = parseFloat(document.getElementById("recebido").value || "0");
      if(recebido < total) { alert("Valor insuficiente."); return; }
      troco = recebido - total;
    }

    const payload = {
      caixa_id: CAIXA_ID,
      forma_pagamento: forma,
      valor_recebido: recebido,
      troco: troco,
      itens: carrinho.map(i => ({ id: i.id, qtd: i.qtd }))
    };

    try {
      const res = await fetch("../api/vendas_salvar.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(payload)
      });
      const json = await res.json();

      if(!json.ok) { alert("Erro: " + json.erro); return; }

      fecharModal();
      limparCarrinho();

      // EM VEZ DE window.open, carregamos no iframe oculto
      document.getElementById("iframeImpressao").src = "imprimir.php?id=" + json.venda_id;

    } catch (err) {
      alert("Erro de comunicação.");
    }
  }

  // Modifique a função confirmarFecharCaixa para o seguinte:
  async function confirmarFecharCaixa() {
    if(sessaoAbertaId <= 0) return;
    const obs = document.getElementById("obsFechamento").value.trim();

    const res = await fetch("../api/caixa_fechar.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ sessao_id: sessaoAbertaId, obs })
    });

    const json = await res.json();
    if(!json.ok) { alert(json.erro); return; }

    fecharModalFechar();
    await verificarCaixa();

    // CARREGA O RESUMO NO IFRAME OCULTO
    document.getElementById("iframeImpressao").src = "imprimir_fechamento.php?sessao_id=" + json.sessao_id;
  }

  function atualizarQtdDigitada(id, novoValor) {
    const item = carrinho.find(i => i.id === id);
    if (!item) return;

    const qtd = parseFloat(novoValor);

    // Valida se é um número válido e maior que zero
    if (isNaN(qtd) || qtd <= 0) {
        alert("Quantidade inválida. Insira um valor maior que zero.");
        render(); // Recarrega a tabela para voltar ao valor anterior
        return;
    }

    item.qtd = qtd;
    render(); // Atualiza os totais e a tela
  }
</script>
</body>
</html>