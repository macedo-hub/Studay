<?php
/*
 * SIMULADO DO ENEM
 * Esta página só desenha a estrutura. O sorteio, a correção e o XP ficam em api/simulado.php,
 * e o funcionamento na tela fica em js/simulado.js.
 */
require_once __DIR__ . '/../includes/sessao.php';
require_once __DIR__ . '/../includes/gamificacao.php';
require_once __DIR__ . '/../includes/simulado.php';
require_once __DIR__ . '/../includes/menu.php';

$prefs     = PREFERENCIAS_PADRAO;
$logado    = false;
$n_duvidas = 0;
$n_erros   = 0;
$historico = [];

try {
    $usuario = usuario_logado();
    if ($usuario) {
        $logado = true;
        $banco = conectar_banco();
        $prefs = preferencias_do_usuario($banco, (int) $usuario['id']) ?? $prefs;
        $n_duvidas = count(questoes_salvas($banco, (int) $usuario['id'], 'duvida'));
        $n_erros   = count(questoes_salvas($banco, (int) $usuario['id'], 'erro'));
    }
} catch (Throwable $e) {
    error_log('Studay simulado: ' . $e->getMessage());
}

// Atalho vindo do Modo ENEM: simulado.php?qtd=25
$qtd_url = filter_input(INPUT_GET, 'qtd', FILTER_VALIDATE_INT);
if ($qtd_url !== false && $qtd_url !== null && in_array($qtd_url, QUANTIDADES_SIMULADO, true)) {
    $prefs['quantidade'] = $qtd_url;
}

$contagem = contagem_de_questoes();
$dados_pagina = [
    'prefs'     => $prefs,
    'padrao'    => PREFERENCIAS_PADRAO,
    'contagem'  => $contagem,
    'logado'    => $logado,
    'duvidas'   => $n_duvidas,
    'erros'     => $n_erros,
    'veioDaUrl' => $qtd_url !== false && $qtd_url !== null,
    'areas'     => array_map(fn($a) => ['nome' => $a['nome'], 'curto' => $a['curto'], 'dia' => $a['dia']], AREAS_SIMULADO),
];
$json_pagina = json_encode($dados_pagina, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);

