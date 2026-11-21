<?php
/*
 * Coisas do portal "Sites da Turma".
 */

// Matérias que podem aparecer nos filtros (lista fechada).
const DISCIPLINAS_SITES = [
    'Matemática', 'Português', 'Redação', 'História', 'Geografia', 'Física',
    'Química', 'Biologia', 'Inglês', 'Filosofia', 'Sociologia', 'Artes',
    'Interdisciplinar', 'Outra',
];

/** Nome do arquivo da capa só pode ter letras, números, ponto, traço e underline. */
function nome_de_imagem_valido(string $nome): bool
{
    return (bool) preg_match('/^[A-Za-z0-9._-]{1,80}\.(webp|png|jpe?g)$/i', $nome);
}

/** Sites ativos para o portal, na ordem escolhida. Inclui quantas vezes cada um foi aberto. */
function buscar_sites_ativos(PDO $banco): array
{
    $consulta = $banco->query(
        'SELECT s.*, (SELECT COUNT(*) FROM cliques_sites c WHERE c.site_id = s.id) AS acessos
         FROM sites_turma s WHERE s.ativo = 1 ORDER BY s.ordem ASC, s.nome_site ASC'
    );
    return $consulta->fetchAll();
}

/** Letras para o desenho da capa quando o site ainda não tem imagem. */
function iniciais_do_site(string $nome): string
{
    $partes = preg_split('/\s+/u', trim($nome)) ?: [];
    $letras = '';
    foreach (array_slice($partes, 0, 2) as $parte) {
        $letras .= function_exists('mb_substr') ? mb_substr($parte, 0, 1, 'UTF-8') : substr($parte, 0, 1);
    }
    return function_exists('mb_strtoupper') ? mb_strtoupper($letras, 'UTF-8') : strtoupper($letras);
}
