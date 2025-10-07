// Função para toggle de visibilidade da senha
function togglePasswordVisibility(inputId, toggleId) {
  const input = document.getElementById(inputId);
  const toggle = document.getElementById(toggleId);
  const isPassword = input.type === "password";

  input.type = isPassword ? "text" : "password";
  toggle.src = isPassword ? "../img/eye-open.png" : "../img/eye-close.png";
}

// Event listeners para os ícones do olho
document.getElementById("toggleSenha1").addEventListener("click", () => {
  togglePasswordVisibility("senha", "toggleSenha1");
});

document.getElementById("toggleSenha2").addEventListener("click", () => {
  togglePasswordVisibility("confirmarSenha", "toggleSenha2");
});

// Validação no submit
document.getElementById("registroForm").addEventListener("submit", function(e) {
  e.preventDefault();
  
  const senha = document.getElementById("senha").value;
  const confirmarSenha = document.getElementById("confirmarSenha").value;
  const avisoSenha = document.getElementById("avisoSenha");
  
  if (senha !== confirmarSenha) {
    avisoSenha.style.display = "block";
    return; // Impede o submit
  }
  
  // Se senhas coincidem, redireciona diretamente para metodos.html sem notificação
  window.location.href = "metodos.html";
});
