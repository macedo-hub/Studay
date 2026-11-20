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
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Studay — Modo ENEM</title>
    <link rel="shortcut icon" href="../img/logo/faisca.webp" type="image/x-icon">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/modoENEM.css">
</head>
<body>
<div class="main-container">


<!-- SIDEBAR -->
<aside class="sidebar" aria-label="Menu de navegação principal">

    <div class="sidebar-header">
        <a href="metodos.html" class="studay-logo sidebar-logo" aria-label="Studay — ir para métodos">
            <span class="logo-stu">Stu</span><span class="logo-day">day</span>
        </a>
    </div>

    <!-- Itens de navegação -->
    <nav class="sidebar-nav" aria-label="Páginas do Studay">

        <!-- Métodos -->
        <a href="metodos.html"
           class="sidebar-item"
           aria-label="Ir para Métodos de estudo">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <rect x="3" y="3" width="7" height="7" rx="1"/>
                <rect x="14" y="3" width="7" height="7" rx="1"/>
                <rect x="3" y="14" width="7" height="7" rx="1"/>
                <rect x="14" y="14" width="7" height="7" rx="1"/>
            </svg>
            <span>Métodos</span>
        </a>

        <!-- Modo ENEM -->
        <a href="modoENEM.php"
           class="sidebar-item active"
           aria-label="Ir para Modo ENEM">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                <path d="M6 12v5c0 1.657 2.686 3 6 3s6-1.343 6-3v-5"/>
            </svg>
            <span>modoENEM</span>
        </a>

        <!-- Modo Faísca -->
        <a href="modoFaisca.php"
           class="sidebar-item"
           aria-label="Ir para Modo Faísca">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/>
            </svg>
            <span>Modo Faísca</span>
        </a>

        <!-- Calculadora de Média -->
        <a href="avaliacoes.html"
           class="sidebar-item"
           aria-label="Ir para Calculadora de Média">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <rect x="4" y="2" width="16" height="20" rx="2"/>
                <line x1="8" y1="6" x2="16" y2="6"/>
                <line x1="16" y1="10" x2="16" y2="10"/>
                <line x1="12" y1="10" x2="12" y2="10"/>
                <line x1="8"  y1="10" x2="8"  y2="10"/>
                <line x1="16" y1="14" x2="16" y2="14"/>
                <line x1="12" y1="14" x2="12" y2="14"/>
                <line x1="8"  y1="14" x2="8"  y2="14"/>
                <line x1="16" y1="18" x2="16" y2="18"/>
                <line x1="12" y1="18" x2="12" y2="18"/>
                <line x1="8"  y1="18" x2="8"  y2="18"/>
            </svg>
            <span>Calculadora de média</span>
        </a>

        <!-- Música — abre o modal -->
        <button class="sidebar-item sidebar-item-music js-open-music-modal"
                aria-label="Abrir player de música"
                aria-haspopup="dialog"
                type="button">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M9 18V5l12-2v13"/>
                <circle cx="6" cy="18" r="3"/>
                <circle cx="18" cy="16" r="3"/>
            </svg>
            <span>Música</span>
        </button>

        <!-- Sobre -->
        <a href="sobre.html"
           class="sidebar-item"
           aria-label="Ir para página Sobre">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="8"/>
                <line x1="12" y1="12" x2="12" y2="16"/>
            </svg>
            <span>Sobre</span>
        </a>

    </nav>

</aside>


<nav class="navbar-mobile" aria-label="Navegação principal mobile">
    <div class="navbar-mobile-inner">

        <!-- Métodos -->
        <a href="metodos.html"
           class="navbar-mobile-item"
           aria-label="Métodos">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <rect x="3" y="3" width="7" height="7" rx="1"/>
                <rect x="14" y="3" width="7" height="7" rx="1"/>
                <rect x="3" y="14" width="7" height="7" rx="1"/>
                <rect x="14" y="14" width="7" height="7" rx="1"/>
            </svg>
            <span>Métodos</span>
        </a>

        <!-- modoENEM -->
        <a href="modoENEM.php"
           class="navbar-mobile-item active"
           aria-label="modoENEM">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M22 10v6M2 10l10-5 10 5-10 5z"/>
                <path d="M6 12v5c0 1.657 2.686 3 6 3s6-1.343 6-3v-5"/>
            </svg>
            <span>modoENEM</span>
        </a>

        <!-- Música -->
        <button class="navbar-mobile-item js-open-music-modal"
                type="button"
                aria-label="Abrir player de música">
            <svg viewBox="0 0 24 24" aria-hidden="true">
                <path d="M9 18V5l12-2v13"/>
                <circle cx="6" cy="18" r="3"/>
                <circle cx="18" cy="16" r="3"/>
            </svg>
            <span>Música</span>
        </button>

        <button class="navbar-mobile-item js-more-toggle"
                type="button"
                aria-label="Mais opções"
                aria-haspopup="true"
                aria-expanded="false">
            <svg viewBox="0 0 24 24" aria-hidden="true" class="js-more-chevron">
                <polyline points="18 15 12 9 6 15"/>
            </svg>
            <span>Mais</span>
        </button>

    </div>
