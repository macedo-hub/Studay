// Seleção dos elementos
const form = document.getElementById("media-form");
const notasContainer = document.getElementById("notas-container");
const btnAdicionar = document.getElementById("add-nota-btn");
const resultadoMedia = document.getElementById("resultado-media");
const resultadoBox = document.getElementById("resultado-box");
const mensagemErro = document.getElementById("mensagem-erro-media");

// Adicionar nova nota
btnAdicionar.addEventListener("click", () => {
  const div = document.createElement("div");
  div.classList.add("nota-row");

  div.innerHTML = `
    <input type="number" class="nota-input" placeholder="Nova nota (0 a 100)" min="0" max="100" step="0.01">
    <button type="button" class="nota-remove-btn" aria-label="Remover nota">
      <svg viewBox="0 0 24 24" aria-hidden="true">
        <polyline points="3 6 5 6 21 6"/>
        <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
        <path d="M10 11v6"/>
        <path d="M14 11v6"/>
        <path d="M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2"/>
      </svg>
    </button>
  `;

  div.querySelector(".nota-remove-btn").addEventListener("click", () => {
    div.remove();
  });

  notasContainer.appendChild(div);
});

// Calcular média
form.addEventListener("submit", (event) => {
  event.preventDefault();

  const inputs = document.querySelectorAll(".nota-input");
  let soma = 0;

  // Limpa mensagens e esconde o resultado anterior
  mensagemErro.style.display = "none";
  resultadoMedia.textContent = "";
  resultadoBox.classList.remove("visible");

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
  resultadoBox.classList.add("visible");
});
