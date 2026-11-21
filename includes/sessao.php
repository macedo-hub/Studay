<?php
/*
 * Sessão de login (quem está logado agora) e proteção contra CSRF.
 */

require_once __DIR__ . '/funcoes.php';
require_once __DIR__ . '/conexao.php';

function iniciar_sessao(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_name('studay_sessao');
        session_set_cookie_params([
            'lifetime' => 60 * 60 * 24 * 14,   // lembra por 14 dias
            'path'     => '/',
            'secure'   => eh_https(),
            'httponly' => true,                // o JavaScript não consegue ler o cookie
            'samesite' => 'Lax',
        ]);
        session_start();
    }
}

/** Devolve os dados de quem está logado, ou null se ninguém estiver. */
function usuario_logado(): ?array
{
    static $cache = false;

    iniciar_sessao();

    if (empty($_SESSION['usuario_id'])) {
        return null;
    }

    if ($cache !== false) {
        return $cache;
    }

    try {
        // SELECT * para continuar funcionando mesmo se o banco ainda não tiver as colunas novas (avatar etc.)
        $consulta = conectar_banco()->prepare('SELECT * FROM usuarios WHERE id = ?');
        $consulta->execute([(int) $_SESSION['usuario_id']]);
        $usuario = $consulta->fetch();
    } catch (Throwable $erro) {
        error_log('Studay: erro ao buscar usuário - ' . $erro->getMessage());
        return null;
    }

    if (!$usuario) {
        // Conta apagada: derruba a sessão.
        $_SESSION = [];
        return null;
    }

    $cache = $usuario;
    return $usuario;
}

/** Marca a pessoa como logada. */
function entrar_na_conta(array $usuario): void
{
    iniciar_sessao();
    session_regenerate_id(true);   // troca o ID da sessão (evita "session fixation")
    $_SESSION['usuario_id'] = (int) $usuario['id'];
}

function sair_da_conta(): void
{
    iniciar_sessao();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 3600, $p['path'], $p['domain'] ?? '', $p['secure'], $p['httponly']);
    }
    session_destroy();
}

/** Página que só quem está logado pode ver. */
function exige_login(): array
{
    $usuario = usuario_logado();
    if (!$usuario) {
        header('Location: login.php?aviso=entre');
        exit;
    }
    return $usuario;
}

/** Página que só administrador pode ver. */
function exige_admin(): array
{
    $usuario = exige_login();
    if ($usuario['papel'] !== 'admin') {
        http_response_code(403);
        echo '<!DOCTYPE html><meta charset="UTF-8"><title>Acesso negado</title>'
           . '<p style="font-family:sans-serif;padding:2rem">Esta área é só para a equipe do Studay. '
           . '<a href="metodos.html">Voltar</a></p>';
        exit;
    }
    return $usuario;
}

/** Token escondido nos formulários. Garante que o envio veio do nosso site. */
function token_csrf(): string
{
    iniciar_sessao();
    if (empty($_SESSION['token_csrf'])) {
        $_SESSION['token_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['token_csrf'];
}

function confere_csrf($token): bool
{
    iniciar_sessao();
    return is_string($token)
        && !empty($_SESSION['token_csrf'])
        && hash_equals($_SESSION['token_csrf'], $token);
}

/** Campo <input hidden> pronto para colocar dentro do <form>. */
function campo_csrf(): string
{
    return '<input type="hidden" name="token_csrf" value="' . h(token_csrf()) . '">';
}

/** Se o e-mail está na lista de admins do config.php, vira admin. */
function aplicar_papel_admin(PDO $banco, array $usuario): array
{
    $email = strtolower($usuario['email']);
    $deveSerAdmin = in_array($email, array_map('strtolower', EMAILS_ADMIN), true);

    if ($deveSerAdmin && $usuario['papel'] !== 'admin') {
        $banco->prepare('UPDATE usuarios SET papel = ? WHERE id = ?')->execute(['admin', $usuario['id']]);
        $usuario['papel'] = 'admin';
    }
    return $usuario;
}
