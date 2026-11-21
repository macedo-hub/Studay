/*
 * APRESENTAÇÃO DO FAÍSCA
 * Um passeio curtinho pelo site: o Faísca aparece, fala frases bem pequenas
 * e destaca cada área do menu. Abre sozinho na primeira vez que a pessoa entra
 * e pode ser aberto de novo pelo botão "Ver apresentação do Faísca".
 *
 * Uso: window.StudayApresentacao.abrir({ raiz, logado, aoFechar, caminhos })
 */
(function () {
    'use strict';

    let aberta = false;

    function criar(tag, classe, texto) {
        const el = document.createElement(tag);
        if (classe) el.className = classe;
        if (texto !== undefined) el.textContent = texto;
        return el;
    }

    function semMovimento() {
        return window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    }

    /** Acha o primeiro elemento da lista de seletores que está aparecendo na tela. */
    function acharVisivel(seletores) {
        for (let i = 0; i < seletores.length; i++) {
            const lista = document.querySelectorAll(seletores[i]);
            for (let j = 0; j < lista.length; j++) {
                const caixa = lista[j].getBoundingClientRect();
                if (caixa.width > 0 && caixa.height > 0) return lista[j];
            }
        }
        return null;
    }

    function abrir(config) {
        if (aberta) return;
        aberta = true;

        const raiz = config.raiz || '../';
        const caminhos = config.caminhos || {};
        const logado = !!config.logado;
        const celular = function () { return window.matchMedia('(max-width: 768px)').matches; };

        const NIVEIS = [
            ['Fagulha', 0], ['Chama Viva', 100], ['Tocha', 250],
            ['Fogueira', 500], ['Labareda', 900], ['Estrela', 1500],
        ];

        // Cada passo: o que o Faísca diz (frases curtas) e o que ele aponta.
        const passos = [
            { tipo: 'centro', fala: 'Oi! Eu sou o Faísca.', extra: 'Vou te mostrar o Studay rapidinho.' },
            { foco: ['.sidebar-item[href$="metodos.html"]', '.navbar-mobile-item[href$="metodos.html"]'],
              fala: 'Aqui ficam os métodos de estudo.', extra: 'Pomodoro, flashcards, resumos e mais.' },
            { foco: ['.sidebar-item[href$="modoENEM.php"]', '.navbar-mobile-item[href$="modoENEM.php"]'],
              fala: 'Aqui você treina para o ENEM.', extra: 'Questões, redação e muito conteúdo.' },
            { foco: ['.sidebar-item[href$="sitesTurma.php"]', '.navbar-mobile-item[href$="sitesTurma.php"]'],
              fala: 'Aqui ficam os sites da turma.', extra: 'Cada colega fez o seu, do seu jeito.' },
            { foco: ['.sidebar-item[href$="modoFaisca.php"]'],
              fala: 'No Modo Faísca eu estudo com você.', extra: 'Quanto mais você foca, mais eu brilho.' },
            { tipo: 'xp', fala: 'Estudou? Ganhou XP!', extra: 'XP são os pontos do Studay. Um Pomodoro de 25 min vale 15 XP.' },
            { tipo: 'niveis', fala: 'Com XP você sobe de nível.', extra: 'Começa na Fagulha e chega até a Estrela.' },
            { foco: ['.sidebar-item[href$="ranking.php"]', '.navbar-mobile-item[href$="ranking.php"]'],
              fala: 'No ranking você vê quem mais estudou.', extra: 'Dá para se comparar com a turma, sem pressão.' },
            { tipo: 'fim', fala: 'Pronto! Bons estudos.', extra: logado ? 'Se quiser rever, é só clicar em "Ver apresentação do Faísca".' : 'Crie sua conta para guardar o seu XP.' },
        ];

        let indice = 0;
        let digitando = null;
        const elementoAnterior = document.activeElement;

        /* ---------- Estrutura ---------- */
        const fundo = criar('div', 'apr-fundo');
        fundo.setAttribute('role', 'dialog');
        fundo.setAttribute('aria-modal', 'true');
        fundo.setAttribute('aria-label', 'Apresentação do Faísca');

        const foco = criar('div', 'apr-foco');
        foco.hidden = true;

        const palco = criar('div', 'apr-palco');

        const mascote = criar('img', 'apr-faisca');
        mascote.alt = 'O Faísca, mascote do Studay';
        mascote.src = raiz + 'img/logo/faisca-sem-fundo.webp';

        const cartao = criar('div', 'apr-cartao');
        const fala = criar('p', 'apr-fala');
        fala.setAttribute('aria-live', 'polite');
        const extra = criar('p', 'apr-extra');
        const demo = criar('div', 'apr-demo');
        const pontos = criar('div', 'apr-pontos');
        pontos.setAttribute('aria-hidden', 'true');
        passos.forEach(function () { pontos.appendChild(criar('i', 'apr-ponto')); });

        const botoes = criar('div', 'apr-botoes');
        const pular = criar('button', 'apr-pular', 'Pular');
        pular.type = 'button';
        const voltar = criar('button', 'apr-botao apr-botao-claro', 'Voltar');
        voltar.type = 'button';
        const proximo = criar('button', 'btn-primary apr-botao', 'Próximo');
        proximo.type = 'button';
        botoes.appendChild(pular);
        botoes.appendChild(voltar);
        botoes.appendChild(proximo);

        cartao.appendChild(fala);
        cartao.appendChild(extra);
        cartao.appendChild(demo);
        cartao.appendChild(pontos);
        cartao.appendChild(botoes);
        palco.appendChild(mascote);
        palco.appendChild(cartao);
        fundo.appendChild(foco);
        fundo.appendChild(palco);
        document.body.appendChild(fundo);
        document.body.classList.add('apr-aberta');
        requestAnimationFrame(function () { fundo.classList.add('apr-visivel'); });

        /* ---------- Conteúdo de cada tipo de passo ---------- */
        function montarDemoXp() {
            demo.appendChild(criar('span', 'apr-xp-numero', '+15 XP'));
            const barra = criar('div', 'apr-xp-barra');
            barra.appendChild(criar('div', 'apr-xp-cheia'));
            demo.appendChild(barra);
            demo.appendChild(criar('small', '', 'Pomodoro  ·  Quiz do ENEM  ·  Flashcards  ·  Resumos'));
        }

        function montarDemoNiveis() {
            const lista = criar('ol', 'apr-niveis');
            NIVEIS.forEach(function (n, i) {
                const item = criar('li');
                item.style.animationDelay = (i * 0.09) + 's';
                item.appendChild(criar('b', '', String(i + 1)));
                item.appendChild(criar('span', '', n[0]));
                item.appendChild(criar('em', '', n[1] + ' XP'));
                lista.appendChild(item);
            });
            demo.appendChild(lista);
        }

        function montarFim() {
            const area = criar('div', 'apr-fim');
            const como = criar('a', 'apr-botao apr-botao-claro', 'Ver como o XP funciona');
            como.href = caminhos.comoFunciona || (raiz + 'pages/comoFunciona.php');
            area.appendChild(como);
            if (!logado) {
                const criarConta = criar('a', 'apr-botao apr-botao-claro', 'Criar conta');
                criarConta.href = caminhos.registro || (raiz + 'pages/registro.php');
                area.appendChild(criarConta);
            }
            demo.appendChild(area);
        }

        /* ---------- Escrever devagarinho ---------- */
        function escrever(elemento, texto, aoTerminar) {
            if (digitando) { clearInterval(digitando); digitando = null; }
            if (semMovimento()) { elemento.textContent = texto; if (aoTerminar) aoTerminar(); return; }
            elemento.textContent = '';
            let i = 0;
            digitando = setInterval(function () {
                i++;
                elemento.textContent = texto.slice(0, i);
                if (i >= texto.length) {
                    clearInterval(digitando);
                    digitando = null;
                    if (aoTerminar) aoTerminar();
                }
            }, 26);
        }

        function terminarDeEscrever() {
            if (!digitando) return;
            clearInterval(digitando);
            digitando = null;
            fala.textContent = passos[indice].fala;
            extra.classList.add('apr-extra-visivel');
        }

        /* ---------- Posicionar o holofote ---------- */
        function posicionarFoco(seletores) {
            const alvo = seletores ? acharVisivel(seletores) : null;
            if (!alvo) {
                foco.hidden = true;
                fundo.classList.remove('apr-com-foco');
                return;
            }
            const c = alvo.getBoundingClientRect();
            const folga = 6;
            foco.style.top = (c.top - folga) + 'px';
            foco.style.left = (c.left - folga) + 'px';
            foco.style.width = (c.width + folga * 2) + 'px';
            foco.style.height = (c.height + folga * 2) + 'px';
            foco.hidden = false;
            fundo.classList.add('apr-com-foco');
        }

        /* ---------- Mostrar um passo ---------- */
        function mostrar(i) {
            indice = i;
            const passo = passos[i];

            demo.textContent = '';
            extra.classList.remove('apr-extra-visivel');
            extra.textContent = passo.extra || '';
            fundo.classList.toggle('apr-centro', !passo.foco || !acharVisivel(passo.foco));
            posicionarFoco(passo.foco);

            // Reinicia o balancinho do Faísca a cada fala nova.
            mascote.classList.remove('apr-fala-nova');
            void mascote.offsetWidth;
            mascote.classList.add('apr-fala-nova');

            if (passo.tipo === 'xp') montarDemoXp();
            if (passo.tipo === 'niveis') montarDemoNiveis();
            if (passo.tipo === 'fim') montarFim();

            Array.prototype.forEach.call(pontos.children, function (p, n) {
                p.classList.toggle('apr-ponto-ativo', n === i);
                p.classList.toggle('apr-ponto-feito', n < i);
            });

            voltar.hidden = i === 0;
            pular.hidden = i === passos.length - 1;
            proximo.textContent = i === passos.length - 1 ? 'Começar a estudar' : (i === 0 ? 'Vamos lá' : 'Próximo');

            escrever(fala, passo.fala, function () { extra.classList.add('apr-extra-visivel'); });
        }

        /* ---------- Fechar ---------- */
        function fechar() {
            if (digitando) clearInterval(digitando);
            document.removeEventListener('keydown', aoApertarTecla);
            window.removeEventListener('resize', aoRedimensionar);
            document.body.classList.remove('apr-aberta');
            fundo.classList.remove('apr-visivel');
            setTimeout(function () { fundo.remove(); }, 300);
            aberta = false;
            if (typeof config.aoFechar === 'function') config.aoFechar();
            if (elementoAnterior && elementoAnterior.focus) { try { elementoAnterior.focus(); } catch (e) {} }
        }

        function avancar() {
            if (digitando) { terminarDeEscrever(); return; }
            if (indice >= passos.length - 1) { fechar(); return; }
            mostrar(indice + 1);
        }

        function aoApertarTecla(e) {
            if (e.key === 'Escape') fechar();
            else if (e.key === 'ArrowRight' || e.key === 'Enter') {
                if (document.activeElement && document.activeElement.tagName === 'A') return;
                e.preventDefault();
                avancar();
            } else if (e.key === 'ArrowLeft' && indice > 0) {
                mostrar(indice - 1);
            }
        }

        function aoRedimensionar() { mostrar(indice); }

        proximo.addEventListener('click', avancar);
        voltar.addEventListener('click', function () { if (indice > 0) mostrar(indice - 1); });
        pular.addEventListener('click', fechar);
        fala.addEventListener('click', terminarDeEscrever);
        document.addEventListener('keydown', aoApertarTecla);
        window.addEventListener('resize', aoRedimensionar);

        mostrar(0);
        proximo.focus();
    }

    window.StudayApresentacao = { abrir: abrir };
})();
