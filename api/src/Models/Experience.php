<?php

namespace App\Models;

use App\DB\Database;
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
          200, 
          "success", 
          $results
        );

			}
      
      return ApiResponseFormatter::formatResponse(
        204, 
        "success", 
        "Nenhuma experiência encontrada"
      );

		} catch (\PDOException $e) {

			return ApiResponseFormatter::formatResponse(
        500, 
        "error", 
        "Falha ao obter experiências: " . $e->getMessage()
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
          200, 
          "success", 
          $results[0]
        );
        
      }

			return ApiResponseFormatter::formatResponse(
        404, 
        "error", 
        "Experiência não encontrada"
      );

		} catch (\PDOException $e) {
			
			return ApiResponseFormatter::formatResponse(
        500, 
        "error", 
        "Falha ao obter experiência: " . $e->getMessage()
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
        201, 
        "success", 
        "Experiência criada com sucesso"
      );

    } catch (\PDOException $e) {
			
			return ApiResponseFormatter::formatResponse(
        500, 
        "error", 
        "Falha ao criar experiência: " . $e->getMessage()
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
        200, 
        "success", 
        "Experiência atualizada com sucesso"
      );

    } catch (\PDOException $e) {

      return ApiResponseFormatter::formatResponse(
        500, 
        "error", 
        "Falha ao atualizar experiência: " . $e->getMessage()
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
        200, 
        "success", 
        "Experiência excluida com sucesso"
      );

		} catch (\PDOException $e) {

			return ApiResponseFormatter::formatResponse(
        500, 
        "error", 
        "Falha ao excluir experiência: " . $e->getMessage()
      );
			
		}

  }

}
