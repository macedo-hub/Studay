<?php

// Variáveis
$gabarito = ['q1' => 'b', 'q2' => 'd', 'q3' => 'c', 'q4' => 'a', 'q5' => 'e'];


$quiz_enviado = false;
$acertos      = 0;
$total        = count($gabarito);
$respostas    = [];
$feedback_msg    = '';
$feedback_classe = '';
$nivel_titulo    = '';

// Processa o formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['quiz_submit'])) {
    $quiz_enviado = true;

    // Pega as respostas enviadas
    $respostas['q1'] = isset($_POST['q1']) ? $_POST['q1'] : '';
    $respostas['q2'] = isset($_POST['q2']) ? $_POST['q2'] : '';
    $respostas['q3'] = isset($_POST['q3']) ? $_POST['q3'] : '';
    $respostas['q4'] = isset($_POST['q4']) ? $_POST['q4'] : '';
    $respostas['q5'] = isset($_POST['q5']) ? $_POST['q5'] : '';

    // Operador relacional
    if ($respostas['q1'] === $gabarito['q1']) { $acertos += 1; }
    if ($respostas['q2'] === $gabarito['q2']) { $acertos += 1; }
    if ($respostas['q3'] === $gabarito['q3']) { $acertos += 1; }
    if ($respostas['q4'] === $gabarito['q4']) { $acertos += 1; }
    if ($respostas['q5'] === $gabarito['q5']) { $acertos += 1; }

    // Calcula percentual
    $percentual = ($acertos / $total) * 100;

    $resto_validacao = $acertos % 2;

    // SWITCH
        switch ($acertos) {
        case 5:
            $nivel_titulo = 'Excelente desempenho';
            break;

        case 4:
            $nivel_titulo = 'Ótimo desempenho';
            break;

        case 3:
            $nivel_titulo = 'Bom desempenho';
            break;

        case 2:
            $nivel_titulo = 'Desempenho regular';
            break;

        case 1:
            $nivel_titulo = 'Desempenho insuficiente';
            break;

        case 0:
            $nivel_titulo = 'Necessita de revisão';
            break;

        default:
            $nivel_titulo = 'Resultado do Quiz';
    }

    // Estruturas condicionais
    if ($percentual >= 80 || $acertos === 5) {
        $feedback_msg = "Você acertou $acertos de $total questões ($percentual%).";
        $feedback_classe = 'feedback-sucesso';
    } else if ($percentual >= 60 || $acertos === 3) {
        $feedback_msg = "Você acertou $acertos de $total questões ($percentual%).";
        $feedback_classe = 'feedback-aviso';
    } else {
        $feedback_msg = "Você acertou $acertos de $total questões ($percentual%).";
        $feedback_classe = 'feedback-erro';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Studay - Modo ENEM</title>

  <link rel="stylesheet" href="../css/style.css" />
  <link rel="stylesheet" href="../css/modoENEM.css" />
  <link href="https://fonts.cdnfonts.com/css/seasons" rel="stylesheet">
  <link href="https://db.onlinewebfonts.com/c/d353583e5cdf878d047eae178df5b757?family=Stars+%26+Love+Bottom+Heavy" rel="stylesheet">
  <link rel="shortcut icon" href="../img/faisca.png" type="image/x-icon">
</head>
<body>
  <div class="main-container">

    <aside class="sidebar">
      <a href="perfil.html"><img src="../img/faisca.png" alt="Faisca - Perfil" class="icon"></a>
      <a href="modoENEM.php"><img src="../img/modoEnem.png" alt="Modo Enem" class="icon active-icon"></a>
      <a href="metodos.html"><img src="../img/livro.png" alt="Métodos" class="icon"></a>
      <div class="music-icon">
        <img src="../img/musica.png" alt="Música" class="icon">
        <div class="music-player">
          <iframe width="280" height="200"
            src="https://www.youtube.com/embed/jfKfPfyJRdk?autoplay=1"
            frameborder="0" allow="autoplay"></iframe>
        </div>
      </div>
      <img src="../img/comunidade.png" alt="Comunidades" class="icon">
      <img src="../img/configuracoes.png" alt="Configurações" class="icon">
    </aside>

    <main class="content enem-main">

      <h2 class="metodos-title">Modo ENEM</h2>

      <a href="metodos.html" class="home-btn">
        <img src="../img/casa.png" alt="Home" class="home-icon">
        <span>Métodos</span>
      </a>

      <div class="enem-container-wrapper">

        <div class="passos-header">
          <span class="passo-label">Revolução de 1930</span>
          <span class="passo-label">Governo Provisório</span>
          <span class="passo-label">Estado Novo</span>
        </div>

        <div class="passos-grid">
          <div class="passo-card" id="card-rev1930" onclick="togglePanel('rev1930')">
            <img src="../img/rev1930.png" alt="Revolução de 1930" class="passo-img">
          </div>
          <div class="passo-card" id="card-govprov" onclick="togglePanel('govprov')">
            <img src="../img/gov_provisorio.png" alt="Governo Provisório" class="passo-img">
          </div>
          <div class="passo-card" id="card-estnovo" onclick="togglePanel('estnovo')">
            <img src="../img/estadonovo.jpg" alt="Estado Novo" class="passo-img">
          </div>
        </div>

        <div class="passo-panel" id="panel-rev1930">
          <div class="passo-panel-inner">
            <p>
              A Revolução de 1930 foi o movimento que colocou Getúlio Vargas no poder e encerrou a República Velha.
              Na época existia a política do café com leite, na qual São Paulo e Minas Gerais se revezavam na presidência.
              Em 1930, o presidente Washington Luís quebrou esse acordo ao apoiar outro paulista, Júlio Prestes.
            </p>
            <p>
              Como resposta, Minas Gerais, Rio Grande do Sul e Paraíba formaram a Aliança Liberal e lançaram Getúlio
              Vargas como candidato. Vargas perdeu a eleição, mas a oposição denunciou fraudes. O assassinato de João
              Pessoa, vice de Vargas, aumentou a revolta contra o governo.
            </p>
            <p>
              Em outubro de 1930, grupos militares e políticos iniciaram uma revolta armada que derrubou Washington Luís
              e impediu a posse de Júlio Prestes. Com isso, Vargas assumiu a presidência provisória do Brasil. A revolução
              marcou o fim do domínio das oligarquias da República Velha e o início de uma nova fase da história brasileira.
            </p>
          </div>
        </div>

        <div class="passo-panel" id="panel-govprov">
          <div class="passo-panel-inner">
            <p>
              O Governo Provisório foi a primeira fase da Era Vargas. Nesse período, Vargas governou sem Constituição e
              sem Congresso Nacional, utilizando decretos para tomar decisões. Seu objetivo oficial era reorganizar o
              país até a elaboração de uma nova Constituição.
            </p>
            <p>
              Vargas promoveu a centralização do poder, retirando a autonomia das antigas oligarquias estaduais. Os
              governadores foram substituídos por interventores nomeados pelo governo federal. Também foram criados
              importantes ministérios, como o Ministério da Educação e Saúde e o Ministério do Trabalho.
            </p>
            <p>
              Em 1932 ocorreu a Revolução Constitucionalista, quando os paulistas exigiram uma nova Constituição.
              Apesar da derrota militar, conseguiram pressionar Vargas. Em 1934 foi promulgada uma nova Constituição
              que trouxe o voto secreto, o voto feminino, a Justiça Eleitoral e direitos trabalhistas.
            </p>
          </div>
        </div>

        <div class="passo-panel" id="panel-estnovo">
          <div class="passo-panel-inner">
            <p>
              O Estado Novo foi a fase ditatorial da Era Vargas. Em 1937, alegando a existência de uma ameaça comunista,
              Vargas deu um golpe de Estado, cancelou as eleições previstas e implantou uma nova Constituição.
            </p>
            <p>
              Durante o Estado Novo, o Congresso Nacional foi fechado, os partidos políticos foram proibidos e a censura
              foi fortalecida. O Departamento de Imprensa e Propaganda controlava a divulgação de informações e
              fiscalizava manifestações culturais.
            </p>
            <p>
              Apesar do autoritarismo, esse período ficou marcado pelo avanço da industrialização e pela ampliação da
              legislação trabalhista. Em 1943 foi criada a CLT. Com o fim da Segunda Guerra Mundial e o aumento das
              pressões por democracia, Vargas foi deposto em 1945.
            </p>
          </div>
        </div>

        <div class="enem-secao-box">
          <h3 class="enem-secao-titulo">Vídeo sobre o assunto:</h3>
        </div>

        <div class="enem-secao-box enem-video-box">
          <iframe
            class="enem-iframe-video"
            src="https://www.youtube.com/embed/-O9yeWy2E-s?si=z9-KeJMLvm3ZDKFa"
            allow="autoplay"
            allowfullscreen>
          </iframe>
        </div>

        <div class="enem-secao-box">
          <h3 class="enem-secao-titulo">Perguntas do ENEM sobre Era Vargas:</h3>
        </div>

        <!-- Feedback do quiz -->
        <?php if ($quiz_enviado): ?>
          <div class="enem-feedback enem-feedback-<?= htmlspecialchars($feedback_classe) ?>">
            <h3><?= htmlspecialchars($nivel_titulo) ?></h3>
            <p><?= htmlspecialchars($feedback_msg) ?></p>
          </div>
        <?php endif; ?>

        <form method="POST" action="modoENEM.php" class="enem-quiz-form">

          <!-- QUESTÃO 1 -->
          <div class="enem-questao-box">
            <div class="enem-questao-header">QUESTÃO 1 — ENEM 2011</div>
            <div class="enem-questao-enunciado">
              <p>
                O governo Vargas, principalmente durante o Estado Novo (1937-1945), pretendeu construir um State capaz
                de criar uma nova sociedade. Uma dimensão-chave desse projeto tinha no território seu foco principal.
                Não por acaso, foram criadas então instituições encarregadas de fornecer dados confiáveis para a ação
                do governo, como o Conselho Nacional de Geografia, o Conselho Nacional de Cartografia, o Conselho
                Nacional de Estatística e o Instituto Brasileiro de Geografia e Estatística (IBGE), este de 1938.
                <br>LIPPI L. A conquista de Oeste. (adaptado)
                <br><br>A criação dessas instituições pelo governo Vargas representa uma estratégia política de:
              </p>
            </div>
            <div class="enem-alternativas">
              <?php
              $alt_q1 = [
                'a' => 'Levantar informações para a preservação da paisagem dos sertões.',
                'b' => 'Obter conhecimento científico das diversidades regionais.',
                'c' => 'Controlar o crescimento exponencial da população brasileira.',
                'd' => 'Conter o fluxo migratório do campo para a cidade.',
                'e' => 'Propor a criação de novas unidades da federação.'
              ];
              foreach ($alt_q1 as $letra => $texto):
                $classe_alt = '';
                if ($quiz_enviado) {
                  if ($letra === $gabarito['q1']) { $classe_alt = 'alt-correta'; }
                  elseif (isset($respostas['q1']) && $respostas['q1'] === $letra) { $classe_alt = 'alt-errada'; }
                }
              ?>
              <label class="enem-alt <?= $classe_alt ?>">
                <input type="radio" name="q1" value="<?= $letra ?>"
                  <?= ($quiz_enviado && isset($respostas['q1']) && $respostas['q1'] === $letra) ? 'checked' : '' ?>
                  <?= $quiz_enviado ? 'disabled' : '' ?>>
                <span class="alt-letra"><?= strtoupper($letra) ?></span>
                <?= htmlspecialchars($texto) ?>
              </label>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- QUESTÃO 2 -->
          <div class="enem-questao-box">
            <div class="enem-questao-header">QUESTÃO 2 — ENEM 2013</div>
            <div class="enem-questao-enunciado">
              <p>
                Quando Getúlio Vargas se suicidou, em agosto de 1954, o país parecia à beira do caos. Acuado por uma
                grave crise política, o velho líder preferiu uma bala no peito à humilhação de aceitar uma nova
                deposição, como a que sofrera em outubro de 1945. Entretanto, ao contrário do que imaginavam os
                inimigos, ao ruído do estampido não se seguiu o silêncio que cerca a derrota.
                <br>REIS FILHO, D. A. O Estado à sombra de Vargas. Revista Nossa História, n. 7, maio 2004.
                <br><br>O evento analisado no texto teve como repercussão imediata na política nacional a
              </p>
            </div>
            <div class="enem-alternativas">
              <?php
              $alt_q2 = [
                'a' => 'Campanha anticomunista.',
                'b' => 'Intervenção militar.',
                'c' => 'Abertura democrática.',
                'd' => 'Reação popular.',
                'e' => 'Radicalização oposicionista.'
              ];
              foreach ($alt_q2 as $letra => $texto):
                $classe_alt = '';
                if ($quiz_enviado) {
                  if ($letra === $gabarito['q2']) { $classe_alt = 'alt-correta'; }
                  elseif (isset($respostas['q2']) && $respostas['q2'] === $letra) { $classe_alt = 'alt-errada'; }
                }
              ?>
              <label class="enem-alt <?= $classe_alt ?>">
                <input type="radio" name="q2" value="<?= $letra ?>"
                  <?= ($quiz_enviado && isset($respostas['q2']) && $respostas['q2'] === $letra) ? 'checked' : '' ?>
                  <?= $quiz_enviado ? 'disabled' : '' ?>>
                <span class="alt-letra"><?= strtoupper($letra) ?></span>
                <?= htmlspecialchars($texto) ?>
              </label>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- QUESTÃO 3 -->
          <div class="enem-questao-box">
            <div class="enem-questao-header">QUESTÃO 3 — ENEM 2021</div>
            <div class="enem-questao-enunciado">
              <p>
                A Educação Nova, alargando a sua finalidade para além dos limites das classes, assume, com uma feição
                mais humana, a sua verdadeira função social, preparando-se para formar "a hierarquia democrática" pela
                "hierarquia das capacidades", recrutadas em todos os grupos sociais, a que se abrem as mesmas
                oportunidades de educação.
                <br>Disponível em: www.histedbr.fe.unicamp.br. (adaptado).
                <br><br>Os autores do manifesto citado procuravam contrapor-se ao caráter oligárquico da sociedade
                brasileira. Nesse sentido, o trecho propõe uma relação necessária entre
              </p>
            </div>
            <div class="enem-alternativas">
              <?php
              $alt_q3 = [
                'a' => 'Ensino técnico e mercado de trabalho.',
                'b' => 'Ampliação de vagas e formação de gestores.',
                'c' => 'Acesso à escola e valorização do mérito.',
                'd' => 'Disponibilidade de financiamento e pesquisa avançada.',
                'e' => 'Remuneração de professores e extinção do analfabetismo.'
              ];
              foreach ($alt_q3 as $letra => $texto):
                $classe_alt = '';
                if ($quiz_enviado) {
                  if ($letra === $gabarito['q3']) { $classe_alt = 'alt-correta'; }
                  elseif (isset($respostas['q3']) && $respostas['q3'] === $letra) { $classe_alt = 'alt-errada'; }
                }
              ?>
              <label class="enem-alt <?= $classe_alt ?>">
                <input type="radio" name="q3" value="<?= $letra ?>"
                  <?= ($quiz_enviado && isset($respostas['q3']) && $respostas['q3'] === $letra) ? 'checked' : '' ?>
                  <?= $quiz_enviado ? 'disabled' : '' ?>>
                <span class="alt-letra"><?= strtoupper($letra) ?></span>
                <?= htmlspecialchars($texto) ?>
              </label>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- QUESTÃO 4 -->
          <div class="enem-questao-box">
            <div class="enem-questao-header">QUESTÃO 4 — ENEM 2017</div>
            <div class="enem-questao-enunciado">
              <p>
                Nos primeiros anos do governo Vargas, as organizações operárias sob controle das correntes de esquerda
                tentaram se opor ao seu enquadramento pelo Estado. Mas a tentativa fracassou. Além do governo, a própria
                base dessas organizações pressionou pela legalização. Vários benefícios, como as férias e a possibilidade
                de postular direitos perante às Juntas de Conciliação e Julgamento, dependiam da condição de ser membro
                de sindicato reconhecido pelo governo.
                <br>FAUSTO, B. História concisa do Brasil. São Paulo: Edusp, 2002 (adaptado).
                <br><br>No contexto histórico retratado pelo texto, a relação entre governo e movimento sindical foi
                caracterizada
              </p>
            </div>
            <div class="enem-alternativas">
              <?php
              $alt_q4 = [
                'a' => 'Pela vinculação de direitos trabalhistas à tutela do Estado.',
                'b' => 'Por um diálogo democraticamente constituído.',
                'c' => 'Pelas benesses sociais do getulismo.',
                'd' => 'Pelo reconhecimento de diferentes ideologias políticas.',
                'e' => 'Por uma legislação construída consensualmente.'
              ];
              foreach ($alt_q4 as $letra => $texto):
                $classe_alt = '';
                if ($quiz_enviado) {
                  if ($letra === $gabarito['q4']) { $classe_alt = 'alt-correta'; }
                  elseif (isset($respostas['q4']) && $respostas['q4'] === $letra) { $classe_alt = 'alt-errada'; }
                }
              ?>
              <label class="enem-alt <?= $classe_alt ?>">
                <input type="radio" name="q4" value="<?= $letra ?>"
                  <?= ($quiz_enviado && isset($respostas['q4']) && $respostas['q4'] === $letra) ? 'checked' : '' ?>
                  <?= $quiz_enviado ? 'disabled' : '' ?>>
                <span class="alt-letra"><?= strtoupper($letra) ?></span>
                <?= htmlspecialchars($texto) ?>
              </label>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- QUESTÃO 5 -->
          <div class="enem-questao-box">
            <div class="enem-questao-header">QUESTÃO 5 — ENEM 2015</div>
            <div class="enem-questao-enunciado">
              <p>
                A Justiça Eleitoral foi criada em 1932, como parte de uma ampla reforma no processo eleitoral
                incentivada pela Revolução de 1930. Sua criação foi um grande avanço institucional, garantindo que
                as eleições tivessem o aval de um órgão teoricamente imune à influência dos mandatários.
                <br>TAYLOR, M. Justiça Eleitoral. In: AVRITZER, L.; ANASTASIA, F. Reforma política no Brasil. UFMG, 2006 (adaptado).
                <br><br>Em relação ao regime democrático no país, a instituição analisada teve o seguinte papel:
              </p>
            </div>
            <div class="enem-alternativas">
              <?php
              $alt_q5 = [
                'a' => 'Expandiu a participação com o fim do critério censitário.',
                'b' => 'Implementou o voto direto para presidente.',
                'c' => 'Alterou as regras para as candidaturas na ditadura.',
                'd' => 'Impulsionou as denúncias de corrupção administrativa.',
                'e' => 'Combateu as fraudes sistemáticas nas apurações.'
              ];
              foreach ($alt_q5 as $letra => $texto):
                $classe_alt = '';
                if ($quiz_enviado) {
                  if ($letra === $gabarito['q5']) { $classe_alt = 'alt-correta'; }
                  elseif (isset($respostas['q5']) && $respostas['q5'] === $letra) { $classe_alt = 'alt-errada'; }
                }
              ?>
              <label class="enem-alt <?= $classe_alt ?>">
                <input type="radio" name="q5" value="<?= $letra ?>"
                  <?= ($quiz_enviado && isset($respostas['q5']) && $respostas['q5'] === $letra) ? 'checked' : '' ?>
                  <?= $quiz_enviado ? 'disabled' : '' ?>>
                <span class="alt-letra"><?= strtoupper($letra) ?></span>
                <?= htmlspecialchars($texto) ?>
              </label>
              <?php endforeach; ?>
            </div>
          </div>

          <?php if (!$quiz_enviado): ?>
            <div class="enem-submit-container">
              <button type="submit" name="quiz_submit" class="enem-btn-submit">
                Verificar Respostas
              </button>
            </div>
          <?php else: ?>
            <div class="enem-submit-container">
              <a href="modoENEM.php" class="enem-btn-submit" style="text-decoration:none; display:inline-block;">
                Tentar Novamente
              </a>
            </div>
          <?php endif; ?>

        </form>

      </div>
    </main>

  </div>

  <script src="../js/modoENEM.js"></script>
</body>
</html>