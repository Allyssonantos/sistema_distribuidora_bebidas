<?php
header("Content-Type: application/json; charset=utf-8");
require_once __DIR__ . "/../config/db.php";

$data = json_decode(file_get_contents("php://input"), true);
if(!$data){ http_response_code(400); echo json_encode(["ok"=>false,"erro"=>"JSON inválido"]); exit; }

$id = (int)($data["id"] ?? 0);
if($id <= 0){ http_response_code(400); echo json_encode(["ok"=>false,"erro"=>"ID inválido"]); exit; }

try{
  // não deixa excluir se existir produto usando
  $stmt = $pdo->prepare("SELECT COUNT(*) qtd FROM produtos WHERE categoria_id = :id");
  $stmt->execute(["id"=>$id]);
  $qtd = (int)$stmt->fetchColumn();

  if($qtd > 0){
    http_response_code(400);
    echo json_encode(["ok"=>false,"erro"=>"Não dá pra excluir: existem produtos nessa categoria. Mova os produtos para outra categoria primeiro."]);
    exit;
  }

  $del = $pdo->prepare("DELETE FROM categorias WHERE id = :id");
  $del->execute(["id"=>$id]);

  echo json_encode(["ok"=>true]);
}catch(Exception $e){
  http_response_code(400);
  echo json_encode(["ok"=>false,"erro"=>$e->getMessage()]);
}