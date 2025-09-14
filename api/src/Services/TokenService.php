<?php

namespace App\Services;

class TokenService
{

  private static function generateToken($payload)
  {

    $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
    
    $payload = json_encode($payload);

    $header = self::base64UrlEncode($header);
    
    $payload = self::base64UrlEncode($payload);

    $sign = hash_hmac('sha256', $header . "." . $payload, $_ENV['JWT_SECRET_KEY'], true);
    
    $sign = self::base64UrlEncode($sign);

    return $header . '.' . $payload . '.' . $sign;

  }
  
  private static function base64UrlEncode($data)
  {
      
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    
  }
  
  public static function generatePublicToken() 
  {
    
    $payload = [ 
      "sub"  => "site-client",
      "user" => [
        "name"  => "Guest User",
        "roles" => [
          ["name" => "public"]
        ]
      ], 
      "iat" => time(), 
      "exp" => time() + 3600 
    ];
    
    return self::generateToken($payload);
  
  }

  public static function generatePrivateToken($data) {
    
    $payload = [
      "sub" => "user-client",
      "user" => [
        "id" => $data['id'],
        "name" => $data['name'],
        "roles" => $data['roles'] 
      ],
      "iat" => time(),
      "exp" => time() + 3600
    ];

    return self::generateToken($payload);

  }

}