function total_do_ano(array $contagem, int $ano): int
{
    return array_sum($contagem[$ano] ?? []);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Studay — Simulado do ENEM</title>
    <link rel="shortcut icon" href="../img/logo/faisca.webp" type="image/x-icon">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/simulado.css">
    <link rel="stylesheet" href="../css/gamificacao.css">
    <link rel="stylesheet" href="../css/animacoes.css">
</head>
<body data-xp-pagina="simulado">
<div class="main-container">

<?php desenhar_menu('enem'); ?>

    <main class="content sim-content">

        <!-- ============ 1. ESCOLHER AS OPÇÕES ============ -->
        <section id="sim-config" class="sim-tela" aria-labelledby="sim-titulo">
            <a href="modoENEM.php" class="sim-voltar">&larr; Modo ENEM</a>
            <h1 class="page-title" id="sim-titulo">Simulado do ENEM</h1>

            <div id="sim-retomar" class="sim-retomar hidden" role="status">
                <div>
                    <strong>Você tem um simulado em andamento.</strong>
                    <span id="sim-retomar-info"></span>
                </div>
                <div class="sim-retomar-botoes">
                    <button type="button" class="sim-btn sim-btn-claro" id="sim-descartar">Descartar</button>
                    <button type="button" class="sim-btn sim-btn-forte" id="sim-continuar">Continuar</button>
                </div>
            </div>

            <form id="sim-form" class="sim-form" autocomplete="off" novalidate>

                <fieldset class="sim-bloco">
                    <legend><span class="sim-num">1</span> Quantas questões?</legend>
                    <div class="sim-pilulas" role="radiogroup" aria-label="Quantidade de questões" id="grupo-quantidade">
                        <?php foreach ([10 => '10', 25 => '25', 45 => '45', 90 => '90 (um dia)', 180 => '180 (prova toda)'] as $n => $rotulo): ?>
                        <button type="button" class="sim-pilula" role="radio" data-valor="<?= $n ?>"><?= h($rotulo) ?></button>
                        <?php endforeach; ?>
                    </div>
                    <label class="sim-campo-linha">
                        <span>Ou escolha o número:</span>
                        <input type="number" id="campo-quantidade" min="1" max="180" inputmode="numeric" class="sim-numero">
                    </label>
                </fieldset>

                <fieldset class="sim-bloco">
                    <legend><span class="sim-num">2</span> De onde vêm as questões?</legend>

                    <p class="sim-rotulo">Provas</p>
                    <div class="sim-pilulas" id="grupo-anos">
                        <?php foreach (ANOS_SIMULADO as $ano): ?>
                        <button type="button" class="sim-pilula sim-marca" aria-pressed="false" data-ano="<?= $ano ?>">
                            ENEM <?= $ano ?> <small><?= total_do_ano($contagem, $ano) ?> questões</small>
                        </button>
                        <?php endforeach; ?>
                    </div>

                    <p class="sim-rotulo">Áreas <span class="sim-atalhos"><button type="button" data-areas="todas">todas</button><button type="button" data-areas="dia1">só dia 1</button><button type="button" data-areas="dia2">só dia 2</button></span></p>
                    <div class="sim-pilulas" id="grupo-areas">
                        <?php foreach (AREAS_SIMULADO as $codigo => $area): ?>
                        <button type="button" class="sim-pilula sim-marca" aria-pressed="false" data-area="<?= h($codigo) ?>">
                            <?= h($area['curto']) ?> <small>dia <?= (int) $area['dia'] ?></small>
                        </button>
                        <?php endforeach; ?>
                    </div>

                    <p class="sim-rotulo">Como misturar</p>
                    <div class="sim-opcoes" id="grupo-modo" role="radiogroup">
                        <label class="sim-opcao"><input type="radio" name="modo" value="misturado"><span><strong>Misturar os anos</strong><em>Pega questões de todos os anos marcados.</em></span></label>
                        <label class="sim-opcao"><input type="radio" name="modo" value="mesmo_ano"><span><strong>Tudo do mesmo ano</strong><em>Sorteia um ano e usa só questões dele.</em></span></label>
                    </div>

                    <p class="sim-rotulo">Quais questões usar</p>
                    <div class="sim-opcoes" id="grupo-origem" role="radiogroup">
                        <label class="sim-opcao"><input type="radio" name="origem" value="todas"><span><strong>Todas</strong><em>Questões novas sorteadas.</em></span></label>
                        <label class="sim-opcao" id="opcao-duvidas"><input type="radio" name="origem" value="duvidas"><span><strong>Minhas dúvidas</strong><em id="texto-duvidas"></em></span></label>
                        <label class="sim-opcao" id="opcao-erros"><input type="radio" name="origem" value="erros"><span><strong>As que eu errei</strong><em id="texto-erros"></em></span></label>
                    </div>

                    <div class="sim-linha-dupla">
                        <label class="sim-campo">
                            <span class="sim-rotulo">Idioma das questões 1 a 5</span>
                            <select id="campo-idioma" class="sim-select">
                                <option value="ingles">Inglês</option>
                                <option value="espanhol">Espanhol</option>
                            </select>
                        </label>
                        <label class="sim-chave">
                            <input type="checkbox" id="campo-embaralhar">
                            <span class="sim-chave-visual" aria-hidden="true"></span>
                            <span>Embaralhar a ordem<small id="texto-embaralhar"></small></span>
                        </label>
                    </div>
                </fieldset>

                <fieldset class="sim-bloco">
                    <legend><span class="sim-num">3</span> Quando ver o gabarito?</legend>
                    <div class="sim-opcoes" id="grupo-resposta" role="radiogroup">
                        <label class="sim-opcao"><input type="radio" name="resposta" value="final"><span><strong>Só no final</strong><em>Igual à prova de verdade. Você pode mudar de resposta até terminar.</em></span></label>
                        <label class="sim-opcao"><input type="radio" name="resposta" value="imediata"><span><strong>Na hora</strong><em>Depois de confirmar cada questão, você já vê se acertou.</em></span></label>
                    </div>
                </fieldset>

                <fieldset class="sim-bloco">
                    <legend><span class="sim-num">4</span> Tempo</legend>
                    <div class="sim-opcoes" id="grupo-tempo" role="radiogroup">
                        <label class="sim-opcao"><input type="radio" name="tempo" value="nenhum"><span><strong>Sem limite</strong><em>Estude no seu ritmo.</em></span></label>
                        <label class="sim-opcao"><input type="radio" name="tempo" value="por_questao"><span><strong>Tempo por questão</strong><em>Acabou o tempo, passa para a próxima.</em></span></label>
                        <label class="sim-opcao"><input type="radio" name="tempo" value="total"><span><strong>Tempo total</strong><em>Quando o relógio zera, o simulado termina.</em></span></label>
                    </div>
                    <label class="sim-campo-linha hidden" id="linha-minutos-questao">
                        <span>Minutos por questão:</span>
                        <input type="number" id="campo-minutos-questao" min="1" max="15" inputmode="numeric" class="sim-numero">
                    </label>
                    <label class="sim-campo-linha hidden" id="linha-minutos-total">
                        <span>Minutos no total:</span>
                        <input type="number" id="campo-minutos-total" min="5" max="330" inputmode="numeric" class="sim-numero">
                        <button type="button" class="sim-link" id="sugerir-tempo">usar 3 min por questão</button>
                    </label>
                    <label class="sim-chave">
                        <input type="checkbox" id="campo-cronometro">
                        <span class="sim-chave-visual" aria-hidden="true"></span>
                        <span>Mostrar o cronômetro na tela<small>O tempo que já passou desde o começo.</small></span>
                    </label>
                </fieldset>

                <div class="sim-resumo" id="sim-resumo" aria-live="polite"></div>
                <p class="sim-aviso hidden" id="sim-erro" role="alert"></p>

                <div class="sim-acoes">
                    <button type="button" class="sim-btn sim-btn-claro" id="sim-imprimir">Imprimir a prova</button>
                    <button type="submit" class="sim-btn sim-btn-forte" id="sim-comecar">Começar o simulado</button>
                </div>
                <p class="sim-nota-salvo" id="sim-nota-salvo"></p>
            </form>
        </section>

        <!-- ============ 2. FAZENDO A PROVA ============ -->
        <section id="sim-prova" class="sim-tela hidden" aria-label="Simulado em andamento">
            <header class="sim-topo">
                <div class="sim-topo-info">
                    <strong id="sim-posicao">Questão 1 de 10</strong>
                    <span id="sim-origem-questao" class="sim-origem"></span>
                </div>
                <div class="sim-relogios">
                    <span class="sim-relogio hidden" id="sim-relogio-restante" title="Tempo que falta"><svg class="icone" viewBox="0 0 24 24" aria-hidden="true"><line x1="10" y1="2" x2="14" y2="2"/><line x1="12" y1="14" x2="15" y2="11"/><circle cx="12" cy="14" r="8"/></svg><b id="sim-tempo-restante">00:00</b><i id="sim-tempo-rotulo">restam</i></span>
                    <span class="sim-relogio hidden" id="sim-relogio-corrido" title="Tempo desde o começo"><svg class="icone" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg><b id="sim-tempo-corrido">00:00</b></span>
                </div>
                <button type="button" class="sim-btn sim-btn-pequeno sim-btn-claro" id="sim-finalizar-topo">Finalizar</button>
            </header>
            <div class="sim-progresso" aria-hidden="true"><i id="sim-progresso-barra"></i></div>

            <div class="sim-palco">
                <article class="sim-questao" id="sim-questao" aria-live="polite">
                    <div class="sim-questao-cabeca">
                        <div class="sim-meta" id="sim-meta"></div>
                        <div class="sim-ferramentas">
                            <button type="button" class="sim-ferr" id="ferr-duvida" aria-pressed="false" title="Marcar como dúvida (tecla D)">
                                <svg class="icone" viewBox="0 0 24 24" aria-hidden="true"><path d="M4 22V4"/><path d="M4 4h13l-2 4 2 4H4"/></svg><span>Dúvida</span>
                            </button>
                            <button type="button" class="sim-ferr" id="ferr-marca" aria-pressed="false" title="Marca-texto: ligue e selecione um trecho">
                                <svg class="icone" viewBox="0 0 24 24" aria-hidden="true"><path d="M9 11l-6 6v3h9l3-3"/><path d="M22 12l-4.6 4.6a2 2 0 0 1-2.8 0l-5.2-5.2a2 2 0 0 1 0-2.8L14 4"/></svg><span>Marca-texto</span>
                            </button>
                        </div>
                    </div>
                    <div class="sim-corpo" id="sim-corpo"></div>
                    <ol class="sim-alternativas" id="sim-alternativas"></ol>
                    <div class="sim-feedback hidden" id="sim-feedback" role="status"></div>
                    <p class="sim-tempo-acabou hidden" id="sim-tempo-acabou">O tempo dessa questão acabou.</p>
                    <div class="sim-confirmar hidden" id="sim-confirmar-linha">
                        <button type="button" class="sim-btn sim-btn-forte" id="sim-confirmar" disabled>Confirmar resposta</button>
                    </div>
                </article>

                <aside class="sim-mapa-caixa" id="sim-mapa-caixa" aria-label="Mapa das questões">
                    <h2>Mapa das questões</h2>
                    <div class="sim-mapa" id="sim-mapa"></div>
                    <ul class="sim-legenda">
                        <li><i class="m-resp"></i>respondida</li>
                        <li><i class="m-duvida"></i>dúvida</li>
                        <li><i class="m-atual"></i>você está aqui</li>
                    </ul>
                </aside>
            </div>

            <nav class="sim-rodape" aria-label="Navegar entre as questões">
                <button type="button" class="sim-btn sim-btn-claro" id="sim-anterior">&larr; Anterior</button>
                <button type="button" class="sim-btn sim-btn-claro sim-so-celular" id="sim-abrir-mapa">Mapa</button>
                <button type="button" class="sim-btn sim-btn-forte" id="sim-proxima">Próxima &rarr;</button>
            </nav>
        </section>

        <!-- ============ 3. RESULTADO ============ -->
        <section id="sim-resultado" class="sim-tela hidden" aria-labelledby="sim-res-titulo">
            <h1 class="page-title" id="sim-res-titulo">Resultado</h1>

            <div class="sim-res-topo">
                <div class="sim-res-nota"><strong id="res-acertos">0</strong><span id="res-total">de 0</span></div>
                <div class="sim-res-textos">
                    <p id="res-frase" class="sim-res-frase"></p>
                    <p id="res-detalhes" class="sim-res-detalhes"></p>
                    <p id="res-xp" class="sim-res-xp hidden"></p>
                </div>
            </div>

            <div class="sim-res-bloco">
                <h2>Por área</h2>
                <div id="res-areas" class="sim-res-areas"></div>
            </div>

            <div class="sim-res-bloco">
                <h2>Suas respostas</h2>
                <p class="sim-res-dica">Toque em um número para rever a questão e ver o gabarito.</p>
                <div id="res-lista" class="sim-mapa sim-mapa-resultado"></div>
                <ul class="sim-legenda">
                    <li><i class="m-certa"></i>acertou</li>
                    <li><i class="m-errada"></i>errou</li>
                    <li><i class="m-branca"></i>em branco</li>
                    <li><i class="m-duvida"></i>dúvida</li>
                </ul>
            </div>

            <p class="sim-aviso hidden" id="res-aviso-login">
                <a href="login.php?voltar=simulado.php">Entre na sua conta</a> para ganhar XP e guardar o histórico, as dúvidas e os erros.
            </p>

            <div class="sim-acoes sim-acoes-resultado">
                <button type="button" class="sim-btn sim-btn-claro" id="res-imprimir">Imprimir com gabarito</button>
                <button type="button" class="sim-btn sim-btn-claro hidden" id="res-refazer-erros">Refazer só as que errei</button>
                <button type="button" class="sim-btn sim-btn-forte" id="res-novo">Fazer outro simulado</button>
            </div>
        </section>

        <!-- ============ 4. PRÉVIA DE IMPRESSÃO ============ -->
        <section id="sim-previa" class="sim-tela sim-tela-previa hidden" aria-label="Prova para imprimir">
            <div class="sim-previa-barra">
                <button type="button" class="sim-btn sim-btn-claro sim-btn-pequeno" id="prev-voltar">&larr; Voltar</button>
                <label class="sim-check"><input type="checkbox" id="prev-cartao" checked> Cartão-resposta</label>
                <label class="sim-check"><input type="checkbox" id="prev-gabarito"> Gabarito no final</label>
                <button type="button" class="sim-btn sim-btn-forte sim-btn-pequeno" id="prev-imprimir">Imprimir</button>
            </div>
            <div id="sim-papel" class="sim-papel"></div>
        </section>

    </main>
</div>

<dialog id="sim-dialogo" class="sim-dialogo">
    <form method="dialog">
        <h2 id="sim-dialogo-titulo"></h2>
        <p id="sim-dialogo-texto"></p>
        <div class="sim-dialogo-botoes">
            <button value="cancelar" class="sim-btn sim-btn-claro" id="sim-dialogo-nao">Voltar</button>
            <button value="ok" class="sim-btn sim-btn-forte" id="sim-dialogo-sim">Finalizar</button>
        </div>
    </form>
</dialog>

<div id="sim-flutuante" class="sim-flutuante hidden" role="toolbar" aria-label="Marca-texto">
    <button type="button" id="flut-marcar">Marcar</button>
    <button type="button" id="flut-limpar">Tirar marca</button>
</div>

<script type="application/json" id="sim-dados"><?= $json_pagina ?></script>
<script src="../js/navigation.js"></script>
<script src="../js/gamificacao.js"></script>
<script src="../js/simulado.js"></script>
<script src="../js/animacoes.js"></script>
</body>
</html>
