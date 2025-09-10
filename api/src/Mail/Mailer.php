<?php 

namespace App\Mail;

use App\Enums\HttpStatus as HTTPStatus;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../vendor/autoload.php';

class Mailer {

	private $mail;

  public function __construct()
  {
    
    $this->mail = new PHPMailer(true);
    
    $this->mail->isSMTP();
    
    $this->mail->SMTPDebug = 0;
    
    $this->mail->Host = 'smtp.gmail.com';
    
    $this->mail->Port = 587;
    
    $this->mail->SMTPAuth = true;
    
    $this->mail->Username = $_ENV['MAILER_USERNAME'];
    
    $this->mail->Password = $_ENV['MAILER_PASSWORD'];
    
    $this->mail->SMTPSecure = 'tls';
    
    $this->mail->SMTPOptions = [      
      'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
        'allow_self_signed' => true
      ]    
    ];
    
    $this->mail->setFrom($_ENV['MAILER_USERNAME'], 'Loja Exemplo');
    
    $this->mail->isHTML(true);
    
    $this->mail->CharSet = 'UTF-8';
  
  }

	public function send($toAddress, $toName, $subject, $body)
  {    

    try {
      
      $this->mail->clearAddresses();
      
      $this->mail->addAddress($toAddress, $toName);
      
      $this->mail->Subject = $subject;
      
      $this->mail->Body = $body;
      
      return $this->mail->send();
    
    } catch (Exception $e) {
      
      throw new Exception("O e-mail não pôde ser enviado: {$this->mail->ErrorInfo}", HTTPStatus::SERVICE_UNAVAILABLE);
    
    }
  
  }

  public function sendEmail($toAddress, $toName, $subject, $content)
  {
      
    $body = '<html>
              <head>
                <style>
                  body { font-family: Arial, sans-serif; background-color: #ffffff; color: #3c3c3c; padding: 20px; }
                  .container { max-width: 600px; margin: 0 auto; padding: 20px; background-color: #ffffff; border: 1px solid #dddddd; border-radius: 8px; }
                  .logo { text-align: center; margin-bottom: 20px; }
                  .logo img { max-width: 150px; }
                  .header { text-align: center; padding-bottom: 20px; }
                  .header h1 { color: #038de7; margin: 0; }
                  .content { font-size: 16px; line-height: 1.6; }
                  .footer { text-align: center; margin-top: 30px; font-size: 12px; color: #999999; }
                </style>
              </head>
              <body>
                <div class="container">
                  <div class="logo">
                    <img src="' . $_ENV['SITE_URL'] . '/img/logo.png" alt="Logo Loja Exemplo">
                  </div>
                  <div class="header">
                    <h1>' . $subject . '</h1>
                  </div>
                  <div class="content">' . $content . '</div>
                  <div class="footer">
                    <p>© ' . date('Y') . ' Loja Exemplo. Todos os direitos reservados.</p>
                  </div>
                </div>
              </body>
            </html>';
              
    return $this->send($toAddress, $toName, $subject, $body);

  }

}

 ?>