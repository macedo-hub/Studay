<?php
/*
 * SITES DA TURMA — o portal da Plataforma ENEM.
 * Mostra os sites que cada equipe da turma criou. Os dados vêm do banco (tabela sites_turma).
 */

require_once __DIR__ . '/../includes/sessao.php';
require_once __DIR__ . '/../includes/sites.php';
require_once __DIR__ . '/../includes/menu.php';

$sites = [];
$bancoComProblema = false;

try {
    $sites = buscar_sites_ativos(conectar_banco());
} catch (Throwable $erro) {
    error_log('Studay sitesTurma: ' . $erro->getMessage());
    $bancoComProblema = true;
}

$usuario = usuario_logado();
$ehAdmin = $usuario && $usuario['papel'] === 'admin';

// Lista de matérias que existem de verdade (para os filtros)
$materias = [];
foreach ($sites as $site) {
    $materias[$site['disciplina']] = true;
}
$materias = array_keys($materias);
sort($materias);

$totalAcessos = array_sum(array_map(fn($s) => (int) $s['acessos'], $sites));
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Studay — Sites da Turma</title>
    <!-- Pré-visualização de links (Open Graph e Twitter Cards) -->
    <meta name="description" content="Conheça os sites que a turma criou para estudar para o ENEM e abra todos por aqui.">
    <link rel="canonical" href="https://studay.site.je/pages/sitesTurma.php">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Studay">
    <meta property="og:locale" content="pt_BR">
    <meta property="og:title" content="Sites da Turma | Studay">
    <meta property="og:description" content="Conheça os sites que a turma criou para estudar para o ENEM e abra todos por aqui.">
    <meta property="og:url" content="https://studay.site.je/pages/sitesTurma.php">
    <meta property="og:image" content="https://studay.site.je/img/compartilhar/studay-preview.jpg">
    <meta property="og:image:secure_url" content="https://studay.site.je/img/compartilhar/studay-preview.jpg">
    <meta property="og:image:type" content="image/jpeg">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="Studay: o Faísca, mascote do site, ao lado do nome Studay">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Sites da Turma | Studay">
    <meta name="twitter:description" content="Conheça os sites que a turma criou para estudar para o ENEM e abra todos por aqui.">
    <meta name="twitter:image" content="https://studay.site.je/img/compartilhar/studay-preview.jpg">
    <meta name="twitter:image:alt" content="Studay: o Faísca, mascote do site, ao lado do nome Studay">
    <!-- Fim da pré-visualização de links -->
    <link rel="shortcut icon" href="../img/logo/faisca.webp" type="image/x-icon">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/sitesTurma.css">
    <link rel="stylesheet" href="../css/gamificacao.css">
    <link rel="stylesheet" href="../css/animacoes.css">
</head>
<body data-xp-pagina="sites">
<div class="main-container">

