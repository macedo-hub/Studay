<div align="center">
<img width="500" alt="Image" src="https://github.com/user-attachments/assets/ca6ea08f-1601-4876-b837-7f3f6f9c24e3" />
</div>

**Studay!** Um site planejado para transformar esforço em resultados, tornando os estudos uma atividade mais leve, interativa e eficiente.

---

### Contexto Acadêmico

Este projeto foi desenvolvido como parte da avaliação da disciplina de Desenvolvimento de Aplicações Web II.

- **Instituição:** Instituto Federal da Paraíba – Campus Santa Luzia
- **Curso:** Técnico Integrado em Informática
- **Disciplina:** Desenvolvimento de Aplicações Web II
- **Professor:** Felipe Ramos
- **Bimestre:** 1º Bimestre - Avaliação 01 (Projeto em Equipe) e atualizações subsequentes.

---

### Equipe

A equipe responsável pelo desenvolvimento e implementação das funcionalidades do sistema é formada por:

- **Lucas Emanuel Nóbrega de Macêdo** - `[nobrega.macedo@academico.ifpb.edu.br]`
- **Iasmim Anahí Alves Silva** - `[iasmim.anahi@academico.ifpb.edu.br]`
- **Sabrina Yasmine Araújo da Silva** - `[sabrina.yasmine@academico.ifpb.edu]`

---

### Sobre as Atualizações (Foco em JavaScript e PHP)

O projeto evoluiu de uma interface estática para uma aplicação web dinâmica e interativa. 
1. **Etapa Client-Side (JavaScript):** Implementação da lógica de programação para captura de formulários, persistência local de dados e manipulação do DOM em tempo real.
2. **Etapa Server-Side (PHP - Modo ENEM):** Introdução de processamento no servidor para criação de um ambiente dinâmico de simulados, unindo o desenvolvimento web ao tema de História (**Era Vargas**).

---

### Funcionalidades Implementadas

O projeto agora conta com lógica robusta tanto no front-end quanto no back-end. Abaixo, detalhamos as implementações de cada ferramenta:

#### 1. Calculadora de Média 
*Atende ao requisito de processamento de formulários e lógica matemática.*
- **Funcionamento:** O usuário insere notas em campos gerados dinamicamente.
- **Lógica JS:** Uso de `document.createElement` para adicionar novos campos de nota; captura do evento `submit` com `event.preventDefault()`; loop (`for...of`) para somar os valores; exibição dinâmica do resultado (`.textContent`).
- **Validação:** O sistema alerta se algum campo estiver vazio ou se a nota não estiver entre 0 e 100.
<div align="center">
  <img width="319" height="350" alt="Image" src="https://github.com/user-attachments/assets/54dd4290-020d-44f9-a020-7a74c6821e0a" />
</div>

#### 2. Flashcards Interativos 
- **Gerenciamento:** Criação, edição e exclusão de cartões utilizando Arrays de objetos.
- **Persistência:** Uso de `localStorage` para salvar os flashcards no navegador.
- **Interatividade:** Efeito de "Flip" (virar a carta) ativado via JS alterando classes CSS (`classList.toggle`).
- **Feedback:** Botões de "Correto" e "Incorreto" que avançam para o próximo card.
<div align="center">
<img width="359" height="423" alt="Image" src="https://github.com/user-attachments/assets/1acdeb3a-7a51-450f-aad2-5252246c5156" />
</div>

#### 3. Pomodoro Timer 
- **Lógica de Tempo:** Implementação de um cronômetro regressivo usando `setInterval`.
- **Modos de Estudo:** Alternância dinâmica entre "Foco", "Pausa Curta" e "Pausa Longa".
- **Tasks:** Lista de tarefas integrada onde o usuário pode adicionar, marcar como concluída (riscando o texto via JS/CSS) ou excluir.
<div align="center">
  <img width="663" height="354" alt="Image" src="https://github.com/user-attachments/assets/f355e8d5-2c56-4463-b843-732ac81c5876" />
</div>

#### 4. Sistema de Resumos 
- **CRUD Completo:** O usuário pode criar, ler, editar e deletar resumos.
- **Manipulação do DOM:** Os resumos são renderizados na tela como elementos HTML criados via JavaScript a partir dos dados salvos.

#### 5. Login e Registro 
- **Validação de Senha:** Verificação se a "Senha" e "Confirmar Senha" coincidem antes de permitir o envio.
- **UX:** Funcionalidade de "Mostrar/Ocultar Senha" alterando o atributo `type` do input entre `password` e `text` dinamicamente.

#### 6. Modo ENEM — Era Vargas (Novo) 
*Módulo interdisciplinar com foco no ENEM abordando a Revolução de 1930, Governo Provisório e Estado Novo.*
- **Interface Sanfona (JS):** Expansão e recolhimento de painéis de conteúdo histórico de forma exclusiva (apenas um painel aberto por vez).
- **Simulado Integrado (PHP):** Quiz dinâmico com 5 questões do ENEM processadas via método `POST` no servidor.
- **Correção e Feedback:** O servidor PHP valida as respostas, calcula a pontuação utilizando operadores lógicos e aritméticos e estiliza as alternativas (roxo para correta, laranja para errada) dinamicamente com base nos acertos e erros.
- **Mídia:** Incorporação de videoaula através de Iframe do YouTube.

---

### Requisitos Técnicos Atendidos

