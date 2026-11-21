<?php
/*
 * MEU PERFIL — a pessoa muda o nome, o apelido, a foto (avatar pronto) e a senha.
 * Não existe envio de foto: os avatares são imagens prontas do site (deixa o servidor leve).
 * Só quem está logado entra aqui.
 */

require_once __DIR__ . '/valida_dados.php';
require_once __DIR__ . '/../includes/sessao.php';
require_once __DIR__ . '/../includes/gamificacao.php';
require_once __DIR__ . '/../includes/menu.php';

$usuario = usuario_logado();
if (!$usuario) {
    header('Location: login.php?voltar=perfil.php&aviso=entre');
    exit;
}

if (!defined('PADRAO_VALIDA_NOME'))    define('PADRAO_VALIDA_NOME', '/^[\p{L}][\p{L}\s\-.]{2,59}$/u');
if (!defined('PADRAO_VALIDA_APELIDO')) define('PADRAO_VALIDA_APELIDO', '/^[\p{L}\p{N}_]{3,20}$/u');
if (!defined('PADRAO_VALIDA_SENHA'))   define('PADRAO_VALIDA_SENHA', '/^(?=.*[A-Za-z])(?=.*\d).{8,64}$/');

$temColunaAvatar = array_key_exists('avatar', $usuario);
$erros = ['dados' => '', 'nome' => '', 'apelido' => '', 'avatar' => '', 'senha_atual' => '', 'senha_nova' => '', 'senha_confirma' => '', 'geral' => ''];
$nome = $usuario['nome'];
$apelido = $usuario['apelido'];

$mensagensOk = [
    'dados'  => 'Pronto! Seus dados foram atualizados.',
    'avatar' => 'Foto de perfil trocada. Ficou ótima!',
    'senha'  => 'Senha trocada com sucesso.',
];
$avisoOk = $mensagensOk[$_GET['ok'] ?? ''] ?? '';

/** Volta para a página mostrando a mensagem de sucesso (evita reenviar o formulário ao atualizar). */
function perfil_voltar_com_sucesso(string $qual): void
{
    header('Location: perfil.php?ok=' . $qual);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    if (!confere_csrf($_POST['token_csrf'] ?? '')) {
        $erros['geral'] = 'A página ficou aberta por muito tempo. Atualize e tente de novo.';
    } else {
        try {
            $banco = conectar_banco();
            $id = (int) $usuario['id'];

            if ($acao === 'dados') {
                $nome = valida_dados($_POST['nome'] ?? '');
                $apelido = valida_dados($_POST['apelido'] ?? '');

                if (empty($nome)) {
                    $erros['nome'] = 'Informe seu nome.';
                } elseif (!preg_match(PADRAO_VALIDA_NOME, $nome)) {
                    $erros['nome'] = 'Use de 3 a 60 caracteres: apenas letras, espaços, ponto e hífen.';
                }

                if (empty($apelido)) {
                    $erros['apelido'] = 'Escolha um apelido para o ranking.';
                } elseif (!preg_match(PADRAO_VALIDA_APELIDO, $apelido)) {
                    $erros['apelido'] = 'Use de 3 a 20 caracteres: letras, números e _ (sem espaço).';
                } else {
                    $repetido = $banco->prepare('SELECT COUNT(*) FROM usuarios WHERE apelido = ? AND id <> ?');
                    $repetido->execute([$apelido, $id]);
                    if ((int) $repetido->fetchColumn() > 0) {
                        $erros['apelido'] = 'Esse apelido já está em uso. Escolha outro!';
                    }
                }

                if ($erros['nome'] === '' && $erros['apelido'] === '') {
                    $banco->prepare('UPDATE usuarios SET nome = ?, apelido = ? WHERE id = ?')->execute([$nome, $apelido, $id]);
                    perfil_voltar_com_sucesso('dados');
                }

            } elseif ($acao === 'avatar') {
                $codigo = $_POST['avatar'] ?? '';
                if (!$temColunaAvatar) {
                    $erros['avatar'] = 'Falta uma atualização no banco de dados. Rode o arquivo sql/atualizacao_perfil.sql no phpMyAdmin.';
                } elseif (!avatar_codigo_valido($codigo)) {
                    $erros['avatar'] = 'Escolha uma das fotos da lista.';
                } else {
                    $banco->prepare('UPDATE usuarios SET avatar = ? WHERE id = ?')->execute([$codigo, $id]);
                    perfil_voltar_com_sucesso('avatar');
                }

            } elseif ($acao === 'senha') {
                $atual = (string) ($_POST['senha_atual'] ?? '');
                $nova = valida_dados($_POST['senha_nova'] ?? '');
                $confirma = valida_dados($_POST['senha_confirma'] ?? '');

                if ($atual === '' || !password_verify($atual, $usuario['senha_hash'])) {
                    $erros['senha_atual'] = 'A senha atual não confere.';
                }
                if (empty($nova)) {
                    $erros['senha_nova'] = 'Crie uma senha nova.';
                } elseif (!preg_match(PADRAO_VALIDA_SENHA, $nova)) {
                    $erros['senha_nova'] = 'Use 8 a 64 caracteres com letras e números.';
                }
                if ($nova !== $confirma) {
                    $erros['senha_confirma'] = 'As senhas não coincidem.';
                }

                if ($erros['senha_atual'] === '' && $erros['senha_nova'] === '' && $erros['senha_confirma'] === '') {
                    $banco->prepare('UPDATE usuarios SET senha_hash = ? WHERE id = ?')
                          ->execute([password_hash($nova, PASSWORD_DEFAULT), $id]);
                    session_regenerate_id(true);
                    perfil_voltar_com_sucesso('senha');
                }
            }
        } catch (Throwable $erro) {
            error_log('Studay perfil: ' . $erro->getMessage());
            $erros['geral'] = 'Estamos com um probleminha para salvar agora. Tente de novo daqui a pouco.';
        }
    }
}

