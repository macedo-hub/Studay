<?php
/*
 * LOGIN — agora de verdade: confere o e-mail e a senha no banco de dados.
 * A senha nunca é guardada: só o "hash" dela (password_hash / password_verify).
 */

include "valida_dados.php";
require_once __DIR__ . '/../includes/sessao.php';
require_once __DIR__ . '/../includes/destinos.php';

iniciar_sessao();

$email = $senha = "";
$email_erro = $senha_erro = $erro_geral = "";
$voltar = destino_seguro($_POST['voltar'] ?? ($_GET['voltar'] ?? ''));
$aviso  = (($_GET['aviso'] ?? '') === 'entre') ? 'Entre na sua conta para ver essa página.' : '';

// Quem já está logado vai direto para os métodos.
if ($_SERVER["REQUEST_METHOD"] !== "POST" && usuario_logado()) {
    header("Location: " . $voltar);
    exit;
}

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
    }

    if (!confere_csrf($_POST['token_csrf'] ?? '')) {
        $erro_geral = "A página ficou aberta por muito tempo. Atualize e tente de novo.";
    }

    // Limite de tentativas: 6 erros e espera 5 minutos (protege contra adivinhar senha)
    $bloqueadoAte = $_SESSION['login_bloqueado_ate'] ?? 0;
    if ($bloqueadoAte > time()) {
        $erro_geral = "Muitas tentativas seguidas. Espere uns minutos e tente de novo.";
    }

    // Se está tudo certo no formulário, confere no banco
    if (empty($email_erro) && empty($senha_erro) && empty($erro_geral)) {
        try {
            $banco = conectar_banco();
            $consulta = $banco->prepare('SELECT * FROM usuarios WHERE email = ?');
            $consulta->execute([strtolower($email)]);
            $usuario = $consulta->fetch();

            if ($usuario && password_verify($senha, $usuario['senha_hash'])) {
                unset($_SESSION['login_erros'], $_SESSION['login_bloqueado_ate']);
                $usuario = aplicar_papel_admin($banco, $usuario);
                entrar_na_conta($usuario);
                header("Location: " . $voltar);
                exit;
            }

            $_SESSION['login_erros'] = ($_SESSION['login_erros'] ?? 0) + 1;
            if ($_SESSION['login_erros'] >= 6) {
                $_SESSION['login_bloqueado_ate'] = time() + 300;
                $_SESSION['login_erros'] = 0;
            }
            $senha_erro = "E-mail ou senha incorretos. Confira e tente de novo.";
        } catch (Throwable $erro) {
            error_log('Studay login: ' . $erro->getMessage());
            $erro_geral = "Estamos com um probleminha para falar com o banco de dados. Tente de novo daqui a pouco.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Studay &mdash; Login</title>
    <!-- Pré-visualização de links (Open Graph e Twitter Cards) -->
    <meta name="description" content="Entre na sua conta para guardar seu XP, seu progresso e sua posição no ranking.">
    <link rel="canonical" href="https://studay.site.je/pages/login.php">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Studay">
    <meta property="og:locale" content="pt_BR">
    <meta property="og:title" content="Entrar no Studay">
    <meta property="og:description" content="Entre na sua conta para guardar seu XP, seu progresso e sua posição no ranking.">
    <meta property="og:url" content="https://studay.site.je/pages/login.php">
    <meta property="og:image" content="https://studay.site.je/img/compartilhar/studay-preview.jpg">
    <meta property="og:image:secure_url" content="https://studay.site.je/img/compartilhar/studay-preview.jpg">
    <meta property="og:image:type" content="image/jpeg">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="Studay: o Faísca, mascote do site, ao lado do nome Studay">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Entrar no Studay">
    <meta name="twitter:description" content="Entre na sua conta para guardar seu XP, seu progresso e sua posição no ranking.">
    <meta name="twitter:image" content="https://studay.site.je/img/compartilhar/studay-preview.jpg">
    <meta name="twitter:image:alt" content="Studay: o Faísca, mascote do site, ao lado do nome Studay">
    <!-- Fim da pré-visualização de links -->
    <link rel="shortcut icon" href="../img/logo/faisca.webp" type="image/x-icon">
    <!-- Ícones do site -->
    <link rel="icon" href="../favicon.ico" sizes="any">
    <link rel="icon" type="image/png" sizes="48x48" href="../img/icones/icone-48.png">
    <link rel="icon" type="image/png" sizes="192x192" href="../img/icones/icone-192.png">
    <link rel="apple-touch-icon" sizes="180x180" href="../img/icones/apple-touch-icon.png">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/animacoes.css">
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
                <p class="auth-subtitulo">Que bom te ver de novo! Entre para ganhar XP e subir no ranking.</p>

                <?php if (!empty($aviso)): ?>
                <p class="form-error visivel aviso-suave" role="status"><?php echo h($aviso); ?></p>
                <?php endif; ?>
                <?php if (!empty($erro_geral)): ?>
                <p class="form-error visivel" role="alert"><?php echo h($erro_geral); ?></p>
                <?php endif; ?>

                <form id="loginForm" class="auth-form" method="POST"
                      action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" novalidate>

                    <?php echo campo_csrf(); ?>
                    <input type="hidden" name="voltar" value="<?php echo h($voltar); ?>">

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
                    <p>Não tem uma conta? <a href="registro.php">Crie aqui!</a></p>
                    <p class="auth-olhadinha"><a href="metodos.html">Só quero dar uma olhada</a></p>
                </div>
            </div>

        </div>
    </div>
    <script src="../js/login.js"></script>
</body>
</html>
