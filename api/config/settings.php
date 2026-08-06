<?php

declare(strict_types=1);

return [
    'settings' => [
        'displayErrorDetails' => true, // Should be set to false in production
        'logError'            => true,
        'logErrorDetails'     => true,

        'db' => ($_ENV['APP_ENV'] ?? '') === 'testing' ? [
            'host' => $_ENV['DB_TEST_HOST'] ?? 'localhost',
            'database' => $_ENV['DB_TEST_NAME'] ?? 'portfolio_test',
            'username' => $_ENV['DB_TEST_USER'] ?? 'root',
            'password' => $_ENV['DB_TEST_PASS'] ?? '',
            'charset' => $_ENV['DB_TEST_CHARSET'] ?? 'utf8mb4',
        ] : [
            'host' => $_ENV['DB_HOST'] ?? 'localhost',
            'database' => $_ENV['DB_NAME'] ?? 'portfolio',
            'username' => $_ENV['DB_USER'] ?? 'root',
            'password' => $_ENV['DB_PASS'] ?? '',
            'charset' => $_ENV['DB_CHARSET'] ?? 'utf8mb4',
        ],

        'redis' => [
            'host'     => $_ENV['REDIS_HOST'] ?? '127.0.0.1',
            'port'     => (int)($_ENV['REDIS_PORT'] ?? 6379),
            'password' => $_ENV['REDIS_PASSWORD'] ?? null,
        ],

        'jwt' => [
            'secret' => $_ENV['JWT_SECRET_KEY'] ?? 'secret',
            'algorithm' => 'HS256',
        ],

        'mailer' => [
            'host'       => $_ENV['MAIL_HOST'] ?? 'localhost',
            'port'       => (int)($_ENV['MAIL_PORT'] ?? 587),
            'username'   => $_ENV['MAIL_USERNAME'] ?? '',
            'password'   => $_ENV['MAIL_PASSWORD'] ?? '',
            'encryption' => $_ENV['MAIL_ENCRYPTION'] ?? 'tls',
            'from'       => $_ENV['MAIL_FROM_EMAIL'] ?? 'noreply@example.com',
            'name'       => $_ENV['MAIL_FROM_NAME'] ?? 'Portfolio',
        ],

        'site_url' => $_ENV['SITE_URL'] ?? 'http://localhost',
        'api_url' => $_ENV['API_URL'] ?? 'http://localhost:8080',

        'cors' => [
            'allowed_origins' => isset($_ENV['CORS_ALLOWED_ORIGINS']) ? array_map('trim', explode(',', $_ENV['CORS_ALLOWED_ORIGINS'])) : [$_ENV['SITE_URL'] ?? 'http://localhost'],
            'allowed_methods' => isset($_ENV['CORS_ALLOWED_METHODS']) ? array_map('trim', explode(',', $_ENV['CORS_ALLOWED_METHODS'])) : ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],
            'allowed_headers' => isset($_ENV['CORS_ALLOWED_HEADERS']) ? array_map('trim', explode(',', $_ENV['CORS_ALLOWED_HEADERS'])) : ['Authorization', 'Content-Type', 'Accept', 'Origin', 'X-Requested-With'],
            'exposed_headers' => isset($_ENV['CORS_EXPOSED_HEADERS']) ? array_map('trim', explode(',', $_ENV['CORS_EXPOSED_HEADERS'])) : [],
            'max_age' => (int)($_ENV['CORS_MAX_AGE'] ?? 0),
            'allow_credentials' => filter_var($_ENV['CORS_ALLOW_CREDENTIALS'] ?? true, FILTER_VALIDATE_BOOLEAN),
        ],

        'rate_limit' => [
            'enabled' => (bool)($_ENV['RATE_LIMIT_ENABLED'] ?? true),
            'max_requests' => (int)($_ENV['RATE_LIMIT_MAX_REQUESTS'] ?? 60),
            'window' => (int)($_ENV['RATE_LIMIT_WINDOW'] ?? 60), // In seconds
            'trusted_proxies' => [],
        ],
    ],
];
