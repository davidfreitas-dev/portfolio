<?php

namespace App\Handlers;

use App\Utils\ApiResponseFormatter;
use Slim\Psr7\Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Throwable;

class ErrorHandler
{

  public function __invoke(
    Request $request,
    Throwable $exception,
    bool $displayErrorDetails,
    bool $logErrors,
    bool $logErrorDetails
  ): Response {

    $statusCode = $this->normalizeStatusCode($exception->getCode());

    $apiResponse = ApiResponseFormatter::formatResponse(
      $statusCode,
      "error",
      $exception->getMessage(),
      NULL
    );

    $response = new Response();
    
    $response->getBody()->write(json_encode($apiResponse, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

    return $this->addCorsHeaders($response)
                ->withStatus($statusCode)
                ->withHeader("Content-Type", "application/json");
  }

  private function normalizeStatusCode(int $code): int
  {
      
    return ($code >= 100 && $code < 600) ? $code : 500;
    
  }

  private function addCorsHeaders(Response $response): Response
  {
      
    return $response
      ->withHeader("Access-Control-Allow-Origin", $_ENV['SITE_URL'])
      ->withHeader("Access-Control-Allow-Headers", "Authorization, Content-Type, Accept, Origin, X-Requested-With")
      ->withHeader("Access-Control-Allow-Methods", "GET, POST, PUT, PATCH, DELETE, OPTIONS")
      ->withHeader("Access-Control-Allow-Credentials", "true");
    
  }

}
