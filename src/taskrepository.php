<?php
declare(strict_types=1);

final class TaskRepository
{
    private const LIST_KEY = 'tasks:all';
    private const TTL = 300; // 5 min

    public function __construct(private Connection $conn) {}

    /** READ → Redis first, PG only as fallback (cache miss). */
    public function all(): array
    {
        $redis = $this->conn->cache();
        $cached = $redis->get(self::LIST_KEY);

        if ($cached !== false) {
            return json_decode($cached, true);          // hit: never touches PG
        }

        $stmt = $this->conn->pg()->query('SELECT * FROM tasks ORDER BY id');
        $rows = $stmt->fetchAll();

        $redis->setex(self::LIST_KEY, self::TTL, json_encode($rows)); // fill cache
        return $rows;
    }

    public function find(int $id): ?array
    {
        $redis = $this->conn->cache();
        $key = "task:{$id}";

        $cached = $redis->get($key);
        if ($cached !== false) {
            return json_decode($cached, true);
        }

        $stmt = $this->conn->pg()->prepare('SELECT * FROM tasks WHERE id = ?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();

        if ($row) {
            $redis->setex($key, self::TTL, json_encode($row));
        }
        return $row ?: null;
    }

    /** WRITE → PostgreSQL, then invalidate/update Redis. */
    public function create(string $title): array
    {
        $stmt = $this->conn->pg()->prepare('INSERT INTO tasks (title) VALUES (?) RETURNING *');
        $stmt->execute([$title]);
        $task = $stmt->fetch();

        $this->invalidate((int)$task['id']);
        return $task;
    }

    public function setDone(int $id, bool $done): bool
    {
        $stmt = $this->conn->pg()->prepare('UPDATE tasks SET done = ? WHERE id = ?');
        $stmt->execute([$done, $id]);

        $this->invalidate($id);
        return $stmt->rowCount() > 0;
    }

    /** Cache invalidation: delete precise key + the list, or update in place. */
    private function invalidate(int $id): void
    {
        $redis = $this->conn->cache();
        $redis->del("task:{$id}");
        $redis->del(self::LIST_KEY);   // list cache rebuilds on next read
    }
}