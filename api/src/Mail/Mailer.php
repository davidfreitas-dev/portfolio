<?php 

namespace App\Mail;

use App\Enums\HttpStatus as HTTPStatus;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../vendor/autoload.php';

class Mailer {

	private $mail;

	public function __construct($toAddress, $toName, $subject, $data = array())
	{
		
		$this->mail = new PHPMailer(true);

		//Enable SMTP debugging
		// 0 = off (for production use)
		// 1 = client messages
		// 2 = client and server messages
		$this->mail->SMTPDebug = 0;

		//Tell PHPMailer to use SMTP
		$this->mail->isSMTP();		

		//Ask for HTML-friendly debug output
		// $this->mail->Debugoutput = 'html';

		//Set the hostname of the mail server
		$this->mail->Host = 'smtp.gmail.com';
		// use
		// $this->mail->Host = gethostbyname('smtp.gmail.com');
		// if your network does not support SMTP over IPv6

		//Whether to use SMTP authentication
		$this->mail->SMTPAuth = true;

		//Username to use for SMTP authentication - use full email address for gmail
		$this->mail->Username = $_ENV['MAILER_USERNAME'];

		//Password to use for SMTP authentication
		$this->mail->Password = $_ENV['MAILER_PASSWORD'];

		//Set the encryption system to use - ssl (deprecated) or tls
		$this->mail->SMTPSecure = 'tls';

		//Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
		$this->mail->Port = 587;

		$this->mail->SMTPOptions = array(
			'ssl' => array(
				'verify_peer' => false,
				'verify_peer_name' => false,
				'allow_self_signed' => true
			)
		);

		//Set who the message is to be sent from
		$this->mail->setFrom($_ENV['MAILER_USERNAME'], $_ENV['MAILER_NAME_FROM']);

		//Set an alternative reply-to address
		//$this->mail->addReplyTo('replyto@example.com', 'First Last');

		//Set who the message is to be sent to
		$this->mail->addAddress($toAddress, $toName);

		//Read an HTML message body from an external file, convert referenced images to embedded,
		//convert HTML into a basic plain-text alternative body
		$this->mail->isHTML(true);

		$this->mail->CharSet = 'UTF-8';

		//Set the subject line
		$this->mail->Subject = $subject;
		//Replace the plain text body with one created manually
		$this->mail->Body = '
    <html>
    <head>
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #ffffff;
                color: #3c3c3c;
                padding: 20px;
            }
            .container {
                max-width: 600px;
                margin: 0 auto;
                padding: 20px;
                background-color: #ffffff;
                border: 1px solid #dddddd;
                border-radius: 8px;
            }
            .header {
                text-align: center;
                padding-bottom: 20px;
            }
            .header h1 {
                color: #01c38d;
                margin: 0;
            }
            .content {
                font-size: 16px;
                line-height: 1.6;
            }
            .button {
                display: inline-block;
                padding: 10px 20px;
                margin-top: 20px;
                background-color: #01c38d;
                color: #ffffff;
                text-decoration: none;
                border-radius: 5px;
                font-weight: bold;
            }
            .footer {
                text-align: center;
                margin-top: 30px;
                font-size: 12px;
                color: #999999;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>Recuperação de Senha</h1>
            </div>
            <div class="content">
                <p>Olá ' . $data['name'] . ',</p>
                <p>Recebemos uma solicitação para redefinir a sua senha. Clique no botão abaixo para prosseguir.</p>
                <a href="' . $data['link'] . '" class="button">Redefinir Senha</a>
                <p>Se você não solicitou esta alteração, por favor, ignore este e-mail.</p>
            </div>
            <div class="footer">
                <p>© ' . date('Y') . ' Sua Empresa. Todos os direitos reservados.</p>
            </div>
        </div>
    </body>
    </html>';

    $this->mail->AltBody = 'Olá ' . $data['name'] . ',\nRecebemos uma solicitação para redefinir a sua senha. Use o link abaixo para redefini-la:\n' . $data['link'];

		//Attach an image file
		//$mail->addAttachment('images/phpmailer_mini.png');

	}

	public function send()
	{

		try {
			
			return $this->mail->send();

		} catch (Exception $e) {
    		
			throw new Exception("Não foi possível enviar o e-mail. Erro do Mailer: {$this->mail->ErrorInfo}", HTTPStatus::INTERNAL_SERVER_ERROR);
      
		}

	}

}

 ?>