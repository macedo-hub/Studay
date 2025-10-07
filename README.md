# Studay

Studay\! Um site planejado em transformar e facilitar esforço em resultados e tornar os estudos uma forma mais leve e interativa.


-----

### Contexto Acadêmico

Este projeto foi desenvolvido como parte da avaliação do 3º Bimestre da disciplina de Desenvolvimento de Aplicações Web I.

  - **Instituição:** Instituto Federal da Paraíba – Campus Santa Luzia
  - **Curso:** Técnico Integrado em Informática
  - **Disciplina:** Desenvolvimento de Aplicações Web I
  - **Professor:** Felipe Ramos

-----

## Equipe

O Studay é formado por três integrantes:

  - **Lucas Emanuel Nóbrega de Macêdo** - `[nobrega.macedo@academico.ifpb.edu.br]`
  - **Iasmim Anahí Alves Silva** - `[iasmim.anahi@academico.ifpb.edu.br]`
  - **Sabrina Yasmine Araújo da Silva** - `[sabrina.yasmine@academico.ifpb.edu]`

-----

## Sobre o Projeto

Este projeto nasceu da nossa própria experiência como estudantes. A ideia surgiu de uma ideia simples: criar uma ferramenta que realmente ajudasse qualquer pessoa a estudar melhor unindo métodos eficazes com uma interface interativa e fácil de usar. Queríamos algo que fizesse do estudo um hábito prazeroso, que incentivasse a produtividade e criasse uma conexão verdadeira com o aprendizado.

Pra dar vida e personalidade a tudo isso, criamos o **Faísca**, nosso mascote. Inspirado naquela trend de "estudar até a vela apagar".

-----

## Funcionalidades Atuais

No momento, o Studay já conta com quatro métodos de estudo principais totalmente funcionais:

  - **Pomodoro:** Um cronômetro clássico para você gerenciar seus períodos de foco e descanso, com uma lista de tarefas integrada para manter tudo organizado.
  - **Flashcards:** Crie, gerencie e revise seus próprios flashcards. Uma forma poderosa de memorizar conceitos e testar seus conhecimentos.
  - **Resumos:** Um espaço dedicado para você criar e salvar seus resumos, mantendo o material de revisão sempre à mão.
  - **Videoaulas:** Um buscador integrado ao YouTube para você encontrar videoaulas sobre qualquer assunto sem precisar sair do nosso site.

-----

## Mudanças Realizadas (Para a Avaliação)

Para esta entrega, o foco foi expandir o projeto inicial, aplicando os conhecimentos de CSS para criar páginas mais complexas e interativas. As principais mudanças foram:

1.  **Criação de Novas Páginas:** Implementamos as páginas de **Pomodoro, Flashcards, Resumos e Videoaulas**, cada uma com sua lógica e layout específicos.
2.  **Estilização Avançada com CSS:** Todo o estilo foi centralizado no arquivo `css/style.css`. Utilizamos amplamente os conceitos de posicionamento para construir os layouts:
      - `position: fixed` foi usado para criar a barra de navegação lateral (sidebar) que acompanha o usuário em todas as páginas de métodos.
      - `position: absolute` foi crucial para posicionar elementos específicos, como os ícones de apresentação na `index.html` e os ícones de "mostrar/ocultar senha" nos formulários.
      - `z-index` garantiu que elementos flutuantes, como o player de música, aparecessem sobre o conteúdo principal corretamente.
3.  **Layout com Flexbox e Grid:** Organizamos a maioria das seções utilizando Flexbox e Grid Layout para criar uma estrutura responsiva e alinhada.

-----

## Como Usar

1.  **Ponto de Partida:** Abra o arquivo `index.html` para ver a página inicial do projeto.
2.  **Navegação:** Utilize os links "Login" e "Registre-se" para navegar para as respectivas páginas de formulário.
3.  **Acesso aos Métodos:** Após o "login", o usuário seria direcionado para a página `pages/metodos.html`. A partir dela, é possível acessar cada uma das quatro funcionalidades clicando nos botões.
4.  **Interaja:**
      - No **Pomodoro**, inicie o timer, adicione e marque tarefas.
      - Nos **Flashcards**, navegue pelos cards existentes ou vá na aba "Gerenciar" para criar, editar e apagar os seus.
      - Nos **Resumos**, clique no botão "+" para criar um novo resumo.
      - Nas **Videoaulas**, digite um tema na barra de busca para pesquisar no YouTube.

-----

##  Planos Futuros

O Studay ainda tem muito espaço para crescer\! Nossa visão é transformá-lo em uma plataforma de estudos mais colaborativa, quase como uma rede social para estudantes. Algumas ideias para o futuro incluem:

  - Criação de comunidades de estudo.
  - Sistema de gamificação com níveis e medalhas (XP).
  - Compartilhamento de flashcards e resumos entre usuários.