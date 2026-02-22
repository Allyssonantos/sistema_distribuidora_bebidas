<?php
header("Content-Type: application/json; charset=utf-8");
require_once __DIR__ . "/../config/db.php";

$de  = $_GET["de"]  ?? date("Y-m-d");
$ate = $_GET["ate"] ?? date("Y-m-d");

$inicio = $de . " 00:00:00";
$fim    = $ate . " 23:59:59";

try {

  // 🔵 FATURAMENTO (VENDAS FINALIZADAS)
  $stmtV = $pdo->prepare("
    SELECT
      COALESCE(SUM(total),0) AS faturamento,
      COALESCE(SUM(CASE WHEN forma_pagamento='PIX' THEN total ELSE 0 END),0) AS pix,
      COALESCE(SUM(CASE WHEN forma_pagamento='DINHEIRO' THEN total ELSE 0 END),0) AS dinheiro,
      COALESCE(SUM(CASE WHEN forma_pagamento IN ('CARTAO_DEBITO','CARTAO_CREDITO') THEN total ELSE 0 END),0) AS cartoes,
      COUNT(*) AS qtd_vendas
    FROM vendas
    WHERE data_venda BETWEEN :ini AND :fim
      AND status = 'FINALIZADA'
  ");
  $stmtV->execute(["ini"=>$inicio, "fim"=>$fim]);
  $v = $stmtV->fetch();

  // 🟢 COMPRAS (ENTRADA DE ESTOQUE)
  $stmtC = $pdo->prepare("
    SELECT
      COALESCE(SUM(quantidade),0) AS qtd_compra,
      COALESCE(SUM(quantidade * COALESCE(valor_unit,0)),0) AS valor_compras
    FROM mov_estoque
    WHERE criado_em BETWEEN :ini AND :fim
      AND origem = 'COMPRA'
  ");
  $stmtC->execute(["ini"=>$inicio, "fim"=>$fim]);
  $c = $stmtC->fetch();

  // 🔴 PERDAS (QUEBRA/VENCIDO)
  $stmtP = $pdo->prepare("
    SELECT
      COALESCE(SUM(m.quantidade),0) AS qtd_perdas,
      COALESCE(SUM(m.quantidade * p.preco_custo),0) AS valor_perdas
    FROM mov_estoque m
    JOIN produtos p ON p.id = m.produto_id
    WHERE m.criado_em BETWEEN :ini AND :fim
      AND m.origem = 'PERDA'
  ");
  $stmtP->execute(["ini"=>$inicio, "fim"=>$fim]);
  $p = $stmtP->fetch();

  $faturamento  = (float)$v["faturamento"];
  $valorCompras = (float)$c["valor_compras"];
  $valorPerdas  = (float)$p["valor_perdas"];

  $lucroBruto = $faturamento - $valorCompras - $valorPerdas;
  $margem = $faturamento > 0 ? ($lucroBruto / $faturamento) * 100 : 0;

  echo json_encode([
    "ok" => true,
    "vendas" => [
      "faturamento" => $faturamento,
      "pix" => (float)$v["pix"],
      "dinheiro" => (float)$v["dinheiro"],
      "cartoes" => (float)$v["cartoes"],
      "qtd_vendas" => (int)$v["qtd_vendas"],
    ],
    "compras" => [
      "qtd" => (float)$c["qtd_compra"],
      "valor" => $valorCompras
    ],
    "perdas" => [
      "qtd" => (float)$p["qtd_perdas"],
      "valor" => $valorPerdas
    ],
    "lucro" => [
      "bruto" => $lucroBruto,
      "margem_pct" => $margem
    ]
  ]);

} catch (Exception $e) {
  http_response_code(400);
  echo json_encode(["ok"=>false,"erro"=>$e->getMessage()]);
}