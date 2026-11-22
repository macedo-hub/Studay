<?php
/*
 * POST api/simulado.php   (corpo em JSON, com o campo "acao")
 *
 *   montar              sorteia as questões e começa uma tentativa
 *   conferir            mostra o gabarito de UMA questão (modo "na hora")
 *   gabarito            gabarito de várias questões (usado para imprimir)
 *   duvida              marca ou desmarca uma questão como dúvida
 *   finalizar           corrige tudo, dá XP e guarda o resultado
 *   salvar_preferencias guarda as opções da pessoa na conta
 *
 * Proteções: só POST, só vindo do nosso site (cabeçalho X-Requested-With),
 * o XP é calculado aqui no servidor e cada tentativa só vale uma vez.
 */

require_once __DIR__ . '/../includes/sessao.php';
require_once __DIR__ . '/../includes/gamificacao.php';
require_once __DIR__ . '/../includes/simulado.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder_json(['ok' => false, 'erro' => 'metodo_invalido'], 405);
}
if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') !== 'Studay') {
    responder_json(['ok' => false, 'erro' => 'origem_invalida'], 403);
}

try {
    $dados = json_decode(file_get_contents('php://input'), true);
    if (!is_array($dados)) {
        $dados = [];
    }
    $acao = is_string($dados['acao'] ?? null) ? $dados['acao'] : '';
    $usuario = usuario_logado();
    $banco = null;
    if ($usuario) {
        try { $banco = conectar_banco(); } catch (Throwable $e) { $banco = null; }
    }
    iniciar_sessao();

    switch ($acao) {

        case 'montar': {
            $p = sanear_preferencias($dados['opcoes'] ?? null);
            $salvas = [];
            if ($p['origem'] !== 'todas') {
                if (!$usuario || !$banco) {
                    responder_json(['ok' => false, 'erro' => 'precisa_login', 'mensagem' => 'Entre na sua conta para usar as suas dúvidas e erros.'], 401);
                }
                $salvas = questoes_salvas($banco, (int) $usuario['id'], $p['origem'] === 'duvidas' ? 'duvida' : 'erro');
            }

            $ids = sortear_simulado($p, $salvas);
            if (!$ids) {
                responder_json(['ok' => false, 'erro' => 'sem_questoes',
                    'mensagem' => $p['origem'] === 'todas'
                        ? 'Não achei questões com essas opções. Tente marcar mais anos ou áreas.'
                        : 'Você ainda não tem questões salvas com essas opções.']);
            }

            $todas = todas_as_questoes();
            $questoes = [];
            foreach ($ids as $id) {
                $questoes[] = questao_publica($todas[$id]);
            }

            $duvidas = ($usuario && $banco) ? questoes_salvas($banco, (int) $usuario['id'], 'duvida') : [];

            // Impressão só pega as questões: não começa tentativa (e não atrapalha um simulado em andamento).
            $impressao = !empty($dados['impressao']);
            $tentativa = $impressao ? null : bin2hex(random_bytes(8));
            if (!$impressao) $_SESSION['simulado'] = [
                'tentativa' => $tentativa,
                'ids'       => $ids,
                'inicio'    => time(),
                'travadas'  => [],      // respostas já conferidas na hora (não dá mais para mudar)
                'gabarito_visto' => false,
                'finalizado' => false,
            ];

            responder_json([
                'ok' => true,
                'tentativa' => $tentativa,
                'total' => count($questoes),
                'questoes' => $questoes,
                'duvidas' => array_values(array_intersect($ids, $duvidas)),
                'pedido' => $p['quantidade'],
            ]);
        }

        case 'conferir': {
            $id = is_string($dados['id'] ?? null) ? $dados['id'] : '';
            $resposta = is_string($dados['resposta'] ?? null) ? strtoupper($dados['resposta']) : '';
            $sim = $_SESSION['simulado'] ?? null;
            $todas = todas_as_questoes();
            if (!$sim || $sim['finalizado'] || !in_array($id, $sim['ids'], true) || !isset($todas[$id])) {
                responder_json(['ok' => false, 'erro' => 'questao_invalida'], 400);
            }
            $certa = $todas[$id]['gabarito'];
            if (isset($sim['travadas'][$id])) {
                $resposta = $sim['travadas'][$id];       // já tinha respondido: vale a primeira
            } elseif ($resposta !== '' && preg_match('/^[A-E]$/', $resposta)) {
                $_SESSION['simulado']['travadas'][$id] = $resposta;
            } else {
                $resposta = '';
            }
            responder_json(['ok' => true, 'correta' => $certa, 'resposta' => $resposta, 'acertou' => $resposta === $certa]);
        }

        case 'gabarito': {
            $ids = is_array($dados['ids'] ?? null) ? array_slice($dados['ids'], 0, 180) : [];
            $todas = todas_as_questoes();
            $saida = [];
            foreach ($ids as $id) {
                if (is_string($id) && isset($todas[$id])) {
                    $saida[$id] = $todas[$id]['gabarito'];
                }
            }
            // Quem pediu o gabarito inteiro de uma tentativa em andamento não ganha XP nela.
            if (isset($_SESSION['simulado']) && !$_SESSION['simulado']['finalizado']
                && count(array_intersect($ids, $_SESSION['simulado']['ids'])) > 1) {
                $_SESSION['simulado']['gabarito_visto'] = true;
            }
            responder_json(['ok' => true, 'gabarito' => $saida]);
        }

        case 'duvida': {
            if (!$usuario || !$banco) {
                responder_json(['ok' => true, 'guardado' => false]);
            }
            $id = is_string($dados['id'] ?? null) ? $dados['id'] : '';
            if (!isset(todas_as_questoes()[$id])) {
                responder_json(['ok' => false, 'erro' => 'questao_invalida'], 400);
            }
            $marcar = !empty($dados['marcar']);
            try {
                $banco->prepare('DELETE FROM questoes_salvas WHERE usuario_id = ? AND questao_id = ? AND tipo = ?')
                      ->execute([(int) $usuario['id'], $id, 'duvida']);
                if ($marcar) {
                    $banco->prepare('INSERT INTO questoes_salvas (usuario_id, questao_id, tipo, criado_em) VALUES (?, ?, ?, ?)')
                          ->execute([(int) $usuario['id'], $id, 'duvida', date('Y-m-d H:i:s')]);
                }
            } catch (Throwable $e) {
                error_log('Studay simulado (dúvida): ' . $e->getMessage());
                responder_json(['ok' => true, 'guardado' => false]);
            }
            responder_json(['ok' => true, 'guardado' => true]);
        }

        case 'salvar_preferencias': {
            $p = sanear_preferencias($dados['opcoes'] ?? null);
            if (!$usuario || !$banco) {
                responder_json(['ok' => true, 'guardado' => false]);
            }
            responder_json(['ok' => true, 'guardado' => guardar_preferencias($banco, (int) $usuario['id'], $p)]);
        }

        case 'finalizar': {
            $sim = $_SESSION['simulado'] ?? null;
            if (!$sim) {
                responder_json(['ok' => false, 'erro' => 'sem_tentativa'], 400);
            }
            if ($sim['finalizado']) {
                responder_json(['ok' => false, 'erro' => 'ja_finalizado', 'mensagem' => 'Esse simulado já foi corrigido.'], 400);
            }

            $enviadas = is_array($dados['respostas'] ?? null) ? $dados['respostas'] : [];
            $todas = todas_as_questoes();
            $total = count($sim['ids']);
            $acertos = 0;
            $respondidas = 0;
            $gabarito = [];
            $porArea = [];
            $erros = [];
            $acertadas = [];

            foreach ($sim['ids'] as $id) {
                $q = $todas[$id];
                $certa = $q['gabarito'];
                $gabarito[$id] = $certa;

                $r = $sim['travadas'][$id] ?? ($enviadas[$id] ?? '');
                $r = is_string($r) ? strtoupper($r) : '';
                if (!preg_match('/^[A-E]$/', $r)) $r = '';

                $area = $q['area'];
                if (!isset($porArea[$area])) $porArea[$area] = ['acertos' => 0, 'total' => 0, 'respondidas' => 0];
                $porArea[$area]['total']++;

                if ($r !== '') {
                    $respondidas++;
                    $porArea[$area]['respondidas']++;
                    if ($r === $certa) {
                        $acertos++;
                        $porArea[$area]['acertos']++;
                        $acertadas[] = $id;
                    } else {
                        $erros[] = $id;
                    }
                }
            }

            $duracao = max(0, min(86400, (int) ($dados['duracao'] ?? 0)));
            $passouNoServidor = time() - (int) $sim['inicio'];
            $_SESSION['simulado']['finalizado'] = true;

            $resposta = [
                'ok' => true,
                'total' => $total,
                'respondidas' => $respondidas,
                'acertos' => $acertos,
                'por_area' => $porArea,
                'gabarito' => $gabarito,
                'logado' => (bool) $usuario,
                'ganhou' => 0,
                'motivo' => '',
                'mensagem' => '',
                'rotulo' => ROTULOS_XP['simulado'] ?? 'Simulado',
                'subiu_de_nivel' => false,
                'conquistas_novas' => [],
            ];

            if ($usuario && $banco) {
                $uid = (int) $usuario['id'];
                $agora = date('Y-m-d H:i:s');
                try {
                    $banco->prepare('INSERT INTO resultados_simulado (usuario_id, total, respondidas, acertos, duracao_seg, por_area, criado_em) VALUES (?, ?, ?, ?, ?, ?, ?)')
                          ->execute([$uid, $total, $respondidas, $acertos, $duracao ?: $passouNoServidor, json_encode($porArea), $agora]);

                    // Caderno de erros: guarda o que errou e tira o que acertou agora.
                    foreach ($erros as $id) {
                        $banco->prepare('DELETE FROM questoes_salvas WHERE usuario_id = ? AND questao_id = ? AND tipo = ?')->execute([$uid, $id, 'erro']);
                        $banco->prepare('INSERT INTO questoes_salvas (usuario_id, questao_id, tipo, criado_em) VALUES (?, ?, ?, ?)')->execute([$uid, $id, 'erro', $agora]);
                    }
                    foreach ($acertadas as $id) {
                        $banco->prepare('DELETE FROM questoes_salvas WHERE usuario_id = ? AND questao_id = ? AND tipo = ?')->execute([$uid, $id, 'erro']);
                    }
                } catch (Throwable $e) {
                    error_log('Studay simulado (resultado): ' . $e->getMessage());
                }

                // XP: só vale se a pessoa respondeu de verdade e o gabarito não foi aberto antes.
                $xp = 0;
                if ($respondidas < 5) {
                    $resposta['motivo'] = 'poucas_respostas';
                    $resposta['mensagem'] = 'Responda pelo menos 5 questões para ganhar XP no simulado.';
                } elseif (!empty($sim['gabarito_visto'])) {
                    $resposta['motivo'] = 'gabarito_visto';
                    $resposta['mensagem'] = 'Você abriu o gabarito antes de terminar, então esse simulado não rendeu XP.';
                } elseif ($passouNoServidor < $respondidas * 6) {
                    $resposta['motivo'] = 'rapido_demais';
                    $resposta['mensagem'] = 'Esse simulado foi rápido demais para render XP. Leia as questões com calma.';
                } else {
                    $xp = xp_do_simulado($acertos, $total);
                }

                $antes = usuario_por_id($banco, $uid);
                if ($xp > 0) {
                    $resumo = registrar_xp($banco, $uid, 'simulado', $xp);
                    $resposta = $resumo + $resposta;
                    $resposta['ganhou'] = $resumo['ganhou'];
                    $resposta['mensagem'] = $resumo['mensagem'];
                    $resposta['motivo'] = $resumo['motivo'];
                    $resposta['subiu_de_nivel'] = $resumo['subiu_de_nivel'];
                    $resposta['conquistas_novas'] = $resumo['conquistas_novas'];
                } else {
                    $resposta['conquistas_novas'] = verificar_conquistas($banco, $uid);
                }
                $depois = usuario_por_id($banco, $uid);
                if ($depois) {
                    $resposta['estado'] = estado_do_usuario($depois);
                }
            }

            responder_json($resposta);
        }

        default:
            responder_json(['ok' => false, 'erro' => 'acao_invalida'], 400);
    }
} catch (Throwable $erro) {
    error_log('Studay api/simulado: ' . $erro->getMessage());
    responder_json(['ok' => false, 'erro' => 'erro_no_servidor'], 500);
}