#### Lógica Client-Side (JavaScript)
| Requisito | Onde foi aplicado? |
| :--- | :--- |
| **Variáveis e Constantes** | Uso amplo de `let` e `const` em todos os scripts. |
| **Manipulação de Formulários** | Presente em `avaliacoes.js`, `flashcards.js` e `resumos.js`. |
| **Captura de Eventos** | Uso de `addEventListener` para interceptar cliques e envios de formulários. |
| **Prevenção de Recarga** | Uso de `event.preventDefault()` nos formulários baseados em JS. |
| **Leitura de Dados & DOM** | Leitura com `.value` e renderização com `.textContent` / `.innerHTML`. |
| **Bônus JS** | Manipulação de estilos CSS via JS (`display`, classes de animação), loops de repetição e `localStorage`. |

#### Lógica Server-Side & Estrutural (PHP e Módulo ENEM)
- **HTML e CSS:** Estrutura expandida e estilização dedicada com folha de estilo externa (`modoENEM.css`).
- **PHP para Variáveis e Saídas Dinâmicas:** Exibição de dados processados no servidor.
- **Processamento de Formulário:** Manipulação de requisições utilizando o método `POST`.
- **Operadores Diversos:** Atribuição (`=`, `+=`), aritméticos (`*`, `/`), relacionais (`===`, `>=`, `<=`) e lógicos (`&&`, `||`).
- **Estruturas Condicionais:** Fluxos de validação de respostas usando `if`, `elseif` e `else`.
- **Arrays e Repetição:** Organização das alternativas de questões e renderização dinâmica na tela através do laço `foreach`.
- **Comentários:** Código fonte do PHP devidamente documentado com comentários explicativos.

---

### Link da Aplicação

O front-end base do projeto está hospedado e funcional no GitHub Pages:
👉 **[https://macedo-hub.github.io/Studay/]**

*Nota: Por se tratar de um ambiente estático, o módulo **Modo ENEM (PHP)** requer a execução em um servidor local (conforme instruções abaixo).*

---

### Instruções de Uso e Configuração

#### Uso Geral (Interface Front-End)
1. Acesse o link do GitHub Pages ou abra o arquivo `index.html`.
2. Vá em **Continuar** -> **Registre-se** para simular a criação de uma conta e realize o login.
3. No painel principal, escolha a ferramenta desejada (Calculadora de Média, Pomodoro, Flashcards ou Resumos).

<div align="center">
  <img width="663" alt="Image" src="https://github.com/user-attachments/assets/13f27672-3f5a-43bd-bf10-e50c83f5b35d" />
</div>

#### Configuração do Módulo Modo ENEM (PHP Local)

Para rodar e testar as novas funcionalidades em PHP:

1. **Preparação do Ambiente:** Certifique-se de possuir um servidor PHP local ativo (Ex: XAMPP).
2. **Diretório do Projeto:** Mova a pasta do projeto para o diretório de hospedagem local (ex: `htdocs/` no XAMPP).
3. **Configuração do Vídeo (YouTube):**
   - Tenha acesso a internet para poder acessar o vídeo que está presente no YouTube
4. **Execução:** Inicialize o Apache no seu painel de controle do servidor local e acesse no navegador: `http://localhost/Studay-main/pages/modoENEM.php`.

---

### Estrutura de Arquivos

```bash
studay/
│
├── index.html          # Página Inicial
├── README.md           # Documentação do Projeto
│
├── css/
│   ├── style.css       # Estilização Global do Studay
│   └── modoENEM.css    # Estilização específica do Modo ENEM
│
├── fontes/             # Fontes utilizadas no projeto
│   ├── Fontspring-DEMO-theseasons-bdit.otf
│   ├── Fontspring-DEMO-theseasons-bd.otf
│   ├── Fontspring-DEMO-theseasons-it.otf
│   ├── Fontspring-DEMO-theseasons-ltit.otf
│   ├── Fontspring-DEMO-theseasons-lt.otf
│   ├── Fontspring-DEMO-theseasons-reg.otf
│   └── SFPRODISPLAYREGULAR.OTF
│
├── img/                # Imagens, ícones e recursos visuais
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
├── js/                 # Scripts JavaScript
│   ├── avaliacoes.js   # Lógica da Calculadora de Média
│   ├── flashcards.js   # Controle dos Flashcards e LocalStorage
│   ├── login.js        # Validações de acesso
│   ├── modoENEM.js     # Controle dos painéis interativos da Era Vargas
│   ├── pomodoro.js     # Timer Pomodoro e gerenciamento de tarefas
│   ├── registro.js     # Validações de cadastro
│   ├── resumos.js      # CRUD de Resumos
│   └── videoaulas.js   # Busca e redirecionamento para vídeoaulas
│
├── pages/              # Páginas e módulos do sistema
│   ├── avaliacoes.html # Calculadora de Média
│   ├── flashcards.html # Sistema de Flashcards
│   ├── login.html      # Tela de Login
│   ├── metodos.html    # Hub principal das ferramentas de estudo
│   ├── modoENEM.php    # Módulo ENEM: Era Vargas (HTML + PHP)
│   ├── pomodoro.html   # Timer Pomodoro
│   ├── registro.html   # Tela de Cadastro
│   ├── resumos.html    # Sistema de Resumos
│   └── videoaulas.html # Pesquisa de Vídeoaulas