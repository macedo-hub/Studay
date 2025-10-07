document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('search-input');
  const searchIcon = document.querySelector('.search-icon');

  // Função para realizar a busca no YouTube
  function performSearch() {
    const query = searchInput.value.trim();
    if (query) {
      const youtubeUrl = `https://www.youtube.com/results?search_query=${encodeURIComponent(query)}`;
      window.open(youtubeUrl, '_blank'); // Abre em nova aba
      searchInput.value = ''; // Limpa o input após busca
    } else {
      // Opcional: alerta se vazio (mas mantive simples, sem alerta)
      searchInput.placeholder = 'Digite algo para buscar!';
      setTimeout(() => {
        searchInput.placeholder = 'Digite o tema da videoaula (ex: HTML para iniciantes)...';
      }, 2000);
    }
  }

  // Event listener para Enter no input
  searchInput.addEventListener('keypress', function(event) {
    if (event.key === 'Enter') {
      performSearch();
    }
  });

  // Event listener para clique na lupa
  searchIcon.addEventListener('click', performSearch);
});