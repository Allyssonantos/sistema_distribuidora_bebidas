<?php
header("Content-Type: application/json; charset=utf-8");
require_once __DIR__ . "/../config/db.php";

$de  = $_GET["de"]  ?? date("Y-m-d");
$ate = $_GET["ate"] ?? date("Y-m-d");

$inicio = $de . " 00:00:00";
$fim    = $ate . " 23:59:59";

/*
  Valores:
  - COMPRA: quantidade * valor_unit (se não tiver valor_unit, cai 0)
  - PERDA: quantidade * preco_custo
  - INVENTARIO: ABS(delta) * preco_custo, mas também mostramos separando ajuste + e -
*/

$stmt = $pdo->prepare("
  SELECT
    m.id, m.criado_em, m.tipo, m.origem, m.quantidade, m.delta, m.valor_unit, m.observacao,
    p.nome AS produto_nome, p.unidade, p.preco_custo
  FROM mov_estoque m
  JOIN produtos p ON p.id = m.produto_id
  WHERE m.criado_em BETWEEN :ini AND :fim
  ORDER BY m.criado_em DESC
  LIMIT 3000
");
$stmt->execute([":ini"=>$inicio, ":fim"=>$fim]);
$lista = $stmt->fetchAll();

$tot_qtd_compra = 0; $tot_qtd_perda = 0; $tot_qtd_ajuste = 0;
$val_compra = 0; $val_perda = 0; $val_ajuste = 0;

$val_ajuste_pos = 0; // ajuste pra cima (estoque aumentou)
$val_ajuste_neg = 0; // ajuste pra baixo (estoque diminuiu)

foreach ($lista as $r) {
  $qtd = (float)$r["quantidade"];
  $delta = ($r["delta"] === null) ? null : (float)$r["delta"];
  $custo = (float)$r["preco_custo"];
  $vunit = ($r["valor_unit"] === null) ? 0 : (float)$r["valor_unit"];

  if ($r["origem"] === "COMPRA") {
    $tot_qtd_compra += $qtd;
    $val_compra += $qtd * $vunit;
  }

  if ($r["origem"] === "PERDA") {
    $tot_qtd_perda += $qtd;
    $val_perda += $qtd * $custo;
  }

  if ($r["origem"] === "INVENTARIO") {
    // se delta estiver nulo (registros antigos), considera só abs(qtd) como neutro
    $tot_qtd_ajuste += $qtd;

    if ($delta === null) {
      $val_ajuste += $qtd * $custo;
    } else {
      $val = abs($delta) * $custo;
      $val_ajuste += $val;

      if ($delta > 0) $val_ajuste_pos += $val;
      if ($delta < 0) $val_ajuste_neg += $val;
    }
  }
}

echo json_encode([
  "ok" => true,
  "de" => $de,
  "ate" => $ate,
  "totais_qtd" => [
    "compra" => $tot_qtd_compra,
    "perda"  => $tot_qtd_perda,
    "ajuste" => $tot_qtd_ajuste,
  ],
  "totais_valor" => [
    "compra" => $val_compra,
    "perda"  => $val_perda,
    "ajuste" => $val_ajuste,
    "ajuste_pos" => $val_ajuste_pos,
    "ajuste_neg" => $val_ajuste_neg,
  ],
  "lista" => $lista
]);