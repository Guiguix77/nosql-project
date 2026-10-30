<?php
declare(strict_types=1);
session_start();

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="fr">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>FindBrick - Crédits</title>
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
        <a href="account.php"><?= isset($_SESSION['username']) ? '👤 ' . h($_SESSION['username']) : 'Mon Compte' ?></a>
        <span class="sep">|</span>
        <a id="nav-credits" href="credits.php">Crédits</a>
        <div class="buttons">
          <button id="toggle-language" type="button">English</button>
          <button id="toggle-theme" type="button" aria-label="Toggle theme">🌞/🌙</button>
        </div>
      </div>
    </nav>
  </header>

  <main class="container">
    <section class="card">
      <h2 id="credits-title"><u>Crédits</u></h2>

      <h3 id="credits-project-title">Projet</h3>
      <p id="credits-project-text">FindBrick — est un site réalisé dans le cadre du projet NoSQL / BAWE. Objectif :
        rechercher et afficher des sets LEGO avec persistance hybride (PostgreSQL & Redis).</p>
      <br>

      <h3 id="credits-team-title">Équipe</h3>
      <p id="credits-team-text">Réalisation : <strong>Guillaume MÉRIEUX</strong></p>
      <br>

      <h3 id="credits-data-title">Données & images</h3>
      <p id="credits-data-text-1">
        Les informations de sets et certaines vignettes proviennent du site
        <a href="https://rebrickable.com/downloads/" target="_blank">Rebrickable</a>.
      </p>
      <p id="credits-data-text-2">
        <a href="https://lego.fr" target="_blank">LEGO®</a> est une marque déposée du groupe LEGO,
        qui ne sponsorise ni n’autorise ce projet.
      </p>
      <p id="credits-data-text-3">
        Les visuels utilisés sur ce site proviennent de sources tierces
        et restent la propriété de leurs auteurs respectifs.
      </p>
      <br>

      <h3 id="credits-tech-title">Technologies</h3>
      <p id="credits-tech-text">PHP · PostgreSQL · Redis · Docker · HTML/CSS · JavaScript.</p>
      <br>

      <h3 id="credits-contact-title">Contact</h3>
      <p id="credits-contact-text">Contact : <em><a
            href="mailto:guillaume.merieux@ensiie.eu">guillaume.merieux@ensiie.eu</a></em></p>
    </section>
  </main>

  <footer>
    <p id="footer-text">Crédits : FindBrick ©2025</p>
  </footer>
</body>

</html>