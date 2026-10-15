<?php
require __DIR__ . '/src/db/db.php';
$pdo = db();

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$set_num    = isset($_GET['set_num']) ? trim((string)$_GET['set_num']) : '';
$theme_id   = isset($_GET['theme_id']) ? trim((string)$_GET['theme_id']) : '';
$theme_name = isset($_GET['theme_name']) ? trim((string)$_GET['theme_name']) : '';
$ret        = isset($_GET['ret']) ? (string)$_GET['ret'] : '';

$page   = max(1, (int)($_GET['page'] ?? 1));
$limit  = 24;
$offset = ($page - 1) * $limit;

$title = 'FindBrick';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= h($title) ?></title>
  <link rel="stylesheet" href="src/css/style.css">
  <script src="src/js/script.js" defer></script>
</head>
<body>
<header>
  <nav class="container">
    <div class="nav-left">
      <img src="src/img/brick.jpg" alt="FindBrick" class="logo">
      <h1 id="site-title">Bienvenue sur FindBrick</h1>
    </div>
    <div class="nav-right">
      <a id="nav-home" href="index.php">Accueil</a>
      <span class="sep">|</span>
      <a id="nav-credits" href="credits.html">Crédits</a>
      <div class="buttons">
        <button id="toggle-language" type="button">English</button>
        <button id="toggle-theme" type="button" aria-label="Toggle theme">🌞/🌙</button>
      </div>
    </div>
  </nav>
</header>

