<?php

namespace App\Models;

use App\DB\Database;
use App\Enums\HttpStatus as HTTPStatus;
use App\Utils\ApiResponseFormatter;

class Technology
{

  public static function list() 
  {    
    $sql = "SELECT * FROM tb_technologies
            ORDER BY idtechnology DESC";
		
		try {

			$db = new Database();

			$results = $db->select($sql);
			
			if (count($results)) {

				return ApiResponseFormatter::formatResponse(
          HTTPStatus::OK, 
          "success", 
          $results
        );

			}
      
      return ApiResponseFormatter::formatResponse(
        HTTPStatus::NO_CONTENT,
        "success", 
        "Nenhuma tecnologia encontrada"
      );

		} catch (\PDOException $e) {

			return ApiResponseFormatter::formatResponse(
        HTTPStatus::INTERNAL_SERVER_ERROR, 
        "error", 
        "Falha ao obter tecnologias: " . $e->getMessage()
      );
			
		}

	}

  public static function get($idtechnology)
	{

    $sql = "SELECT * FROM tb_technologies
            WHERE idtechnology = :idtechnology";

		try {

			$db = new Database();

			$results = $db->select($sql, array(
				":idtechnology"=>$idtechnology
			));

      if (count($results)) {
			
			  return ApiResponseFormatter::formatResponse(
          HTTPStatus::OK, 
          "success", 
          $results[0]
        );
        
      }

			return ApiResponseFormatter::formatResponse(
        HTTPStatus::NOT_FOUND,
        "error", 
        "Tecnologia não encontrada"
      );

		} catch (\PDOException $e) {
			
			return ApiResponseFormatter::formatResponse(
        HTTPStatus::INTERNAL_SERVER_ERROR, 
        "error", 
        "Falha ao obter tecnologia: " . $e->getMessage()
      );
			
		}

  }

  public static function create($technology)
  {

    $sql = "INSERT INTO tb_technologies (desname)
            VALUES (:desname)";

    try {
      
      $db = new Database();

			$db->query($sql, array(
				":desname"=>$technology['desname']
			));

      return ApiResponseFormatter::formatResponse(
        HTTPStatus::CREATED, 
        "success", 
        "Tecnologia criada com sucesso"
      );

    } catch (\PDOException $e) {
			
			return ApiResponseFormatter::formatResponse(
        HTTPStatus::INTERNAL_SERVER_ERROR, 
        "error", 
        "Falha ao criar tecnologia: " . $e->getMessage()
      );
			
		}

  }

  public static function update($idtechnology, $technology) 
	{

    $sql = "UPDATE tb_technologies
            SET desname = :desname
            WHERE idtechnology = :idtechnology";

    try {

      $db = new Database();
      
      $db->query($sql, array(
				":idtechnology"=>$idtechnology,
        ":desname"=>$technology['desname']
      ));

      return ApiResponseFormatter::formatResponse(
        HTTPStatus::OK, 
        "success", 
        "Tecnologia atualizada com sucesso"
      );

    } catch (\PDOException $e) {

      return ApiResponseFormatter::formatResponse(
        HTTPStatus::INTERNAL_SERVER_ERROR, 
        "error", 
        "Falha ao atualizar tecnologia: " . $e->getMessage()
      );
      
    }

  }

  public static function delete($idtechnology) 
	{

    $sql = "DELETE FROM tb_technologies
            WHERE idtechnology = :idtechnology";		
		
		try {

			$db = new Database();
			
			$db->query($sql, array(
				":idtechnology"=>$idtechnology
			));
			
			return ApiResponseFormatter::formatResponse(
        HTTPStatus::OK, 
        "success", 
        "Tecnologia excluida com sucesso"
      );

		} catch (\PDOException $e) {

			return ApiResponseFormatter::formatResponse(
        HTTPStatus::INTERNAL_SERVER_ERROR, 
        "error", 
        "Falha ao excluir tecnologia: " . $e->getMessage()
      );
			
		}

  }

}