$codigoAvatar = $temColunaAvatar && avatar_codigo_valido($usuario['avatar']) ? $usuario['avatar'] : AVATAR_PADRAO;
$nivel = calcular_nivel((int) $usuario['xp_total']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Studay — Meu perfil</title>
    <link rel="shortcut icon" href="../img/logo/faisca.webp" type="image/x-icon">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/perfil.css">
    <link rel="stylesheet" href="../css/gamificacao.css">
    <link rel="stylesheet" href="../css/animacoes.css">
</head>
<body>
<div class="main-container">

<?php desenhar_menu(''); ?>

    <main class="content">
        <h1 class="page-title">Meu perfil</h1>

        <?php if ($avisoOk !== ''): ?>
        <p class="perfil-aviso perfil-aviso-ok" role="status"><?= icone('check') ?> <?= h($avisoOk) ?></p>
        <?php endif; ?>
        <?php if ($erros['geral'] !== ''): ?>
        <p class="perfil-aviso perfil-aviso-erro" role="alert"><?= h($erros['geral']) ?></p>
        <?php endif; ?>

        <section class="perfil-topo revelar">
            <img src="../<?= h(avatar_arquivo($codigoAvatar)) ?>" alt="" class="perfil-topo-avatar js-avatar-previa">
            <div>
                <h2><?= h($usuario['apelido']) ?></h2>
                <p>Nível <?= (int) $nivel['nivel'] ?> · <?= h($nivel['nome']) ?> · <?= (int) $usuario['xp_total'] ?> XP</p>
                <a href="progresso.php" class="perfil-topo-link">Ver meu progresso <?= icone('seta') ?></a>
            </div>
        </section>

        <!-- FOTO DE PERFIL -->
        <section class="perfil-caixa revelar">
            <h2>Foto de perfil</h2>
            <p class="perfil-texto">Escolha uma das fotos prontas. Tem Faíscas, memes e bichinhos.</p>

            <?php if ($erros['avatar'] !== ''): ?>
            <p class="perfil-aviso perfil-aviso-erro" role="alert"><?= h($erros['avatar']) ?></p>
            <?php endif; ?>

            <form method="POST" action="perfil.php" class="js-form-avatar">
                <?= campo_csrf() ?>
                <input type="hidden" name="acao" value="avatar">

                <div class="avatar-abas" role="tablist" aria-label="Categorias de foto">
                    <?php $primeira = true; foreach (CATEGORIAS_AVATARES as $chave => $categoria): ?>
                    <button type="button" role="tab" class="avatar-aba js-aba-avatar<?= $primeira ? ' ativa' : '' ?>"
                            data-categoria="<?= h($chave) ?>" aria-selected="<?= $primeira ? 'true' : 'false' ?>">
                        <?= h($categoria['nome']) ?>
                    </button>
                    <?php $primeira = false; endforeach; ?>
                </div>

                <?php $primeira = true; foreach (CATEGORIAS_AVATARES as $chave => $categoria): ?>
                <div class="avatar-grade js-grade-avatar<?= $primeira ? '' : ' escondida' ?>" data-categoria="<?= h($chave) ?>" role="tabpanel">
                    <?php foreach ($categoria['itens'] as $codigo => $rotulo): ?>
                    <label class="avatar-opcao">
                        <input type="radio" name="avatar" value="<?= h($codigo) ?>" <?= $codigo === $codigoAvatar ? 'checked' : '' ?>>
                        <span class="avatar-moldura"><img src="../<?= h(avatar_arquivo($codigo)) ?>" alt="" loading="lazy"></span>
                        <span class="avatar-nome"><?= h($rotulo) ?></span>
                    </label>
                    <?php endforeach; ?>
                </div>
                <?php $primeira = false; endforeach; ?>

                <button type="submit" class="btn-primary perfil-botao-salvar">Salvar foto</button>
            </form>
        </section>

        <!-- DADOS -->
        <section class="perfil-caixa revelar">
            <h2>Meus dados</h2>
            <form method="POST" action="perfil.php" class="perfil-form" novalidate>
                <?= campo_csrf() ?>
                <input type="hidden" name="acao" value="dados">

                <label for="nome">Nome</label>
                <input type="text" id="nome" name="nome" value="<?= h($nome) ?>" autocomplete="name">
                <?php if ($erros['nome'] !== ''): ?><p class="perfil-erro-campo" role="alert"><?= h($erros['nome']) ?></p><?php endif; ?>

                <label for="apelido">Apelido (aparece no ranking)</label>
                <input type="text" id="apelido" name="apelido" value="<?= h($apelido) ?>" maxlength="20" autocomplete="nickname">
                <?php if ($erros['apelido'] !== ''): ?><p class="perfil-erro-campo" role="alert"><?= h($erros['apelido']) ?></p><?php endif; ?>

                <label for="email">E-mail</label>
                <input type="email" id="email" value="<?= h($usuario['email']) ?>" disabled>
                <small class="perfil-dica">O e-mail não pode ser trocado.</small>

                <button type="submit" class="btn-primary perfil-botao-salvar">Salvar dados</button>
            </form>
        </section>

        <!-- SENHA -->
        <section class="perfil-caixa revelar">
            <h2>Trocar senha</h2>
            <form method="POST" action="perfil.php" class="perfil-form" novalidate>
                <?= campo_csrf() ?>
                <input type="hidden" name="acao" value="senha">

                <label for="senha_atual">Senha atual</label>
                <input type="password" id="senha_atual" name="senha_atual" autocomplete="current-password">
                <?php if ($erros['senha_atual'] !== ''): ?><p class="perfil-erro-campo" role="alert"><?= h($erros['senha_atual']) ?></p><?php endif; ?>

                <label for="senha_nova">Senha nova</label>
                <input type="password" id="senha_nova" name="senha_nova" autocomplete="new-password">
                <?php if ($erros['senha_nova'] !== ''): ?><p class="perfil-erro-campo" role="alert"><?= h($erros['senha_nova']) ?></p><?php endif; ?>

                <label for="senha_confirma">Repita a senha nova</label>
                <input type="password" id="senha_confirma" name="senha_confirma" autocomplete="new-password">
                <?php if ($erros['senha_confirma'] !== ''): ?><p class="perfil-erro-campo" role="alert"><?= h($erros['senha_confirma']) ?></p><?php endif; ?>

                <small class="perfil-dica">Use 8 a 64 caracteres, com letras e números.</small>
                <button type="submit" class="btn-primary perfil-botao-salvar">Trocar senha</button>
            </form>
        </section>

        <div class="perfil-rodape-links">
            <button type="button" class="perfil-link-botao js-abrir-apresentacao"><?= icone('repetir') ?> Ver apresentação do Faísca</button>
            <a href="sair.php" class="perfil-link-botao"><?= icone('sair') ?> Sair da conta</a>
        </div>
    </main>

</div>

<script src="../js/navigation.js"></script>
<script src="../js/gamificacao.js"></script>
<script src="../js/perfil.js"></script>
<script src="../js/animacoes.js"></script>
</body>
</html>
