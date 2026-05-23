<?php
session_start();

// =============================================
// DÉCONNEXION (doit être traité EN PREMIER)
// =============================================
if (isset($_GET['action']) && $_GET['action'] === 'deconnexion') {
    session_unset();
    session_destroy();
    header("Location: connexion.php");
    exit;
}

// Si déjà connecté, rediriger vers l'accueil
if (isset($_SESSION['utilisateur_connecte']) && $_SESSION['utilisateur_connecte'] === true) {
    header("Location: index.php");
    exit;
}

// =============================================
// CONNEXION À LA BASE DE DONNÉES
// =============================================
$host     = 'sql210.infinityfree.com';
$dbname   = 'if0_41992281_XXX';
$user     = 'if0_41992281';
$password = 'WALxqqYFL6';

// =============================================
// TRAITEMENT DU FORMULAIRE
// =============================================
$erreur = '';

if (isset($_POST['login']) && isset($_POST['mot_de_passe'])) {
    $login        = trim($_POST['login']);
    $mot_de_passe = $_POST['mot_de_passe'];

    if ($login == '' || $mot_de_passe == '') {
        $erreur = "Veuillez remplir tous les champs.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE login = ? AND mot_de_passe = ?");
        $stmt->execute([$login, $mot_de_passe]);
        $utilisateur = $stmt->fetch();

        if ($utilisateur != null) {
            // Connexion réussie : on enregistre la session
            $_SESSION['utilisateur_connecte'] = true;
            $_SESSION['login']                = $utilisateur['login'];
            header("Location: index.php");
            exit;
        } else {
            $erreur = "Login ou mot de passe incorrect.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — IRTM</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: var(--bleu);
        }

        .login-box {
            background: var(--blanc);
            border-radius: 10px;
            padding: 40px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.3);
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header h1 {
            font-family: 'Segoe UI', sans-serif;
            font-size: 22px;
            color: var(--bleu);
            margin-bottom: 6px;
        }

        .login-header p {
            color: #888;
            font-size: 14px;
        }

        .login-logo {
            background: var(--bleu);
            color: var(--beige);
            font-size: 20px;
            font-weight: 700;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px auto;
        }
    </style>
</head>
<body>

<div class="login-box">

    <div class="login-header">
        <div class="login-logo">IRTM</div>
        <h1>Espace de connexion</h1>
        <p>Gestion des notes — Année 2025-2026</p>
    </div>

    <?php if ($erreur != ''): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($erreur) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="form-groupe">
            <label>Login</label>
            <input type="text" name="login" placeholder="Entrez votre login" required
                   value="<?php if (isset($_POST['login'])) echo htmlspecialchars($_POST['login']); ?>">
        </div>

        <div class="form-groupe">
            <label>Mot de passe</label>
            <input type="password" name="mot_de_passe" placeholder="Entrez votre mot de passe" required>
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%; margin-top:8px; padding:12px;">
            Se connecter
        </button>
    </form>

    <p style="text-align:center; color:#aaa; font-size:12px; margin-top:20px;">
        Accès réservé aux administrateurs et enseignants
    </p>

</div>

</body>
</html>
