

(function () {
    'use strict';

    const sel = {
        overlay:       document.querySelector('.js-music-modal-overlay'),
        openBtns:      document.querySelectorAll('.js-open-music-modal'),
        closeBtn:      document.querySelector('.js-close-music-modal'),
        urlInput:      document.querySelector('.js-music-url-input'),
        loadBtn:       document.querySelector('.js-music-load-btn'),
        iframeContainer: document.querySelector('.js-music-iframe-container'),
        placeholder:   document.querySelector('.js-music-placeholder'),
        indicator:     document.querySelector('.js-music-indicator'),
        shortcuts:     document.querySelectorAll('.js-shortcut'),

        moreToggle:    document.querySelector('.js-more-toggle'),
        morePopup:     document.querySelector('.js-more-popup'),
        moreOverlay:   document.querySelector('.js-more-overlay'),
        moreChevron:   document.querySelector('.js-more-chevron'),
    };

    let musicIsPlaying = false; 


    /**
     * @param {string} url 
     * @returns {string|null}
     */
    function parseYoutubeUrl(url) {
        url = url.trim();

        // Aceita só o ID de 11 caracteres e remonta a URL (evita "javascript:" e outros esquemas)
        const embedMatch = url.match(/^https:\/\/(?:www\.)?youtube\.com\/embed\/([a-zA-Z0-9_-]{11})/);
        if (embedMatch) {
            return `https://www.youtube.com/embed/${embedMatch[1]}`;
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
     * @param {string} rawUrl 
     * @param {HTMLElement|null} shortcutBtn 
     */
    function loadMusicUrl(rawUrl, shortcutBtn = null) {
        const embedUrl = parseYoutubeUrl(rawUrl);

        if (!embedUrl) {
            sel.urlInput.style.borderColor = 'rgba(255, 77, 77, 0.6)';
            sel.urlInput.focus();
            setTimeout(() => {
                sel.urlInput.style.borderColor = '';
            }, 2000);
            return;
        }

        const existingIframe = sel.iframeContainer.querySelector('#music-iframe');
        if (existingIframe) existingIframe.remove();

        if (sel.placeholder) sel.placeholder.style.display = 'none';

        const iframe = document.createElement('iframe');
        iframe.id           = 'music-iframe';
        iframe.src          = embedUrl; 
        iframe.allow        = 'accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture';
        iframe.allowFullscreen = true;
        iframe.title        = 'Player de música para estudo';
        sel.iframeContainer.appendChild(iframe);

        musicIsPlaying = true;
        sel.urlInput.value = rawUrl;
        sel.urlInput.style.borderColor = '';

        sel.shortcuts.forEach(btn => btn.classList.remove('loaded'));
        if (shortcutBtn) shortcutBtn.classList.add('loaded');

        try {
            localStorage.setItem('studay_music_url', rawUrl);
            const activeShortcutUrl = shortcutBtn ? shortcutBtn.dataset.url : null;
            localStorage.setItem('studay_music_shortcut', activeShortcutUrl || '');
        } catch (e) {
        }
    }

    function openMusicModal() {
        if (!sel.overlay) return;
        sel.overlay.classList.add('open');
        document.body.style.overflow = 'hidden';

        if (sel.indicator) sel.indicator.classList.remove('visible');

        setTimeout(() => {
            if (sel.urlInput) sel.urlInput.focus();
        }, 100);
    }

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
            }
        } catch (e) {
        }
    })();

    function openMorePopup() {
        if (!sel.morePopup) return;
        sel.morePopup.classList.add('open');
        sel.moreOverlay && sel.moreOverlay.classList.add('open');
        sel.moreToggle && sel.moreToggle.setAttribute('aria-expanded', 'true');
        if (sel.moreChevron) {
            sel.moreChevron.style.transform = 'rotate(180deg)';
        }
    }

    function closeMorePopup() {
        if (!sel.morePopup) return;
        sel.morePopup.classList.remove('open');
        sel.moreOverlay && sel.moreOverlay.classList.remove('open');
        sel.moreToggle && sel.moreToggle.setAttribute('aria-expanded', 'false');
        if (sel.moreChevron) {
            sel.moreChevron.style.transform = '';
        }
    }

    if (sel.moreToggle) {
        sel.moreToggle.addEventListener('click', function () {
            const isOpen = sel.morePopup && sel.morePopup.classList.contains('open');
            isOpen ? closeMorePopup() : openMorePopup();
        });
    }

    if (sel.moreOverlay) {
        sel.moreOverlay.addEventListener('click', closeMorePopup);
    }

})();
