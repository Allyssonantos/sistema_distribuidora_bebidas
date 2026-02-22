<?php
header("Content-Type: application/json; charset=utf-8");
require_once __DIR__ . "/../config/db.php";

$data = json_decode(file_get_contents("php://input"), true);
if (!$data) { http_response_code(400); echo json_encode(["ok"=>false,"erro"=>"JSON inválido"]); exit; }

$id            = (int)($data["id"] ?? 0);
$categoria_id  = $data["categoria_id"] ?? null;
$nome          = trim($data["nome"] ?? "");
$codigo_barras = trim($data["codigo_barras"] ?? "");
$unidade       = $data["unidade"] ?? "UN";
$preco_custo   = (float)($data["preco_custo"] ?? 0);
$preco_venda   = (float)($data["preco_venda"] ?? 0);
$estoque_atual = (float)($data["estoque_atual"] ?? 0);
$estoque_min   = (float)($data["estoque_minimo"] ?? 0);
$ativo         = (int)($data["ativo"] ?? 1);

$unidadesValidas = ["UN","CX","LT","ML","KG"];
if ($nome === "") { http_response_code(400); echo json_encode(["ok"=>false,"erro"=>"Nome é obrigatório"]); exit; }
if (!in_array($unidade, $unidadesValidas)) { http_response_code(400); echo json_encode(["ok"=>false,"erro"=>"Unidade inválida"]); exit; }

if ($categoria_id === "" || $categoria_id === 0) $categoria_id = null;
if ($codigo_barras === "") $codigo_barras = null;

try {
  if ($id > 0) {
    $stmt = $pdo->prepare("
      UPDATE produtos SET
        categoria_id=:categoria_id,
        nome=:nome,
        codigo_barras=:codigo_barras,
        unidade=:unidade,
        preco_custo=:preco_custo,
        preco_venda=:preco_venda,
        estoque_atual=:estoque_atual,
        estoque_minimo=:estoque_minimo,
        ativo=:ativo
      WHERE id=:id
    ");
    $stmt->execute([
      ":categoria_id"=>$categoria_id, ":nome"=>$nome, ":codigo_barras"=>$codigo_barras,
      ":unidade"=>$unidade, ":preco_custo"=>$preco_custo, ":preco_venda"=>$preco_venda,
      ":estoque_atual"=>$estoque_atual, ":estoque_minimo"=>$estoque_min, ":ativo"=>$ativo, ":id"=>$id
    ]);
    echo json_encode(["ok"=>true,"id"=>$id]);
    exit;
  }

  $stmt = $pdo->prepare("
    INSERT INTO produtos
      (categoria_id, nome, codigo_barras, unidade, preco_custo, preco_venda, estoque_atual, estoque_minimo, ativo)
    VALUES
      (:categoria_id, :nome, :codigo_barras, :unidade, :preco_custo, :preco_venda, :estoque_atual, :estoque_minimo, :ativo)
  ");
  $stmt->execute([
    ":categoria_id"=>$categoria_id, ":nome"=>$nome, ":codigo_barras"=>$codigo_barras,
    ":unidade"=>$unidade, ":preco_custo"=>$preco_custo, ":preco_venda"=>$preco_venda,
    ":estoque_atual"=>$estoque_atual, ":estoque_minimo"=>$estoque_min, ":ativo"=>$ativo
  ]);

  echo json_encode(["ok"=>true,"id"=>(int)$pdo->lastInsertId()]);
} catch (Exception $e) {
  http_response_code(400);
  echo json_encode(["ok"=>false,"erro"=>$e->getMessage()]);
}