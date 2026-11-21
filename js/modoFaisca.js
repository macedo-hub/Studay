(function () {
    'use strict';

    const visual = document.querySelector('.js-faisca-visual');
    const fala = document.querySelector('.js-faisca-fala');
    const img = document.querySelector('.js-faisca-img');

    if (!visual || !fala) return;

    const usaImagens = visual.dataset.imagens === 'true';

    // Falas do Faísca de acordo com o estágio do cronômetro
    const FALAS_ESTAGIO = {
        1: 'Minha chama está a todo vapor! Vamos nessa.',
        2: 'Já estou derretendo um pouquinho... mas ainda dá pra render!',
        3: 'Metade do caminho! Continue firme.',
        4: 'Já estou quase me apagando... faltam poucos minutos!',
        5: 'Última chama! Termine o bloco com tudo.',
        6: 'Missão cumprida! Pode me apagar com orgulho. Descanse um pouco.'
    };

    // Falas de acordo com a disposição escolhida
    const FALAS_DISPOSICAO = {
        animado: 'Que energia boa! Minha chama está lá em cima, vamos aproveitar!',
        normal: 'Tudo certo! Vamos em ritmo firme, um bloco de cada vez.',
        cansado: 'Hoje o pavio está curtinho, então vamos leve e com uma pausa.'
    };

    // Atualiza a fala e a imagem do Faísca
    function aplicarEstagio(estagio) {
        visual.dataset.estagio = String(estagio);
        fala.textContent = FALAS_ESTAGIO[estagio];

        if (usaImagens && img) {
            img.src = '../img/faisca/estagio-' + estagio + '.webp';
        }
    }

    // Altera a fala quando a disposição é escolhida
    document.querySelectorAll('input[name="disposicao"]').forEach(function (radio) {
        radio.addEventListener('change', function () {
            if (FALAS_DISPOSICAO[radio.value]) {
                fala.textContent = FALAS_DISPOSICAO[radio.value];
            }
        });
    });

    const lista = document.querySelector('.js-faisca-sessao');
    if (!lista) return;

    const blocos = Array.from(lista.querySelectorAll('.cronograma-bloco'));
    const tempoEl = document.querySelector('.js-faisca-tempo');
    const blocoAtualEl = document.querySelector('.js-faisca-bloco-atual');
    const btnIniciar = document.querySelector('.js-faisca-iniciar');
    const btnReiniciar = document.querySelector('.js-faisca-reiniciar');
    const chkAcelerar = document.querySelector('.js-faisca-acelerar');

    const totalMin = parseInt(lista.dataset.total, 10);
    if (!Number.isInteger(totalMin) || totalMin <= 0) return;

    const totalSeg = totalMin * 60;
    let restanteSeg = totalSeg;
    let intervalo = null;
    let estagioAtual = 1;
    let usouAcelerar = false;   // se acelerou o tempo, não ganha XP

    // Pega a duração de cada bloco do cronograma
    const duracoes = blocos.map(function (bloco) {
        const minutos = parseInt(bloco.dataset.minutos, 10);
        return Number.isInteger(minutos) ? minutos * 60 : 0;
    });

    // Converte os segundos para o formato 00:00
    function formatar(segundos) {
        const minutos = Math.floor(segundos / 60);
        const segundosRestantes = segundos % 60;

        return String(minutos).padStart(2, '0') + ':' +
            String(segundosRestantes).padStart(2, '0');
    }

    // Define o estágio do Faísca de acordo com o tempo restante
    function calcularEstagio() {
        if (restanteSeg <= 0) return 6;

        const fracao = restanteSeg / totalSeg;

        if (fracao > 0.80) return 1;
        if (fracao > 0.60) return 2;
        if (fracao > 0.40) return 3;
        if (fracao > 0.20) return 4;

        return 5;
    }

    // Atualiza o cronômetro e marca o bloco atual
    function atualizar() {
        const decorrido = totalSeg - restanteSeg;
        tempoEl.textContent = formatar(restanteSeg);

        let acumulado = 0;
        let achouAtivo = false;

        blocos.forEach(function (bloco, i) {
            const inicio = acumulado;
            const fim = acumulado + duracoes[i];

            acumulado = fim;

            bloco.classList.remove('ativo', 'concluido');

            if (decorrido >= fim) {
                bloco.classList.add('concluido');
            } else if (!achouAtivo && decorrido >= inicio && decorrido < fim) {
                bloco.classList.add('ativo');
                achouAtivo = true;
                blocoAtualEl.textContent = 'Agora: ' + bloco.dataset.titulo;
            }
        });

        if (restanteSeg <= 0) {
            blocoAtualEl.textContent = 'Sessão concluída!';
        } else if (!achouAtivo) {
            blocoAtualEl.textContent = 'Pronto para começar';
        }

        const novoEstagio = calcularEstagio();

        if (novoEstagio !== estagioAtual) {
            estagioAtual = novoEstagio;
            aplicarEstagio(novoEstagio);
        }
    }

    // Altera o texto e o ícone do botão
    function textoBotao(texto) {
        btnIniciar.querySelector('span').textContent = texto;

        const rodando = texto === 'Pausar';

        btnIniciar.querySelector('.js-icone-play').hidden = rodando;
        btnIniciar.querySelector('.js-icone-pausa').hidden = !rodando;
    }

    // Para o cronômetro
    function parar() {
        if (intervalo !== null) {
            clearInterval(intervalo);
            intervalo = null;
        }
    }

    // Diminui o tempo a cada segundo
    function tick() {
        const passo = chkAcelerar && chkAcelerar.checked ? 60 : 1;
        if (passo > 1) { usouAcelerar = true; }

        restanteSeg = Math.max(0, restanteSeg - passo);
        atualizar();

        if (restanteSeg <= 0) {
            parar();
            textoBotao('Concluído');
            btnIniciar.disabled = true;

            // Gamificação: sessão completa e sem acelerar rende XP (se estiver logado)
            if (window.Studay && !usouAcelerar) {
                window.Studay.registrarXp('faisca', { minutos: totalMin });
            }
        }
    }

    // Inicia, pausa e continua o cronômetro
    btnIniciar.addEventListener('click', function () {
        if (intervalo !== null) {
            parar();
            textoBotao('Continuar');
            return;
        }

        if (restanteSeg === totalSeg) {
            aplicarEstagio(1);
            usouAcelerar = false;
            // Gamificação: avisa o servidor que a sessão começou
            if (window.Studay) { window.Studay.iniciarCronometro('faisca'); }
        }

        intervalo = setInterval(tick, 1000);
        textoBotao('Pausar');
        atualizar();
    });

    // Reinicia o cronômetro e o Faísca
    btnReiniciar.addEventListener('click', function () {
        parar();

        restanteSeg = totalSeg;
        estagioAtual = 1;
        usouAcelerar = false;

        btnIniciar.disabled = false;
        textoBotao('Iniciar');

        blocos.forEach(function (bloco) {
            bloco.classList.remove('ativo', 'concluido');
        });

        tempoEl.textContent = formatar(totalSeg);
        blocoAtualEl.textContent = 'Pronto para começar';

        aplicarEstagio(1);
    });
})();