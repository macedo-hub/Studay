<?php
/*
 * MENU DO STUDAY (barra lateral no computador + barra de baixo no celular).
 * Use nas páginas .php assim:   desenhar_menu('ranking');
 * Chaves: metodos, enem, sites, faisca, ranking, progresso, calculadora, sobre
 */

require_once __DIR__ . '/icones.php';

function desenhar_menu(string $ativo = ''): void
{
    $icones = [
        'metodos' => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
        'enem' => '<path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c0 1.657 2.686 3 6 3s6-1.343 6-3v-5"/>',
        'sites' => '<circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>',
        'faisca' => '<path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/>',
        'ranking' => '<path d="M8 21h8M12 17v4M7 4h10v5a5 5 0 0 1-10 0V4z"/><path d="M17 5h3v2a3 3 0 0 1-3 3M7 5H4v2a3 3 0 0 0 3 3"/>',
        'progresso' => '<line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>',
        'calculadora' => '<rect x="4" y="2" width="16" height="20" rx="2"/><line x1="8" y1="6" x2="16" y2="6"/><line x1="16" y1="10" x2="16" y2="10"/><line x1="12" y1="10" x2="12" y2="10"/><line x1="8" y1="10" x2="8" y2="10"/><line x1="16" y1="14" x2="16" y2="14"/><line x1="12" y1="14" x2="12" y2="14"/><line x1="8" y1="14" x2="8" y2="14"/><line x1="16" y1="18" x2="16" y2="18"/><line x1="12" y1="18" x2="12" y2="18"/><line x1="8" y1="18" x2="8" y2="18"/>',
        'musica' => '<path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/>',
        'sobre' => '<circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="8"/><line x1="12" y1="12" x2="12" y2="16"/>',
        'mais' => '<circle cx="5" cy="12" r="1.6"/><circle cx="12" cy="12" r="1.6"/><circle cx="19" cy="12" r="1.6"/>',
    ] + ICONES_SVG;

    // [chave, link, texto da barra lateral, texto da barra de baixo]
    $itensLaterais = [
        ['metodos',     'metodos.html',    'Métodos'],
        ['enem',        'modoENEM.php',    'modoENEM'],
        ['sites',       'sitesTurma.php',  'Sites da Turma'],
        ['faisca',      'modoFaisca.php',  'Modo Faísca'],
        ['ranking',     'ranking.php',     'Ranking'],
        ['calculadora', 'avaliacoes.html', 'Calculadora de média'],
    ];

    $classe = function (string $chave, string $base) use ($ativo): string {
        return $base . ($chave === $ativo ? ' active' : '');
    };
    $svg = function (string $chave) use ($icones): string {
        return '<svg viewBox="0 0 24 24" aria-hidden="true">' . $icones[$chave] . '</svg>';
    };
    ?>

<!-- SIDEBAR -->
<aside class="sidebar" aria-label="Menu de navegação principal">

    <div class="sidebar-header">
        <a href="metodos.html" class="studay-logo sidebar-logo" aria-label="Studay — ir para métodos">
            <span class="logo-stu">Stu</span><span class="logo-day">day</span>
        </a>
    </div>

    <nav class="sidebar-nav" aria-label="Páginas do Studay">
        <?php foreach ($itensLaterais as [$chave, $link, $texto]): ?>
        <a href="<?= $link ?>" class="<?= $classe($chave, 'sidebar-item') ?>" aria-label="Ir para <?= $texto ?>">
            <?= $svg($chave) ?>
            <span><?= $texto ?></span>
        </a>
        <?php endforeach; ?>

        <!-- Música — abre o modal -->
        <button class="sidebar-item sidebar-item-music js-open-music-modal"
                aria-label="Abrir player de música"
                aria-haspopup="dialog"
                type="button">
            <?= $svg('musica') ?>
            <span>Música</span>
        </button>

        <a href="sobre.html" class="<?= $classe('sobre', 'sidebar-item') ?>" aria-label="Ir para página Sobre">
            <?= $svg('sobre') ?>
            <span>Sobre</span>
        </a>
    </nav>

</aside>


<nav class="navbar-mobile" aria-label="Navegação principal no celular">
    <div class="navbar-mobile-inner">

        <a href="metodos.html" class="<?= $classe('metodos', 'navbar-mobile-item') ?>" aria-label="Métodos de estudo">
            <?= $svg('metodos') ?>
            <span>Métodos</span>
        </a>

        <a href="modoENEM.php" class="<?= $classe('enem', 'navbar-mobile-item') ?>" aria-label="modoENEM">
            <?= $svg('enem') ?>
            <span>ENEM</span>
        </a>

        <a href="sitesTurma.php" class="<?= $classe('sites', 'navbar-mobile-item') ?>" aria-label="Sites da Turma">
            <?= $svg('sites') ?>
            <span>Sites</span>
        </a>

        <a href="ranking.php" class="<?= $classe('ranking', 'navbar-mobile-item') ?>" aria-label="Ranking">
            <?= $svg('ranking') ?>
            <span>Ranking</span>
        </a>

        <button class="navbar-mobile-item js-more-toggle"
                type="button"
                aria-label="Mais opções"
                aria-haspopup="true"
                aria-expanded="false">
            <?= $svg('mais') ?>
            <span>Mais</span>
        </button>

    </div>
</nav>

<div class="navbar-more-overlay js-more-overlay" aria-hidden="true"></div>

<div class="navbar-more-popup js-more-popup" role="menu" aria-label="Mais opções">
    <a href="progresso.php" class="navbar-more-item" role="menuitem"><?= $svg('progresso') ?> <span>Meu progresso</span></a>
    <a href="perfil.php" class="navbar-more-item" role="menuitem"><?= $svg('usuario') ?> <span>Meu perfil</span></a>
    <a href="modoFaisca.php" class="navbar-more-item" role="menuitem"><?= $svg('faisca') ?> <span>Modo Faísca</span></a>
    <button type="button" class="navbar-more-item js-open-music-modal" role="menuitem"><?= $svg('musica') ?> <span>Música</span></button>
    <a href="avaliacoes.html" class="navbar-more-item" role="menuitem"><?= $svg('calculadora') ?> <span>Calculadora de média</span></a>
    <a href="comoFunciona.php" class="navbar-more-item" role="menuitem"><?= $svg('ajuda') ?> <span>Como funciona o XP</span></a>
    <button type="button" class="navbar-more-item js-abrir-apresentacao" role="menuitem"><?= $svg('repetir') ?> <span>Ver apresentação do Faísca</span></button>
    <a href="sobre.html" class="navbar-more-item" role="menuitem"><?= $svg('sobre') ?> <span>Sobre</span></a>
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

<?php
}
