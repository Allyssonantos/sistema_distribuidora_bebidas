<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin - Produtos</title>
  <style>
    body{font-family:Arial, sans-serif; margin:20px; background-color: #f4f7f6;}
    
    /* Barra de ferramentas organizada */
    .toolbar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      background: #fff;
      padding: 15px;
      border-radius: 8px;
      margin-bottom: 20px;
      border: 1px solid #dee2e6;
      box-shadow: 0 2px 4px rgba(0,0,0,0.05);
      flex-wrap: wrap;
      gap: 15px;
    }

    .search-group { display: flex; align-items: center; gap: 10px; }
    .btn-group { display: flex; gap: 8px; flex-wrap: wrap; }

    input, select { padding: 10px; border: 1px solid #ddd; border-radius: 4px; }
    
    /* Estilos de Botões */
    button { padding: 10px 14px; cursor: pointer; border-radius: 4px; border: none; transition: 0.2s; }
    .btn-primary { background-color: #28a745; color: white; font-weight: bold; }
    .btn-primary:hover { background-color: #218838; }
    .btn-secondary { background-color: #f8f9fa; border: 1px solid #ccc; color: #333; text-decoration: none; font-size: 14px; display: inline-flex; align-items: center; }
    .btn-secondary:hover { background-color: #e2e6ea; }

    table { width: 100%; border-collapse: collapse; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
    th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
    th { background: #f8f9fa; color: #333; font-weight: bold; }
    .right { text-align: right; }
    
    .badge { padding: 4px 8px; border-radius: 999px; font-size: 11px; font-weight: bold; }
    .ok { background: #e7f7ed; color: #2e7d32; }
    .low { background: #ffe8e8; color: #c62828; }
    .rowlow { background-color: #fff5f5; }

    .modalbg { display:none; position:fixed; inset:0; background:rgba(0,0,0,.4); align-items:center; justify-content:center; padding:20px; z-index: 1000; }
    .modal { background:#fff; width:760px; max-width:100%; border-radius:10px; padding:20px; box-shadow: 0 10px 25px rgba(0,0,0,0.2); }
    .grid { display:grid; grid-template-columns: 1fr 1fr; gap:15px; }
  </style>
</head>
<body>

<h2>📦 Painel de Produtos</h2>

<div class="toolbar">
  <div class="search-group">
    <input id="q" placeholder="Nome ou código de barras..." style="width: 250px;">
    <label><input type="checkbox" id="somenteLow" onchange="carregar()"> Acabando</label>
    <button onclick="carregar()" class="btn-secondary">🔎 Buscar</button>
  </div>

  <div class="btn-group">
    <button onclick="novoProduto()" class="btn-primary">➕ Novo Produto</button>
    <a href="relatorios.php" class="btn-secondary">📊 Vendas</a>
    <a href="estoque.php" class="btn-secondary">📥 Estoque</a>
    <a href="categorias.php" class="btn-secondary">🏷️ Categorias</a>
    <a href="lucro.php" class="btn-secondary">📈 Lucro</a>
    <a href="fechamento_caixa.php" class="btn-secondary">🧾 Caixas</a>
  </div>
</div>

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

<div id="modalbg" class="modalbg">
  <div class="modal">
    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:15px;">
      <h3 id="tituloModal" style="margin:0;">Produto</h3>
      <button onclick="fechar()" style="background:none; font-size:20px;">&times;</button>
    </div>
    <div class="grid" id="formProduto">
        <div><label>Nome</label><br><input id="nome" style="width:100%;"></div>
        <div><label>Categoria</label><br><select id="categoria_id" style="width:100%;"></select></div>
        <div><label>Preço Custo</label><br><input id="preco_custo" type="number" step="0.01" style="width:100%;"></div>
        <div><label>Preço Venda</label><br><input id="preco_venda" type="number" step="0.01" style="width:100%;"></div>
        <div><label>Estoque Atual</label><br><input id="estoque_atual" type="number" style="width:100%;"></div>
        <div><label>Estoque Mínimo</label><br><input id="estoque_minimo" type="number" style="width:100%;"></div>
    </div>
    <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:20px;">
      <button onclick="fechar()">Cancelar</button>
      <button onclick="salvar()" class="btn-primary">Salvar Produto</button>
    </div>
  </div>
</div>

<script>
  let categorias = [];
  let editId = 0;

  function brl(v){ return Number(v||0).toFixed(2).replace(".", ","); }

  async function carregarCategorias(){
    const res = await fetch("../api/categorias_listar.php");
    categorias = await res.json();
    const sel = document.getElementById("categoria_id");
    sel.innerHTML = `<option value="">(Sem categoria)</option>`;
    categorias.forEach(c => { sel.innerHTML += `<option value="${c.id}">${c.nome}</option>`; });
  }

  async function carregar(){
    const q = document.getElementById("q").value.trim();
    const low = document.getElementById("somenteLow").checked ? 1 : 0;
    const res = await fetch(`../api/produtos_listar.php?q=${encodeURIComponent(q)}&low=${low}`);
    const lista = await res.json();

    const tbody = document.getElementById("lista");
    tbody.innerHTML = "";

    lista.forEach(p => {
      const est = Number(p.estoque_atual);
      const min = Number(p.estoque_minimo);
      const acabando = (p.ativo == 1 && est <= min);

      const tr = document.createElement("tr");
      if(acabando) tr.className = "rowlow";

      // AQUI ESTÁ A PARTE DO BOTÃO EDITAR QUE VOCÊ BUSCAVA:
      tr.innerHTML = `
        <td><b>${p.nome}</b><br><small style="color:#999">${p.codigo_barras || ''}</small></td>
        <td>${p.categoria_nome || "-"}</td>
        <td class="right">${est}</td>
        <td class="right">${min}</td>
        <td class="right">R$ ${brl(p.preco_custo)}</td>
        <td class="right">R$ ${brl(p.preco_venda)}</td>
        <td>${acabando ? `<span class="badge low">ALERTA</span>` : `<span class="badge ok">OK</span>`}</td>
        <td class="right">
          <button onclick='editar(${JSON.stringify(p)})' 
                  style="background:#007bff; color:white; border:none; padding:6px 12px; border-radius:4px; font-size:12px;">
            Editar
          </button>
        </td>
      `;
      tbody.appendChild(tr);
    });
  }

  function abrir(){ document.getElementById("modalbg").style.display = "flex"; }
  function fechar(){ document.getElementById("modalbg").style.display = "none"; }

  function novoProduto(){
    editId = 0;
    document.getElementById("tituloModal").textContent = "Novo Produto";
    // Limpar campos...
    abrir();
  }

  function editar(p){
    editId = Number(p.id);
    document.getElementById("tituloModal").textContent = "Editar Produto";
    document.getElementById("nome").value = p.nome;
    document.getElementById("categoria_id").value = p.categoria_id || "";
    document.getElementById("preco_custo").value = p.preco_custo;
    document.getElementById("preco_venda").value = p.preco_venda;
    document.getElementById("estoque_atual").value = p.estoque_atual;
    document.getElementById("estoque_minimo").value = p.estoque_minimo;
    abrir();
  }

  async function salvar(){
    const payload = {
      id: editId,
      nome: document.getElementById("nome").value,
      categoria_id: document.getElementById("categoria_id").value,
      preco_custo: document.getElementById("preco_custo").value,
      preco_venda: document.getElementById("preco_venda").value,
      estoque_atual: document.getElementById("estoque_atual").value,
      estoque_minimo: document.getElementById("estoque_minimo").value,
      ativo: 1
    };

    const res = await fetch("../api/produtos_salvar.php", {
      method:"POST",
      headers:{"Content-Type":"application/json"},
      body: JSON.stringify(payload)
    });

    const json = await res.json();
    if(json.ok) { fechar(); carregar(); } else { alert(json.erro); }
  }

  window.onload = () => { carregarCategorias(); carregar(); };
</script>

</body>
</html>