<main class="container">
<?php
/* ====== FICHE SET ====== */
if ($set_num !== '') {
  $stmt = $pdo->prepare("SELECT s.set_num,s.name,s.year,s.theme_id,s.num_parts,s.img_url,t.name AS theme_name
                         FROM sets s LEFT JOIN themes t ON t.id=s.theme_id
                         WHERE s.set_num=:sn LIMIT 1");
  $stmt->execute([':sn'=>$set_num]);
  $row = $stmt->fetch();

  // commentaires
  $comments = $pdo->prepare("SELECT id, comment, created_at FROM set_comments WHERE set_num=:sn ORDER BY id DESC");
  $comments->execute([':sn'=>$set_num]);
  $commentsRows = $comments->fetchAll();

  $backHref = $ret !== '' ? ('research.php?'.$ret) : 'index.php';
  ?>
  <section class="card">
    <h2 id="result-set-title">Résultat</h2>
    <?php if ($row): ?>
      <div class="set-card">
        <div class="set-media">
          <?php if (!empty($row['img_url'])): ?>
            <img src="<?= h($row['img_url']) ?>" alt="Image du set <?= h($row['set_num']) ?>">
          <?php endif; ?>
        </div>
        <div class="set-info">
          <h3 class="set-name"><?= h($row['name']) ?></h3>
          <p class="meta"><strong id="lbl-setnum">Set :</strong> <?= h($row['set_num']) ?></p>
          <p class="meta"><strong id="lbl-year">Année :</strong> <?= h($row['year']) ?></p>
          <p class="meta"><strong id="lbl-theme">Thème :</strong> <?= h($row['theme_name'] ?? '—') ?></p>
          <p class="meta"><strong id="lbl-parts">Pièces :</strong> <?= h($row['num_parts']) ?></p>
          <?php if (!empty($row['img_url'])): ?>
            <p><a id="view-image" href="<?= h($row['img_url']) ?>" target="_blank" rel="noopener">Voir l’image</a></p>
          <?php endif; ?>
          <p><a class="btn-link" id="back-home" href="<?= h($backHref) ?>">⟵ Retour</a></p>
        </div>
      </div>

      <div class="card" style="margin-top:16px;">
        <h3 id="comments-title">Commentaires</h3>
        <form class="form-row" action="comment.php" method="post">
          <label for="comment_text" id="label-comment">Ajouter un commentaire :</label>
          <input type="hidden" name="set_num" value="<?= h($row['set_num']) ?>">
          <input type="hidden" name="ret" value="<?= h($ret) ?>">
          <input type="text" id="comment_text" name="comment" required placeholder="ex: Acheté en 2025, pièces manquantes...">
          <button id="btn-add-comment" type="submit" name="action" value="add">Ajouter</button>
        </form>

        <?php if ($commentsRows): ?>
          <ul class="comments-list" id="comments-list">
            <?php foreach ($commentsRows as $n): ?>
              <li class="comment-item">
                <div class="comment-text"><?= h($n['comment']) ?></div>
                <div class="comment-meta"><?= h($n['created_at']) ?></div>
                <form action="comment.php" method="post" class="comment-actions">
                  <input type="hidden" name="id" value="<?= (int)$n['id'] ?>">
                  <input type="hidden" name="set_num" value="<?= h($row['set_num']) ?>">
                  <input type="hidden" name="ret" value="<?= h($ret) ?>">
                  <button class="btn-link danger i18n-delete-comment" type="submit" name="action" value="delete">Supprimer</button>
                </form>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <p id="no-comments">Aucun commentaire pour ce set.</p>
        <?php endif; ?>
      </div>
    <?php else: ?>
      <p id="no-set">Aucun set trouvé pour ce numéro.</p>
      <p><a class="btn-link" id="back-home-2" href="<?= h($backHref) ?>">⟵ Retour</a></p>
    <?php endif; ?>
  </section>
  <?php
  echo '</main><footer><p id="footer-text">Crédits : FindBrick ©2025</p></footer></body></html>';
  exit;
}

/* ====== LISTING PAR THÈME OU NOM ====== */
$where=''; $params=[]; $themeLabel='';
if ($theme_id !== '') {
  $where='WHERE s.theme_id = :tid'; $params[':tid']=(int)$theme_id;

  $t=$pdo->prepare("WITH RECURSIVE p(id,name,parent_id,depth) AS (
      SELECT id,name,parent_id,0 FROM themes WHERE id=:id
      UNION ALL SELECT t.id,t.name,t.parent_id, p.depth+1 FROM themes t JOIN p ON t.id=p.parent_id
    )
    SELECT GROUP_CONCAT(name,' › ') FROM (SELECT name FROM p ORDER BY depth DESC)");
  $t->execute([':id'=>(int)$theme_id]);
  $themeLabel = (string)$t->fetchColumn() ?: ('ID '.(int)$theme_id);

} elseif ($theme_name !== '') {
  $where='WHERE t.name LIKE :tname'; $params[':tname']='%'.$theme_name.'%';
  $themeLabel = $theme_name;
} else {
  header('Location: index.php'); exit;
}

$c=$pdo->prepare("SELECT COUNT(*) FROM sets s LEFT JOIN themes t ON t.id=s.theme_id $where");
$c->execute($params);
$total=(int)$c->fetchColumn();

$q=$pdo->prepare("SELECT s.set_num,s.name,s.year,s.num_parts,s.img_url,t.name AS theme_name
                  FROM sets s LEFT JOIN themes t ON t.id=s.theme_id
                  $where
                  ORDER BY s.year DESC, s.set_num ASC
                  LIMIT :lim OFFSET :off");
foreach($params as $k=>$v){ $q->bindValue($k,$v); }
$q->bindValue(':lim',$limit,PDO::PARAM_INT);
$q->bindValue(':off',$offset,PDO::PARAM_INT);
$q->execute();
$rows=$q->fetchAll();

$pages=max(1,(int)ceil($total/$limit));
$retQuery = http_build_query($_GET);
?>
<section class="card">
  <h2 id="result-theme-title">Résultats</h2>
  <p class="meta">
    <span id="theme-path-label">Thème :</span> <strong><?= h($themeLabel) ?></strong> —
    <span id="count-label">Sets trouvés :</span> <?= $total ?>
    <?php if ($pages>1): ?> — <span id="page-label">Page</span> <?= $page ?> / <?= $pages ?> <?php endif; ?>
  </p>

  <?php if (!$rows): ?>
    <p id="no-theme-results">Aucun set pour ce thème.</p>
    <p><a class="btn-link" id="back-home-3" href="index.php">⟵ Retour</a></p>
  <?php else: ?>
    <div class="grid" id="results-grid">
      <?php foreach ($rows as $r): ?>
        <article class="card card-item">
          <?php if (!empty($r['img_url'])): ?>
            <img src="<?= h($r['img_url']) ?>" alt="Image du set <?= h($r['set_num']) ?>">
          <?php endif; ?>
          <div class="name"><?= h($r['name']) ?></div>
          <div class="small">
            <div><strong class="i18n-lbl-set">Set :</strong> <?= h($r['set_num']) ?></div>
            <div><strong class="i18n-lbl-year">Année :</strong> <?= h($r['year']) ?></div>
            <div><strong class="i18n-lbl-theme">Thème :</strong> <?= h($r['theme_name'] ?? '—') ?></div>
            <div><strong class="i18n-lbl-parts">Pièces :</strong> <?= h($r['num_parts']) ?></div>
          </div>
          <div class="actions">
            <a class="btn-link i18n-view-sheet"
               href="research.php?set_num=<?= urlencode($r['set_num']) ?>&ret=<?= urlencode($retQuery) ?>">
               Voir la fiche
            </a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>

    <?php if ($pages>1): ?>
      <nav class="pager" aria-label="Pagination">
        <?php if ($page>1): ?>
          <a href="<?= 'research.php?'.http_build_query(array_merge($_GET,['page'=>1])) ?>" class="i18n-first-page">« Première</a>
          <a href="<?= 'research.php?'.http_build_query(array_merge($_GET,['page'=>$page-1])) ?>" class="i18n-prev-page">‹ Précédente</a>
        <?php endif; ?>
        <span class="current"><span id="page-label-2">Page</span> <?= $page ?></span>
        <?php if ($page<$pages): ?>
          <a href="<?= 'research.php?'.http_build_query(array_merge($_GET,['page'=>$page+1])) ?>" class="i18n-next-page">Suivante ›</a>
          <a href="<?= 'research.php?'.http_build_query(array_merge($_GET,['page'=>$pages])) ?>" class="i18n-last-page">Dernière »</a>
        <?php endif; ?>
      </nav>
    <?php endif; ?>

    <p style="margin-top:18px"><a class="btn-link" id="back-home-4" href="index.php">⟵ Nouvelle recherche</a></p>
  <?php endif; ?>
</section>
</main>

<footer>
  <p id="footer-text">Crédits : FindBrick ©2025</p>
</footer>
</body>
</html>