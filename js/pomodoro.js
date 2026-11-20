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
    let currentMode = 'focus'; 
  
    // Atualizar display do timer
    function updateDisplay() {
      const minutes = Math.floor(timeLeft / 60);
      const seconds = timeLeft % 60;
      if (!isEditing) {
        timerDisplay.textContent = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
      }
    }
  
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
  
    timerDisplay.addEventListener('click', function() {
      if (isRunning || isEditing) return;
      isEditing = true;
      timerDisplay.contentEditable = true;
      timerDisplay.focus();
      timerDisplay.textContent = Math.floor(originalTime / 60).toString(); 
    });
  
    timerDisplay.addEventListener('keydown', function(e) {
      if (e.key === 'Enter') {
        e.preventDefault();
        const minutes = parseInt(timerDisplay.textContent) || 25;
        if (minutes > 0 && minutes <= 60) { 
          timeLeft = minutes * 60;
          originalTime = timeLeft;
        }
        isEditing = false;
        timerDisplay.contentEditable = false;
        updateDisplay();
      }
    });
  
    timerDisplay.addEventListener('blur', function() {
      if (isEditing) {
        isEditing = false;
        timerDisplay.contentEditable = false;
        updateDisplay();
      }
    });
  
    startBtn.addEventListener('click', function() {
      if (isEditing) return; 
      isRunning = true;
      startBtn.style.display = 'none';
      pauseBtn.style.display = 'inline-block';
      resetBtn.style.display = 'inline-block';
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
          playAlarm(); // Só som, sem alert
        }
      }, 1000);
      updateDisplay();
    });
  
    pauseBtn.addEventListener('click', function() {
      if (isRunning) {
        // Pausar
        clearInterval(timerInterval);
        isRunning = false;
        pauseBtn.textContent = 'Retomar';
      } else {
        // Retomar
        startBtn.style.display = 'none'; 
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
            playAlarm(); 
          }
        }, 1000);
        isRunning = true;
      }
    });
  
    resetBtn.addEventListener('click', function() {
      clearInterval(timerInterval);
      isRunning = false;
      timeLeft = originalTime;
      startBtn.style.display = 'inline-block';
      pauseBtn.style.display = 'none';
      resetBtn.style.display = 'none';
      pauseBtn.textContent = 'Pausar'; 
      updateDisplay();
    });
  
    function playAlarm() {
      try {
        const audioContext = new (window.AudioContext || window.webkitAudioContext)();
        const oscillator = audioContext.createOscillator();
        const gainNode = audioContext.createGain();
        oscillator.connect(gainNode);
        gainNode.connect(audioContext.destination);
        oscillator.frequency.value = 800; 
        oscillator.type = 'sine';
        gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
        gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 1);
        oscillator.start(audioContext.currentTime);
        oscillator.stop(audioContext.currentTime + 1);
      } catch (e) {
        console.log('Alarme tocado (som não suportado no browser)');
      }
    }
  
    const taskInput = document.getElementById('task-input');
    const taskList = document.getElementById('task-list');
    const addTaskBtn = document.getElementById('add-task-btn');
  
    function addTask(text) {
      if (!text.trim()) return;
      const li = document.createElement('li');
      li.className = 'task-item';
      li.innerHTML = `
        <button class="check-btn" onclick="toggleComplete(this)">✓</button>
        <span class="task-text"></span>
        <button class="delete-btn" onclick="deleteTask(this)">X</button>
      `;
      // textContent escreve o texto da tarefa como TEXTO (não interpreta HTML/JavaScript)
      li.querySelector('.task-text').textContent = text;
      taskList.appendChild(li);
      taskInput.value = ''; 
    }
  
    taskInput.addEventListener('keypress', function(e) {
      if (e.key === 'Enter') {
        addTask(taskInput.value);
      }
    });
  
    addTaskBtn.addEventListener('click', function() {
      addTask(taskInput.value);
      taskInput.focus();
    });
  
    window.deleteTask = function(btn) {
      btn.parentElement.remove();
    };
  
    window.toggleComplete = function(btn) {
      const taskItem = btn.parentElement;
      const taskText = taskItem.querySelector('.task-text');
      const deleteBtn = taskItem.querySelector('.delete-btn'); 
      
      taskItem.classList.toggle('completed');
      btn.classList.toggle('completed');
      
      if (taskItem.classList.contains('completed')) {
        taskText.style.textDecoration = 'line-through';
        taskText.style.color = '#ccc'; 
      } else {
        taskText.style.textDecoration = 'none';
        taskText.style.color = '#fff'; 
      }
      
    };
  
    updateDisplay();
  });
  