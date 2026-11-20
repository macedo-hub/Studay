# Testes — Avaliação 02 (Web II)

Roteiro para testar os formulários e tirar os prints da entrega.
A coluna **Resultado esperado** foi conferida em teste local. **https://studay.site.je/**

Como testar: abra a página, preencha o campo indicado e envie.
Os formulários usam `novalidate`, então quem valida é o PHP (e não o navegador).

---

## 1. Modo Faísca (`pages/modoFaisca.php`)

Valores base (válidos): Matéria = Matemática · Conteúdo = `Funções do 1º grau` · Tempo = `60` · Disposição = Normal · Dificuldade = Resolver questões · Objetivo = Prova.

| # | Tipo | O que digitar | Resultado esperado |
|---|---|---|---|
| 1 | Válido | Valores base | Cronograma de 3 blocos (12 + 30 + 18 = 60 min) |
| 2 | Válido | Valores base, Disposição = Cansado, Dificuldade = Entender o conteúdo | Cronograma com bloco de **Pausa** de 5 min |
| 3 | Válido | Tempo = `10` e depois `240` | Aceitos (limites do intervalo) |
| 4 | Vazio (`empty`) | Enviar o formulário sem preencher nada | Mensagem de erro em cada campo obrigatório |
| 5 | Inválido (`filter_var`) | Tempo = `abc` | "Digite um número inteiro de 10 a 240 minutos." |
| 6 | Inválido (`filter_var`) | Tempo = `5`, `999`, `-5`, `60.5` ou `1e2` | Mesma mensagem acima |
| 7 | Inválido (`empty`) | Tempo = `0` | "Informe quantos minutos você tem disponíveis." |
| 8 | Inválido (`preg_match`) | Conteúdo = `ab` (muito curto) | Erro de formato do conteúdo |
| 9 | **HTML Injection** | Conteúdo = `<h1>teste</h1>` | Recusado pelo `preg_match`; o campo mostra o texto, sem virar título |
| 10 | **JS Injection** | Conteúdo = `<script>alert(1)</script>` | Recusado; **nenhum alerta aparece**; texto exibido como texto |
| 11 | **JS Injection** | Observações = `<img src=x onerror=alert(1)>` | Aceito, mas aparece como texto na tela; **nenhum alerta** |
| 12 | **JS Injection** | Observações = `<script>alert('xss')</script>` | Aparece como texto; **nenhum alerta** |
| 13 | Quebra de atributo | Conteúdo = `"><script>alert(1)</script>` | Recusado; o campo não é "quebrado" |
| 14 | Quebra de atributo | Tempo = `60" onfocus="alert(1)` | "Digite um número inteiro..." |
| 15 | Texto de link | Conteúdo = `javascript:alert(1)` | Tratado como texto comum, sem executar nada |
| 16 | Observações longas | Mais de 300 caracteres | "Escreva no máximo 300 caracteres." |

### Teste com valor forjado (opcional, para mostrar a lista fechada)
Abra as ferramentas do navegador (F12), edite o `value` de uma opção do `select` para `hack` e envie.
Resultado esperado: "Matéria inválida. Escolha uma das opções da lista."

---

## 2. Registro (`pages/registro.php`)

| # | Tipo | O que digitar | Resultado esperado |
|---|---|---|---|
| 1 | Válido | Nome `Lucas Emanuel` · E-mail `lucas@email.com` · Senha `Senha1234` · Confirmar `Senha1234` | Vai para a tela de Métodos |
| 2 | Vazio (`empty`) | Enviar sem preencher | Erro em todos os campos |
| 3 | `filter_var` | E-mail = `lucas@@email` ou `nao-e-email` | "Digite um e-mail válido..." |
| 4 | `preg_match` | Senha = `abcdefgh` (sem número) | "A senha deve ter de 8 a 64 caracteres, com pelo menos uma letra e um número." |
| 5 | `preg_match` | Nome = `<b>Lucas</b>` | "Use de 3 a 60 caracteres: apenas letras..." |
| 6 | Confirmação | Senhas diferentes | "As senhas não coincidem." |
| 7 | **JS Injection** | Nome = `"><script>alert(1)</script>` | Recusado; o campo exibe o texto, **sem alerta** |
| 8 | **HTML Injection** | E-mail = `<img src=x onerror=alert(1)>` | Recusado; exibido como texto, **sem alerta** |

---

## 3. Login (`pages/login.php`)

| # | Tipo | O que digitar | Resultado esperado |
|---|---|---|---|
| 1 | Válido | E-mail `lucas@email.com` · Senha qualquer | Vai para a tela de Métodos |
| 2 | Vazio (`empty`) | Enviar sem preencher | "Informe seu e-mail." e "Informe sua senha." |
| 3 | `filter_var` | E-mail = `nao-e-email` | "Digite um e-mail válido..." |
| 4 | **JS Injection** | E-mail = `"><svg onload=alert(1)>` | Recusado; **sem alerta** |

---

## 4. Correções no JavaScript (Resumos, Pomodoro e Música)

| # | Onde | O que digitar | Resultado esperado |
|---|---|---|---|
| 1 | Resumos | Título = `<img src=x onerror=alert(1)>`; texto = `<b>teste</b>` | Aparece como texto no card; **sem alerta**; recarregue a página e continua seguro |
| 2 | Pomodoro (tarefas) | Tarefa = `<img src=x onerror=alert(1)>` | Aparece como texto na lista; **sem alerta** |
| 3 | Música | URL = `javascript:alert(1)//youtube.com/embed/` | Recusada como link inválido |
| 4 | Música | URL = `https://www.youtube.com/watch?v=dQw4w9WgXcQ` | Funciona normalmente |

---

## 5. Prints para a entrega

- [ ] Página inicial e Modo Faísca funcionando no endereço público (print com a URL visível)
- [ ] Teste válido do Modo Faísca (cronograma gerado)
- [ ] Teste com campos vazios
- [ ] Teste com tempo inválido
- [ ] Tentativa de HTML Injection
- [ ] Tentativa de JavaScript Injection (formulário e observações)
- [ ] Testes de Registro e Login (válido e inválido)
- [ ] Teste de Resumos e Pomodoro com código injetado
