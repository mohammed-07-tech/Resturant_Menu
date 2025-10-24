<?php
session_start();
if (!empty($_SESSION['admin_id'])) {
  header('Location: dashboard.php'); exit;
}
require __DIR__ . '/db.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = trim($_POST['email'] ?? '');
  $pass  = $_POST['password'] ?? '';

  if ($email && $pass) {
    try {
      $stmt = db()->prepare("SELECT id, password_hash FROM admins WHERE email = ?");
      $stmt->execute([$email]);
      $row = $stmt->fetch();
      if ($row && password_verify($pass, $row['password_hash'])) {
        $_SESSION['admin_id'] = (int)$row['id'];
        $_SESSION['admin_email'] = $email;
        header('Location: dashboard.php'); exit;
      } else {
        $error = "Identifiants invalides.";
      }
    } catch (Throwable $e) {
      $error = "Erreur serveur.";
    }
  } else {
    $error = "Email et mot de passe requis.";
  }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin — Connexion</title>
  <link rel="stylesheet" href="../css/style.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
</head>
<body class="login-body">
  <div class="login-container">
    <div class="login-header">
      <i class="fas fa-user-shield"></i>
      <h1>Administration</h1>
      <p>Connectez-vous pour gérer le menu</p>
    </div>
    <div class="login-form">
      <?php if ($error): ?>
        <div class="message error" role="alert">
          <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($error) ?>
        </div>
      <?php else: ?>
      <?php endif; ?>
      <form method="post" class="needs-validate" novalidate>
        <div class="form-group">
          <label for="email">Email</label>
          <input required type="email" id="email" name="email" placeholder="email@domaine.com">
        </div>
        <div class="form-group">
          <label for="password">Mot de passe</label>
          <input required type="password" id="password" name="password" placeholder="••••••••">
        </div>
        <button class="btn btn-primary" type="submit"><i class="fas fa-sign-in-alt"></i> Se connecter</button>
      </form>
    </div>
  </div>
  <script src="admin.js"></script>
  <script>initAdmin();</script>
</body>
</html>
