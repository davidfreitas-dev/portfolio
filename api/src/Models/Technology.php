<?php

namespace App\Models;

use App\DB\Database;
use App\Models\Model;
use App\Utils\UploadHandler;
use App\Enums\HttpStatus as HTTPStatus;
use App\Utils\ApiResponseFormatter;

class Technology extends Model
{

  public function create()
  {

    $sql = "INSERT INTO tb_technologies (desname) VALUES (:desname)";

    try {

      $this->checkTechnologyExists($this->getdesname());
      
      $db = new Database();

			$idtechnology = $db->insert($sql, array(
				":desname" => $this->getdesname()
			));

      $this->setidtechnology($idtechnology);

      if (NULL !== $this->getimage() && !is_string($this->getimage())) {

        $imageUrl = $this->setPhoto($this->getidtechnology(), $this->getimage());
        
        if ($imageUrl) {

          $this->setimage($imageUrl);

        }

      }

      return ApiResponseFormatter::formatResponse(
        HTTPStatus::CREATED, 
        "success", 
        "Tecnologia criada com sucesso",
        $this->getAttributes()
      );

    } catch (\Exception $e) {
			
			return ApiResponseFormatter::formatResponse(
        $e->getCode(), 
        "error", 
        "Falha ao criar tecnologia: " . $e->getMessage(),
        null
      );
			
		}

  }

  public function update() 
	{

    $sql = "UPDATE tb_technologies
            SET desname = :desname
            WHERE idtechnology = :idtechnology";

    try {

      $this->checkTechnologyExists($this->getdesname(), $this->getidtechnology());

      $db = new Database();
      
      $db->query($sql, array(
        ":desname"      => $this->getdesname(),
        ":idtechnology" => $this->getidtechnology()
      ));

      if (NULL !== $this->getimage() && !is_string($this->getimage())) {

        $imageUrl = $this->setPhoto($this->getidtechnology(), $this->getimage());
        
        if ($imageUrl) {

          $this->setimage($imageUrl);

        }

      }

      return ApiResponseFormatter::formatResponse(
        HTTPStatus::OK, 
        "success", 
        "Tecnologia atualizada com sucesso",
        $this->getAttributes()
      );

    } catch (\Exception $e) {

      return ApiResponseFormatter::formatResponse(
        $e->getCode(), 
        "error", 
        "Falha ao atualizar tecnologia: " . $e->getMessage(),
        null
      );
      
    }

  }

  public static function list() 
  {    
    
    $sql = "SELECT * FROM tb_technologies ORDER BY idtechnology DESC";
		
		try {

			$db = new Database();

			$results = $db->select($sql);
			
			if (empty($results)) {

        throw new \Exception("Nenhuma tecnologia encontrada.", HTTPStatus::NO_CONTENT);
        
      }
      
      return ApiResponseFormatter::formatResponse(
        HTTPStatus::OK, 
        "success", 
        "Lista de tecnologias",
        $results
      );

		} catch (\Exception $e) {

			return ApiResponseFormatter::formatResponse(
        $e->getCode(), 
        "error", 
        "Falha ao obter tecnologias: " . $e->getMessage(),
        null
      );
			
		}

	}

  public static function get($idtechnology)
	{

    $sql = "SELECT * FROM tb_technologies WHERE idtechnology = :idtechnology";

		try {

			$db = new Database();

			$results = $db->select($sql, array(
				":idtechnology"=>$idtechnology
			));

      if (empty($results)) {
			
        throw new \Exception("Tecnologia não encontrada.", HTTPStatus::NOT_FOUND);
        
      }
      
      return ApiResponseFormatter::formatResponse(
        HTTPStatus::OK, 
        "success", 
        "Detalhes da tecnologia",
        $results[0]
      );

		} catch (\Exception $e) {
			
			return ApiResponseFormatter::formatResponse(
        $e->getCode(), 
        "error", 
        "Falha ao obter tecnologia: " . $e->getMessage(),
        null
      );
			
		}

  }

  public static function getPage($page = 1, $itemsPerPage = 5)
	{

    $start = ($page - 1) * $itemsPerPage;
		
		$sql = "SELECT SQL_CALC_FOUND_ROWS * 
            FROM tb_technologies 
            ORDER BY desname 
            LIMIT $start, $itemsPerPage";		
		
		try {

			$db = new Database();

			$results = $db->select($sql);

      $resultsTotal = $db->select("SELECT FOUND_ROWS() AS nrtotal");
			
			if (empty($results)) {

        throw new \Exception("Nenhuma tecnologia encontrada.", HTTPStatus::NO_CONTENT);
        
      } 

      return ApiResponseFormatter::formatResponse(
        HTTPStatus::OK, 
        "success", 
        "Lista de tecnologias",
        [
          "technologies" => $results,
          "total" => (int)$resultsTotal[0]["nrtotal"],
          "pages" => ceil($resultsTotal[0]["nrtotal"] / $itemsPerPage)
        ]
      );

		} catch (\Exception $e) {

			return ApiResponseFormatter::formatResponse(
        $e->getCode(), 
        "error", 
        "Falha ao obter projetos: " . $e->getMessage(),
        null
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
        "Tecnologia excluída com sucesso",
        null
      );

		} catch (\PDOException $e) {

			return ApiResponseFormatter::formatResponse(
        HTTPStatus::INTERNAL_SERVER_ERROR, 
        "error", 
        "Falha ao excluir tecnologia: " . $e->getMessage(),
        null
      );
			
		}

  }

  private function checkTechnologyExists($name, $idtechnology = null) 
  {

    $sql = "SELECT * FROM tb_technologies WHERE desname = :desname";

    		
    if ($idtechnology) {

      $sql .= " AND idtechnology != :idtechnology";

    }
    
    try {

      $db = new Database();
      
      $params = [":desname" => $name];
      
      if ($idtechnology) {

        $params[":idtechnology"] = $idtechnology;

      }
      
      $results = $db->select($sql, $params);
      
      if (count($results) > 0) {

        throw new \Exception("Uma tecnologia com este nome já foi cadastrada.", HTTPStatus::CONFLICT);

      }

    } catch (\Exception $e) {

      throw new \Exception($e->getMessage(), $e->getCode());

    }

  }

  private function setPhoto($idtechnology, $image)
  {
    
    $photoUploaded = UploadHandler::uploadPhoto($idtechnology, $image, "technologies");

    if (!$photoUploaded) {
      
      return null;      

    }

    $imageUrl = $_ENV['API_URL']."/images/technologies/".$idtechnology.".jpg";
      
    $sql = "UPDATE tb_technologies SET desimage = :desimage WHERE idtechnology = :idtechnology";
    
    try {

      $db = new Database();

      $db->query($sql, array(
        ':desimage'     => $imageUrl,
        ':idtechnology' => $idtechnology
      ));

      return $imageUrl;

    } catch (\PDOException $e) {

      return null;

    }

  }

}
