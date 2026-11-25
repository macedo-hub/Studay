<?php
/*
 * RANKING — quem mais estudou na semana (e no geral).
 * A lista é desenhada pelo js/ranking.js, que busca os dados em api/ranking.php
 * e atualiza sozinho de tempos em tempos.
 */

require_once __DIR__ . '/../includes/sessao.php';
require_once __DIR__ . '/../includes/menu.php';

$usuario = usuario_logado();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Studay — Ranking</title>
    <!-- Pré-visualização de links (Open Graph e Twitter Cards) -->
    <meta name="description" content="Veja quem mais estudou na semana e no geral. Estude, ganhe XP e suba de posição.">
    <link rel="canonical" href="https://studay.site.je/pages/ranking.php">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Studay">
    <meta property="og:locale" content="pt_BR">
    <meta property="og:title" content="Ranking | Studay">
    <meta property="og:description" content="Veja quem mais estudou na semana e no geral. Estude, ganhe XP e suba de posição.">
    <meta property="og:url" content="https://studay.site.je/pages/ranking.php">
    <meta property="og:image" content="https://studay.site.je/img/compartilhar/studay-preview.jpg">
    <meta property="og:image:secure_url" content="https://studay.site.je/img/compartilhar/studay-preview.jpg">
    <meta property="og:image:type" content="image/jpeg">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="Studay: o Faísca, mascote do site, ao lado do nome Studay">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Ranking | Studay">
    <meta name="twitter:description" content="Veja quem mais estudou na semana e no geral. Estude, ganhe XP e suba de posição.">
    <meta name="twitter:image" content="https://studay.site.je/img/compartilhar/studay-preview.jpg">
    <meta name="twitter:image:alt" content="Studay: o Faísca, mascote do site, ao lado do nome Studay">
    <!-- Fim da pré-visualização de links -->
    <link rel="shortcut icon" href="../img/logo/faisca.webp" type="image/x-icon">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/ranking.css">
    <link rel="stylesheet" href="../css/gamificacao.css">
    <link rel="stylesheet" href="../css/animacoes.css">
</head>
<body>
<div class="main-container">

<?php desenhar_menu('ranking'); ?>

    <main class="content">
        <h1 class="page-title">Ranking</h1>
        <p class="ranking-intro">
            Estudar rende XP, e XP te coloca no topo! O ranking da semana zera toda segunda-feira,
            então todo mundo começa de novo.
        </p>

        <div class="ranking-abas" role="tablist" aria-label="Período do ranking">
            <button type="button" class="ranking-aba ativo js-aba-ranking" data-periodo="semana" role="tab" aria-selected="true">Esta semana</button>
            <button type="button" class="ranking-aba js-aba-ranking" data-periodo="geral" role="tab" aria-selected="false">Geral</button>
            <span class="ranking-aovivo" title="A lista se atualiza sozinha"><i></i> ao vivo</span>
        </div>

        <?php if ($usuario): ?>
        <div class="ranking-meu js-ranking-meu" aria-live="polite">Buscando a sua posição...</div>
        <?php else: ?>
        <div class="ranking-meu ranking-meu-convite">
            Quer aparecer aqui? <a href="login.php?voltar=ranking.php">Entre</a> ou <a href="registro.php">crie sua conta</a>.
        </div>
        <?php endif; ?>

        <div class="ranking-podio js-ranking-podio" aria-label="Os três primeiros"></div>
        <ol class="ranking-lista js-ranking-lista" aria-label="Demais posições"></ol>

        <div class="ranking-vazio js-ranking-vazio" hidden>
            <img src="../img/faisca/estagio-1.webp" alt="" class="ranking-vazio-faisca">
            <h2>Ninguém pontuou ainda</h2>
            <p>Faça um Pomodoro ou o quiz do Modo ENEM e seja o primeiro da lista!</p>
        </div>

        <p class="ranking-erro js-ranking-erro" hidden>Não consegui atualizar agora. Vou tentar de novo já já.</p>
    </main>

</div>

<script src="../js/navigation.js"></script>
<script src="../js/gamificacao.js"></script>
<script src="../js/ranking.js"></script>
<script src="../js/animacoes.js"></script>
</body>
</html>
