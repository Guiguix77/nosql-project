<?php
declare(strict_types=1);

final class Connection
{
    private static ?Connection $instance = null;
    private ?PDO $pdo = null;
    private Redis $redis;

    public function __construct(array $config)
    {
        // PostgreSQL Connection
        $this->pdo = new PDO(
            $config['pg_dsn'],
            $config['pg_user'],
            $config['pg_pass'],
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_PERSISTENT         => true,
            ]
        );

        // Redis Connection
        $this->redis = new Redis();
        
        $redisConfig = $config['redis'];
        $host    = $redisConfig['host'] ?? 'redis';
        $port    = (int)($redisConfig['port'] ?? 6379);
        $timeout = (float)($redisConfig['timeout'] ?? 2.5);
        $connected = false;
        
        // Try to connect with authentication if password is set
        if (!empty($redisConfig['password'])) {
            $connected = $this->redis->pconnect(
                $host,
                $port,
                $timeout,
                null,
                0,
                0,
                ['auth' => [$redisConfig['password']]]
            );
        }
        
        // Fallback to connection without authentication
        if (!$connected) {
            $connected = $this->redis->pconnect($host, $port, $timeout);
        }
        
        if (!$connected) {
            throw new RuntimeException('Failed to connect to Redis server');
        }
    }

    public static function get(): self
    {
        if (self::$instance === null) {
            $config = require __DIR__ . '/config.php';
            self::$instance = new self($config);
        }
        return self::$instance;
    }

    public function pg(): PDO
    {
        return $this->pdo;
    }

    public function cache(): Redis
    {
        return $this->redis;
    }

    // Close all connections (useful for testing)
    public function close(): void
    {
        $this->pdo = null;
        $this->redis->close();
        self::$instance = null;
    }
}