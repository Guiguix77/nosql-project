<?php
declare(strict_types=1);

final class Connection
{
    private static ?Connection $instance = null;
    private PDO $pdo;
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
        $connected = false;
        
        // Try to connect with authentication if password is set
        if (!empty($redisConfig['password'])) {
            $connected = $this->redis->pconnect(
                $redisConfig['host'],
                $redisConfig['port'],
                $redisConfig['timeout'],
                null,
                0,
                0,
                ['auth' => [$redisConfig['password']]]
            );
        }
        
        // Fallback to connection without authentication
        if (!$connected) {
            $connected = $this->redis->pconnect(
                $redisConfig['host'],
                $redisConfig['port'],
                $redisConfig['timeout']
            );
        }
        
        if (!$connected) {
            throw new RuntimeException('Failed to connect to Redis server');
        }
        
        // Set Redis options
        $this->redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
        $this->redis->setOption(Redis::OPT_PERSISTENT, $redisConfig['persistent'] ?? true);
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

    /**
     * Close all connections (useful for testing)
     */
    public function close(): void
    {
        $this->pdo = null;
        $this->redis->close();
        self::$instance = null;
    }
}
