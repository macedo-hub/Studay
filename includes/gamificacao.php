<?php
/*
 * GAMIFICAÇÃO DO STUDAY
 * XP, níveis, sequência de dias, conquistas e ranking.
 * Quem calcula o XP é sempre o SERVIDOR (o navegador só avisa "terminei o Pomodoro").
 */

require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/avatares.php';

// Os 6 níveis do Faísca. "xp_minimo" é o XP total para chegar naquele nível.
const NIVEIS_FAISCA = [
    1 => ['nome' => 'Fagulha',    'xp_minimo' => 0],
    2 => ['nome' => 'Chama Viva', 'xp_minimo' => 100],
    3 => ['nome' => 'Tocha',      'xp_minimo' => 250],
    4 => ['nome' => 'Fogueira',   'xp_minimo' => 500],
    5 => ['nome' => 'Labareda',   'xp_minimo' => 900],
    6 => ['nome' => 'Estrela',    'xp_minimo' => 1500],
];

// Regras de cada tipo de ação: quanto XP pode render por dia (limite_dia) e se só conta uma vez (unico).
const REGRAS_XP = [
    'login_diario'    => ['limite_dia' => 5,   'unico' => false],
    'pomodoro'        => ['limite_dia' => 120, 'unico' => false],
    'faisca'          => ['limite_dia' => 100, 'unico' => false],
    'quiz'            => ['limite_dia' => 100, 'unico' => false],
    'simulado'        => ['limite_dia' => 120, 'unico' => false],
    'flashcard_certo' => ['limite_dia' => 20,  'unico' => false],
    'resumo_criado'   => ['limite_dia' => 15,  'unico' => false],
    'visita_site'     => ['limite_dia' => 25,  'unico' => true],   // só ganha na 1ª visita a cada site
];

// O que cada ação é chamada na tela quando ganha XP.
const ROTULOS_XP = [
    'login_diario'    => 'Bônus por entrar hoje',
    'pomodoro'        => 'Foco concluído',
    'faisca'          => 'Sessão do Modo Faísca concluída',
    'quiz'            => 'Quiz do Modo ENEM',
    'simulado'        => 'Simulado do ENEM concluído',
    'flashcard_certo' => 'Flashcard acertado',
    'resumo_criado'   => 'Resumo criado',
    'visita_site'     => 'Site novo explorado',
];

// Nome curto usado nas mensagens ("você já ganhou todo o XP de ... hoje").
const NOMES_CURTOS_XP = [
    'login_diario'    => 'entrar no site',
    'pomodoro'        => 'Pomodoro',
    'faisca'          => 'Modo Faísca',
    'quiz'            => 'quiz',
    'simulado'        => 'simulados',
    'flashcard_certo' => 'flashcards',
    'resumo_criado'   => 'resumos',
    'visita_site'     => 'visitas aos sites',
];

// Tabela mostrada na página "Como funciona o XP": [chave, título, o que fazer, quanto rende, limite por dia]
const TABELA_XP = [
    ['pomodoro',        'Pomodoro',          'Termine um tempo de foco inteiro, sem parar.',            '25 minutos de foco = 15 XP',                   'até 120 XP por dia'],
    ['faisca',          'Modo Faísca',       'Faça a sessão do começo ao fim, sem acelerar o tempo.',   '60 minutos = 30 XP',                           'até 100 XP por dia'],
    ['quiz',            'Quiz do Modo ENEM', 'Responda as 5 questões e confira.',                       '10 XP por acerto, mais 20 XP se acertar tudo', 'até 100 XP por dia'],
    ['simulado',        'Simulado do ENEM',  'Termine um simulado e confira o resultado.',              '2 XP por acerto, mais 10 XP se acertar 70% ou mais',  'até 120 XP por dia'],
    ['flashcard_certo', 'Flashcards',        'Vire o cartão e marque "Correto" quando acertar.',        '2 XP por cartão',                              'até 20 XP por dia'],
    ['resumo_criado',   'Resumos',           'Escreva um resumo novo.',                                 '5 XP por resumo',                              'até 15 XP por dia'],
    ['visita_site',     'Sites da Turma',    'Abra um site novo pelo portal.',                          '5 XP na primeira visita a cada site',          'só vale 1 vez por site'],
    ['login_diario',    'Entrar no Studay',  'Entre na sua conta uma vez por dia.',                     '5 XP',                                         '1 vez por dia'],
];

