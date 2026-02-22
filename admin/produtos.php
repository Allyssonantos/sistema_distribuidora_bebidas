<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin - Produtos</title>
  <style>
    body{font-family:Arial; margin:20px;}
    .top{display:flex; gap:10px; flex-wrap:wrap; align-items:center;}
    input, select{padding:10px;}
    button{padding:10px 14px; cursor:pointer;}
    table{width:100%; border-collapse:collapse; margin-top:14px;}
    th,td{border-bottom:1px solid #ddd; padding:10px;}
    th{background:#fafafa; text-align:left;}
    .right{text-align:right;}
    .badge{padding:4px 8px; border-radius:999px; font-size:12px; display:inline-block;}
    .ok{background:#e7f7ed;}
    .low{background:#ffe8e8;}
    .rowlow{background:#fff5f5;}
    .grid{display:grid; grid-template-columns:1fr 1fr; gap:10px;}
    @media(max-width:800px){ .grid{grid-template-columns:1fr;} }
    .modalbg{display:none; position:fixed; inset:0; background:rgba(0,0,0,.35); align-items:center; justify-content:center; padding:20px;}
    .modal{background:#fff; width:760px; max-width:100%; border-radius:10px; padding:14px;}
  </style>
</head>
<body>

<h2>📦 Admin - Produtos</h2>

<div class="top">
  <input id="q" placeholder="Buscar por nome ou código..." />
  <label><input type="checkbox" id="somenteLow" onchange="carregar()"> Mostrar acabando</label>
  <button onclick="carregar()">🔎 Buscar</button>
  <button onclick="novoProduto()">➕ Novo produto</button>
  <a href="relatorios.php">📊 Relatório de Vendas</a>
  <a href="estoque_relatorios.php">📦 Relatório de Estoque</a>
  <a href="estoque.php">📥 Movimentar Estoque</a>
  <a href="categorias.php">🏷️ Categorias</a>
  <a href="lucro.php">📊 Admin - Lucro</a>
</div>

<table>
  <thead>
    <tr>
      <th>Produto</th>
      <th>Categoria</th>
      <th class="right">Estoque</th>
      <th class="right">Min</th>
      <th class="right">Custo</th>
      <th class="right">Venda</th>
      <th class="right">% Lucro</th>
      <th>Status</th>
      <th></th>
    </tr>
  </thead>
  <tbody id="lista"></tbody>
</table>

<!-- MODAL CADASTRO/EDIÇÃO -->
<div id="modalbg" class="modalbg">
  <div class="modal">
    <div style="display:flex; justify-content:space-between; align-items:center;">
      <h3 id="tituloModal" style="margin:0;">Produto</h3>
      <button onclick="fechar()">X</button>
    </div>
    <hr>

    <div class="grid">
      <div>
        <label>Nome</label><br>
        <input id="nome" style="width:100%;">
      </div>

      <div>
        <label>Categoria</label><br>
        <select id="categoria_id" style="width:100%;"></select>
      </div>

      <div>
        <label>Código de barras</label><br>
        <input id="codigo_barras" style="width:100%;">
      </div>

      <div>
        <label>Unidade</label><br>
        <select id="unidade" style="width:100%;">
          <option>UN</option><option>CX</option><option>LT</option><option>ML</option><option>KG</option>
        </select>
      </div>

      <div>
        <label>Preço de custo</label><br>
        <input id="preco_custo" type="number" step="0.01" style="width:100%;" oninput="calcLucro()">
      </div>

      <div>
        <label>Preço de venda</label><br>
        <input id="preco_venda" type="number" step="0.01" style="width:100%;" oninput="calcLucro()">
      </div>

      <div>
        <label>Estoque atual</label><br>
        <input id="estoque_atual" type="number" step="0.001" style="width:100%;">
      </div>

      <div>
        <label>Estoque mínimo</label><br>
        <input id="estoque_minimo" type="number" step="0.001" style="width:100%;">
      </div>

      <div>
        <label>Ativo</label><br>
        <select id="ativo" style="width:100%;">
          <option value="1">Sim</option>
          <option value="0">Não</option>
        </select>
      </div>

      <div>
        <label>% Lucro</label><br>
        <input id="lucro" disabled style="width:100%;">
      </div>
    </div>

    <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:12px;">
      <button onclick="fechar()">Cancelar</button>
      <button onclick="salvar()" style="font-weight:bold;">Salvar</button>
    </div>
  </div>
</div>

<script>
  let categorias = [];
  let editId = 0;

  function brl(v){ return Number(v||0).toFixed(2).replace(".", ","); }

  function lucroPct(custo, venda){
    custo = Number(custo||0); venda = Number(venda||0);
    if(custo <= 0 || venda <= 0) return "";
    return (((venda - custo) / custo) * 100).toFixed(1).replace(".", ",") + "%";
  }

  async function carregarCategorias(){
    const res = await fetch("../api/categorias_listar.php");
    categorias = await res.json();
    const sel = document.getElementById("categoria_id");
    sel.innerHTML = `<option value="">(Sem categoria)</option>`;
    categorias.forEach(c => {
      sel.innerHTML += `<option value="${c.id}">${c.nome}</option>`;
    });
  }

  async function carregar(){
    const q = document.getElementById("q").value.trim();
    const low = document.getElementById("somenteLow").checked ? 1 : 0;

    const url = `../api/produtos_listar.php?q=${encodeURIComponent(q)}&low=${low}`;
    const res = await fetch(url);
    const lista = await res.json();

    const tbody = document.getElementById("lista");
    tbody.innerHTML = "";

    lista.forEach(p => {
      const est = Number(p.estoque_atual);
      const min = Number(p.estoque_minimo);
      const acabando = (p.ativo == 1 && est <= min);

      const tr = document.createElement("tr");
      if(acabando) tr.className = "rowlow";

      tr.innerHTML = `
        <td>${p.nome}</td>
        <td>${p.categoria_nome || "-"}</td>
        <td class="right">${est}</td>
        <td class="right">${min}</td>
        <td class="right">R$ ${brl(p.preco_custo)}</td>
        <td class="right">R$ ${brl(p.preco_venda)}</td>
        <td class="right">${lucroPct(p.preco_custo, p.preco_venda) || "-"}</td>
        <td>${acabando ? `<span class="badge low">ACABANDO</span>` : `<span class="badge ok">${p.ativo==1?"OK":"INATIVO"}</span>`}</td>
        <td class="right"><button onclick='editar(${JSON.stringify(p)})'>Editar</button></td>
      `;
      tbody.appendChild(tr);
    });
  }

  function abrir(){ document.getElementById("modalbg").style.display = "flex"; }
  function fechar(){ document.getElementById("modalbg").style.display = "none"; }

  function calcLucro(){
    const custo = document.getElementById("preco_custo").value;
    const venda = document.getElementById("preco_venda").value;
    document.getElementById("lucro").value = lucroPct(custo, venda);
  }

  function novoProduto(){
    editId = 0;
    document.getElementById("tituloModal").textContent = "Novo produto";
    document.getElementById("nome").value = "";
    document.getElementById("categoria_id").value = "";
    document.getElementById("codigo_barras").value = "";
    document.getElementById("unidade").value = "UN";
    document.getElementById("preco_custo").value = "";
    document.getElementById("preco_venda").value = "";
    document.getElementById("estoque_atual").value = "0";
    document.getElementById("estoque_minimo").value = "0";
    document.getElementById("ativo").value = "1";
    document.getElementById("lucro").value = "";
    abrir();
  }

  function editar(p){
    editId = Number(p.id);
    document.getElementById("tituloModal").textContent = "Editar produto";
    document.getElementById("nome").value = p.nome || "";
    document.getElementById("categoria_id").value = p.categoria_id || "";
    document.getElementById("codigo_barras").value = p.codigo_barras || "";
    document.getElementById("unidade").value = p.unidade || "UN";
    document.getElementById("preco_custo").value = p.preco_custo;
    document.getElementById("preco_venda").value = p.preco_venda;
    document.getElementById("estoque_atual").value = p.estoque_atual;
    document.getElementById("estoque_minimo").value = p.estoque_minimo;
    document.getElementById("ativo").value = p.ativo;
    calcLucro();
    abrir();
  }

  async function salvar(){
    const payload = {
      id: editId,
      nome: document.getElementById("nome").value.trim(),
      categoria_id: document.getElementById("categoria_id").value,
      codigo_barras: document.getElementById("codigo_barras").value.trim(),
      unidade: document.getElementById("unidade").value,
      preco_custo: document.getElementById("preco_custo").value,
      preco_venda: document.getElementById("preco_venda").value,
      estoque_atual: document.getElementById("estoque_atual").value,
      estoque_minimo: document.getElementById("estoque_minimo").value,
      ativo: document.getElementById("ativo").value
    };

    const res = await fetch("../api/produtos_salvar.php", {
      method:"POST",
      headers:{"Content-Type":"application/json"},
      body: JSON.stringify(payload)
    });

    const json = await res.json();
    if(!json.ok){
      alert("Erro: " + (json.erro || "desconhecido"));
      return;
    }

    fechar();
    await carregar();
  }

  // init
  (async () => {
    await carregarCategorias();
    await carregar();
  })();
</script>

</body>
</html>