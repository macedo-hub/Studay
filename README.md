<div align="center">
<img width="500" alt="Studay" src="https://github.com/user-attachments/assets/ca6ea08f-1601-4876-b837-7f3f6f9c24e3" />
</div>

# **Studay!**

Um site planejado para transformar esforço em resultados, tornando os estudos uma atividade mais leve, interativa e eficiente.

---

### Contexto Acadêmico

Este projeto foi desenvolvido como parte da avaliação da disciplina de Desenvolvimento de Aplicações Web II.

- **Instituição:** Instituto Federal da Paraíba – Campus Santa Luzia
- **Curso:** Técnico Integrado em Informática
- **Disciplina:** Desenvolvimento de Aplicações Web II
- **Professor:** Felipe Ramos
- **Bimestre:** 1º Bimestre - Avaliação 01 (Projeto em Equipe) e atualizações subsequentes.
- **Versão atual:** 4º Bimestre - Avaliação 02 (Plataforma ENEM: banco de dados, portal dos sites da turma, XP e ranking). Antes: 3º Bimestre (validação de dados e proteção contra HTML/JavaScript Injection).

---

### Equipe

A equipe responsável pelo desenvolvimento e implementação das funcionalidades do sistema é formada por:

- **Lucas Emanuel Nóbrega de Macêdo** - `nobrega.macedo@academico.ifpb.edu.br`
- **Iasmim Anahí Alves Silva** - `iasmim.anahi@academico.ifpb.edu.br`
- **Sabrina Yasmine Araújo da Silva** - `sabrina.yasmine@academico.ifpb.edu`

---

### Sobre as Atualizações

O projeto evoluiu de uma interface estática para uma aplicação web dinâmica e interativa.

1. **Etapa Client-Side (JavaScript):** Implementação da lógica de programação para captura de formulários, persistência local de dados e manipulação do DOM em tempo real.
2. **Etapa Server-Side (PHP):** Introdução de processamento no servidor para criação de módulos dinâmicos, como o Modo ENEM e, posteriormente, o Modo Faísca.

---

### Funcionalidades Implementadas

O projeto conta com funcionalidades desenvolvidas tanto no front-end quanto no back-end.

#### 1. Calculadora de Média

*Atende ao requisito de processamento de formulários e lógica matemática.*

- **Funcionamento:** O usuário insere notas em campos gerados dinamicamente.
- **Lógica JS:** Uso de `document.createElement` para adicionar novos campos de nota; captura do evento `submit` com `event.preventDefault()`; loop (`for...of`) para somar os valores; exibição dinâmica do resultado (`textContent`).
- **Validação:** O sistema alerta se algum campo estiver vazio ou se a nota não estiver entre 0 e 100.

<div align="center">
<img width="319" height="350" alt="Calculadora de Média" src="https://github.com/user-attachments/assets/54dd4290-020d-44f9-a020-7a74c6821e0a" />
</div>

#### 2. Flashcards Interativos

- **Gerenciamento:** Criação, edição e exclusão de cartões utilizando arrays de objetos.
- **Persistência:** Uso de `localStorage` para salvar os flashcards no navegador.
- **Interatividade:** Efeito de "Flip" (virar a carta) ativado via JS alterando classes CSS com `classList.toggle`.
- **Feedback:** Botões de "Correto" e "Incorreto" que avançam para o próximo card.

<div align="center">
<img width="359" height="423" alt="Flashcards" src="https://github.com/user-attachments/assets/1acdeb3a-7a51-450f-aad2-5252246c5156" />
</div>

#### 3. Pomodoro Timer

- **Lógica de Tempo:** Implementação de um cronômetro regressivo usando `setInterval`.
- **Modos de Estudo:** Alternância dinâmica entre "Foco", "Pausa Curta" e "Pausa Longa".
- **Tasks:** Lista de tarefas integrada onde o usuário pode adicionar, marcar como concluída ou excluir.

<div align="center">
<img width="663" height="354" alt="Pomodoro" src="https://github.com/user-attachments/assets/f355e8d5-2c56-4463-b843-732ac81c5876" />
</div>

#### 4. Sistema de Resumos

- **CRUD Completo:** O usuário pode criar, ler, editar e deletar resumos.
- **Manipulação do DOM:** Os resumos são renderizados na tela como elementos HTML criados via JavaScript a partir dos dados salvos.

#### 5. Login e Registro

