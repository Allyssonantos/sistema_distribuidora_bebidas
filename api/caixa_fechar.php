<?php
header("Content-Type: application/json; charset=utf-8");
require_once __DIR__ . "/../config/db.php";

$data = json_decode(file_get_contents("php://input"), true);
if(!$data){ http_response_code(400); echo json_encode(["ok"=>false,"erro"=>"JSON inválido"]); exit; }

$sessao_id = (int)($data["sessao_id"] ?? 0);
$obs = trim($data["obs"] ?? "");

if($sessao_id<=0){ http_response_code(400); echo json_encode(["ok"=>false,"erro"=>"sessao_id inválido"]); exit; }

try{
  $pdo->beginTransaction();

  // pega sessão
  $st = $pdo->prepare("SELECT * FROM caixa_sessoes WHERE id=:id FOR UPDATE");
  $st->execute(["id"=>$sessao_id]);
  $sessao = $st->fetch(PDO::FETCH_ASSOC);
  if(!$sessao) throw new Exception("Sessão não encontrada");
  if($sessao["status"] !== "ABERTO") throw new Exception("Sessão já está FECHADA");

  // totais das vendas ligadas nessa sessão
  $tot = $pdo->prepare("
    SELECT
      COUNT(*) qtd_vendas,
      COALESCE(SUM(total),0) total_geral,
      COALESCE(SUM(CASE WHEN forma_pagamento='DINHEIRO' THEN total ELSE 0 END),0) dinheiro,
      COALESCE(SUM(CASE WHEN forma_pagamento='PIX' THEN total ELSE 0 END),0) pix,
      COALESCE(SUM(CASE WHEN forma_pagamento='CARTAO_DEBITO' THEN total ELSE 0 END),0) deb,
      COALESCE(SUM(CASE WHEN forma_pagamento='CARTAO_CREDITO' THEN total ELSE 0 END),0) cred,
      COALESCE(SUM(CASE WHEN forma_pagamento='OUTROS' THEN total ELSE 0 END),0) outros
    FROM vendas
    WHERE caixa_sessao_id = :sid AND status='FINALIZADA'
  ");
  $tot->execute(["sid"=>$sessao_id]);
  $t = $tot->fetch(PDO::FETCH_ASSOC);

  // grava fechamento
  $up = $pdo->prepare("
    UPDATE caixa_sessoes SET
      fechado_em = NOW(),
      status = 'FECHADO',
      qtd_vendas = :qtd,
      total_geral = :geral,
      total_dinheiro = :din,
      total_pix = :pix,
      total_cartao_debito = :deb,
      total_cartao_credito = :cred,
      total_outros = :out,
      obs = :obs
    WHERE id = :id
  ");
  $up->execute([
    "qtd" => (int)$t["qtd_vendas"],
    "geral" => (float)$t["total_geral"],
    "din" => (float)$t["dinheiro"],
    "pix" => (float)$t["pix"],
    "deb" => (float)$t["deb"],
    "cred" => (float)$t["cred"],
    "out" => (float)$t["outros"],
    "obs" => ($obs===""?null:$obs),
    "id" => $sessao_id
  ]);

  $pdo->commit();
  echo json_encode(["ok"=>true, "totais"=>$t]);

}catch(Exception $e){
  if($pdo->inTransaction()) $pdo->rollBack();
  http_response_code(400);
  echo json_encode(["ok"=>false,"erro"=>$e->getMessage()]);
}