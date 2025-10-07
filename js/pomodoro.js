document.addEventListener('DOMContentLoaded', function() {
    // Elementos do Timer
    const timerDisplay = document.getElementById('timer-display');
    const startBtn = document.getElementById('start-btn');
    const pauseBtn = document.getElementById('pause-btn');
    const resetBtn = document.getElementById('reset-btn');
    const modeBtns = document.querySelectorAll('.mode-btn');
    
    let timerInterval;
    let timeLeft = 25 * 60; // Padrão 25 min em segundos
    let isRunning = false;
    let isEditing = false;
    let originalTime = 25 * 60; // Tempo original para reset
    let currentMode = 'focus'; // Rastreia o modo ativo
  
    // Atualizar display do timer
    function updateDisplay() {
      const minutes = Math.floor(timeLeft / 60);
      const seconds = timeLeft % 60;
      if (!isEditing) {
        timerDisplay.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
      }
    }
  
    // Botões de modo (Focus, Short Break, Long Break)
    modeBtns.forEach(btn => {
      btn.addEventListener('click', function() {
        if (isRunning) return; // Não permite mudar modo enquanto roda
        const minutes = parseInt(this.dataset.minutes);
        timeLeft = minutes * 60;
        originalTime = timeLeft;
        currentMode = this.textContent.toLowerCase().replace(' ', '-');
        
        // Remove destaque de todos e adiciona ao clicado
        modeBtns.forEach(b => b.classList.remove('active-mode'));
        this.classList.add('active-mode');
        
        updateDisplay();
      });
    });
  
    // Editar display: clique para tornar editável (só minutos)
    timerDisplay.addEventListener('click', function() {
      if (isRunning || isEditing) return;
      isEditing = true;
      timerDisplay.contentEditable = true;
      timerDisplay.focus();
      timerDisplay.textContent = Math.floor(originalTime / 60).toString(); // Mostra só minutos para edição
    });
  
    // Confirmar edição com Enter
    timerDisplay.addEventListener('keydown', function(e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        const minutes = parseInt(timerDisplay.textContent) || 25;
        if (minutes > 0 && minutes <= 60) { // Limite razoável
          timeLeft = minutes * 60;
          originalTime = timeLeft;
        }
        isEditing = false;
        timerDisplay.contentEditable = false;
        updateDisplay();
      }
    });
  
    // Cancelar edição ao perder foco (blur)
    timerDisplay.addEventListener('blur', function() {
      if (isEditing) {
        isEditing = false;
        timerDisplay.contentEditable = false;
        updateDisplay();
      }
    });
  
    // Iniciar timer
    startBtn.addEventListener('click', function() {
      if (isEditing) return; // Não inicia se editando
      isRunning = true;
      startBtn.style.display = 'none';
      pauseBtn.style.display = 'inline-block';
      resetBtn.style.display = 'inline-block';
      pauseBtn.textContent = 'Pausar'; // Garante texto inicial
      
      timerInterval = setInterval(() => {
        timeLeft--;
        updateDisplay();
        if (timeLeft <= 0) {
          clearInterval(timerInterval);
          isRunning = false;
          startBtn.style.display = 'inline-block';
          pauseBtn.style.display = 'none';
          resetBtn.style.display = 'none';
          playAlarm(); // Só som, sem alert
        }
      }, 1000);
      updateDisplay();
    });
  
    // Pausar/Retomar
    pauseBtn.addEventListener('click', function() {
      if (isRunning) {
        // Pausar
        clearInterval(timerInterval);
        isRunning = false;
        pauseBtn.textContent = 'Retomar';
      } else {
        // Retomar
        startBtn.style.display = 'none'; // Mantém Iniciar escondido
        pauseBtn.textContent = 'Pausar';
        timerInterval = setInterval(() => {
          timeLeft--;
          updateDisplay();
          if (timeLeft <= 0) {
            clearInterval(timerInterval);
            isRunning = false;
            startBtn.style.display = 'inline-block';
            pauseBtn.style.display = 'none';
            resetBtn.style.display = 'none';
            playAlarm(); // Só som
          }
        }, 1000);
        isRunning = true;
      }
    });
  
    // Reset: volta ao tempo original do modo
    resetBtn.addEventListener('click', function() {
      clearInterval(timerInterval);
      isRunning = false;
      timeLeft = originalTime;
      startBtn.style.display = 'inline-block';
      pauseBtn.style.display = 'none';
      resetBtn.style.display = 'none';
      pauseBtn.textContent = 'Pausar'; // Reset texto
      updateDisplay();
    });
  
    // Som de alarme (beep simples via AudioContext, sem notificação)
    function playAlarm() {
      try {
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioContext.createOscillator();
        const gainNode = audioContext.createGain();
        oscillator.connect(gainNode);
        gainNode.connect(audioContext.destination);
        oscillator.frequency.value = 800; // Tom do beep
        oscillator.type = 'sine';
        gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
        gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 1);
        oscillator.start(audioContext.currentTime);
        oscillator.stop(audioContext.currentTime + 1);
      } catch (e) {
        // Fallback silencioso se não suportar (sem alert)
        console.log('Alarme tocado (som não suportado no browser)');
      }
    }
  
    // Elementos das Tarefas
    const taskInput = document.getElementById('task-input');
    const taskList = document.getElementById('task-list');
    const addTaskBtn = document.getElementById('add-task-btn');
  
    // Adicionar tarefa via Enter ou botão +
    function addTask(text) {
      if (!text.trim()) return;
      const li = document.createElement('li');
      li.className = 'task-item';
      li.innerHTML = `
        <button class="check-btn" onclick="toggleComplete(this)">✓</button>
        <span class="task-text">${text}</span>
        <button class="delete-btn" onclick="deleteTask(this)">X</button>
      `;
      taskList.appendChild(li);
      taskInput.value = ''; // Limpa input
    }
  
    // Evento para Enter no input (placeholder some ao digitar)
    taskInput.addEventListener('keypress', function(e) {
      if (e.key === 'Enter') {
        addTask(taskInput.value);
      }
    });
  
    // Evento para botão +
    addTaskBtn.addEventListener('click', function() {
      addTask(taskInput.value);
      taskInput.focus(); // Foca no input após adicionar
    });
  
    // Função para deletar tarefa (X)
    window.deleteTask = function(btn) {
      btn.parentElement.remove();
    };
  
    // Função para completar tarefa (check: verde, risco no texto, cinza)
    window.toggleComplete = function(btn) {
      const taskItem = btn.parentElement;
      const taskText = taskItem.querySelector('.task-text');
      const deleteBtn = taskItem.querySelector('.delete-btn'); // X não muda
      
      taskItem.classList.toggle('completed');
      btn.classList.toggle('completed');
      
      if (taskItem.classList.contains('completed')) {
        taskText.style.textDecoration = 'line-through';
        taskText.style.color = '#ccc'; // Cinza só no texto
      } else {
        taskText.style.textDecoration = 'none';
        taskText.style.color = '#fff'; // Branco normal
      }
      
      // X permanece vermelho, sem mudança
    };
  
    // Inicializar display
    updateDisplay();
  });
  