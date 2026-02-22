<?php
header("Content-Type: application/json; charset=utf-8");
require_once __DIR__ . "/../config/db.php";

$caixa_id = (int)($_GET["caixa_id"] ?? 0);
if ($caixa_id <= 0) {
  echo json_encode(["ok"=>false, "erro"=>"caixa_id inválido"]);
  exit;
}

$stmt = $pdo->prepare("
  SELECT id, aberto_em, troco_inicial
  FROM caixa_sessoes
  WHERE caixa_id = ? AND status = 'ABERTO'
  ORDER BY id DESC
  LIMIT 1
");
$stmt->execute([$caixa_id]);
$sessao = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode([
  "ok" => true,
  "aberto" => $sessao ? true : false,
  "sessao_id" => $sessao ? (int)$sessao["id"] : null,
  "aberto_em" => $sessao["aberto_em"] ?? null,
  "troco_inicial" => $sessao["troco_inicial"] ?? null,
]);