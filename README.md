<div align="center">
<img width="500" alt="Image" src="https://github.com/user-attachments/assets/ca6ea08f-1601-4876-b837-7f3f6f9c24e3" /></div>

**Studay!** Um site planejado para transformar esforço em resultados, tornando os estudos uma atividade mais leve, interativa e eficiente.

---

### Contexto Acadêmico

Este projeto foi desenvolvido como parte da avaliação do **4º Bimestre** (Avaliação 02 - Projeto em Equipe) da disciplina de Desenvolvimento de Aplicações Web I.

- **Instituição:** Instituto Federal da Paraíba – Campus Santa Luzia
- **Curso:** Técnico Integrado em Informática
- **Disciplina:** Desenvolvimento de Aplicações Web I
- **Professor:** Felipe Ramos
- **Bimestre:** 4º Bimestre - Avaliação 02

---

### Equipe

A equipe responsável pelo desenvolvimento e implementação das funcionalidades JavaScript é formada por:

- **Lucas Emanuel Nóbrega de Macêdo** - `[nobrega.macedo@academico.ifpb.edu.br]`
- **Iasmim Anahí Alves Silva** - `[iasmim.anahi@academico.ifpb.edu.br]`
- **Sabrina Yasmine Araújo da Silva** - `[sabrina.yasmine@academico.ifpb.edu]`

---

### Sobre a Atualização (Foco em JavaScript)

Nesta etapa do projeto, o foco foi dar vida à interface criada anteriormente. Utilizamos **JavaScript** para tornar a página totalmente funcional e interativa.

O objetivo principal foi implementar a lógica de programação para **captura de formulários, processamento de dados e manipulação do DOM**, garantindo que o usuário possa interagir com as ferramentas de estudo em tempo real.

---

### Funcionalidades JavaScript Implementadas

O projeto agora conta com lógica JavaScript robusta em todas as páginas. Abaixo, detalhamos as implementações:

#### 1. Calculadora de Média (Novo) 
*Atende ao requisito de processamento de formulários e lógica matemática.*
- **Funcionamento:** O usuário insere notas em campos gerados dinamicamente.
- **Lógica JS:**
  - Uso de `document.createElement` para adicionar novos campos de nota infinitamente.
  - Captura do evento `submit` com `event.preventDefault()`.
  - Loop (`for...of`) para percorrer todos os inputs e somar os valores.
  - Cálculo da média aritmética e exibição dinâmica do resultado (`.textContent`).
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
- **UX:** Funcionalidade de "Mostrar/Ocultar Senha" alterando o atributo `type` do input entre `password` e `text` e trocando o ícone do olho dinamicamente.

---

### Requisitos Técnicos Atendidos

O código foi estruturado para atender a todos os critérios da avaliação:

| Requisito | Onde foi aplicado? |
| :--- | :--- |
| **Variáveis e Constantes** | Uso amplo de `let` (para contadores, arrays de dados) e `const` (para seleção de elementos DOM). |
| **Manipulação de Formulários** | Presente em `avaliacoes.js` (Média), `flashcards.js` e `resumos.js`. |
| **Captura de Eventos** | Uso de `form.addEventListener("submit", ...)` e botões com `click`. |
| **Prevenção de Recarga** | Uso de `event.preventDefault()` em todos os formulários. |
| **Leitura de Dados (.value)** | Captura de valores dos inputs de texto e numéricos. |
| **Conversão de Tipos** | Uso de `parseFloat()` na calculadora de média. |
| **Manipulação do DOM** | Exibição de resultados via `.textContent` e `.innerHTML`. |

#### Bônus Implementados
- **Validação de Formulário:** Checagem de campos vazios e intervalos de valores (0 a 100).
- **Manipulação de Estilos CSS via JS:** Alteração de visibilidade (`display: block/none`) e classes para animações (flip do card).
- **Arrays e Estruturas de Repetição:** Uso de loops para calcular a média das notas e renderizar as listas de flashcards/resumos.

---

### Link da Aplicação

O projeto está hospedado e funcional no GitHub Pages. Acesse através do link abaixo:

**[https://macedo-hub.github.io/studay/]**

---

### Instruções de Uso

Siga os passos abaixo para testar as funcionalidades do sistema:

1. **Acesso Inicial:** Abra o link do GitHub Pages (acima) ou o arquivo `index.html`.
2. **Login/Registro:**
   - Clique em "Continuar" e depois em "Registre-se".
   - Crie uma conta (a validação de senhas iguais está ativa).
   - Faça login (simulação de acesso).
3. **Menu de Métodos:** Após o login, você verá o painel principal. Escolha uma ferramenta. 
<div align="center">
  <img width="663" alt="Image" src="https://github.com/user-attachments/assets/13f27672-3f5a-43bd-bf10-e50c83f5b35d" />
</div>

4. **Testando a Nova Funcionalidade (Calcular média):**
   - Clique no ícone de calculadora (canto inferior direito) ou acesse via URL `pages/avaliacoes.html`.
   - Adicione quantas notas desejar clicando no botão `+`.
   - Clique em "Calcular Média" para ver o resultado instantâneo.
5. **Outras Ferramentas:**
   - **Pomodoro:** Inicie o timer e adicione tarefas na lista lateral.
   - **Flashcards:** Vá na aba "Gerenciar" para criar cartas, e depois volte para "Flashcards" para jogar.
   - **Resumos:** Clique no `+` para escrever e salvar um resumo.
   - **Vídeoaulas:** Digite a vídeoaula e seja redirecionado para o YouTube.


---

### Estrutura de Arquivos

```bash
studay/
│
├── index.html          # Página Inicial
├── README.md           # Documentação
│
├── css/
│   └── style.css       # Todo o CSS
│
├── js/                 # JavaScript
│   ├── avaliacoes.js   
│   ├── flashcards.js   
│   ├── pomodoro.js     
│   ├── resumos.js      
│   ├── videoaulas.js   
│   ├── login.js        
│   └── registro.js     
│
├── img/                # Imagens e Ícones
│
└── pages/              # Páginas HTML do sistema
    ├── login.html
    ├── registro.html
    ├── metodos.html
    ├── pomodoro.html
    ├── flashcards.html
    ├── resumos.html
    ├── videoaulas.html
    └── avaliacoes.html 




