<?php
header("Content-Type: application/json; charset=utf-8");
require_once __DIR__."/../config/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$caixa_id = (int)($data["caixa_id"] ?? 1);
$valor_inicial = (float)($data["valor_inicial"] ?? 0);
$usuario_id = null;

try {
    $st = $pdo->prepare("SELECT id FROM caixa_sessoes WHERE caixa_id=? AND status='ABERTO'");
    $st->execute([$caixa_id]);

    if($st->fetch()){
        throw new Exception("Já existe um caixa aberto.");
    }

    $stmt = $pdo->prepare("
        INSERT INTO caixa_sessoes (caixa_id, usuario_id, aberto_em, status, troco_inicial)
        VALUES (?, ?, NOW(), 'ABERTO', ?)
    ");
    $stmt->execute([$caixa_id, $usuario_id, $valor_inicial]);

    echo json_encode(["ok" => true]);
} catch(Exception $e){
    http_response_code(400);
    echo json_encode(["ok" => false, "erro" => $e->getMessage()]);
}