<?php

declare(strict_types=1);

use Dotenv\Dotenv;
use Selective\BasePath\BasePathMiddleware;
use Slim\Factory\AppFactory;

if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

require APP_ROOT . '/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv::createImmutable(APP_ROOT);
$dotenv->load();

// Set up dependency injection container
$container = require __DIR__ . '/container.php';

// Instantiate the app
AppFactory::setContainer($container);
$app = AppFactory::create();

// Register middleware
$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$app->add(new BasePathMiddleware($app));

$settings = $container->get('settings');

// CORS Middleware
$app->add($container->get(\App\Infrastructure\Http\Middleware\CorsMiddleware::class));

// JWT Middleware
$app->add($container->get(\App\Infrastructure\Http\Middleware\JwtAuthMiddleware::class));

// Rate Limit Middleware
$app->add($container->get(\App\Infrastructure\Http\Middleware\RateLimitMiddleware::class));

// Error Middleware
$app->add($container->get(\App\Infrastructure\Http\Middleware\ErrorMiddleware::class));

// Register routes
(require __DIR__ . '/routes.php')($app);

return $app;
