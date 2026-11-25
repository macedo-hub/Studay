<?php
/*
 * MEU PROGRESSO — o nível do Faísca, o XP, a sequência de dias e as conquistas.
 * Só quem está logado vê esta página.
 */

require_once __DIR__ . '/../includes/sessao.php';
require_once __DIR__ . '/../includes/gamificacao.php';
require_once __DIR__ . '/../includes/menu.php';

$usuario = usuario_logado();
if (!$usuario) {
    header('Location: login.php?voltar=progresso.php&aviso=entre');
    exit;
}

$erroBanco = false;
$ultimosDias = [];
$atividades = [];
$tem = [];
$posicaoSemana = null;

try {
    $banco = conectar_banco();
    $idUsuario = (int) $usuario['id'];

    $tem = conquistas_do_usuario($banco, $idUsuario);
    $posicaoSemana = minha_posicao($banco, 'semana', $idUsuario);

    // XP dos últimos 7 dias
    $inicio = date('Y-m-d', strtotime('-6 days'));
    $consulta = $banco->prepare('SELECT dia, SUM(xp) AS total FROM eventos_xp WHERE usuario_id = ? AND dia >= ? GROUP BY dia');
    $consulta->execute([$idUsuario, $inicio]);
    $porDia = [];
    foreach ($consulta->fetchAll() as $linha) {
        $porDia[$linha['dia']] = (int) $linha['total'];
    }
    $nomesDias = ['dom', 'seg', 'ter', 'qua', 'qui', 'sex', 'sáb'];
    for ($i = 6; $i >= 0; $i--) {
        $data = date('Y-m-d', strtotime("-$i days"));
        $ultimosDias[] = ['rotulo' => $nomesDias[(int) date('w', strtotime($data))], 'xp' => $porDia[$data] ?? 0, 'hoje' => $i === 0];
    }

    // Últimas atividades
    $consulta = $banco->prepare('SELECT tipo, xp, criado_em FROM eventos_xp WHERE usuario_id = ? ORDER BY id DESC LIMIT 6');
    $consulta->execute([$idUsuario]);
    $atividades = $consulta->fetchAll();
} catch (Throwable $erro) {
    error_log('Studay progresso: ' . $erro->getMessage());
    $erroBanco = true;
}

$nivel = calcular_nivel((int) $usuario['xp_total']);
$maiorDia = max(1, ...array_map(fn($d) => $d['xp'], $ultimosDias ?: [['xp' => 1]]));

$textosAtividade = [
    'login_diario'    => 'Entrou no Studay hoje',
    'pomodoro'        => 'Terminou um Pomodoro',
    'faisca'          => 'Concluiu uma sessão do Modo Faísca',
    'quiz'            => 'Respondeu o quiz do Modo ENEM',
    'flashcard_certo' => 'Acertou um flashcard',
    'resumo_criado'   => 'Criou um resumo',
    'visita_site'     => 'Explorou um site da turma',
];

