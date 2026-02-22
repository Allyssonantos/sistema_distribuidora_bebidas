<?php
header("Content-Type: application/json; charset=utf-8");
require_once __DIR__ . "/../config/db.php";

$de  = $_GET["de"] ?? date("Y-m-d");
$ate = $_GET["ate"] ?? date("Y-m-d");
$caixa_sessao_id = (int)($_GET["caixa_sessao_id"] ?? 0);

try{
  $ini = $de . " 00:00:00";
  $fim = $ate . " 23:59:59";

  $where = "v.data_venda BETWEEN :ini AND :fim AND v.status='FINALIZADA'";
  if($caixa_sessao_id > 0){
    $where .= " AND v.caixa_sessao_id = :sid";
  }

  // lista vendas (com horários da sessão)
  $sql = "
    SELECT
      v.id, v.data_venda, v.forma_pagamento, v.total,
      v.caixa_sessao_id,
      cs.aberto_em, cs.fechado_em
    FROM vendas v
    LEFT JOIN caixa_sessoes cs ON cs.id = v.caixa_sessao_id
    WHERE $where
    ORDER BY v.id DESC
  ";

  $st = $pdo->prepare($sql);
  $params = [":ini"=>$ini, ":fim"=>$fim];
  if($caixa_sessao_id > 0) $params[":sid"] = $caixa_sessao_id;
  $st->execute($params);
  $vendas = $st->fetchAll(PDO::FETCH_ASSOC);

  // totais por forma
  $totais = [
    "qtd_vendas" => 0,
    "total_geral" => 0,
    "dinheiro" => 0,
    "pix" => 0,
    "cartao_debito" => 0,
    "cartao_credito" => 0,
    "outros" => 0
  ];

  foreach($vendas as $v){
    $totais["qtd_vendas"]++;
    $totais["total_geral"] += (float)$v["total"];

    switch($v["forma_pagamento"]){
      case "DINHEIRO": $totais["dinheiro"] += (float)$v["total"]; break;
      case "PIX": $totais["pix"] += (float)$v["total"]; break;
      case "CARTAO_DEBITO": $totais["cartao_debito"] += (float)$v["total"]; break;
      case "CARTAO_CREDITO": $totais["cartao_credito"] += (float)$v["total"]; break;
      default: $totais["outros"] += (float)$v["total"]; break;
    }
  }

  // dados da sessão selecionada (se filtrar)
  $sessao = null;
  if($caixa_sessao_id > 0){
    $s = $pdo->prepare("SELECT id, caixa_id, aberto_em, fechado_em, status FROM caixa_sessoes WHERE id=?");
    $s->execute([$caixa_sessao_id]);
    $sessao = $s->fetch(PDO::FETCH_ASSOC);
  }

  echo json_encode([
    "ok"=>true,
    "sessao"=>$sessao,
    "totais"=>$totais,
    "vendas"=>$vendas
  ]);

}catch(Exception $e){
  http_response_code(400);
  echo json_encode(["ok"=>false,"erro"=>$e->getMessage()]);
}