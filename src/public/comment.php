<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/../connection.php';
$conn  = Connection::get();
$pdo   = $conn->pg();
$redis = $conn->cache();

$action  = $_POST['action']  ?? '';
$set_num = trim((string)($_POST['set_num'] ?? ''));
$ret     = (string)($_POST['ret'] ?? '');

if ($action === 'add') {
    $comment = trim((string)($_POST['comment'] ?? ''));
    if ($set_num !== '' && $comment !== '') {
        // 1. Écriture dans PostgreSQL
        $stmt = $pdo->prepare("INSERT INTO set_comments (set_num, comment) VALUES (:sn, :c) RETURNING id, created_at");
        $stmt->execute([':sn' => $set_num, ':c' => $comment]);
        $inserted = $stmt->fetch();

        // 2. Écriture persistante dans Redis (NoSQL)
        $payload = json_encode([
            'id'         => $inserted['id'],
            'set_num'    => $set_num,
            'comment'    => $comment,
            'created_at' => $inserted['created_at']
        ], JSON_UNESCAPED_UNICODE);

        // On stocke l'historique des commentaires dans une Liste Redis dédiée
        $redis->lPush("lego:comments:recent", $payload);
        // On incrémente le classement des sets les plus commentés (Sorted Set Redis)
        $redis->zIncrBy("lego:sets:most_commented", 1, $set_num);
        // On invalide le cache de la fiche du set pour forcer le rafraîchissement
        $redis->del("lego:comments:set:{$set_num}");
    }
} elseif ($action === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        $stmt = $pdo->prepare("DELETE FROM set_comments WHERE id = :id");
        $stmt->execute([':id' => $id]);
        if ($set_num !== '') {
            $redis->del("lego:comments:set:{$set_num}");
        }
    }
}

$q = ['set_num' => $set_num];
if ($ret !== '') $q['ret'] = $ret;

header('Location: research.php?' . http_build_query($q));
exit;