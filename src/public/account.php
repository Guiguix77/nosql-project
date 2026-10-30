<?php
declare(strict_types=1);
session_start();

require_once __DIR__ . '/../connection.php';
$conn  = Connection::get();
$pdo   = $conn->pg();
$redis = $conn->cache();

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }

$error   = '';
$success = '';
$action  = $_POST['action'] ?? '';

if ($action === 'register') {
    $username = trim((string)($_POST['username'] ?? ''));
    $email    = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    if ($username !== '' && $email !== '' && $password !== '') {
        try {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            // 1. Persistance dans PostgreSQL
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (:u, :e, :p) RETURNING id, created_at");
            $stmt->execute([':u' => $username, ':e' => $email, ':p' => $hash]);
            $user = $stmt->fetch();

            // 2. Persistance dans Redis (Hash profil + Liste des inscrits récents)
            $redis->hMSet("lego:user:{$username}", [
                'id'         => (string)$user['id'],
                'username'   => $username,
                'email'      => $email,
                'created_at' => (string)$user['created_at'],
                'logins'     => '1'
            ]);
            $redis->lPush("lego:users:recent", $username);

            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $username;
            $success = "Compte créé et connecté avec succès !";
        } catch (PDOException $e) {
            $error = "Ce pseudo ou cet email est déjà utilisé.";
        }
    } else {
        $error = "Veuillez remplir tous les champs.";
    }
} elseif ($action === 'login') {
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :u LIMIT 1");
    $stmt->execute([':u' => $username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id']  = $user['id'];
        $_SESSION['username'] = $user['username'];

        // Mise à jour du compteur de connexions et de la date dans Redis
        $redis->hIncrBy("lego:user:{$username}", 'logins', 1);
        $redis->hSet("lego:user:{$username}", 'last_login', date('Y-m-d H:i:s'));

        $success = "Connexion réussie !";
    } else {
        $error = "Identifiants incorrects.";
    }
} elseif ($action === 'logout') {
    session_destroy();
    header('Location: account.php');
    exit;
}

$redisProfile = isset($_SESSION['username']) ? $redis->hGetAll("lego:user:" . $_SESSION['username']) : [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Mon Compte - FindBrick</title>
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
      <a href="account.php"><strong><?= isset($_SESSION['username']) ? '👤 ' . h($_SESSION['username']) : 'Mon Compte' ?></strong></a>
      <span class="sep">|</span>
      <a id="nav-credits" href="credits.php">Crédits</a>
    </div>
  </nav>
</header>

<main class="container">
  <?php if ($error): ?>
    <div class="card" style="border-left: 4px solid #e74c3c; color: #e74c3c;"><p><?= h($error) ?></p></div>
  <?php endif; ?>
  <?php if ($success): ?>
    <div class="card" style="border-left: 4px solid #2ecc71; color: #2ecc71;"><p><?= h($success) ?></p></div>
  <?php endif; ?>

  <?php if (isset($_SESSION['username'])): ?>
    <section class="card">
      <h2>Profil de <?= h($_SESSION['username']) ?></h2>
      <p><strong>Email :</strong> <?= h($redisProfile['email'] ?? '—') ?></p>
      <p><strong>Inscrit le :</strong> <?= h($redisProfile['created_at'] ?? '—') ?></p>
      <p><strong>Nombre de connexions (stocké dans Redis) :</strong> <?= h($redisProfile['logins'] ?? '1') ?></p>
      <form method="post" action="account.php" style="margin-top: 15px;">
        <button type="submit" name="action" value="logout">Se déconnecter</button>
      </form>
    </section>
  <?php else: ?>
    <section class="card">
      <h2>Connexion</h2>
      <form class="form-row" method="post" action="account.php">
        <label for="login_user">Pseudo :</label>
        <input type="text" id="login_user" name="username" required>
        <br>
        <label for="login_pass">Mot de passe :</label>
        <input type="password" id="login_pass" name="password" required>
        <br>
        <button type="submit" name="action" value="login">Se connecter</button>
      </form>
    </section>

    <section class="card">
      <h2>Créer un compte</h2>
      <form class="form-row" method="post" action="account.php">
        <label for="reg_user">Pseudo :</label>
        <input type="text" id="reg_user" name="username" required>
        <br>
        <label for="reg_email">Email :</label>
        <input type="email" id="reg_email" name="email" required>
        <br>
        <label for="reg_pass">Mot de passe :</label>
        <input type="password" id="reg_pass" name="password" required>
        <br>
        <button type="submit" name="action" value="register">S'inscrire</button>
      </form>
    </section>
  <?php endif; ?>
</main>
<footer>
  <p id="footer-text">Crédits : FindBrick ©2025</p>
</footer>
</body>
</html>