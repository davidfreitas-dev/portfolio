<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Selective\BasePath\BasePathMiddleware;
use Slim\Factory\AppFactory;

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');

$dotenv->load();

$app = AppFactory::create();

$app->addBodyParsingMiddleware();

$app->addRoutingMiddleware();

$app->add(new BasePathMiddleware($app));

$app->addErrorMiddleware(true, true, true);

$app->add(new Tuupola\Middleware\JwtAuthentication([
  "header" => "X-Token",
  "regexp" => "/(.*)/",
  "path" => "/api",
  "secure" => "false",
  "ignore" => [
    "/api/signin", 
    "/api/signup", 
    "/api/forgot", 
    "/api/forgot/token", 
    "/api/forgot/reset", 
    "/api/images", 
    "/api/experiences($|/)",
    "/api/technologies($|/)",
    "/api/projects($|/)",
    "/api/($|/)"
  ],
  "secret" => $_ENV['JWT_SECRET_KEY'],
  "algorithm" => "HS256",
  "attribute" => "jwt",
  "error" => function ($response, $arguments) {
    $data["status"] = "error";
    $data["message"] = $arguments["message"];
    $response->getBody()->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    return $response->withHeader('content-type', 'application/json');
  }
]));

$app->group('/api', function () use ($app) {
  $app->get('/', function (Request $request, Response $response) {
    $response->getBody()->write(json_encode([
      'message' => 'Welcome to the Personal Portfolio Site API!'
    ]));    
    return $response->withHeader('content-type', 'application/json');
  });

  require_once __DIR__ . '/../src/Routes/auth.php';
  require_once __DIR__ . '/../src/Routes/experience.php';
  require_once __DIR__ . '/../src/Routes/image.php';
  require_once __DIR__ . '/../src/Routes/project.php';
  require_once __DIR__ . '/../src/Routes/technology.php';
  require_once __DIR__ . '/../src/Routes/user.php';
});

$app->run();