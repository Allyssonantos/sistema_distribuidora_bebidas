<?php
header("Content-Type: application/json; charset=utf-8");
require_once __DIR__ . "/../config/db.php";

$input = json_decode(file_get_contents("php://input"), true);
$sessaoId = (int)($input["sessao_id"] ?? 0);
$obs = trim($input["obs"] ?? "");

if($sessaoId <= 0){
  http_response_code(400);
  echo json_encode(["ok"=>false,"erro"=>"Sessão inválida"]);
  exit;
}

try {
  $pdo->beginTransaction();

  // trava sessão
  $st = $pdo->prepare("SELECT * FROM caixa_sessoes WHERE id=? FOR UPDATE");
  $st->execute([$sessaoId]);
  $sessao = $st->fetch(PDO::FETCH_ASSOC);

  if(!$sessao) throw new Exception("Sessão não encontrada");
  if($sessao["status"] !== "ABERTO") throw new Exception("Sessão já está FECHADA");

  // calcula totais a partir das vendas da sessão
  $sql = "
    SELECT forma_pagamento, COALESCE(SUM(total),0) as total, COUNT(*) as qtd
    FROM vendas
    WHERE caixa_sessao_id = :sid AND status='FINALIZADA'
    GROUP BY forma_pagamento
  ";
  $st2 = $pdo->prepare($sql);
  $st2->execute([":sid"=>$sessaoId]);

  $map = [
    "DINHEIRO"=>0, "PIX"=>0, "CARTAO_DEBITO"=>0, "CARTAO_CREDITO"=>0, "OUTROS"=>0
  ];
  $qtdVendas = 0;

  while($r = $st2->fetch(PDO::FETCH_ASSOC)){
    $fp = $r["forma_pagamento"];
    $map[$fp] = (float)$r["total"];
    $qtdVendas += (int)$r["qtd"];
  }

  $totalGeral = array_sum($map);

  // fecha e grava na sessão
  $upd = $pdo->prepare("
    UPDATE caixa_sessoes SET
      fechado_em = NOW(),
      status = 'FECHADO',
      total_dinheiro = :din,
      total_pix = :pix,
      total_cartao_debito = :deb,
      total_cartao_credito = :cred,
      total_outros = :out,
      total_geral = :geral,
      qtd_vendas = :qtd,
      obs = :obs
    WHERE id = :id
  ");

  $upd->execute([
    ":din"=>$map["DINHEIRO"],
    ":pix"=>$map["PIX"],
    ":deb"=>$map["CARTAO_DEBITO"],
    ":cred"=>$map["CARTAO_CREDITO"],
    ":out"=>$map["OUTROS"],
    ":geral"=>$totalGeral,
    ":qtd"=>$qtdVendas,
    ":obs"=>($obs===""? null : $obs),
    ":id"=>$sessaoId
  ]);

  $pdo->commit();

  echo json_encode(["ok"=>true, "sessao_id"=>$sessaoId]);

} catch(Exception $e){
  if($pdo->inTransaction()) $pdo->rollBack();
  http_response_code(400);
  echo json_encode(["ok"=>false,"erro"=>$e->getMessage()]);
}