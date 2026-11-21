/*
 * RANKING — busca a lista em api/ranking.php e desenha na tela.
 * Atualiza sozinho a cada 10 segundos enquanto a aba estiver aberta.
 */
(function () {
    'use strict';

    const INTERVALO_MS = 10000;

    const abas = document.querySelectorAll('.js-aba-ranking');
    const podio = document.querySelector('.js-ranking-podio');
    const lista = document.querySelector('.js-ranking-lista');
    const vazio = document.querySelector('.js-ranking-vazio');
    const erro = document.querySelector('.js-ranking-erro');
    const meu = document.querySelector('.js-ranking-meu');

    if (!podio || !lista) return;

    let periodo = 'semana';
    let temporizador = null;
    let xpAnterior = {};          // para dar um "brilhinho" em quem ganhou XP agora
    let primeiraVez = true;

    function criar(tag, classe, texto) {
        const el = document.createElement(tag);
        if (classe) el.className = classe;
        if (texto !== undefined) el.textContent = texto;
        return el;
    }

    /** Foto de perfil da pessoa, com uma plaquinha pequena mostrando o nível. */
    function fotoDoJogador(jogador, classe) {
        const caixa = criar('span', 'ranking-foto ' + (classe || ''));
        if (window.Studay) {
            caixa.appendChild(window.Studay.criarAvatar(jogador.avatar, 'ranking-avatar'));
        } else {
            const img = criar('img', 'ranking-avatar');
            img.alt = '';
            img.src = '../img/logo/faisca-sem-fundo.webp';
            caixa.appendChild(img);
        }
        caixa.appendChild(criar('span', 'ranking-foto-nivel', 'Nv ' + jogador.nivel));
        return caixa;
    }

    /** Medalha do pódio: um troféu nas cores ouro, prata e bronze (sem emoji). */
    function medalha(posicao) {
        const caixa = criar('span', 'podio-medalha medalha-' + posicao);
        if (window.Studay) caixa.appendChild(window.Studay.criarIcone('trofeu'));
        return caixa;
    }

    function desenharPodio(top3) {
        podio.textContent = '';
        // Ordem visual: 2º, 1º, 3º
        const ordem = [top3[1], top3[0], top3[2]];

        ordem.forEach(function (jogador) {
            if (!jogador) {
                podio.appendChild(criar('div', 'podio-vaga'));
                return;
            }
            const coluna = criar('div', 'podio-coluna podio-' + jogador.posicao + (jogador.sou_eu ? ' eu' : ''));
            coluna.appendChild(medalha(jogador.posicao));
            coluna.appendChild(fotoDoJogador(jogador, 'ranking-foto-grande'));
            coluna.appendChild(criar('strong', 'podio-apelido', jogador.apelido));
            coluna.appendChild(criar('span', 'podio-xp', jogador.xp + ' XP'));
            coluna.appendChild(criar('div', 'podio-degrau', jogador.posicao + 'º'));
            podio.appendChild(coluna);
        });
    }

    function desenharLista(demais) {
        lista.textContent = '';
        demais.forEach(function (jogador, i) {
            const item = criar('li', 'ranking-linha' + (jogador.sou_eu ? ' eu' : ''));
            item.style.animationDelay = (i * 40) + 'ms';

            item.appendChild(criar('span', 'ranking-posicao', jogador.posicao + 'º'));
            item.appendChild(fotoDoJogador(jogador));

            const nome = criar('div', 'ranking-nome');
            nome.appendChild(criar('strong', '', jogador.apelido + (jogador.sou_eu ? ' (você)' : '')));
            nome.appendChild(criar('span', '', 'Nv ' + jogador.nivel + ' · ' + jogador.nome_nivel));
            item.appendChild(nome);

            const xp = criar('span', 'ranking-xp', jogador.xp + ' XP');
            if (!primeiraVez && xpAnterior[jogador.apelido] !== undefined && xpAnterior[jogador.apelido] < jogador.xp) {
                item.classList.add('brilhou');
            }
            item.appendChild(xp);
            lista.appendChild(item);
        });
    }

    function desenharMinhaPosicao(eu) {
        if (!meu || meu.classList.contains('ranking-meu-convite')) return;
        if (!eu) {
            meu.textContent = periodo === 'semana'
                ? 'Você ainda não pontuou esta semana. Que tal um Pomodoro agora?'
                : 'Você ainda não tem XP. Bora começar?';
            return;
        }
        meu.textContent = 'Você está em ' + eu.posicao + 'º lugar, com ' + eu.xp + ' XP.';
    }

    function desenhar(dados) {
        const jogadores = dados.lista || [];

        if (jogadores.length === 0) {
            podio.textContent = '';
            lista.textContent = '';
            vazio.hidden = false;
        } else {
            vazio.hidden = true;
            desenharPodio(jogadores.slice(0, 3));
            desenharLista(jogadores.slice(3));
        }

        desenharMinhaPosicao(dados.eu);

        xpAnterior = {};
        jogadores.forEach(function (j) { xpAnterior[j.apelido] = j.xp; });
        primeiraVez = false;
    }

    async function atualizar() {
        try {
            const resposta = await fetch('../api/ranking.php?periodo=' + periodo, { credentials: 'same-origin' });
            const dados = await resposta.json();
            if (!dados.ok) throw new Error('resposta ruim');
            erro.hidden = true;
            desenhar(dados);
        } catch (e) {
            erro.hidden = false;
        }
    }

    function agendar() {
        clearInterval(temporizador);
        temporizador = setInterval(function () {
            if (document.visibilityState === 'visible') atualizar();
        }, INTERVALO_MS);
    }

    abas.forEach(function (aba) {
        aba.addEventListener('click', function () {
            if (aba.dataset.periodo === periodo) return;
            periodo = aba.dataset.periodo;
            abas.forEach(function (outra) {
                const ativa = outra === aba;
                outra.classList.toggle('ativo', ativa);
                outra.setAttribute('aria-selected', ativa ? 'true' : 'false');
            });
            primeiraVez = true;
            atualizar();
        });
    });

    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'visible') atualizar();
    });

    atualizar();
    agendar();
})();