// Dicas que aparecem no topo de cada página de estudo (a chave é a página).
const DICAS_XP = [
    'pomodoro'   => 'Termine um tempo de foco inteiro e ganhe XP. Um foco de 25 minutos rende 15 XP.',
    'faisca'     => 'Faça a sessão do começo ao fim, sem acelerar, e ganhe XP. 60 minutos rendem 30 XP.',
    'flashcards' => 'Cada flashcard que você marcar como Correto rende 2 XP (até 20 XP por dia).',
    'resumos'    => 'Cada resumo novo que você criar rende 5 XP (até 15 XP por dia).',
    'enem'       => 'No simulado, cada acerto vale 2 XP, e se você acertar 70% ou mais ganha mais 10 XP.',
    'simulado'   => 'Termine o simulado e confira o resultado: cada acerto vale 2 XP (mais 10 XP se acertar 70% ou mais).',
    'quiz'       => 'No quiz, cada acerto vale 10 XP. Acertou tudo? Ganha mais 20 XP de bônus.',
    'sites'      => 'Abriu um site novo da turma? Você ganha 5 XP na primeira visita.',
];

/** Texto simples explicando por que uma ação não rendeu XP. Vazio = não precisa avisar. */
function mensagem_sem_xp(string $motivo, string $tipo): string
{
    $nome = NOMES_CURTOS_XP[$tipo] ?? 'essa atividade';

    switch ($motivo) {
        case 'limite_diario':
            if ($tipo === 'login_diario') return '';
            return 'Você já ganhou todo o XP de ' . $nome . ' de hoje. Amanhã tem mais!';
        case 'ja_ganhou':
            return 'Você já ganhou XP nesse site. Procure um site novo para ganhar mais.';
        case 'tempo_nao_passou':
            return 'O tempo ainda não tinha acabado, então essa sessão não rendeu XP.';
        case 'tempo_fora_do_limite':
            return 'Esse tempo é curto ou longo demais para render XP.';
        default:
            return '';
    }
}

// Ações que dão sequência de dias (estudar de verdade).
const TIPOS_QUE_CONTAM_SEQUENCIA = ['pomodoro', 'faisca', 'quiz', 'simulado', 'flashcard_certo', 'resumo_criado'];

// Conquistas (medalhas). O nome do ícone vem de includes/icones.php.
const CONQUISTAS = [
    'primeira_chama'  => ['nome' => 'Primeira chama',  'icone' => 'chama', 'descricao' => 'Ganhou o seu primeiro XP.'],
    'foco_total'      => ['nome' => 'Foco total',      'icone' => 'cronometro', 'descricao' => 'Terminou um Pomodoro inteirinho.'],
    'faisca_acesa'    => ['nome' => 'Hora do Faísca',    'icone' => 'chama', 'descricao' => 'Concluiu uma sessão do Modo Faísca.'],
    'nota_maxima'     => ['nome' => 'Nota máxima',     'icone' => 'alvo', 'descricao' => 'Acertou tudo no quiz do Modo ENEM.'],
    'explorador'      => ['nome' => 'Explorador',      'icone' => 'bussola', 'descricao' => 'Visitou 3 sites da turma.'],
    'memoria_de_ferro'=> ['nome' => 'Memória de ferro', 'icone' => 'cartas', 'descricao' => 'Acertou 20 flashcards.'],
    'constante'       => ['nome' => 'Constante',       'icone' => 'calendario', 'descricao' => 'Estudou 3 dias seguidos.'],
    'semana_de_fogo'  => ['nome' => 'Semana de fogo',  'icone' => 'trofeu', 'descricao' => 'Estudou 7 dias seguidos.'],
    'primeiro_simulado' => ['nome' => 'Primeiro simulado', 'icone' => 'lapis', 'descricao' => 'Terminou o seu primeiro simulado do ENEM.'],
    'maratonista'     => ['nome' => 'Maratonista',     'icone' => 'cronometro', 'descricao' => 'Terminou um simulado com 90 questões ou mais.'],
    'tocha_acesa'     => ['nome' => 'Tocha acesa',     'icone' => 'estrela', 'descricao' => 'Chegou ao nível 3.'],
];

