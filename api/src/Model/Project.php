<?php

namespace App\Model;

use App\DB\Database;
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
          200, 
          "success", 
          $results
        );

			}
      
      return ApiResponseFormatter::formatResponse(
        204, 
        "success", 
        "Nenhum projeto encontrado"
      );

		} catch (\PDOException $e) {

			return ApiResponseFormatter::formatResponse(
        500, 
        "error", 
        "Falha ao obter projetos: " . $e->getMessage()
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
          200, 
          "success", 
          $results[0]
        );
        
      }

			return ApiResponseFormatter::formatResponse(
        404, 
        "error", 
        "Projeto não encontrada"
      );

		} catch (\PDOException $e) {
			
			return ApiResponseFormatter::formatResponse(
        500, 
        "error", 
        "Falha ao obter projeto: " . $e->getMessage()
      );
			
		}

  }

  public static function save($project)
  {

    $sql = "CALL sp_projects_create(
      :idproject, 
      :destitle, 
      :desdescription, 
      :desimage, 
      :technologies
    )";

    try {
      
      $db = new Database();

			$results = $db->select($sql, array(
				":idproject"=>$project['idproject'],
				":destitle"=>$project['destitle'],
				":desdescription"=>$project['desdescription'],
				":desimage"=>$_ENV['BASE_URL']."/images/projects/".$project['idproject'].".jpg",
				":technologies"=>$project['technologies']
			));

      if (empty($results)) {
        
        return ApiResponseFormatter::formatResponse(
          400, 
          "error", 
          "Falha ao criar/atualizar projeto"
        );

      }

      Project::uploadPhoto($results[0]['idproject'], $project['image']);

      $status  = $project['idproject'] ? 200 : 201;

      $message = $project['idproject'] ? "Projeto atualizado com sucesso" : "Projeto criado com sucesso";

      return ApiResponseFormatter::formatResponse(
        $status, 
        "success", 
        $message
      );

    } catch (\PDOException $e) {
			
			return ApiResponseFormatter::formatResponse(
        500, 
        "error", 
        "Falha ao criar projeto: " . $e->getMessage()
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
        200, 
        "success", 
        "Projeto excluído com sucesso!"
      );

		} catch (\PDOException $e) {

			return ApiResponseFormatter::formatResponse(
        500, 
        "error", 
        "Falha ao excluir projeto: " . $e->getMessage()
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
              "projects" . DIRECTORY_SEPARATOR . 
              $idproject . ".jpg";

        imagejpeg($image, $dist);

        imagedestroy($image);

      }

    } catch (\Throwable $e) {

      return ApiResponseFormatter::formatResponse(
        500, 
        "error", 
        "Erro: " . $e->getMessage()
      );
      
    }	

	}

}
