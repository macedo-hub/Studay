/*
 * PERFIL — troca de abas das categorias de foto e prévia da foto escolhida.
 * Sem JavaScript a página continua funcionando: todas as categorias aparecem juntas.
 */
(function () {
    'use strict';

    const abas = document.querySelectorAll('.js-aba-avatar');
    const grades = document.querySelectorAll('.js-grade-avatar');
    const previa = document.querySelector('.js-avatar-previa');

    function mostrarCategoria(categoria) {
        abas.forEach(function (aba) {
            const ativa = aba.dataset.categoria === categoria;
            aba.classList.toggle('ativa', ativa);
            aba.setAttribute('aria-selected', ativa ? 'true' : 'false');
        });
        grades.forEach(function (grade) {
            grade.classList.toggle('escondida', grade.dataset.categoria !== categoria);
        });
    }

    abas.forEach(function (aba) {
        aba.addEventListener('click', function () { mostrarCategoria(aba.dataset.categoria); });
    });

    // Abre já na categoria da foto que a pessoa usa hoje.
    const marcada = document.querySelector('.js-grade-avatar input:checked');
    if (marcada) {
        const grade = marcada.closest('.js-grade-avatar');
        if (grade) mostrarCategoria(grade.dataset.categoria);
    }

    // Mostra no topo a foto escolhida, antes mesmo de salvar.
    document.querySelectorAll('.js-grade-avatar input').forEach(function (campo) {
        campo.addEventListener('change', function () {
            const imagem = campo.closest('.avatar-opcao').querySelector('img');
            if (previa && imagem) previa.src = imagem.src;
        });
    });
})();