/** Descobre o nível, o progresso e o quanto falta para o próximo. */
function calcular_nivel(int $xp): array
{
    $nivel = 1;
    foreach (NIVEIS_FAISCA as $numero => $dados) {
        if ($xp >= $dados['xp_minimo']) {
            $nivel = $numero;
        }
    }

    $atual   = NIVEIS_FAISCA[$nivel]['xp_minimo'];
    $proximo = NIVEIS_FAISCA[$nivel + 1]['xp_minimo'] ?? null;

    if ($proximo === null) {
        $porcentagem = 100;
        $faltam = 0;
    } else {
        $porcentagem = (int) floor((($xp - $atual) / ($proximo - $atual)) * 100);
        $faltam = $proximo - $xp;
    }

    return [
        'nivel'          => $nivel,
        'nome'           => NIVEIS_FAISCA[$nivel]['nome'],
        'xp_do_nivel'    => $atual,
        'xp_proximo'     => $proximo,
        'faltam'         => $faltam,
        'porcentagem'    => max(0, min(100, $porcentagem)),
        'nivel_maximo'   => $proximo === null,
    ];
}

function dia_de_hoje(): string
{
    return date('Y-m-d');
}

/** Segunda-feira da semana atual (a semana do ranking começa na segunda). */
function inicio_da_semana(): string
{
    return date('Y-m-d', strtotime('monday this week'));
}

/**
 * Soma XP para uma pessoa, respeitando os limites diários.
 * Devolve um resumo para o navegador mostrar o "+XP".
 */
function registrar_xp(PDO $banco, int $usuarioId, string $tipo, int $xp, ?string $referencia = null): array
{
    $regras = REGRAS_XP[$tipo] ?? null;
    $hoje   = dia_de_hoje();

    $resumo = [
        'ganhou'            => 0,
        'motivo'            => '',
        'mensagem'          => '',
        'tipo'              => $tipo,
        'rotulo'            => ROTULOS_XP[$tipo] ?? 'XP',
        'subiu_de_nivel'    => false,
        'conquistas_novas'  => [],
    ];

    if ($regras === null || $xp <= 0) {
        $resumo['motivo'] = 'tipo_invalido';
        return $resumo;
    }

    // Ações que só valem uma vez por referência (ex.: primeira visita a um site)
    if ($regras['unico'] && $referencia !== null) {
        $jaTem = $banco->prepare('SELECT COUNT(*) FROM eventos_xp WHERE usuario_id = ? AND tipo = ? AND referencia = ?');
        $jaTem->execute([$usuarioId, $tipo, $referencia]);
        if ((int) $jaTem->fetchColumn() > 0) {
            $resumo['motivo'] = 'ja_ganhou';
            $resumo['mensagem'] = mensagem_sem_xp('ja_ganhou', $tipo);
            return $resumo;
        }
    }

    // Limite diário
    if ($regras['limite_dia'] !== null) {
        $somaHoje = $banco->prepare('SELECT COALESCE(SUM(xp), 0) FROM eventos_xp WHERE usuario_id = ? AND tipo = ? AND dia = ?');
        $somaHoje->execute([$usuarioId, $tipo, $hoje]);
        $restante = $regras['limite_dia'] - (int) $somaHoje->fetchColumn();
        if ($restante <= 0) {
            $resumo['motivo'] = 'limite_diario';
            $resumo['mensagem'] = mensagem_sem_xp('limite_diario', $tipo);
            return $resumo;
        }
        $xp = min($xp, $restante);
    }

    // Nível antes de ganhar
    $linha = $banco->prepare('SELECT xp_total, sequencia_dias, maior_sequencia, ultimo_dia_ativo FROM usuarios WHERE id = ?');
    $linha->execute([$usuarioId]);
    $usuario = $linha->fetch();
    if (!$usuario) {
        $resumo['motivo'] = 'sem_usuario';
        return $resumo;
    }
    $nivelAntes = calcular_nivel((int) $usuario['xp_total'])['nivel'];

    $agora = date('Y-m-d H:i:s');
    $banco->beginTransaction();
    try {
        $banco->prepare('INSERT INTO eventos_xp (usuario_id, tipo, xp, referencia, dia, criado_em) VALUES (?, ?, ?, ?, ?, ?)')
              ->execute([$usuarioId, $tipo, $xp, $referencia, $hoje, $agora]);

        $novoTotal = (int) $usuario['xp_total'] + $xp;
        $banco->prepare('UPDATE usuarios SET xp_total = ? WHERE id = ?')->execute([$novoTotal, $usuarioId]);

        // Sequência de dias estudando
        if (in_array($tipo, TIPOS_QUE_CONTAM_SEQUENCIA, true) && $usuario['ultimo_dia_ativo'] !== $hoje) {
            $ontem = date('Y-m-d', strtotime('-1 day'));
            $sequencia = ($usuario['ultimo_dia_ativo'] === $ontem) ? (int) $usuario['sequencia_dias'] + 1 : 1;
            $maior = max($sequencia, (int) $usuario['maior_sequencia']);
            $banco->prepare('UPDATE usuarios SET sequencia_dias = ?, maior_sequencia = ?, ultimo_dia_ativo = ? WHERE id = ?')
                  ->execute([$sequencia, $maior, $hoje, $usuarioId]);
        }

        $banco->commit();
    } catch (Throwable $erro) {
        if ($banco->inTransaction()) {
            $banco->rollBack();
        }
        throw $erro;
    }

    $nivelDepois = calcular_nivel($novoTotal)['nivel'];

    $resumo['ganhou']           = $xp;
    $resumo['subiu_de_nivel']   = $nivelDepois > $nivelAntes;
    $resumo['conquistas_novas'] = verificar_conquistas($banco, $usuarioId);

    return $resumo;
}

