<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>PDV - Caixa</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .linha { display: flex; gap: 10px; align-items: center; }
    input { padding: 10px; width: 320px; }
    button { padding: 10px 14px; cursor: pointer; }
    table { width: 100%; border-collapse: collapse; margin-top: 15px; }
    th, td { border-bottom: 1px solid #ddd; padding: 10px; text-align: left; }
    .right { text-align: right; }
    .total { font-size: 24px; font-weight: bold; margin-top: 15px; }
    .sugestoes { border: 1px solid #ddd; max-width: 520px; }
    .sugestoes div { padding: 8px 10px; cursor: pointer; }
    .sugestoes div:hover { background: #f2f2f2; }
  </style>
</head>
<body>

<h2>🧾 PDV - Caixa</h2>

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

<script>
  const carrinho = [];

  function fmt(v){ return v.toFixed(2).replace(".", ","); }

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
          <b style="margin:0 8px">${i.qtd}</b>
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
</script>

<!-- AÇÕES -->
<div style="margin-top:16px; display:flex; gap:10px;">
  <button onclick="abrirFinalizar()" style="padding:12px 18px; font-weight:bold;">
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
  function abrirFinalizar(){
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

    alert("Venda salva! ID: " + json.venda_id);

    fecharModal();
    limparCarrinho();

    window.open("imprimir.php?id=" + json.venda_id, "_blank");

  } catch (err) {
    alert("Falha na comunicação com o servidor.");
    console.error(err);
  }
}
</script>

</body>
</html>