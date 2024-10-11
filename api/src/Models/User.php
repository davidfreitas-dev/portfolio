<?php 

namespace App\Models;

use App\DB\Database;
use App\Models\Model;
use App\Utils\PasswordHelper;
use App\Traits\TokenGenerator;
use App\Utils\ApiResponseFormatter;
use App\Enums\HttpStatus as HTTPStatus;

class User extends Model {

  use TokenGenerator;
  
  public function create()
  {

    $sql = "CALL sp_users_create(
      :desperson, 
      :deslogin, 
      :despassword, 
      :desemail, 
      :nrphone, 
      :nrcpf, 
      :inadmin
    )";

    try {

      if (!filter_var(strtolower($this->getdesemail()), FILTER_VALIDATE_EMAIL)) {

        throw new \Exception("O e-mail informado não é válido.", HTTPStatus::BAD_REQUEST);
        
      }

      PasswordHelper::checkPasswordStrength($this->getdespassword());

      $this->checkUserExists($this->getdeslogin(), strtolower($this->getdesemail()), $this->getnrcpf());
      
      $db = new Database();

			$results = $db->select($sql, array(
				":desperson"   => $this->getdesperson(),
				":deslogin"    => $this->getdeslogin(),
				":desemail"    => strtolower($this->getdesemail()),
				":despassword" => PasswordHelper::hashPassword($this->getdespassword()),
				":nrphone"     => $this->getnrphone() ? preg_replace('/[^0-9]/is', '', $this->getnrphone()) : NULL,
				":nrcpf"       => $this->getnrcpf() ? preg_replace('/[^0-9]/is', '', $this->getnrcpf()) : NULL,
				":inadmin"     => $this->getinadmin()
			));

      if (empty($results)) {
        
        throw new \Exception("Não foi possível persistir os dados do cadastro", HTTPStatus::BAD_REQUEST);

      }

      return $results[0];

    } catch (\Exception $e) {
			
			throw new \Exception($e->getMessage(), $e->getCode());
			
		}

  }

	public function update() 
	{
		
		$sql = "CALL sp_users_update(
              :iduser, 
              :desperson, 
              :deslogin, 
              :despassword, 
              :desemail, 
              :nrphone, 
              :nrcpf, 
              :inadmin
            )";
		
		try {

      $this->checkUserExists(
        $this->getdeslogin(), 
        $this->getdesemail(),
        $this->getnrcpf() 
      );

			$db = new Database();
			
			$results = $db->select($sql, array(
        ":iduser"      => $this->getiduser(),	
        ":desperson"   => $this->getdesperson(),
				":deslogin"    => $this->getdeslogin(),
				":despassword" => PasswordHelper::hashPassword($this->getdespassword()),
				":desemail"    => $this->getdesemail(),
				":nrphone"     => $this->getnrphone(),
				":nrcpf"       => $this->getnrcpf(),
				":inadmin"     => $this->getinadmin()
			));

      if (empty($results)) {
        
        throw new \Exception("Não foi possível persistir os dados do cadastro");

      }

      $jwt = self::generateToken($results[0]);

			return ApiResponseFormatter::formatResponse(
        HTTPStatus::OK, 
        "success", 
        "Usuário atualizado com sucesso",
        $jwt
      );

		} catch (\Exception $e) {

			return ApiResponseFormatter::formatResponse(
        HTTPStatus::INTERNAL_SERVER_ERROR, 
        "error", 
        "Falha ao atualizar dados do usuário: " . $e->getMessage(),
        null
      );
			
		}

	}

	public static function list() 
  { 

    $sql = "SELECT * FROM tb_users a 
            INNER JOIN tb_persons b 
            ON a.idperson = b.idperson";
		
		try {

			$db = new Database();

			$results = $db->select($sql);
			
			if (empty($results)) {

				return ApiResponseFormatter::formatResponse(
          HTTPStatus::NO_CONTENT,  
          "success", 
          "Nenhum usuário encontrado",
          null
        );

			}

      return ApiResponseFormatter::formatResponse(
        HTTPStatus::OK, 
        "success", 
        "Lista de usuários",
        $results
      );

		} catch (\PDOException $e) {

			return ApiResponseFormatter::formatResponse(
        HTTPStatus::INTERNAL_SERVER_ERROR, 
        "error", 
        "Falha ao obter usuários: " . $e->getMessage(),
        null
      );
			
		}

	}

	public static function get($iduser)
	{

		$sql = "SELECT * FROM tb_users a 
            INNER JOIN tb_persons b 
            USING(idperson) 
            WHERE a.iduser = :iduser";

		try {

			$db = new Database();

			$results = $db->select($sql, array(
				":iduser"=>$iduser
			));

      if (empty($results)) {

        return ApiResponseFormatter::formatResponse(
          HTTPStatus::NOT_FOUND, 
          "error", 
          "Usuário não encontrado",
          null
        );
        
      }
			
      return ApiResponseFormatter::formatResponse(
        HTTPStatus::OK, 
        "success", 
        "Dados do usuário",
        $results[0]
      );

		} catch (\PDOException $e) {
			
			return ApiResponseFormatter::formatResponse(
        HTTPStatus::INTERNAL_SERVER_ERROR, 
        "error", 
        "Falha ao obter usuário: " . $e->getMessage(),
        null
      );
			
		}

	}

	public static function delete($iduser) 
	{
		
		$sql = "CALL sp_users_delete(:iduser)";		
		
		try {

			$db = new Database();
			
			$db->query($sql, array(
				":iduser"=>$iduser
			));
			
			return ApiResponseFormatter::formatResponse(
        HTTPStatus::OK, 
        "success", 
        "Usuário excluído com sucesso",
        null
      );

		} catch (\PDOException $e) {

			return ApiResponseFormatter::formatResponse(
        HTTPStatus::INTERNAL_SERVER_ERROR, 
        "error", 
        "Falha ao excluir usuário: " . $e->getMessage(),
        null
      );
			
		}		

	}

  private function checkUserExists($login, $email, $cpf, $iduser = null) 
  {

    $sql = "SELECT * FROM tb_users a 
            INNER JOIN tb_persons b 
            ON a.idperson = b.idperson 
            WHERE a.deslogin = :deslogin 
            OR b.desemail = :desemail	
            OR b.nrcpf = :nrcpf";

    if ($iduser) {

      $sql .= " AND iduser != :iduser";

    }

    try {

      $db = new Database();

      $params = [
        ":deslogin" => $login,
        ":desemail" => $email,
        ":nrcpf"    => $cpf,
      ];

      if ($iduser) {

        $params[":iduser"] = $iduser;
  
      }
        
      $results = $db->select($sql, $params);

      if (count($results) > 0) {
        
        if ($results[0]['deslogin'] === $login) {
            
          throw new \Exception("O nome de usuário informado já está em uso", HTTPStatus::BAD_REQUEST);
          
        }

        if ($results[0]['desemail'] === $email) {
          
          throw new \Exception("O email informado já está cadastrado", HTTPStatus::BAD_REQUEST);
          
        }

        if ($results[0]['nrcpf'] === $cpf) {
          
          throw new \Exception("O CPF informado já está cadastrado", HTTPStatus::BAD_REQUEST);
          
        }

      }

    } catch (\Exception $e) {

      throw new \Exception($e->getMessage(), $e->getCode());
      
    }

  }

}

 ?>