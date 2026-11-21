<?php
/*
 * POST api/xp.php
 * O navegador avisa "terminei uma coisa" e o servidor decide quanto XP vale.
 *
 * Campos (JSON):
 *   acao    = "iniciar" ou "concluir"
 *   tipo    = pomodoro | faisca | flashcard_certo | resumo_criado
 *   minutos = duração da sessão (só para pomodoro e faisca)
 *
 * Proteções:
 *  - só aceita POST com o cabeçalho X-Requested-With: Studay (outro site não consegue mandar)
 *  - para Pomodoro e Faísca, o servidor confere se o tempo realmente passou
 *  - cada tipo tem limite de XP por dia (veja REGRAS_XP em includes/gamificacao.php)
 */

require_once __DIR__ . '/../includes/sessao.php';
require_once __DIR__ . '/../includes/gamificacao.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder_json(['ok' => false, 'erro' => 'metodo_invalido'], 405);
}

if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') !== 'Studay') {
    responder_json(['ok' => false, 'erro' => 'origem_invalida'], 403);
}

try {
    $usuario = usuario_logado();
    if (!$usuario) {
        responder_json(['ok' => false, 'logado' => false, 'erro' => 'nao_logado'], 401);
    }

    $dados = json_decode(file_get_contents('php://input'), true);
    if (!is_array($dados)) {
        $dados = $_POST;
    }

    $acao    = is_string($dados['acao'] ?? null) ? $dados['acao'] : 'concluir';
    $tipo    = is_string($dados['tipo'] ?? null) ? $dados['tipo'] : '';
    $minutos = filter_var($dados['minutos'] ?? 0, FILTER_VALIDATE_INT);
    $minutos = $minutos === false ? 0 : $minutos;

    $permitidos = ['pomodoro', 'faisca', 'flashcard_certo', 'resumo_criado'];
    if (!in_array($tipo, $permitidos, true)) {
        responder_json(['ok' => false, 'erro' => 'tipo_invalido'], 400);
    }

    iniciar_sessao();
    $temTimer = in_array($tipo, ['pomodoro', 'faisca'], true);

    // O navegador avisa que o cronômetro começou. Guardamos a hora no servidor.
    if ($acao === 'iniciar') {
        if ($temTimer) {
            $_SESSION['inicio_sessao'][$tipo] = time();
        }
        responder_json(['ok' => true]);
    }

    // Evita mandar a mesma ação várias vezes por segundo.
    $ultima = $_SESSION['ultimo_envio'][$tipo] ?? 0;
    if (time() - $ultima < 1) {
        responder_json(['ok' => true, 'ganhou' => 0, 'motivo' => 'muito_rapido']);
    }
    $_SESSION['ultimo_envio'][$tipo] = time();

    // Calcula o XP de cada tipo
    $xp = 0;
    switch ($tipo) {
        case 'pomodoro':
            if ($minutos < 5 || $minutos > 60) {
                responder_json(['ok' => true, 'ganhou' => 0, 'motivo' => 'tempo_fora_do_limite', 'mensagem' => mensagem_sem_xp('tempo_fora_do_limite', $tipo)]);
            }
            $xp = (int) round($minutos * 0.6);
            break;

        case 'faisca':
            if ($minutos < 10 || $minutos > 240) {
                responder_json(['ok' => true, 'ganhou' => 0, 'motivo' => 'tempo_fora_do_limite', 'mensagem' => mensagem_sem_xp('tempo_fora_do_limite', $tipo)]);
            }
            $xp = min(40, (int) round($minutos / 2));
            break;

        case 'flashcard_certo':
            $xp = 2;
            break;

        case 'resumo_criado':
            $xp = 5;
            break;
    }

    // Para os cronômetros: confere se o tempo de verdade passou (aceita 10% de folga)
    if ($temTimer) {
        $inicio = $_SESSION['inicio_sessao'][$tipo] ?? 0;
        $passou = time() - $inicio;
        unset($_SESSION['inicio_sessao'][$tipo]);

        if ($inicio === 0 || $passou < $minutos * 60 * 0.9) {
            responder_json(['ok' => true, 'ganhou' => 0, 'motivo' => 'tempo_nao_passou', 'mensagem' => mensagem_sem_xp('tempo_nao_passou', $tipo)]);
        }
    }

    $resumo = registrar_xp(conectar_banco(), (int) $usuario['id'], $tipo, $xp);

    $atual = usuario_por_id(conectar_banco(), (int) $usuario['id']);

    responder_json(['ok' => true] + $resumo + ['estado' => estado_do_usuario($atual)]);
} catch (Throwable $erro) {
    error_log('Studay api/xp: ' . $erro->getMessage());
    responder_json(['ok' => false, 'erro' => 'erro_no_servidor'], 500);
}
