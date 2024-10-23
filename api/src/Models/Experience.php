<?php

namespace App\Models;

use App\DB\Database;
use App\Models\Model;
use App\Enums\HttpStatus as HTTPStatus;
use App\Utils\ApiResponseFormatter;

class Experience extends Model
{

  public function create()
  {

    $sql = "INSERT INTO tb_experiences (destitle, desdescription, dtstart, dtend)
            VALUES (:destitle, :desdescription, :dtstart, :dtend)";

    try {
      
      $db = new Database();

			$idexperience = $db->insert($sql, array(
				":destitle"       => $this->getdestitle(),
				":desdescription" => $this->getdesdescription(),
				":dtstart"        => $this->getdtstart(),
				":dtend"          => $this->getdtend()
			));

      $this->setidexperience($idexperience);

      return ApiResponseFormatter::formatResponse(
        HTTPStatus::CREATED, 
        "success", 
        "Experiência criada com sucesso",
        $this->getAttributes()
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

  public function update() 
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
				":idexperience"   => $this->getidexperience(),
        ":destitle"       => $this->getdestitle(),
        ":desdescription" => $this->getdesdescription(),
        ":dtstart"        => $this->getdtstart(),
        ":dtend"          => $this->getdtend()
      ));

      return ApiResponseFormatter::formatResponse(
        HTTPStatus::OK, 
        "success", 
        "Experiência atualizada com sucesso",
        $this->getAttributes()
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

  public static function list() 
  {    
    $sql = "SELECT * FROM tb_experiences
            ORDER BY idexperience DESC";
		
		try {

			$db = new Database();

			$results = $db->select($sql);
			
			if (empty($results)) {

				return ApiResponseFormatter::formatResponse(
          HTTPStatus::NO_CONTENT, 
          "success", 
          "Nenhuma experiência encontrada.",
          NULL
        );

			}
      
      return ApiResponseFormatter::formatResponse(
        HTTPStatus::OK, 
        "success", 
        "Lista de experiências",
        $results
      );

		} catch (\Exception $e) {

			return ApiResponseFormatter::formatResponse(
        $e->getCode(), 
        "error", 
        "Falha ao obter experiências: " . $e->getMessage(),
        null
      );
			
		}

	}

  public static function getPage($page = 1, $itemsPerPage = 5)
	{

    $start = ($page - 1) * $itemsPerPage;
		
		$sql = "SELECT SQL_CALC_FOUND_ROWS * 
            FROM tb_experiences 
            ORDER BY idexperience 
            LIMIT $start, $itemsPerPage";		
		
		try {

			$db = new Database();

			$results = $db->select($sql);

      $resultsTotal = $db->select("SELECT FOUND_ROWS() AS nrtotal");
			
			if (empty($results)) {

        return ApiResponseFormatter::formatResponse(
          HTTPStatus::NO_CONTENT, 
          "success", 
          "Nenhuma experiência encontrada.",
          NULL
        );
        
      } 

      return ApiResponseFormatter::formatResponse(
        HTTPStatus::OK, 
        "success", 
        "Lista de experiências",
        [
          "experiences" => $results,
          "total" => (int)$resultsTotal[0]["nrtotal"],
          "pages" => ceil($resultsTotal[0]["nrtotal"] / $itemsPerPage)
        ]
      );

		} catch (\Exception $e) {

			return ApiResponseFormatter::formatResponse(
        $e->getCode(), 
        "error", 
        "Falha ao obter experiências: " . $e->getMessage(),
        null
      );
			
		}		

	}

  public static function getPageSearch($search, $page = 1, $itemsPerPage = 5)
	{

		$start = ($page - 1) * $itemsPerPage;

    $sql = "SELECT SQL_CALC_FOUND_ROWS * 
            FROM tb_experiences 
            WHERE destitle 
            LIKE :search 
            ORDER BY destitle 
            LIMIT $start, $itemsPerPage";

    try {
      
      $db = new Database();
      
      $results = $db->select($sql, [
        ':search' => '%' . $search . '%'
      ]);
  
      $resultsTotal = $db->select("SELECT FOUND_ROWS() AS nrtotal;");

      if (empty($results)) {
        
        return ApiResponseFormatter::formatResponse(
          HTTPStatus::NO_CONTENT,  
          "success", 
          "Nenhuma experiência encontrada",
          null
        );

			} 

      return ApiResponseFormatter::formatResponse(
        HTTPStatus::OK, 
        "success", 
        "Lista de categorias",
        [
          "experiences" => $results,
          "total" => (int)$resultsTotal[0]["nrtotal"],
          "pages" => ceil($resultsTotal[0]["nrtotal"] / $itemsPerPage)
        ]
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

      if (empty($results)) {
			
			  return ApiResponseFormatter::formatResponse(
          HTTPStatus::NOT_FOUND, 
          "success", 
          "Experiência não encontrada.",
          NULL
        );
        
      }

			return ApiResponseFormatter::formatResponse(
        HTTPStatus::OK, 
        "success", 
        "Detalhes da experiência",
        $results[0]
      );

		} catch (\Exception $e) {
			
			return ApiResponseFormatter::formatResponse(
        $e->getCode(), 
        "error", 
        "Falha ao obter experiência: " . $e->getMessage(),
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
