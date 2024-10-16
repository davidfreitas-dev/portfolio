<?php

namespace App\Utils;

class UploadHandler
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

      $dist = $_ENV['STORAGE_PATH'] . DIRECTORY_SEPARATOR . $directory;

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

  public static function deletePhoto($id, $directory)
  {

    $dist = $_ENV['STORAGE_PATH'] . DIRECTORY_SEPARATOR . $directory . DIRECTORY_SEPARATOR . $id . ".jpg";

    if (file_exists($dist)) {

      unlink($dist);

    }
    
  }

}
