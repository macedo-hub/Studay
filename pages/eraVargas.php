<?php

$gabarito = ['q1' => 'b', 'q2' => 'd', 'q3' => 'c', 'q4' => 'a', 'q5' => 'e'];

$quiz_enviado    = false;
$acertos         = 0;
$total           = count($gabarito);
$respostas       = [];
$feedback_msg    = '';
$feedback_classe = '';
$nivel_titulo    = '';

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quiz_submit'])) {
        $quiz_enviado = true;

    if (isset($_POST['q1'])) {
        $respostas['q1'] = $_POST['q1'];
    } else {
        $respostas['q1'] = '';
    }

    if (isset($_POST['q2'])) {
        $respostas['q2'] = $_POST['q2'];
    } else {
        $respostas['q2'] = '';
    }

    if (isset($_POST['q3'])) {
        $respostas['q3'] = $_POST['q3'];
    } else {
        $respostas['q3'] = '';
    }

    if (isset($_POST['q4'])) {
        $respostas['q4'] = $_POST['q4'];
    } else {
        $respostas['q4'] = '';
    }

    if (isset($_POST['q5'])) {
        $respostas['q5'] = $_POST['q5'];
    } else {
        $respostas['q5'] = '';
    }

    if ($respostas['q1'] === $gabarito['q1']) { $acertos += 1; }
    if ($respostas['q2'] === $gabarito['q2']) { $acertos += 1; }
    if ($respostas['q3'] === $gabarito['q3']) { $acertos += 1; }
    if ($respostas['q4'] === $gabarito['q4']) { $acertos += 1; }
    if ($respostas['q5'] === $gabarito['q5']) { $acertos += 1; }

    $percentual       = ($acertos / $total) * 100;
    $resto_validacao  = $acertos % 2;

    switch ($acertos) {
        case 5: $nivel_titulo = 'Excelente desempenho';      break;
        case 4: $nivel_titulo = 'Ótimo desempenho';          break;
        case 3: $nivel_titulo = 'Bom desempenho';            break;
        case 2: $nivel_titulo = 'Desempenho regular';        break;
        case 1: $nivel_titulo = 'Desempenho insuficiente';   break;
        case 0: $nivel_titulo = 'Necessita de revisão';      break;
        default: $nivel_titulo = 'Resultado do Quiz';
    }

    if ($percentual >= 80 || $acertos === 5) {
        $feedback_msg    = "Você acertou $acertos de $total questões ($percentual%).";
        $feedback_classe = 'feedback-sucesso';
    } else if ($percentual >= 60 || $acertos === 3) {
        $feedback_msg    = "Você acertou $acertos de $total questões ($percentual%).";
        $feedback_classe = 'feedback-aviso';
    } else {
        $feedback_msg    = "Você acertou $acertos de $total questões ($percentual%).";
        $feedback_classe = 'feedback-erro';
    }
}

// ---- Gamificação: guarda o resultado e dá XP (só para quem está logado) ----
require_once __DIR__ . '/../includes/sessao.php';
require_once __DIR__ . '/../includes/gamificacao.php';
require_once __DIR__ . '/../includes/menu.php';

$xp_quiz_ganho  = 0;
$quiz_logado    = false;
$evento_xp_json = 'null';

