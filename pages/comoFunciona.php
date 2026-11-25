<?php
/*
 * COMO FUNCIONA O XP — explicação simples de tudo: como ganhar XP, como os níveis funcionam
 * e quais são as regras de jogo limpo. Qualquer pessoa pode ver (não precisa estar logada).
 */

require_once __DIR__ . '/../includes/sessao.php';
require_once __DIR__ . '/../includes/gamificacao.php';
require_once __DIR__ . '/../includes/menu.php';

$usuario = usuario_logado();
$xpAtual = $usuario ? (int) $usuario['xp_total'] : -1;

$iconesDaTabela = [
    'pomodoro'        => 'cronometro',
    'faisca'          => 'chama',
    'quiz'            => 'alvo',
    'flashcard_certo' => 'cartas',
    'resumo_criado'   => 'lapis',
    'visita_site'     => 'globo',
    'login_diario'    => 'calendario',
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Studay — Como funciona o XP</title>
    <!-- Pré-visualização de links (Open Graph e Twitter Cards) -->
    <meta name="description" content="Entenda como ganhar XP, subir de nível e aparecer no ranking de forma justa.">
    <link rel="canonical" href="https://studay.site.je/pages/comoFunciona.php">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Studay">
    <meta property="og:locale" content="pt_BR">
    <meta property="og:title" content="Como funciona o XP | Studay">
    <meta property="og:description" content="Entenda como ganhar XP, subir de nível e aparecer no ranking de forma justa.">
    <meta property="og:url" content="https://studay.site.je/pages/comoFunciona.php">
    <meta property="og:image" content="https://studay.site.je/img/compartilhar/studay-preview.jpg">
    <meta property="og:image:secure_url" content="https://studay.site.je/img/compartilhar/studay-preview.jpg">
    <meta property="og:image:type" content="image/jpeg">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="Studay: o Faísca, mascote do site, ao lado do nome Studay">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Como funciona o XP | Studay">
    <meta name="twitter:description" content="Entenda como ganhar XP, subir de nível e aparecer no ranking de forma justa.">
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
    <link rel="stylesheet" href="../css/comoFunciona.css">
    <link rel="stylesheet" href="../css/gamificacao.css">
    <link rel="stylesheet" href="../css/animacoes.css">
</head>
<body>
<div class="main-container">

<?php desenhar_menu(''); ?>

    <main class="content">
        <h1 class="page-title">Como funciona o XP</h1>
        <p class="explica-intro">
            XP são os pontos do Studay. Você ganha estudando de verdade.
            Quanto mais XP, mais o Faísca evolui junto com você.
        </p>

        <section class="explica-passos revelar" aria-label="Resumo em três passos">
            <div class="explica-passo">
                <span class="explica-numero">1</span>
                <h2>Estude</h2>
                <p>Faça um Pomodoro, um quiz, flashcards ou um resumo.</p>
            </div>
            <div class="explica-seta" aria-hidden="true"><?= icone('seta') ?></div>
            <div class="explica-passo">
                <span class="explica-numero">2</span>
                <h2>Ganhe XP</h2>
                <p>Ao terminar, aparece um aviso <strong>+XP</strong> com o motivo.</p>
            </div>
            <div class="explica-seta" aria-hidden="true"><?= icone('seta') ?></div>
            <div class="explica-passo">
                <span class="explica-numero">3</span>
                <h2>Suba de nível</h2>
                <p>A barra enche e o Faísca evolui. Há 6 níveis.</p>
            </div>
        </section>

        <section class="explica-caixa revelar">
            <h2>Quando eu ganho XP?</h2>
            <p class="explica-texto">Só quando você termina a atividade. Veja quanto vale cada uma:</p>
            <ul class="explica-tabela">
                <?php foreach (TABELA_XP as [$chave, $titulo, $oQueFazer, $quanto, $limite]): ?>
                <li>
                    <span class="explica-icone"><?= icone($iconesDaTabela[$chave] ?? 'estrela') ?></span>
                    <div class="explica-tabela-texto">
                        <strong><?= h($titulo) ?></strong>
                        <span><?= h($oQueFazer) ?></span>
                    </div>
                    <div class="explica-tabela-valor">
                        <b><?= h($quanto) ?></b>
                        <small><?= h($limite) ?></small>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
        </section>

        <section class="explica-caixa revelar">
            <h2>Como funcionam os níveis?</h2>
            <p class="explica-texto">
                O seu XP vai somando e nunca diminui. Quando passa de um número, você sobe de nível.
                Aí aparece uma tela de comemoração!
            </p>
            <?php desenhar_lista_niveis($xpAtual); ?>
            <?php if (!$usuario): ?>
            <p class="explica-texto explica-aviso">
                <a href="login.php?voltar=comoFunciona.php">Entre</a> ou <a href="registro.php">crie sua conta</a>
                para guardar o seu XP e aparecer no ranking.
            </p>
            <?php endif; ?>
        </section>

        <section class="explica-caixa revelar">
            <h2>Dias seguidos e conquistas</h2>
            <p class="explica-texto">
                Estudar de verdade em dias seguidos cria uma <strong>sequência</strong>.
                Sequências, treinos e marcas especiais liberam <strong>conquistas</strong>,
                que ficam guardadas na página <a href="progresso.php">Meu progresso</a>.
            </p>
        </section>

        <section class="explica-caixa revelar">
            <h2>Regras de jogo limpo</h2>
            <ul class="explica-regras">
                <li><?= icone('check') ?> <span>O cronômetro precisa terminar. Sair antes não dá XP.</span></li>
                <li><?= icone('check') ?> <span>Acelerar o tempo no Modo Faísca não dá XP.</span></li>
                <li><?= icone('check') ?> <span>Cada atividade tem um limite por dia, para ninguém ganhar tudo de uma vez.</span></li>
                <li><?= icone('check') ?> <span>Quem confere tudo é o servidor, então não adianta tentar enganar o site.</span></li>
                <li><?= icone('check') ?> <span>O ranking da semana zera toda segunda-feira. Todo mundo recomeça junto.</span></li>
            </ul>
        </section>

        <div class="explica-acoes">
            <button type="button" class="btn-primary js-abrir-apresentacao">Ver apresentação do Faísca</button>
            <?php if ($usuario): ?>
            <a href="progresso.php" class="explica-link">Ir para o meu progresso</a>
            <?php endif; ?>
        </div>
    </main>

</div>

<script src="../js/navigation.js"></script>
<script src="../js/gamificacao.js"></script>
<script src="../js/animacoes.js"></script>
</body>
</html>
