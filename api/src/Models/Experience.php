<?php

namespace App\Models;

use App\DB\Database;
use App\Enums\HttpStatus as HTTPStatus;
use App\Utils\ApiResponseFormatter;

class Experience
{

  public static function list() 
  {    
    $sql = "SELECT * FROM tb_experiences
            ORDER BY dtstart DESC";
		
		try {

			$db = new Database();

			$results = $db->select($sql);
			
			if (count($results)) {

				return ApiResponseFormatter::formatResponse(
          HTTPStatus::OK, 
          "success", 
          "Lista de experiências",
          $results
        );

			}
      
      return ApiResponseFormatter::formatResponse(
        HTTPStatus::NO_CONTENT,
        "success", 
        "Nenhuma experiência encontrada",
        null
      );

		} catch (\PDOException $e) {

			return ApiResponseFormatter::formatResponse(
        HTTPStatus::INTERNAL_SERVER_ERROR, 
        "error", 
        "Falha ao obter experiências: " . $e->getMessage(),
        null
      );
			
		}

	}

  public static function get($idexperience)
	{

    $sql = "SELECT * FROM tb_experiences
            WHERE idexperience = :idexperience";

		try {

			$db = new Database();

			$results = $db->select($sql, array(
				":idexperience"=>$idexperience
			));

      if (count($results)) {
			
			  return ApiResponseFormatter::formatResponse(
          HTTPStatus::OK, 
          "success", 
          "Detalhes da experiência",
          $results[0]
        );
        
      }

			return ApiResponseFormatter::formatResponse(
        HTTPStatus::NOT_FOUND,
        "error", 
        "Experiência não encontrada",
        null
      );

		} catch (\PDOException $e) {
			
			return ApiResponseFormatter::formatResponse(
        HTTPStatus::INTERNAL_SERVER_ERROR, 
        "error", 
        "Falha ao obter experiência: " . $e->getMessage(),
        null
      );
			
		}

  }

  public static function create($experience)
  {

    $sql = "INSERT INTO tb_experiences (destitle, desdescription, dtstart, dtend)
            VALUES (:destitle, :desdescription, :dtstart, :dtend)";

    try {
      
      $db = new Database();

			$db->query($sql, array(
				":destitle"=>$experience['destitle'],
				":desdescription"=>$experience['desdescription'],
				":dtstart"=>$experience['dtstart'],
				":dtend"=>$experience['dtend']
			));

      return ApiResponseFormatter::formatResponse(
        HTTPStatus::CREATED, 
        "success", 
        "Experiência criada com sucesso",
        null
      );

    } catch (\PDOException $e) {
			
			return ApiResponseFormatter::formatResponse(
        HTTPStatus::INTERNAL_SERVER_ERROR, 
        "error", 
        "Falha ao criar experiência: " . $e->getMessage(),
        null
      );
			
		}

  }

  public static function update($idexperience, $experience) 
	{

    $sql = "UPDATE tb_experiences
            SET destitle = :destitle,
                desdescription = :desdescription,
                dtstart = :dtstart,
                dtend = :dtend
            WHERE idexperience = :idexperience";

    try {

      $db = new Database();
      
      $db->query($sql, array(
				":idexperience"=>$idexperience,
        ":destitle"=>$experience['destitle'],
        ":desdescription"=>$experience['desdescription'],
        ":dtstart"=>$experience['dtstart'],
        ":dtend"=>$experience['dtend']
      ));

      return ApiResponseFormatter::formatResponse(
        HTTPStatus::OK, 
        "success", 
        "Experiência atualizada com sucesso",
        null
      );

    } catch (\PDOException $e) {

      return ApiResponseFormatter::formatResponse(
        HTTPStatus::INTERNAL_SERVER_ERROR, 
        "error", 
        "Falha ao atualizar experiência: " . $e->getMessage(),
        null
      );
      
    }

  }

  public static function delete($idexperience) 
	{

    $sql = "DELETE FROM tb_experiences
            WHERE idexperience = :idexperience";		
		
		try {

			$db = new Database();
			
			$db->query($sql, array(
				":idexperience"=>$idexperience
			));
			
			return ApiResponseFormatter::formatResponse(
        HTTPStatus::OK, 
        "success", 
        "Experiência excluída com sucesso",
        null
      );

		} catch (\PDOException $e) {

			return ApiResponseFormatter::formatResponse(
        HTTPStatus::INTERNAL_SERVER_ERROR, 
        "error", 
        "Falha ao excluir experiência: " . $e->getMessage(),
        null
      );
			
		}

  }

}
