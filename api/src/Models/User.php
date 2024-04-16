<?php 

namespace App\Models;

use App\DB\Database;
use App\Enums\HttpStatus as HTTPStatus;
use App\Utils\ApiResponseFormatter;

class User {

	public static function list() 
  {    
    $sql = "SELECT * FROM tb_users a 
            INNER JOIN tb_persons b 
            ON a.idperson = b.idperson";
		
		try {

			$db = new Database();

			$results = $db->select($sql);
			
			if (count($results)) {

				return ApiResponseFormatter::formatResponse(
          HTTPStatus::OK, 
          "success", 
          "Lista de usuários",
          $results
        );

			}
      
      return ApiResponseFormatter::formatResponse(
        HTTPStatus::NO_CONTENT,
        "success", 
        "Nenhum usuário encontrado",
        null
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

      if (count($results)) {
			
			  return ApiResponseFormatter::formatResponse(
          HTTPStatus::OK, 
          "success",
          "Detalhes do usuário", 
          $results[0]
        );
        
      }

			return ApiResponseFormatter::formatResponse(
        HTTPStatus::NOT_FOUND,
        "error", 
        "Usuário não encontrado",
        null
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
  
  public static function create($user)
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
      
      $db = new Database();

			$results = $db->select($sql, array(
				":desperson"=>$user['desperson'],
				":deslogin"=>$user['deslogin'],
				":despassword"=>User::getPasswordHash($user['despassword']),
				":desemail"=>$user['desemail'],
				":nrphone"=>$user['nrphone'],
				":nrcpf"=>$user['nrcpf'],
				":inadmin"=>$user['inadmin']
			));

      if (empty($results)) {
        
        return ApiResponseFormatter::formatResponse(
          HTTPStatus::BAD_REQUEST,
          "error", 
          "Não foi possível retornar os dados do usuário cadastrado",
          null
        );

      }

      return ApiResponseFormatter::formatResponse(
        HTTPStatus::CREATED, 
        "success", 
        "Usuário cadastrado com sucesso",
        $results[0]
      );

    } catch (\PDOException $e) {
			
			return ApiResponseFormatter::formatResponse(
        HTTPStatus::INTERNAL_SERVER_ERROR, 
        "error", 
        "Falha ao cadastrar usuário: " . $e->getMessage(),
        null
      );
			
		}		

  }

	public static function update($iduser, $user) 
	{
		
		$sql = "CALL sp_usersupdate_save(
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

			$db = new Database();
			
			$result = $db->select($sql, array(
				":iduser"=>$iduser,
				":desperson"=>$user['desperson'],
				":deslogin"=>$user['deslogin'],
				":despassword"=>User::getPasswordHash($user['despassword']),
				":desemail"=>$user['desemail'],
				":nrphone"=>$user['nrphone'],
				":nrcpf"=>$user['nrcpf'],
				":inadmin"=>$user['inadmin']
			));

			if (count($result) == 0) {

				return ApiResponseFormatter::formatResponse(
          HTTPStatus::OK, 
          "success", 
          "Usuário atualizado com sucesso",
          null
        );
				
			}

		} catch (\PDOException $e) {

			return ApiResponseFormatter::formatResponse(
        HTTPStatus::INTERNAL_SERVER_ERROR, 
        "error", 
        "Falha ao atualizar dados do usuário: " . $e->getMessage(),
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

  public static function getByCredentials($user) 
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
				":deslogin"=>$user['deslogin'],
				":desemail"=>$user['desemail'],
				":nrcpf"=>$user['nrcpf']
			));

			return count($results);

		} catch (\PDOException $e) {

			return ApiResponseFormatter::formatResponse(
        HTTPStatus::INTERNAL_SERVER_ERROR, 
        "error", 
        "Falha ao obter usuário: " . $e->getMessage(),
        null
      );

		}		

	}

  private static function getPasswordHash($password)
	{

		return password_hash($password, PASSWORD_BCRYPT, [
			'cost' => 12
		]);

	}

}

 ?>