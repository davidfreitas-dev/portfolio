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
  "path" => "/",
  "secure" => "false",
  "ignore" => ["/signin", "/signup", "/forgot", "/forgot/token", "/forgot/reset", "/images"],
  "secret" => $_ENV['JWT_SECRET_KEY'],
  "algorithm" => "HS256",
  "error" => function ($response, $arguments) {
    $data["status"] = "error";
    $data["message"] = $arguments["message"];
    $response->getBody()->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
    return $response->withHeader('content-type', 'application/json');
  }
]));

$app->get('/images/{folder}/{image}', function (Request $request, Response $response, array $args) {

  $imageDirectoryPath = '/../res/img/';

  $imagePath = __DIR__ . $imageDirectoryPath . $args['folder'] . '/' . $args['image'];

  if (!file_exists($imagePath)) {

    $imagePath = __DIR__ . $imageDirectoryPath . '/no-image.png';

  }

  $image = file_get_contents($imagePath);

  $response->getBody()->write($image);

  return $response->withHeader('Content-Type', 'image/jpeg');

});

require_once('auth.php');
require_once('user.php');
require_once('project.php');
require_once('technology.php');
require_once('experience.php');

$app->run();