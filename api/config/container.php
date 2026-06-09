<?php

declare(strict_types=1);

use App\Application\Service\AuthService;
use App\Application\Service\ContactService;
use App\Application\Service\ErrorLoggerService;
use App\Application\Service\ExperienceService;
use App\Application\Service\FileUploaderService;
use App\Application\Service\JwtService;
use App\Application\Service\MailService;
use App\Application\Service\ProjectService;
use App\Application\Service\TechnologyService;
use App\Application\Service\UserService;
use App\Domain\Contract\ErrorLogRepositoryInterface;
use App\Domain\Contract\ExperienceRepositoryInterface;
use App\Domain\Contract\MailerInterface;
use App\Domain\Contract\OtpRepositoryInterface;
use App\Domain\Contract\ProjectRepositoryInterface;
use App\Domain\Contract\TechnologyRepositoryInterface;
use App\Domain\Contract\UserRepositoryInterface;
use App\Infrastructure\Http\Middleware\CorsMiddleware;
use App\Infrastructure\Http\Middleware\ErrorMiddleware;
use App\Infrastructure\Http\Middleware\HttpCacheInvalidationMiddleware;
use App\Infrastructure\Http\Middleware\HttpCacheMiddleware;
use App\Infrastructure\Http\Middleware\JwtAuthMiddleware;
use App\Infrastructure\Http\Middleware\RateLimitMiddleware;
use App\Infrastructure\Mailer\PHPMailerService;
use App\Infrastructure\Persistence\Database;
use App\Infrastructure\Persistence\ErrorLogRepository;
use App\Infrastructure\Persistence\ExperienceRepository;
use App\Infrastructure\Persistence\OtpRepository;
use App\Infrastructure\Persistence\ProjectRepository;
use App\Infrastructure\Persistence\Redis\RedisCache;
use App\Infrastructure\Persistence\TechnologyRepository;
use App\Infrastructure\Persistence\UserRepository;
use App\Presentation\Responder\JsonResponder;
use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

$containerBuilder = new ContainerBuilder();

// Set up settings
$containerBuilder->addDefinitions(require __DIR__ . '/settings.php');

