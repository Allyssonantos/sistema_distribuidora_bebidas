<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin - Categorias</title>
  <style>
    body{font-family:Arial; margin:20px;}
    .top{display:flex; gap:10px; flex-wrap:wrap; align-items:center;}
    input{padding:10px; width:320px; max-width:100%;}
    button{padding:10px 14px; cursor:pointer;}
    table{width:100%; border-collapse:collapse; margin-top:14px;}
    th,td{border-bottom:1px solid #ddd; padding:10px;}
    th{background:#fafafa; text-align:left;}
    .right{text-align:right;}
    .modalbg{display:none; position:fixed; inset:0; background:rgba(0,0,0,.35); align-items:center; justify-content:center; padding:20px;}
    .modal{background:#fff; width:520px; max-width:100%; border-radius:10px; padding:14px;}
  </style>
</head>
<body>

<h2>🏷️ Admin - Categorias</h2>

<div class="top">
  <button onclick="abrirNovo()">➕ Nova categoria</button>
  <a href="produtos.php">📦 Voltar Produtos</a>
</div>

<table>
  <thead>
    <tr>
      <th>ID</th>
      <th>Categoria</th>
      <th class="right">Ações</th>
    </tr>
  </thead>
  <tbody id="lista"></tbody>
</table>

<!-- MODAL -->
<div id="modalbg" class="modalbg">
  <div class="modal">
    <div style="display:flex; justify-content:space-between; align-items:center;">
      <h3 id="tituloModal" style="margin:0;">Categoria</h3>
      <button onclick="fechar()">X</button>
    </div>
    <hr>
    <label>Nome</label><br>
    <input id="nome" style="width:100%;">
    <div style="display:flex; gap:10px; justify-content:flex-end; margin-top:12px;">
      <button onclick="fechar()">Cancelar</button>
      <button onclick="salvar()" style="font-weight:bold;">Salvar</button>
    </div>
  </div>
</div>

<script>
  let editId = 0;

  function abrir(){ document.getElementById("modalbg").style.display="flex"; }
  function fechar(){ document.getElementById("modalbg").style.display="none"; }

  function abrirNovo(){
    editId = 0;
    document.getElementById("tituloModal").textContent = "Nova categoria";
    document.getElementById("nome").value = "";
    abrir();
  }

  function abrirEditar(c){
    editId = Number(c.id);
    document.getElementById("tituloModal").textContent = "Editar categoria";
    document.getElementById("nome").value = c.nome || "";
    abrir();
  }

  async function carregar(){
    const res = await fetch("../api/categorias_listar.php");
    const lista = await res.json();

    const tbody = document.getElementById("lista");
    tbody.innerHTML = "";

    lista.forEach(c => {
      const tr = document.createElement("tr");
      tr.innerHTML = `
        <td>${c.id}</td>
        <td>${c.nome}</td>
        <td class="right">
          <button onclick='abrirEditar(${JSON.stringify(c)})'>Editar</button>
          <button onclick='excluir(${c.id})'>Excluir</button>
        </td>
      `;
      tbody.appendChild(tr);
    });
  }

  async function salvar(){
    const nome = document.getElementById("nome").value.trim();
    if(!nome){ alert("Informe o nome."); return; }

    const res = await fetch("../api/categorias_salvar.php",{
      method:"POST",
      headers:{"Content-Type":"application/json"},
      body: JSON.stringify({ id: editId, nome })
    });
    const json = await res.json();
    if(!json.ok){ alert("Erro: " + (json.erro || "desconhecido")); return; }

    fechar();
    carregar();
  }

  async function excluir(id){
    if(!confirm("Excluir categoria?")) return;

    const res = await fetch("../api/categorias_excluir.php",{
      method:"POST",
      headers:{"Content-Type":"application/json"},
      body: JSON.stringify({ id })
    });
    const json = await res.json();
    if(!json.ok){ alert("Erro: " + (json.erro || "desconhecido")); return; }

    carregar();
  }

  carregar();
</script>

</body>
</html>