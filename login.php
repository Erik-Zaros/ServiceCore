<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Login - ServiceCore</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="shortcut icon" src="public/img/logo_service_core.png" type="image/x-icon">
  <link rel="stylesheet" type="text/css" href="public/css/login.css">
</head>
<body>
  <div class="bg-animation" id="bgAnimation"></div>

  <div class="login-container">
    <div class="login-card">
      <div class="logo-container">
        <img src="public/img/logo_service_core.png" alt="Logo ServiceCore" class="logo">
      </div>
      <h2 class="login-title">ServiceCore</h2>

      <div id="msgErro"></div>

      <form id="formLogin">
        <div class="form-group">
          <i class="bi bi-person-fill input-icon"></i>
          <input type="text" name="login" id="login" class="form-control" placeholder="Digite seu login" required>
        </div>

        <div class="form-group">
          <i class="bi bi-lock-fill input-icon"></i>
          <input type="password" name="senha" id="senha" class="form-control" placeholder="Digite sua senha" required>
          <button type="button" class="password-toggle" id="togglePassword">
            <i class="bi bi-eye-fill"></i>
          </button>
        </div>

        <button type="submit" class="btn btn-login">
          <i class="bi bi-box-arrow-in-right" id="loginIcon"></i>
          <span id="loginText">Entrar</span>
        </button>
      </form>

<!--  <div class="forgot-password">
        <a href="#" onclick="showMessage('Funcionalidade em desenvolvimento!', 'info')">Esqueceu sua senha?</a>
      </div> -->
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="public/js/login.js"></script>
</body>
</html>

