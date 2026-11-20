document.addEventListener('DOMContentLoaded', function() {
  const searchInput = document.getElementById('search-input');
  const searchIcon = document.querySelector('.search-icon');

  // Função para realizar a busca no YouTube
  function performSearch() {
    const query = searchInput.value.trim();
    if (query) {
      const youtubeUrl = `https://www.youtube.com/results?search_query=${encodeURIComponent(query)}`;
      window.open(youtubeUrl, '_blank'); 
      searchInput.value = ''; 
    } else {
      searchInput.placeholder = 'Digite algo para buscar!';
      setTimeout(() => {
        searchInput.placeholder = 'Digite o tema da videoaula (ex: HTML para iniciantes)...';
      }, 2000);
    }
  }

  searchInput.addEventListener('keypress', function(event) {
    if (event.key === 'Enter') {
      performSearch();
    }
  });

  searchIcon.addEventListener('click', performSearch);
});