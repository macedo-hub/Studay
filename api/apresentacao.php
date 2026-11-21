<?php
/*
 * POST api/apresentacao.php
 * Marca que a pessoa já viu a apresentação do Faísca (para ela não aparecer toda hora).
 * Para ver de novo, é só clicar em "Ver apresentação" no menu.
 */

require_once __DIR__ . '/../includes/sessao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder_json(['ok' => false, 'erro' => 'metodo_invalido'], 405);
}

if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') !== 'Studay') {
    responder_json(['ok' => false, 'erro' => 'origem_invalida'], 403);
}

try {
    $usuario = usuario_logado();
    if (!$usuario) {
        responder_json(['ok' => false, 'erro' => 'nao_logado'], 401);
    }

    conectar_banco()->prepare('UPDATE usuarios SET apresentacao_vista = 1 WHERE id = ?')->execute([(int) $usuario['id']]);
    responder_json(['ok' => true]);
} catch (Throwable $erro) {
    // Se a coluna ainda não existe no banco (falta rodar sql/atualizacao_perfil.sql), não quebra nada.
    error_log('Studay api/apresentacao: ' . $erro->getMessage());
    responder_json(['ok' => false, 'erro' => 'erro_no_servidor'], 200);
}
