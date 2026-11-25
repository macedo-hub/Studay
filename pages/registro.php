<?php
/*
 * REGISTRO — cria a conta no banco de dados.
 * Validações: empty(), preg_match(), filter_var() e valida_dados() (igual à Avaliação 02).
 */

include "valida_dados.php";
require_once __DIR__ . '/../includes/sessao.php';

iniciar_sessao();

// Padrões usados nas validações
define('PADRAO_VALIDA_NOME', '/^[\p{L}][\p{L}\s\-.]{2,59}$/u');
define('PADRAO_VALIDA_APELIDO', '/^[\p{L}\p{N}_]{3,20}$/u');
define('PADRAO_VALIDA_SENHA', '/^(?=.*[A-Za-z])(?=.*\d).{8,64}$/');

// Variáveis do formulário
$nome = $apelido = $email = $senha = $confirmarSenha = "";
$nome_erro = $apelido_erro = $email_erro = $senha_erro = $confirmarSenha_erro = $erro_geral = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nome = valida_dados($_POST["nome"] ?? "");
    $apelido = valida_dados($_POST["apelido"] ?? "");
    $email = valida_dados($_POST["email"] ?? "");
    $senha = valida_dados($_POST["senha"] ?? "");
    $confirmarSenha = valida_dados($_POST["confirmarSenha"] ?? "");

    // Validação do nome
    if (empty($nome)) {
        $nome_erro = "Informe seu nome.";
    } elseif (!preg_match(PADRAO_VALIDA_NOME, $nome)) {
        $nome_erro = "Use de 3 a 60 caracteres: apenas letras, espaços, ponto e hífen.";
    }

    // Validação do apelido (é o nome que aparece no ranking)
    if (empty($apelido)) {
        $apelido_erro = "Escolha um apelido para o ranking.";
    } elseif (!preg_match(PADRAO_VALIDA_APELIDO, $apelido)) {
        $apelido_erro = "Use de 3 a 20 caracteres: letras, números e _ (sem espaço).";
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

    if (!confere_csrf($_POST['token_csrf'] ?? '')) {
        $erro_geral = "A página ficou aberta por muito tempo. Atualize e tente de novo.";
    }

    // Se o formulário está certo, tenta criar a conta no banco
    if (empty($nome_erro) && empty($apelido_erro) && empty($email_erro) && empty($senha_erro)
        && empty($confirmarSenha_erro) && empty($erro_geral)) {

        try {
            $banco = conectar_banco();

            $existeEmail = $banco->prepare('SELECT COUNT(*) FROM usuarios WHERE email = ?');
            $existeEmail->execute([strtolower($email)]);

            $existeApelido = $banco->prepare('SELECT COUNT(*) FROM usuarios WHERE apelido = ?');
            $existeApelido->execute([$apelido]);

            if ((int) $existeEmail->fetchColumn() > 0) {
                $email_erro = "Já existe uma conta com esse e-mail. Que tal entrar?";
            } elseif ((int) $existeApelido->fetchColumn() > 0) {
                $apelido_erro = "Esse apelido já está em uso. Escolha outro!";
            } else {
                $banco->prepare(
                    'INSERT INTO usuarios (nome, apelido, email, senha_hash, papel, criado_em) VALUES (?, ?, ?, ?, ?, ?)'
                )->execute([
                    $nome, $apelido, strtolower($email),
                    password_hash($senha, PASSWORD_DEFAULT),
                    'aluno', date('Y-m-d H:i:s'),
                ]);

                $consulta = $banco->prepare('SELECT * FROM usuarios WHERE id = ?');
                $consulta->execute([(int) $banco->lastInsertId()]);
                $usuario = aplicar_papel_admin($banco, $consulta->fetch());

                entrar_na_conta($usuario);
                header("Location: metodos.html?bemvindo=1");
                exit;
            }
        } catch (PDOException $erro) {
            // 23000 = valor repetido (duas pessoas criando a mesma conta ao mesmo tempo)
            if ($erro->getCode() === '23000') {
                $erro_geral = "Esse e-mail ou apelido acabou de ser usado. Tente outro.";
            } else {
                error_log('Studay registro: ' . $erro->getMessage());
                $erro_geral = "Estamos com um probleminha para falar com o banco de dados. Tente de novo daqui a pouco.";
            }
        } catch (Throwable $erro) {
            error_log('Studay registro: ' . $erro->getMessage());
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
    <title>Studay &mdash; Criar Conta</title>
    <!-- Pré-visualização de links (Open Graph e Twitter Cards) -->
    <meta name="description" content="Crie sua conta grátis, escolha seu avatar e entre no ranking dos estudos.">
    <link rel="canonical" href="https://studay.site.je/pages/registro.php">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Studay">
    <meta property="og:locale" content="pt_BR">
    <meta property="og:title" content="Criar conta no Studay">
    <meta property="og:description" content="Crie sua conta grátis, escolha seu avatar e entre no ranking dos estudos.">
    <meta property="og:url" content="https://studay.site.je/pages/registro.php">
    <meta property="og:image" content="https://studay.site.je/img/compartilhar/studay-preview.jpg">
    <meta property="og:image:secure_url" content="https://studay.site.je/img/compartilhar/studay-preview.jpg">
    <meta property="og:image:type" content="image/jpeg">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="Studay: o Faísca, mascote do site, ao lado do nome Studay">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Criar conta no Studay">
    <meta name="twitter:description" content="Crie sua conta grátis, escolha seu avatar e entre no ranking dos estudos.">
    <meta name="twitter:image" content="https://studay.site.je/img/compartilhar/studay-preview.jpg">
    <meta name="twitter:image:alt" content="Studay: o Faísca, mascote do site, ao lado do nome Studay">
    <!-- Fim da pré-visualização de links -->
    <link rel="shortcut icon" href="../img/logo/faisca.webp" type="image/x-icon">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/animacoes.css">
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
                <p class="auth-subtitulo">É rapidinho! Com a conta você ganha XP, evolui o Faísca e entra no ranking.</p>

                <?php if (!empty($erro_geral)): ?>
                <p class="form-error visivel" role="alert"><?php echo h($erro_geral); ?></p>
                <?php endif; ?>

                <!-- novalidate: quem valida é o PHP (servidor) -->
                <form id="registroForm" class="auth-form" method="POST"
                      action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" novalidate>

                    <?php echo campo_csrf(); ?>

                    <label for="nome">Nome</label>
                    <input type="text" id="nome" name="nome" value="<?php echo $nome; ?>"
                           placeholder="Seu nome completo" autocomplete="name">
                    <?php if (!empty($nome_erro)): ?>
                    <p class="form-error form-error-campo visivel" role="alert"><?php echo $nome_erro; ?></p>
                    <?php endif; ?>

                    <label for="apelido">Apelido (aparece no ranking)</label>
                    <input type="text" id="apelido" name="apelido" value="<?php echo $apelido; ?>"
                           placeholder="Ex.: faisquinha_10" autocomplete="nickname" maxlength="20">
                    <?php if (!empty($apelido_erro)): ?>
                    <p class="form-error form-error-campo visivel" role="alert"><?php echo $apelido_erro; ?></p>
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
                    <p class="auth-olhadinha"><a href="metodos.html">Só quero dar uma olhada</a></p>
                </div>
            </div>

        </div>
    </div>

    <script src="../js/registro.js"></script>
</body>
</html>
