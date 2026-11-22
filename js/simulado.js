/*
 * SIMULADO DO ENEM (parte que roda no navegador)
 *
 * O servidor (api/simulado.php) sorteia as questões, confere o gabarito e dá o XP.
 * Aqui ficam: as opções, a tela da prova, os cronômetros, a dúvida, o marca-texto,
 * o resultado e a prévia de impressão.
 */
(function () {
    'use strict';

    const API = '../api/simulado.php';
    const CHAVE_ANDAMENTO = 'studay_simulado_andamento_v1';
    const CHAVE_PREFS = 'studay_simulado_prefs_v1';

    const $ = (id) => document.getElementById(id);
    const dadosPagina = JSON.parse($('sim-dados').textContent);
    const logado = !!dadosPagina.logado;
    const NOMES_AREA = dadosPagina.areas;

    /* ------------------------------------------------------------------ */
    /* Utilidades                                                          */
    /* ------------------------------------------------------------------ */

    async function pedir(corpo) {
        try {
            const r = await fetch(API, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'Studay' },
                body: JSON.stringify(corpo),
            });
            let dados = null;
            try { dados = await r.json(); } catch (e) { dados = null; }
            return { status: r.status, dados: dados };
        } catch (e) {
            return { status: 0, dados: null };
        }
    }

    function guardar(chave, valor, onde) {
        try { (onde || localStorage).setItem(chave, JSON.stringify(valor)); } catch (e) { /* sem armazenamento: tudo bem */ }
    }
    function ler(chave, onde) {
        try { const t = (onde || localStorage).getItem(chave); return t ? JSON.parse(t) : null; } catch (e) { return null; }
    }
    function apagar(chave, onde) {
        try { (onde || localStorage).removeItem(chave); } catch (e) { /* ok */ }
    }

    function formatarTempo(seg) {
        seg = Math.max(0, Math.round(seg));
        const h = Math.floor(seg / 3600);
        const m = Math.floor((seg % 3600) / 60);
        const s = seg % 60;
        const dois = (n) => String(n).padStart(2, '0');
        return h > 0 ? h + ':' + dois(m) + ':' + dois(s) : dois(m) + ':' + dois(s);
    }

    function formatarDuracaoLonga(seg) {
        seg = Math.round(seg);
        if (seg < 60) return seg + ' s';
        const m = Math.floor(seg / 60);
        if (m < 60) return m + ' min';
        return Math.floor(m / 60) + ' h ' + (m % 60) + ' min';
    }

    // As imagens vêm como "img/enem/..." e esta página fica dentro de /pages
    function ajustarImagens(html) {
        return String(html || '').replace(/src="img\//g, 'src="../img/');
    }

    function aviso(titulo, texto, icone) {
        if (window.Studay && window.Studay.mostrarAviso) {
            window.Studay.mostrarAviso({ icone: icone || 'ajuda', titulo: titulo, texto: texto || '', duracao: 4500 });
        }
    }

    function mostrarErro(texto) {
        const caixa = $('sim-erro');
        caixa.textContent = texto;
        caixa.classList.remove('hidden');
    }
    function limparErro() { $('sim-erro').classList.add('hidden'); }

    function confirmar(titulo, texto, rotuloSim, rotuloNao) {
        const dialogo = $('sim-dialogo');
        if (!dialogo || typeof dialogo.showModal !== 'function') {
            return Promise.resolve(window.confirm(titulo + '\n\n' + texto));
        }
        $('sim-dialogo-titulo').textContent = titulo;
        $('sim-dialogo-texto').textContent = texto;
        $('sim-dialogo-sim').textContent = rotuloSim || 'Confirmar';
        $('sim-dialogo-nao').textContent = rotuloNao || 'Voltar';
        return new Promise(function (resolver) {
            const aoFechar = function () {
                dialogo.removeEventListener('close', aoFechar);
                resolver(dialogo.returnValue === 'ok');
            };
            dialogo.returnValue = '';
            dialogo.addEventListener('close', aoFechar);
            dialogo.showModal();
        });
    }

    /* ------------------------------------------------------------------ */
    /* Telas                                                               */
    /* ------------------------------------------------------------------ */

    const TELAS = { config: 'sim-config', prova: 'sim-prova', resultado: 'sim-resultado', previa: 'sim-previa' };
    let telaAtual = 'config';

    function mostrarTela(nome) {
        telaAtual = nome;
        Object.keys(TELAS).forEach(function (chave) {
            $(TELAS[chave]).classList.toggle('hidden', chave !== nome);
        });
        document.body.classList.toggle('sim-em-prova', nome === 'prova');
        document.body.classList.toggle('sim-em-previa', nome === 'previa');
        window.scrollTo(0, 0);
    }

    /* ------------------------------------------------------------------ */
    /* 1. Opções                                                           */
    /* ------------------------------------------------------------------ */

    const ORDEM_AREAS = Object.keys(NOMES_AREA);
    let prefs = Object.assign({}, dadosPagina.padrao, dadosPagina.prefs);
    let embaralharMexido = false;
    let temporizadorSalvar = null;

    if (!logado) {
        const local = ler(CHAVE_PREFS);
        if (local) {
            const urlQtd = dadosPagina.veioDaUrl ? prefs.quantidade : null;
            prefs = Object.assign({}, prefs, local);
            if (urlQtd) prefs.quantidade = urlQtd;
        }
    }

    function totalDisponivel() {
        const contagem = dadosPagina.contagem;
        if (prefs.origem === 'duvidas') return dadosPagina.duvidas;
        if (prefs.origem === 'erros') return dadosPagina.erros;
        const porAno = prefs.anos.map(function (ano) {
            let soma = 0;
            prefs.areas.forEach(function (area) { soma += ((contagem[ano] || {})[area]) || 0; });
            return soma;
        });
        if (!porAno.length) return 0;
        if (prefs.modo === 'mesmo_ano') return Math.max.apply(null, porAno);
        return porAno.reduce(function (a, b) { return a + b; }, 0);
    }

    function textoLista(itens) {
        if (itens.length <= 1) return itens.join('');
        return itens.slice(0, -1).join(', ') + ' e ' + itens[itens.length - 1];
    }

    function atualizarResumo() {
        const disp = totalDisponivel();
        const qtd = Math.min(prefs.quantidade, disp || prefs.quantidade);
        const partes = [];
        partes.push('<strong>' + qtd + ' ' + (qtd === 1 ? 'questão' : 'questões') + '</strong>');

        if (prefs.areas.length === ORDEM_AREAS.length) partes.push('todas as áreas');
        else partes.push(textoLista(ORDEM_AREAS.filter(function (a) { return prefs.areas.indexOf(a) >= 0; }).map(function (a) { return NOMES_AREA[a].curto; })));

        const anos = prefs.anos.slice().sort();
        partes.push('ENEM ' + textoLista(anos.map(String)) + (prefs.modo === 'mesmo_ano' ? ' (tudo do mesmo ano)' : ''));
        if (prefs.origem === 'duvidas') partes.push('só as suas dúvidas');
        if (prefs.origem === 'erros') partes.push('só as que você errou');
        partes.push(prefs.resposta === 'imediata' ? 'gabarito na hora' : 'gabarito só no final');
        if (prefs.tempo === 'nenhum') partes.push('sem limite de tempo');
        else if (prefs.tempo === 'por_questao') partes.push(prefs.minutos_questao + ' min por questão');
        else partes.push(formatarDuracaoLonga(prefs.minutos_total * 60) + ' no total');

        let extra = '';
        if (disp === 0) extra = '<p class="sim-resumo-alerta">Com essas opções não há nenhuma questão disponível.</p>';
        else if (prefs.quantidade > disp) extra = '<p class="sim-resumo-alerta">Só existem ' + disp + ' questões com essas opções, então o simulado terá ' + disp + '.</p>';
        $('sim-resumo').innerHTML = partes.join(' &middot; ') + extra;
    }

    function desenharOpcoes() {
        // quantidade
        const botoes = document.querySelectorAll('#grupo-quantidade .sim-pilula');
        botoes.forEach(function (b) {
            const ativo = Number(b.dataset.valor) === prefs.quantidade;
            b.classList.toggle('ativo', ativo);
            b.setAttribute('aria-checked', ativo ? 'true' : 'false');
        });
        $('campo-quantidade').value = prefs.quantidade;

        // anos e áreas
        document.querySelectorAll('#grupo-anos .sim-pilula').forEach(function (b) {
            const ativo = prefs.anos.indexOf(Number(b.dataset.ano)) >= 0;
            b.classList.toggle('ativo', ativo);
            b.setAttribute('aria-pressed', ativo ? 'true' : 'false');
        });
        document.querySelectorAll('#grupo-areas .sim-pilula').forEach(function (b) {
            const ativo = prefs.areas.indexOf(b.dataset.area) >= 0;
            b.classList.toggle('ativo', ativo);
            b.setAttribute('aria-pressed', ativo ? 'true' : 'false');
        });

        // rádios
        ['modo', 'origem', 'resposta', 'tempo'].forEach(function (nome) {
            document.querySelectorAll('input[name="' + nome + '"]').forEach(function (r) {
                r.checked = r.value === prefs[nome];
                r.closest('.sim-opcao').classList.toggle('ativo', r.checked);
            });
        });

        // dúvidas e erros só existem para quem entrou na conta
        const infoDuvidas = $('texto-duvidas');
        const infoErros = $('texto-erros');
        const radioDuvidas = document.querySelector('input[name="origem"][value="duvidas"]');
        const radioErros = document.querySelector('input[name="origem"][value="erros"]');
        if (!logado) {
            infoDuvidas.textContent = 'Entre na conta para usar.';
            infoErros.textContent = 'Entre na conta para usar.';
            radioDuvidas.disabled = true;
            radioErros.disabled = true;
        } else {
            infoDuvidas.textContent = dadosPagina.duvidas > 0 ? dadosPagina.duvidas + ' questões que você marcou.' : 'Você ainda não marcou nenhuma.';
            infoErros.textContent = dadosPagina.erros > 0 ? dadosPagina.erros + ' questões para refazer.' : 'Nenhum erro guardado ainda.';
            radioDuvidas.disabled = dadosPagina.duvidas === 0;
            radioErros.disabled = dadosPagina.erros === 0;
        }
        radioDuvidas.closest('.sim-opcao').classList.toggle('desativada', radioDuvidas.disabled);
        radioErros.closest('.sim-opcao').classList.toggle('desativada', radioErros.disabled);

        $('campo-idioma').value = prefs.idioma;
        $('campo-embaralhar').checked = !!prefs.embaralhar;
        $('texto-embaralhar').textContent = prefs.embaralhar ? 'As questões vêm em ordem aleatória.' : 'Na ordem da prova: por área e número.';
        $('campo-cronometro').checked = !!prefs.cronometro;
        $('campo-minutos-questao').value = prefs.minutos_questao;
        $('campo-minutos-total').value = prefs.minutos_total;
        $('linha-minutos-questao').classList.toggle('hidden', prefs.tempo !== 'por_questao');
        $('linha-minutos-total').classList.toggle('hidden', prefs.tempo !== 'total');

        $('sim-nota-salvo').textContent = logado
            ? 'Suas escolhas ficam salvas na sua conta.'
            : 'Suas escolhas ficam salvas neste aparelho. Entre na conta para levá-las com você.';

        atualizarResumo();
    }

    function preferenciasMudaram() {
        desenharOpcoes();
        limparErro();
        clearTimeout(temporizadorSalvar);
        temporizadorSalvar = setTimeout(salvarPreferencias, 700);
    }

    function salvarPreferencias() {
        if (logado) pedir({ acao: 'salvar_preferencias', opcoes: prefs });
        else guardar(CHAVE_PREFS, prefs);
    }

    function alternar(lista, valor) {
        const i = lista.indexOf(valor);
        if (i >= 0) {
            if (lista.length === 1) return lista;          // sempre fica pelo menos um marcado
            return lista.filter(function (x) { return x !== valor; });
        }
        return lista.concat([valor]);
    }

    function ligarOpcoes() {
        $('grupo-quantidade').addEventListener('click', function (e) {
            const b = e.target.closest('.sim-pilula');
            if (!b) return;
            const n = Number(b.dataset.valor);
            prefs.quantidade = n;
            if (!embaralharMexido) prefs.embaralhar = n < 90;
            preferenciasMudaram();
        });
        $('campo-quantidade').addEventListener('input', function () {
            const n = parseInt(this.value, 10);
            if (n >= 1 && n <= 180) {
                prefs.quantidade = n;
                clearTimeout(temporizadorSalvar);
                temporizadorSalvar = setTimeout(salvarPreferencias, 700);
                document.querySelectorAll('#grupo-quantidade .sim-pilula').forEach(function (b) {
                    const ativo = Number(b.dataset.valor) === n;
                    b.classList.toggle('ativo', ativo);
                    b.setAttribute('aria-checked', ativo ? 'true' : 'false');
                });
                atualizarResumo();
            }
        });
        $('campo-quantidade').addEventListener('blur', function () { this.value = prefs.quantidade; });

        $('grupo-anos').addEventListener('click', function (e) {
            const b = e.target.closest('.sim-pilula');
            if (!b) return;
            prefs.anos = alternar(prefs.anos, Number(b.dataset.ano));
            preferenciasMudaram();
        });
        $('grupo-areas').addEventListener('click', function (e) {
            const b = e.target.closest('.sim-pilula');
            if (!b) return;
            prefs.areas = alternar(prefs.areas, b.dataset.area);
            preferenciasMudaram();
        });
        document.querySelector('.sim-atalhos').addEventListener('click', function (e) {
            const b = e.target.closest('button');
            if (!b) return;
            const tipo = b.dataset.areas;
            prefs.areas = ORDEM_AREAS.filter(function (a) {
                return tipo === 'todas' || NOMES_AREA[a].dia === (tipo === 'dia1' ? 1 : 2);
            });
            preferenciasMudaram();
        });

        ['modo', 'origem', 'resposta', 'tempo'].forEach(function (nome) {
            document.querySelectorAll('input[name="' + nome + '"]').forEach(function (r) {
                r.addEventListener('change', function () {
                    prefs[nome] = r.value;
                    preferenciasMudaram();
                });
            });
        });

        $('campo-idioma').addEventListener('change', function () { prefs.idioma = this.value; preferenciasMudaram(); });
        $('campo-embaralhar').addEventListener('change', function () {
            prefs.embaralhar = this.checked;
            embaralharMexido = true;
            preferenciasMudaram();
        });
        $('campo-cronometro').addEventListener('change', function () { prefs.cronometro = this.checked; preferenciasMudaram(); });

        $('campo-minutos-questao').addEventListener('input', function () {
            const n = parseInt(this.value, 10);
            if (n >= 1 && n <= 15) { prefs.minutos_questao = n; atualizarResumo(); clearTimeout(temporizadorSalvar); temporizadorSalvar = setTimeout(salvarPreferencias, 700); }
        });
        $('campo-minutos-total').addEventListener('input', function () {
            const n = parseInt(this.value, 10);
            if (n >= 5 && n <= 330) { prefs.minutos_total = n; atualizarResumo(); clearTimeout(temporizadorSalvar); temporizadorSalvar = setTimeout(salvarPreferencias, 700); }
        });
        $('sugerir-tempo').addEventListener('click', function () {
            prefs.minutos_total = Math.max(5, Math.min(330, Math.min(prefs.quantidade, totalDisponivel() || prefs.quantidade) * 3));
            preferenciasMudaram();
        });

        $('sim-form').addEventListener('submit', function (e) {
            e.preventDefault();
            comecar();
        });
        $('sim-imprimir').addEventListener('click', imprimirDaConfiguracao);
    }

    /* ------------------------------------------------------------------ */
    /* 2. A prova                                                          */
    /* ------------------------------------------------------------------ */

    let estado = null;           // tudo sobre o simulado em andamento
    let ultimoTick = Date.now();
    let relogio = null;
    let marcaLigada = false;
    let temporizadorAvanco = null;

    function novoEstado(resposta, opcoes) {
        return {
            tentativa: resposta.tentativa,
            opcoes: JSON.parse(JSON.stringify(opcoes)),
            questoes: resposta.questoes,
            indice: 0,
            respostas: {},
            duvidas: {},
            riscadas: {},
            marcas: {},
            conferidas: {},
            gasto: {},
            esgotadas: {},
            segundos: 0,
            finalizado: false,
            resultado: null,
            revisao: false,
        };
    }

    function guardarAndamento() {
        if (!estado || estado.finalizado) return;
        guardar(CHAVE_ANDAMENTO, estado, sessionStorage);
    }

    async function comecar(opcoesExtras) {
        limparErro();
        const opcoes = Object.assign({}, prefs, opcoesExtras || {});
        const botao = $('sim-comecar');
        botao.disabled = true;
        botao.textContent = 'Montando…';
        salvarPreferencias();

        const r = await pedir({ acao: 'montar', opcoes: opcoes });
        botao.disabled = false;
        botao.textContent = 'Começar o simulado';

        if (!r.dados || !r.dados.ok) {
            const msg = r.dados && r.dados.mensagem ? r.dados.mensagem : 'Não consegui montar o simulado agora. Tente de novo em instantes.';
            mostrarErro(msg);
            if (telaAtual !== 'config') mostrarTela('config');
            return;
        }

        estado = novoEstado(r.dados, opcoes);
        (r.dados.duvidas || []).forEach(function (id) { estado.duvidas[id] = true; });
        guardarAndamento();
        iniciarProva();
    }

    function iniciarProva() {
        $('sim-relogio-restante').classList.toggle('hidden', estado.opcoes.tempo === 'nenhum');
        $('sim-relogio-corrido').classList.toggle('hidden', !estado.opcoes.cronometro);
        $('sim-tempo-rotulo').textContent = estado.opcoes.tempo === 'por_questao' ? 'nesta questão' : 'restam';
        mostrarTela('prova');
        desenharMapa();
        irPara(estado.indice || 0);
        ultimoTick = Date.now();
        clearInterval(relogio);
        relogio = setInterval(tique, 1000);
    }

    function questaoAtual() { return estado.questoes[estado.indice]; }

    function bloqueada(id) {
        return estado.revisao || estado.finalizado || !!estado.conferidas[id] || !!estado.esgotadas[id];
    }

    /* ---------- Cronômetros ---------- */

    function tique() {
        if (!estado || estado.finalizado || estado.revisao || telaAtual !== 'prova') return;
        const agora = Date.now();
        const delta = Math.floor((agora - ultimoTick) / 1000);
        if (delta < 1) return;
        ultimoTick += delta * 1000;

        const q = questaoAtual();
        estado.segundos += delta;
        if (!bloqueada(q.id)) estado.gasto[q.id] = (estado.gasto[q.id] || 0) + delta;

        atualizarRelogios();

        if (estado.opcoes.tempo === 'total' && estado.segundos >= estado.opcoes.minutos_total * 60) {
            aviso('O tempo acabou', 'Vou corrigir o seu simulado agora.', 'cronometro');
            finalizar(true);
            return;
        }
        if (estado.opcoes.tempo === 'por_questao' && !estado.esgotadas[q.id] && !estado.conferidas[q.id]
            && (estado.gasto[q.id] || 0) >= estado.opcoes.minutos_questao * 60) {
            esgotarQuestao();
        }
        if (estado.segundos % 5 === 0) guardarAndamento();
    }

    function atualizarRelogios() {
        const q = questaoAtual();
        $('sim-tempo-corrido').textContent = formatarTempo(estado.segundos);
        let restante = 0;
        if (estado.opcoes.tempo === 'total') restante = estado.opcoes.minutos_total * 60 - estado.segundos;
        else if (estado.opcoes.tempo === 'por_questao') restante = estado.opcoes.minutos_questao * 60 - (estado.gasto[q.id] || 0);
        $('sim-tempo-restante').textContent = formatarTempo(restante);
        $('sim-relogio-restante').classList.toggle('acabando', restante <= 30 && restante > 0 && estado.opcoes.tempo !== 'nenhum');
    }

    async function esgotarQuestao() {
        const q = questaoAtual();
        estado.esgotadas[q.id] = true;
        aviso('Tempo da questão acabou', 'Vamos para a próxima.', 'cronometro');
        if (estado.opcoes.resposta === 'imediata') {
            await conferirNoServidor(q, estado.respostas[q.id] || '');
        }
        desenharQuestao();
        desenharMapa();
        guardarAndamento();
        clearTimeout(temporizadorAvanco);
        const idEsgotada = q.id;
        temporizadorAvanco = setTimeout(function () {
            if (!estado || estado.finalizado || questaoAtual().id !== idEsgotada) return;
            if (estado.indice < estado.questoes.length - 1) irPara(estado.indice + 1);
            else pedirFinalizar();
        }, 2200);
    }

    /* ---------- Desenho da questão ---------- */

    function nomeIdioma(i) { return i === 'ingles' ? 'Inglês' : (i === 'espanhol' ? 'Espanhol' : ''); }

    function desenharQuestao() {
        const q = questaoAtual();
        const total = estado.questoes.length;
        const revisao = estado.revisao;

        $('sim-posicao').textContent = (revisao ? 'Revisão · ' : '') + 'Questão ' + (estado.indice + 1) + ' de ' + total;

        // Dados da questão: de qual prova ela é
        const meta = $('sim-meta');
        meta.innerHTML = '';
        const chips = ['ENEM ' + q.ano, 'Dia ' + q.dia, 'Questão ' + q.numero + ' da prova', q.area_nome];
        if (q.idioma) chips.push(nomeIdioma(q.idioma));
        chips.forEach(function (t, i) {
            const s = document.createElement('span');
            s.className = 'sim-chip' + (i === 0 ? ' sim-chip-ano' : '');
            s.textContent = t;
            meta.appendChild(s);
        });

        // Texto
        const corpo = $('sim-corpo');
        const salvo = estado.marcas[q.id];
        if (salvo && salvo.corpo) {
            corpo.innerHTML = salvo.corpo;
        } else {
            let html = '';
            if (q.enunciado) html += '<div class="sim-enunciado">' + ajustarImagens(q.enunciado) + '</div>';
            if (q.introducao) html += '<div class="sim-introducao">' + ajustarImagens(q.introducao) + '</div>';
            corpo.innerHTML = html;
        }

        // Alternativas
        const lista = $('sim-alternativas');
        lista.innerHTML = '';
        const conferida = estado.conferidas[q.id];
        const gabarito = revisao && estado.resultado ? estado.resultado.gabarito[q.id] : (conferida ? conferida.correta : null);
        const escolhida = estado.respostas[q.id] || '';
        const riscadas = estado.riscadas[q.id] || [];

        q.alternativas.forEach(function (alt) {
            const li = document.createElement('li');
            li.className = 'sim-alt';
            li.dataset.letra = alt.l;
            if (escolhida === alt.l) li.classList.add('sel');
            if (riscadas.indexOf(alt.l) >= 0) li.classList.add('riscada');
            if (gabarito) {
                if (alt.l === gabarito) li.classList.add('certa');
                else if (escolhida === alt.l) li.classList.add('errada');
            }

            const letra = document.createElement('button');
            letra.type = 'button';
            letra.className = 'sim-alt-letra';
            letra.textContent = alt.l;
            letra.setAttribute('aria-label', 'Escolher a alternativa ' + alt.l);

            const texto = document.createElement('div');
            texto.className = 'sim-alt-texto';
            const marcado = salvo && salvo.alts && salvo.alts[alt.l];
            if (marcado) {
                texto.innerHTML = marcado;
            } else {
                let h = alt.t || '';
                if (alt.img) h += '<img src="../' + alt.img + '" alt="Alternativa ' + alt.l + '" loading="lazy">';
                texto.innerHTML = ajustarImagens(h);
            }

            const riscar = document.createElement('button');
            riscar.type = 'button';
            riscar.className = 'sim-alt-riscar';
            riscar.title = 'Riscar essa alternativa';
            riscar.setAttribute('aria-label', 'Riscar a alternativa ' + alt.l);
            riscar.textContent = riscadas.indexOf(alt.l) >= 0 ? 'desfazer' : 'riscar';

            li.appendChild(letra);
            li.appendChild(texto);
            li.appendChild(riscar);
            lista.appendChild(li);
        });
        lista.classList.toggle('travada', bloqueada(q.id));

        // Dúvida
        const duvida = !!estado.duvidas[q.id];
        $('ferr-duvida').setAttribute('aria-pressed', duvida ? 'true' : 'false');
        $('ferr-duvida').classList.toggle('ativo', duvida);

        // Feedback, tempo esgotado e botão de confirmar
        const feedback = $('sim-feedback');
        feedback.classList.add('hidden');
        feedback.className = 'sim-feedback hidden';
        if (gabarito && (revisao || conferida)) {
            const acertou = escolhida === gabarito;
            feedback.classList.remove('hidden');
            feedback.classList.add(acertou ? 'ok' : (escolhida ? 'erro' : 'branco'));
            feedback.textContent = acertou
                ? 'Você acertou. A resposta é a alternativa ' + gabarito + '.'
                : (escolhida ? 'Você marcou ' + escolhida + '. A resposta certa é a alternativa ' + gabarito + '.'
                              : 'Você deixou em branco. A resposta certa é a alternativa ' + gabarito + '.');
        }
        $('sim-tempo-acabou').classList.toggle('hidden', !(estado.esgotadas[q.id] && !revisao));

        const mostraConfirmar = estado.opcoes.resposta === 'imediata' && !revisao && !conferida && !estado.esgotadas[q.id];
        $('sim-confirmar-linha').classList.toggle('hidden', !mostraConfirmar);
        $('sim-confirmar').disabled = !escolhida;

        // Botões de baixo e do topo
        $('sim-anterior').disabled = estado.indice === 0;
        const ultima = estado.indice === total - 1;
        const proxima = $('sim-proxima');
        if (revisao) {
            proxima.innerHTML = 'Próxima &rarr;';
            proxima.disabled = ultima;
            $('sim-finalizar-topo').textContent = 'Voltar ao resultado';
        } else {
            proxima.disabled = false;
            proxima.innerHTML = ultima ? 'Finalizar' : 'Próxima &rarr;';
            $('sim-finalizar-topo').textContent = 'Finalizar';
        }

        atualizarProgresso();
        atualizarRelogios();
        marcarMapaAtual();
        $('sim-questao').classList.remove('trocou');
        void $('sim-questao').offsetWidth;
        $('sim-questao').classList.add('trocou');
    }

    function atualizarProgresso() {
        const total = estado.questoes.length;
        const feitas = estado.questoes.filter(function (q) { return estado.respostas[q.id]; }).length;
        $('sim-progresso-barra').style.width = (feitas / total * 100) + '%';
    }

    function irPara(i) {
        if (!estado) return;
        clearTimeout(temporizadorAvanco);
        estado.indice = Math.max(0, Math.min(estado.questoes.length - 1, i));
        desenharQuestao();
        fecharMapaCelular();
        if (!estado.revisao) guardarAndamento();
        const topo = $('sim-prova').getBoundingClientRect().top + window.scrollY - 8;
        window.scrollTo({ top: Math.max(0, topo), behavior: 'auto' });
        esconderFlutuante();
    }

    /* ---------- Mapa ---------- */

    function desenharMapa() {
        const mapa = $('sim-mapa');
        mapa.innerHTML = '';
        estado.questoes.forEach(function (q, i) {
            const b = document.createElement('button');
            b.type = 'button';
            b.dataset.i = i;
            b.textContent = i + 1;
            b.setAttribute('aria-label', 'Ir para a questão ' + (i + 1));
            mapa.appendChild(b);
        });
        marcarMapaAtual();
    }

    function marcarMapaAtual() {
        const botoes = $('sim-mapa').children;
        for (let i = 0; i < botoes.length; i++) {
            const q = estado.questoes[i];
            const b = botoes[i];
            const classes = [];
            if (estado.respostas[q.id]) classes.push('m-resp');
            if (estado.duvidas[q.id]) classes.push('m-duvida');
            if (i === estado.indice) classes.push('m-atual');
            if (estado.revisao && estado.resultado) {
                const certa = estado.resultado.gabarito[q.id];
                const r = estado.respostas[q.id];
                classes.push(!r ? 'm-branca' : (r === certa ? 'm-certa' : 'm-errada'));
            }
            b.className = classes.join(' ');
        }
    }

    function abrirMapaCelular() {
        $('sim-mapa-caixa').classList.add('aberto');
        document.body.classList.add('sim-mapa-aberto');
    }
    function fecharMapaCelular() {
        $('sim-mapa-caixa').classList.remove('aberto');
        document.body.classList.remove('sim-mapa-aberto');
    }

    /* ---------- Responder ---------- */

    function escolher(letra) {
        const q = questaoAtual();
        if (bloqueada(q.id)) return;
        if (estado.respostas[q.id] === letra && estado.opcoes.resposta !== 'imediata') {
            delete estado.respostas[q.id];          // tocar de novo tira a resposta
        } else {
            estado.respostas[q.id] = letra;
        }
        desenharQuestao();
        guardarAndamento();
    }

    async function conferirNoServidor(q, letra) {
        const r = await pedir({ acao: 'conferir', id: q.id, resposta: letra });
        if (r.dados && r.dados.ok) {
            estado.conferidas[q.id] = { correta: r.dados.correta, resposta: r.dados.resposta, acertou: r.dados.acertou };
            if (r.dados.resposta) estado.respostas[q.id] = r.dados.resposta;
            return true;
        }
        return false;
    }

    async function confirmarResposta() {
        const q = questaoAtual();
        const letra = estado.respostas[q.id];
        if (!letra || bloqueada(q.id)) return;
        $('sim-confirmar').disabled = true;
        const ok = await conferirNoServidor(q, letra);
        if (!ok) {
            aviso('Sem conexão', 'Não consegui conferir agora. Tente de novo.', 'ajuda');
            $('sim-confirmar').disabled = false;
            return;
        }
        desenharQuestao();
        desenharMapa();
        guardarAndamento();
    }

    function alternarDuvida() {
        const q = questaoAtual();
        const novo = !estado.duvidas[q.id];
        if (novo) estado.duvidas[q.id] = true; else delete estado.duvidas[q.id];
        $('ferr-duvida').setAttribute('aria-pressed', novo ? 'true' : 'false');
        $('ferr-duvida').classList.toggle('ativo', novo);
        marcarMapaAtual();
        guardarAndamento();
        if (logado) pedir({ acao: 'duvida', id: q.id, marcar: novo });
    }

    /* ---------- Marca-texto ---------- */

    function dentroDoTexto(no) {
        const el = no.nodeType === 1 ? no : no.parentElement;
        return !!(el && el.closest && el.closest('#sim-corpo, .sim-alt-texto'));
    }

    function selecaoValida() {
        const sel = window.getSelection();
        if (!sel || sel.rangeCount === 0 || sel.isCollapsed) return null;
        const range = sel.getRangeAt(0);
        if (!dentroDoTexto(range.startContainer) || !dentroDoTexto(range.endContainer)) return null;
        return range;
    }

    function salvarMarcasDaQuestao() {
        const q = questaoAtual();
        const corpo = $('sim-corpo');
        const alts = {};
        let temMarca = !!corpo.querySelector('mark.sim-marca-texto');
        document.querySelectorAll('#sim-alternativas .sim-alt').forEach(function (li) {
            const t = li.querySelector('.sim-alt-texto');
            if (t.querySelector('mark.sim-marca-texto')) { alts[li.dataset.letra] = t.innerHTML; temMarca = true; }
        });
        if (temMarca) estado.marcas[q.id] = { corpo: corpo.innerHTML, alts: alts, todas: true };
        else delete estado.marcas[q.id];
        // Se só o enunciado tem marca, as alternativas continuam sendo desenhadas do zero
        guardarAndamento();
    }

    function marcarTrecho(range) {
        const raiz = range.commonAncestorContainer.nodeType === 3 ? range.commonAncestorContainer.parentNode : range.commonAncestorContainer;
        const nos = [];
        const caminhante = document.createTreeWalker(raiz, NodeFilter.SHOW_TEXT, {
            acceptNode: function (n) {
                return range.intersectsNode(n) && n.nodeValue.trim() !== '' && dentroDoTexto(n)
                    ? NodeFilter.FILTER_ACCEPT : NodeFilter.FILTER_REJECT;
            },
        });
        while (caminhante.nextNode()) nos.push(caminhante.currentNode);

        nos.forEach(function (no) {
            let ini = 0;
            let fim = no.nodeValue.length;
            if (no === range.startContainer) ini = range.startOffset;
            if (no === range.endContainer) fim = range.endOffset;
            if (fim <= ini) return;
            if (no.parentElement.closest('mark.sim-marca-texto')) return;
            let alvo = no;
            if (fim < no.nodeValue.length) no.splitText(fim);
            if (ini > 0) alvo = no.splitText(ini);
            const m = document.createElement('mark');
            m.className = 'sim-marca-texto';
            alvo.parentNode.insertBefore(m, alvo);
            m.appendChild(alvo);
        });
        window.getSelection().removeAllRanges();
        salvarMarcasDaQuestao();
    }

    function desmarcar(marca) {
        const pai = marca.parentNode;
        while (marca.firstChild) pai.insertBefore(marca.firstChild, marca);
        pai.removeChild(marca);
        pai.normalize();
    }

    function limparTrecho(range) {
        document.querySelectorAll('#sim-questao mark.sim-marca-texto').forEach(function (m) {
            if (range.intersectsNode(m)) desmarcar(m);
        });
        window.getSelection().removeAllRanges();
        salvarMarcasDaQuestao();
    }

    function mostrarFlutuante(range) {
        const caixa = $('sim-flutuante');
        const r = range.getBoundingClientRect();
        caixa.classList.remove('hidden');
        const largura = caixa.offsetWidth || 190;
        let esquerda = r.left + r.width / 2 - largura / 2;
        esquerda = Math.max(8, Math.min(window.innerWidth - largura - 8, esquerda));
        let topo = r.top - 48;
        if (topo < 8) topo = r.bottom + 10;
        caixa.style.left = esquerda + 'px';
        caixa.style.top = topo + 'px';
    }
    function esconderFlutuante() { $('sim-flutuante').classList.add('hidden'); }

    let temporizadorSelecao = null;
    function aoMudarSelecao() {
        clearTimeout(temporizadorSelecao);
        temporizadorSelecao = setTimeout(function () {
            if (telaAtual !== 'prova') return;
            const range = selecaoValida();
            if (range) mostrarFlutuante(range); else esconderFlutuante();
        }, 220);
    }

    function ligarMarcaTexto() {
        $('ferr-marca').addEventListener('click', function () {
            marcaLigada = !marcaLigada;
            this.setAttribute('aria-pressed', marcaLigada ? 'true' : 'false');
            this.classList.toggle('ativo', marcaLigada);
            $('sim-questao').classList.toggle('modo-marca', marcaLigada);
            if (marcaLigada) aviso('Marca-texto ligado', 'Selecione um trecho para pintar de rosa. Toque numa marca para tirar.', 'lapis');
        });

        document.addEventListener('selectionchange', aoMudarSelecao);

        // No computador, com o marca-texto ligado, soltar o mouse já pinta.
        document.addEventListener('mouseup', function () {
            if (!marcaLigada || telaAtual !== 'prova') return;
            setTimeout(function () {
                const range = selecaoValida();
                if (range) { marcarTrecho(range); esconderFlutuante(); }
            }, 10);
        });

        $('flut-marcar').addEventListener('mousedown', function (e) { e.preventDefault(); });
        $('flut-limpar').addEventListener('mousedown', function (e) { e.preventDefault(); });
        $('flut-marcar').addEventListener('click', function () {
            const range = selecaoValida();
            if (range) marcarTrecho(range);
            esconderFlutuante();
        });
        $('flut-limpar').addEventListener('click', function () {
            const range = selecaoValida();
            if (range) limparTrecho(range);
            esconderFlutuante();
        });

        // Com o marca-texto ligado, tocar numa marca tira ela.
        $('sim-questao').addEventListener('click', function (e) {
            const marca = e.target.closest ? e.target.closest('mark.sim-marca-texto') : null;
            if (marca && marcaLigada && window.getSelection().isCollapsed) {
                desmarcar(marca);
                salvarMarcasDaQuestao();
                e.stopPropagation();
            }
        }, true);
    }

    /* ---------- Cliques na questão ---------- */

    function ligarQuestao() {
        $('sim-alternativas').addEventListener('click', function (e) {
            const li = e.target.closest('.sim-alt');
            if (!li) return;
            const q = questaoAtual();

            if (e.target.closest('.sim-alt-riscar')) {
                const letra = li.dataset.letra;
                const lista = estado.riscadas[q.id] || [];
                const i = lista.indexOf(letra);
                if (i >= 0) lista.splice(i, 1); else lista.push(letra);
                estado.riscadas[q.id] = lista;
                li.classList.toggle('riscada');
                li.querySelector('.sim-alt-riscar').textContent = li.classList.contains('riscada') ? 'desfazer' : 'riscar';
                guardarAndamento();
                return;
            }

            // Se a pessoa estava selecionando texto (para o marca-texto), não escolhe a alternativa.
            const sel = window.getSelection();
            if (sel && !sel.isCollapsed) return;
            if (marcaLigada && e.target.closest('.sim-alt-texto')) return;
            escolher(li.dataset.letra);
        });

        $('sim-confirmar').addEventListener('click', confirmarResposta);
        $('ferr-duvida').addEventListener('click', alternarDuvida);
        $('sim-anterior').addEventListener('click', function () { irPara(estado.indice - 1); });
        $('sim-proxima').addEventListener('click', function () {
            if (!estado.revisao && estado.indice === estado.questoes.length - 1) pedirFinalizar();
            else irPara(estado.indice + 1);
        });
        $('sim-finalizar-topo').addEventListener('click', function () {
            if (estado.revisao) voltarAoResultado(); else pedirFinalizar();
        });
        $('sim-mapa').addEventListener('click', function (e) {
            const b = e.target.closest('button');
            if (b) irPara(Number(b.dataset.i));
        });
        $('sim-abrir-mapa').addEventListener('click', function () {
            if ($('sim-mapa-caixa').classList.contains('aberto')) fecharMapaCelular(); else abrirMapaCelular();
        });

        // botão de fechar o mapa no celular
        const fechar = document.createElement('button');
        fechar.type = 'button';
        fechar.className = 'sim-mapa-fechar';
        fechar.textContent = 'Fechar';
        fechar.addEventListener('click', fecharMapaCelular);
        $('sim-mapa-caixa').appendChild(fechar);

        document.addEventListener('keydown', function (e) {
            if (telaAtual !== 'prova' || !estado) return;
            const alvo = e.target;
            if (alvo && (alvo.tagName === 'INPUT' || alvo.tagName === 'TEXTAREA' || alvo.tagName === 'SELECT')) return;
            if ($('sim-dialogo').open) return;
            if (e.ctrlKey || e.metaKey || e.altKey) return;
            const tecla = e.key.toLowerCase();
            if (tecla === 'arrowright') { if (estado.indice < estado.questoes.length - 1) irPara(estado.indice + 1); }
            else if (tecla === 'arrowleft') { irPara(estado.indice - 1); }
            else if (/^[a-e]$/.test(tecla)) { escolher(tecla.toUpperCase()); }
            else if (tecla === 'd') { alternarDuvida(); }
            else if (tecla === 'enter' && estado.opcoes.resposta === 'imediata' && !$('sim-confirmar').disabled && !$('sim-confirmar-linha').classList.contains('hidden')) { confirmarResposta(); }
        });
    }

    /* ---------- Finalizar ---------- */

    async function pedirFinalizar() {
        const total = estado.questoes.length;
        const respondidas = estado.questoes.filter(function (q) { return estado.respostas[q.id]; }).length;
        const duvidas = estado.questoes.filter(function (q) { return estado.duvidas[q.id]; }).length;
        let texto = 'Você respondeu ' + respondidas + ' de ' + total + ' questões.';
        if (respondidas < total) texto += ' ' + (total - respondidas) + ' ficaram em branco.';
        if (duvidas > 0) texto += ' ' + duvidas + ' estão marcadas como dúvida.';
        const sim = await confirmar('Finalizar o simulado?', texto, 'Finalizar', 'Continuar');
        if (sim) finalizar(false);
    }

    async function finalizar(automatico) {
        if (!estado || estado.finalizado) return;
        clearInterval(relogio);
        clearTimeout(temporizadorAvanco);

        const respostas = {};
        estado.questoes.forEach(function (q) { if (estado.respostas[q.id]) respostas[q.id] = estado.respostas[q.id]; });

        const r = await pedir({ acao: 'finalizar', respostas: respostas, duracao: estado.segundos });
        if (!r.dados || !r.dados.ok) {
            const msg = r.dados && r.dados.mensagem ? r.dados.mensagem
                : (r.dados && r.dados.erro === 'sem_tentativa' ? 'A sessão expirou e o simulado não pôde ser corrigido. Comece outro.' : 'Não consegui corrigir agora. Verifique a internet e tente de novo.');
            aviso('Não deu para corrigir', msg, 'ajuda');
            if (r.dados && (r.dados.erro === 'sem_tentativa' || r.dados.erro === 'ja_finalizado')) {
                apagar(CHAVE_ANDAMENTO, sessionStorage);
                estado = null;
                mostrarTela('config');
            } else if (!automatico) {
                relogio = setInterval(tique, 1000);
                ultimoTick = Date.now();
            } else {
                relogio = setInterval(tique, 1000);
            }
            return;
        }

        estado.finalizado = true;
        estado.resultado = r.dados;
        apagar(CHAVE_ANDAMENTO, sessionStorage);
        desenharResultado();
        mostrarTela('resultado');
        if (window.Studay && window.Studay.aplicarRespostaXp) {
            setTimeout(function () { window.Studay.aplicarRespostaXp(r.dados); }, 500);
        }
    }

    /* ------------------------------------------------------------------ */
    /* 3. Resultado e revisão                                              */
    /* ------------------------------------------------------------------ */

    function desenharResultado() {
        const r = estado.resultado;
        const pct = r.total > 0 ? Math.round(r.acertos / r.total * 100) : 0;
        $('res-acertos').textContent = r.acertos;
        $('res-total').textContent = 'de ' + r.total + ' (' + pct + '%)';

        let frase;
        if (r.respondidas === 0) frase = 'Você não respondeu nenhuma questão.';
        else if (pct >= 80) frase = 'Muito bom! Você foi bem em quase tudo.';
        else if (pct >= 60) frase = 'Bom resultado. Revisando os erros você sobe ainda mais.';
        else if (pct >= 40) frase = 'Está no caminho. Vale rever as que você errou.';
        else frase = 'Foi um começo. Rever cada erro é o que mais ajuda a evoluir.';
        $('res-frase').textContent = frase;

        const duvidas = estado.questoes.filter(function (q) { return estado.duvidas[q.id]; }).length;
        const partes = ['Respondidas: ' + r.respondidas + ' de ' + r.total, 'Tempo: ' + formatarDuracaoLonga(estado.segundos)];
        if (duvidas > 0) partes.push('Com dúvida: ' + duvidas);
        $('res-detalhes').textContent = partes.join(' · ');

        const caixaXp = $('res-xp');
        if (r.ganhou > 0) {
            caixaXp.textContent = '+' + r.ganhou + ' XP pelo simulado';
            caixaXp.classList.remove('hidden');
        } else if (r.logado && r.mensagem) {
            caixaXp.textContent = r.mensagem;
            caixaXp.classList.remove('hidden');
        } else {
            caixaXp.classList.add('hidden');
        }
        $('res-aviso-login').classList.toggle('hidden', !!r.logado);

        // por área
        const areas = $('res-areas');
        areas.innerHTML = '';
        ORDEM_AREAS.forEach(function (codigo) {
            const a = r.por_area[codigo];
            if (!a) return;
            const p = a.total > 0 ? Math.round(a.acertos / a.total * 100) : 0;
            const linha = document.createElement('div');
            linha.className = 'sim-area';
            linha.innerHTML = '<span class="sim-area-nome"></span><span class="sim-area-barra"><i></i></span><span class="sim-area-num"></span>';
            linha.querySelector('.sim-area-nome').textContent = NOMES_AREA[codigo].curto;
            linha.querySelector('i').style.width = p + '%';
            linha.querySelector('.sim-area-num').textContent = a.acertos + '/' + a.total;
            areas.appendChild(linha);
        });

        // lista de questões (clicável para rever)
        const lista = $('res-lista');
        lista.innerHTML = '';
        estado.questoes.forEach(function (q, i) {
            const b = document.createElement('button');
            b.type = 'button';
            b.textContent = i + 1;
            b.dataset.i = i;
            const resp = estado.respostas[q.id];
            const certa = r.gabarito[q.id];
            const classes = [!resp ? 'm-branca' : (resp === certa ? 'm-certa' : 'm-errada')];
            if (estado.duvidas[q.id]) classes.push('m-duvida');
            b.className = classes.join(' ');
            b.setAttribute('aria-label', 'Rever a questão ' + (i + 1));
            lista.appendChild(b);
        });

        const erros = estado.questoes.filter(function (q) { return estado.respostas[q.id] && estado.respostas[q.id] !== r.gabarito[q.id]; }).length;
        $('res-refazer-erros').classList.toggle('hidden', !(r.logado && erros > 0));
    }

    function abrirRevisao(i) {
        estado.revisao = true;
        $('sim-relogio-restante').classList.add('hidden');
        $('sim-relogio-corrido').classList.add('hidden');
        mostrarTela('prova');
        desenharMapa();
        irPara(i);
    }

    function voltarAoResultado() {
        estado.revisao = false;
        mostrarTela('resultado');
    }

    function ligarResultado() {
        $('res-lista').addEventListener('click', function (e) {
            const b = e.target.closest('button');
            if (b) abrirRevisao(Number(b.dataset.i));
        });
        $('res-novo').addEventListener('click', function () {
            // recarrega a página para atualizar também os números de dúvidas e erros
            window.location.href = 'simulado.php';
        });
        $('res-refazer-erros').addEventListener('click', function () {
            irParaConfig();
            prefs.origem = 'erros';
            prefs.quantidade = 180;
            prefs.embaralhar = true;
            desenharOpcoes();
            comecar();
        });
        $('res-imprimir').addEventListener('click', function () {
            abrirPrevia(estado.questoes, estado.resultado ? estado.resultado.gabarito : null, 'resultado');
        });
    }

    function irParaConfig() {
        // dúvidas novas podem ter sido marcadas: recarrega os números ao voltar
        mostrarTela('config');
        desenharOpcoes();
        verificarAndamento();
    }

    /* ------------------------------------------------------------------ */
    /* 4. Impressão                                                        */
    /* ------------------------------------------------------------------ */

    let previaQuestoes = [];
    let previaGabarito = null;
    let previaVoltarPara = 'config';

    async function imprimirDaConfiguracao() {
        limparErro();
        const botao = $('sim-imprimir');
        botao.disabled = true;
        botao.textContent = 'Preparando…';
        const r = await pedir({ acao: 'montar', opcoes: prefs, impressao: true });
        botao.disabled = false;
        botao.textContent = 'Imprimir a prova';
        if (!r.dados || !r.dados.ok) {
            mostrarErro(r.dados && r.dados.mensagem ? r.dados.mensagem : 'Não consegui preparar a prova agora.');
            return;
        }
        salvarPreferencias();
        abrirPrevia(r.dados.questoes, null, 'config');
    }

    function abrirPrevia(questoes, gabarito, voltarPara) {
        previaQuestoes = questoes;
        previaGabarito = gabarito;
        previaVoltarPara = voltarPara;
        $('prev-gabarito').checked = false;
        $('prev-cartao').checked = true;
        mostrarTela('previa');
        desenharPapel();
    }

    async function desenharPapel() {
        const papel = $('sim-papel');
        const comCartao = $('prev-cartao').checked;
        const comGabarito = $('prev-gabarito').checked;

        if (comGabarito && !previaGabarito) {
            const r = await pedir({ acao: 'gabarito', ids: previaQuestoes.map(function (q) { return q.id; }) });
            if (r.dados && r.dados.ok) previaGabarito = r.dados.gabarito;
        }

        const anos = Array.from(new Set(previaQuestoes.map(function (q) { return q.ano; }))).sort();
        const hoje = new Date().toLocaleDateString('pt-BR');
        let html = '';
        html += '<header class="papel-cabeca"><div><h1>Simulado do ENEM</h1><p>' + previaQuestoes.length + ' questões · provas de ' + anos.join(', ') + '</p></div>'
              + '<div class="papel-logo">Studay</div></header>';
        html += '<div class="papel-campos"><span>Nome: ______________________________________</span><span>Data: ' + hoje + '</span></div>';
        html += '<p class="papel-instrucao">Leia com atenção. Cada questão tem uma única alternativa correta. Marque no cartão-resposta ou na própria prova.</p>';

        previaQuestoes.forEach(function (q, i) {
            html += '<section class="papel-questao"><div class="papel-q-cab"><b>QUESTÃO ' + String(i + 1).padStart(2, '0') + '</b>'
                  + '<span>ENEM ' + q.ano + ' · ' + q.area_nome + ' · questão ' + q.numero + ' da prova' + (q.idioma ? ' · ' + nomeIdioma(q.idioma) : '') + '</span></div>';
            if (q.enunciado) html += '<div class="papel-texto">' + ajustarImagens(q.enunciado) + '</div>';
            if (q.introducao) html += '<div class="papel-texto">' + ajustarImagens(q.introducao) + '</div>';
            html += '<ol class="papel-alts">';
            q.alternativas.forEach(function (a) {
                html += '<li><b>' + a.l + '</b><div>' + ajustarImagens(a.t || '') + (a.img ? '<img src="../' + a.img + '" alt="">' : '') + '</div></li>';
            });
            html += '</ol></section>';
        });

        if (comCartao) {
            html += '<section class="papel-cartao"><h2>Cartão-resposta</h2><p>Preencha completamente a bolinha da alternativa escolhida.</p><div class="papel-cartao-grade">';
            previaQuestoes.forEach(function (q, i) {
                html += '<div><b>' + String(i + 1).padStart(2, '0') + '</b>' + ['A', 'B', 'C', 'D', 'E'].map(function (l) { return '<span class="bolinha">' + l + '</span>'; }).join('') + '</div>';
            });
            html += '</div></section>';
        }

        if (comGabarito && previaGabarito) {
            html += '<section class="papel-gabarito"><h2>Gabarito</h2><div class="papel-gabarito-grade">';
            previaQuestoes.forEach(function (q, i) {
                html += '<span><b>' + String(i + 1).padStart(2, '0') + '</b> ' + (previaGabarito[q.id] || '-') + '</span>';
            });
            html += '</div></section>';
        }

        html += '<footer class="papel-rodape">Questões: provas oficiais do INEP (ENEM). Gerado no Studay.</footer>';
        papel.innerHTML = html;
    }

    function ligarPrevia() {
        $('prev-cartao').addEventListener('change', desenharPapel);
        $('prev-gabarito').addEventListener('change', desenharPapel);
        $('prev-voltar').addEventListener('click', function () {
            if (previaVoltarPara === 'resultado') mostrarTela('resultado');
            else { mostrarTela('config'); }
        });
        $('prev-imprimir').addEventListener('click', function () { window.print(); });
    }

    /* ------------------------------------------------------------------ */
    /* Simulado em andamento (sobrevive a recarregar a página)             */
    /* ------------------------------------------------------------------ */

    function verificarAndamento() {
        const salvo = ler(CHAVE_ANDAMENTO, sessionStorage);
        const caixa = $('sim-retomar');
        if (!salvo || !salvo.questoes || salvo.finalizado) { caixa.classList.add('hidden'); return; }
        const feitas = salvo.questoes.filter(function (q) { return salvo.respostas[q.id]; }).length;
        $('sim-retomar-info').textContent = ' ' + feitas + ' de ' + salvo.questoes.length + ' respondidas, ' + formatarDuracaoLonga(salvo.segundos || 0) + ' de prova.';
        caixa.classList.remove('hidden');
    }

    function ligarAndamento() {
        $('sim-continuar').addEventListener('click', function () {
            const salvo = ler(CHAVE_ANDAMENTO, sessionStorage);
            if (!salvo) { verificarAndamento(); return; }
            estado = salvo;
            estado.revisao = false;
            iniciarProva();
        });
        $('sim-descartar').addEventListener('click', async function () {
            const sim = await confirmar('Descartar o simulado?', 'Você vai perder as respostas desse simulado.', 'Descartar', 'Voltar');
            if (sim) { apagar(CHAVE_ANDAMENTO, sessionStorage); $('sim-retomar').classList.add('hidden'); }
        });

        window.addEventListener('beforeunload', function (e) {
            if (estado && !estado.finalizado && telaAtual === 'prova' && !estado.revisao) {
                guardarAndamento();
                e.preventDefault();
                e.returnValue = '';
            }
        });
        document.addEventListener('visibilitychange', function () {
            if (document.hidden) guardarAndamento();
        });
    }

    /* ------------------------------------------------------------------ */
    /* Início                                                              */
    /* ------------------------------------------------------------------ */

    ligarOpcoes();
    ligarQuestao();
    ligarMarcaTexto();
    ligarResultado();
    ligarPrevia();
    ligarAndamento();
    desenharOpcoes();
    verificarAndamento();
})();
