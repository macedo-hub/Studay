/*
 * SITES DA TURMA — filtro por matéria e busca (tudo no navegador, sem recarregar).
 */
(function () {
    'use strict';

    const cartoes = Array.from(document.querySelectorAll('.js-site-card'));
    const botoes = Array.from(document.querySelectorAll('.js-filtro-materia'));
    const busca = document.querySelector('.js-busca-sites');
    const aviso = document.querySelector('.js-sites-nada');

    // Se a imagem de capa não existir, some e fica a capa padrão (com as iniciais do site).
    document.querySelectorAll('.js-capa-site').forEach(function (img) {
        img.addEventListener('error', function () { img.remove(); });
    });

    if (cartoes.length === 0) return;

    let materiaAtual = '';
    let textoAtual = '';

    function aplicarFiltros() {
        let visiveis = 0;

        cartoes.forEach(function (cartao) {
            const combinaMateria = materiaAtual === '' || cartao.dataset.materia === materiaAtual;
            const combinaTexto = textoAtual === '' || cartao.dataset.busca.indexOf(textoAtual) !== -1;
            const mostrar = combinaMateria && combinaTexto;

            cartao.hidden = !mostrar;
            if (mostrar) visiveis++;
        });

        if (aviso) aviso.hidden = visiveis !== 0;
    }

    botoes.forEach(function (botao) {
        botao.addEventListener('click', function () {
            materiaAtual = botao.dataset.materia;
            botoes.forEach(function (outro) { outro.classList.toggle('ativo', outro === botao); });
            aplicarFiltros();
        });
    });

    if (busca) {
        busca.addEventListener('input', function () {
            textoAtual = busca.value.trim().toLowerCase();
            aplicarFiltros();
        });
    }
})();
