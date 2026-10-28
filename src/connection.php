<?php
declare(strict_types=1);

final class Connection
{
    private static ?Connection $instance = null;
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
        $this->redis->pconnect($config['redis']['host'], (int)$config['redis']['port']);
    }

    public static function get(): self
    {
        if (self::$instance === null) {
            $config = require __DIR__ . '/config.php';
            self::$instance = new self($config);
        }
        return self::$instance;
    }

    public function pg(): PDO       { return $this->pdo; }
    public function cache(): Redis  { return $this->redis; }
}