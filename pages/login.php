<?php

include "valida_dados.php";

$email = $senha = "";
$email_erro = $senha_erro = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = valida_dados($_POST["email"] ?? "");
    $senha = valida_dados($_POST["senha"] ?? "");

    // Validação do e-mail
    if (empty($email)) {
        $email_erro = "Informe seu e-mail.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $email_erro = "Digite um e-mail válido, como seu@email.com.";
    }

    // Validação da senha
    if (empty($senha)) {
        $senha_erro = "Informe sua senha.";
    } elseif (!preg_match('/^(?=.*[A-Za-z])(?=.*\d).{8,64}$/', $senha)) {
        $senha_erro = "Use 8 a 64 caracteres com letras e números.";
    }

    // Se não houver erros, vai para a tela de métodos
    if (empty($email_erro) && empty($senha_erro)) {
        header("Location: metodos.html");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Studay &mdash; Login</title>
    <link rel="shortcut icon" href="../img/logo/faisca.webp" type="image/x-icon">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <nav class="nav-top" aria-label="Navegação principal">
        <a href="../index.html">Início</a>
        <a href="login.php" class="active">Login</a>
        <a href="registro.php">Registre-se</a>
    </nav>

    <div class="page-auth">
        <div class="auth-wrapper">

            <div class="auth-logo studay-logo" aria-label="Studay">
                <span class="logo-stu">Stu</span><span class="logo-day">day</span>
            </div>

            <div class="auth-box">
                <h2>Entrar</h2>

                <form id="loginForm" class="auth-form" method="POST"
                      action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" novalidate>

                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" value="<?php echo $email; ?>"
                           placeholder="seu@email.com" autocomplete="email">
                    <?php if (!empty($email_erro)): ?>
                    <p class="form-error form-error-campo visivel" role="alert"><?php echo $email_erro; ?></p>
                    <?php endif; ?>

                    <label for="senha">Senha</label>
                    <div class="password-field">
                        <input type="password" id="senha" name="senha"
                               placeholder="Digite sua senha" autocomplete="current-password">
                        <button type="button" id="toggleSenha" class="toggle-password-btn" aria-label="Mostrar/ocultar senha">
                            <svg class="eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                    <?php if (!empty($senha_erro)): ?>
                    <p class="form-error form-error-campo visivel" role="alert"><?php echo $senha_erro; ?></p>
                    <?php endif; ?>
                    <button type="submit" class="btn-primary">Continuar</button>
                </form>
                <div class="auth-options">
                    <a href="#">Esqueceu a senha?</a>
                    <p style="margin-top: 10px;">Não tem uma conta? <a href="registro.php">Crie aqui!</a></p>
                </div>
            </div>

        </div>
    </div>
    <script src="../js/login.js"></script>
</body>
</html>