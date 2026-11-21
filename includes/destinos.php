<?php
/*
 * Páginas para onde a pessoa pode voltar depois do login.
 * Só aceita nomes desta lista (assim ninguém consegue mandar o usuário para outro site).
 */

const DESTINOS_PERMITIDOS = [
    'metodos.html', 'sitesTurma.php', 'ranking.php', 'progresso.php', 'adminSites.php', 'modoENEM.php', 'modoFaisca.php',
];

function destino_seguro($valor): string
{
    return (is_string($valor) && in_array($valor, DESTINOS_PERMITIDOS, true)) ? $valor : 'metodos.html';
}
