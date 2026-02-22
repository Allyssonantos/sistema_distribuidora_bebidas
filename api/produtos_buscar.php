<?php
require_once __DIR__ . "/../config/db.php";

$q = trim($_GET["q"] ?? "");
if ($q === "") {
  echo json_encode([]);
  exit;
}

$sql = "SELECT id, nome, codigo_barras, preco_venda, estoque_atual, unidade
        FROM produtos
        WHERE ativo = 1
          AND (nome LIKE :q OR codigo_barras = :exato)
        ORDER BY nome
        LIMIT 20";

$stmt = $pdo->prepare($sql);
$stmt->execute([
  ":q" => "%$q%",
  ":exato" => $q
]);

header("Content-Type: application/json; charset=utf-8");
echo json_encode($stmt->fetchAll());