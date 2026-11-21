/*
 * ANIMAÇÕES DO STUDAY
 * - elementos com a classe "revelar" aparecem suavemente quando entram na tela
 * - barras de progresso "enchem" quando aparecem
 * - botões ganham um efeitinho de onda ao clicar
 * Tudo respeita quem pediu menos movimento no celular/computador.
 */
(function () {
    'use strict';

    const menosMovimento = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* Revelar ao rolar a página */
    function ativarRevelar() {
        const itens = Array.from(document.querySelectorAll('.revelar'));
        if (itens.length === 0) return;

        if (menosMovimento || !('IntersectionObserver' in window)) {
            itens.forEach(function (el) { el.classList.add('revelado'); });
            preencherBarras(document);
            return;
        }

        // Quem já aparece na tela entra em sequência (um depois do outro)
        let ordem = 0;
        const observador = new IntersectionObserver(function (entradas) {
            entradas.forEach(function (entrada) {
                if (!entrada.isIntersecting) return;
                const el = entrada.target;
                el.style.transitionDelay = Math.min(ordem * 70, 420) + 'ms';
                ordem++;
                el.classList.add('revelado');
                observador.unobserve(el);
                // Depois que entrou, limpa o atraso para o hover ficar rápido
                setTimeout(function () { el.style.transitionDelay = ''; }, 900);
                if (el.querySelector('.js-barra-xp, .js-barra-dia')) preencherBarras(el);
            });
            setTimeout(function () { ordem = 0; }, 500);
        }, { threshold: 0.12 });

        itens.forEach(function (el) { observador.observe(el); });
    }

    /* Barras que enchem (XP e gráfico dos 7 dias) */
    function preencherBarras(raiz) {
        raiz.querySelectorAll('.js-barra-xp').forEach(function (barra) {
            barra.style.width = (barra.dataset.largura || 0) + '%';
        });
        raiz.querySelectorAll('.js-barra-dia').forEach(function (barra) {
            barra.style.height = (barra.dataset.altura || 0) + '%';
        });
    }

    function ativarBarrasDireto() {
        document.querySelectorAll('.js-barra-xp, .js-barra-dia').forEach(function (barra) {
            if (barra.closest('.revelar')) return;   // essas enchem junto com o "revelar"
            preencherBarras(barra.parentElement || document);
        });
    }

    /* Efeito de onda nos botões principais */
    function ativarOnda() {
        if (menosMovimento) return;
        document.addEventListener('pointerdown', function (e) {
            const alvo = e.target.closest('.btn-primary, .site-botao, .hero-btn-primary, .metodo-btn, .ranking-aba, .sites-chip');
            if (!alvo) return;

            const caixa = alvo.getBoundingClientRect();
            const onda = document.createElement('span');
            const tamanho = Math.max(caixa.width, caixa.height);
            onda.className = 'onda';
            onda.style.width = onda.style.height = tamanho + 'px';
            onda.style.left = (e.clientX - caixa.left - tamanho / 2) + 'px';
            onda.style.top = (e.clientY - caixa.top - tamanho / 2) + 'px';
            alvo.classList.add('com-onda');
            alvo.appendChild(onda);
            setTimeout(function () { onda.remove(); }, 650);
        });
    }

    /* Entrada suave da página */
    function entrarNaPagina() {
        document.body.classList.add('pagina-pronta');
    }

    function iniciar() {
        entrarNaPagina();
        ativarRevelar();
        ativarBarrasDireto();
        ativarOnda();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', iniciar);
    } else {
        iniciar();
    }
})();
