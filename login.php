<?php
session_start();
include 'connexion.php';

$erreur = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = htmlspecialchars($_POST['email']);
    $mdp   = $_POST['mot_de_passe'];

    // On cherche l'utilisateur dans la BDD
    $req = $db->prepare("SELECT * FROM utilisateurs WHERE email = ?");
    $req->execute([$email]);
    $user = $req->fetch();

    if ($user && password_verify($mdp, $user['mot_de_passe'])) {
        // Connexion réussie → on crée la session
        $_SESSION['id']    = $user['id_utilisateur'];
        $_SESSION['nom']   = $user['nom'];
        $_SESSION['role']  = $user['role'];

        // Redirection selon le rôle
        if ($user['role'] == 'admin') {
            header('Location: admin/dashboard.php');
        } else {
            header('Location: mes-reservations.php');
        }
        exit;
    } else {
        $erreur = "Email ou mot de passe incorrect.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>E-LLUSION – Connexion</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>

<?php include 'header.php'; ?>

<section class="login-section">
  <div class="login-box">
    <h1>Connexion</h1>
    <p class="text-muted">Accédez à vos réservations</p>

    <?php if ($erreur): ?>
      <div class="alert-error"><?php echo $erreur; ?></div>
    <?php endif; ?>

    <form action="login.php" method="POST" class="form-inscription">
      <div class="form-group">
        <label for="email">Adresse mail</label>
        <input type="email" id="email" name="email" required placeholder="votre@email.fr">
      </div>
      <div class="form-group">
        <label for="mot_de_passe">Mot de passe</label>
        <input type="password" id="mot_de_passe" name="mot_de_passe" required placeholder="••••••••">
      </div>
      <div class="form-submit">
        <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">
          Se connecter
        </button>
      </div>
    </form>

    <p style="text-align:center;margin-top:16px;font-size:13px;color:var(--text-muted);">
      Pas encore de compte ? <a href="inscription.php" style="color:var(--teal-darker);">S'inscrire</a>
    </p>
  </div>
</section>

<?php include 'footer.php'; ?>

</body>
</html>