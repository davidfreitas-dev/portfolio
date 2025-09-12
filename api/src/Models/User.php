<?php 

namespace App\Models;

use App\DB\Database;
use App\Models\Model;
use App\Logging\ErrorLog;
use App\Utils\PasswordHelper;
use App\Services\TokenService;
use App\Handlers\DocumentHandler;
use App\Enums\HttpStatus as HTTPStatus;

class User extends Model {

  public static function get($userId)
	{

		try {

			$db = new Database();

			$results = $db->select(
        "SELECT u.id, p.name, p.email, p.phone, p.cpfcnpj, u.is_active, u.created_at, u.updated_at
        FROM users u
        INNER JOIN persons p ON u.id = p.id
        WHERE u.id = :userId
        LIMIT 1", 
        array(
          ":userId" => $userId
        )
      );

      if (empty($results)) {

        throw new \Exception("Usuário não encontrado", HTTPStatus::NOT_FOUND);   
        
      }

      $user = $results[0];

      $user['roles'] = $db->select(
        "SELECT r.id, r.name
        FROM roles r
        INNER JOIN user_roles ur ON ur.role_id = r.id
        WHERE ur.user_id = :userId",
        array(
          ":userId" => $userId
        )
      );
			
      return $user;

		} catch (\PDOException $e) {
			
			throw new \Exception($e->getMessage(), HTTPStatus::INTERNAL_SERVER_ERROR);
			
		}	catch (\Exception $e) {
			
			throw new \Exception($e->getMessage(), $e->getCode());
			
		}

	}

