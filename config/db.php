<?php
$host = "127.0.0.1";
$db   = "distribuidora";
$user = "root";
$pass = ""; // XAMPP padrão

try {
  $pdo = new PDO(
    "mysql:host=$host;dbname=$db;charset=utf8mb4",
    $user,
    $pass,
    [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
  );
} catch (Exception $e) {
  http_response_code(500);
  echo "Erro ao conectar no banco: " . $e->getMessage();
  exit;
}