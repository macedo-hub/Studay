<?php

include "valida_dados.php";
require_once __DIR__ . '/../includes/menu.php';

// Define os limites de tempo
define("TEMPO_MIN", 10);
define("TEMPO_MAX", 240);

// Padrões usados nas validações
define("PADRAO_VALIDA_CONTEUDO", '/^[\p{L}\p{N}\s\-\.,:()+=\/%]{3,60}$/u');
define("PADRAO_VALIDA_OBSERVACAO", '/^.{0,300}$/su');

// Define se serão usadas imagens diferentes para cada estágio
define("FAISCA_USA_IMAGENS", true);

// Lista de matérias
$materias = [
    "matematica" => "Matemática",
    "portugues" => "Português",
    "redacao" => "Redação",
    "historia" => "História",
    "geografia" => "Geografia",
    "fisica" => "Física",
    "quimica" => "Química",
    "biologia" => "Biologia",
    "ingles" => "Inglês",
    "programacao" => "Programação"
];

// Lista de disposições
$disposicoes = [
    "animado" => ["nome" => "Animado", "icone" => "raio"],
    "normal" => ["nome" => "Normal", "icone" => "sorriso"],
    "cansado" => ["nome" => "Cansado", "icone" => "lua"]
];

// Lista de dificuldades
$dificuldades = [
    "conteudo_novo" => "Entender o conteúdo",
    "resolver_questoes" => "Resolver questões",
    "memorizar" => "Memorizar",
    "concentracao" => "Me concentrar"
];

// Lista de objetivos
$objetivos = [
    "prova" => "Me preparar para uma prova",
    "enem" => "Treinar para o ENEM",
    "revisar" => "Revisar o que já estudei",
    "aprender" => "Aprender um assunto novo"
];

// Mensagem de cada objetivo
$metas = [
    "prova" => "Chegar na prova com segurança e sem sustos.",
    "enem" => "Treinar o raciocínio e o ritmo de prova do ENEM.",
    "revisar" => "Fixar de vez o que você já viu antes.",
    "aprender" => "Sair da sessão entendendo a base do assunto."
];

// Fala do Faísca de acordo com a disposição
$falas_disposicao = [
    "animado" => "Que energia boa! Minha chama está lá em cima, vamos aproveitar!",
    "normal" => "Tudo certo! Vamos em ritmo firme, um bloco de cada vez.",
    "cansado" => "Hoje o pavio está curtinho, então vamos leve e com uma pausa."
];

// Função para mostrar os ícones
function icone_svg($nome)
{
    $icones = [
        "livro" => '<path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>',
        "lapis" => '<path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/>',
        "alvo" => '<circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/>',
        "refazer" => '<path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/>',
        "cafe" => '<path d="M10 2v2"/><path d="M14 2v2"/><path d="M16 8a1 1 0 0 1 1 1v8a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4V9a1 1 0 0 1 1-1h14a4 4 0 1 1 0 8h-1"/><path d="M6 2v2"/>',
        "lampada" => '<path d="M15 14c.2-1 .7-1.7 1.5-2.5 1-.9 1.5-2.2 1.5-3.5A6 6 0 0 0 6 8c0 1 .2 2.2 1.5 3.5.7.7 1.3 1.5 1.5 2.5"/><path d="M9 18h6"/><path d="M10 22h4"/>',
        "camadas" => '<polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/>',
        "raio" => '<polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>',
        "check" => '<polyline points="20 6 9 17 4 12"/>',
        "sorriso" => '<circle cx="12" cy="12" r="10"/><path d="M8 14s1.5 2 4 2 4-2 4-2"/><line x1="9" y1="9" x2="9.01" y2="9"/><line x1="15" y1="9" x2="15.01" y2="9"/>',
        "lua" => '<path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"/>',
        "chama" => '<path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/>',
        "relogio" => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>'
    ];

    if (array_key_exists($nome, $icones)) {
        return '<svg viewBox="0 0 24 24" aria-hidden="true">' . $icones[$nome] . '</svg>';
    }

    return "";
}