  public function create()
  {

    $db = new Database();
    
    $conn = $db->getConnection();
    
    try {

      $conn->beginTransaction();
        
      $cpfcnpj = preg_replace('/[^0-9]/is', '', $this->getCpfcnpj());

      if (!DocumentHandler::validateDocument($cpfcnpj)) {
          
        throw new \Exception("CPF/CNPJ inválido.", HTTPStatus::BAD_REQUEST);
      
      }

      $email = strtolower(trim($this->getEmail()));

      if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
          
        throw new \Exception("O formato do e-mail informado não é válido.", HTTPStatus::BAD_REQUEST);
      
      }

      $this->checkUserExists($cpfcnpj, $email);

      PasswordHelper::checkPasswordStrength($this->getPassword());

      $personId = $this->insertPerson($conn, [
        'name'     => mb_convert_case(trim(preg_replace('/\s+/', ' ', $this->getName())), MB_CASE_TITLE, "UTF-8"),
        'email'    => $email,
        'phone'    => $this->getPhone() ? preg_replace('/\D/', '', $this->getPhone()) : NULL,
        'cpfcnpj'  => $cpfcnpj,
      ]);

      $userId = $this->insertUser($conn, [
        'person_id' => $personId,
        'password'  => $this->getPassword(),
      ]);

      $this->setUserRole($conn,$userId);

      $conn->commit();

      return self::get($userId);

    } catch (\PDOException $e) {

      $conn->rollBack();

      ErrorLog::log($e, ['method' => 'User::create']);
			
			throw new \Exception($e->getMessage(), HTTPStatus::INTERNAL_SERVER_ERROR);
			
		}	catch (\Exception $e) {

      $conn->rollBack();

      ErrorLog::log($e, ['method' => 'User::create']);
			
			throw new \Exception($e->getMessage(), $e->getCode());
			
		}
  
  }

	public function update() 
	{
		
		try {

      $cpfcnpj = preg_replace('/[^0-9]/is', '', $this->getCpfcnpj());
      
      if (!DocumentHandler::validateDocument($cpfcnpj)) {
          
        throw new \Exception("CPF/CNPJ inválido.", HTTPStatus::BAD_REQUEST);
      
      }

      $email = strtolower(trim($this->getEmail()));

      if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        throw new \Exception("O formato do e-mail informado não é válido.", HTTPStatus::BAD_REQUEST);
        
      }

      $this->checkUserExists($cpfcnpj, $email, $this->getUserId());

			$db = new Database();
			
			$db->query(
        "UPDATE persons 
        SET name = :name, email = :email, cpfcnpj = :cpfcnpj, phone = :phone
        WHERE id = :id",
        [
          ":name"    => mb_convert_case(trim(preg_replace('/\s+/', ' ', $this->getName())), MB_CASE_TITLE, "UTF-8"),
          ":email"   => $email,
          ":phone"   => $this->getPhone() ? preg_replace('/\D/', '', $this->getPhone()) : NULL,
          ":cpfcnpj" => $cpfcnpj,
          ":id"      => $this->getUserId()
        ]
      );

      $user = self::get($this->getUserId());

      $jwt = TokenService::generatePrivateToken($user);

			return $jwt;

		} catch (\PDOException $e) {

			throw new \Exception("Erro ao atualizar dados do usuário", HTTPStatus::INTERNAL_SERVER_ERROR);
			
		} catch (\Exception $e) {

			throw new \Exception($e->getMessage(), $e->getCode());
			
		}				

	}

	public static function delete($userId) 
	{
		
		$sql = "DELETE FROM users WHERE id = :id";
		
		try {

			$db = new Database();
			
			$db->query($sql, array(
        ":id" => $userId
      ));

		} catch (\PDOException $e) {

			throw new \Exception("Erro ao excluir usuário", HTTPStatus::INTERNAL_SERVER_ERROR);
			
		} catch (\Exception $e) {

			throw new \Exception($e->getMessage(), $e->getCode());
			
		}

	}

  private function checkUserExists($cpfcnpj, $email, $userId = NULL) 
  {
    
    $sql = "SELECT u.id, p.email, p.cpfcnpj
            FROM users u
            INNER JOIN persons p ON u.id = p.id
            WHERE (p.email = :email OR p.cpfcnpj = :cpfcnpj)";

    if ($userId) {
        
      $sql .= " AND u.id != :userId";
    
    }

    try {
      
      $db = new Database();

      $params = [
        ":email"   => $email,
        ":cpfcnpj" => $cpfcnpj
      ];

      if ($userId) {
          
        $params[":userId"] = $userId;
      
      }

      $results = $db->select($sql, $params);

      if (count($results) > 0) {
          
        $existing = $results[0];

        if (strtolower($existing["email"]) === strtolower($email)) {
            
          throw new \Exception("O e-mail informado já está sendo utilizado por outro usuário", HTTPStatus::CONFLICT);
        
        }

        if ($existing["cpfcnpj"] === $cpfcnpj) {
            
          throw new \Exception("O CPF/CNPJ informado já está sendo utilizado por outro usuário", HTTPStatus::CONFLICT);
        
        }

      }

    } catch (\PDOException $e) {
        
      throw new \Exception($e->getMessage(), HTTPStatus::INTERNAL_SERVER_ERROR);
    
    } catch (\Exception $e) {
        
      throw new \Exception($e->getMessage(), $e->getCode());
    
    }
  
  }

  private function insertPerson($conn, $data)
  {

    $sql = "INSERT INTO persons (name, email, phone, cpfcnpj) 
            VALUES (:name, :email, :phone, :cpfcnpj)";

    $stmt = $conn->prepare($sql);

    $stmt->execute([
      ":name"    => $data['name'],
      ":email"   => $data['email'],
      ":phone"   => $data['phone'],
      ":cpfcnpj" => $data['cpfcnpj']
    ]);

    return $conn->lastInsertId();
    
  }

  private function insertUser($conn, $data)
  {
    
    $sql = "INSERT INTO users (id, password, is_active, created_at, updated_at) 
            VALUES (:id, :password, 1, NOW(), NOW())";

    $stmt = $conn->prepare($sql);

    $stmt->execute([
      ":id"       => $data['person_id'],
      ":password" => PasswordHelper::hashPassword($data['password'])
    ]);

    return $data['person_id'];

  }
  
  private function setUserRole($conn, $userId) 
  {

    $sql = "INSERT INTO user_roles (user_id, role_id) VALUES (:user_id, :role_id)";
    
    $stmt = $conn->prepare($sql);
    
    $stmt->execute([
      ':user_id' => $userId,
      ':role_id' => 2 // Usuário do sistema
    ]);
    
  }

}

 ?>