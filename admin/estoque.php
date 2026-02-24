<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <title>Admin - Movimentar Estoque</title>
  <style>
    body{font-family:Arial, sans-serif; margin:20px; background-color: #f4f7f6;}
    .toolbar { background: #fff; padding: 15px; border-radius: 8px; border: 1px solid #dee2e6; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
    .card { background: #fff; padding: 20px; border-radius: 8px; border: 1px solid #dee2e6; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 20px; }
    input, select, textarea { padding: 10px; border: 1px solid #ddd; border-radius: 4px; width: 100%; box-sizing: border-box; margin-top: 5px; }
    .btn-primary { background: #28a745; color: white; border: none; padding: 12px 20px; border-radius: 4px; cursor: pointer; font-weight: bold; }
    .btn-secondary { background: #f8f9fa; border: 1px solid #ccc; padding: 10px 14px; border-radius: 4px; text-decoration: none; color: #333; }
    .grid { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-top: 15px; }
    .sugestoes { border: 1px solid #ddd; background: #fff; position: absolute; width: 300px; z-index: 10; border-radius: 4px; }
    .sugestoes div { padding: 10px; cursor: pointer; border-bottom: 1px solid #eee; }
    .sugestoes div:hover { background: #f8f9fa; }
  </style>
</head>
<body>

<h2>📥 Movimentação de Estoque</h2>

<div class="toolbar">
  <div style="position: relative;">
    <input id="busca" placeholder="Nome ou código de barras..." style="width: 300px;" onkeyup="buscar(event)">
    <div id="sugestoes" class="sugestoes" style="display:none;"></div>
  </div>
  <div class="btn-group">
    <a href="produtos.php" class="btn-secondary">📦 Voltar</a>
    <a href="estoque_relatorios.php" class="btn-secondary">📋 Relatório Mov.</a>
  </div>
</div>

<div id="formMov" class="card" style="display:none;">
  <h3 id="p_nome" style="margin-top:0;"></h3>
  <div class="grid">
    <div>
      <label>Tipo de Movimentação</label>
      <select id="tipo">
        <option value="ENTRADA">ENTRADA (Compra)</option>
        <option value="PERDA">PERDA (Quebra/Vencimento)</option>
        <option value="AJUSTE">AJUSTE (Inventário)</option>
      </select>
    </div>
    <div>
      <label>Quantidade</label>
      <input id="quantidade" type="number" step="0.001">
    </div>
    <div>
      <label>Valor Unitário (Opcional na compra)</label>
      <input id="valor_unit" type="number" step="0.01">
    </div>
    <div>
      <label>Observação</label>
      <input id="obs" placeholder="Ex: Fornecedor X">
    </div>
  </div>
  <div style="text-align:right; margin-top:20px;">
    <button onclick="confirmar()" class="btn-primary">✅ Confirmar Movimentação</button>
  </div>
</div>

<script>
  let produtoId = 0;

  async function buscar(e){
    const q = e.target.value.trim();
    if(q.length < 2) return document.getElementById("sugestoes").style.display = "none";

    const res = await fetch(`../api/produtos_buscar.php?q=${encodeURIComponent(q)}`);
    const itens = await res.json();
    
    const div = document.getElementById("sugestoes");
    div.innerHTML = "";
    div.style.display = "block";

    itens.forEach(p => {
      const d = document.createElement("div");
      d.textContent = `${p.nome} (Estoque: ${p.estoque_atual})`;
      d.onclick = () => selecionar(p);
      div.appendChild(d);
    });
  }

  function selecionar(p){
    produtoId = p.id;
    document.getElementById("sugestoes").style.display = "none";
    document.getElementById("p_nome").textContent = "📦 " + p.nome;
    document.getElementById("formMov").style.display = "block";
    document.getElementById("quantidade").focus();
  }

  async function confirmar(){
    const payload = {
      produto_id: produtoId,
      tipo: document.getElementById("tipo").value,
      quantidade: document.getElementById("quantidade").value,
      valor_unit: document.getElementById("valor_unit").value,
      observacao: document.getElementById("obs").value
    };

    const res = await fetch("../api/estoque_movimentar.php", {
      method: "POST",
      headers: {"Content-Type":"application/json"},
      body: JSON.stringify(payload)
    });

    const json = await res.json();
    if(json.ok){
      alert("Sucesso!");
      location.reload();
    } else {
      alert(json.erro);
    }
  }
</script>

</body>
</html>