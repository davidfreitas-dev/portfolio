<?php

declare(strict_types=1);

namespace Tests;

use App\Application\Service\JwtService;
use DI\Container;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\StreamFactory;

abstract class AppTestCase extends BaseTestCase
{
    protected ?App $app = null;
    protected ?Container $container = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->app = $this->getAppInstance();
        $this->container = $this->app->getContainer();
    }

    protected function tearDown(): void
    {
        $this->app = null;
        $this->container = null;
        parent::tearDown();
    }

    protected function getAppInstance(): App
    {
        // Forçar ambiente de teste
        $_ENV['APP_ENV'] = 'testing';
        
        // Carregar a aplicação
        return require __DIR__ . '/../config/bootstrap.php';
    }

    protected function createRequest(
        string $method,
        string $path,
        array $headers = ['Content-Type' => 'application/json'],
        array $cookies = [],
        array $serverParams = []
    ): ServerRequestInterface {
        $factory = new ServerRequestFactory();
        $request = $factory->createServerRequest($method, $path, $serverParams)
            ->withCookieParams($cookies);

        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        return $request;
    }

    protected function createJsonRequest(
        string $method,
        string $path,
        array $data = [],
        array $headers = ['Content-Type' => 'application/json']
    ): ServerRequestInterface {
        $request = $this->createRequest($method, $path, $headers);
        
        if (!empty($data)) {
            $stream = (new StreamFactory())->createStream(json_encode($data));
            $request = $request->withBody($stream);
        }

        return $request;
    }

    protected function request(ServerRequestInterface $request): ResponseInterface
    {
        return $this->app->handle($request);
    }

    protected function createAdminToken(): string
    {
        /** @var JwtService $jwtService */
        $jwtService = $this->container->get(JwtService::class);
        
        return $jwtService->createToken([
            'id' => 1,
            'name' => 'Admin Test',
            'email' => 'admin@test.com',
            'role_name' => 'admin'
        ]);
    }

    protected function withAdminToken(ServerRequestInterface $request): ServerRequestInterface
    {
        return $request->withHeader('Authorization', 'Bearer ' . $this->createAdminToken());
    }
}
