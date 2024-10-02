$(function() {
    // Função para navegação suave
    function smoothScroll() {
        $('.nav a[href^="#"]').on('click', function(e) {
            e.preventDefault();
            const targetId = $(this).attr('href');
            const targetOffset = $(targetId).offset().top;
            
            $('html, body').animate({ 
                scrollTop: targetOffset - 50
            }, 500);
        });
    }

    // Função para atualizar a classe ativa nos links de navegação
    function updateActiveNavLink() {
        const scrollPos = $(document).scrollTop();

        $('.nav__link').each(function() {
            const currLink = $(this);
            const refElement = $(currLink.attr('href'));
            const elementTop = refElement.position().top;
            const elementHeight = refElement.height();

            if (elementTop <= scrollPos + 50 && elementTop + elementHeight > scrollPos + 50) {
                $('.nav__link').removeClass('active');
                currLink.addClass('active');
            } else {
                currLink.removeClass('active');
            }
        });
    }

    // Inicializa a navegação suave
    smoothScroll();

    // Atualiza a classe ativa nos links de navegação ao rolar a página
    $(window).on('scroll', updateActiveNavLink);
    
    // Atualiza a classe ativa ao carregar a página
    updateActiveNavLink();
});
