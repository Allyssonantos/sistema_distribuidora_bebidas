<?php
header("Content-Type: application/json; charset=utf-8");
require_once __DIR__ . "/../config/db.php";

$id = (int)($_GET["id"] ?? 0);
if ($id <= 0) {
  http_response_code(400);
  echo json_encode(["ok"=>false, "erro"=>"ID inválido"]);
  exit;
}

$stmt = $pdo->prepare("
  SELECT id, nome, codigo_barras, unidade, preco_custo, preco_venda, estoque_atual, estoque_minimo, ativo
  FROM produtos
  WHERE id = ?
  LIMIT 1
");
$stmt->execute([$id]);
$p = $stmt->fetch();

if (!$p) {
  http_response_code(404);
  echo json_encode(["ok"=>false, "erro"=>"Produto não encontrado"]);
  exit;
}

echo json_encode(["ok"=>true, "produto"=>$p]);