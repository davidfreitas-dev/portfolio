<?php 

namespace App\Utils;

class ApiResponseFormatter {

	public static function formatResponse($code, $status, $message, $data)
	{

		return array(
      "code" => $code,
      "status" => $status,
      "message" => $message,
      "data" => $data
    );

	}
    
}
