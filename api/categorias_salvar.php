<?php
header("Content-Type: application/json; charset=utf-8");
require_once __DIR__ . "/../config/db.php";

$data = json_decode(file_get_contents("php://input"), true);
if(!$data){ http_response_code(400); echo json_encode(["ok"=>false,"erro"=>"JSON inválido"]); exit; }

$id = (int)($data["id"] ?? 0);
$nome = trim($data["nome"] ?? "");
if($nome === ""){ http_response_code(400); echo json_encode(["ok"=>false,"erro"=>"Nome obrigatório"]); exit; }

try{
  if($id > 0){
    $stmt = $pdo->prepare("UPDATE categorias SET nome = :nome WHERE id = :id");
    $stmt->execute(["nome"=>$nome, "id"=>$id]);
  } else {
    $stmt = $pdo->prepare("INSERT INTO categorias (nome) VALUES (:nome)");
    $stmt->execute(["nome"=>$nome]);
    $id = (int)$pdo->lastInsertId();
  }

  echo json_encode(["ok"=>true,"id"=>$id]);
}catch(Exception $e){
  http_response_code(400);
  echo json_encode(["ok"=>false,"erro"=>$e->getMessage()]);
}