if ($quiz_enviado) {
    try {
        $usuario_quiz = usuario_logado();
        if ($usuario_quiz) {
            $quiz_logado = true;
            $banco_quiz  = conectar_banco();
            $xp_quiz     = ($acertos * 10) + ($acertos === $total ? 20 : 0);

            $banco_quiz->prepare('INSERT INTO resultados_quiz (usuario_id, quiz, acertos, total, criado_em) VALUES (?, ?, ?, ?, ?)')
                       ->execute([(int) $usuario_quiz['id'], 'era_vargas', $acertos, $total, date('Y-m-d H:i:s')]);

            $resumo_quiz    = registrar_xp($banco_quiz, (int) $usuario_quiz['id'], 'quiz', $xp_quiz);
            $xp_quiz_ganho  = $resumo_quiz['ganhou'];

            $linha_xp = $banco_quiz->prepare('SELECT xp_total FROM usuarios WHERE id = ?');
            $linha_xp->execute([(int) $usuario_quiz['id']]);
            $xp_depois = (int) $linha_xp->fetchColumn();
            $resumo_quiz['nivel'] = calcular_nivel($xp_depois);
            $resumo_quiz['nivel_antes'] = calcular_nivel(max(0, $xp_depois - (int) $resumo_quiz['ganhou']));

            $evento_xp_json = json_encode($resumo_quiz, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
        }
    } catch (Throwable $erro_quiz) {
        error_log('Studay quiz: ' . $erro_quiz->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Studay — Era Vargas</title>
    <link rel="shortcut icon" href="../img/logo/faisca.webp" type="image/x-icon">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/modoENEM.css">
    <link rel="stylesheet" href="../css/gamificacao.css">
    <link rel="stylesheet" href="../css/animacoes.css">
</head>
<body data-xp-pagina="quiz">
<div class="main-container">


<?php desenhar_menu('enem'); ?>

    <main class="content">

        <h1 class="page-title">Era Vargas</h1>
        <a href="modoENEM.php" class="enem-voltar">&larr; Voltar ao Modo ENEM</a>

        <div class="enem-container-wrapper">

            <div class="passos-header">
                <span class="passo-label">Revolução de 1930</span>
                <span class="passo-label">Governo Provisório</span>
                <span class="passo-label">Estado Novo</span>
            </div>

            <div class="passos-grid">
                <div class="passo-card" id="card-rev1930" onclick="togglePanel('rev1930')">
                    <img src="../img/enem/rev1930.webp" alt="Revolução de 1930" class="passo-img">
                </div>
                <div class="passo-card" id="card-govprov" onclick="togglePanel('govprov')">
                    <img src="../img/enem/gov_provisorio.webp" alt="Governo Provisório" class="passo-img">
                </div>
                <div class="passo-card" id="card-estnovo" onclick="togglePanel('estnovo')">
                    <img src="../img/enem/estadonovo.webp" alt="Estado Novo" class="passo-img">
                </div>
            </div>

            <div class="passo-panel" id="panel-rev1930">
                <div class="passo-panel-inner">
                    <p>A Revolução de 1930 foi o movimento que colocou Getúlio Vargas no poder e encerrou a República Velha.
                    Na época existia a política do café com leite, na qual São Paulo e Minas Gerais se revezavam na presidência.
                    Em 1930, o presidente Washington Luís quebrou esse acordo ao apoiar outro paulista, Júlio Prestes.</p>
                    <p>Como resposta, Minas Gerais, Rio Grande do Sul e Paraíba formaram a Aliança Liberal e lançaram Getúlio
                    Vargas como candidato. Vargas perdeu a eleição, mas a oposição denunciou fraudes. O assassinato de João
                    Pessoa, vice de Vargas, aumentou a revolta contra o governo.</p>
                    <p>Em outubro de 1930, grupos militares e políticos iniciaram uma revolta armada que derrubou Washington Luís
                    e impediu a posse de Júlio Prestes. Com isso, Vargas assumiu a presidência provisória do Brasil.</p>
                </div>
            </div>

            <div class="passo-panel" id="panel-govprov">
                <div class="passo-panel-inner">
                    <p>O Governo Provisório foi a primeira fase da Era Vargas. Nesse período, Vargas governou sem Constituição e
                    sem Congresso Nacional, utilizando decretos para tomar decisões. Seu objetivo oficial era reorganizar o
                    país até a elaboração de uma nova Constituição.</p>
                    <p>Vargas promoveu a centralização do poder, retirando a autonomia das antigas oligarquias estaduais. Os
                    governadores foram substituídos por interventores nomeados pelo governo federal. Também foram criados
                    importantes ministérios, como o Ministério da Educação e Saúde e o Ministério do Trabalho.</p>
                    <p>Em 1932 ocorreu a Revolução Constitucionalista. Em 1934 foi promulgada uma nova Constituição
                    que trouxe o voto secreto, o voto feminino, a Justiça Eleitoral e direitos trabalhistas.</p>
                </div>
            </div>

            <div class="passo-panel" id="panel-estnovo">
                <div class="passo-panel-inner">
                    <p>O Estado Novo foi a fase ditatorial da Era Vargas. Em 1937, alegando a existência de uma ameaça comunista,
                    Vargas deu um golpe de Estado, cancelou as eleições previstas e implantou uma nova Constituição.</p>
                    <p>Durante o Estado Novo, o Congresso Nacional foi fechado, os partidos políticos foram proibidos e a censura
                    foi fortalecida. O Departamento de Imprensa e Propaganda controlava a divulgação de informações.</p>
                    <p>Apesar do autoritarismo, esse período ficou marcado pelo avanço da industrialização e pela ampliação da
                    legislação trabalhista. Em 1943 foi criada a CLT. Com o fim da Segunda Guerra Mundial e o aumento das
                    pressões por democracia, Vargas foi deposto em 1945.</p>
                </div>
            </div>

            <div class="enem-secao-box">
                <h3 class="enem-secao-titulo">Vídeo sobre o assunto:</h3>
            </div>

            <div class="enem-secao-box enem-video-box">
                <iframe class="enem-iframe-video"
                        src="https://www.youtube.com/embed/-O9yeWy2E-s?si=z9-KeJMLvm3ZDKFa"
                        allowfullscreen
                        title="Vídeo Era Vargas">
                </iframe>
            </div>

            <div class="enem-secao-box">
                <h3 class="enem-secao-titulo">Perguntas do ENEM sobre Era Vargas:</h3>
            </div>

            <?php if ($quiz_enviado): ?>
            <div class="enem-feedback enem-feedback-<?= htmlspecialchars($feedback_classe) ?>">
                <h3><?= htmlspecialchars($nivel_titulo) ?></h3>
                <p><?= htmlspecialchars($feedback_msg) ?></p>
                <?php if ($xp_quiz_ganho > 0): ?>
                <p class="enem-xp-ganho">+<?= (int) $xp_quiz_ganho ?> XP na sua conta!</p>
                <?php elseif (!$quiz_logado): ?>
                <p class="enem-xp-ganho"><a href="login.php?voltar=eraVargas.php">Entre na sua conta</a> para ganhar XP com o quiz.</p>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="eraVargas.php" class="enem-quiz-form">

                <?php
                // Helper para renderizar alternativas — evita repetição
                function renderAlternativas(string $questao, array $alternativas, array $gabarito, array $respostas, bool $quiz_enviado): void {
                    foreach ($alternativas as $letra => $texto):
                        $classe_alt = '';
                        if ($quiz_enviado) {
                            if ($letra === $gabarito[$questao])                                          { $classe_alt = 'alt-correta'; }
                            elseif (isset($respostas[$questao]) && $respostas[$questao] === $letra)     { $classe_alt = 'alt-errada'; }
                        }
                        $checked   = ($quiz_enviado && isset($respostas[$questao]) && $respostas[$questao] === $letra) ? 'checked' : '';
                        $disabled  = $quiz_enviado ? 'disabled' : '';
                        $texto_esc = htmlspecialchars($texto);
                        echo "<label class=\"enem-alt $classe_alt\">";
                        echo "<input type=\"radio\" name=\"$questao\" value=\"$letra\" $checked $disabled>";
                        echo "<span class=\"alt-letra\">" . strtoupper($letra) . "</span>";
                        echo "$texto_esc</label>";
                    endforeach;
                }
                ?>

                <!-- QUESTÃO 1 -->
                <div class="enem-questao-box">
                    <div class="enem-questao-header">QUESTÃO 1 — ENEM 2011</div>
                    <div class="enem-questao-enunciado">
                        <p>O governo Vargas, principalmente durante o Estado Novo (1937-1945), pretendeu construir um State capaz
                        de criar uma nova sociedade. Uma dimensão-chave desse projeto tinha no território seu foco principal.
                        Não por acaso, foram criadas então instituições encarregadas de fornecer dados confiáveis para a ação
                        do governo, como o Conselho Nacional de Geografia, o Conselho Nacional de Cartografia, o Conselho
                        Nacional de Estatística e o Instituto Brasileiro de Geografia e Estatística (IBGE), este de 1938.
                        <br>LIPPI L. A conquista de Oeste. (adaptado)
                        <br><br>A criação dessas instituições pelo governo Vargas representa uma estratégia política de:</p>
                    </div>
                    <div class="enem-alternativas">
                        <?php renderAlternativas('q1', [
                            'a' => 'Levantar informações para a preservação da paisagem dos sertões.',
                            'b' => 'Obter conhecimento científico das diversidades regionais.',
                            'c' => 'Controlar o crescimento exponencial da população brasileira.',
                            'd' => 'Conter o fluxo migratório do campo para a cidade.',
                            'e' => 'Propor a criação de novas unidades da federação.'
                        ], $gabarito, $respostas, $quiz_enviado); ?>
                    </div>
                </div>

                <!-- QUESTÃO 2 -->
                <div class="enem-questao-box">
                    <div class="enem-questao-header">QUESTÃO 2 — ENEM 2013</div>
                    <div class="enem-questao-enunciado">
                        <p>Quando Getúlio Vargas se suicidou, em agosto de 1954, o país parecia à beira do caos. Acuado por uma
                        grave crise política, o velho líder preferiu uma bala no peito à humilhação de aceitar uma nova
                        deposição, como a que sofrera em outubro de 1945.
                        <br>REIS FILHO, D. A. O Estado à sombra de Vargas. Revista Nossa História, n. 7, maio 2004.
                        <br><br>O evento analisado no texto teve como repercussão imediata na política nacional a</p>
                    </div>
                    <div class="enem-alternativas">
                        <?php renderAlternativas('q2', [
                            'a' => 'Campanha anticomunista.',
                            'b' => 'Intervenção militar.',
                            'c' => 'Abertura democrática.',
                            'd' => 'Reação popular.',
                            'e' => 'Radicalização oposicionista.'
                        ], $gabarito, $respostas, $quiz_enviado); ?>
                    </div>
                </div>

                <!-- QUESTÃO 3 -->
                <div class="enem-questao-box">
                    <div class="enem-questao-header">QUESTÃO 3 — ENEM 2021</div>
                    <div class="enem-questao-enunciado">
                        <p>A Educação Nova, alargando a sua finalidade para além dos limites das classes, assume, com uma feição
                        mais humana, a sua verdadeira função social, preparando-se para formar "a hierarquia democrática" pela
                        "hierarquia das capacidades", recrutadas em todos os grupos sociais.
                        <br>Disponível em: www.histedbr.fe.unicamp.br. (adaptado).
                        <br><br>Os autores do manifesto citado procuravam contrapor-se ao caráter oligárquico da sociedade
                        brasileira. Nesse sentido, o trecho propõe uma relação necessária entre</p>
                    </div>
                    <div class="enem-alternativas">
                        <?php renderAlternativas('q3', [
                            'a' => 'Ensino técnico e mercado de trabalho.',
                            'b' => 'Ampliação de vagas e formação de gestores.',
                            'c' => 'Acesso à escola e valorização do mérito.',
                            'd' => 'Disponibilidade de financiamento e pesquisa avançada.',
                            'e' => 'Remuneração de professores e extinção do analfabetismo.'
                        ], $gabarito, $respostas, $quiz_enviado); ?>
                    </div>
                </div>

                <!-- QUESTÃO 4 -->
                <div class="enem-questao-box">
                    <div class="enem-questao-header">QUESTÃO 4 — ENEM 2017</div>
                    <div class="enem-questao-enunciado">
                        <p>Nos primeiros anos do governo Vargas, as organizações operárias sob controle das correntes de esquerda
                        tentaram se opor ao seu enquadramento pelo Estado. Mas a tentativa fracassou.
                        <br>FAUSTO, B. História concisa do Brasil. São Paulo: Edusp, 2002 (adaptado).
                        <br><br>No contexto histórico retratado pelo texto, a relação entre governo e movimento sindical foi caracterizada</p>
                    </div>
                    <div class="enem-alternativas">
                        <?php renderAlternativas('q4', [
                            'a' => 'Pela vinculação de direitos trabalhistas à tutela do Estado.',
                            'b' => 'Por um diálogo democraticamente constituído.',
                            'c' => 'Pelas benesses sociais do getulismo.',
                            'd' => 'Pelo reconhecimento de diferentes ideologias políticas.',
                            'e' => 'Por uma legislação construída consensualmente.'
                        ], $gabarito, $respostas, $quiz_enviado); ?>
                    </div>
                </div>

                <!-- QUESTÃO 5 -->
                <div class="enem-questao-box">
                    <div class="enem-questao-header">QUESTÃO 5 — ENEM 2015</div>
                    <div class="enem-questao-enunciado">
                        <p>A Justiça Eleitoral foi criada em 1932, como parte de uma ampla reforma no processo eleitoral
                        incentivada pela Revolução de 1930. Sua criação foi um grande avanço institucional.
                        <br>TAYLOR, M. Justiça Eleitoral. In: AVRITZER, L.; ANASTASIA, F. Reforma política no Brasil. UFMG, 2006 (adaptado).
                        <br><br>Em relação ao regime democrático no país, a instituição analisada teve o seguinte papel:</p>
                    </div>
                    <div class="enem-alternativas">
                        <?php renderAlternativas('q5', [
                            'a' => 'Expandiu a participação com o fim do critério censitário.',
                            'b' => 'Implementou o voto direto para presidente.',
                            'c' => 'Alterou as regras para as candidaturas na ditadura.',
                            'd' => 'Impulsionou as denúncias de corrupção administrativa.',
                            'e' => 'Combateu as fraudes sistemáticas nas apurações.'
                        ], $gabarito, $respostas, $quiz_enviado); ?>
                    </div>
                </div>

                <?php if (!$quiz_enviado): ?>
                <div class="enem-submit-container">
                    <button type="submit" name="quiz_submit" class="enem-btn-submit">
                        Verificar Respostas
                    </button>
                </div>
                <?php else: ?>
                <div class="enem-submit-container">
                    <a href="eraVargas.php" class="enem-btn-submit" style="text-decoration:none; display:inline-block;">
                        Tentar Novamente
                    </a>
                </div>
                <?php endif; ?>

            </form>

        </div>
    </main>

</div>

<script src="../js/modoENEM.js"></script>
<script src="../js/navigation.js"></script>
<script>window.STUDAY_EVENTO_XP = <?= $evento_xp_json ?>;</script>
<script src="../js/gamificacao.js"></script>
<script src="../js/animacoes.js"></script>
</body>
</html>
