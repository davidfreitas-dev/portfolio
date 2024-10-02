$(function() {
    emailjs.init('j5lfz4mIUUaOnUcfZ');

    const form = $('#form-contact');
    const sendButton = $('#btn-send');

    form.on('submit', handleFormSubmit);

    function handleFormSubmit(event) {
        event.preventDefault();
        const formData = new FormData(this);
        const { name, email, message } = Object.fromEntries(formData.entries());

        if (!name || !email || !message) {
            showToastMessage('Oops! Preencha todos os campos.', 'error');
            return;
        }

        if (!validateEmail(email)) {
            showToastMessage('Informe um e-mail válido.', 'error');
            return;
        }

        sendEmail({ name, email, message });
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
    

    async function sendEmail({ name, email, message }) {
        sendButton.val('Enviando...');

        const templateParams = { name, email, message };

        try {
            await emailjs.send('service_m0ovszh', 'template_ltj7azl', templateParams);
            showToastMessage('Obrigado por entrar em contato conosco! Recebemos sua mensagem e responderemos o mais breve possível no e-mail que você forneceu.', 'success');
            form[0].reset();
        } catch (error) {
            showToastMessage(`Erro: ${error.text || error}`, 'error');
        }

        sendButton.val('Enviar Mensagem');
    }
});
