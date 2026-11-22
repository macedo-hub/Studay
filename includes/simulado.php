<?php
/*
 * SIMULADO DO ENEM
 * Funções que carregam as questões (pasta dados/enem), sorteiam o simulado
 * e corrigem as respostas. O gabarito só sai do servidor quando a pessoa pede.
 *
 * As questões são das provas oficiais do INEP (ENEM 2021, 2022 e 2023).
 * Os textos foram importados com ferramentas/importar_questoes.py.
 */

require_once __DIR__ . '/funcoes.php';

const ANOS_SIMULADO = [2021, 2022, 2023];

// Ordem das áreas na prova de verdade.
const AREAS_SIMULADO = [
    'linguagens'        => ['nome' => 'Linguagens, Códigos e suas Tecnologias', 'curto' => 'Linguagens',  'dia' => 1],
    'ciencias-humanas'  => ['nome' => 'Ciências Humanas e suas Tecnologias',    'curto' => 'Humanas',     'dia' => 1],
    'ciencias-natureza' => ['nome' => 'Ciências da Natureza e suas Tecnologias', 'curto' => 'Natureza',    'dia' => 2],
    'matematica'        => ['nome' => 'Matemática e suas Tecnologias',          'curto' => 'Matemática',  'dia' => 2],
];

const QUANTIDADES_SIMULADO = [5, 10, 25, 45, 90, 180];

const PREFERENCIAS_PADRAO = [
    'quantidade'      => 10,
    'anos'            => [2021, 2022, 2023],
    'areas'           => ['linguagens', 'ciencias-humanas', 'ciencias-natureza', 'matematica'],
    'modo'            => 'misturado',     // misturado = anos misturados | mesmo_ano = tudo do mesmo ano
    'origem'          => 'todas',         // todas | duvidas | erros
    'idioma'          => 'ingles',        // vale para as questões 1 a 5 de Linguagens
    'embaralhar'      => true,
    'resposta'        => 'final',         // imediata = mostra o gabarito na hora | final = só no fim
    'tempo'           => 'nenhum',        // nenhum | por_questao | total
    'minutos_questao' => 3,
    'minutos_total'   => 30,
    'cronometro'      => true,            // mostra o cronômetro na tela
];

/** Lê as questões de um ano (já em HTML seguro). */
function questoes_do_ano(int $ano): array
{
    static $cache = [];
    if (!in_array($ano, ANOS_SIMULADO, true)) {
        return [];
    }
    if (!isset($cache[$ano])) {
        $arquivo = __DIR__ . '/../dados/enem/' . $ano . '.json';
        $dados = is_file($arquivo) ? json_decode((string) file_get_contents($arquivo), true) : null;
        $cache[$ano] = [];
        if (is_array($dados)) {
            foreach ($dados as $q) {
                $cache[$ano][$q['id']] = $q;
            }
        }
    }
    return $cache[$ano];
}

/** Todas as questões, na forma  id => questão. */
function todas_as_questoes(): array
{
    $todas = [];
    foreach (ANOS_SIMULADO as $ano) {
        $todas += questoes_do_ano($ano);
    }
    return $todas;
}

/** Quantas questões existem por ano e por área (para mostrar na tela). */
function contagem_de_questoes(): array
{
    $conta = [];
    foreach (todas_as_questoes() as $q) {
        // as questões em inglês e espanhol contam como uma só
        if ($q['idioma'] === 'espanhol') continue;
        $conta[$q['ano']][$q['area']] = ($conta[$q['ano']][$q['area']] ?? 0) + 1;
    }
    return $conta;
}

/** A questão sem o gabarito, pronta para o navegador. */
function questao_publica(array $q): array
{
    return [
        'id'           => $q['id'],
        'ano'          => (int) $q['ano'],
        'numero'       => (int) $q['numero'],
        'area'         => $q['area'],
        'area_nome'    => AREAS_SIMULADO[$q['area']]['curto'] ?? '',
        'dia'          => (int) $q['dia'],
        'idioma'       => $q['idioma'],
        'enunciado'    => $q['enunciado'],
        'introducao'   => $q['introducao'],
        'alternativas' => $q['alternativas'],
    ];
}

