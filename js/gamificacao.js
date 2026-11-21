/*
 * GAMIFICAÇÃO DO STUDAY (lado do navegador)
 * - mostra avatar, nível e XP no menu
 * - avisa o servidor quando a pessoa termina algo (Pomodoro, Modo Faísca, flashcard...)
 * - mostra a animação "+XP" (com o motivo), as conquistas e a tela de "subiu de nível"
 * - mostra a dica de XP de cada página e abre a apresentação do Faísca
 *
 * Quem decide quanto XP vale cada coisa é o servidor (api/xp.php).
 * Outras páginas usam:  window.Studay.registrarXp('pomodoro', { minutos: 25 })
 */
(function () {
    'use strict';

    // Descobre onde está a raiz do site a partir do endereço deste próprio arquivo.
    const scriptAtual = document.currentScript;
    const RAIZ = scriptAtual && scriptAtual.src
        ? scriptAtual.src.replace(/js\/gamificacao\.js.*$/, '')
        : '../';

    const CAMINHOS = {
        estado:       RAIZ + 'api/estado.php',
        xp:           RAIZ + 'api/xp.php',
        apresentacao: RAIZ + 'api/apresentacao.php',
        login:        RAIZ + 'pages/login.php',
        registro:     RAIZ + 'pages/registro.php',
        progresso:    RAIZ + 'pages/progresso.php',
        perfil:       RAIZ + 'pages/perfil.php',
        comoFunciona: RAIZ + 'pages/comoFunciona.php',
        sair:         RAIZ + 'pages/sair.php',
        admin:        RAIZ + 'pages/adminSites.php',
    };

    // Desenhos dos ícones (os mesmos de includes/icones.php). São textos fixos, nunca vêm de usuário.
    const ICONES = {"chama":"<path d=\"M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z\"/>","cronometro":"<line x1=\"10\" y1=\"2\" x2=\"14\" y2=\"2\"/><line x1=\"12\" y1=\"14\" x2=\"15\" y2=\"11\"/><circle cx=\"12\" cy=\"14\" r=\"8\"/>","alvo":"<circle cx=\"12\" cy=\"12\" r=\"10\"/><circle cx=\"12\" cy=\"12\" r=\"6\"/><circle cx=\"12\" cy=\"12\" r=\"2\"/>","bussola":"<circle cx=\"12\" cy=\"12\" r=\"10\"/><polygon points=\"16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76\"/>","cartas":"<polygon points=\"12 2 2 7 12 12 22 7 12 2\"/><polyline points=\"2 17 12 22 22 17\"/><polyline points=\"2 12 12 17 22 12\"/>","calendario":"<rect x=\"3\" y=\"4\" width=\"18\" height=\"18\" rx=\"2\"/><line x1=\"16\" y1=\"2\" x2=\"16\" y2=\"6\"/><line x1=\"8\" y1=\"2\" x2=\"8\" y2=\"6\"/><line x1=\"3\" y1=\"10\" x2=\"21\" y2=\"10\"/>","trofeu":"<path d=\"M8 21h8M12 17v4M7 4h10v5a5 5 0 0 1-10 0V4z\"/><path d=\"M17 5h3v2a3 3 0 0 1-3 3M7 5H4v2a3 3 0 0 0 3 3\"/>","estrela":"<polygon points=\"12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2\"/>","cadeado":"<rect x=\"3\" y=\"11\" width=\"18\" height=\"11\" rx=\"2\"/><path d=\"M7 11V7a5 5 0 0 1 10 0v4\"/>","check":"<polyline points=\"20 6 9 17 4 12\"/>","celular":"<rect x=\"5\" y=\"2\" width=\"14\" height=\"20\" rx=\"2\"/><line x1=\"12\" y1=\"18\" x2=\"12.01\" y2=\"18\"/>","raio":"<polygon points=\"13 2 3 14 12 14 11 22 21 10 12 10 13 2\"/>","usuario":"<path d=\"M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2\"/><circle cx=\"12\" cy=\"7\" r=\"4\"/>","lapis":"<path d=\"M12 20h9\"/><path d=\"M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z\"/>","repetir":"<polyline points=\"23 4 23 10 17 10\"/><path d=\"M20.49 15a9 9 0 1 1-2.12-9.36L23 10\"/>","ajuda":"<circle cx=\"12\" cy=\"12\" r=\"10\"/><path d=\"M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3\"/><line x1=\"12\" y1=\"17\" x2=\"12.01\" y2=\"17\"/>","seta":"<line x1=\"5\" y1=\"12\" x2=\"19\" y2=\"12\"/><polyline points=\"12 5 19 12 12 19\"/>","globo":"<circle cx=\"12\" cy=\"12\" r=\"10\"/><line x1=\"2\" y1=\"12\" x2=\"22\" y2=\"12\"/><path d=\"M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z\"/>","livro":"<path d=\"M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z\"/><path d=\"M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z\"/>","sair":"<path d=\"M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4\"/><polyline points=\"16 17 21 12 16 7\"/><line x1=\"21\" y1=\"12\" x2=\"9\" y2=\"12\"/>","engrenagem":"<circle cx=\"12\" cy=\"12\" r=\"3\"/><path d=\"M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z\"/>"};

    const FAISCA_PADRAO = 'img/logo/faisca-sem-fundo.webp';

    let estadoAtual = null;
    let avisouSemLogin = false;
    let pilhaAvisos = null;
    let ganhoAtual = null;
    let celebracaoAdiada = null;   // o bônus de entrar espera a apresentação terminar

    /* ------------------------------------------------------------------ */
    /* Utilitários                                                         */
    /* ------------------------------------------------------------------ */

    function criarElemento(tag, classe, texto) {
        const el = document.createElement(tag);
        if (classe) el.className = classe;
        if (texto !== undefined) el.textContent = texto;   // textContent: nunca interpreta HTML
        return el;
    }

    function reduzirMovimento() {
        return window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    }

    function criarIcone(nome, classe) {
        const desenho = ICONES[nome] || ICONES.estrela;
        const modelo = document.createElement('template');
        modelo.innerHTML = '<svg class="icone ' + (classe || '') + '" viewBox="0 0 24 24" aria-hidden="true" focusable="false">' + desenho + '</svg>';
        return modelo.content.firstChild;
    }

    /** Foto de perfil (avatar). Se o arquivo falhar, usa o Faísca clássico. */
    function criarAvatar(arquivo, classe) {
        const img = criarElemento('img', classe || 'avatar');
        img.alt = '';
        img.loading = 'lazy';
        img.src = RAIZ + (arquivo || FAISCA_PADRAO);
        img.addEventListener('error', function () {
            if (img.dataset.reserva) return;
            img.dataset.reserva = '1';
            img.src = RAIZ + FAISCA_PADRAO;
        });
        return img;
    }

    /** Imagem do Faísca para um nível. Se o arquivo do nível ainda não existe, usa o Faísca padrão. */
    function criarImagemNivel(nivel, classe) {
        const img = criarElemento('img', classe);
        img.alt = '';
        img.dataset.nivel = String(nivel);
        img.src = RAIZ + 'img/faisca/nivel-' + nivel + '.webp';
        img.onerror = function () {
            img.onerror = null;
            img.src = RAIZ + FAISCA_PADRAO;
            img.classList.add('faisca-reserva');
        };
        return img;
    }

    async function pedirJson(url, opcoes) {
        const resposta = await fetch(url, Object.assign({ credentials: 'same-origin' }, opcoes || {}));
        let dados = null;
        try { dados = await resposta.json(); } catch (e) { dados = null; }
        return { status: resposta.status, dados: dados };
    }

    function esperar(ms) {
        return new Promise(function (resolver) { setTimeout(resolver, ms); });
    }

    /* ------------------------------------------------------------------ */
    /* Avisos pequenos (conquistas, recados)                                */
    /* ------------------------------------------------------------------ */

    function garantirPilhaAvisos() {
        if (pilhaAvisos) return pilhaAvisos;
        pilhaAvisos = criarElemento('div', 'toasts');
        pilhaAvisos.setAttribute('role', 'status');
        pilhaAvisos.setAttribute('aria-live', 'polite');
        document.body.appendChild(pilhaAvisos);
        return pilhaAvisos;
    }

    function mostrarAviso(opcoes) {
        const pilha = garantirPilhaAvisos();
        const aviso = criarElemento('div', 'toast' + (opcoes.tipo ? ' toast-' + opcoes.tipo : ''));

        const caixaIcone = criarElemento('span', 'toast-icone');
        caixaIcone.appendChild(criarIcone(opcoes.icone || 'estrela'));
        aviso.appendChild(caixaIcone);

        const texto = criarElemento('div', 'toast-texto');
        texto.appendChild(criarElemento('strong', '', opcoes.titulo));
        if (opcoes.texto) texto.appendChild(criarElemento('span', '', opcoes.texto));
        aviso.appendChild(texto);

        pilha.appendChild(aviso);
        requestAnimationFrame(function () { aviso.classList.add('toast-entrou'); });

        setTimeout(function () {
            aviso.classList.remove('toast-entrou');
            aviso.classList.add('toast-saiu');
            setTimeout(function () { aviso.remove(); }, 400);
        }, opcoes.duracao || 4600);
    }

    /* ------------------------------------------------------------------ */
    /* Animação "+XP"                                                       */
    /* ------------------------------------------------------------------ */

    function contarAte(elemento, valor, prefixo, sufixo) {
        if (reduzirMovimento()) {
            elemento.textContent = prefixo + valor + sufixo;
            return;
        }
        const inicio = performance.now();
        const duracao = 800;
        function passo(agora) {
            const t = Math.min(1, (agora - inicio) / duracao);
            const suave = 1 - Math.pow(1 - t, 3);
            elemento.textContent = prefixo + Math.round(valor * suave) + sufixo;
            if (t < 1) requestAnimationFrame(passo);
        }
        requestAnimationFrame(passo);
    }

    function faiscasDoXp(caixa) {
        if (reduzirMovimento()) return;
        for (let i = 0; i < 10; i++) {
            const ponto = criarElemento('i', 'ganho-xp-ponto');
            const angulo = (Math.PI * 2 * i) / 10;
            ponto.style.setProperty('--dx', Math.round(Math.cos(angulo) * (46 + Math.random() * 22)) + 'px');
            ponto.style.setProperty('--dy', Math.round(Math.sin(angulo) * (46 + Math.random() * 22)) + 'px');
            ponto.style.animationDelay = (Math.random() * 0.15) + 's';
            caixa.appendChild(ponto);
        }
    }

    /**
     * Mostra "+15 XP", o motivo e a barra do nível enchendo.
     * dados = { ganhou, rotulo, antes: {nivel...}, depois: {nivel...} }
     */
    function mostrarGanhoXp(dados) {
        if (ganhoAtual) { ganhoAtual.remove(); ganhoAtual = null; }

        const caixa = criarElemento('div', 'ganho-xp');
        caixa.setAttribute('role', 'status');

        const icone = criarElemento('div', 'ganho-xp-icone');
        icone.appendChild(criarIcone('raio'));
        faiscasDoXp(icone);
        caixa.appendChild(icone);

        const texto = criarElemento('div', 'ganho-xp-texto');
        const numero = criarElemento('strong', 'ganho-xp-numero', '+0 XP');
        texto.appendChild(numero);
        texto.appendChild(criarElemento('span', 'ganho-xp-motivo', dados.rotulo || 'Você ganhou XP'));

        const barra = criarElemento('div', 'ganho-xp-barra');
        const cheia = criarElemento('div', 'ganho-xp-barra-cheia');
        barra.appendChild(cheia);
        texto.appendChild(barra);

        const depois = dados.depois || null;
        const antes = dados.antes || depois;
        if (depois) {
            cheia.style.width = (antes ? antes.porcentagem : 0) + '%';
            const subiu = antes && depois.nivel > antes.nivel;
            const legenda = depois.nivel_maximo
                ? 'Nível ' + depois.nivel + ' · ' + depois.nome + ' (nível máximo)'
                : 'Nível ' + depois.nivel + ' · ' + depois.nome + ' · faltam ' + depois.faltam + ' XP para o próximo';
            texto.appendChild(criarElemento('small', 'ganho-xp-legenda', legenda));
            setTimeout(function () { cheia.style.width = (subiu ? 100 : depois.porcentagem) + '%'; }, 250);
        } else {
            barra.hidden = true;
        }

        caixa.appendChild(texto);
        document.body.appendChild(caixa);
        ganhoAtual = caixa;

        requestAnimationFrame(function () { caixa.classList.add('ganho-xp-entrou'); });
        contarAte(numero, dados.ganhou, '+', ' XP');

        setTimeout(function () {
            caixa.classList.remove('ganho-xp-entrou');
            caixa.classList.add('ganho-xp-saiu');
            setTimeout(function () { caixa.remove(); if (ganhoAtual === caixa) ganhoAtual = null; }, 450);
        }, 5200);
    }

    /* ------------------------------------------------------------------ */
    /* Tela de "subiu de nível"                                            */
    /* ------------------------------------------------------------------ */

    function confete(caixa) {
        if (reduzirMovimento()) return;
        const cores = ['#19dfbb', '#ffd166', '#ff8c42', '#ffffff', '#7bdff2'];
        for (let i = 0; i < 28; i++) {
            const pedaco = criarElemento('i', 'confete');
            pedaco.style.left = Math.random() * 100 + '%';
            pedaco.style.background = cores[i % cores.length];
            pedaco.style.animationDelay = (Math.random() * 0.5) + 's';
            pedaco.style.animationDuration = (1.6 + Math.random() * 1.4) + 's';
            pedaco.style.setProperty('--desvio', (Math.random() * 120 - 60) + 'px');
            caixa.appendChild(pedaco);
        }
    }

    function mostrarSubiuDeNivel(infoNivel) {
        const fundo = criarElemento('div', 'nivel-fundo');
        fundo.setAttribute('role', 'dialog');
        fundo.setAttribute('aria-modal', 'true');
        fundo.setAttribute('aria-label', 'Você subiu de nível');

        const caixa = criarElemento('div', 'nivel-caixa');
        const palco = criarElemento('div', 'nivel-palco');

        // Se existir o vídeo do Faísca comemorando, ele aparece. Se não, aparece a imagem do nível.
        palco.appendChild(criarImagemNivel(infoNivel.nivel, 'nivel-imagem'));

        const video = document.createElement('video');
        video.className = 'nivel-video';
        video.muted = true;
        video.autoplay = true;
        video.loop = true;
        video.playsInline = true;
        video.setAttribute('playsinline', '');
        video.addEventListener('loadeddata', function () {
            palco.classList.add('com-video');
            video.play().catch(function () {});
        });
        video.addEventListener('error', function () { video.remove(); });
        video.src = RAIZ + 'videos/faisca/faisca-comemorando.mp4';
        palco.appendChild(video);

        caixa.appendChild(palco);
        caixa.appendChild(criarElemento('p', 'nivel-pequeno', 'Você subiu de nível!'));
        caixa.appendChild(criarElemento('h2', 'nivel-titulo', 'Nível ' + infoNivel.nivel + ' · ' + infoNivel.nome));
        caixa.appendChild(criarElemento('p', 'nivel-texto', 'O Faísca ficou mais forte. Continue assim!'));

        const botao = criarElemento('button', 'btn-primary nivel-botao', 'Continuar');
        botao.type = 'button';
        caixa.appendChild(botao);
        confete(caixa);

        fundo.appendChild(caixa);
        document.body.appendChild(fundo);
        requestAnimationFrame(function () { fundo.classList.add('nivel-aberto'); });
        botao.focus();

        function fechar() {
            document.removeEventListener('keydown', aoApertarTecla);
            fundo.classList.remove('nivel-aberto');
            setTimeout(function () { fundo.remove(); }, 300);
        }
        function aoApertarTecla(e) { if (e.key === 'Escape') fechar(); }

        botao.addEventListener('click', fechar);
        fundo.addEventListener('click', function (e) { if (e.target === fundo) fechar(); });
        document.addEventListener('keydown', aoApertarTecla);
    }

    /* ------------------------------------------------------------------ */
    /* Menu lateral e botãozinho do celular                                */
    /* ------------------------------------------------------------------ */

    function linkMenu(texto, href, icone) {
        const link = criarElemento('a', 'perfil-link');
        link.href = href;
        link.appendChild(criarIcone(icone));
        link.appendChild(criarElemento('span', '', texto));
        return link;
    }

    function botaoApresentacao() {
        const botao = criarElemento('button', 'perfil-link js-abrir-apresentacao');
        botao.type = 'button';
        botao.appendChild(criarIcone('repetir'));
        botao.appendChild(criarElemento('span', '', 'Ver apresentação do Faísca'));
        return botao;
    }

    function desenharMenu(porcentagemAnterior) {
        const lateral = document.querySelector('.sidebar');
        if (lateral) {
            const antigo = lateral.querySelector('.perfil-menu');
            if (antigo) antigo.remove();
            lateral.appendChild(montarBlocoPerfil(porcentagemAnterior));
        }

        const antigoChip = document.querySelector('.chip-xp');
        if (antigoChip) antigoChip.remove();
        if (document.querySelector('.navbar-mobile')) {
            document.body.appendChild(montarChipCelular());
        }
    }

    function montarBlocoPerfil(porcentagemAnterior) {
        const bloco = criarElemento('div', 'perfil-menu');

        if (!estadoAtual || !estadoAtual.logado) {
            bloco.appendChild(criarElemento('p', 'perfil-convite', 'Entre para ganhar XP, evoluir o Faísca e subir no ranking.'));
            const acoes = criarElemento('div', 'perfil-acoes');
            const entrar = criarElemento('a', 'perfil-botao', 'Entrar');
            entrar.href = CAMINHOS.login;
            const criar = criarElemento('a', 'perfil-botao perfil-botao-claro', 'Criar conta');
            criar.href = CAMINHOS.registro;
            acoes.appendChild(entrar);
            acoes.appendChild(criar);
            bloco.appendChild(acoes);

            const lista = criarElemento('div', 'perfil-links');
            lista.appendChild(linkMenu('Como funciona o XP', CAMINHOS.comoFunciona, 'ajuda'));
            lista.appendChild(botaoApresentacao());
            bloco.appendChild(lista);
            return bloco;
        }

        const link = criarElemento('a', 'perfil-cartao');
        link.href = CAMINHOS.progresso;
        link.setAttribute('aria-label', 'Abrir meu progresso');

        const moldura = criarElemento('span', 'perfil-avatar-moldura');
        moldura.appendChild(criarAvatar(estadoAtual.avatar, 'perfil-avatar'));
        link.appendChild(moldura);

        const info = criarElemento('div', 'perfil-info');
        info.appendChild(criarElemento('strong', 'perfil-apelido', estadoAtual.apelido));
        info.appendChild(criarElemento('span', 'perfil-nivel',
            'Nível ' + estadoAtual.nivel.nivel + ' · ' + estadoAtual.nivel.nome));
        const barra = criarElemento('div', 'perfil-barra');
        const preenchida = criarElemento('div', 'perfil-barra-cheia');
        const alvo = estadoAtual.nivel.porcentagem;
        preenchida.style.width = (typeof porcentagemAnterior === 'number' ? porcentagemAnterior : alvo) + '%';
        if (typeof porcentagemAnterior === 'number') {
            requestAnimationFrame(function () { requestAnimationFrame(function () { preenchida.style.width = alvo + '%'; }); });
        }
        barra.appendChild(preenchida);
        info.appendChild(barra);
        info.appendChild(criarElemento('span', 'perfil-xp', estadoAtual.xp_total + ' XP'
            + (estadoAtual.nivel.nivel_maximo ? '' : ' · faltam ' + estadoAtual.nivel.faltam)));
        link.appendChild(info);
        bloco.appendChild(link);

        if (estadoAtual.sequencia_dias > 0) {
            const sequencia = criarElemento('p', 'perfil-sequencia');
            sequencia.appendChild(criarIcone('chama'));
            sequencia.appendChild(criarElemento('span', '', estadoAtual.sequencia_dias + (estadoAtual.sequencia_dias === 1 ? ' dia seguido' : ' dias seguidos')));
            bloco.appendChild(sequencia);
        }

        const lista = criarElemento('div', 'perfil-links');
        lista.appendChild(linkMenu('Editar perfil', CAMINHOS.perfil, 'lapis'));
        lista.appendChild(linkMenu('Como funciona o XP', CAMINHOS.comoFunciona, 'ajuda'));
        lista.appendChild(botaoApresentacao());
        if (estadoAtual.papel === 'admin') {
            lista.appendChild(linkMenu('Gerenciar sites', CAMINHOS.admin, 'engrenagem'));
        }
        lista.appendChild(linkMenu('Sair', CAMINHOS.sair, 'sair'));
        bloco.appendChild(lista);

        return bloco;
    }

    function montarChipCelular() {
        const chip = criarElemento('a', 'chip-xp');
        if (estadoAtual && estadoAtual.logado) {
            chip.href = CAMINHOS.progresso;
            chip.setAttribute('aria-label', 'Meu progresso: nível ' + estadoAtual.nivel.nivel + ', ' + estadoAtual.xp_total + ' XP');
            chip.appendChild(criarAvatar(estadoAtual.avatar, 'chip-xp-avatar'));
            chip.appendChild(criarElemento('span', 'chip-xp-nivel', 'Nv ' + estadoAtual.nivel.nivel));
            chip.appendChild(criarElemento('span', 'chip-xp-valor', estadoAtual.xp_total + ' XP'));
        } else {
            chip.href = CAMINHOS.login;
            chip.appendChild(criarElemento('span', 'chip-xp-valor', 'Entrar'));
        }
        return chip;
    }

    /* ------------------------------------------------------------------ */
    /* Dica de XP no topo da página                                        */
    /* ------------------------------------------------------------------ */

    function mostrarDicaXp(texto, logado) {
        if (!texto || document.querySelector('.dica-xp')) return;
        const titulo = document.querySelector('.content .page-title, .content h1');
        const conteudo = document.querySelector('.content');
        if (!conteudo) return;

        const dica = criarElemento('div', 'dica-xp');
        dica.appendChild(criarIcone('raio'));
        const corpo = criarElemento('p');
        corpo.appendChild(criarElemento('span', '', texto + ' '));
        if (!logado) {
            const entrar = criarElemento('a', '', 'Entre na sua conta');
            entrar.href = CAMINHOS.login;
            corpo.appendChild(entrar);
            corpo.appendChild(criarElemento('span', '', ' para guardar o seu XP. '));
        }
        const como = criarElemento('a', 'dica-xp-link', 'Como funciona o XP');
        como.href = CAMINHOS.comoFunciona;
        corpo.appendChild(como);
        dica.appendChild(corpo);

        if (titulo && titulo.parentNode) {
            titulo.insertAdjacentElement('afterend', dica);
        } else {
            conteudo.insertBefore(dica, conteudo.firstChild);
        }
    }

    /* ------------------------------------------------------------------ */
    /* Apresentação do Faísca (carrega só quando precisa)                  */
    /* ------------------------------------------------------------------ */

    let carregandoApresentacao = null;

    function carregarApresentacao() {
        if (window.StudayApresentacao) return Promise.resolve();
        if (carregandoApresentacao) return carregandoApresentacao;

        carregandoApresentacao = new Promise(function (resolver, rejeitar) {
            const css = document.createElement('link');
            css.rel = 'stylesheet';
            css.href = RAIZ + 'css/apresentacao.css';
            document.head.appendChild(css);

            const script = document.createElement('script');
            script.src = RAIZ + 'js/apresentacao.js';
            script.onload = resolver;
            script.onerror = rejeitar;
            document.head.appendChild(script);
        });
        return carregandoApresentacao;
    }

    function iniciarApresentacao() {
        return carregarApresentacao().then(function () {
            window.StudayApresentacao.abrir({
                raiz: RAIZ,
                logado: !!(estadoAtual && estadoAtual.logado),
                aoFechar: function () {
                    marcarApresentacaoVista();
                    if (celebracaoAdiada) {
                        const mostrar = celebracaoAdiada;
                        celebracaoAdiada = null;
                        setTimeout(mostrar, 500);
                    }
                },
                caminhos: CAMINHOS,
            });
        }).catch(function () {});
    }

    function marcarApresentacaoVista() {
        if (!estadoAtual || !estadoAtual.logado || !estadoAtual.apresentacao_pendente) return;
        estadoAtual.apresentacao_pendente = false;
        pedirJson(CAMINHOS.apresentacao, {
            method: 'POST',
            headers: { 'X-Requested-With': 'Studay' },
        }).catch(function () {});
    }

    /* ------------------------------------------------------------------ */
    /* Conversa com o servidor                                             */
    /* ------------------------------------------------------------------ */

    /** Mostra tudo o que aconteceu: "+XP", conquistas e subida de nível. */
    function celebrar(resumo, estadoNovo, estadoAntes) {
        const nivelAntes = estadoAntes && estadoAntes.nivel ? estadoAntes.nivel : null;
        const nivelDepois = estadoNovo && estadoNovo.nivel ? estadoNovo.nivel : null;

        if (resumo.ganhou > 0) {
            mostrarGanhoXp({ ganhou: resumo.ganhou, rotulo: resumo.rotulo, antes: nivelAntes, depois: nivelDepois });
        } else if (resumo.mensagem) {
            mostrarAviso({ icone: 'ajuda', titulo: 'Sem XP desta vez', texto: resumo.mensagem, duracao: 5600 });
        }

        (resumo.conquistas_novas || []).forEach(function (c, i) {
            setTimeout(function () {
                mostrarAviso({ icone: c.icone, titulo: 'Conquista: ' + c.nome, texto: c.descricao, tipo: 'conquista', duracao: 5400 });
            }, 900 + i * 1000);
        });

        if (resumo.subiu_de_nivel && nivelDepois) {
            setTimeout(function () { mostrarSubiuDeNivel(nivelDepois); }, 1800);
        }
    }

    async function buscarEstado() {
        const pagina = document.body.dataset.xpPagina || '';
        try {
            const r = await pedirJson(CAMINHOS.estado + (pagina ? '?pagina=' + encodeURIComponent(pagina) : ''));
            if (!r.dados) { desenharMenu(); return; }
            estadoAtual = r.dados;
            desenharMenu();
            mostrarDicaXp(r.dados.dica_xp, !!r.dados.logado);

            if (estadoAtual.logado) {
                const mostrarBonus = function () {
                    celebrar({
                        ganhou: estadoAtual.bonus_login || 0,
                        rotulo: estadoAtual.rotulo_bonus,
                        conquistas_novas: estadoAtual.conquistas_novas,
                        subiu_de_nivel: estadoAtual.subiu_de_nivel,
                    }, estadoAtual, { nivel: estadoAtual.nivel_antes || estadoAtual.nivel });
                };

                if (estadoAtual.apresentacao_pendente) {
                    // A apresentação abre primeiro. O "+XP" de entrar aparece quando ela fechar.
                    celebracaoAdiada = mostrarBonus;
                    await esperar(1100);
                    iniciarApresentacao();
                } else {
                    mostrarBonus();
                }
            }
        } catch (e) {
            // Sem internet ou servidor fora do ar: o site continua funcionando, só sem XP.
            desenharMenu();
        }
    }

    /** Avisa o servidor que um cronômetro (Pomodoro / Modo Faísca) começou. */
    function iniciarCronometro(tipo) {
        pedirJson(CAMINHOS.xp, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'Studay' },
            body: JSON.stringify({ acao: 'iniciar', tipo: tipo }),
        }).catch(function () {});
    }

    /** Pede XP ao servidor por algo que a pessoa terminou. */
    async function registrarXp(tipo, extras) {
        try {
            const estadoAntes = estadoAtual;
            const corpo = Object.assign({ acao: 'concluir', tipo: tipo }, extras || {});
            const r = await pedirJson(CAMINHOS.xp, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'Studay' },
                body: JSON.stringify(corpo),
            });

            if (r.status === 401) {
                if (!avisouSemLogin) {
                    avisouSemLogin = true;
                    mostrarAviso({ icone: 'cadeado', titulo: 'Entre na sua conta', texto: 'Assim o seu XP fica guardado e você entra no ranking.', duracao: 6000 });
                }
                return null;
            }
            if (!r.dados || !r.dados.ok) return null;
            if (r.dados.motivo === 'muito_rapido') return r.dados;

            if (r.dados.estado) {
                estadoAtual = Object.assign({}, estadoAtual || {}, r.dados.estado);
                desenharMenu(estadoAntes && estadoAntes.nivel ? estadoAntes.nivel.porcentagem : undefined);
            }
            celebrar(r.dados, r.dados.estado, estadoAntes);
            return r.dados;
        } catch (e) {
            return null;
        }
    }

    /* ------------------------------------------------------------------ */
    /* Início                                                              */
    /* ------------------------------------------------------------------ */

    function iniciar() {
        buscarEstado();

        // Quem clica em qualquer botão "Ver apresentação" abre o tour.
        document.addEventListener('click', function (e) {
            const botao = e.target.closest ? e.target.closest('.js-abrir-apresentacao') : null;
            if (!botao) return;
            e.preventDefault();
            const popup = document.querySelector('.js-more-popup.open');
            if (popup) popup.classList.remove('open');
            const sobreposicao = document.querySelector('.js-more-overlay.open');
            if (sobreposicao) sobreposicao.classList.remove('open');
            iniciarApresentacao();
        });

        // Página feita no servidor (ex.: quiz) pode deixar um recado pronto para mostrarmos aqui.
        if (window.STUDAY_EVENTO_XP) {
            const evento = window.STUDAY_EVENTO_XP;
            setTimeout(function () {
                celebrar(evento, evento.nivel ? { nivel: evento.nivel } : null,
                    evento.nivel_antes ? { nivel: evento.nivel_antes } : null);
            }, 600);
        }
    }

    window.Studay = {
        registrarXp: registrarXp,
        iniciarCronometro: iniciarCronometro,
        mostrarAviso: mostrarAviso,
        mostrarToast: mostrarAviso,
        criarImagemNivel: criarImagemNivel,
        criarAvatar: criarAvatar,
        criarIcone: criarIcone,
        iniciarApresentacao: iniciarApresentacao,
        raiz: RAIZ,
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', iniciar);
    } else {
        iniciar();
    }
})();
