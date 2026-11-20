<?php
include "valida_dados.php";

// Padrões usados nas validações
define('PADRAO_VALIDA_NOME', '/^[\p{L}][\p{L}\s\-.]{2,59}$/u');
define('PADRAO_VALIDA_SENHA', '/^(?=.*[A-Za-z])(?=.*\d).{8,64}$/');

// Variáveis do formulário
$nome = $email = $senha = $confirmarSenha = "";
$nome_erro = $email_erro = $senha_erro = $confirmarSenha_erro = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nome = valida_dados($_POST["nome"] ?? "");
    $email = valida_dados($_POST["email"] ?? "");
    $senha = valida_dados($_POST["senha"] ?? "");
    $confirmarSenha = valida_dados($_POST["confirmarSenha"] ?? "");

    // Validação do nome
    if (empty($nome)) {
        $nome_erro = "Informe seu nome.";
    } elseif (!preg_match(PADRAO_VALIDA_NOME, $nome)) {
        $nome_erro = "Use de 3 a 60 caracteres: apenas letras, espaços, ponto e hífen.";
    }

    // Validação do e-mail
    if (empty($email)) {
        $email_erro = "Informe seu e-mail.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $email_erro = "Digite um e-mail válido, como seu@email.com.";
    }

    // Validação da senha
    if (empty($senha)) {
        $senha_erro = "Crie uma senha.";
    } elseif (!preg_match(PADRAO_VALIDA_SENHA, $senha)) {
        $senha_erro = "Use 8 a 64 caracteres com letras e números.";
    }

    // Confirmação da senha
    if (empty($confirmarSenha)) {
        $confirmarSenha_erro = "Repita a senha.";
    } elseif ($senha !== $confirmarSenha) {
        $confirmarSenha_erro = "As senhas não coincidem.";
    }

    // Se não houver erros, vai para a tela de métodos
    if (empty($nome_erro) && empty($email_erro) && empty($senha_erro) && empty($confirmarSenha_erro)) {
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
    <title>Studay &mdash; Criar Conta</title>
    <link rel="shortcut icon" href="../img/logo/faisca.webp" type="image/x-icon">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>

    <nav class="nav-top" aria-label="Navegação principal">
        <a href="../index.html">Início</a>
        <a href="login.php">Login</a>
        <a href="registro.php" class="active">Registre-se</a>
    </nav>

    <div class="page-auth">
        <div class="auth-wrapper">

            <div class="auth-logo studay-logo" aria-label="Studay">
                <span class="logo-stu">Stu</span><span class="logo-day">day</span>
            </div>

            <div class="auth-box">
                <h2>Criar Conta</h2>

                <!-- novalidate: quem valida é o PHP (servidor) -->
                <form id="registroForm" class="auth-form" method="POST"
                      action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" novalidate>

                    <label for="nome">Nome</label>
                    <input type="text" id="nome" name="nome" value="<?php echo $nome; ?>"
                           placeholder="Seu nome completo" autocomplete="name">
                    <?php if (!empty($nome_erro)): ?>
                    <p class="form-error form-error-campo visivel" role="alert"><?php echo $nome_erro; ?></p>
                    <?php endif; ?>

                    <label for="email">E-mail</label>
                    <input type="email" id="email" name="email" value="<?php echo $email; ?>"
                           placeholder="seu@email.com" autocomplete="email">
                    <?php if (!empty($email_erro)): ?>
                    <p class="form-error form-error-campo visivel" role="alert"><?php echo $email_erro; ?></p>
                    <?php endif; ?>

                    <label for="senha">Senha</label>
                    <div class="password-field">
                        <input type="password" id="senha" name="senha"
                               placeholder="Crie uma senha" autocomplete="new-password">
                        <button type="button" id="toggleSenha1" class="toggle-password-btn" aria-label="Mostrar/ocultar senha">
                            <svg class="eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                    <?php if (!empty($senha_erro)): ?>
                    <p class="form-error form-error-campo visivel" role="alert"><?php echo $senha_erro; ?></p>
                    <?php endif; ?>

                    <label for="confirmarSenha">Confirmar Senha</label>
                    <div class="password-field">
                        <input type="password" id="confirmarSenha" name="confirmarSenha"
                               placeholder="Repita a senha" autocomplete="new-password">
                        <button type="button" id="toggleSenha2" class="toggle-password-btn" aria-label="Mostrar/ocultar senha">
                            <svg class="eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                    <?php if (!empty($confirmarSenha_erro)): ?>
                    <p class="form-error form-error-campo visivel" role="alert"><?php echo $confirmarSenha_erro; ?></p>
                    <?php endif; ?>

                    <button type="submit" class="btn-primary">Criar Sua Conta</button>
                </form>

                <div class="auth-options">
                    <p>Já tem uma conta? <a href="login.php">Fazer login</a></p>
                </div>
            </div>

        </div>
    </div>

    <script src="../js/registro.js"></script>
</body>
</html>