/** Confere as opções que vieram do navegador e devolve só o que é permitido. */
function sanear_preferencias($entrada): array
{
    $p = PREFERENCIAS_PADRAO;
    if (!is_array($entrada)) {
        return $p;
    }

    $qtd = filter_var($entrada['quantidade'] ?? null, FILTER_VALIDATE_INT);
    if ($qtd !== false && $qtd >= 1 && $qtd <= 180) $p['quantidade'] = $qtd;

    if (isset($entrada['anos']) && is_array($entrada['anos'])) {
        $anos = array_values(array_filter(array_map('intval', $entrada['anos']), fn($a) => in_array($a, ANOS_SIMULADO, true)));
        if ($anos) $p['anos'] = array_values(array_unique($anos));
    }
    if (isset($entrada['areas']) && is_array($entrada['areas'])) {
        $areas = array_values(array_filter($entrada['areas'], fn($a) => is_string($a) && isset(AREAS_SIMULADO[$a])));
        if ($areas) $p['areas'] = array_values(array_unique($areas));
    }

    $escolha = function ($valor, array $permitidos, $padrao) {
        return (is_string($valor) && in_array($valor, $permitidos, true)) ? $valor : $padrao;
    };
    $p['modo']     = $escolha($entrada['modo'] ?? null, ['misturado', 'mesmo_ano'], $p['modo']);
    $p['origem']   = $escolha($entrada['origem'] ?? null, ['todas', 'duvidas', 'erros'], $p['origem']);
    $p['idioma']   = $escolha($entrada['idioma'] ?? null, ['ingles', 'espanhol'], $p['idioma']);
    $p['resposta'] = $escolha($entrada['resposta'] ?? null, ['imediata', 'final'], $p['resposta']);
    $p['tempo']    = $escolha($entrada['tempo'] ?? null, ['nenhum', 'por_questao', 'total'], $p['tempo']);

    $p['embaralhar'] = isset($entrada['embaralhar']) ? (bool) $entrada['embaralhar'] : $p['embaralhar'];
    $p['cronometro'] = isset($entrada['cronometro']) ? (bool) $entrada['cronometro'] : $p['cronometro'];

    $mq = filter_var($entrada['minutos_questao'] ?? null, FILTER_VALIDATE_INT);
    if ($mq !== false && $mq >= 1 && $mq <= 15) $p['minutos_questao'] = $mq;
    $mt = filter_var($entrada['minutos_total'] ?? null, FILTER_VALIDATE_INT);
    if ($mt !== false && $mt >= 5 && $mt <= 330) $p['minutos_total'] = $mt;

    return $p;
}

/**
 * Sorteia as questões do simulado.
 * $salvas = ids de dúvidas/erros da pessoa (só usado quando origem != "todas").
 * Devolve a lista de ids na ordem em que vão aparecer.
 */
function sortear_simulado(array $p, array $salvas = []): array
{
    $pool = [];
    foreach (todas_as_questoes() as $id => $q) {
        if (!in_array($q['ano'], $p['anos'], true)) continue;
        if (!in_array($q['area'], $p['areas'], true)) continue;
        if ($q['idioma'] !== null && $q['idioma'] !== $p['idioma']) continue;
        if ($p['origem'] !== 'todas' && !in_array($id, $salvas, true)) continue;
        $pool[$id] = $q;
    }
    if (!$pool) {
        return [];
    }

    $quantidade = $p['quantidade'];

    // "Tudo do mesmo ano": escolhe um ano que tenha questões suficientes.
    if ($p['modo'] === 'mesmo_ano' && $p['origem'] === 'todas') {
        $porAno = [];
        foreach ($pool as $id => $q) $porAno[$q['ano']][$id] = $q;
        $bons = array_keys(array_filter($porAno, fn($lista) => count($lista) >= $quantidade));
        $ano = $bons ? $bons[array_rand($bons)] : array_search(max(array_map('count', $porAno)), array_map('count', $porAno));
        $pool = $porAno[$ano];
    }

    $quantidade = min($quantidade, count($pool));

    // Divide por área e vai pegando uma de cada vez (para ficar equilibrado).
    $porArea = [];
    foreach (array_keys(AREAS_SIMULADO) as $area) $porArea[$area] = [];
    foreach ($pool as $id => $q) $porArea[$q['area']][] = $id;
    foreach ($porArea as &$lista) shuffle($lista);
    unset($lista);

    $escolhidas = [];
    while (count($escolhidas) < $quantidade) {
        $pegou = false;
        foreach ($porArea as $area => &$lista) {
            if (count($escolhidas) >= $quantidade) break;
            if ($lista) {
                $escolhidas[] = array_pop($lista);
                $pegou = true;
            }
        }
        unset($lista);
        if (!$pegou) break;
    }

    if ($p['embaralhar']) {
        shuffle($escolhidas);
    } else {
        // Na ordem da prova: por área, ano e número.
        $ordemArea = array_flip(array_keys(AREAS_SIMULADO));
        usort($escolhidas, function ($a, $b) use ($pool, $ordemArea) {
            $qa = $pool[$a]; $qb = $pool[$b];
            return [$ordemArea[$qa['area']], $qa['ano'], $qa['numero']] <=> [$ordemArea[$qb['area']], $qb['ano'], $qb['numero']];
        });
    }
    return $escolhidas;
}