</nav>

<div class="navbar-more-overlay js-more-overlay" aria-hidden="true"></div>

<div class="navbar-more-popup js-more-popup" role="menu" aria-label="Mais opções">
    <a href="modoFaisca.php" class="navbar-more-item" role="menuitem">
        <svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/>
        </svg>
        Modo Faísca
    </a>
    <a href="avaliacoes.html" class="navbar-more-item" role="menuitem">
        <svg viewBox="0 0 24 24" aria-hidden="true">
            <rect x="4" y="2" width="16" height="20" rx="2"/>
            <line x1="8" y1="6" x2="16" y2="6"/>
            <line x1="8" y1="10" x2="16" y2="10"/>
            <line x1="8" y1="14" x2="16" y2="14"/>
        </svg>
        Calculadora de média
    </a>
    <a href="sobre.html" class="navbar-more-item" role="menuitem">
        <svg viewBox="0 0 24 24" aria-hidden="true">
            <circle cx="12" cy="12" r="10"/>
            <line x1="12" y1="8" x2="12" y2="8"/>
            <line x1="12" y1="12" x2="12" y2="16"/>
        </svg>
        Sobre
    </a>
</div>


<div class="music-modal-overlay js-music-modal-overlay"
     role="dialog"
     aria-modal="true"
     aria-label="Player de música para estudo">

    <div class="music-modal">

        <div class="music-modal-header">
            <h3>
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path d="M9 18V5l12-2v13"/>
                    <circle cx="6" cy="18" r="3"/>
                    <circle cx="18" cy="16" r="3"/>
                </svg>
                Música para estudar
            </h3>
            <button class="music-modal-close js-close-music-modal"
                    type="button"
                    aria-label="Fechar modal (a música continuará tocando)">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>

        <div class="music-modal-body">

            <div class="music-shortcuts" role="group" aria-label="Playlists sugeridas">
                <button class="music-shortcut-btn js-shortcut"
                        data-url="https://www.youtube.com/watch?v=lTRiuFIWV54"
                        type="button">
                    Lo-Fi
                </button>
                <button class="music-shortcut-btn js-shortcut"
                        data-url="https://www.youtube.com/watch?v=6X_OEUFV0v4"
                        type="button">
                    Piano
                </button>
                <button class="music-shortcut-btn js-shortcut"
                        data-url="https://www.youtube.com/watch?v=Dx5qFachd3A"
                        type="button">
                    Jazz
                </button>
                <button class="music-shortcut-btn js-shortcut"
                        data-url="https://www.youtube.com/watch?v=q76bMs-NwRk"
                        type="button">
                    Chuva
                </button>
            </div>

            <!-- Campo de URL personalizada -->
            <div class="music-url-row">
                <input type="url"
                       class="music-url-input js-music-url-input"
                       placeholder="Cole aqui a URL do YouTube..."
                       aria-label="URL do YouTube para carregar no player">
                <button class="music-load-btn js-music-load-btn"
                        type="button"
                        aria-label="Carregar URL no player">
                    Carregar
                </button>
            </div>

            <!-- Área do iframe -->
            <div class="music-iframe-container js-music-iframe-container">
                <div class="music-iframe-placeholder js-music-placeholder">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M22.54 6.42a2.78 2.78 0 0 0-1.95-1.96C18.88 4 12 4 12 4s-6.88 0-8.59.46a2.78 2.78 0 0 0-1.95 1.96A29 29 0 0 0 1 12a29 29 0 0 0 .46 5.58A2.78 2.78 0 0 0 3.41 19.6C5.12 20 12 20 12 20s6.88 0 8.59-.46a2.78 2.78 0 0 0 1.95-1.95A29 29 0 0 0 23 12a29 29 0 0 0-.46-5.58z"/>
                        <polygon points="9.75 15.02 15.5 12 9.75 8.98 9.75 15.02"/>
                    </svg>
                    Cole uma URL do YouTube ou escolha uma playlist acima
                </div>
            </div>

        </div>
    </div>
</div>

<div class="music-playing-indicator js-music-indicator"
     role="status"
     aria-live="polite"
     title="Clique para reabrir o player">
    <svg viewBox="0 0 24 24" aria-hidden="true">
        <path d="M9 18V5l12-2v13"/>
        <circle cx="6" cy="18" r="3"/>
        <circle cx="18" cy="16" r="3"/>
    </svg>
    Tocando...
</div>


    <main class="content">

        <h1 class="page-title">Modo ENEM</h1>

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
            </div>
            <?php endif; ?>

            <form method="POST" action="modoENEM.php" class="enem-quiz-form">

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
                    <a href="modoENEM.php" class="enem-btn-submit" style="text-decoration:none; display:inline-block;">
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
</body>
</html>
