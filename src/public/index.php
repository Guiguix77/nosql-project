<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/../connection.php';
$conn  = Connection::get();
$pdo   = $conn->pg();
$redis = $conn->cache();

// On cherche d'abord la liste des thèmes dans Redis, sinon dans PostgreSQL
$cachedThemes = $redis->get('lego:themes:all');
if ($cachedThemes !== false) {
    $themes = json_decode($cachedThemes, true);
} else {
    $themes = $pdo->query("
      SELECT c.id, c.name AS child_name, p.name AS parent_name
      FROM themes c
      LEFT JOIN themes p ON p.id = c.parent_id
      ORDER BY LOWER(COALESCE(p.name, '')), LOWER(c.name)
    ")->fetchAll();
    $redis->setex('lego:themes:all', 3600, json_encode($themes));
}

// Récupération des derniers commentaires depuis Redis pour les afficher en page d'accueil
$recentCommentsRaw = $redis->lRange('lego:comments:recent', 0, 4);
$recentComments = array_map(fn($item) => json_decode($item, true), $recentCommentsRaw ?: []);
?>
<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FindBrick</title>
    <link rel="stylesheet" href="src/css/style.css">
    <script src="src/js/script.js"></script>
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
      <section class="hero">
        <img src="src/img/lego.jpg" alt="Une image de LEGO" class="hero-img">
        <p id="content">Ce site web est réalisé dans le cadre du projet BAWE 2025.</p>
        <br>
      </section>

      <section class="card">
        <h2 id="search-set-title">Recherche par numéro de set</h2>
        <form class="form-row" action="research.php" method="get">
          <label for="set_num" id="label-setnum">Numéro du set :</label>
          <input type="text" id="set_num" name="set_num" required placeholder="ex : 10316-1" pattern="[0-9-]+">
          <button id="btn-search-set" type="submit">Rechercher</button>
          <p class="hint" id="hint-set">Correspondance exacte sur la colonne avec le numéro de set.</p>
        </form>
      </section>

      <section class="card">
        <h2 id="search-theme-title">Recherche par thème</h2>
        <form class="form-row" action="research.php" method="get">
          <label for="theme_id" id="label-theme">Choisir un thème :</label>
          <select id="theme_id" name="theme_id" aria-label="Choisir un thème">
            <option value="" id="opt-theme-placeholder">— sélectionner —</option>
            <?php foreach ($themes as $t): ?>
              <?php $label = $t['parent_name'] ? $t['parent_name'].' › '.$t['child_name'] : $t['child_name']; ?>
              <option value="<?= (int)$t['id'] ?>"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
          </select>
          <span class="muted" id="or-text">ou</span>
          <input type="text" name="theme_name" id="theme_name" placeholder="Recherche texte (partielle)">
          <button id="btn-search-theme" type="submit">Afficher les sets</button>
          <p class="hint" id="hint-theme">L’un OU l’autre suffit. Si les deux sont fournis, l’ID est prioritaire.</p>
        </form>
      </section>
    </main>

    <footer>
      <p id="footer-text">Crédits : FindBrick ©2025</p>
    </footer>
  </body>
</html>