/** Olha o que a pessoa já fez e entrega as medalhas que ela ainda não tinha. */
function verificar_conquistas(PDO $banco, int $usuarioId): array
{
    $linha = $banco->prepare('SELECT xp_total, sequencia_dias, maior_sequencia FROM usuarios WHERE id = ?');
    $linha->execute([$usuarioId]);
    $usuario = $linha->fetch();
    if (!$usuario) {
        return [];
    }

    $contagens = [];
    $porTipo = $banco->prepare('SELECT tipo, COUNT(*) AS total FROM eventos_xp WHERE usuario_id = ? GROUP BY tipo');
    $porTipo->execute([$usuarioId]);
    foreach ($porTipo->fetchAll() as $linhaTipo) {
        $contagens[$linhaTipo['tipo']] = (int) $linhaTipo['total'];
    }

    $perfeitos = $banco->prepare('SELECT COUNT(*) FROM resultados_quiz WHERE usuario_id = ? AND acertos = total');
    $perfeitos->execute([$usuarioId]);
    $quizPerfeitos = (int) $perfeitos->fetchColumn();

    $simulados = 0;
    $simuladosGrandes = 0;
    try {
        $feitos = $banco->prepare('SELECT COUNT(*) AS n, COALESCE(SUM(CASE WHEN respondidas >= 90 THEN 1 ELSE 0 END), 0) AS grandes FROM resultados_simulado WHERE usuario_id = ?');
        $feitos->execute([$usuarioId]);
        $linhaSim = $feitos->fetch();
        $simulados = (int) ($linhaSim['n'] ?? 0);
        $simuladosGrandes = (int) ($linhaSim['grandes'] ?? 0);
    } catch (Throwable $e) {
        // a tabela de simulados ainda não existe: tudo bem
    }

    $merecidas = [];
    if ($simulados >= 1)                             $merecidas[] = 'primeiro_simulado';
    if ($simuladosGrandes >= 1)                      $merecidas[] = 'maratonista';
    if (($contagens['pomodoro'] ?? 0) >= 1)         $merecidas[] = 'foco_total';
    if (($contagens['faisca'] ?? 0) >= 1)           $merecidas[] = 'faisca_acesa';
    if ($quizPerfeitos >= 1)                         $merecidas[] = 'nota_maxima';
    if (($contagens['visita_site'] ?? 0) >= 3)       $merecidas[] = 'explorador';
    if (($contagens['flashcard_certo'] ?? 0) >= 20)  $merecidas[] = 'memoria_de_ferro';
    if ((int) $usuario['maior_sequencia'] >= 3)      $merecidas[] = 'constante';
    if ((int) $usuario['maior_sequencia'] >= 7)      $merecidas[] = 'semana_de_fogo';
    if (calcular_nivel((int) $usuario['xp_total'])['nivel'] >= 3) $merecidas[] = 'tocha_acesa';
    if ((int) $usuario['xp_total'] > 0)              $merecidas[] = 'primeira_chama';

    $jaTem = $banco->prepare('SELECT codigo FROM conquistas_usuarios WHERE usuario_id = ?');
    $jaTem->execute([$usuarioId]);
    $codigosQueTem = array_column($jaTem->fetchAll(), 'codigo');

    $novas = [];
    foreach ($merecidas as $codigo) {
        if (in_array($codigo, $codigosQueTem, true) || !isset(CONQUISTAS[$codigo])) {
            continue;
        }
        $banco->prepare('INSERT INTO conquistas_usuarios (usuario_id, codigo, conquistada_em) VALUES (?, ?, ?)')
              ->execute([$usuarioId, $codigo, date('Y-m-d H:i:s')]);
        $novas[] = ['codigo' => $codigo] + CONQUISTAS[$codigo];
    }

    return $novas;
}

