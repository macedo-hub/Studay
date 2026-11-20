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
- **Versão atual:** 3º Bimestre - Avaliação 02 (validação de dados e proteção contra HTML/JavaScript Injection).

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

#### 6. Modo ENEM — Era Vargas

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

A mascote **Faísca** participa da experiência: ela apresenta falas de acordo com a disposição escolhida e, durante a sessão, muda de estágio conforme o tempo do cronômetro diminui.

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

> **Observação:** o projeto não utiliza banco de dados. O login e o cadastro apenas validam os dados e seguem para a tela de métodos, mantendo o comportamento original do projeto. A senha não é armazenada nem exibida novamente.

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

### Link da Aplicação

**Versão com PHP (Avaliação 02 — InfinityFree):**

`COLOQUE AQUI O ENDEREÇO PÚBLICO DO INFINITYFREE`

O front-end base do projeto continua disponível no GitHub Pages:

👉 https://macedo-hub.github.io/Studay/

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