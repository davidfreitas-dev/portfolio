<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($subject) ?></title>
  <style>
    /* Reset styles */
    body, table, td, a { 
      -webkit-text-size-adjust: 100%; 
      -ms-text-size-adjust: 100%; 
    }
    img { 
      -ms-interpolation-mode: bicubic; 
      border: 0; 
      height: auto; 
      line-height: 100%; 
      outline: none; 
      text-decoration: none; 
    }
    table { 
      border-collapse: collapse !important; 
    }
    body { 
      height: 100% !important; 
      margin: 0 !important; 
      padding: 0 !important; 
      width: 100% !important; 
      background-color: #f4f7f6; 
      font-family: 'Poppins', Helvetica, Arial, sans-serif; 
    }

    /* Custom styles */
    .preheader { 
      display: none; 
      max-width: 0; 
      max-height: 0; 
      overflow: hidden; 
    }
    .container { 
      width: 100%; 
      max-width: 600px; 
      margin: 0 auto; 
    }
    .header { 
      padding: 40px 0; 
      text-align: center; 
      background: linear-gradient(135deg, #01c38d 0%, #2af598 100%); 
      border-radius: 8px 8px 0 0; 
    }
    .content { 
      background-color: #ffffff; 
      padding: 40px 30px; 
      border-radius: 0 0 8px 8px; 
      box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05); 
    }
    .footer { 
      padding: 30px 20px; 
      text-align: center; 
      color: #888888; 
      font-size: 13px; 
      line-height: 1.5; 
    }
    .logo-text { 
      font-size: 28px; 
      font-weight: 700; 
      color: #ffffff; 
      text-decoration: none; 
    }
    .logo-text span { 
      font-size: 16px; 
      font-weight: 400; 
      opacity: 0.85; 
    }
    h1 { 
      color: #2d3748; 
      font-size: 24px; 
      font-weight: 700; 
      margin-top: 0; 
      margin-bottom: 20px; 
      text-align: left; 
    }
    p { 
      color: #4a5568; 
      font-size: 16px; 
      line-height: 1.6; 
      margin-bottom: 20px; 
      text-align: left; 
    }
    .divider { 
      border-top: 1px solid #e2e8f0; 
      margin: 30px 0; 
    }
    .social-links a { 
      color: #01c38d; 
      text-decoration: none; 
      margin: 0 10px; 
      font-weight: 600; 
    }
    
    @media screen and (max-width: 600px) {
      .content { 
        padding: 30px 20px !important; 
      }
    }
  </style>
</head>
<body>
  <div class="preheader">
    <?= htmlspecialchars($subject) ?> - David Freitas portfólio.
  </div>
  <table border="0" cellpadding="0" cellspacing="0" width="100%">
    <tr>
      <td align="center" style="padding: 20px 0;">
        <table class="container" border="0" cellpadding="0" cellspacing="0">
          <!-- Header -->
          <tr>
            <td class="header">
              <a href="<?= $_ENV['SITE_URL'] ?? '#' ?>" class="logo-text">Dave<span> dev</span></a>
            </td>
          </tr>
          <!-- Content -->
          <tr>
            <td class="content">
              <h1><?= htmlspecialchars($subject) ?></h1>
              <?= $contentHtml ?>
              
              <div class="divider"></div>
              
              <p style="font-size: 14px; color: #718096; margin-bottom: 0;">
                Atenciosamente,<br>
                <strong>Equipe David Freitas</strong>
              </p>
            </td>
          </tr>
          <!-- Footer -->
          <tr>
            <td class="footer">
              <div class="social-links" style="margin-bottom: 15px;">
                <a href="https://linkedin.com/in/davidfreitas-dev/">LinkedIn</a>
                <a href="https://github.com/davidfreitas-dev">GitHub</a>
                <a href="https://instagram.com/davidfreitas.dev/">Instagram</a>
              </div>
              <p style="text-align: center; margin-bottom: 5px;">© <?= date('Y') ?> David Freitas. Todos os direitos reservados.</p>
              <p style="text-align: center; margin-top: 0;">Você recebeu este e-mail porque está cadastrado em davidfreitas.dev</p>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
