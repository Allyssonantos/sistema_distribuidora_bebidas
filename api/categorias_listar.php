<?php
header("Content-Type: application/json; charset=utf-8");
require_once __DIR__ . "/../config/db.php";

try{
  $stmt = $pdo->query("SELECT id, nome FROM categorias ORDER BY nome");
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

  // ⚠️ Retorna ARRAY PURO (igual a tela espera)
  echo json_encode($rows);
}catch(Exception $e){
  http_response_code(400);
  echo json_encode([]);
}