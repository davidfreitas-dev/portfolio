<?php

namespace App\Models;

use App\DB\Database;
use App\Enums\HttpStatus as HTTPStatus;
use App\Utils\ApiResponseFormatter;

class Project
{

  public static function list()
  {

    $sql = "SELECT * FROM tb_projects
            ORDER BY idproject DESC";
		
		try {

			$db = new Database();

			$results = $db->select($sql);
			
			if (count($results)) {

				return ApiResponseFormatter::formatResponse(
          HTTPStatus::OK, 
          "success", 
          "Lista de projetos",
          $results
        );

			}
      
      return ApiResponseFormatter::formatResponse(
        HTTPStatus::NO_CONTENT,
        "success", 
        "Nenhum projeto encontrado",
        null
      );

		} catch (\PDOException $e) {

			return ApiResponseFormatter::formatResponse(
        HTTPStatus::INTERNAL_SERVER_ERROR, 
        "error", 
        "Falha ao obter projetos: " . $e->getMessage(),
        null
      );
			
		}

  }

  public static function get($idproject)
	{

    $sql = "SELECT * FROM tb_projects
            WHERE idproject = :idproject";

		try {

			$db = new Database();

			$results = $db->select($sql, array(
				":idproject"=>$idproject
			));

      if (count($results)) {
			
			  return ApiResponseFormatter::formatResponse(
          HTTPStatus::OK, 
          "success", 
          "Detalhes do projeto",
          $results[0]
        );
        
      }

			return ApiResponseFormatter::formatResponse(
        HTTPStatus::NOT_FOUND,
        "error", 
        "Projeto não encontrado",
        null
      );

		} catch (\PDOException $e) {
			
			return ApiResponseFormatter::formatResponse(
        HTTPStatus::INTERNAL_SERVER_ERROR, 
        "error", 
        "Falha ao obter projeto: " . $e->getMessage(),
        null
      );
			
		}

  }

  public static function getPage($page = 1, $itemsPerPage = 5)
	{

    $start = ($page - 1) * $itemsPerPage;
		
		$sql = "SELECT 
                p.*, 
                GROUP_CONCAT(pt.idtechnology) as technologies
            FROM tb_projects p
            LEFT JOIN tb_projectstechnologies pt ON p.idproject = pt.idproject
            GROUP BY p.idproject
            ORDER BY p.dtregister 
            LIMIT $start, $itemsPerPage";
		
		try {

			$db = new Database();

			$results = $db->select($sql);

      $resultsTotal = $db->select("SELECT FOUND_ROWS() AS nrtotal");
			
			if (empty($results)) {

        return ApiResponseFormatter::formatResponse(
          HTTPStatus::NO_CONTENT,
          "success", 
          "Nenhum projeto encontrado",
          null
        );
        
      } 

      return ApiResponseFormatter::formatResponse(
        HTTPStatus::OK, 
        "success", 
        "Lista de projetos",
        [
          "projects" => $results,
          "total" => (int)$resultsTotal[0]["nrtotal"],
          "pages" => ceil($resultsTotal[0]["nrtotal"] / $itemsPerPage)
        ]
      );

		} catch (\PDOException $e) {

			return ApiResponseFormatter::formatResponse(
        HTTPStatus::INTERNAL_SERVER_ERROR, 
        "error", 
        "Falha ao obter projetos: " . $e->getMessage(),
        null
      );
			
		}		

	}

  public static function save($project)
  {

    $sql = "CALL sp_projects_create(
      :idproject, 
      :destitle, 
      :desdescription, 
      :technologies
    )";

    $idproject = isset($project['idproject']) ? $project['idproject'] : 0;

    try {
      
      $db = new Database();

			$results = $db->select($sql, array(
				":idproject"=>$idproject,
				":destitle"=>$project['destitle'],
				":desdescription"=>$project['desdescription'],
				":technologies"=>$project['technologies']
			));

      if (empty($results)) {
        
        return ApiResponseFormatter::formatResponse(
          HTTPStatus::BAD_REQUEST,
          "error", 
          "Falha ao criar/atualizar projeto",
          null
        );

      }

      if (!is_string($project['desimage'])) {

        Project::uploadPhoto($results[0]['idproject'], $project['desimage']);
  
      }

      $code  = $idproject ? 200 : 201;

      $message = $idproject ? "Projeto atualizado com sucesso" : "Projeto criado com sucesso";

      return ApiResponseFormatter::formatResponse(
        $code, 
        "success", 
        $message,
        $results[0]
      );

    } catch (\PDOException $e) {
			
			return ApiResponseFormatter::formatResponse(
        HTTPStatus::INTERNAL_SERVER_ERROR, 
        "error", 
        "Falha ao criar/atualizar projeto: " . $e->getMessage(),
        null
      );
			
		}

  }

  public static function delete($idproject) 
	{

    $sql = "CALL sp_projects_delete(:idproject)";
		
		try {

			$db = new Database();
			
			$db->query($sql, array(
				':idproject'=>$idproject
			));

      return ApiResponseFormatter::formatResponse(
        HTTPStatus::OK, 
        "success", 
        "Projeto excluído com sucesso",
        null
      );

		} catch (\PDOException $e) {

			return ApiResponseFormatter::formatResponse(
        HTTPStatus::INTERNAL_SERVER_ERROR, 
        "error", 
        "Falha ao excluir projeto: " . $e->getMessage(),
        null
      );
			
		}

  }

  private static function setPhoto($idproject) 
	{

    $imageUrl = $_ENV['API_URL']."/images/projects/".$idproject.".jpg";

    $sql = "UPDATE tb_projects
            SET desimage = :desimage
            WHERE idproject = :idproject";
		
		try {

			$db = new Database();
			
			$db->query($sql, array(
				':idproject'=>$idproject,
				':desimage'=>$imageUrl
			));

		} catch (\PDOException $e) {

			return ApiResponseFormatter::formatResponse(
        HTTPStatus::INTERNAL_SERVER_ERROR, 
        "error", 
        "Falha ao definir imagem do projeto: " . $e->getMessage(),
        null
      );
			
		}

  }

  private static function uploadPhoto($idproject, $file)
	{

		try {

      if (isset($file['name'])) {

        $extension = explode('.', $file['name']);
  
        $extension = end($extension);

        switch ($extension) {

          case "jpg":
          case "jpeg":
          $image = imagecreatefromjpeg($file["tmp_name"]);
          break;
    
          case "gif":
          $image = imagecreatefromgif($file["tmp_name"]);
          break;
    
          case "png":
          $image = imagecreatefrompng($file["tmp_name"]);
          break;
    
        }

        $dist = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 
              "res" . DIRECTORY_SEPARATOR . 
              "img" . DIRECTORY_SEPARATOR . 
              "projects";

        if (!is_dir($dist)) {

            mkdir($dist, 0777, true);

        }

        $dist = $dist . DIRECTORY_SEPARATOR . $idproject . ".jpg";

        imagejpeg($image, $dist);

        imagedestroy($image);

        Project::setPhoto($idproject);

      }

    } catch (\Throwable $e) {

      return ApiResponseFormatter::formatResponse(
        HTTPStatus::INTERNAL_SERVER_ERROR, 
        "error", 
        "Erro: " . $e->getMessage(),
        null
      );
      
    }	

	}

}
