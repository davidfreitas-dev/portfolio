<?php

namespace App\Utils;

use App\Utils\ApiResponseFormatter;
use App\Enums\HttpStatus as HTTPStatus;

class uploadHandler
{

  public static function uploadPhoto($id, $file, $directory)
	{

		if (isset($file['name'])) {

      $extension = pathinfo($file['name'], PATHINFO_EXTENSION);

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
            "storage" . DIRECTORY_SEPARATOR .
            $directory;

      if (!is_dir($dist)) {

          mkdir($dist, 0777, true);

      }

      $dist = $dist . DIRECTORY_SEPARATOR . $id . ".jpg";

      imagejpeg($image, $dist);

      imagedestroy($image);

      return true;

    }

    return false;

	}

}
