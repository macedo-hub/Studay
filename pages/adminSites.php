<?php
/*
 * ÁREA DA EQUIPE — cadastrar, editar, esconder e apagar os sites da turma.
 * Só administradores entram aqui (veja EMAILS_ADMIN em config/config.php).
 * Todos os envios usam token CSRF e consultas preparadas (PDO).
 */

require_once __DIR__ . '/../includes/sessao.php';
require_once __DIR__ . '/../includes/sites.php';
require_once __DIR__ . '/../includes/menu.php';

$admin = exige_admin();
$banco = conectar_banco();

$mensagem = $_SESSION['aviso_admin'] ?? '';
unset($_SESSION['aviso_admin']);

// Campos do formulário (vazios por padrão)
$campos = [
    'id' => 0, 'nome_site' => '', 'equipe' => '', 'disciplina' => 'História', 'descricao' => '',
    'perspectiva' => '', 'link' => '', 'imagem_capa' => '', 'validado_por' => '', 'testado_por' => '',
    'fontes' => '', 'ordem' => 0, 'ativo' => 1,
];
$erros = [];

/** Guarda um aviso e recarrega a página (assim o F5 não repete a ação). */
function avisar_e_voltar(string $texto): void
{
    $_SESSION['aviso_admin'] = $texto;
    header('Location: adminSites.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!confere_csrf($_POST['token_csrf'] ?? '')) {
        avisar_e_voltar('A página ficou aberta por muito tempo. Tente de novo.');
    }

    $acao = $_POST['acao'] ?? '';
    $idAlvo = filter_var($_POST['id'] ?? 0, FILTER_VALIDATE_INT);
    $idAlvo = $idAlvo === false ? 0 : $idAlvo;

    if ($acao === 'excluir' && $idAlvo > 0) {
        $banco->prepare('DELETE FROM sites_turma WHERE id = ?')->execute([$idAlvo]);
        avisar_e_voltar('Site apagado.');
    }

    if ($acao === 'alternar' && $idAlvo > 0) {
        $banco->prepare('UPDATE sites_turma SET ativo = 1 - ativo, atualizado_em = ? WHERE id = ?')
              ->execute([date('Y-m-d H:i:s'), $idAlvo]);
        avisar_e_voltar('Pronto! Mudei se o site aparece ou não no portal.');
    }

    if ($acao === 'salvar') {
        foreach (['nome_site', 'equipe', 'disciplina', 'descricao', 'perspectiva', 'link', 'imagem_capa', 'validado_por', 'testado_por', 'fontes'] as $nome) {
            $campos[$nome] = limpa_texto($_POST[$nome] ?? '');
        }
        $campos['id'] = $idAlvo;
        $campos['ordem'] = (int) filter_var($_POST['ordem'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['default' => 0]]);
        $campos['ativo'] = isset($_POST['ativo']) ? 1 : 0;

        // Validações
        $t = 'tamanho_texto';
        if ($t($campos['nome_site']) < 3 || $t($campos['nome_site']) > 80) $erros['nome_site'] = 'Use de 3 a 80 letras.';
        if ($t($campos['equipe']) < 3 || $t($campos['equipe']) > 120)      $erros['equipe'] = 'Use de 3 a 120 letras.';
        if (!in_array($campos['disciplina'], DISCIPLINAS_SITES, true))     $erros['disciplina'] = 'Escolha uma matéria da lista.';
        if ($t($campos['descricao']) < 10 || $t($campos['descricao']) > 300) $erros['descricao'] = 'Escreva de 10 a 300 letras.';
        if ($t($campos['perspectiva']) > 400)                              $erros['perspectiva'] = 'No máximo 400 letras.';
        if (!link_seguro($campos['link']) || $t($campos['link']) > 255)    $erros['link'] = 'Cole um link completo, começando com https://';
        if ($campos['imagem_capa'] !== '' && !nome_de_imagem_valido($campos['imagem_capa'])) {
            $erros['imagem_capa'] = 'Escreva só o nome do arquivo, como capa-equipe.webp (formatos: webp, png ou jpg).';
        }
        if ($t($campos['validado_por']) > 80) $erros['validado_por'] = 'No máximo 80 letras.';
        if ($t($campos['testado_por']) > 80)  $erros['testado_por'] = 'No máximo 80 letras.';
        if ($t($campos['fontes']) > 400)      $erros['fontes'] = 'No máximo 400 letras.';
        if ($campos['ordem'] < 0 || $campos['ordem'] > 999) $erros['ordem'] = 'Use um número de 0 a 999.';

        if (empty($erros)) {
            $agora = date('Y-m-d H:i:s');
            $valores = [
                $campos['nome_site'], $campos['equipe'], $campos['disciplina'], $campos['descricao'],
                $campos['perspectiva'], $campos['link'], $campos['imagem_capa'], $campos['validado_por'],
                $campos['testado_por'], $campos['fontes'], $campos['ativo'], $campos['ordem'], $agora,
            ];

            if ($idAlvo > 0) {
                $banco->prepare(
                    'UPDATE sites_turma SET nome_site=?, equipe=?, disciplina=?, descricao=?, perspectiva=?, link=?,
                     imagem_capa=?, validado_por=?, testado_por=?, fontes=?, ativo=?, ordem=?, atualizado_em=? WHERE id=?'
                )->execute(array_merge($valores, [$idAlvo]));
                avisar_e_voltar('Site atualizado com sucesso!');
            }

            $banco->prepare(
                'INSERT INTO sites_turma (nome_site, equipe, disciplina, descricao, perspectiva, link, imagem_capa,
                 validado_por, testado_por, fontes, ativo, ordem, atualizado_em, criado_em) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
            )->execute(array_merge($valores, [$agora]));
            avisar_e_voltar('Site cadastrado! Ele já aparece no portal.');
        }
    }
}

