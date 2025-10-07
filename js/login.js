const form = document.getElementById("loginForm");
form.addEventListener("submit", function(e) {
    e.preventDefault(); // Impede envio real do formulário
    // Aqui você poderia validar o login, se necessário
    window.location.href = "metodos.html"; // Redireciona para metodos.html
});