// Função que monta o cronograma
function monta_cronograma($tempo, $dificuldade, $disposicao, $objetivo)
{
    $cronograma = [];

    // Define os blocos de acordo com a dificuldade
    if ($dificuldade == "conteudo_novo") {
        $cronograma[] = [
            "titulo" => "Revisão do conteúdo",
            "descricao" => "Leia com calma e anote com suas palavras o que cada ideia significa.",
            "icone" => "livro"
        ];
        $cronograma[] = [
            "titulo" => "Exemplos guiados",
            "descricao" => "Acompanhe um ou dois exemplos resolvidos, passo a passo.",
            "icone" => "lampada"
        ];
        $cronograma[] = [
            "titulo" => "Resolução de questões",
            "descricao" => "Agora tente sozinho(a). O objetivo é aplicar o que entendeu.",
            "icone" => "lapis"
        ];
        $cronograma[] = [
            "titulo" => "Revisão dos erros",
            "descricao" => "Volte nas questões que errou e descubra onde o raciocínio escapou.",
            "icone" => "refazer"
        ];
    } elseif ($dificuldade == "resolver_questoes") {
        $cronograma[] = [
            "titulo" => "Aquecimento teórico",
            "descricao" => "Releia fórmulas, regras e pontos principais antes de começar.",
            "icone" => "livro"
        ];
        $cronograma[] = [
            "titulo" => "Resolução de questões",
            "descricao" => "Resolva sem consultar e marque as que gerarem dúvida.",
            "icone" => "lapis"
        ];
        $cronograma[] = [
            "titulo" => "Revisão dos erros",
            "descricao" => "Refaça as questões erradas e veja o motivo de cada erro.",
            "icone" => "refazer"
        ];
    } elseif ($dificuldade == "memorizar") {
        $cronograma[] = [
            "titulo" => "Leitura ativa",
            "descricao" => "Leia destacando somente as informações mais importantes.",
            "icone" => "livro"
        ];
        $cronograma[] = [
            "titulo" => "Resumo e flashcards",
            "descricao" => "Faça um resumo curto e perguntas sobre o conteúdo.",
            "icone" => "camadas"
        ];
        $cronograma[] = [
            "titulo" => "Autoteste",
            "descricao" => "Tente responder sem olhar o material e confira depois.",
            "icone" => "alvo"
        ];
        $cronograma[] = [
            "titulo" => "Revisão final",
            "descricao" => "Revise os pontos que ainda causaram dúvida.",
            "icone" => "refazer"
        ];
    } else {
        $cronograma[] = [
            "titulo" => "Preparar o ambiente",
            "descricao" => "Silencie o celular e deixe na mesa somente o necessário.",
            "icone" => "alvo"
        ];
        $cronograma[] = [
            "titulo" => "Foco total",
            "descricao" => "Concentre-se em um assunto por vez.",
            "icone" => "raio"
        ];
        $cronograma[] = [
            "titulo" => "Revisão rápida",
            "descricao" => "Releia suas anotações principais.",
            "icone" => "livro"
        ];
        $cronograma[] = [
            "titulo" => "Fechamento",
            "descricao" => "Anote o que aprendeu e o que precisa revisar.",
            "icone" => "check"
        ];
    }

    // Altera o nome das questões de acordo com o objetivo
    foreach ($cronograma as $i => $bloco) {
        if ($bloco["titulo"] == "Resolução de questões") {
            if ($objetivo == "prova") {
                $cronograma[$i]["titulo"] = "Questões estilo prova";
            } elseif ($objetivo == "enem") {
                $cronograma[$i]["titulo"] = "Questões estilo ENEM";
            }
        }
    }

    // Se estiver animado e tiver pelo menos 45 minutos, adiciona um desafio
    if ($disposicao == "animado" && $tempo >= 45) {
        $cronograma[] = [
            "titulo" => "Desafio relâmpago",
            "descricao" => "Escolha uma questão mais difícil e tente resolver sem ajuda.",
            "icone" => "raio"
        ];
    }

    // Divide o tempo igualmente entre os blocos
    $quantidade = count($cronograma);

    if ($quantidade > 0) {
        $tempo_bloco = (int) ($tempo / $quantidade);
        $resto = $tempo % $quantidade;

        foreach ($cronograma as $i => $bloco) {
            $minutos = $tempo_bloco;

            // O primeiro bloco recebe o tempo que sobrar
            if ($i == 0) {
                $minutos = $minutos + $resto;
            }

            $cronograma[$i]["minutos"] = $minutos;
            $cronograma[$i]["pausa"] = false;
        }
    }

    // Se estiver cansado e tiver pelo menos 40 minutos, adiciona uma pausa
    if ($disposicao == "cansado" && $tempo >= 40) {
        $pausa = [
            "titulo" => "Pausa",
            "descricao" => "Levante, beba água e descanse um pouco.",
            "icone" => "cafe",
            "minutos" => 5,
            "pausa" => true
        ];

        $meio = (int) (count($cronograma) / 2);
        $cronograma_temp = [];

        foreach ($cronograma as $i => $bloco) {
            if ($i == $meio) {
                $cronograma_temp[] = $pausa;
            }

            $cronograma_temp[] = $bloco;
        }

        $cronograma = $cronograma_temp;
    }

    return $cronograma;
}

