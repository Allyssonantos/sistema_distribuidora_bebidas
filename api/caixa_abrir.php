<?php
header("Content-Type: application/json; charset=utf-8");
require_once __DIR__ . "/../config/db.php";

$data = json_decode(file_get_contents("php://input"), true);

$caixa_id      = (int)($data["caixa_id"]      ?? 1);
$troco_inicial = (float)($data["troco_inicial"] ?? 0);
$operador_nome = trim($data["operador_nome"]    ?? "Não informado");
$usuario_id    = null;

try {
    // Verifica se já tem caixa aberto
    $st = $pdo->prepare("SELECT id FROM caixa_sessoes WHERE caixa_id = ? AND status = 'ABERTO'");
    $st->execute([$caixa_id]);
    if ($st->fetch()) {
        throw new Exception("Já existe um caixa aberto.");
    }

    // Insere nova sessão com operador_nome
    $stmt = $pdo->prepare("
        INSERT INTO caixa_sessoes (caixa_id, usuario_id, aberto_em, status, troco_inicial, operador_nome)
        VALUES (?, ?, NOW(), 'ABERTO', ?, ?)
    ");
    $stmt->execute([$caixa_id, $usuario_id, $troco_inicial, $operador_nome]);

    echo json_encode(["ok" => true]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(["ok" => false, "erro" => $e->getMessage()]);
}