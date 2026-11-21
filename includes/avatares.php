<?php
/*
 * AVATARES (fotos de perfil prontas)
 * Ninguém envia foto: a pessoa escolhe uma da lista. Assim o servidor grátis não fica pesado.
 * Cada imagem fica em  img/avatares/<categoria>/<código>.webp
 * (o "Faísca clássico" usa a imagem que já existe em img/logo/).
 */

const AVATAR_PADRAO = 'faisca-classico';

const CATEGORIAS_AVATARES = [
    'faisca' => [
        'nome'  => 'Faíscas',
        'itens' => [
            'faisca-classico'       => 'Faísca clássico',
            'faisca-oculos-escuros' => 'Faísca estiloso',
            'faisca-heroi'          => 'Faísca herói',
            'faisca-sono'           => 'Faísca com sono',
            'faisca-fones'          => 'Faísca de fones',
            'faisca-formando'       => 'Faísca formando',
            'faisca-cafe'           => 'Faísca com café',
        ],
    ],
    'memes' => [
        'nome'  => 'Memes',
        'itens' => [
            'faisca-nerd'       => 'Faísca nerd',
            'faisca-surpreso'   => 'Faísca surpreso',
            'faisca-sobrancelha'=> 'Faísca desconfiado',
            'faisca-pensando'   => 'Faísca pensativo',
            'faisca-riso'       => 'Faísca morrendo de rir',
            'faisca-implorando' => 'Faísca pidão',
        ],
    ],
    'animais' => [
        'nome'  => 'Animais',
        'itens' => [
            'gato'     => 'Gatinho',
            'cachorro' => 'Cachorrinho',
            'capivara' => 'Capivara',
            'coruja'   => 'Coruja',
            'panda'    => 'Panda',
            'raposa'   => 'Raposa',
        ],
    ],
];

/** Lista simples: código => ['nome' => ..., 'categoria' => ...] */
function todos_os_avatares(): array
{
    $todos = [];
    foreach (CATEGORIAS_AVATARES as $categoria => $dados) {
        foreach ($dados['itens'] as $codigo => $nome) {
            $todos[$codigo] = ['nome' => $nome, 'categoria' => $categoria];
        }
    }
    return $todos;
}

function avatar_codigo_valido($codigo): bool
{
    return is_string($codigo) && isset(todos_os_avatares()[$codigo]);
}

/** Caminho da imagem a partir da raiz do site (ex.: img/avatares/memes/faisca-nerd.webp). Se o arquivo ainda não existe, usa o Faísca clássico. */
function avatar_arquivo($codigo): string
{
    $padrao = 'img/logo/faisca-sem-fundo.webp';

    if (!avatar_codigo_valido($codigo) || $codigo === AVATAR_PADRAO) {
        return $padrao;
    }

    $categoria = todos_os_avatares()[$codigo]['categoria'];
    $caminho = 'img/avatares/' . $categoria . '/' . $codigo . '.webp';

    return is_file(__DIR__ . '/../' . $caminho) ? $caminho : $padrao;
}

/** Dados prontos para mandar ao navegador. */
function avatar_info($codigo): array
{
    $codigo = avatar_codigo_valido($codigo) ? $codigo : AVATAR_PADRAO;
    return [
        'codigo'  => $codigo,
        'nome'    => todos_os_avatares()[$codigo]['nome'],
        'arquivo' => avatar_arquivo($codigo),
    ];
}

/** Descobre o avatar de várias pessoas de uma vez (id => código). Se a coluna ainda não existe no banco, todo mundo fica com o padrão. */
function avatares_de_usuarios(PDO $banco, array $ids): array
{
    $ids = array_values(array_unique(array_map('intval', $ids)));
    $mapa = [];
    foreach ($ids as $id) {
        $mapa[$id] = AVATAR_PADRAO;
    }
    if (count($ids) === 0) {
        return $mapa;
    }

    try {
        $marcas = implode(',', array_fill(0, count($ids), '?'));
        $consulta = $banco->prepare("SELECT id, avatar FROM usuarios WHERE id IN ($marcas)");
        $consulta->execute($ids);
        foreach ($consulta->fetchAll() as $linha) {
            $mapa[(int) $linha['id']] = $linha['avatar'];
        }
    } catch (Throwable $erro) {
        error_log('Studay avatares: ' . $erro->getMessage());
    }

    return $mapa;
}