- **Validação:** Verificação dos dados informados pelo usuário antes do envio.
- **Validação de e-mail:** Uso de `filter_var()` para verificar o formato do e-mail.
- **Validação de senha:** Verificação dos requisitos definidos para a senha e confirmação da senha.
- **UX:** Funcionalidade de "Mostrar/Ocultar Senha" alterando o atributo `type` do input entre `password` e `text` dinamicamente.

#### 6. Modo ENEM — Simulado e Era Vargas

O Modo ENEM (`pages/modoENEM.php`) agora é uma página inicial com duas opções: o **Simulado do ENEM** e o tema **Era Vargas** (a página antiga, agora em `pages/eraVargas.php`).

**Simulado do ENEM (`pages/simulado.php`, `js/simulado.js`, `css/simulado.css`, `api/simulado.php`)**

- 533 questões reais das provas do ENEM 2021, 2022 e 2023, escritas em HTML (com as imagens, tabelas e gráficos), não só em PDF. O link para as provas originais em PDF fica no site do INEP.
- Modos de 10, 25, 45, 90 (um dia) e 180 questões (prova toda), ou qualquer número de 1 a 180.
- Escolha dos anos e das áreas, "misturar os anos" ou "tudo do mesmo ano", idioma das questões 1 a 5 (inglês ou espanhol) e ordem da prova ou embaralhada. Cada questão mostra de qual prova é: ano, dia, número da questão e área.
- Gabarito **na hora** (depois de confirmar cada questão) ou **só no final**.
- Tempo: sem limite, por questão (passa sozinho para a próxima) ou total (o simulado termina quando zera). Cronômetro opcional na tela.
- Ferramentas na prova: marcar como **dúvida**, **marca-texto rosa**, riscar alternativa, mapa de questões e atalhos de teclado.
- Resultado por área, revisão de cada questão com gabarito, "caderno de dúvidas" e "caderno de erros" (dá para fazer um simulado só com elas).
- **Imprimir a prova:** prévia na tela e impressão com cartão-resposta e gabarito opcional.
- As escolhas ficam salvas na conta (tabela `preferencias_simulado`) ou no aparelho, para quem não entrou. Se a página for recarregada no meio da prova, dá para continuar.
- Segurança: o gabarito não vai junto com as questões. O servidor corrige e calcula o XP, cada simulado só vale uma vez, e há limite de 120 XP por dia. Os arquivos de `dados/` ficam bloqueados por `.htaccess`.
- Créditos: as questões são das provas oficiais do INEP. Os textos foram organizados a partir do projeto aberto enem.dev (`ferramentas/importar_questoes.py`). Algumas questões do repositório vieram incompletas e foram deixadas de fora.

**Era Vargas (`pages/eraVargas.php`)**

*Módulo interdisciplinar com foco no ENEM, abordando a Revolução de 1930, Governo Provisório e Estado Novo.*

- **Interface Sanfona (JS):** Expansão e recolhimento de painéis de conteúdo histórico de forma exclusiva.
- **Simulado Integrado (PHP):** Quiz dinâmico com questões do ENEM processadas via método `POST` no servidor.
- **Correção e Feedback:** O servidor PHP valida as respostas, calcula a pontuação e estiliza as alternativas de acordo com os acertos e erros.
- **Mídia:** Incorporação de videoaula através de Iframe do YouTube.

---

### Avaliação 02 — Validação de dados e proteção contra injeção

A Avaliação 02 dá continuidade ao mesmo projeto, mantendo o tema e a proposta do Studay. Nesta etapa, foram implementadas validações no servidor, proteção contra HTML/JavaScript Injection e uma nova funcionalidade chamada **Modo Faísca**.

#### Modo Faísca — Nova Funcionalidade

O aluno informa a matéria, o conteúdo, o tempo disponível, a disposição, a maior dificuldade e o objetivo do estudo.

O PHP valida os dados recebidos e monta um **mini cronograma personalizado**. Por exemplo, uma sessão de uma hora em Matemática, com dificuldade em questões, pode ser organizada em aquecimento teórico, resolução de questões e revisão dos erros.

O mascote **Faísca** participa da experiência: ele apresenta falas de acordo com a disposição escolhida e, durante a sessão, muda de estágio conforme o tempo do cronômetro diminui.

#### Onde cada requisito foi aplicado

