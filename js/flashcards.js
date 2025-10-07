document.addEventListener('DOMContentLoaded', () => {
  // Elementos das abas
  const tabFlashcards = document.getElementById('tab-flashcards');
  const tabGerenciar = document.getElementById('tab-gerenciar');
  const flashcardsContent = document.getElementById('flashcards-content');
  const manageContent = document.getElementById('manage-content');

  // Elementos do Flashcard Display
  const flashcardCard = document.querySelector('.flashcard-card');
  const flashcardQuestionDisplay = document.getElementById('flashcard-question-display');
  const flashcardAnswerDisplay = document.getElementById('flashcard-answer-display');
  const btnFlip = document.getElementById('btn-flip');
  const btnCorrect = document.getElementById('btn-correct');
  const btnIncorrect = document.getElementById('btn-incorrect');
  const btnNext = document.getElementById('btn-next');

  // Elementos do Gerenciamento
  const btnAddNewFlashcard = document.getElementById('btn-add-new-flashcard');
  const flashcardForm = document.getElementById('flashcard-form');
  const inputQuestion = document.getElementById('input-question');
  const inputAnswer = document.getElementById('input-answer');
  const btnSaveFlashcard = document.getElementById('btn-save-flashcard');
  const cancelEditBtn = document.getElementById('cancel-edit');
  const flashcardsList = document.getElementById('flashcards-list');

  // Variáveis de estado
  let flashcards = JSON.parse(localStorage.getItem('flashcards')) || [];
  let currentIndex = 0;
  let isFlipped = false;
  let editingIndex = null; // Para saber se estamos editando ou adicionando

  // --- Funções de Utilidade ---

  function salvarFlashcards() {
    localStorage.setItem('flashcards', JSON.stringify(flashcards));
  }

  function resetFlashcardDisplay() {
    flashcardCard.classList.remove('flipped');
    isFlipped = false;
    btnCorrect.classList.remove('feedback-active');
    btnIncorrect.classList.remove('feedback-active');
    btnNext.classList.add('hidden'); // Esconde o botão "Próximo"
  }

  function updateFlashcardDisplay() {
    resetFlashcardDisplay(); // Reseta o estado visual antes de mostrar o novo

    if (flashcards.length === 0) {
      flashcardQuestionDisplay.textContent = 'Nenhum flashcard disponível. Adicione um na aba "Gerenciar"!';
      flashcardAnswerDisplay.textContent = '';
      btnFlip.classList.add('hidden');
      btnCorrect.classList.add('hidden');
      btnIncorrect.classList.add('hidden');
      return;
    }

    btnFlip.classList.remove('hidden');
    btnCorrect.classList.remove('hidden');
    btnIncorrect.classList.remove('hidden');

    const currentFlashcard = flashcards[currentIndex];
    flashcardQuestionDisplay.textContent = currentFlashcard.question;
    flashcardAnswerDisplay.textContent = currentFlashcard.answer;
  }

  function showNextFlashcard() {
    currentIndex = (currentIndex + 1) % flashcards.length; // Loop infinito
    updateFlashcardDisplay();
  }

  function activateTab(tabName) {
    // Remove active de todas as abas e conteúdos
    tabFlashcards.classList.remove('menu-active');
    tabGerenciar.classList.remove('menu-active');
    flashcardsContent.classList.add('hidden');
    manageContent.classList.add('hidden');

    if (tabName === 'flashcards') {
      tabFlashcards.classList.add('menu-active');
      flashcardsContent.classList.remove('hidden');
      updateFlashcardDisplay(); // Atualiza o flashcard ao entrar na aba
    } else { // 'gerenciar'
      tabGerenciar.classList.add('menu-active');
      manageContent.classList.remove('hidden');
      updateManageList(); // Atualiza a lista de gerenciamento
      hideFlashcardForm(); // Garante que o formulário esteja escondido
    }
  }

  function updateManageList() {
    flashcardsList.innerHTML = ''; // Limpa a lista existente

    if (flashcards.length === 0) {
      const noFlashcardsMsg = document.createElement('p');
      noFlashcardsMsg.textContent = 'Nenhum flashcard adicionado ainda.';
      noFlashcardsMsg.style.color = '#fff';
      noFlashcardsMsg.style.textAlign = 'center';
      noFlashcardsMsg.style.marginTop = '20px';
      flashcardsList.appendChild(noFlashcardsMsg);
      return;
    }

    flashcards.forEach((fc, index) => {
      const cardDiv = document.createElement('div');
      cardDiv.className = 'manage-card';

      const questionP = document.createElement('p');
      questionP.textContent = fc.question;
      cardDiv.appendChild(questionP);

      const actionsDiv = document.createElement('div');
      actionsDiv.className = 'manage-actions';

      const editBtn = document.createElement('button');
      editBtn.innerHTML = '<i class="fa-solid fa-pen"></i>';
      editBtn.className = 'edit-btn';
      editBtn.title = 'Editar';
      editBtn.addEventListener('click', () => editFlashcard(index));
      actionsDiv.appendChild(editBtn);

      const deleteBtn = document.createElement('button');
      deleteBtn.innerHTML = '<i class="fa-solid fa-trash"></i>';
      deleteBtn.className = 'delete-btn';
      deleteBtn.title = 'Apagar';
      deleteBtn.addEventListener('click', () => deleteFlashcard(index));
      actionsDiv.appendChild(deleteBtn);

      cardDiv.appendChild(actionsDiv);
      flashcardsList.appendChild(cardDiv);
    });
  }

  function showFlashcardForm() {
    flashcardForm.classList.remove('hidden');
    btnAddNewFlashcard.classList.add('hidden');
  }

  function hideFlashcardForm() {
    flashcardForm.classList.add('hidden');
    btnAddNewFlashcard.classList.remove('hidden');
    flashcardForm.reset(); // Limpa o formulário
    editingIndex = null; // Reseta o índice de edição
  }

  function editFlashcard(index) {
    editingIndex = index;
    const flashcardToEdit = flashcards[index];
    inputQuestion.value = flashcardToEdit.question;
    inputAnswer.value = flashcardToEdit.answer;
    showFlashcardForm();
  }

  function deleteFlashcard(index) {
    // Removido o confirm() - agora apaga diretamente
    flashcards.splice(index, 1);
    salvarFlashcards();
    updateManageList();
    // Ajusta o currentIndex se o flashcard atual for apagado
    if (currentIndex >= flashcards.length && flashcards.length > 0) {
      currentIndex = flashcards.length - 1;
    } else if (flashcards.length === 0) {
      currentIndex = 0;
    }
    updateFlashcardDisplay(); // Atualiza o display principal
  }

  // --- Event Listeners ---

  // Abas
  tabFlashcards.addEventListener('click', () => activateTab('flashcards'));
  tabGerenciar.addEventListener('click', () => activateTab('gerenciar'));

  // Flashcard Display
  btnFlip.addEventListener('click', () => {
    if (flashcards.length === 0) return;
    flashcardCard.classList.toggle('flipped');
    isFlipped = !isFlipped;
    if (isFlipped) {
      btnNext.classList.remove('hidden'); // Mostra o botão "Próximo" após virar
    } else {
      btnNext.classList.add('hidden'); // Esconde se virar de volta
    }
  });

  btnCorrect.addEventListener('click', () => {
    if (flashcards.length === 0 || !isFlipped) return;
    btnCorrect.classList.add('feedback-active');
    btnIncorrect.classList.remove('feedback-active');
    // Lógica futura para marcar como "correto" (ex: mover para o final da fila, etc.)
    console.log('Flashcard marcado como correto!');
    setTimeout(showNextFlashcard, 500); // Mostra o próximo após um pequeno delay
  });

  btnIncorrect.addEventListener('click', () => {
    if (flashcards.length === 0 || !isFlipped) return;
    btnIncorrect.classList.add('feedback-active');
    btnCorrect.classList.remove('feedback-active');
    // Lógica futura para marcar como "incorreto" (ex: repetir mais tarde)
    console.log('Flashcard marcado como incorreto!');
    setTimeout(showNextFlashcard, 500); // Mostra o próximo após um pequeno delay
  });

  btnNext.addEventListener('click', showNextFlashcard);

  // Gerenciamento
  btnAddNewFlashcard.addEventListener('click', showFlashcardForm);
  cancelEditBtn.addEventListener('click', hideFlashcardForm);

  flashcardForm.addEventListener('submit', (e) => {
    e.preventDefault();
    const question = inputQuestion.value.trim();
    const answer = inputAnswer.value.trim();

    if (!question || !answer) {
      alert('Por favor, preencha tanto a pergunta quanto a resposta.');
      return;
    }

    if (editingIndex !== null) {
      // Editando um flashcard existente
      flashcards[editingIndex] = { question, answer };
    } else {
      // Adicionando um novo flashcard
      flashcards.push({ question, answer });
    }

    salvarFlashcards();
    hideFlashcardForm();
    updateManageList();
    // Se adicionou o primeiro flashcard, atualiza o display principal
    if (flashcards.length === 1 && currentIndex === 0) {
      updateFlashcardDisplay();
    }
  });

  // --- Inicialização ---
  activateTab('flashcards'); // Inicia na aba de flashcards
});
