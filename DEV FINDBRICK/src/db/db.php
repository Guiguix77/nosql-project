<?php
// db.php
declare(strict_types=1);

function db(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) return $pdo;

    $dbPath = __DIR__ . '/sets.db';
    if (!is_readable($dbPath)) {
        http_response_code(500);
        exit('Base SQLite introuvable !');
    }

    $pdo = new PDO('sqlite:' . $dbPath, null, null, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec('PRAGMA foreign_keys = ON;');

    return $pdo;
}
