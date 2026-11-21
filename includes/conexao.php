<?php
/*
 * Conexão com o banco de dados (usando PDO).
 * Use sempre:  $pdo = conectar_banco();
 * Todas as consultas do site usam "prepared statements" (com ? ou :nome),
 * assim ninguém consegue injetar SQL pelos formulários.
 */

require_once __DIR__ . '/../config/config.php';

function conectar_banco(): PDO
{
    static $conexao = null;

    if ($conexao instanceof PDO) {
        return $conexao;
    }

    $usuario = defined('BANCO_USUARIO') ? BANCO_USUARIO : null;
    $senha   = defined('BANCO_SENHA') ? BANCO_SENHA : null;

    $opcoes = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    // Se der erro, a exceção sobe e cada página decide o que mostrar.
    // (A mensagem real do erro nunca é mostrada para o visitante.)
    $conexao = new PDO(BANCO_DSN, $usuario, $senha, $opcoes);

    return $conexao;
}

/** Retorna true se o banco está funcionando (usado para mostrar avisos amigáveis). */
function banco_disponivel(): bool
{
    try {
        conectar_banco();
        return true;
    } catch (Throwable $erro) {
        error_log('Studay: falha ao conectar no banco - ' . $erro->getMessage());
        return false;
    }
}
