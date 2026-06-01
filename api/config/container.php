<?php

declare(strict_types=1);

use App\Application\Service\AuthService;
use App\Application\Service\ExperienceService;
use App\Application\Service\FileUploaderService;
use App\Application\Service\JwtService;
use App\Application\Service\MailService;
use App\Application\Service\ProjectService;
use App\Application\Service\TechnologyService;
use App\Application\Service\UserService;
use App\Domain\Contract\ExperienceRepositoryInterface;
use App\Domain\Contract\MailerInterface;
use App\Domain\Contract\OtpRepositoryInterface;
use App\Domain\Contract\ProjectRepositoryInterface;
use App\Domain\Contract\TechnologyRepositoryInterface;
use App\Domain\Contract\UserRepositoryInterface;
use App\Infrastructure\Http\Middleware\CorsMiddleware;
use App\Infrastructure\Http\Middleware\ErrorMiddleware;
use App\Infrastructure\Http\Middleware\JwtAuthMiddleware;
use App\Infrastructure\Http\Middleware\RateLimitMiddleware;
use App\Infrastructure\Mailer\PHPMailerService;
use App\Infrastructure\Persistence\Database;
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
    Database::class => fn (ContainerInterface $c) => new Database(),

    LoggerInterface::class => function (ContainerInterface $c) {
        $logger = new Logger('app');
        $processor = new UidProcessor();
        $logger->pushProcessor($processor);
        $handler = new StreamHandler(__DIR__ . '/../logs/app.log', Logger::DEBUG);
        $logger->pushHandler($handler);
        return $logger;
    },

    Redis::class => function (ContainerInterface $c) {
        $settings = $c->get('settings')['redis'];
        $redis = new Redis();
        $redis->connect($settings['host'], (int)$settings['port']);
        if (!empty($settings['password'])) {
            $redis->auth($settings['password']);
        }
        return $redis;
    },

    RedisCache::class => fn (ContainerInterface $c) => new RedisCache($c->get(Redis::class)),

    UserRepositoryInterface::class => fn (ContainerInterface $c) => new UserRepository($c->get(Database::class)),

    ProjectRepositoryInterface::class => fn (ContainerInterface $c) => new ProjectRepository($c->get(Database::class)),

    ProjectService::class => fn (ContainerInterface $c) => new ProjectService(
        $c->get(ProjectRepositoryInterface::class),
        $c->get(ValidatorInterface::class),
    ),

    ExperienceRepositoryInterface::class => fn (ContainerInterface $c) => new ExperienceRepository($c->get(Database::class)),

    ExperienceService::class => fn (ContainerInterface $c) => new ExperienceService(
        $c->get(ExperienceRepositoryInterface::class),
        $c->get(ValidatorInterface::class),
    ),

    TechnologyRepositoryInterface::class => fn (ContainerInterface $c) => new TechnologyRepository($c->get(Database::class)),

    TechnologyService::class => fn (ContainerInterface $c) => new TechnologyService(
        $c->get(TechnologyRepositoryInterface::class),
        $c->get(ValidatorInterface::class),
        $c->get(FileUploaderService::class),
    ),

    OtpRepositoryInterface::class => fn (ContainerInterface $c) => new OtpRepository($c->get(RedisCache::class)),

    MailerInterface::class => function (ContainerInterface $c) {
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

    MailService::class => fn (ContainerInterface $c) => new MailService($c->get(MailerInterface::class)),

    JwtService::class => function (ContainerInterface $c) {
        $settings = $c->get('settings')['jwt'];
        return new JwtService(
            $settings['secret'],
            $settings['algorithm'],
        );
    },

    AuthService::class => fn (ContainerInterface $c) => new AuthService(
        $c->get(UserRepositoryInterface::class),
        $c->get(OtpRepositoryInterface::class),
        $c->get(JwtService::class),
        $c->get(MailService::class),
    ),

    UserService::class => fn (ContainerInterface $c) => new UserService(
        $c->get(UserRepositoryInterface::class),
    ),

    ValidatorInterface::class => fn () => Validation::createValidatorBuilder()
            ->enableAttributeMapping()
            ->getValidator(),

    JsonResponder::class => fn () => new JsonResponder(),

    ErrorMiddleware::class => fn (ContainerInterface $c) => new ErrorMiddleware(
        $c->get(JsonResponder::class),
        $c->get(LoggerInterface::class),
        (bool)$c->get('settings')['displayErrorDetails'],
    ),

    CorsMiddleware::class => fn (ContainerInterface $c) => new CorsMiddleware($c->get('settings')['cors']),

    JwtAuthMiddleware::class => fn (ContainerInterface $c) => new JwtAuthMiddleware(
        $c->get(JwtService::class),
        ['/', '/images', '/auth/login', '/auth/request-login', '/auth/forgot', '/auth/validate-reset-code', '/auth/reset'],
    ),

    RateLimitMiddleware::class => fn (ContainerInterface $c) => new RateLimitMiddleware(
        $c->get(RedisCache::class),
        $c->get(JwtService::class),
        $c->get('settings')['rate_limit'] ?? [],
    ),
]);

return $containerBuilder->build();
