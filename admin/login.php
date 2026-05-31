<?php
session_start();

$usuario_correto = "admin";
$senha_correta = "admin123";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario_digitado = $_POST["usuario"] ?? "";
    $senha_digitada = $_POST["senha"] ?? "";
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
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;600&family=IBM+Plex+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --bg:#0f1117; --surface:#1a1d27; --surface2:#222535; --border:#2e3146;
      --accent:#4f8ef7; --green:#22c55e; --red:#ef4444;
      --text:#e8eaf0; --text-muted:#6b7399;
      --mono:"IBM Plex Mono",monospace; --sans:"IBM Plex Sans",sans-serif;
    }
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
    body{font-family:var(--sans);background:var(--bg);color:var(--text);min-height:100vh;display:flex;align-items:center;justify-content:center;}
    .card{background:var(--surface);border:1px solid var(--border);border-radius:14px;padding:36px 32px;width:360px;max-width:100%;box-shadow:0 8px 32px rgba(0,0,0,.5);}
    .brand{text-align:center;margin-bottom:28px;}
    .brand-name{font-family:var(--mono);font-size:18px;font-weight:600;letter-spacing:.04em;}
    .brand-sub{font-size:12px;color:var(--text-muted);margin-top:4px;}
    label{display:block;font-size:11px;font-weight:600;letter-spacing:.08em;text-transform:uppercase;color:var(--text-muted);margin-bottom:6px;}
    .field{margin-bottom:16px;}
    input{width:100%;background:var(--bg);border:1px solid var(--border);color:var(--text);border-radius:8px;padding:11px 14px;font-size:14px;font-family:var(--sans);outline:none;transition:.15s;}
    input:focus{border-color:var(--accent);box-shadow:0 0 0 3px rgba(79,142,247,.15);}
    input::placeholder{color:var(--text-muted);}
    .btn{width:100%;background:var(--accent);color:#fff;border:none;border-radius:8px;padding:13px;font-size:15px;font-weight:600;font-family:var(--sans);cursor:pointer;margin-top:8px;transition:opacity .15s;}
    .btn:hover{opacity:.85;}
    .erro{background:#3b0a0a;border:1px solid var(--red);color:var(--red);border-radius:8px;padding:10px 14px;font-size:13px;margin-bottom:16px;text-align:center;}
    .divider{border:none;border-top:1px solid var(--border);margin:24px 0;}
  </style>
</head>
<body>
  <div class="card">
    <div class="brand">
      <div class="brand-name">🔐 ADORA BEBIDAS</div>
      <div class="brand-sub">Painel Administrativo</div>
    </div>
    <?php if(isset($erro)): ?>
      <div class="erro"><?= htmlspecialchars($erro) ?></div>
    <?php endif; ?>
    <form method="POST">
      <div class="field">
        <label>Usuário</label>
        <input name="usuario" placeholder="Digite o usuário" required autofocus>
      </div>
      <div class="field">
        <label>Senha</label>
        <input type="password" name="senha" placeholder="Digite a senha" required>
      </div>
      <button type="submit" class="btn">Entrar no painel</button>
    </form>
  </div>
</body>
</html>