<?php
declare(strict_types=1);

$appEnv = getenv('APP_ENV') ?: 'development';
$isProduction = $appEnv === 'production';

return [
    // PostgreSQL Configuration
    'pg_dsn'  => getenv('PG_DSN') ?: 'pgsql:host=db;port=5432;dbname=' . (getenv('POSTGRES_DB') ?: 'legodb'),
    'pg_user' => getenv('PG_USER') ?: getenv('POSTGRES_USER') ?: 'legouser',
    'pg_pass' => getenv('PG_PASS') ?: getenv('POSTGRES_PASSWORD') ?: 'legopass',
    
    // Redis Configuration
    'redis'   => [
        'host'     => getenv('REDIS_HOST') ?: 'redis',
        'port'     => (int)(getenv('REDIS_PORT') ?: 6379),
        'password' => getenv('REDIS_PASSWORD') ?: null,
        'timeout'  => (float)(getenv('REDIS_TIMEOUT') ?: 2.5),
        'persistent' => true,
    ],
    
    // Application Configuration
    'app' => [
        'env' => $appEnv,
        'debug' => (bool)(getenv('APP_DEBUG') ?: false),
        'url' => getenv('APP_URL') ?: 'http://localhost:8080',
        'timezone' => 'UTC',
        'session' => [
            'save_path' => getenv('PHP_SESSION_SAVE_PATH') ?: '/tmp/sessions',
            'cookie_secure' => $isProduction,
            'cookie_httponly' => true,
            'cookie_samesite' => 'Strict',
        ],
    ],
    
    // Logging Configuration
    'logging' => [
        'level' => getenv('LOG_LEVEL') ?: ($isProduction ? 'error' : 'debug'),
        'file' => getenv('LOG_FILE') ?: '/var/log/php/application.log',
    ],
];
