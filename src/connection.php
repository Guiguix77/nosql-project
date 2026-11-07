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

        // Auto-synchronisation initiale : si Redis est vide au premier lancement,
        // on charge les commentaires et utilisateurs existants de PostgreSQL vers Redis
        if (!$this->redis->exists('lego:initialized')) {
            $this->seedRedisFromPostgres();
            $this->redis->set('lego:initialized', '1');
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

    private function seedRedisFromPostgres(): void
    {
        try {
            // 1. Synchroniser les utilisateurs existants
            $users = $this->pdo->query("SELECT id, username, email, created_at FROM users")->fetchAll();
            foreach ($users as $u) {
                $this->redis->hMSet("lego:user:{$u['username']}", [
                    'id'         => (string)$u['id'],
                    'username'   => (string)$u['username'],
                    'email'      => (string)$u['email'],
                    'created_at' => (string)$u['created_at'],
                    'logins'     => '1'
                ]);
                $this->redis->lPush("lego:users:recent", (string)$u['username']);
            }

            // 2. Synchroniser les commentaires existants
            $comments = $this->pdo->query("SELECT id, set_num, username, comment, created_at FROM set_comments ORDER BY id ASC")->fetchAll();
            foreach ($comments as $c) {
                $payload = json_encode([
                    'id'         => $c['id'],
                    'set_num'    => $c['set_num'],
                    'username'   => $c['username'],
                    'comment'    => $c['comment'],
                    'created_at' => $c['created_at']
                ], JSON_UNESCAPED_UNICODE);

                $this->redis->lPush("lego:comments:recent", $payload);
                $this->redis->zIncrBy("lego:sets:most_commented", 1, (string)$c['set_num']);
            }
        } catch (Throwable $e) {
            // Ignore si les tables ne sont pas encore prêtes
        }
    }
}