| Requisito | Onde está |
|---|---|
| `empty()` | Campos obrigatórios de `modoFaisca.php`, `registro.php` e `login.php` |
| `preg_match()` | Conteúdo e observações em `modoFaisca.php`, nome e senha em `registro.php` |
| `filter_var()` | Tempo disponível com `FILTER_VALIDATE_INT` e intervalo de 10 a 240 minutos em `modoFaisca.php`; e-mail com `FILTER_VALIDATE_EMAIL` em `registro.php` e `login.php` |
| `valida_dados()` | `pages/valida_dados.php`, utilizada nas entradas dos três formulários |
| Proteção contra HTML Injection | Os dados passam por `valida_dados()`, que utiliza `htmlspecialchars()` antes da exibição |
| Proteção contra JavaScript Injection | Os dados recebidos pelo usuário são tratados antes da exibição e não são inseridos diretamente em código JavaScript ou atributos de evento |
| Listas fechadas | Valores de `select` e `radio` são conferidos com `array_key_exists()` |
| Extras | Arrays, `foreach`, funções próprias como `monta_cronograma()` e constantes com `define()` |

#### Correções de segurança no JavaScript existente

- **Resumos (`resumos.js`):** títulos e textos que eram inseridos com `innerHTML` passaram a receber tratamento com `escapeHtml()`.
- **Pomodoro (`pomodoro.js`):** o texto das tarefas passou a ser inserido com `textContent`.
- **Música (`navigation.js`):** a URL do YouTube passou a ser conferida, aceitando somente o ID de vídeo esperado e remontando a URL de forma controlada.

#### Formulários processados no servidor

`login.html` e `registro.html` foram convertidos para `login.php` e `registro.php`.

O envio dos dados é realizado por `POST`, e as mensagens de erro são exibidas abaixo dos respectivos campos.

> **Observação (3º bimestre):** nessa etapa o projeto ainda não usava banco de dados. No 4º bimestre o login e o cadastro passaram a usar o banco (veja a seção abaixo).

Os testes com dados válidos, inválidos e tentativas de injeção estão descritos em **`TESTES.md`**.

---

### Requisitos Técnicos Atendidos

#### Lógica Client-Side (JavaScript)

| Requisito | Onde foi aplicado? |
|---|---|
| **Variáveis e Constantes** | Uso de `let` e `const` nos scripts. |
| **Manipulação de Formulários** | Presente em `avaliacoes.js`, `flashcards.js` e `resumos.js`. |
| **Captura de Eventos** | Uso de `addEventListener` para cliques e envios de formulários. |
| **Prevenção de Recarga** | Uso de `event.preventDefault()` nos formulários baseados em JavaScript. |
| **Leitura de Dados e DOM** | Leitura com `.value` e renderização com `.textContent` e outros recursos do DOM. |
| **Bônus JS** | Manipulação de classes e estilos, loops de repetição e uso de `localStorage`. |

#### Lógica Server-Side e Estrutural (PHP)

- **HTML e CSS:** Estrutura das páginas e estilização por meio de folhas de estilo externas.
- **PHP para Variáveis e Saídas Dinâmicas:** Exibição de dados processados no servidor.
- **Processamento de Formulários:** Manipulação de requisições utilizando o método `POST`.
- **Validação:** Uso de `empty()`, `preg_match()` e `filter_var()`.
- **Proteção de Dados:** Uso da função `valida_dados()` com `trim()`, `stripslashes()` e `htmlspecialchars()`.
- **Operadores:** Uso de operadores de atribuição, aritméticos, relacionais e lógicos.
- **Estruturas Condicionais:** Uso de `if`, `elseif` e `else` nos fluxos de validação e processamento.
- **Arrays e Repetição:** Organização de informações e uso de `foreach` para percorrer listas e montar o cronograma.
- **Funções:** Utilização de funções próprias, como `valida_dados()` e `monta_cronograma()`.
- **Comentários:** Código PHP organizado com comentários explicativos.

---

### 4º Bimestre — Plataforma ENEM (banco de dados, portal, XP e ranking)

O Studay é a porta de entrada da Plataforma ENEM da turma. Esta etapa acrescentou, sem mudar o visual:

