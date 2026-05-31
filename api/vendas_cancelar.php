<?php
header("Content-Type: application/json; charset=utf-8");
require_once __DIR__ . "/../config/db.php";

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) {
    http_response_code(400);
    echo json_encode(["ok" => false, "erro" => "JSON inválido"]);
    exit;
}

$venda_id = (int)($data["venda_id"] ?? 0);
$senha_adm = $data["senha_adm"] ?? "";

// Em um sistema real, você verificaria a senha no banco. 
// Aqui vamos usar uma senha padrão para demonstração ou você pode ajustar conforme sua lógica de login.
if ($senha_adm !== "admin123") {
    http_response_code(401);
    echo json_encode(["ok" => false, "erro" => "Senha administrativa incorreta."]);
    exit;
}

if ($venda_id <= 0) {
    http_response_code(400);
    echo json_encode(["ok" => false, "erro" => "ID da venda inválido."]);
    exit;
}

try {
    $pdo->beginTransaction();

    // 1. Verifica se a venda existe e já não está cancelada
    $st = $pdo->prepare("SELECT status FROM vendas WHERE id = ? FOR UPDATE");
    $st->execute([$venda_id]);
    $venda = $st->fetch();

    if (!$venda) throw new Exception("Venda não encontrada.");
    if ($venda["status"] === "CANCELADA") throw new Exception("Esta venda já foi cancelada.");

    // 2. Busca os itens da venda para devolver ao estoque
    $stItems = $pdo->prepare("SELECT produto_id, quantidade FROM venda_itens WHERE venda_id = ?");
    $stItems->execute([$venda_id]);
    $items = $stItems->fetchAll();

    foreach ($items as $item) {
        // Devolve ao estoque
        $stEstoque = $pdo->prepare("UPDATE produtos SET estoque_atual = estoque_atual + ? WHERE id = ?");
        $stEstoque->execute([$item["quantidade"], $item["produto_id"]]);

        // Registra a movimentação de estorno
        $stMov = $pdo->prepare("
            INSERT INTO mov_estoque (produto_id, tipo, quantidade, delta, origem, observacao)
            VALUES (?, 'ENTRADA', ?, ?, 'CANCELAMENTO', ?)
        ");
        $stMov->execute([
            $item["produto_id"], 
            $item["quantidade"], 
            $item["quantidade"], 
            "Estorno Venda #$venda_id"
        ]);
    }

    // 3. Atualiza o status da venda
    $stUpdate = $pdo->prepare("UPDATE vendas SET status = 'CANCELADA' WHERE id = ?");
    $stUpdate->execute([$venda_id]);

    $pdo->commit();
    echo json_encode(["ok" => true]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    http_response_code(400);
    echo json_encode(["ok" => false, "erro" => $e->getMessage()]);
}
