<?php
/*
 * IR PARA UM SITE DA TURMA
 * Conta o clique, dá XP na primeira visita e manda a pessoa para o site da equipe.
 * É por aqui que o portal sabe quantas vezes cada site foi aberto.
 */

require_once __DIR__ . '/../includes/sessao.php';
require_once __DIR__ . '/../includes/gamificacao.php';

$id = filter_var($_GET['id'] ?? '', FILTER_VALIDATE_INT);
$destino = '';
$nomeSite = '';
$xpGanho = 0;
$logado = false;

try {
    if ($id !== false && $id > 0) {
        $banco = conectar_banco();
        $consulta = $banco->prepare('SELECT id, nome_site, link FROM sites_turma WHERE id = ? AND ativo = 1');
        $consulta->execute([$id]);
        $site = $consulta->fetch();

        if ($site && link_seguro($site['link'])) {
            $destino  = $site['link'];
            $nomeSite = $site['nome_site'];

            $usuario = usuario_logado();
            $logado  = $usuario !== null;

            $banco->prepare('INSERT INTO cliques_sites (site_id, usuario_id, criado_em) VALUES (?, ?, ?)')
                  ->execute([$site['id'], $usuario ? $usuario['id'] : null, date('Y-m-d H:i:s')]);

            if ($usuario) {
                $resumo  = registrar_xp($banco, (int) $usuario['id'], 'visita_site', 5, (string) $site['id']);
                $xpGanho = $resumo['ganhou'];
            }
        }
    }
} catch (Throwable $erro) {
    error_log('Studay ir.php: ' . $erro->getMessage());
}

if ($destino === '') {
    header('Location: sitesTurma.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta http-equiv="refresh" content="2;url=<?= h($destino) ?>">
    <title>Studay — Abrindo o site...</title>
    <link rel="shortcut icon" href="../img/logo/faisca.webp" type="image/x-icon">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/animacoes.css">
    <link rel="stylesheet" href="../css/sitesTurma.css">
</head>
<body>
    <main class="page-auth">
        <div class="saindo-box">
            <img src="../img/logo/faisca-sem-fundo.webp" alt="" class="saindo-faisca">
            <h1 class="saindo-titulo">Abrindo o site da equipe...</h1>
            <p class="saindo-nome"><?= h($nomeSite) ?></p>
            <?php if ($xpGanho > 0): ?>
                <p class="saindo-xp">+<?= (int) $xpGanho ?> XP por explorar um site novo!</p>
            <?php elseif (!$logado): ?>
                <p class="saindo-dica">Dica: entre na sua conta para ganhar XP quando visitar os sites.</p>
            <?php endif; ?>
            <a class="btn-primary saindo-botao" href="<?= h($destino) ?>">Se não abrir sozinho, clique aqui</a>
        </div>
    </main>
    <script>
        setTimeout(function () { window.location.replace(<?= json_encode($destino, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>); }, 1600);
    </script>
</body>
</html>
