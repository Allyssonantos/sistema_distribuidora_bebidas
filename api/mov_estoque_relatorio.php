<?php
header("Content-Type: application/json; charset=utf-8");
require_once __DIR__ . "/../config/db.php";

$de  = $_GET["de"]  ?? date("Y-m-d");
$ate = $_GET["ate"] ?? date("Y-m-d");

$inicio = $de . " 00:00:00";
$fim    = $ate . " 23:59:59";

// 1) Totais por origem (COMPRA / PERDA / INVENTARIO)
$stmtTotais = $pdo->prepare("
  SELECT
    COALESCE(SUM(CASE WHEN origem = 'COMPRA' THEN quantidade ELSE 0 END),0) AS total_compra,
    COALESCE(SUM(CASE WHEN origem = 'PERDA' THEN quantidade ELSE 0 END),0) AS total_perda,
    COALESCE(SUM(CASE WHEN origem = 'INVENTARIO' THEN quantidade ELSE 0 END),0) AS total_ajuste
  FROM mov_estoque
  WHERE criado_em BETWEEN :ini AND :fim
");
$stmtTotais->execute([":ini"=>$inicio, ":fim"=>$fim]);
$totais = $stmtTotais->fetch() ?: ["total_compra"=>0,"total_perda"=>0,"total_ajuste"=>0];

// 2) Lista detalhada
$stmtLista = $pdo->prepare("
  SELECT
    m.id,
    m.criado_em,
    m.tipo,
    m.origem,
    m.quantidade,
    m.valor_unit,
    m.observacao,
    p.nome AS produto_nome,
    p.unidade
  FROM mov_estoque m
  JOIN produtos p ON p.id = m.produto_id
  WHERE m.criado_em BETWEEN :ini AND :fim
  ORDER BY m.criado_em DESC
  LIMIT 1500
");
$stmtLista->execute([":ini"=>$inicio, ":fim"=>$fim]);
$lista = $stmtLista->fetchAll();

echo json_encode([
  "ok" => true,
  "de" => $de,
  "ate" => $ate,
  "totais" => [
    "compra" => (float)$totais["total_compra"],
    "perda" => (float)$totais["total_perda"],
    "ajuste" => (float)$totais["total_ajuste"],
  ],
  "lista" => $lista
]);