/** Conquistas que a pessoa já tem (com a data). */
function conquistas_do_usuario(PDO $banco, int $usuarioId): array
{
    $consulta = $banco->prepare('SELECT codigo, conquistada_em FROM conquistas_usuarios WHERE usuario_id = ?');
    $consulta->execute([$usuarioId]);
    $tem = [];
    foreach ($consulta->fetchAll() as $linha) {
        $tem[$linha['codigo']] = $linha['conquistada_em'];
    }
    return $tem;
}

/**
 * Ranking. $periodo = 'semana' ou 'geral'.
 * Mostra só o apelido (nunca o e-mail).
 */
function buscar_ranking(PDO $banco, string $periodo, int $limite = 20, ?int $meuId = null): array
{
    $limite = max(1, min(100, $limite));

    if ($periodo === 'geral') {
        $consulta = $banco->query(
            "SELECT id, apelido, xp_total AS xp, xp_total FROM usuarios WHERE xp_total > 0
             ORDER BY xp_total DESC, apelido ASC LIMIT $limite"
        );
        $linhas = $consulta->fetchAll();
    } else {
        $consulta = $banco->prepare(
            "SELECT u.id, u.apelido, u.xp_total, SUM(e.xp) AS xp
             FROM eventos_xp e JOIN usuarios u ON u.id = e.usuario_id
             WHERE e.dia >= ?
             GROUP BY u.id, u.apelido, u.xp_total
             ORDER BY xp DESC, u.apelido ASC LIMIT $limite"
        );
        $consulta->execute([inicio_da_semana()]);
        $linhas = $consulta->fetchAll();
    }

    $avatares = avatares_de_usuarios($banco, array_column($linhas, 'id'));

    $lista = [];
    foreach ($linhas as $posicao => $linha) {
        $nivel = calcular_nivel((int) $linha['xp_total']);
        $lista[] = [
            'posicao' => $posicao + 1,
            'apelido' => $linha['apelido'],
            'avatar'  => avatar_arquivo($avatares[(int) $linha['id']] ?? AVATAR_PADRAO),
            'xp'      => (int) $linha['xp'],
            'nivel'   => $nivel['nivel'],
            'nome_nivel' => $nivel['nome'],
            'sou_eu'  => $meuId !== null && (int) $linha['id'] === $meuId,
        ];
    }
    return $lista;
}

