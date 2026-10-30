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

$username = $_SESSION['username'] ?? 'Anonyme';

if ($action === 'add') {
    $comment = trim((string)($_POST['comment'] ?? ''));
    if ($set_num !== '' && $comment !== '') {
        // 1. Écriture dans PostgreSQL
        $stmt = $pdo->prepare("INSERT INTO set_comments (set_num, username, comment) VALUES (:sn, :u, :c) RETURNING id, created_at");
        $stmt->execute([':sn' => $set_num, ':u' => $username, ':c' => $comment]);
        $inserted = $stmt->fetch();

        // 2. Écriture persistante dans Redis (NoSQL)
        $payload = json_encode([
            'id'         => $inserted['id'],
            'set_num'    => $set_num,
            'username'   => $username,
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
        // 1. Suppression dans PostgreSQL
        $stmt = $pdo->prepare("DELETE FROM set_comments WHERE id = :id");
        $stmt->execute([':id' => $id]);

        // 2. Suppression dans la liste Redis "lego:comments:recent"
        $recentList = $redis->lRange("lego:comments:recent", 0, -1);
        foreach ($recentList as $itemJson) {
            $data = json_decode($itemJson, true);
            if (isset($data['id']) && (int)$data['id'] === $id) {
                // lRem supprime l'élément exact de la liste Redis
                $redis->lRem("lego:comments:recent", $itemJson, 0);
                break;
            }
        }

        // 3. Décrémentation du compteur et invalidation du cache du set
        if ($set_num !== '') {
            $newScore = $redis->zIncrBy("lego:sets:most_commented", -1, $set_num);
            if ($newScore <= 0) {
                $redis->zRem("lego:sets:most_commented", $set_num);
            }
            $redis->del("lego:comments:set:{$set_num}");
        }
    }
}

$q = ['set_num' => $set_num];
if ($ret !== '') $q['ret'] = $ret;

header('Location: research.php?' . http_build_query($q));
exit;