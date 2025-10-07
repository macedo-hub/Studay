document.addEventListener('DOMContentLoaded', function() {
  const addBtn = document.getElementById('add-resumo-btn');
  const form = document.getElementById('resumo-form');
  const cancelBtn = document.getElementById('cancel-btn');
  const confirmBtn = document.getElementById('confirm-btn');
  const resumosList = document.getElementById('resumos-list');
  const titleInput = document.getElementById('resumo-title');
  const textInput = document.getElementById('resumo-text');

  let resumos = JSON.parse(localStorage.getItem('resumos')) || []; // Carrega do localStorage
  let editingId = null; // Para modo editar

  // Função para renderizar a lista de resumos
  function renderResumos() {
    resumosList.innerHTML = '';
    if (resumos.length === 0) {
      resumosList.innerHTML = '<p style="text-align: center; color: rgba(255,255,255,0.7); font-style: italic;">Nenhum resumo adicionado ainda. Clique em + para adicionar!</p>';
      return;
    }
    resumos.forEach(resumo => {
      const item = document.createElement('div');
      item.className = 'resumo-item';
      item.innerHTML = `
        <div style="flex: 1;">
          <div class="resumo-title">${resumo.title}</div>
          <div class="resumo-text">${resumo.text}</div>
        </div>
        <div class="resumo-actions">
          <button class="edit-resumo-btn check-btn" data-id="${resumo.id}"><i class="fa-solid fa-pen"></i></button> <!-- Icon fa-pen da flashcards -->
          <button class="delete-resumo-btn delete-btn" data-id="${resumo.id}"><i class="fa-solid fa-trash"></i></button> <!-- Icon fa-trash da flashcards -->
        </div>
      `;
      resumosList.appendChild(item);
    });

    // Event listeners para editar/excluir (delegados após render - evita duplicatas)
    document.querySelectorAll('.edit-resumo-btn').forEach(btn => {
      btn.addEventListener('click', editResumo);
    });
    document.querySelectorAll('.delete-resumo-btn').forEach(btn => {
      btn.addEventListener('click', deleteResumo);
    });
  }

  // Mostrar form (limpo para add, ou pré-preenchido para edit)
  function showForm(id = null) {
    editingId = id;
    form.classList.remove('hidden');
    addBtn.style.display = 'none'; // Esconde o + enquanto form aberto
    if (id) {
      // Modo editar: pré-preenche
      const resumo = resumos.find(r => r.id === id);
      titleInput.value = resumo.title;
      textInput.value = resumo.text;
      confirmBtn.textContent = 'Atualizar';
    } else {
      // Modo add: limpa
      titleInput.value = '';
      textInput.value = '';
      confirmBtn.textContent = 'Confirmar';
    }
    titleInput.focus();
  }

  // Esconder form
  function hideForm() {
    form.classList.add('hidden');
    addBtn.style.display = 'flex';
    editingId = null;
    form.reset(); // Limpa o form
  }

  // Adicionar ou atualizar resumo
  function saveResumo(e) {
    e.preventDefault();
    const title = titleInput.value.trim();
    const text = textInput.value.trim();
    if (!title || !text) {
      alert('Por favor, preencha o título e o texto!'); // Validação simples
      return;
    }

    if (editingId) {
      // Atualiza
      const index = resumos.findIndex(r => r.id === editingId);
      resumos[index] = { id: editingId, title, text };
    } else {
      // Adiciona novo
      const newId = Date.now(); // ID único via timestamp
      resumos.unshift({ id: newId, title, text }); // Adiciona no topo da lista
    }

    localStorage.setItem('resumos', JSON.stringify(resumos)); // Salva no localStorage
    hideForm();
    renderResumos();
  }

  // Editar resumo
  function editResumo(e) {
    const id = parseInt(e.target.closest('button').dataset.id); // Pega do botão pai se clicou no icon
    showForm(id);
  }

  // Excluir resumo (SEM CONFIRM - apaga direto, como pedido)
  function deleteResumo(e) {
    const id = parseInt(e.target.closest('button').dataset.id); // Pega do botão pai se clicou no icon
    resumos = resumos.filter(r => r.id !== id);
    localStorage.setItem('resumos', JSON.stringify(resumos));
    renderResumos();
  }

  // Event listeners principais
  addBtn.addEventListener('click', () => showForm()); // Clique no + para adicionar

  form.addEventListener('submit', saveResumo); // Submit do form (confirmar)

  cancelBtn.addEventListener('click', hideForm); // Cancelar fecha o form

  // Renderiza a lista inicialmente ao carregar a página
  renderResumos();
});