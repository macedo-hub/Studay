<?php
/*
 * MODO ENEM — página inicial.
 * Daqui a pessoa escolhe: fazer um simulado com questões de verdade ou estudar um tema (Era Vargas).
 */
require_once __DIR__ . '/../includes/sessao.php';
require_once __DIR__ . '/../includes/gamificacao.php';
require_once __DIR__ . '/../includes/simulado.php';
require_once __DIR__ . '/../includes/menu.php';

$historico = [];
$n_duvidas = 0;
$n_erros   = 0;
$logado    = false;

try {
    $usuario = usuario_logado();
    if ($usuario) {
        $logado = true;
        $banco = conectar_banco();
        $historico = historico_simulados($banco, (int) $usuario['id'], 3);
        $n_duvidas = count(questoes_salvas($banco, (int) $usuario['id'], 'duvida'));
        $n_erros   = count(questoes_salvas($banco, (int) $usuario['id'], 'erro'));
    }
} catch (Throwable $e) {
    error_log('Studay modoENEM: ' . $e->getMessage());
}

$total_questoes = 0;
foreach (contagem_de_questoes() as $porArea) {
    $total_questoes += array_sum($porArea);
}

function formata_duracao(int $seg): string
{
    if ($seg < 60) return $seg . ' s';
    $min = intdiv($seg, 60);
    if ($min < 60) return $min . ' min';
    return intdiv($min, 60) . ' h ' . ($min % 60) . ' min';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Studay — Modo ENEM</title>
    <!-- Pré-visualização de links (Open Graph e Twitter Cards) -->
    <meta name="description" content="Faça simulados com questões do ENEM de 2021, 2022 e 2023 e acompanhe seu desempenho por área.">
    <link rel="canonical" href="https://studay.site.je/pages/modoENEM.php">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="Studay">
    <meta property="og:locale" content="pt_BR">
    <meta property="og:title" content="Modo ENEM | Studay">
    <meta property="og:description" content="Faça simulados com questões do ENEM de 2021, 2022 e 2023 e acompanhe seu desempenho por área.">
    <meta property="og:url" content="https://studay.site.je/pages/modoENEM.php">
    <meta property="og:image" content="https://studay.site.je/img/compartilhar/studay-preview.jpg">
    <meta property="og:image:secure_url" content="https://studay.site.je/img/compartilhar/studay-preview.jpg">
    <meta property="og:image:type" content="image/jpeg">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="Studay: o Faísca, mascote do site, ao lado do nome Studay">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="Modo ENEM | Studay">
    <meta name="twitter:description" content="Faça simulados com questões do ENEM de 2021, 2022 e 2023 e acompanhe seu desempenho por área.">
    <meta name="twitter:image" content="https://studay.site.je/img/compartilhar/studay-preview.jpg">
    <meta name="twitter:image:alt" content="Studay: o Faísca, mascote do site, ao lado do nome Studay">
    <!-- Fim da pré-visualização de links -->
    <link rel="shortcut icon" href="../img/logo/faisca.webp" type="image/x-icon">
    <!-- Ícones do site -->
    <link rel="icon" href="../favicon.ico" sizes="any">
    <link rel="icon" type="image/png" sizes="48x48" href="../img/icones/icone-48.png">
    <link rel="icon" type="image/png" sizes="192x192" href="../img/icones/icone-192.png">
    <link rel="apple-touch-icon" sizes="180x180" href="../img/icones/apple-touch-icon.png">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/modoENEM.css">
    <link rel="stylesheet" href="../css/simulado.css">
    <link rel="stylesheet" href="../css/gamificacao.css">
    <link rel="stylesheet" href="../css/animacoes.css">
</head>
<body data-xp-pagina="enem">
<div class="main-container">

<?php desenhar_menu('enem'); ?>

    <main class="content">

        <h1 class="page-title">Modo ENEM</h1>

        <div class="hub-enem">

            <p class="hub-intro">Treine com questões de verdade das provas do ENEM ou revise um assunto antes de fazer o simulado.</p>

            <section class="hub-principal" aria-labelledby="hub-simulado-titulo">
                <div class="hub-principal-texto">
                    <span class="hub-etiqueta">Novo</span>
                    <h2 id="hub-simulado-titulo">Simulado do ENEM</h2>
                    <p>São <?= (int) $total_questoes ?> questões das provas de 2021, 2022 e 2023. Você escolhe quantas questões quer, o tempo, as áreas e se quer ver o gabarito na hora ou só no final. Também dá para imprimir.</p>
                    <div class="hub-atalhos">
                        <a class="hub-atalho" href="simulado.php?qtd=10">10 questões</a>
                        <a class="hub-atalho" href="simulado.php?qtd=25">25 questões</a>
                        <a class="hub-atalho" href="simulado.php?qtd=45">45 questões</a>
                        <a class="hub-atalho hub-atalho-forte" href="simulado.php">Personalizar</a>
                    </div>
                </div>
                <?php if ($logado): ?>
                <div class="hub-resumo" aria-label="Seu caderno de estudos">
                    <div><strong><?= (int) $n_duvidas ?></strong><span>questões com dúvida</span></div>
                    <div><strong><?= (int) $n_erros ?></strong><span>questões que você errou</span></div>
                </div>
                <?php endif; ?>
            </section>

            <?php if ($historico): ?>
            <section class="hub-historico" aria-label="Últimos simulados">
                <h3>Seus últimos simulados</h3>
                <ul>
                    <?php foreach ($historico as $h):
                        $pct = $h['total'] > 0 ? (int) round($h['acertos'] / $h['total'] * 100) : 0; ?>
                    <li>
                        <span class="hist-nota"><?= (int) $h['acertos'] ?>/<?= (int) $h['total'] ?></span>
                        <span class="hist-barra"><i style="width:<?= $pct ?>%"></i></span>
                        <span class="hist-info"><?= $pct ?>% &middot; <?= h(formata_duracao((int) $h['duracao_seg'])) ?> &middot; <?= h(tempo_atras($h['criado_em'])) ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </section>
            <?php endif; ?>

            <section class="hub-tema" aria-labelledby="hub-tema-titulo">
                <img src="../img/enem/rev1930.webp" alt="" class="hub-tema-img" loading="lazy">
                <div>
                    <h2 id="hub-tema-titulo">Estudar por tema: Era Vargas</h2>
                    <p>Um resumo da Revolução de 1930, do Governo Provisório e do Estado Novo, com vídeo e 5 perguntas.</p>
                    <a class="hub-atalho" href="eraVargas.php">Abrir o tema</a>
                </div>
            </section>

            <p class="hub-creditos">
                Questões das provas oficiais do INEP (ENEM 2021, 2022 e 2023), organizadas a partir do projeto aberto
                <a href="https://enem.dev" target="_blank" rel="noopener">enem.dev</a>.
                As provas originais em PDF ficam no site do
                <a href="https://www.gov.br/inep/pt-br/areas-de-atuacao/avaliacao-e-exames-educacionais/enem/provas-e-gabaritos" target="_blank" rel="noopener">INEP</a>.
            </p>
        </div>
    </main>

</div>

<script src="../js/navigation.js"></script>
<script src="../js/gamificacao.js"></script>
<script src="../js/animacoes.js"></script>
</body>
</html>
