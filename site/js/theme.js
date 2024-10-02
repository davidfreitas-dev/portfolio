$(function() {
  const themeKey = 'theme';
  const themeToggleBtn = $('.btn__toggle__theme');

  // Inicialização do tema
  initializeTheme();

  // Inicialização do tema com base no armazenamento local ou esquema de cores do sistema
  function initializeTheme() {
    const storedTheme = localStorage.getItem(themeKey);

    if (storedTheme) {
      applyTheme(storedTheme);
    } else {
      applySystemColorScheme();
    }
  }

  // Aplica o esquema de cores do sistema
  function applySystemColorScheme() {
    const prefersDarkScheme = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    const systemTheme = prefersDarkScheme ? 'dark' : 'light';
    applyTheme(systemTheme);
  }

  // Aplica o tema e atualiza o ícone do botão
  function applyTheme(theme) {
    $('html').attr('data-theme', theme);
    updateButtonIcon(theme);
    localStorage.setItem(themeKey, theme);
  }

  // Atualiza o ícone do botão com base no tema
  function updateButtonIcon(theme) {
    const btnIcon = $('.btn__toggle__theme span');
    btnIcon.toggleClass('la-moon', theme === 'light');
    btnIcon.toggleClass('la-sun', theme === 'dark');
  }

  // Alterna entre os temas claro e escuro
  function toggleTheme() {
    const currentTheme = $('html').attr('data-theme');
    const newTheme = currentTheme === 'light' ? 'dark' : 'light';
    applyTheme(newTheme);
  }

  // Evento de clique no botão de alternância de tema
  themeToggleBtn.on('click', toggleTheme);

  // Descomente a linha abaixo se desejar aplicar o tema automaticamente quando o esquema de cores do sistema mudar
  // window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', applySystemColorScheme);
});
