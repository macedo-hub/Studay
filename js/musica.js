/**
 * js/musica.js
 *
 * Sistema de música (portado da Versão 2).
 * Controla o modal de música: abrir/fechar, atalhos de playlist,
 * carregar URL personalizada do YouTube e o indicador flutuante
 * de "tocando".
 *
 * Substitui o antigo comportamento de mostrar um iframe ao passar
 * o mouse sobre o ícone de música na sidebar.
 */

(function () {
    'use strict';

    const sel = {
        overlay:         document.querySelector('.js-music-modal-overlay'),
        openBtns:        document.querySelectorAll('.js-open-music-modal'),
        closeBtn:        document.querySelector('.js-close-music-modal'),
        urlInput:        document.querySelector('.js-music-url-input'),
        loadBtn:         document.querySelector('.js-music-load-btn'),
        iframeContainer: document.querySelector('.js-music-iframe-container'),
        placeholder:     document.querySelector('.js-music-placeholder'),
        indicator:       document.querySelector('.js-music-indicator'),
        shortcuts:       document.querySelectorAll('.js-shortcut'),
    };

    let musicIsPlaying = false; // true quando o iframe já foi carregado com uma URL

    /**
     * Converte uma URL normal do YouTube para o formato embed.
     * Suporta os formatos:
     *   - youtube.com/watch?v=VIDEO_ID
     *   - youtu.be/VIDEO_ID
     *   - youtube.com/embed/VIDEO_ID (já está no formato correto)
     *
     * @param {string} url — URL digitada pelo usuário
     * @returns {string|null} — URL no formato embed ou null se inválida
     */
    function parseYoutubeUrl(url) {
        url = url.trim();

        if (url.includes('youtube.com/embed/')) {
            return url;
        }

        const watchMatch = url.match(/[?&]v=([a-zA-Z0-9_-]{11})/);
        if (watchMatch) {
            return `https://www.youtube.com/embed/${watchMatch[1]}`;
        }

        const shortMatch = url.match(/youtu\.be\/([a-zA-Z0-9_-]{11})/);
        if (shortMatch) {
            return `https://www.youtube.com/embed/${shortMatch[1]}`;
        }

        return null;
    }

    /**
     * Carrega uma URL no iframe do player de música.
     * Sem autoplay — o usuário precisa clicar no play manualmente.
     * Persiste a URL no localStorage para restaurar na próxima visita.
     *
     * @param {string} rawUrl — URL do YouTube (qualquer formato)
     * @param {HTMLElement|null} shortcutBtn — botão de atalho clicado (para marcá-lo como ativo)
     */
    function loadMusicUrl(rawUrl, shortcutBtn = null) {
        const embedUrl = parseYoutubeUrl(rawUrl);

        if (!embedUrl) {
            if (sel.urlInput) {
                sel.urlInput.style.borderColor = 'rgba(255, 77, 77, 0.6)';
                sel.urlInput.focus();
                setTimeout(() => {
                    sel.urlInput.style.borderColor = '';
                }, 2000);
            }
            return;
        }

        const existingIframe = sel.iframeContainer
            ? sel.iframeContainer.querySelector('#music-iframe')
            : null;
        if (existingIframe) existingIframe.remove();

        if (sel.placeholder) sel.placeholder.style.display = 'none';

        const iframe = document.createElement('iframe');
        iframe.id              = 'music-iframe';
        iframe.src             = embedUrl; // SEM ?autoplay=1 — play manual
        iframe.allow           = 'accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
        iframe.allowFullscreen = true;
        iframe.title           = 'Player de música para estudo';
        if (sel.iframeContainer) sel.iframeContainer.appendChild(iframe);

        musicIsPlaying = true;
        if (sel.urlInput) {
            sel.urlInput.value = rawUrl;
            sel.urlInput.style.borderColor = '';
        }

        sel.shortcuts.forEach(btn => btn.classList.remove('loaded'));
        if (shortcutBtn) shortcutBtn.classList.add('loaded');

        try {
            localStorage.setItem('studay_music_url', rawUrl);
            const activeShortcutUrl = shortcutBtn ? shortcutBtn.dataset.url : null;
            localStorage.setItem('studay_music_shortcut', activeShortcutUrl || '');
        } catch (e) {
            // localStorage pode não estar disponível em alguns contextos
        }
    }

    /** Abre o modal de música */
    function openMusicModal() {
        if (!sel.overlay) return;
        sel.overlay.classList.add('open');
        document.body.style.overflow = 'hidden';

        if (sel.indicator) sel.indicator.classList.remove('visible');

        setTimeout(() => {
            if (sel.urlInput) sel.urlInput.focus();
        }, 100);
    }

    /** Fecha o modal (a música continua tocando) */
    function closeMusicModal() {
        if (!sel.overlay) return;
        sel.overlay.classList.remove('open');
        document.body.style.overflow = '';

        if (musicIsPlaying && sel.indicator) {
            sel.indicator.classList.add('visible');
        }
    }

    sel.openBtns.forEach(btn => {
        btn.addEventListener('click', openMusicModal);
    });

    if (sel.closeBtn) {
        sel.closeBtn.addEventListener('click', closeMusicModal);
    }

    if (sel.overlay) {
        sel.overlay.addEventListener('click', function (e) {
            if (e.target === sel.overlay) closeMusicModal();
        });
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && sel.overlay && sel.overlay.classList.contains('open')) {
            closeMusicModal();
        }
    });

    if (sel.loadBtn) {
        sel.loadBtn.addEventListener('click', function () {
            const url = sel.urlInput ? sel.urlInput.value : '';
            if (url.trim()) loadMusicUrl(url);
        });
    }

    if (sel.urlInput) {
        sel.urlInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                if (this.value.trim()) loadMusicUrl(this.value);
            }
        });
    }

    sel.shortcuts.forEach(function (btn) {
        btn.addEventListener('click', function () {
            loadMusicUrl(this.dataset.url, this);
        });
    });

    if (sel.indicator) {
        sel.indicator.addEventListener('click', openMusicModal);
    }

    // Restaurar última URL do localStorage
    (function restoreLastUrl() {
        try {
            const savedUrl = localStorage.getItem('studay_music_url');
            const savedShortcut = localStorage.getItem('studay_music_shortcut');

            if (savedUrl && sel.urlInput) {
                sel.urlInput.value = savedUrl;

                if (savedShortcut) {
                    sel.shortcuts.forEach(function (btn) {
                        if (btn.dataset.url === savedShortcut) {
                            btn.classList.add('loaded');
                        }
                    });
                }
                // Nota: não carrega automaticamente o iframe — o usuário
                // precisa clicar em "Carregar" ou no atalho para iniciar.
            }
        } catch (e) {
            // localStorage indisponível — ignora silenciosamente
        }
    })();

})();
