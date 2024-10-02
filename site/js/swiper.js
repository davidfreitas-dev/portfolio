const swiper = new Swiper('.swiper', {
  slidesPerView: 3, // Exibir 3 slides por vez
  spaceBetween: 30, // Espaço entre os slides
  loop: true, // Habilitar o loop para repetir os slides
  pagination: {
    el: '.swiper-pagination',
    clickable: true,
  },
  navigation: {
    nextEl: '.swiper-button-next',
    prevEl: '.swiper-button-prev',
  },
  autoplay: {
    delay: 3000,
    disableOnInteraction: false,
  },
  breakpoints: {
    768: { // Tela acima de 768px de largura
      slidesPerView: 3,
      spaceBetween: 30,
    },
    576: { // Tela entre 576px e 767px de largura
      slidesPerView: 2,
      spaceBetween: 20,
    },
    0: { // Tela abaixo de 576px de largura
      slidesPerView: 1,
      spaceBetween: 10,
    }
  }
});
