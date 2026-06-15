$(function() {
    // Função para navegação suave
    function smoothScroll() {
        $('.nav a[href^="#"], .header__logo-link[href^="#"]').on('click', function(e) {
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
            
            if (refElement.length) {
                const elementTop = refElement.position().top;
                const elementHeight = refElement.height();

                if (elementTop <= scrollPos + 50 && elementTop + elementHeight > scrollPos + 50) {
                    $('.nav__link').removeClass('active');
                    currLink.addClass('active');
                } else {
                    currLink.removeClass('active');
                }
            }
        });
    }

    // Função para gerenciar o cabeçalho transparente/sólido
    function handleHeaderScroll() {
        const scrollPos = $(document).scrollTop();
        const header = $('.header');
        
        if (scrollPos > 50) {
            header.addClass('header--scrolled');
        } else {
            header.removeClass('header--scrolled');
        }
    }

    // Inicializa as funções
    smoothScroll();

    // Eventos de rolagem
    $(window).on('scroll', function() {
        updateActiveNavLink();
        handleHeaderScroll();
    });
    
    // Executa no carregamento inicial
    updateActiveNavLink();
    handleHeaderScroll();
});
