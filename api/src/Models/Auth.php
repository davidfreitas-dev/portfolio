<?php 

namespace App\Models;

use App\DB\Database;
use App\Mail\Mailer;
use App\Models\User;
use App\Traits\TokenGenerator;
use App\Utils\AESCryptographer;
use App\Utils\ApiResponseFormatter;
use App\Enums\HttpStatus as HTTPStatus;

class Auth {

  use TokenGenerator;

  public static function signup($data) 
	{

		try {

      if (self::checkUserExists($data)) {
        
        return ApiResponseFormatter::formatResponse(
          HTTPStatus::CONFLICT,
          "error", 
          "Usuário já cadastrado no banco de dados",
          null
        );

      }

      return User::create($data);

    } catch (\PDOException $e) {

      return ApiResponseFormatter::formatResponse(
        HTTPStatus::INTERNAL_SERVER_ERROR, 
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
        ":deslogin"=>$credential,
        ":desemail"=>$credential,
        ":nrcpf"=>$credential
      ));

      if (empty($results) || !password_verify($password, $results[0]['despassword'])) {

        return ApiResponseFormatter::formatResponse(
          HTTPStatus::NOT_FOUND,
          "error", 
          "Usuário inexistente ou senha inválida",
          null
        );
  
      }

      $token = self::generateToken($results[0]);

      return ApiResponseFormatter::formatResponse(
        HTTPStatus::OK,
        "success", 
        "Usuário autenticado com sucesso",
        $token
      );      

    } catch (\PDOException $e) {
      
      return ApiResponseFormatter::formatResponse(
        HTTPStatus::INTERNAL_SERVER_ERROR, 
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
        ":email"=>$email
      ));

      if (empty($results)) {

        return ApiResponseFormatter::formatResponse(
          HTTPStatus::NOT_FOUND,
          "error", 
          "O e-mail informado não consta no banco de dados",
          null
        );

      } 
      
      $user = $results[0];

      $query = $db->select(
        "CALL sp_userspasswordsrecoveries_create(:iduser, :desip)", array(
          ":iduser"=>$user['iduser'],
          ":desip"=>$_SERVER['REMOTE_ADDR']
        )
      ); 

      if (empty($query))	{

        return ApiResponseFormatter::formatResponse(
          HTTPStatus::BAD_REQUEST,
          "error", 
          "Não foi possível recuperar a senha",
          null
        );

      }

      $recovery = $query[0];

      $code = AESCryptographer::encrypt($recovery['idrecovery']);

      $link = $_ENV['DASHBOARD_URL']."/forgot/reset?code=$code";

      $mailer = new Mailer(
        $user['desemail'], 
        $user['desperson'], 
        "Redefinição de senha", 
        array(
          "name"=>$user['desperson'],
          "link"=>$link
        )
      );				

      $mailer->send();

      return ApiResponseFormatter::formatResponse(
        HTTPStatus::OK, 
        "success", 
        "Link de redefinição de senha enviado para o e-mail informado.",
        null
      );

    } catch (\PDOException $e) {
      
      return ApiResponseFormatter::formatResponse(
        HTTPStatus::INTERNAL_SERVER_ERROR, 
        "error", 
        "Falha ao recuperar senha: " . $e->getMessage(),
        null
      );

    }		

  }

  public static function validateForgotLink($code)
  {

    if (!isset($code) || empty($code)) {

      return ApiResponseFormatter::formatResponse(
        HTTPStatus::UNAUTHORIZED, 
        "error", 
        "Falha ao validar token: token inexistente.",
        null
      );

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
        ":idrecovery"=>$idrecovery
      ));

      if (empty($results)) {

        return ApiResponseFormatter::formatResponse(
          HTTPStatus::UNAUTHORIZED,
          "error", 
          "O link de redefinição utilizado expirou",
          null
        );

      } 
      
      return ApiResponseFormatter::formatResponse(
        HTTPStatus::OK, 
        "success", 
        "Link de redefinição validado com sucesso.",
        $results[0]
      );

    } catch (\PDOException $e) {
      
      return ApiResponseFormatter::formatResponse(
        HTTPStatus::INTERNAL_SERVER_ERROR, 
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

      $db = new Database();

      $db->query($sql, array(
        ":despassword"=>Auth::getPasswordHash($password),
        ":iduser"=>$iduser
      ));

      return ApiResponseFormatter::formatResponse(
        HTTPStatus::OK, 
        "success", 
        "Senha alterada com sucesso.",
        null
      );

    } catch (\PDOException $e) {

      return ApiResponseFormatter::formatResponse(
        HTTPStatus::INTERNAL_SERVER_ERROR, 
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

  private static function checkUserExists($data) 
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
        ":deslogin" => $data['deslogin'],
        ":desemail" => $data['desemail'],
        ":nrcpf" => $data['nrcpf']
      ));

      return !empty($results);

    } catch (\PDOException $e) {

      return false;
      
    }

  }

  private static function getPasswordHash($password)
	{

		return password_hash($password, PASSWORD_BCRYPT, [
			'cost' => 12
		]);

	}

}