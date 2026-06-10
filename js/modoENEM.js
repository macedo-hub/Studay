let painelAberto = null;

/**
 * Abre ou fecha um painel.
 * @param {string} id
 */

function togglePanel(id) {
  const painel = document.getElementById('panel-' + id);
  const card   = document.getElementById('card-' + id);

  // Se o painel clicado já está aberto, fechar
  if (painelAberto === id) {
    fecharPainel(id);
    painelAberto = null;
    return;
  }

  // Se havia outro painel aberto, fechar o anterior primeiro
  if (painelAberto !== null) {
    fecharPainel(painelAberto);
  }

  // Abrir o novo painel
  painel.classList.add('aberto');
  card.classList.add('ativo');
  painelAberto = id;

  setTimeout(function () {
    painel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }, 80);
}

/**
 * fecharPainel 
 * @param {string} id 
 */
function fecharPainel(id) {
  var painel = document.getElementById('panel-' + id);
  var card   = document.getElementById('card-' + id);

  if (painel) painel.classList.remove('aberto');
  if (card)   card.classList.remove('ativo');
}
