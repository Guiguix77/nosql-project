<?php
declare(strict_types=1);

return [
    'pg_dsn'  => getenv('PG_DSN'),
    'pg_user' => getenv('PG_USER'),
    'pg_pass' => getenv('PG_PASS'),
    'redis'   => [
        'host' => getenv('REDIS_HOST') ?: 'redis',
        'port' => (int)(getenv('REDIS_PORT') ?: 6379),
    ],
];