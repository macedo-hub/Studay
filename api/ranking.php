<?php
/*
 * GET api/ranking.php?periodo=semana   (ou geral)
 * Devolve o ranking em JSON. A tela do ranking chama isso a cada poucos segundos.
 * Só mostra apelido, nível e XP (nunca e-mail ou nome completo).
 */

require_once __DIR__ . '/../includes/sessao.php';
require_once __DIR__ . '/../includes/gamificacao.php';

$periodo = ($_GET['periodo'] ?? 'semana') === 'geral' ? 'geral' : 'semana';

try {
    $usuario = usuario_logado();
    $meuId   = $usuario ? (int) $usuario['id'] : null;
    $banco   = conectar_banco();

    responder_json([
        'ok'      => true,
        'periodo' => $periodo,
        'lista'   => buscar_ranking($banco, $periodo, 20, $meuId),
        'eu'      => $meuId ? minha_posicao($banco, $periodo, $meuId) : null,
    ]);
} catch (Throwable $erro) {
    error_log('Studay api/ranking: ' . $erro->getMessage());
    responder_json(['ok' => false, 'erro' => 'erro_no_servidor'], 500);
}