/** Posição e XP de uma pessoa no ranking (mesmo que ela não esteja no top). */
function minha_posicao(PDO $banco, string $periodo, int $usuarioId): ?array
{
    if ($periodo === 'geral') {
        $xp = $banco->prepare('SELECT xp_total FROM usuarios WHERE id = ?');
        $xp->execute([$usuarioId]);
        $meuXp = (int) $xp->fetchColumn();
        if ($meuXp <= 0) return null;

        $acima = $banco->prepare('SELECT COUNT(*) FROM usuarios WHERE xp_total > ?');
        $acima->execute([$meuXp]);
    } else {
        $xp = $banco->prepare('SELECT COALESCE(SUM(xp), 0) FROM eventos_xp WHERE usuario_id = ? AND dia >= ?');
        $xp->execute([$usuarioId, inicio_da_semana()]);
        $meuXp = (int) $xp->fetchColumn();
        if ($meuXp <= 0) return null;

        $acima = $banco->prepare(
            'SELECT COUNT(*) FROM (SELECT usuario_id FROM eventos_xp WHERE dia >= ? GROUP BY usuario_id HAVING SUM(xp) > ?) AS acima'
        );
        $acima->execute([inicio_da_semana(), $meuXp]);
    }

    return ['posicao' => (int) $acima->fetchColumn() + 1, 'xp' => $meuXp];
}

/** Busca a linha completa de uma pessoa (usa * para não quebrar se o banco ainda não tem as colunas novas). */
function usuario_por_id(PDO $banco, int $id): ?array
{
    $consulta = $banco->prepare('SELECT * FROM usuarios WHERE id = ?');
    $consulta->execute([$id]);
    $linha = $consulta->fetch();
    return $linha ?: null;
}

/** Dados do usuário prontos para mandar ao navegador (menu lateral, avisos etc.). Nunca inclui e-mail nem senha. */
function estado_do_usuario(array $usuario): array
{
    $nivel = calcular_nivel((int) $usuario['xp_total']);
    return [
        'logado'                => true,
        'apelido'               => $usuario['apelido'],
        'papel'                 => $usuario['papel'],
        'avatar'                => avatar_arquivo($usuario['avatar'] ?? AVATAR_PADRAO),
        'xp_total'              => (int) $usuario['xp_total'],
        'nivel'                 => $nivel,
        'sequencia_dias'        => (int) $usuario['sequencia_dias'],
        'apresentacao_pendente' => (int) ($usuario['apresentacao_vista'] ?? 1) === 0,
    ];
}


/**
 * Desenha a lista dos 6 níveis (usada no Meu progresso e no Como funciona o XP).
 * $xpAtual = XP da pessoa logada, ou -1 para quem não entrou na conta.
 */
function desenhar_lista_niveis(int $xpAtual = -1, string $caminhoImagens = '../'): void
{
    $nivelAtual = $xpAtual >= 0 ? calcular_nivel($xpAtual)['nivel'] : 0;
    echo '<ol class="lista-niveis">';
    foreach (NIVEIS_FAISCA as $numero => $dados) {
        $classe = 'lista-nivel';
        if ($numero === $nivelAtual) $classe .= ' lista-nivel-atual';
        elseif ($numero < $nivelAtual) $classe .= ' lista-nivel-feito';
        echo '<li class="' . $classe . '">';
        echo '<img src="' . h($caminhoImagens) . 'img/faisca/nivel-' . (int) $numero . '.webp" alt="" loading="lazy" '
           . 'onerror="this.onerror=null;this.src=\'' . h($caminhoImagens) . 'img/logo/faisca-sem-fundo.webp\';this.classList.add(\'faisca-reserva\');" data-nivel="' . (int) $numero . '">';
        echo '<div class="lista-nivel-texto"><strong>Nível ' . (int) $numero . ' · ' . h($dados['nome']) . '</strong>';
        echo '<span>' . ($dados['xp_minimo'] === 0 ? 'Todo mundo começa aqui' : 'A partir de ' . (int) $dados['xp_minimo'] . ' XP') . '</span></div>';
        if ($numero === $nivelAtual) echo '<em class="lista-nivel-etiqueta">Você está aqui</em>';
        elseif ($numero < $nivelAtual) echo '<em class="lista-nivel-etiqueta lista-nivel-ok">' . icone('check') . '</em>';
        echo '</li>';
    }
    echo '</ol>';
}