// Variáveis do formulário
$materia = "";
$conteudo = "";
$tempo = "";
$disposicao = "normal";
$dificuldade = "";
$objetivo = "";
$observacoes = "";

// Variáveis para mensagens de erro
$materia_erro = "";
$conteudo_erro = "";
$tempo_erro = "";
$disposicao_erro = "";
$dificuldade_erro = "";
$objetivo_erro = "";
$observacoes_erro = "";
$erro_geral = "";

$cronograma_gerado = false;
$cronograma = [];
$tempo_min = 0;

// Verifica se o formulário foi enviado
if (isset($_POST["gerar_cronograma"])) {
    // Recebe os dados usando a função de validação
    $materia = valida_dados($_POST["materia"] ?? "");
    $conteudo = valida_dados($_POST["conteudo"] ?? "");
    $tempo = valida_dados($_POST["tempo"] ?? "");
    $disposicao = valida_dados($_POST["disposicao"] ?? "");
    $dificuldade = valida_dados($_POST["dificuldade"] ?? "");
    $objetivo = valida_dados($_POST["objetivo"] ?? "");
    $observacoes = valida_dados($_POST["observacoes"] ?? "");

    // Validação da matéria
    if (empty($materia)) {
        $materia_erro = "Escolha uma matéria.";
    } elseif (!array_key_exists($materia, $materias)) {
        $materia_erro = "Matéria inválida.";
    }

    // Validação do conteúdo
    if (empty($conteudo)) {
        $conteudo_erro = "Informe o conteúdo que você vai estudar.";
    } elseif (!preg_match(PADRAO_VALIDA_CONTEUDO, $conteudo)) {
        $conteudo_erro = "Conteúdo inválido. Use letras, números e pontuação simples.";
    }

    // Validação do tempo
    if (empty($tempo)) {
        $tempo_erro = "Informe quantos minutos você tem.";
    } else {
        $tempo_validado = filter_var($tempo, FILTER_VALIDATE_INT, [
            "options" => [
                "min_range" => TEMPO_MIN,
                "max_range" => TEMPO_MAX
            ]
        ]);

        if ($tempo_validado === false) {
            $tempo_erro = "Digite um número inteiro de " . TEMPO_MIN . " a " . TEMPO_MAX . " minutos.";
        }
    }

    // Validação da disposição
    if (empty($disposicao)) {
        $disposicao_erro = "Escolha sua disposição.";
    } elseif (!array_key_exists($disposicao, $disposicoes)) {
        $disposicao_erro = "Disposição inválida.";
    }

    // Validação da dificuldade
    if (empty($dificuldade)) {
        $dificuldade_erro = "Escolha sua maior dificuldade.";
    } elseif (!array_key_exists($dificuldade, $dificuldades)) {
        $dificuldade_erro = "Dificuldade inválida.";
    }

    // Validação do objetivo
    if (empty($objetivo)) {
        $objetivo_erro = "Escolha o objetivo do estudo.";
    } elseif (!array_key_exists($objetivo, $objetivos)) {
        $objetivo_erro = "Objetivo inválido.";
    }

    // Validação das observações
    if (!empty($observacoes) && !preg_match(PADRAO_VALIDA_OBSERVACAO, $observacoes)) {
        $observacoes_erro = "Escreva no máximo 300 caracteres.";
    }

    // Só gera o cronograma se não houver erros
    if (empty($materia_erro) && empty($conteudo_erro) && empty($tempo_erro) && empty($disposicao_erro) && empty($dificuldade_erro) && empty($objetivo_erro) && empty($observacoes_erro)) {
        $tempo_min = (int) $tempo_validado;
        $cronograma = monta_cronograma($tempo_min, $dificuldade, $disposicao, $objetivo);
        $cronograma_gerado = true;
    } else {
        $erro_geral = "Alguns campos precisam de atenção. Corrija-os e tente novamente.";
    }
}

