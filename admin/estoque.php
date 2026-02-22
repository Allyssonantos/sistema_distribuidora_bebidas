<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin - Estoque</title>
  <style>
    body{font-family:Arial; margin:20px;}
    input, select, textarea{padding:10px; width:100%; box-sizing:border-box;}
    button{padding:10px 14px; cursor:pointer;}
    .row{display:grid; grid-template-columns: 1fr 1fr; gap:10px;}
    @media(max-width:900px){ .row{grid-template-columns: 1fr;} }
    .card{border:1px solid #eee; border-radius:12px; padding:12px; margin-top:12px;}
    .sug{border:1px solid #ddd; border-radius:10px; overflow:hidden; margin-top:8px;}
    .sug div{padding:10px; cursor:pointer;}
    .sug div:hover{background:#f2f2f2;}
    .badge{padding:4px 8px; border-radius:999px; font-size:12px; display:inline-block;}
    .low{background:#ffe8e8;}
    .ok{background:#e7f7ed;}
  </style>
</head>
<body>

<h2>📥 Admin - Movimentar Estoque</h2>
<div style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
  <a href="produtos.php">📦 Admin - Produtos</a>
  <a href="relatorios.php">📊 Relatório de Vendas</a>
  <a href="estoque_relatorios.php">📦 Relatório de Estoque</a>
  <a href="lucro.php">📊 Admin - Lucro</a>
</div>

<div class="card">
  <label><b>Buscar produto (nome ou código de barras)</b></label>
  <input id="busca" placeholder="Digite e pressione Enter..." />
  <div id="sugestoes" class="sug" style="display:none;"></div>
</div>

<div id="produtoCard" class="card" style="display:none;">
  <div style="display:flex; justify-content:space-between; gap:10px; flex-wrap:wrap;">
    <div>
      <div><b id="p_nome"></b></div>
      <div style="font-size:13px; color:#555">
        Código: <span id="p_cb"></span> • Unidade: <span id="p_un"></span>
      </div>
    </div>
    <div>
      <span id="p_status" class="badge ok">OK</span>
    </div>
  </div>

  <div class="row" style="margin-top:10px;">
    <div><b>Estoque atual:</b> <span id="p_est"></span></div>
    <div><b>Estoque mínimo:</b> <span id="p_min"></span></div>
    <div><b>Custo:</b> R$ <span id="p_custo"></span></div>
    <div><b>Venda:</b> R$ <span id="p_venda"></span></div>
  </div>
</div>

<div id="movCard" class="card" style="display:none;">
  <div class="row">
    <div>
      <label><b>Tipo</b></label>
      <select id="tipo" onchange="renderTipo()">
        <option value="ENTRADA">ENTRADA (compra)</option>
        <option value="AJUSTE">AJUSTE (inventário)</option>
        <option value="PERDA">PERDA (quebra/vencimento)</option>
      </select>
    </div>

    <div id="campoValorUnit">
      <label><b>Valor unitário (opcional)</b></label>
      <input id="valor_unit" type="number" step="0.01" placeholder="Ex: 5.50">
    </div>
  </div>

  <div id="ajusteModo" style="margin-top:10px; display:none;">
    <label><b>Modo do ajuste</b></label>
    <select id="modo_ajuste" onchange="renderTipo()">
      <option value="FINAL">Definir estoque final</option>
      <option value="DELTA">Somar/Subtrair (delta)</option>
    </select>
  </div>

  <div class="row" style="margin-top:10px;">
    <div id="campoQtd">
      <label><b>Quantidade</b></label>
      <input id="quantidade" type="number" step="0.001" placeholder="Ex: 10">
      <div style="font-size:12px; color:#666; margin-top:6px;" id="hintQtd"></div>
    </div>

    <div id="campoFinal" style="display:none;">
      <label><b>Estoque final (contado)</b></label>
      <input id="estoque_final" type="number" step="0.001" placeholder="Ex: 47">
      <div style="font-size:12px; color:#666; margin-top:6px;">
        O sistema calcula a diferença automaticamente.
      </div>
    </div>
  </div>

  <div style="margin-top:10px;">
    <label><b>Observação / Motivo</b></label>
    <textarea id="obs" rows="2" placeholder="Ex: compra do fornecedor X / perda por vencimento / inventário do dia"></textarea>
  </div>

  <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:12px;">
    <button onclick="limpar()">Limpar</button>
    <button onclick="confirmar()" style="font-weight:bold;">✅ Confirmar movimentação</button>
  </div>
</div>

<script>
  let produtoSel = null;

  function brl(v){ return Number(v||0).toFixed(2).replace(".", ","); }

  async function buscar(){
    const q = document.getElementById("busca").value.trim();
    if(!q) return;

    const res = await fetch("../api/produtos_buscar.php?q=" + encodeURIComponent(q));
    const lista = await res.json();

    const box = document.getElementById("sugestoes");
    box.innerHTML = "";
    box.style.display = "block";

    if(lista.length === 0){
      box.innerHTML = "<div>Nenhum produto encontrado</div>";
      return;
    }

    lista.forEach(p => {
      const div = document.createElement("div");
      div.textContent = `${p.nome} — R$ ${brl(p.preco_venda)} (Est: ${p.estoque_atual})`;
      div.onclick = () => selecionarProduto(p.id);
      box.appendChild(div);
    });
  }

  async function selecionarProduto(id){
    const res = await fetch("../api/produto_get.php?id=" + id);
    const json = await res.json();

    if(!json.ok){
      alert("Erro: " + (json.erro || "desconhecido"));
      return;
    }

    produtoSel = json.produto;

    document.getElementById("sugestoes").style.display = "none";
    document.getElementById("busca").value = "";

    document.getElementById("produtoCard").style.display = "block";
    document.getElementById("movCard").style.display = "block";

    document.getElementById("p_nome").textContent = produtoSel.nome;
    document.getElementById("p_cb").textContent = produtoSel.codigo_barras || "-";
    document.getElementById("p_un").textContent = produtoSel.unidade;
    document.getElementById("p_est").textContent = produtoSel.estoque_atual;
    document.getElementById("p_min").textContent = produtoSel.estoque_minimo;
    document.getElementById("p_custo").textContent = brl(produtoSel.preco_custo);
    document.getElementById("p_venda").textContent = brl(produtoSel.preco_venda);

    const acabando = Number(produtoSel.estoque_atual) <= Number(produtoSel.estoque_minimo);
    const status = document.getElementById("p_status");
    status.textContent = acabando ? "ACABANDO" : "OK";
    status.className = "badge " + (acabando ? "low" : "ok");

    // defaults
    document.getElementById("tipo").value = "ENTRADA";
    document.getElementById("modo_ajuste").value = "FINAL";
    document.getElementById("quantidade").value = "";
    document.getElementById("estoque_final").value = "";
    document.getElementById("valor_unit").value = "";
    document.getElementById("obs").value = "";
    renderTipo();
  }

  function renderTipo(){
    const tipo = document.getElementById("tipo").value;

    const ajusteModo = document.getElementById("ajusteModo");
    const campoFinal = document.getElementById("campoFinal");
    const campoQtd = document.getElementById("campoQtd");
    const campoValorUnit = document.getElementById("campoValorUnit");
    const hint = document.getElementById("hintQtd");

    if(tipo === "ENTRADA"){
      ajusteModo.style.display = "none";
      campoFinal.style.display = "none";
      campoQtd.style.display = "block";
      campoValorUnit.style.display = "block";
      hint.textContent = "Entrada soma no estoque (ex: 20).";
      document.getElementById("quantidade").placeholder = "Ex: 20";
    }

    if(tipo === "PERDA"){
      ajusteModo.style.display = "none";
      campoFinal.style.display = "none";
      campoQtd.style.display = "block";
      campoValorUnit.style.display = "none";
      hint.textContent = "Perda reduz do estoque (ex: 3).";
      document.getElementById("quantidade").placeholder = "Ex: 3";
    }

    if(tipo === "AJUSTE"){
      ajusteModo.style.display = "block";
      campoValorUnit.style.display = "none";

      const modo = document.getElementById("modo_ajuste").value;
      if(modo === "FINAL"){
        campoFinal.style.display = "block";
        campoQtd.style.display = "none";
      } else {
        campoFinal.style.display = "none";
        campoQtd.style.display = "block";
        hint.textContent = "Ajuste por diferença: pode ser + ou - (ex: -2 ou +5).";
        document.getElementById("quantidade").placeholder = "Ex: -2";
      }
    }
  }

  function limpar(){
    produtoSel = null;
    document.getElementById("produtoCard").style.display = "none";
    document.getElementById("movCard").style.display = "none";
    document.getElementById("sugestoes").style.display = "none";
    document.getElementById("busca").value = "";
  }

  async function confirmar(){
    if(!produtoSel){
      alert("Selecione um produto.");
      return;
    }

    const tipo = document.getElementById("tipo").value;
    const modo = document.getElementById("modo_ajuste").value;
    const obs  = document.getElementById("obs").value.trim();
    const valorUnit = document.getElementById("valor_unit").value;

    const payload = {
      produto_id: produtoSel.id,
      tipo: tipo,
      modo_ajuste: modo,
      observacao: obs,
      valor_unit: valorUnit
    };

    if(tipo === "AJUSTE" && modo === "FINAL"){
      payload.estoque_final = document.getElementById("estoque_final").value;
    } else {
      payload.quantidade = document.getElementById("quantidade").value;
    }

    const res = await fetch("../api/estoque_movimentar.php", {
      method:"POST",
      headers:{"Content-Type":"application/json"},
      body: JSON.stringify(payload)
    });

    const json = await res.json();
    if(!json.ok){
      alert("Erro: " + (json.erro || "desconhecido"));
      return;
    }

    alert(
      "Movimentação OK!\n" +
      "Produto: " + json.produto + "\n" +
      "Antes: " + json.estoque_antes + "\n" +
      "Delta: " + json.delta + "\n" +
      "Depois: " + json.estoque_depois
    );

    // recarrega dados do produto pra atualizar estoque na tela
    await selecionarProduto(produtoSel.id);
  }

  document.getElementById("busca").addEventListener("keydown", (e) => {
    if(e.key === "Enter") buscar();
  });
</script>

</body>
</html>