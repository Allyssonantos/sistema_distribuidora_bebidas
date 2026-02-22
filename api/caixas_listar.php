<?php
header("Content-Type: application/json; charset=utf-8");
require_once __DIR__ . "/../config/db.php";

try{
  // Ajuste os campos conforme sua tabela caixas
  // Vou tentar ler id + nome (se não existir "nome", a gente troca por "descricao" etc.)
  $cols = $pdo->query("SHOW COLUMNS FROM caixas")->fetchAll(PDO::FETCH_ASSOC);
  $names = array_column($cols, "Field");

  $campoNome = "nome";
  if (!in_array("nome", $names)) {
    if (in_array("descricao", $names)) $campoNome = "descricao";
    else if (in_array("titulo", $names)) $campoNome = "titulo";
    else $campoNome = "id";
  }

  $sql = "SELECT id, {$campoNome} AS nome FROM caixas ORDER BY id";
  $stmt = $pdo->query($sql);
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

  echo json_encode($rows);
}catch(Exception $e){
  http_response_code(400);
  echo json_encode([]);
}