<?php 

namespace App\Models;

use App\DB\Database;
use App\Mail\Mailer;
use App\Models\User;
use App\Utils\PasswordHelper;
use App\Traits\TokenGenerator;
use App\Utils\AESCryptographer;
use App\Utils\ApiResponseFormatter;
use App\Enums\HttpStatus as HTTPStatus;

class Auth {

  use TokenGenerator;

  public static function signup($data) 
	{

		try {

      $user = new User();

      $user->setAttributes($data);

      $data = $user->create();

      $jwt = self::generateToken($data);

      return ApiResponseFormatter::formatResponse(
        HTTPStatus::CREATED, 
        "success", 
        "Usuário cadastrado com sucesso",
        $jwt
      );

    } catch (\Exception $e) {

      return ApiResponseFormatter::formatResponse(
        $e->getCode(), 
        "error", 
        "Falha ao cadastrar usuário: " . $e->getMessage(),
        null
      );

    }

	}
        
  public static function signin($credential, $password)
  {

    $sql = "SELECT * FROM tb_users a 
            INNER JOIN tb_persons b 
            ON a.idperson = b.idperson 
            WHERE a.deslogin = :deslogin 
            OR b.desemail = :desemail	
            OR b.nrcpf = :nrcpf";

    try {
      
      $db = new Database();

      $results = $db->select($sql, array(
        ":deslogin" => $credential,
        ":desemail" => $credential,
        ":nrcpf"    => $credential
      ));

      if (empty($results) || !password_verify($password, $results[0]['despassword'])) {

        throw new \Exception("Usuário inexistente ou senha inválida.", HTTPStatus::UNAUTHORIZED);
  
      }

      $jwt = self::generateToken($results[0]);

      return ApiResponseFormatter::formatResponse(
        HTTPStatus::OK,
        "success", 
        "Usuário autenticado com sucesso",
        $jwt
      );   

    } catch (\Exception $e) {
      
      return ApiResponseFormatter::formatResponse(
        $e->getCode(), 
        "error", 
        "Falha na autenticação do usuário: " . $e->getMessage(),
        null
      );

    }
    
  }

  public static function getForgotLink($email)
  {

    $sql = "SELECT * FROM tb_persons a 
            INNER JOIN tb_users b 
            USING(idperson) 
            WHERE a.desemail = :email";

    try {
      
      $db = new Database();

      $results = $db->select($sql, array(
        ":email" => $email
      ));
      
      if (empty($results)) {
            
        throw new \Exception("O e-mail informado não consta no banco de dados", HTTPStatus::NOT_FOUND);
        
      }
      
      $user = $results[0];

      $idrecovery = $db->insert(
        "INSERT INTO tb_userspasswordsrecoveries (iduser, desip) VALUES (:iduser, :desip)", array(
          ":iduser" => $user['iduser'],
          ":desip"  => $_SERVER['REMOTE_ADDR']
        )
      ); 

      $code = AESCryptographer::encrypt($idrecovery);

      $link = $_ENV['DASHBOARD_URL']."/forgot/reset?code=$code";

      $mailer = new Mailer(
        $user['desemail'], 
        $user['desperson'], 
        "Redefinição de senha", 
        array(
          "name" => $user['desperson'],
          "link" => $link
        )
      );				

      $mailer->send();

      return ApiResponseFormatter::formatResponse(
        HTTPStatus::OK, 
        "success", 
        "Link de redefinição de senha enviado para o e-mail informado.",
        null
      );

    } catch (\Exception $e) {
      
      return ApiResponseFormatter::formatResponse(
        $e->getCode(), 
        "error", 
        "Falha ao enviar link de recuperação de senha: " . $e->getMessage(),
        null
      );

    }		

  }

  public static function validateForgotLink($code)
  {

    if (!isset($code) || empty($code)) {

      throw new \Exception("Falha ao validar token: token inexistente.", HTTPStatus::UNAUTHORIZED);

    }

    $idrecovery = AESCryptographer::decrypt($code);

    $sql = "SELECT * FROM tb_userspasswordsrecoveries a
            INNER JOIN tb_users b USING(iduser)
            INNER JOIN tb_persons c USING(idperson)
            WHERE a.idrecovery = :idrecovery
            AND a.dtrecovery IS NULL
            AND DATE_ADD(a.dtregister, INTERVAL 1 HOUR) >= NOW()";
    
    try {
      
      $db = new Database();

      $results = $db->select($sql, array(
        ":idrecovery" => $idrecovery
      ));

      if (empty($results)) {

        throw new \Exception("O link de redefinição utilizado expirou", HTTPStatus::UNAUTHORIZED);

      } 
      
      return ApiResponseFormatter::formatResponse(
        HTTPStatus::OK, 
        "success", 
        "Link de redefinição validado com sucesso.",
        $results[0]
      );

    } catch (\Exception $e) {
      
      return ApiResponseFormatter::formatResponse(
        $e->getCode(), 
        "error", 
        "Falha ao validar token: " . $e->getMessage(),
        null
      );

    }

  }

  public static function setNewPassword($password, $iduser)
  {

    $sql = "UPDATE tb_users 
            SET despassword = :despassword 
            WHERE iduser = :iduser";

    try {

      PasswordHelper::checkPasswordStrength($password);

      $db = new Database();

      $db->query($sql, array(
        ":iduser"      => $iduser,
        ":despassword" => PasswordHelper::hashPassword($password)
      ));

      return ApiResponseFormatter::formatResponse(
        HTTPStatus::OK, 
        "success", 
        "Senha alterada com sucesso.",
        null
      );

    } catch (\Exception $e) {

      return ApiResponseFormatter::formatResponse(
        $e->getCode(), 
        "error", 
        "Falha ao gravar nova senha: " . $e->getMessage(),
        null
      );

    }

  }

  public static function setForgotUsed($idrecovery)
  {

    $sql = "UPDATE tb_userspasswordsrecoveries 
            SET dtrecovery = NOW() 
            WHERE idrecovery = :idrecovery";

    try {

      $db = new Database();

      $db->query($sql, array(
        ":idrecovery"=>$idrecovery
      ));

    } catch (\PDOException $e) {

      return ApiResponseFormatter::formatResponse(
        HTTPStatus::INTERNAL_SERVER_ERROR, 
        "error", 
        "Falha ao definir senha antiga como usada: " . $e->getMessage(),
        null
      );

    }

  }

}