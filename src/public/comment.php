<?php
require __DIR__ . '/src/db/db.php';
$pdo = db();

$action  = $_POST['action']  ?? '';
$set_num = trim((string)($_POST['set_num'] ?? ''));
$ret     = (string)($_POST['ret'] ?? '');

if ($action === 'add') {
    $comment = trim((string)($_POST['comment'] ?? ''));
    if ($set_num !== '' && $comment !== '') {
        $stmt = $pdo->prepare("INSERT INTO set_comments (set_num, comment) VALUES (:sn, :c)");
        $stmt->execute([':sn' => $set_num, ':c' => $comment]);
    }
} elseif ($action === 'delete') {
    $id = (int)($_POST['id'] ?? 0);
    if ($id > 0) {
        $stmt = $pdo->prepare("DELETE FROM set_comments WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }
}

$q = ['set_num' => $set_num];
if ($ret !== '') $q['ret'] = $ret;

header('Location: research.php?' . http_build_query($q));
exit;

?>