// Add service and middleware definitions
$containerBuilder->addDefinitions([
    Database::class => function (ContainerInterface $c): \App\Infrastructure\Persistence\Database {
        $dbSettings = $c->get('settings')['db'];
        return new Database(
            $dbSettings['host'],
            $dbSettings['database'],
            $dbSettings['username'],
            $dbSettings['password'],
        );
    },

    LoggerInterface::class => function (ContainerInterface $c): \Monolog\Logger {
        $logger = new Logger('app');
        $processor = new UidProcessor();
        $logger->pushProcessor($processor);
        $handler = new StreamHandler(__DIR__ . '/../logs/app.log', Logger::DEBUG);
        $logger->pushHandler($handler);
        return $logger;
    },

    Redis::class => function (ContainerInterface $c): \Redis {
        $settings = $c->get('settings')['redis'];
        $redis = new Redis();
        try {
            $redis->connect($settings['host'], (int)$settings['port'], 2.0);
            if (!empty($settings['password'])) {
                $redis->auth($settings['password']);
            }
        } catch (\Exception $e) {
            throw new \RuntimeException(sprintf(
                "FALHA NA CONEXÃO COM REDIS: %s. Verifique se o servidor Redis está rodando e se as configurações em .env (REDIS_HOST=%s, REDIS_PORT=%s) estão corretas.",
                $e->getMessage(),
                $settings['host'],
                $settings['port'],
            ), $e->getCode(), $e);
        }
        return $redis;
    },

    RedisCache::class => fn (ContainerInterface $c): \App\Infrastructure\Persistence\Redis\RedisCache => new RedisCache($c->get(Redis::class)),

    UserRepositoryInterface::class => fn (ContainerInterface $c): \App\Infrastructure\Persistence\UserRepository => new UserRepository($c->get(Database::class)),

    ProjectRepositoryInterface::class => fn (ContainerInterface $c): \App\Infrastructure\Persistence\ProjectRepository => new ProjectRepository($c->get(Database::class)),

    ProjectService::class => fn (ContainerInterface $c): \App\Application\Service\ProjectService => new ProjectService(
        $c->get(ProjectRepositoryInterface::class),
        $c->get(ValidatorInterface::class),
        $c->get(FileUploaderService::class),
    ),

    ExperienceRepositoryInterface::class => fn (ContainerInterface $c): \App\Infrastructure\Persistence\ExperienceRepository => new ExperienceRepository($c->get(Database::class)),

    ExperienceService::class => fn (ContainerInterface $c): \App\Application\Service\ExperienceService => new ExperienceService(
        $c->get(ExperienceRepositoryInterface::class),
        $c->get(ValidatorInterface::class),
    ),

    TechnologyRepositoryInterface::class => fn (ContainerInterface $c): \App\Infrastructure\Persistence\TechnologyRepository => new TechnologyRepository($c->get(Database::class)),

    TechnologyService::class => fn (ContainerInterface $c): \App\Application\Service\TechnologyService => new TechnologyService(
        $c->get(TechnologyRepositoryInterface::class),
        $c->get(ValidatorInterface::class),
        $c->get(FileUploaderService::class),
    ),

    ErrorLogRepositoryInterface::class => fn (ContainerInterface $c): \App\Infrastructure\Persistence\ErrorLogRepository => new ErrorLogRepository($c->get(Database::class)),

    ErrorLoggerService::class => fn (ContainerInterface $c): \App\Application\Service\ErrorLoggerService => new ErrorLoggerService($c->get(ErrorLogRepositoryInterface::class)),

    OtpRepositoryInterface::class => fn (ContainerInterface $c): \App\Infrastructure\Persistence\OtpRepository => new OtpRepository($c->get(RedisCache::class)),

    MailerInterface::class => function (ContainerInterface $c): \App\Infrastructure\Mailer\PHPMailerService {
        $settings = $c->get('settings')['mailer'];
        return new PHPMailerService(
            $c->get(LoggerInterface::class),
            [
                'host'        => $settings['host'],
                'port'        => $settings['port'],
                'username'    => $settings['username'],
                'password'    => $settings['password'],
                'from_name'   => $settings['name'],
                'from_email'  => $settings['from'],
                'smtp_secure' => $settings['encryption'],
            ],
        );
    },

    MailService::class => fn (ContainerInterface $c): \App\Application\Service\MailService => new MailService($c->get(MailerInterface::class)),

    ContactService::class => fn (ContainerInterface $c): \App\Application\Service\ContactService => new ContactService(
        $c->get(MailerInterface::class),
        $c->get(LoggerInterface::class),
        $c->get('settings')['mailer'],
    ),

    JwtService::class => function (ContainerInterface $c): \App\Application\Service\JwtService {
        $settings = $c->get('settings')['jwt'];
        return new JwtService(
            $settings['secret'],
            $settings['algorithm'],
        );
    },

    AuthService::class => fn (ContainerInterface $c): \App\Application\Service\AuthService => new AuthService(
        $c->get(UserRepositoryInterface::class),
        $c->get(OtpRepositoryInterface::class),
        $c->get(JwtService::class),
        $c->get(MailService::class),
    ),

    UserService::class => fn (ContainerInterface $c): \App\Application\Service\UserService => new UserService(
        $c->get(UserRepositoryInterface::class),
    ),

    ValidatorInterface::class => fn (): \Symfony\Component\Validator\Validator\ValidatorInterface => Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator(),

    JsonResponder::class => fn (): \App\Presentation\Responder\JsonResponder => new JsonResponder(),

    \App\Presentation\Transformer\ProjectTransformer::class => fn (ContainerInterface $c): \App\Presentation\Transformer\ProjectTransformer => new \App\Presentation\Transformer\ProjectTransformer($c->get('settings')['api_url']),

    \App\Presentation\Transformer\ExperienceTransformer::class => fn (): \App\Presentation\Transformer\ExperienceTransformer => new \App\Presentation\Transformer\ExperienceTransformer(),

    \App\Presentation\Transformer\TechnologyTransformer::class => fn (ContainerInterface $c): \App\Presentation\Transformer\TechnologyTransformer => new \App\Presentation\Transformer\TechnologyTransformer($c->get('settings')['api_url']),

    ErrorMiddleware::class => fn (ContainerInterface $c): \App\Infrastructure\Http\Middleware\ErrorMiddleware => new ErrorMiddleware(
        $c->get(JsonResponder::class),
        $c->get(LoggerInterface::class),
        (bool)$c->get('settings')['displayErrorDetails'],
    ),

    CorsMiddleware::class => fn (ContainerInterface $c): \App\Infrastructure\Http\Middleware\CorsMiddleware => new CorsMiddleware($c->get('settings')['cors']),

    JwtAuthMiddleware::class => fn (ContainerInterface $c): \App\Infrastructure\Http\Middleware\JwtAuthMiddleware => new JwtAuthMiddleware(
        $c->get(JwtService::class),
        ['/', '/health', '/images', '/auth/login', '/auth/request-login', '/auth/forgot', '/auth/validate-reset-code', '/auth/reset', '/public/contact'],
    ),

    RateLimitMiddleware::class => fn (ContainerInterface $c): \App\Infrastructure\Http\Middleware\RateLimitMiddleware => new RateLimitMiddleware(
        $c->get(RedisCache::class),
        $c->get(JwtService::class),
        $c->get('settings')['rate_limit'] ?? [],
    ),

    HttpCacheMiddleware::class => fn (ContainerInterface $c): \App\Infrastructure\Http\Middleware\HttpCacheMiddleware => new HttpCacheMiddleware($c->get(RedisCache::class)),

    HttpCacheInvalidationMiddleware::class => fn (ContainerInterface $c): \App\Infrastructure\Http\Middleware\HttpCacheInvalidationMiddleware => new HttpCacheInvalidationMiddleware($c->get(RedisCache::class)),
]);

return $containerBuilder->build();