// Abrir um site para editar (?editar=3)
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && isset($_GET['editar'])) {
    $idEditar = filter_var($_GET['editar'], FILTER_VALIDATE_INT);
    if ($idEditar !== false && $idEditar > 0) {
        $busca = $banco->prepare('SELECT * FROM sites_turma WHERE id = ?');
        $busca->execute([$idEditar]);
        $encontrado = $busca->fetch();
        if ($encontrado) {
            $campos = array_merge($campos, $encontrado);
        }
    }
}

$todos = $banco->query(
    'SELECT s.*, (SELECT COUNT(*) FROM cliques_sites c WHERE c.site_id = s.id) AS acessos
     FROM sites_turma s ORDER BY s.ordem ASC, s.nome_site ASC'
)->fetchAll();

$editando = (int) $campos['id'] > 0;

/** Mostra a mensagem de erro de um campo. */
function erro_do_campo(array $erros, string $nome): void
{
    if (isset($erros[$nome])) {
        echo '<p class="form-error form-error-campo visivel" role="alert">' . h($erros[$nome]) . '</p>';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Studay — Gerenciar sites</title>
    <link rel="shortcut icon" href="../img/logo/faisca.webp" type="image/x-icon">
    <!-- Ícones do site -->
    <link rel="icon" href="../favicon.ico" sizes="any">
    <link rel="icon" type="image/png" sizes="48x48" href="../img/icones/icone-48.png">
    <link rel="icon" type="image/png" sizes="192x192" href="../img/icones/icone-192.png">
    <link rel="apple-touch-icon" sizes="180x180" href="../img/icones/apple-touch-icon.png">
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/sitesTurma.css">
    <link rel="stylesheet" href="../css/gamificacao.css">
    <link rel="stylesheet" href="../css/animacoes.css">
</head>
<body>
<div class="main-container">

<?php desenhar_menu('sites'); ?>

    <main class="content">
        <h1 class="page-title">Gerenciar sites</h1>
        <p class="sites-intro">
            Cadastre aqui o site de cada equipe. Tudo o que você escrever aparece no portal
            <a href="sitesTurma.php">Sites da Turma</a>.
        </p>

        <?php if ($mensagem !== ''): ?>
        <p class="admin-aviso" role="status"><?= h($mensagem) ?></p>
        <?php endif; ?>

        <section class="admin-caixa revelar">
            <h2><?= $editando ? 'Editar site' : 'Novo site' ?></h2>

            <form method="POST" action="adminSites.php" class="auth-form admin-form" novalidate>
                <?= campo_csrf() ?>
                <input type="hidden" name="acao" value="salvar">
                <input type="hidden" name="id" value="<?= (int) $campos['id'] ?>">

                <div class="admin-coluna">
                    <label for="nome_site">Nome do site</label>
                    <input type="text" id="nome_site" name="nome_site" maxlength="80" value="<?= h($campos['nome_site']) ?>" placeholder="Ex.: Matemática sem Mistério">
                    <?php erro_do_campo($erros, 'nome_site'); ?>
                </div>

                <div class="admin-coluna">
                    <label for="equipe">Equipe (quem fez)</label>
                    <input type="text" id="equipe" name="equipe" maxlength="120" value="<?= h($campos['equipe']) ?>" placeholder="Ex.: Ana, Bruno e Carla">
                    <?php erro_do_campo($erros, 'equipe'); ?>
                </div>

                <div class="admin-coluna">
                    <label for="disciplina">Matéria</label>
                    <select id="disciplina" name="disciplina">
                        <?php foreach (DISCIPLINAS_SITES as $materia): ?>
                        <option value="<?= h($materia) ?>" <?= $campos['disciplina'] === $materia ? 'selected' : '' ?>><?= h($materia) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php erro_do_campo($erros, 'disciplina'); ?>
                </div>

                <div class="admin-coluna">
                    <label for="link">Link do site (InfinityFree)</label>
                    <input type="url" id="link" name="link" maxlength="255" value="<?= h($campos['link']) ?>" placeholder="https://nomedaequipe.free.nf">
                    <?php erro_do_campo($erros, 'link'); ?>
                </div>

                <div class="admin-coluna admin-larga">
                    <label for="descricao">O que tem no site (até 300 letras)</label>
                    <textarea id="descricao" name="descricao" rows="3" maxlength="300" placeholder="Conte de um jeito simples o que a pessoa vai encontrar."><?= h($campos['descricao']) ?></textarea>
                    <?php erro_do_campo($erros, 'descricao'); ?>
                </div>

                <div class="admin-coluna admin-larga">
                    <label for="perspectiva">O olhar da equipe (opcional)</label>
                    <textarea id="perspectiva" name="perspectiva" rows="2" maxlength="400" placeholder="Como a equipe enxergou o ENEM? O que fez o site ser diferente?"><?= h($campos['perspectiva']) ?></textarea>
                    <?php erro_do_campo($erros, 'perspectiva'); ?>
                </div>

                <div class="admin-coluna">
                    <label for="imagem_capa">Imagem de capa (nome do arquivo)</label>
                    <input type="text" id="imagem_capa" name="imagem_capa" maxlength="80" value="<?= h($campos['imagem_capa']) ?>" placeholder="capa-nome-da-equipe.webp">
                    <small class="admin-dica">Coloque o arquivo na pasta <code>img/sites/</code> e escreva aqui só o nome dele.</small>
                    <?php erro_do_campo($erros, 'imagem_capa'); ?>
                </div>

                <div class="admin-coluna">
                    <label for="ordem">Ordem na lista (0 aparece primeiro)</label>
                    <input type="number" id="ordem" name="ordem" min="0" max="999" value="<?= (int) $campos['ordem'] ?>">
                    <?php erro_do_campo($erros, 'ordem'); ?>
                </div>

                <div class="admin-coluna">
                    <label for="validado_por">Professor(a) que validou</label>
                    <input type="text" id="validado_por" name="validado_por" maxlength="80" value="<?= h($campos['validado_por']) ?>" placeholder="Ex.: Prof. Fulano (História)">
                    <?php erro_do_campo($erros, 'validado_por'); ?>
                </div>

                <div class="admin-coluna">
                    <label for="testado_por">Equipe que testou</label>
                    <input type="text" id="testado_por" name="testado_por" maxlength="80" value="<?= h($campos['testado_por']) ?>" placeholder="Ex.: Equipe Beta">
                    <?php erro_do_campo($erros, 'testado_por'); ?>
                </div>

                <div class="admin-coluna admin-larga">
                    <label for="fontes">Fontes e créditos</label>
                    <textarea id="fontes" name="fontes" rows="2" maxlength="400" placeholder="Textos, imagens e questões usados (ex.: ENEM 2019, Brasil Escola)."><?= h($campos['fontes']) ?></textarea>
                    <?php erro_do_campo($erros, 'fontes'); ?>
                </div>

                <label class="admin-check admin-larga">
                    <input type="checkbox" name="ativo" value="1" <?= (int) $campos['ativo'] === 1 ? 'checked' : '' ?>>
                    Mostrar este site no portal
                </label>

                <div class="admin-botoes admin-larga">
                    <button type="submit" class="btn-primary"><?= $editando ? 'Salvar mudanças' : 'Cadastrar site' ?></button>
                    <?php if ($editando): ?>
                    <a href="adminSites.php" class="admin-cancelar">Cancelar</a>
                    <?php endif; ?>
                </div>
            </form>
        </section>

        <section class="admin-caixa revelar">
            <h2>Sites cadastrados (<?= count($todos) ?>)</h2>

            <?php if (count($todos) === 0): ?>
                <p class="admin-vazio">Nenhum site cadastrado ainda. Use o formulário acima para começar.</p>
            <?php else: ?>
            <ul class="admin-lista">
                <?php foreach ($todos as $site): ?>
                <li class="admin-item <?= (int) $site['ativo'] === 1 ? '' : 'admin-item-escondido' ?>">
                    <div class="admin-item-info">
                        <strong><?= h($site['nome_site']) ?></strong>
                        <span><?= h($site['equipe']) ?> · <?= h($site['disciplina']) ?> · <?= (int) $site['acessos'] ?> visitas</span>
                        <span class="admin-item-status"><?= (int) $site['ativo'] === 1 ? 'Aparecendo no portal' : 'Escondido' ?></span>
                    </div>
                    <div class="admin-item-acoes">
                        <a class="admin-mini" href="adminSites.php?editar=<?= (int) $site['id'] ?>#nome_site">Editar</a>

                        <form method="POST" action="adminSites.php">
                            <?= campo_csrf() ?>
                            <input type="hidden" name="acao" value="alternar">
                            <input type="hidden" name="id" value="<?= (int) $site['id'] ?>">
                            <button type="submit" class="admin-mini"><?= (int) $site['ativo'] === 1 ? 'Esconder' : 'Mostrar' ?></button>
                        </form>

                        <form method="POST" action="adminSites.php" onsubmit="return confirm('Apagar este site? Isso não dá para desfazer.');">
                            <?= campo_csrf() ?>
                            <input type="hidden" name="acao" value="excluir">
                            <input type="hidden" name="id" value="<?= (int) $site['id'] ?>">
                            <button type="submit" class="admin-mini admin-mini-perigo">Apagar</button>
                        </form>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </section>
    </main>

</div>

<script src="../js/navigation.js"></script>
<script src="../js/gamificacao.js"></script>
<script src="../js/animacoes.js"></script>
</body>
</html>
