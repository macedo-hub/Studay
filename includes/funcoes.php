<?php
/*
 * Funções pequenas que o site inteiro usa.
 */

/** Escreve um texto na tela com segurança (evita HTML/JavaScript Injection). */
function h($texto): string
{
    return htmlspecialchars((string) $texto, ENT_QUOTES, 'UTF-8');
}

/** Limpa um texto vindo de formulário: tira espaços e barras. (Não escapa HTML: isso é feito ao exibir, com h().) */
function limpa_texto($valor): string
{
    if (!is_string($valor)) {
        return '';
    }
    return trim(stripslashes($valor));
}

/** Confere se o site está rodando em HTTPS. */
function eh_https(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
}

/** Responde em JSON e encerra (usado nas pastas /api). */
function responder_json(array $dados, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-store');
    echo json_encode($dados, JSON_UNESCAPED_UNICODE);
    exit;
}

/** Só aceita links que começam com http:// ou https:// (evita "javascript:" e outros). */
function link_seguro(string $url): bool
{
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        return false;
    }
    $esquema = strtolower((string) parse_url($url, PHP_URL_SCHEME));
    return $esquema === 'http' || $esquema === 'https';
}

/** Tamanho de texto contando letras (funciona com acentos). */
function tamanho_texto(string $texto): int
{
    return function_exists('mb_strlen') ? mb_strlen($texto, 'UTF-8') : strlen($texto);
}

/** Mostra "há 3 min", "há 2 h"... */
function tempo_atras(string $dataHora): string
{
    $segundos = time() - strtotime($dataHora);
    if ($segundos < 60) return 'agora mesmo';
    if ($segundos < 3600) return 'há ' . floor($segundos / 60) . ' min';
    if ($segundos < 86400) return 'há ' . floor($segundos / 3600) . ' h';
    return 'há ' . floor($segundos / 86400) . ' dia(s)';
}