/** XP de um simulado: 2 por acerto, mais 10 se acertar 70% ou mais (a partir de 10 questões). */
function xp_do_simulado(int $acertos, int $total): int
{
    $xp = $acertos * 2;
    if ($total >= 10 && $total > 0 && ($acertos / $total) >= 0.7) {
        $xp += 10;
    }
    return $xp;
}

/* ---------------- Banco de dados (tudo opcional: se as tabelas não existirem, o simulado continua) ---------------- */

/** Ids das questões que a pessoa salvou. $tipo = 'duvida' ou 'erro'. */
function questoes_salvas(PDO $banco, int $usuarioId, string $tipo): array
{
    try {
        $c = $banco->prepare('SELECT questao_id FROM questoes_salvas WHERE usuario_id = ? AND tipo = ?');
        $c->execute([$usuarioId, $tipo]);
        return array_column($c->fetchAll(), 'questao_id');
    } catch (Throwable $e) {
        return [];
    }
}

function preferencias_do_usuario(PDO $banco, int $usuarioId): ?array
{
    try {
        $c = $banco->prepare('SELECT dados FROM preferencias_simulado WHERE usuario_id = ?');
        $c->execute([$usuarioId]);
        $texto = $c->fetchColumn();
        $json = is_string($texto) ? json_decode($texto, true) : null;
        return is_array($json) ? sanear_preferencias($json) : null;
    } catch (Throwable $e) {
        return null;
    }
}

function guardar_preferencias(PDO $banco, int $usuarioId, array $p): bool
{
    try {
        $texto = json_encode($p, JSON_UNESCAPED_UNICODE);
        $existe = $banco->prepare('SELECT COUNT(*) FROM preferencias_simulado WHERE usuario_id = ?');
        $existe->execute([$usuarioId]);
        if ((int) $existe->fetchColumn() > 0) {
            $banco->prepare('UPDATE preferencias_simulado SET dados = ?, atualizado_em = ? WHERE usuario_id = ?')
                  ->execute([$texto, date('Y-m-d H:i:s'), $usuarioId]);
        } else {
            $banco->prepare('INSERT INTO preferencias_simulado (usuario_id, dados, atualizado_em) VALUES (?, ?, ?)')
                  ->execute([$usuarioId, $texto, date('Y-m-d H:i:s')]);
        }
        return true;
    } catch (Throwable $e) {
        error_log('Studay simulado (preferências): ' . $e->getMessage());
        return false;
    }
}

/** Últimos simulados da pessoa (para a tela inicial). */
function historico_simulados(PDO $banco, int $usuarioId, int $limite = 5): array
{
    try {
        $limite = max(1, min(20, $limite));
        $c = $banco->prepare("SELECT total, respondidas, acertos, duracao_seg, criado_em FROM resultados_simulado WHERE usuario_id = ? ORDER BY id DESC LIMIT $limite");
        $c->execute([$usuarioId]);
        return $c->fetchAll();
    } catch (Throwable $e) {
        return [];
    }
}