- **Banco de dados MySQL (extra):** contas reais, sites da turma, XP, conquistas e resultados do quiz. As consultas usam PDO com *prepared statements*.
- **Login e cadastro de verdade:** senha guardada só como hash (`password_hash`), sessão segura, proteção CSRF e limite de tentativas. O cadastro ganhou o campo **apelido** (é o nome que aparece no ranking, nunca o e-mail).
- **Sites da Turma (portal):** cartões com matéria, descrição, "olhar da equipe", selos de **validado por** / **testado por** e **fontes e créditos**. Filtro por matéria e busca. O botão "Abrir site" passa por `pages/ir.php`, que conta as visitas.
- **Área da equipe (`pages/adminSites.php`):** cadastrar, editar, esconder e apagar sites. Só administradores entram (e-mails em `config/config.php`). Assim ninguém consegue apagar o conteúdo, como pede o PDF.
- **XP, níveis e Faísca evoluindo:** Pomodoro, Modo Faísca, simulado, quiz, flashcards, resumos e visitas aos sites dão XP. O servidor confere o tempo dos cronômetros e aplica limites por dia. 6 níveis: Fagulha, Chama Viva, Tocha, Fogueira, Labareda e Estrela.
- **Ranking ao vivo:** semana (zera na segunda) e geral, com pódio. Atualiza sozinho a cada 10 s.
- **Meu progresso:** nível, sequência de dias, gráfico dos últimos 7 dias, conquistas e histórico.
- **Como funciona o XP (`pages/comoFunciona.php`):** página simples que explica quando se ganha XP, quanto vale cada atividade, como funcionam os níveis e as regras de jogo limpo. Sempre que a pessoa ganha XP aparece um aviso animado com o valor, o motivo e a barra do nível enchendo. Cada página de estudo mostra uma dica de XP no topo.
- **Apresentação do Faísca (`js/apresentacao.js`):** passeio curtinho, com o Faísca falando frases bem pequenas e destacando cada área do menu. Abre sozinho na primeira vez que a pessoa cria a conta e pode ser aberto de novo em "Ver apresentação do Faísca" (menu lateral, "Mais" no celular, perfil e Como funciona o XP).
- **Perfil e fotos prontas (`pages/perfil.php`):** troca de nome, apelido e senha, e escolha da foto de perfil entre avatares prontos (Faíscas, memes e animais). Não há envio de arquivos, para não pesar o servidor gratuito.
- **Animações e celular:** entrada suave das páginas, elementos que aparecem ao rolar, efeito de onda nos botões, confete ao subir de nível. Tudo respeita "reduzir movimento".

#### Como colocar no ar (InfinityFree)

1. No painel do InfinityFree, abra **MySQL Databases** e crie um banco.
2. Clique em **phpMyAdmin**, aba **Importar**, escolha `sql/banco.sql` e execute. (Se você já tinha importado uma versão antiga, importe também `sql/atualizacao_perfil.sql` e `sql/atualizacao_simulado.sql`: eles acrescentam o que faltava.)
3. Abra `config/config.php` e preencha host, banco, usuário e senha (o host é parecido com `sql123.infinityfree.com`, não `localhost`).
4. Ainda em `config/config.php`, coloque os e-mails da equipe em `EMAILS_ADMIN`. Quem tiver esse e-mail vira administrador ao entrar.
5. Envie todos os arquivos pela pasta `htdocs`. Coloque as imagens e vídeos novos (veja os arquivos `LEIA-ME.txt` dentro de `videos/faisca/`, `img/faisca/`, `img/avatares/` e `img/sites/`).
6. Cadastre os sites das equipes em **Gerenciar sites** (aparece no menu para administradores).

No XAMPP, use host `localhost`, usuário `root`, senha vazia e importe o mesmo `sql/banco.sql`.

#### Novas pastas

```text
config/     # config.php (dados do banco e e-mails de admin) - bloqueada por .htaccess
includes/   # conexão, sessão, funções, gamificação e menu - bloqueada por .htaccess
sql/        # banco.sql (tabelas) - bloqueada por .htaccess. atualizacao_simulado.sql acrescenta as tabelas do simulado
dados/enem/ # questões do ENEM 2021, 2022 e 2023 em JSON - bloqueada por .htaccess
ferramentas/ # importar_questoes.py (gera dados/enem e img/enem/provas) - bloqueada por .htaccess
img/enem/provas/ # imagens das questões (webp)
api/        # estado.php, xp.php, ranking.php, apresentacao.php, simulado.php (respostas em JSON, usadas pelo JavaScript)
videos/faisca/   # vídeos do Faísca (faisca-apresentacao.mp4, faisca-comemorando.mp4)
img/faisca/      # nivel-1.webp ... nivel-6.webp (Faísca por nível)
img/avatares/    # fotos de perfil prontas, em 3 pastas: faisca, memes e animais
img/sites/       # capas dos sites da turma
fontes/          # (opcional) StarsAndLove-BottomHeavy.ttf (ou .woff2), a fonte do logo, para não depender de site externo
```

