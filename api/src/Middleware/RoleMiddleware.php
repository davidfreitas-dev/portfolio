<?php

namespace App\Middleware;

use App\Enums\HttpStatus as HTTPStatus;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class RoleMiddleware
{

  private array $requiredRoles;
  
  /**
   * Construtor que recebe um ou mais roles permitidos.
   *
   * @param string|array $roles
   */
  public function __construct(string|array $roles)
  {
      
    $this->requiredRoles = (array)$roles;
    
  }

  public function __invoke(Request $request, RequestHandler $handler): Response
  {
    
    $jwt = $request->getAttribute("jwt");

    if (!$jwt || !isset($jwt["user"]->roles)) {
        
      return $this->deny("Acesso negado. Nenhuma role encontrada.");
      
    }

    $userRoles = array_map(fn($r) => $r->name, $jwt['user']->roles);

    $hasAccess = count(array_intersect($this->requiredRoles, $userRoles)) > 0;

    if (!$hasAccess) {
      
      return $this->deny(
        "Acesso negado. Esta ação requer uma das seguintes roles: " . implode(", ", $this->requiredRoles)
      );
      
    }

    return $handler->handle($request);
  
  }

  private function deny(string $message): Response
  {
    
    $response = new \Slim\Psr7\Response();
    
    $response->getBody()->write(json_encode([
      "status"  => "error",
      "message" => $message
    ], JSON_UNESCAPED_UNICODE));
    
    return $response
      ->withHeader("content-type", "application/json")
      ->withStatus(HTTPStatus::FORBIDDEN);

  }

}
