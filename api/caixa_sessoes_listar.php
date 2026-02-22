<?php
header("Content-Type: application/json; charset=utf-8");
require_once __DIR__ . "/../config/db.php";

try{
  $st = $pdo->query("
    SELECT id, caixa_id, aberto_em, fechado_em, status
    FROM caixa_sessoes
    ORDER BY id DESC
    LIMIT 200
  ");
  echo json_encode($st->fetchAll());
}catch(Exception $e){
  http_response_code(400);
  echo json_encode(["ok"=>false,"erro"=>$e->getMessage()]);
}