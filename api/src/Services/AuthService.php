<?php

namespace App\Services;

use App\Models\User;
use App\DB\Database;
use App\Utils\PasswordHelper;
use App\Utils\AESCryptographer;
use App\Services\TokenService;
use App\Enums\HttpStatus as HTTPStatus;

class AuthService
{

  private $db;
  
  private $tokenService;

  public function __construct(Database $db, TokenService $tokenService)
  {
      
    $this->db = $db;
      
    $this->tokenService = $tokenService;

  }

  public function signup(array $data): array
	{

		$requiredFields = ["name", "email", "password", "cpfcnpj"];
      
    foreach ($requiredFields as $field) {
      
      if (!isset($data[$field]) || trim($data[$field]) === "") {
        
        throw new \Exception("O campo '$field' é obrigatório.", HTTPStatus::BAD_REQUEST);
      
      }

    }

    $user = new User();

    $user->setAttributes($data);

    $data = $user->create();

    $jwt = $this->tokenService->generatePrivateToken($data);

    return [
      "token"      => $jwt,
      "type"       => "Bearer",
      "expires_in" => 3600
    ];

	}
        
  public function signin(string $login, string $password): array
  {

    $login = trim($login);
      
    if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
      
      $login = strtolower($login);
    
    }

    $results = $this->db->select(
      "SELECT u.id, p.name, u.password
      FROM users u
      INNER JOIN persons p ON u.id = p.id
      WHERE (p.email = :login OR p.cpfcnpj = :login)", 
      array(
        ":login" => $login
      )
    );

    if (empty($results)) {
      
      throw new \Exception("Usuário inexistente ou senha inválida.", HTTPStatus::UNAUTHORIZED);
    
    }

    $user = $results[0];

    if (!password_verify($password, $user["password"])) {
      
      throw new \Exception("Usuário inexistente ou senha inválida.", HTTPStatus::UNAUTHORIZED);
    
    }

    $user['roles'] = $this->db->select(
      "SELECT r.id, r.name
      FROM roles r
      INNER JOIN user_roles ur ON ur.role_id = r.id
      WHERE ur.user_id = :userId",
      array(
        ":userId" => $user['id']
      )
    );

    $jwt = $this->tokenService->generatePrivateToken($user);

    return [
      "token"      => $jwt,
      "type"       => "Bearer",
      "expires_in" => 3600
    ];
  
  }

  public function getForgotLink(string $email): array
  {
    
    $sql = "SELECT u.id, p.name, p.email
            FROM users u
            INNER JOIN persons p ON u.id = p.id
            WHERE p.email = :email
            LIMIT 1";
    
    $results = $this->db->select($sql, array(
      ":email" => strtolower(trim($email))
    ));

    if (empty($results)) {
        
      throw new \Exception("Não foi possível encontrar um usuário com esse e-mail.", 404);
    
    }

    $user = $results[0];

    $recoveryId = $this->db->insert(
      "INSERT INTO password_resets (user_id, ip_address, created_at, updated_at) 
      VALUES (:user_id, :ip_address, NOW(), NOW())",
      [
        ":user_id"    => $user["id"],
        ":ip_address" => $_SERVER["REMOTE_ADDR"]
      ]
    );

    $code = AESCryptographer::encrypt([
      "recovery_id" => $recoveryId,
      "user_id"     => $user["id"]
    ]);

    return [
      "link"        => $_ENV["SITE_URL"] . "/reset?code=$code",
      "user"        => $user,
      "id_recovery" => $recoveryId
    ];

  }

  public function validateForgotLink(string $code)
  {
    
    $decryptedData = AESCryptographer::decrypt($code);

    if (!is_array($decryptedData) || !isset($decryptedData["recovery_id"], $decryptedData["user_id"])) {
      
      throw new \Exception("Token inválido ou corrompido.", 401);
    
    }
    
    $recoveryId = $decryptedData["recovery_id"];

    $sql = "SELECT pr.*, u.id AS user_id, p.name, p.email
            FROM password_resets pr
            INNER JOIN users u ON u.id = pr.user_id
            INNER JOIN persons p ON p.id = u.id
            WHERE pr.id = :id
              AND pr.used_at IS NULL
              AND DATE_ADD(pr.created_at, INTERVAL 1 HOUR) >= NOW()";

    $results = $this->db->select($sql, array(
      ":id" => $recoveryId
    ));

    if (empty($results)) {
      
      throw new \Exception("O link de redefinição utilizado expirou.", 401);
    
    }

    return [
      "user_id"     => $results[0]["user_id"],
      "recovery_id" => $results[0]["id"]
    ];
  
  }

  public function setNewPassword(string $password, array $data)
  {
    
    PasswordHelper::checkPasswordStrength($password);

    $sql = "UPDATE users SET password = :password WHERE id = :user_id";
    
    $this->db->query($sql, [
      ":password" => PasswordHelper::hashPassword($password),
      ":user_id"  => $data["user_id"]
    ]);

    self::setForgotUsed($data["recovery_id"]);

    return true;
  
  }

  private function setForgotUsed(int $recoveryId)
  {
    
    $sql = "UPDATE password_resets 
            SET used_at = NOW() 
            WHERE id = :id";
    
    $this->db->query($sql, array(
      ":id" => $recoveryId
    ));
  
  }

}
