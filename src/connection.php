<?php
declare(strict_types=1);

final class Connection
{
    private PDO $pdo;
    private Redis $redis;

    public function __construct(array $config)
    {
        $this->pdo = new PDO(
            $config['pg_dsn'],
            $config['pg_user'],
            $config['pg_pass'],
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]
        );
        $this->redis = new Redis();
        $this->redis->pconnect($config['redis']['host'], $config['redis']['port']);
    }

    public function pg(): PDO       { return $this->pdo; }
    public function cache(): Redis  { return $this->redis; }
}