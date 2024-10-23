<?php

namespace App\Utils;

use App\Enums\HttpStatus as HTTPStatus;

class UploadHandler
{

  public static function uploadPhoto($id, $file, $directory)
	{

		if (isset($file['name'])) {

      $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
      
      $image = null;

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

        default:
        throw new \Exception("Extensão de arquivo inválida: $extension. São permitidas apenas as extensões: jpg, jpeg, png e gif", HTTPStatus::BAD_REQUEST);
  
      }

      if (!$image) {

        throw new \Exception("Não foi possível criar a imagem.", HTTPStatus::INTERNAL_SERVER_ERROR);
      
      }

      $dist = $_ENV['STORAGE_PATH'] . DIRECTORY_SEPARATOR . $directory;

      if (!is_dir($dist) && !mkdir($dist, 0777, true)) {
        
        throw new \Exception("Falha ao criar o diretório: $dist", HTTPStatus::INTERNAL_SERVER_ERROR);
    
      }

      self::deletePhoto($id, $directory);

      $timestamp = date('YmdHis');

      $imageName = $timestamp . $id;

      $dist = $dist . DIRECTORY_SEPARATOR . $imageName . ".jpg";

      imagejpeg($image, $dist);

      imagedestroy($image);

      return $imageName;

    }

    return null;

	}

  public static function deletePhoto($id, $directory)
  {

    $dist = $_ENV['STORAGE_PATH'] . DIRECTORY_SEPARATOR . $directory;

    $existingFileName = self::checkExistingPhoto($id, $dist);
    
    $imagePath = $dist . DIRECTORY_SEPARATOR . $existingFileName;

    if (file_exists($imagePath)) {

      unlink($imagePath);

    }
    
  }

  private static function checkExistingPhoto($id, $dist)
  {
    
    $files = scandir($dist);
    
    foreach ($files as $file) {
        
      if (preg_match('/^\d{14}' . preg_quote($id) . '\.jpg$/', $file)) {
          
        return $file; 

      }
        
    }

    return null;

  }


}
