<?php
/*
 * GET api/estado.php?pagina=pomodoro
 * Diz ao navegador quem está logado, quanto XP tem, em qual nível está e qual é o avatar.
 * Também entrega o bônus de "entrou hoje" (5 XP, uma vez por dia) e a dica de XP da página.
 */

require_once __DIR__ . '/../includes/sessao.php';
require_once __DIR__ . '/../includes/gamificacao.php';

// Dica de XP da página (a pessoa vê mesmo sem estar logada)
$pagina = is_string($_GET['pagina'] ?? null) ? $_GET['pagina'] : '';
$dica = DICAS_XP[$pagina] ?? '';

try {
    $usuario = usuario_logado();

    if (!$usuario) {
        responder_json(['logado' => false, 'dica_xp' => $dica]);
    }

    $banco = conectar_banco();
    $bonus = registrar_xp($banco, (int) $usuario['id'], 'login_diario', 5);

    // Busca de novo, porque o XP pode ter mudado agora há pouco.
    $atual = usuario_por_id($banco, (int) $usuario['id']);

    $resposta = estado_do_usuario($atual);
    $resposta['dica_xp']           = $dica;
    $resposta['nivel_antes']       = calcular_nivel(max(0, $resposta['xp_total'] - (int) $bonus['ganhou']));
    $resposta['bonus_login']       = $bonus['ganhou'];
    $resposta['rotulo_bonus']      = $bonus['rotulo'];
    $resposta['subiu_de_nivel']    = $bonus['subiu_de_nivel'];
    $resposta['conquistas_novas']  = $bonus['conquistas_novas'];

    responder_json($resposta);
} catch (Throwable $erro) {
    error_log('Studay api/estado: ' . $erro->getMessage());
    responder_json(['logado' => false, 'dica_xp' => $dica, 'erro' => 'banco_indisponivel'], 200);
}