$comoGanhar = [
    ['Pomodoro',                 'Termine um tempo de foco inteiro.',                      'até 120 XP por dia'],
    ['Modo Faísca',              'Faça a sessão do começo ao fim, sem acelerar.',          'até 100 XP por dia'],
    ['Quiz do Modo ENEM',        'Cada acerto vale 10 XP e gabaritar dá um bônus.',        'até 100 XP por dia'],
    ['Flashcards',               'Marque "Correto" quando acertar um cartão.',             'até 20 XP por dia'],
    ['Resumos',                  'Escreva um resumo novo.',                                'até 15 XP por dia'],
    ['Sites da turma',           'Visite um site novo pelo portal.',                       '5 XP em cada site'],
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Studay — Meu progresso</title>
    <link rel="shortcut icon" href="../img/logo/faisca.webp" type="image/x-icon">
    <!-- Ícones do site -->
    <link rel="icon" href="../favicon.ico" sizes="any">
    <link rel="icon" type="image/png" sizes="48x48" href="../img/icones/icone-48.png">
    <link rel="icon" type="image/png" sizes="192x192" href="../img/icones/icone-192.png">
    <link rel="apple-touch-icon" sizes="180x180" href="../img/icones/apple-touch-icon.png">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/progresso.css">
    <link rel="stylesheet" href="../css/gamificacao.css">
    <link rel="stylesheet" href="../css/animacoes.css">
</head>
<body>
<div class="main-container">

<?php desenhar_menu('progresso'); ?>

    <main class="content">
        <h1 class="page-title">Meu progresso</h1>
        <p class="progresso-intro">Olá, <strong><?= h($usuario['apelido']) ?></strong>! Veja até onde o Faísca já chegou com você.</p>

        <?php if ($erroBanco): ?>
        <p class="admin-aviso">Alguns dados não carregaram agora. Atualize a página daqui a pouco.</p>
        <?php endif; ?>

        <section class="progresso-heroi revelar" data-nivel="<?= (int) $nivel['nivel'] ?>">
            <div class="progresso-faisca">
                <div class="progresso-brilho" aria-hidden="true"></div>
                <img src="../img/faisca/nivel-<?= (int) $nivel['nivel'] ?>.webp"
                     alt="O Faísca no nível <?= (int) $nivel['nivel'] ?>: <?= h($nivel['nome']) ?>"
                     class="progresso-faisca-img" data-nivel="<?= (int) $nivel['nivel'] ?>"
                     onerror="this.onerror=null;this.src='../img/logo/faisca-sem-fundo.webp';this.classList.add('faisca-reserva');">
            </div>

            <div class="progresso-info">
                <span class="progresso-nivel">Nível <?= (int) $nivel['nivel'] ?></span>
                <h2 class="progresso-nome"><?= h($nivel['nome']) ?></h2>

                <div class="barra-xp" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="<?= (int) $nivel['porcentagem'] ?>">
                    <div class="barra-xp-preenchida js-barra-xp" data-largura="<?= (int) $nivel['porcentagem'] ?>"></div>
                </div>

                <p class="progresso-xp">
                    <strong><?= (int) $usuario['xp_total'] ?> XP</strong>
                    <?php if ($nivel['nivel_maximo']): ?>
                        · Você chegou ao nível máximo. Que orgulho!
                    <?php else: ?>
                        · faltam <?= (int) $nivel['faltam'] ?> XP para virar <strong><?= h(NIVEIS_FAISCA[$nivel['nivel'] + 1]['nome']) ?></strong>
                    <?php endif; ?>
                </p>
            </div>
        </section>

        <section class="progresso-numeros">
            <div class="numero-card revelar"><strong><?= (int) $usuario['sequencia_dias'] ?></strong><span>dias seguidos</span></div>
            <div class="numero-card revelar"><strong><?= (int) $usuario['maior_sequencia'] ?></strong><span>maior sequência</span></div>
            <div class="numero-card revelar"><strong><?= $posicaoSemana ? (int) $posicaoSemana['posicao'] . 'º' : '—' ?></strong><span>no ranking da semana</span></div>
            <div class="numero-card revelar"><strong><?= count($tem) ?>/<?= count(CONQUISTAS) ?></strong><span>conquistas</span></div>
        </section>

        <section class="progresso-caixa revelar">
            <h2>XP dos últimos 7 dias</h2>
            <div class="grafico-dias" role="img" aria-label="Gráfico de XP por dia">
                <?php foreach ($ultimosDias as $dia): ?>
                <div class="grafico-dia <?= $dia['hoje'] ? 'grafico-hoje' : '' ?>">
                    <span class="grafico-valor"><?= (int) $dia['xp'] ?></span>
                    <div class="grafico-barra-fundo">
                        <div class="grafico-barra js-barra-dia" data-altura="<?= (int) round(($dia['xp'] / $maiorDia) * 100) ?>"></div>
                    </div>
                    <span class="grafico-rotulo"><?= h($dia['rotulo']) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="progresso-caixa revelar">
            <h2>Conquistas</h2>
            <div class="conquistas-grade">
                <?php foreach (CONQUISTAS as $codigo => $dados):
                    $ganhou = isset($tem[$codigo]); ?>
                <div class="conquista <?= $ganhou ? 'conquista-ganha' : 'conquista-bloqueada' ?>" title="<?= h($dados['descricao']) ?>">
                    <span class="conquista-icone" aria-hidden="true"><?= icone($ganhou ? $dados['icone'] : 'cadeado') ?></span>
                    <strong><?= h($dados['nome']) ?></strong>
                    <small><?= h($dados['descricao']) ?></small>
                </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="progresso-caixa revelar">
            <h2>Os 6 níveis do Faísca</h2>
            <p class="progresso-texto">Quanto mais XP você junta, mais o Faísca evolui.</p>
            <?php desenhar_lista_niveis((int) $usuario['xp_total']); ?>
        </section>

        <section class="progresso-caixa revelar">
            <h2>Como ganhar mais XP</h2>
            <ul class="como-ganhar">
                <?php foreach ($comoGanhar as [$titulo, $texto, $limite]): ?>
                <li><div><strong><?= h($titulo) ?></strong><span><?= h($texto) ?></span></div><em><?= h($limite) ?></em></li>
                <?php endforeach; ?>
            </ul>
            <a href="comoFunciona.php" class="progresso-link-mais">Ver a explicação completa do XP <?= icone('seta') ?></a>
        </section>

        <?php if (count($atividades) > 0): ?>
        <section class="progresso-caixa revelar">
            <h2>Últimas atividades</h2>
            <ul class="atividades">
                <?php foreach ($atividades as $atividade): ?>
                <li>
                    <span><?= h($textosAtividade[$atividade['tipo']] ?? 'Ganhou XP') ?> <small><?= h(tempo_atras($atividade['criado_em'])) ?></small></span>
                    <strong>+<?= (int) $atividade['xp'] ?> XP</strong>
                </li>
                <?php endforeach; ?>
            </ul>
        </section>
        <?php endif; ?>

        <div class="progresso-rodape">
            <a href="perfil.php" class="progresso-sair"><?= icone('lapis') ?> Editar perfil</a>
            <a href="sair.php" class="progresso-sair"><?= icone('sair') ?> Sair da conta</a>
        </div>
    </main>

</div>

<script src="../js/navigation.js"></script>
<script src="../js/gamificacao.js"></script>
<script src="../js/animacoes.js"></script>
</body>
</html>
