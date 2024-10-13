<?php

namespace App\Models;

use App\DB\Database;
use App\Utils\UploadHandler;
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
                GROUP_CONCAT(
                    DISTINCT CONCAT(
                        '{\"idtechnology\":', t.idtechnology, ',\"desname\":\"', t.desname, '\"}'
                    ) SEPARATOR ','
                ) AS technologies
            FROM tb_projects p
            LEFT JOIN tb_projectstechnologies pt ON p.idproject = pt.idproject
            LEFT JOIN tb_technologies t ON pt.idtechnology = t.idtechnology
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
        
        foreach ($results as &$result) {
            $result['technologies'] = $result['technologies']
                ? json_decode('[' . $result['technologies'] . ']', true)
                : [];
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
      :technologies,
      :deslink
    )";

    $idproject = isset($project['idproject']) ? $project['idproject'] : 0;

    try {
      
      $db = new Database();

			$results = $db->select($sql, array(
				":idproject"=>$idproject,
				":destitle"=>$project['destitle'],
				":desdescription"=>$project['desdescription'],
        ":deslink"=> $project['deslink'],
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

        $photoUploaded = UploadHandler::uploadPhoto($results[0]['idproject'], $project['image'], "projects");
  
        if ($photoUploaded) {
          
          $imageUrl = self::setPhoto($results[0]['idproject']);

          $results[0]['image'] = $imageUrl;

        }

      }

      $code = $project['idcategory'] ? HTTPStatus::OK : HTTPStatus::CREATED;

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

      $imagePath = $_SERVER['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . 
                    "res" . DIRECTORY_SEPARATOR . 
                    "img" . DIRECTORY_SEPARATOR . 
                    "projects" . DIRECTORY_SEPARATOR . 
                    $idproject . ".jpg";

      if (file_exists($imagePath)) {

        unlink($imagePath);

      }

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

      return $imageUrl;

		} catch (\PDOException $e) {

			return null;
			
		}

  }

}
