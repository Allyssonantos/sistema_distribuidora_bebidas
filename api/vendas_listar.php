<?php
header("Content-Type: application/json; charset=utf-8");
require_once __DIR__ . "/../config/db.php";

$de = $_GET["de"] ?? date("Y-m-d");
$ate = $_GET["ate"] ?? date("Y-m-d");

$inicio = $de . " 00:00:00";
$fim    = $ate . " 23:59:59";

$stmt = $pdo->prepare("
  SELECT id, data_venda, forma_pagamento, total, status
  FROM vendas
  WHERE data_venda BETWEEN :ini AND :fim
  ORDER BY data_venda DESC
  LIMIT 500
");
$stmt->execute([":ini"=>$inicio, ":fim"=>$fim]);

echo json_encode($stmt->fetchAll());