<?php
header("Content-Type: application/json; charset=utf-8");
require_once __DIR__ . "/../config/db.php";

$caixa_id = (int)($_GET["caixa_id"] ?? 0);
if($caixa_id<=0){ http_response_code(400); echo json_encode(["ok"=>false,"erro"=>"caixa_id inválido"]); exit; }

$stmt = $pdo->prepare("
  SELECT * FROM caixa_sessoes
  WHERE caixa_id = :caixa_id AND status='ABERTO'
  ORDER BY id DESC
  LIMIT 1
");
$stmt->execute(["caixa_id"=>$caixa_id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode(["ok"=>true, "sessao"=>$row]);