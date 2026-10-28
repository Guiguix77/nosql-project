<?php
declare(strict_types=1);

return [
    'pg_dsn'  => getenv('PG_DSN') ?: 'pgsql:host=db;port=5432;dbname=legodb',
    'pg_user' => getenv('PG_USER') ?: 'legouser',
    'pg_pass' => getenv('PG_PASS') ?: 'legopass',
    'redis'   => [
        'host' => getenv('REDIS_HOST') ?: 'redis',
        'port' => (int)(getenv('REDIS_PORT') ?: 6379),
    ],
];