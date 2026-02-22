<?php
header("Content-Type: application/json; charset=utf-8");
require_once __DIR__ . "/../config/db.php";

$de  = $_GET["de"]  ?? date("Y-m-d");
$ate = $_GET["ate"] ?? date("Y-m-d");
$caixa_id = (int)($_GET["caixa_id"] ?? 0); // 0 = todos

$ini = $de . " 00:00:00";
$fim = $ate . " 23:59:59";

try {
  $whereCaixa = ($caixa_id > 0) ? " AND caixa_id = :caixa_id " : "";

  // Totais por pagamento + total geral
  $stmtTot = $pdo->prepare("
    SELECT
      COUNT(*) AS qtd_vendas,
      COALESCE(SUM(total),0) AS total_geral,

      COALESCE(SUM(CASE WHEN forma_pagamento='DINHEIRO' THEN total ELSE 0 END),0) AS dinheiro,
      COALESCE(SUM(CASE WHEN forma_pagamento='PIX' THEN total ELSE 0 END),0) AS pix,
      COALESCE(SUM(CASE WHEN forma_pagamento='CARTAO_DEBITO' THEN total ELSE 0 END),0) AS cartao_debito,
      COALESCE(SUM(CASE WHEN forma_pagamento='CARTAO_CREDITO' THEN total ELSE 0 END),0) AS cartao_credito,
      COALESCE(SUM(CASE WHEN forma_pagamento='OUTROS' THEN total ELSE 0 END),0) AS outros

    FROM vendas
    WHERE data_venda BETWEEN :ini AND :fim
      AND status = 'FINALIZADA'
      $whereCaixa
  ");

  $params = ["ini"=>$ini, "fim"=>$fim];
  if ($caixa_id > 0) $params["caixa_id"] = $caixa_id;

  $stmtTot->execute($params);
  $tot = $stmtTot->fetch(PDO::FETCH_ASSOC);

  // Lista de vendas do período (pra conferência)
  $stmtList = $pdo->prepare("
    SELECT id, caixa_id, usuario_id, data_venda, forma_pagamento, total, valor_recebido, troco
    FROM vendas
    WHERE data_venda BETWEEN :ini AND :fim
      AND status = 'FINALIZADA'
      $whereCaixa
    ORDER BY data_venda DESC, id DESC
    LIMIT 500
  ");
  $stmtList->execute($params);
  $vendas = $stmtList->fetchAll(PDO::FETCH_ASSOC);

  echo json_encode([
    "ok" => true,
    "de" => $de,
    "ate" => $ate,
    "caixa_id" => $caixa_id,
    "totais" => [
      "qtd_vendas" => (int)$tot["qtd_vendas"],
      "total_geral" => (float)$tot["total_geral"],
      "dinheiro" => (float)$tot["dinheiro"],
      "pix" => (float)$tot["pix"],
      "cartao_debito" => (float)$tot["cartao_debito"],
      "cartao_credito" => (float)$tot["cartao_credito"],
      "outros" => (float)$tot["outros"],
    ],
    "vendas" => $vendas
  ]);

} catch (Exception $e) {
  http_response_code(400);
  echo json_encode(["ok"=>false, "erro"=>$e->getMessage()]);
}