document.addEventListener('DOMContentLoaded', function() {
  const addBtn = document.getElementById('add-resumo-btn');
  const form = document.getElementById('resumo-form');
  const cancelBtn = document.getElementById('cancel-btn');
  const confirmBtn = document.getElementById('confirm-btn');
  const resumosList = document.getElementById('resumos-list');
  const titleInput = document.getElementById('resumo-title');
  const textInput = document.getElementById('resumo-text');

  let resumos = JSON.parse(localStorage.getItem('resumos')) || []; 
  let editingId = null; // Para modo editar

  // Proteção contra HTML/JavaScript Injection: converte < > & " ' em texto seguro
  // antes de montar o HTML do card (título e texto vêm do usuário e do localStorage).
  function escapeHtml(valor) {
    return String(valor)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }


  function renderResumos() {
    resumosList.innerHTML = '';
    if (resumos.length === 0) {
      resumosList.innerHTML = `
        <div class="resumos-empty">
          <svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
            <line x1="16" y1="13" x2="8" y2="13"/>
            <line x1="16" y1="17" x2="8" y2="17"/>
          </svg>
          <p>Nenhum resumo adicionado ainda. Clique em + para adicionar!</p>
        </div>
      `;
      return;
    }
    resumos.forEach(resumo => {
      const item = document.createElement('div');
      item.className = 'resumo-card';
      item.innerHTML = `
        <div class="resumo-card-header">
          <span class="resumo-card-title">${escapeHtml(resumo.title)}</span>
          <div class="resumo-card-actions">
            <button class="edit-resumo-btn resumo-edit-btn" data-id="${escapeHtml(resumo.id)}" title="Editar" aria-label="Editar resumo">
              <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/><path d="m15 5 4 4"/></svg>
            </button>
            <button class="delete-resumo-btn resumo-delete-btn" data-id="${escapeHtml(resumo.id)}" title="Apagar" aria-label="Apagar resumo">
              <svg viewBox="0 0 24 24" aria-hidden="true"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
            </button>
          </div>
        </div>
        <p class="resumo-card-text">${escapeHtml(resumo.text)}</p>
      `;
      resumosList.appendChild(item);
    });

    document.querySelectorAll('.edit-resumo-btn').forEach(btn => {
      btn.addEventListener('click', editResumo);
    });
    document.querySelectorAll('.delete-resumo-btn').forEach(btn => {
      btn.addEventListener('click', deleteResumo);
    });
  }

  function showForm(id = null) {
    editingId = id;
    form.classList.remove('hidden');
    addBtn.style.display = 'none'; 
    if (id) {
      const resumo = resumos.find(r => r.id === id);
      titleInput.value = resumo.title;
      textInput.value = resumo.text;
      confirmBtn.textContent = 'Atualizar';
    } else {
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
    form.reset(); 
  }

  function saveResumo(e) {
    e.preventDefault();
    const title = titleInput.value.trim();
    const text = textInput.value.trim();
    if (!title || !text) {
      alert('Por favor, preencha o título e o texto!'); 
      return;
    }

    if (editingId) {
      // Atualiza
      const index = resumos.findIndex(r => r.id === editingId);
      resumos[index] = { id: editingId, title, text };
    } else {
      // Adiciona novo
      const newId = Date.now(); 
      resumos.unshift({ id: newId, title, text }); 
      // Gamificação: resumo novo rende XP (se a pessoa estiver logada)
      if (window.Studay) { window.Studay.registrarXp('resumo_criado'); }
    }

    localStorage.setItem('resumos', JSON.stringify(resumos)); 
    hideForm();
    renderResumos();
  }

  // Editar resumo
  function editResumo(e) {
    const id = parseInt(e.target.closest('button').dataset.id); 
    showForm(id);
  }

  function deleteResumo(e) {
    const id = parseInt(e.target.closest('button').dataset.id);
    resumos = resumos.filter(r => r.id !== id);
    localStorage.setItem('resumos', JSON.stringify(resumos));
    renderResumos();
  }

  addBtn.addEventListener('click', () => showForm()); // Clique no + para adicionar

  form.addEventListener('submit', saveResumo); // confirmar

  cancelBtn.addEventListener('click', hideForm); 

  renderResumos();
});