// Define a fala do Faísca
if ($cronograma_gerado) {
    $fala_inicial = $falas_disposicao[$disposicao];
} else {
    $fala_inicial = "Oi! Eu sou o Faísca. Me conta como está seu dia e eu monto seu cronograma.";
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Studay — Modo Faísca</title>
    <!-- Pré-visualização de links (Open Graph e Twitter Cards) -->
    <meta name="description" content="Estude com o Faísca, o mascote do Studay, e ganhe XP pelo tempo de foco.">
    <link rel="canonical" href="https://studay.site.je/pages/modoFaisca.php">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Studay">
    <meta property="og:locale" content="pt_BR">
    <meta property="og:title" content="Modo Faísca | Studay">
    <meta property="og:description" content="Estude com o Faísca, o mascote do Studay, e ganhe XP pelo tempo de foco.">
    <meta property="og:url" content="https://studay.site.je/pages/modoFaisca.php">
    <meta property="og:image" content="https://studay.site.je/img/compartilhar/studay-preview.jpg">
    <meta property="og:image:secure_url" content="https://studay.site.je/img/compartilhar/studay-preview.jpg">
    <meta property="og:image:type" content="image/jpeg">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="Studay: o Faísca, mascote do site, ao lado do nome Studay">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Modo Faísca | Studay">
    <meta name="twitter:description" content="Estude com o Faísca, o mascote do Studay, e ganhe XP pelo tempo de foco.">
    <meta name="twitter:image" content="https://studay.site.je/img/compartilhar/studay-preview.jpg">
    <meta name="twitter:image:alt" content="Studay: o Faísca, mascote do site, ao lado do nome Studay">
    <!-- Fim da pré-visualização de links -->
    <link rel="shortcut icon" href="../img/logo/faisca.webp" type="image/x-icon">
    <!-- Ícones do site -->
    <link rel="icon" href="../favicon.ico" sizes="any">
    <link rel="icon" type="image/png" sizes="48x48" href="../img/icones/icone-48.png">
    <link rel="icon" type="image/png" sizes="192x192" href="../img/icones/icone-192.png">
    <link rel="apple-touch-icon" sizes="180x180" href="../img/icones/apple-touch-icon.png">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/modoFaisca.css">
    <link rel="stylesheet" href="../css/gamificacao.css">
    <link rel="stylesheet" href="../css/animacoes.css">
</head>

<body data-xp-pagina="faisca">
<div class="main-container">

    <?php desenhar_menu('faisca'); ?>

    <main class="content">
        <h1 class="page-title">Modo Faísca</h1>
        <p class="faisca-subtitulo">Descubra seu momento, encare seu desafio, acenda seu foco</p>

        <div class="faisca-layout">
            <!-- FAÍSCA -->
            <aside class="faisca-lateral" aria-label="Faísca, o mascote do Studay">
                <div class="faisca-visual js-faisca-visual" data-estagio="1" data-imagens="<?php echo FAISCA_USA_IMAGENS ? 'true' : 'false'; ?>">
                    <div class="faisca-aura"></div>
                    <img src="../img/logo/faisca-sem-fundo.webp" alt="Faísca, o mascote do Studay" class="faisca-img js-faisca-img">
                </div>

                <p class="faisca-fala js-faisca-fala" role="status" aria-live="polite">
                    <?php echo $fala_inicial; ?>
                </p>

                <?php if ($cronograma_gerado): ?>
                    <div class="faisca-timer">
                        <div class="faisca-timer-tempo js-faisca-tempo" aria-live="off">
                            <?php echo sprintf('%02d:00', $tempo_min); ?>
                        </div>

                        <p class="faisca-timer-bloco js-faisca-bloco-atual">Pronto para começar</p>

                        <div class="faisca-timer-botoes">
                            <button type="button" class="faisca-btn js-faisca-iniciar">
                                <svg class="js-icone-play" viewBox="0 0 24 24" aria-hidden="true">
                                    <polygon points="6 3 20 12 6 21 6 3"/>
                                </svg>
                                <svg class="js-icone-pausa" viewBox="0 0 24 24" aria-hidden="true" hidden>
                                    <rect x="14" y="4" width="4" height="16" rx="1"/>
                                    <rect x="6" y="4" width="4" height="16" rx="1"/>
                                </svg>
                                <span>Iniciar</span>
                            </button>

                            <button type="button" class="faisca-btn faisca-btn-sec js-faisca-reiniciar" aria-label="Reiniciar sessão">
                                <svg viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
                                    <path d="M3 3v5h5"/>
                                </svg>
                            </button>
                        </div>

                        <label class="faisca-demo">
                            <input type="checkbox" class="js-faisca-acelerar">
                            <span>Acelerar (1 min por segundo, para demonstração)</span>
                        </label>
                    </div>
                <?php endif; ?>
            </aside>

            <div class="faisca-principal">
                <?php if ($cronograma_gerado): ?>
                    <!-- RESULTADO -->
                    <section class="faisca-card" aria-labelledby="titulo-cronograma">
                        <h2 id="titulo-cronograma">
                            <?php echo icone_svg("chama"); ?>
                            Seu mini cronograma de <?php echo $materias[$materia]; ?>
                        </h2>

                        <ul class="faisca-resumo">
                            <li><strong>Conteúdo:</strong> <?php echo $conteudo; ?></li>
                            <li><strong>Tempo:</strong> <?php echo $tempo_min; ?> min</li>
                            <li><strong>Disposição:</strong> <?php echo $disposicoes[$disposicao]["nome"]; ?></li>
                            <li><strong>Dificuldade:</strong> <?php echo $dificuldades[$dificuldade]; ?></li>
                        </ul>

                        <p class="faisca-meta">
                            <strong>Meta da sessão:</strong>
                            <?php echo $metas[$objetivo]; ?>
                        </p>

                        <ol class="cronograma-lista js-faisca-sessao" data-total="<?php echo $tempo_min; ?>">
                            <?php foreach ($cronograma as $i => $bloco): ?>
                                <li class="cronograma-bloco<?php echo $bloco["pausa"] ? " cronograma-pausa" : ""; ?>" data-minutos="<?php echo (int) $bloco["minutos"]; ?>" data-titulo="<?php echo $bloco["titulo"]; ?>">
                                    <span class="cronograma-icone"><?php echo icone_svg($bloco["icone"]); ?></span>

                                    <div class="cronograma-info">
                                        <h3><?php echo $bloco["titulo"]; ?></h3>
                                        <p><?php echo $bloco["descricao"]; ?></p>
                                    </div>

                                    <span class="cronograma-min"><?php echo (int) $bloco["minutos"]; ?> min</span>
                                </li>
                            <?php endforeach; ?>
                        </ol>

                        <?php if (!empty($observacoes)): ?>
                            <p class="faisca-obs">
                                <strong>Suas observações:</strong>
                                <?php echo $observacoes; ?>
                            </p>
                        <?php endif; ?>

                        <div class="faisca-acoes">
                            <a href="modoFaisca.php" class="faisca-btn faisca-btn-sec">Montar outro cronograma</a>
                        </div>
                    </section>
                <?php else: ?>
                    <!-- FORMULÁRIO -->
                    <section class="faisca-card" aria-labelledby="titulo-form">
                        <h2 id="titulo-form">
                            <?php echo icone_svg("chama"); ?>
                            Conte para o Faísca
                        </h2>

                        <?php if (!empty($erro_geral)): ?>
                            <p class="faisca-aviso-erro" role="alert"><?php echo $erro_geral; ?></p>
                        <?php endif; ?>

                        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="faisca-form" novalidate>

                            <!-- MATÉRIA -->
                            <div class="faisca-campo">
                                <label for="materia">Qual matéria você vai estudar?</label>
                                <select id="materia" name="materia">
                                    <option value="">Selecione...</option>
                                    <?php foreach ($materias as $chave => $nome): ?>
                                        <option value="<?php echo $chave; ?>" <?php echo ($materia === $chave) ? "selected" : ""; ?>>
                                            <?php echo $nome; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>

                                <?php if (!empty($materia_erro)): ?>
                                    <span class="faisca-erro"><?php echo $materia_erro; ?></span>
                                <?php endif; ?>
                            </div>

                            <!-- CONTEÚDO -->
                            <div class="faisca-campo">
                                <label for="conteudo">Qual conteúdo?</label>
                                <input type="text" id="conteudo" name="conteudo" value="<?php echo $conteudo; ?>" placeholder="Ex.: Funções do 1º grau" autocomplete="off">

                                <?php if (!empty($conteudo_erro)): ?>
                                    <span class="faisca-erro"><?php echo $conteudo_erro; ?></span>
                                <?php endif; ?>
                            </div>

                            <!-- TEMPO -->
                            <div class="faisca-campo">
                                <label for="tempo">Quanto tempo você tem? (em minutos)</label>
                                <input type="number" id="tempo" name="tempo" value="<?php echo $tempo; ?>" placeholder="Ex.: 60" min="<?php echo TEMPO_MIN; ?>" max="<?php echo TEMPO_MAX; ?>">

                                <?php if (!empty($tempo_erro)): ?>
                                    <span class="faisca-erro"><?php echo $tempo_erro; ?></span>
                                <?php endif; ?>
                            </div>

                            <!-- DISPOSIÇÃO -->
                            <div class="faisca-campo">
                                <span class="faisca-rotulo">Como está sua disposição?</span>

                                <div class="faisca-radios" role="radiogroup" aria-label="Disposição">
                                    <?php foreach ($disposicoes as $chave => $info): ?>
                                        <label class="faisca-radio">
                                            <input type="radio" name="disposicao" value="<?php echo $chave; ?>" <?php echo ($disposicao === $chave) ? "checked" : ""; ?>>
                                            <span class="faisca-radio-box">
                                                <?php echo icone_svg($info["icone"]); ?>
                                                <?php echo $info["nome"]; ?>
                                            </span>
                                        </label>
                                    <?php endforeach; ?>
                                </div>

                                <?php if (!empty($disposicao_erro)): ?>
                                    <span class="faisca-erro"><?php echo $disposicao_erro; ?></span>
                                <?php endif; ?>
                            </div>

                            <!-- DIFICULDADE -->
                            <div class="faisca-campo">
                                <label for="dificuldade">Qual é a sua maior dificuldade?</label>

                                <select id="dificuldade" name="dificuldade">
                                    <option value="">Selecione...</option>
                                    <?php foreach ($dificuldades as $chave => $nome): ?>
                                        <option value="<?php echo $chave; ?>" <?php echo ($dificuldade === $chave) ? "selected" : ""; ?>>
                                            <?php echo $nome; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>

                                <?php if (!empty($dificuldade_erro)): ?>
                                    <span class="faisca-erro"><?php echo $dificuldade_erro; ?></span>
                                <?php endif; ?>
                            </div>

                            <!-- OBJETIVO -->
                            <div class="faisca-campo">
                                <label for="objetivo">Qual é o objetivo deste estudo?</label>

                                <select id="objetivo" name="objetivo">
                                    <option value="">Selecione...</option>
                                    <?php foreach ($objetivos as $chave => $nome): ?>
                                        <option value="<?php echo $chave; ?>" <?php echo ($objetivo === $chave) ? "selected" : ""; ?>>
                                            <?php echo $nome; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>

                                <?php if (!empty($objetivo_erro)): ?>
                                    <span class="faisca-erro"><?php echo $objetivo_erro; ?></span>
                                <?php endif; ?>
                            </div>

                            <!-- OBSERVAÇÕES -->
                            <div class="faisca-campo">
                                <label for="observacoes">Observações (opcional)</label>

                                <textarea id="observacoes" name="observacoes" rows="3" placeholder="Algo que o Faísca deva saber?"><?php echo $observacoes; ?></textarea>

                                <?php if (!empty($observacoes_erro)): ?>
                                    <span class="faisca-erro"><?php echo $observacoes_erro; ?></span>
                                <?php endif; ?>
                            </div>

                            <!-- BOTÃO -->
                            <button type="submit" name="gerar_cronograma" class="faisca-btn faisca-btn-grande">
                                <?php echo icone_svg("chama"); ?>
                                <span>Acender meu cronograma</span>
                            </button>
                        </form>
                    </section>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>
<script src="../js/modoFaisca.js"></script>
<script src="../js/navigation.js"></script>
<script src="../js/gamificacao.js"></script>
<script src="../js/animacoes.js"></script>
</body>
</html>