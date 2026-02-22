<?php
header("Content-Type: application/json; charset=utf-8");
require_once __DIR__ . "/../config/db.php";

$q = trim($_GET["q"] ?? "");
$low = (int)($_GET["low"] ?? 0); // 1 = somente acabando

$sql = "
  SELECT 
    p.id, p.nome, p.codigo_barras, p.unidade,
    p.preco_custo, p.preco_venda,
    p.estoque_atual, p.estoque_minimo, p.ativo,
    p.categoria_id,
    c.nome AS categoria_nome
  FROM produtos p
  LEFT JOIN categorias c ON c.id = p.categoria_id
  WHERE 1=1
";

$params = [];

if ($q !== "") {
  $sql .= " AND (p.nome LIKE :q OR p.codigo_barras = :exato) ";
  $params[":q"] = "%$q%";
  $params[":exato"] = $q;
}

if ($low === 1) {
  $sql .= " AND p.ativo = 1 AND p.estoque_atual <= p.estoque_minimo ";
}

$sql .= " ORDER BY (p.estoque_atual <= p.estoque_minimo) DESC, p.nome ASC LIMIT 300";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

echo json_encode($stmt->fetchAll());