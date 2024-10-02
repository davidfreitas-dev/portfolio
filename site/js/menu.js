$(function() {
  const nav = $('.nav');
  const menuLinks = $('.nav__list a');
  const menuToggle = $('.menu__toggle');

  function toggleMenu(event) {
      if (event.type === 'touchstart') event.preventDefault();

      nav.toggleClass('active');
      menuToggle.toggleClass('active');

      const active = nav.hasClass('active');
      $(this).attr('aria-expanded', active);

      if ($(this).prop('tagName') === 'A') {
          nav.removeClass('active');
          menuToggle.removeClass('active');
          menuLinks.removeClass('active');
          $(this).addClass('active');
      }

      const ariaLabel = active ? 'Fechar menu' : 'Abrir menu';
      $(this).attr('aria-label', ariaLabel);
  }

  menuToggle.on('click touchstart', toggleMenu);
  menuLinks.on('click', toggleMenu);
});