---

### Link da Aplicação

**Versão com PHP (Avaliação 02 — InfinityFree):**

`COLOQUE AQUI O ENDEREÇO PÚBLICO DO INFINITYFREE`

O front-end base do projeto continua disponível no GitHub Pages:

https://macedo-hub.github.io/Studay/

> **Observação:** o GitHub Pages é um ambiente estático e não executa PHP. Por isso, os módulos que utilizam PHP, como o Modo ENEM e o Modo Faísca, devem ser executados em um servidor compatível com PHP.

---

### Instruções de Uso e Configuração

#### Uso Geral

1. Acesse o link do GitHub Pages ou abra o arquivo `index.html`.
2. Vá em **Continuar** → **Registre-se** para simular a criação de uma conta e, em seguida, realize o login.
3. No painel principal, escolha a ferramenta desejada, como Calculadora de Média, Pomodoro, Flashcards ou Resumos.

<div align="center">
<img width="663" alt="Studay" src="https://github.com/user-attachments/assets/13f27672-3f5a-43bd-bf10-e50c83f5b35d" />
</div>

#### Configuração dos módulos PHP localmente

Para executar e testar as funcionalidades em PHP localmente:

1. **Preparação do ambiente:** tenha um servidor PHP local ativo, como o XAMPP.
2. **Diretório do projeto:** coloque a pasta do projeto no diretório de hospedagem local, como `htdocs/` no XAMPP.
3. **Configuração do vídeo:** tenha acesso à internet para reproduzir a videoaula do YouTube.
4. **Execução:** inicialize o Apache no painel de controle do servidor local e acesse a página PHP pelo navegador.

Exemplo:

```text
http://localhost/Studay-main/pages/modoENEM.php
```

---

### Estrutura de Arquivos

```text
studay/
│
├── index.html                  # Página Inicial
├── README.md                   # Documentação do Projeto
├── TESTES.md                   # Registro dos testes realizados
│
├── css/
│   ├── style.css               # Estilização Global do Studay
│   ├── modoENEM.css            # Estilização específica do Modo ENEM
│   └── modoFaisca.css          # Estilização do Modo Faísca
│
├── fontes/                     # Fontes utilizadas no projeto
│   ├── Fontspring-DEMO-theseasons-bdit.otf
│   ├── Fontspring-DEMO-theseasons-bd.otf
│   ├── Fontspring-DEMO-theseasons-it.otf
│   ├── Fontspring-DEMO-theseasons-ltit.otf
│   ├── Fontspring-DEMO-theseasons-lt.otf
│   ├── Fontspring-DEMO-theseasons-reg.otf
│   └── SFPRODISPLAYREGULAR.OTF
│
├── img/                        # Imagens, ícones e recursos visuais
│   ├── calculadora.png
│   ├── casa.png
│   ├── comunidade.png
│   ├── configuracoes.png
│   ├── estadonovo.jpg
│   ├── estado_novo.png
│   ├── eye-close.png
│   ├── eye-open.png
│   ├── faica_sem_fundo.png
│   ├── faisca.png
│   ├── flashcards.png
│   ├── governoprovisorio.jpg
│   ├── gov_provisorio.png
│   ├── livro.png
│   ├── medalha.png
│   ├── modoEnem.png
│   ├── musica.png
│   ├── pomodoro.png
│   ├── resumos.png
│   ├── rev1930.png
│   ├── revolucao1930.jpg
│   └── videoaulas.png
│
├── js/                        # Scripts JavaScript
│   ├── avaliacoes.js          # Lógica da Calculadora de Média
│   ├── flashcards.js          # Controle dos Flashcards e LocalStorage
│   ├── login.js               # Mostrar/ocultar senha
│   ├── modoENEM.js            # Controle dos painéis da Era Vargas
│   ├── modoFaisca.js          # Timer da sessão e Faísca
│   ├── pomodoro.js             # Timer Pomodoro e tarefas
│   ├── registro.js             # Mostrar/ocultar senha
│   ├── resumos.js              # CRUD de Resumos
│   └── videoaulas.js           # Busca e redirecionamento para videoaulas
│
├── pages/                     # Páginas e módulos do sistema
│   ├── avaliacoes.html        # Calculadora de Média