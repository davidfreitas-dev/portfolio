<?php

namespace App\Models;

use App\DB\Database;
use App\Models\Model;
use App\Utils\UploadHandler;
use App\Enums\HttpStatus as HTTPStatus;
use App\Utils\ApiResponseFormatter;

class Project extends Model
{

  public function save()
  {

    $sql = "CALL sp_projects_save(
      :idproject, 
      :destitle, 
      :desdescription, 
      :technologies,
      :deslink
    )";

    try {
      
      $db = new Database();

			$results = $db->select($sql, array(
				":idproject"      => $this->getidproject() ?? 0,
				":destitle"       => $this->getdestitle(),
				":desdescription" => $this->getdesdescription(),
        ":deslink"        => $this->getdeslink(),
				":technologies"   => $this->gettechnologies()
			));

      if (empty($results)) {
        
        throw new \Exception("Falha ao criar/atualizar projeto", HTTPStatus::BAD_REQUEST);
        
      }

      if (NULL !== $this->getdesimage() && !is_string($this->getdesimage())) {

        $imageUrl = $this->setPhoto($results[0]['idproject'], $this->getdesimage());
  
        if ($imageUrl) {

          $results[0]['desimage'] = $imageUrl;

        }

      }
      
      return ApiResponseFormatter::formatResponse(
        HTTPStatus::OK, 
        "success", 
        "Projeto criado/atualizado com sucesso",
        $results[0]
      );

    } catch (\Exception $e) {
			
			return ApiResponseFormatter::formatResponse(
        HTTPStatus::INTERNAL_SERVER_ERROR, 
        "error", 
        "Falha ao criar/atualizar projeto: " . $e->getMessage(),
        null
      );
			
		}

  }

  public static function list()
  {

    $sql = "SELECT 
              p.*, 
              GROUP_CONCAT(
                DISTINCT CONCAT(
                  '{\"idtechnology\":', t.idtechnology, 
                  ',\"desname\":\"', t.desname, 
                  ',\"desimage\":\"', t.desimage, '\"}'
                ) SEPARATOR ','
              ) AS technologies
            FROM tb_projects p
            LEFT JOIN tb_projectstechnologies pt ON p.idproject = pt.idproject
            LEFT JOIN tb_technologies t ON pt.idtechnology = t.idtechnology
            GROUP BY p.idproject
            ORDER BY p.dtregister";
		
		try {

			$db = new Database();

			$results = $db->select($sql);
			
			if (empty($results)) {

				return ApiResponseFormatter::formatResponse(
          HTTPStatus::NO_CONTENT, 
          "success", 
          "Nenhum projeto encontrado.",
          NULL
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
        $results
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

  public static function getPage($page = 1, $itemsPerPage = 5)
  {

    $start = ($page - 1) * $itemsPerPage;

    $sql = "SELECT 
              p.*, 
              GROUP_CONCAT(
                DISTINCT CONCAT(
                  '{\"idtechnology\":', t.idtechnology, 
                  ',\"desname\":\"', t.desname, 
                  ',\"desimage\":\"', t.desimage, '\"}'
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
            "Nenhum projeto encontrado.",
            NULL
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

    } catch (\Exception $e) {

      return ApiResponseFormatter::formatResponse(
        $e->getCode(),
        "error",
        "Falha ao obter projetos: " . $e->getMessage(),
        null
      );
        
    }
  }

  public static function get($idproject)
	{

    $sql = "SELECT 
              p.*, 
              GROUP_CONCAT(
                DISTINCT CONCAT(
                  '{\"idtechnology\":', t.idtechnology, 
                  ',\"desname\":\"', t.desname, 
                  ',\"desimage\":\"', t.desimage, '\"}'
                ) SEPARATOR ','
              ) AS technologies
            FROM tb_projects p
            LEFT JOIN tb_projectstechnologies pt ON p.idproject = pt.idproject
            LEFT JOIN tb_technologies t ON pt.idtechnology = t.idtechnology
            WHERE p.idproject = :idproject";

		try {

			$db = new Database();

			$results = $db->select($sql, array(
				":idproject"=>$idproject
			));

      if (empty($results)) {
			
			  return ApiResponseFormatter::formatResponse(
          HTTPStatus::NOT_FOUND, 
          "success", 
          "Projeto não encontrado.",
          NULL
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
        "Detalhes do projeto",
        $results[0]
      );

		} catch (\Exception $e) {

			return ApiResponseFormatter::formatResponse(
        $e->getCode(), 
        "error", 
        "Falha ao obter projeto: " . $e->getMessage(),
        null
      );
			
		}

  }

  public static function delete($idproject) 
	{

    $sql = "DELETE FROM tb_projects WHERE idproject = :idproject";
		
		try {

			$db = new Database();
			
			$db->query($sql, array(
				':idproject'=>$idproject
			));

      UploadHandler::deletePhoto($idproject, "projects");

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

  private function setPhoto($idproject, $image)
  {

    $photoUploaded = UploadHandler::uploadPhoto($idproject, $image, "projects");

    if (!$photoUploaded) {

      return null;
      
    }

    $imageUrl = $_ENV['API_URL'] . "/images/projects/" . $idproject . ".jpg";

    $sql = "UPDATE tb_projects SET desimage = :desimage WHERE idproject = :idproject";
    
    try {
  
      $db = new Database();

      $db->query($sql, array(
        ':desimage'  => $imageUrl,
        ':idproject' => $idproject
      ));

      return $imageUrl;

    } catch (\PDOException $e) {
  
      return null;
    
    }

  }


}