<?php desenhar_menu('sites'); ?>

    <main class="content">
        <h1 class="page-title">Sites da Turma</h1>
        <p class="sites-intro">
            Aqui estão os sites que a nossa turma criou para te ajudar a estudar para o ENEM.
            Cada equipe olhou para o assunto do seu jeito. Escolha um e bora estudar!
        </p>

        <?php if ($ehAdmin): ?>
        <a href="adminSites.php" class="sites-botao-admin">Gerenciar sites</a>
        <?php endif; ?>

        <?php if ($bancoComProblema): ?>
            <div class="sites-vazio">
                <img src="../img/faisca/estagio-4.webp" alt="" class="sites-vazio-faisca">
                <h2>Ops, o Faísca se atrapalhou</h2>
                <p>Não consegui buscar os sites agora. Tente de novo daqui a pouquinho.</p>
            </div>

        <?php elseif (count($sites) === 0): ?>
            <div class="sites-vazio revelar">
                <img src="../img/faisca/estagio-1.webp" alt="" class="sites-vazio-faisca">
                <h2>Ainda não tem sites por aqui</h2>
                <p>Assim que cada equipe publicar o seu site, ele aparece nesta página. Volte logo!</p>
            </div>

        <?php else: ?>

            <div class="sites-resumo revelar" aria-label="Resumo do portal">
                <div class="sites-resumo-item"><strong><?= count($sites) ?></strong><span>sites</span></div>
                <div class="sites-resumo-item"><strong><?= count($materias) ?></strong><span>matérias</span></div>
                <div class="sites-resumo-item"><strong><?= (int) $totalAcessos ?></strong><span>visitas</span></div>
            </div>

            <div class="sites-filtros revelar">
                <label class="sites-busca" for="buscaSites">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    <input type="search" id="buscaSites" class="js-busca-sites" placeholder="Buscar site, equipe ou assunto..." autocomplete="off">
                </label>

                <div class="sites-chips" role="group" aria-label="Filtrar por matéria">
                    <button type="button" class="sites-chip ativo js-filtro-materia" data-materia="">Todas</button>
                    <?php foreach ($materias as $materia): ?>
                    <button type="button" class="sites-chip js-filtro-materia" data-materia="<?= h($materia) ?>"><?= h($materia) ?></button>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="sites-grade js-sites-grade">
                <?php foreach ($sites as $site):
                    $temCapa = $site['imagem_capa'] !== '' && nome_de_imagem_valido($site['imagem_capa']);
                    $textoBusca = strtolower($site['nome_site'] . ' ' . $site['equipe'] . ' ' . $site['disciplina'] . ' ' . $site['descricao']);
                ?>
                <article class="site-card revelar js-site-card"
                         data-materia="<?= h($site['disciplina']) ?>"
                         data-busca="<?= h($textoBusca) ?>">

                    <div class="site-capa">
                        <?php if ($temCapa): ?>
                        <img src="../img/sites/<?= h($site['imagem_capa']) ?>"
                             alt="Capa do site <?= h($site['nome_site']) ?>"
                             loading="lazy" class="js-capa-site">
                        <?php endif; ?>
                        <div class="site-capa-padrao" aria-hidden="true"><?= h(iniciais_do_site($site['nome_site'])) ?></div>
                        <span class="site-materia"><?= h($site['disciplina']) ?></span>
                    </div>

                    <div class="site-corpo">
                        <h2 class="site-nome"><?= h($site['nome_site']) ?></h2>
                        <p class="site-equipe">Feito por <?= h($site['equipe']) ?></p>
                        <p class="site-descricao"><?= h($site['descricao']) ?></p>

                        <?php if ($site['perspectiva'] !== ''): ?>
                        <blockquote class="site-olhar">
                            <strong>O olhar da equipe:</strong> <?= h($site['perspectiva']) ?>
                        </blockquote>
                        <?php endif; ?>

                        <ul class="site-selos">
                            <?php if ($site['validado_por'] !== ''): ?>
                            <li class="selo selo-ok" title="Conteúdo conferido por quem entende do assunto"><?= icone('check') ?> Validado por <?= h($site['validado_por']) ?></li>
                            <?php else: ?>
                            <li class="selo selo-espera">Aguardando validação</li>
                            <?php endif; ?>
                            <?php if ($site['testado_por'] !== ''): ?>
                            <li class="selo selo-teste"><?= icone('celular') ?> Testado por <?= h($site['testado_por']) ?></li>
                            <?php endif; ?>
                        </ul>

                        <?php if ($site['fontes'] !== ''): ?>
                        <details class="site-fontes">
                            <summary>Fontes e créditos</summary>
                            <p><?= h($site['fontes']) ?></p>
                        </details>
                        <?php endif; ?>

                        <div class="site-rodape">
                            <span class="site-visitas"><?= (int) $site['acessos'] ?> visitas</span>
                            <a class="site-botao" href="ir.php?id=<?= (int) $site['id'] ?>" target="_blank" rel="noopener noreferrer">
                                Abrir site
                                <svg viewBox="0 0 24 24" aria-hidden="true"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                            </a>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>

            <p class="sites-nada js-sites-nada" hidden>Nenhum site encontrado com esse filtro. Tente outra palavra!</p>

            <p class="sites-dica">
                Dica: visitando um site novo pelo Studay você ganha XP e sobe no <a href="ranking.php">ranking</a>.
            </p>
        <?php endif; ?>
    </main>

</div>

<script src="../js/navigation.js"></script>
<script src="../js/sitesTurma.js"></script>
<script src="../js/gamificacao.js"></script>
<script src="../js/animacoes.js"></script>
</body>
</html>
