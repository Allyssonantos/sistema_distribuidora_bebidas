<?php
session_start();

// Dados de acesso fixos (você pode mudar aqui se quiser)
$usuario_correto = "admin";
$senha_correta = "admin123";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario_digitado = $_POST["usuario"] ?? "";
    $senha_digitada = $_POST["senha"] ?? "";

    // Comparação direta no código
    if ($usuario_digitado === $usuario_correto && $senha_digitada === $senha_correta) {
        $_SESSION["admin_id"] = 1;
        $_SESSION["admin_nome"] = "Administrador";
        
        header("Location: produtos.php");
        exit;
    } else {
        $erro = "Usuário ou senha inválidos!";
    }
}

?>
<!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <title>Login Administrativo</title>
  <style>
    body { font-family: Arial; display: flex; justify-content: center; align-items: center; height: 100vh; background: #f4f7f6; margin: 0; }
    .login-box { background: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); width: 300px; }
    h2 { text-align: center; color: #333; }
    input { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 4px; box-sizing: border-box; }
    button { width: 100%; padding: 10px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight: bold; }
    button:hover { background: #218838; }
    .error { color: #d9534f; font-size: 14px; text-align: center; }
  </style>
</head>
<body>
  <div class="login-box">
    <h2>🔐 Acesso Restrito</h2>
    <?php if(isset($erro)) echo "<p class='error'>$erro</p>"; ?>
    <form method="POST">
      <input name="usuario" placeholder="Usuário" required autofocus>
      <input type="password" name="senha" placeholder="Senha" required>
      <button type="submit">Entrar no Painel</button>
    </form>
  </div>
</body>
</html>