<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin - Lucro</title>
  <style>
    body{font-family:Arial; margin:20px;}
    .top{display:flex; gap:10px; flex-wrap:wrap; align-items:center;}
    input{padding:10px;}
    button{padding:10px 14px; cursor:pointer;}
    .cards{display:grid; grid-template-columns:repeat(4,1fr); gap:10px; margin-top:14px;}
    .card{border:1px solid #eee; border-radius:12px; padding:12px;}
    .big{font-size:26px; margin-top:6px;}
    .muted{color:#666; font-size:12px;}
    @media(max-width:1000px){ .cards{grid-template-columns:repeat(2,1fr);} }
    @media(max-width:600px){ .cards{grid-template-columns:1fr;} }
    .subcards{display:grid; grid-template-columns:repeat(3,1fr); gap:10px; margin-top:10px;}
    @media(max-width:900px){ .subcards{grid-template-columns:1fr;} }
  </style>
</head>
<body>

<h2>📈 Admin - Lucro Real</h2>

<div class="top">
  <label>De: <input type="date" id="de"></label>
  <label>Até: <input type="date" id="ate"></label>
  <button onclick="carregar()">Atualizar</button>

  <a href="produtos.php">📦 Admin - Produtos</a>
  <a href="estoque.php">📥 Movimentar Estoque</a>
  <a href="estoque_relatorios.php">📦 Relatório de Estoque</a>
  <a href="relatorios.php">📊 Vendas</a>
</div>

<div class="cards">
  <div class="card">
    <b>💰 Faturamento</b>
    <div class="big" id="fat">R$ 0,00</div>
    <div class="muted" id="qv">0 vendas</div>
  </div>

  <div class="card">
    <b>💸 Compras (Custo)</b>
    <div class="big" id="comp">R$ 0,00</div>
    <div class="muted" id="qc">Qtd: 0</div>
  </div>

  <div class="card">
    <b>💀 Perdas (Prejuízo)</b>
    <div class="big" id="perd">R$ 0,00</div>
    <div class="muted" id="qp">Qtd: 0</div>
  </div>

  <div class="card">
    <b>📈 Lucro Bruto</b>
    <div class="big" id="luc">R$ 0,00</div>
    <div class="muted" id="marg">Margem: 0%</div>
  </div>
</div>

<div class="subcards">
  <div class="card">
    <b>PIX</b>
    <div class="big" id="pix">R$ 0,00</div>
  </div>
  <div class="card">
    <b>Dinheiro</b>
    <div class="big" id="din">R$ 0,00</div>
  </div>
  <div class="card">
    <b>Cartões</b>
    <div class="big" id="car">R$ 0,00</div>
  </div>
</div>

<script>
  function brl(v){ return "R$ " + Number(v||0).toFixed(2).replace(".", ","); }

  async function carregar(){
    const de = document.getElementById("de").value;
    const ate = document.getElementById("ate").value;

    const res = await fetch(`../api/lucro_relatorio.php?de=${de}&ate=${ate}`);
    const json = await res.json();

    if(!json.ok){
      alert("Erro: " + (json.erro || "desconhecido"));
      return;
    }

    document.getElementById("fat").textContent = brl(json.vendas.faturamento);
    document.getElementById("qv").textContent  = `${json.vendas.qtd_vendas} vendas`;

    document.getElementById("comp").textContent = brl(json.compras.valor);
    document.getElementById("qc").textContent   = `Qtd: ${json.compras.qtd}`;

    document.getElementById("perd").textContent = brl(json.perdas.valor);
    document.getElementById("qp").textContent   = `Qtd: ${json.perdas.qtd}`;

    document.getElementById("luc").textContent  = brl(json.lucro.bruto);
    document.getElementById("marg").textContent = `Margem: ${Number(json.lucro.margem_pct||0).toFixed(1).replace(".", ",")}%`;

    document.getElementById("pix").textContent = brl(json.vendas.pix);
    document.getElementById("din").textContent = brl(json.vendas.dinheiro);
    document.getElementById("car").textContent = brl(json.vendas.cartoes);
  }

  function hoje(){
    const d = new Date();
    const iso = d.toISOString().slice(0,10);
    document.getElementById("de").value = iso;
    document.getElementById("ate").value = iso;
  }

  hoje();
  carregar();
</script>

</body>
</html>