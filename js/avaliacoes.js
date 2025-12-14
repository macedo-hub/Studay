// Seleção dos elementos
const form = document.getElementById("media-form");
const notasContainer = document.getElementById("notas-container");
const btnAdicionar = document.getElementById("add-nota-btn");
const resultadoMedia = document.getElementById("resultado-media");
const mensagemErro = document.getElementById("mensagem-erro-media");

// Adicionar nova nota
btnAdicionar.addEventListener("click", () => {
  const div = document.createElement("div");
  div.classList.add("task-input-container");

  div.innerHTML = `
    <input type="number" class="nota-input" placeholder="Nova nota (0 a 100)">
    <button type="button" class="delete-btn">✖</button>
  `;

  div.querySelector(".delete-btn").addEventListener("click", () => {
    div.remove();
  });

  notasContainer.appendChild(div);
});

// Calcular média
form.addEventListener("submit", (event) => {
  event.preventDefault();

  const inputs = document.querySelectorAll(".nota-input");
  let soma = 0;

  // Limpa mensagens
  mensagemErro.style.display = "none";
  resultadoMedia.textContent = "";

  for (let input of inputs) {
    if (input.value === "") {
      mensagemErro.textContent = "Você deixou um campo de nota em branco.";
      mensagemErro.style.display = "block";
      return;
    }

    const nota = parseFloat(input.value);

    if (nota < 0 || nota > 100) {
      mensagemErro.textContent = "As notas devem estar entre 0 a 100.";
      mensagemErro.style.display = "block";
      return;
    }

    soma += nota;
  }

  const media = soma / inputs.length;
  resultadoMedia.textContent = `Média final: ${media.toFixed(2)}`;
});
