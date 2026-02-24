<?php
header("Content-Type: application/json; charset=utf-8");
require_once __DIR__ . "/../config/db.php";

$caixaId = 1; // caixa físico único

try {
  // pega sessão aberta
  $st = $pdo->prepare("SELECT * FROM caixa_sessoes WHERE caixa_id=? AND status='ABERTO' ORDER BY id DESC LIMIT 1");
  $st->execute([$caixaId]);
  $sessao = $st->fetch(PDO::FETCH_ASSOC);

  if(!$sessao){
    echo json_encode(["ok"=>true, "aberto"=>false]);
    exit;
  }

  $sessaoId = (int)$sessao["id"];

  // totais por forma de pagamento (somente finalizadas)
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

  echo json_encode([
    "ok"=>true,
    "aberto"=>true,
    "sessao"=>[
      "id"=>$sessaoId,
      "aberto_em"=>$sessao["aberto_em"],
      "troco_inicial" => (float)$sessao["troco_inicial"],
    ],
    "totais"=>[
      "dinheiro"=>$map["DINHEIRO"],
      "pix"=>$map["PIX"],
      "cartao_debito"=>$map["CARTAO_DEBITO"],
      "cartao_credito"=>$map["CARTAO_CREDITO"],
      "outros"=>$map["OUTROS"],
      "total_geral"=>$totalGeral,
      "qtd_vendas"=>$qtdVendas
    ]
  ]);

} catch(Exception $e){
  http_response_code(400);
  echo json_encode(["ok"=>false,"erro"=>$e->getMessage()]);
}