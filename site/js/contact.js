import { api } from './api.js';

$(function() {
    const form = $('#form-contact');
    const sendButton = $('#btn-send');

    // Usar form.on('submit') garante que capturamos o evento de submissão
    form.on('submit', function(event) {
        event.preventDefault(); // Evita o recarregamento da página imediatamente
        handleFormSubmit.call(this, event);
    });

    function handleFormSubmit(event) {
        const formData = new FormData(this);
        const dataEntries = Object.fromEntries(formData.entries());
        const { name, email, message, website } = dataEntries;

        // Honeypot check: se o campo 'website' estiver preenchido, ignoramos (bot detetado)
        if (website) {
            console.warn('Bot detectado via honeypot.');
            return;
        }

        if (!name || !email || !message) {
            showToastMessage('Oops! Preencha todos os campos obrigatórios.', 'error');
            return;
        }

        if (!validateEmail(email)) {
            showToastMessage('Informe um e-mail válido.', 'error');
            return;
        }

        const data = { 
            name, 
            email, 
            subject: 'Contato via Portfólio', 
            message 
        };

        sendContactRequest(data);
    }

    function validateEmail(email) {
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailPattern.test(email);
    }

    function showToastMessage(message, status) {
      const toast = $('#toast');
    
      toast.text(message).addClass('show').addClass(status);
    
      setTimeout(() => {
          toast.removeClass('show').removeClass(status).text('');
      }, 5000);
    }
    

    async function sendContactRequest(data) {
        const originalButtonText = sendButton.val();
        sendButton.val('Enviando...');
        sendButton.prop('disabled', true);

        try {
            await api.sendContactRequest(data);
            showToastMessage('Obrigado por entrar em contato conosco! Recebemos sua mensagem e responderemos o mais breve possível.', 'success');
            form[0].reset();
        } catch (error) {
            console.error('Erro ao enviar contato:', error);
            const errorMessage = error.message || 'Ocorreu um erro ao enviar sua mensagem.';
            showToastMessage(`Erro: ${errorMessage}`, 'error');
        } finally {
            sendButton.val(originalButtonText);
            sendButton.prop('disabled', false);
        }
    }
});
