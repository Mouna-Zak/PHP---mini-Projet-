<?php
require_once 'config.php';
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    if (empty($login) || empty($password) || empty($confirm)) {
        $error = "Tous les champs sont obligatoires.";
    } elseif ($password !== $confirm) {
        $error = "Les mots de passe ne correspondent pas.";
    } elseif (strlen($password) < 6) {
        $error = "Le mot de passe doit contenir au moins 6 caractères.";
    } else {
        $db = getDB();
        $stmt = $db->prepare("SELECT id FROM users WHERE login = ?");
        $stmt->bind_param("s", $login);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $error = "Ce login est déjà utilisé.";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt2 = $db->prepare("INSERT INTO users (login, password) VALUES (?, ?)");
            $stmt2->bind_param("ss", $login, $hash);
            $stmt2->execute();
            $success = "Compte créé avec succès. Vous pouvez vous connecter.";
        }
        $db->close();
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription — Mouna Agency</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background-color: #0a0a0a;
            color: #e8d5a3;
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-image: radial-gradient(ellipse at 20% 50%, rgba(184,148,72,0.07) 0%, transparent 60%),
                              radial-gradient(ellipse at 80% 20%, rgba(184,148,72,0.05) 0%, transparent 50%);
        }

        .container {
            width: 100%;
            max-width: 440px;
            padding: 20px;
        }

        .logo {
            text-align: center;
            margin-bottom: 40px;
        }

        .logo h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            color: #c9a84c;
            letter-spacing: 0.1em;
        }

        .logo p {
            font-size: 0.7rem;
            letter-spacing: 0.4em;
            text-transform: uppercase;
            color: #6b6b6b;
            margin-top: 4px;
        }

        .card {
            background: #111111;
            border: 1px solid #2a2a2a;
            border-top: 2px solid #c9a84c;
            padding: 40px;
        }

        .card h2 {
            font-family: 'Playfair Display', serif;
            font-size: 1.4rem;
            color: #e8d5a3;
            margin-bottom: 8px;
        }

        .card .subtitle {
            font-size: 0.78rem;
            color: #555;
            margin-bottom: 32px;
            letter-spacing: 0.05em;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-size: 0.72rem;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: #888;
            margin-bottom: 8px;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            background: #1a1a1a;
            border: 1px solid #2a2a2a;
            border-radius: 0;
            padding: 12px 16px;
            color: #e8d5a3;
            font-family: 'Inter', sans-serif;
            font-size: 0.9rem;
            transition: border-color 0.3s;
            outline: none;
        }

        input:focus {
            border-color: #c9a84c;
        }

        .btn {
            width: 100%;
            padding: 14px;
            background: #c9a84c;
            color: #0a0a0a;
            border: none;
            font-family: 'Inter', sans-serif;
            font-size: 0.78rem;
            font-weight: 600;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            cursor: pointer;
            margin-top: 8px;
            transition: background 0.3s;
        }

        .btn:hover { background: #e8c76a; }

        .alert {
            padding: 12px 16px;
            font-size: 0.82rem;
            margin-bottom: 24px;
        }

        .alert-error { background: rgba(180,50,50,0.15); border-left: 3px solid #b43232; color: #e07070; }
        .alert-success { background: rgba(50,180,100,0.1); border-left: 3px solid #32b464; color: #60c080; }

        .link {
            text-align: center;
            margin-top: 24px;
            font-size: 0.78rem;
            color: #555;
        }

        .link a {
            color: #c9a84c;
            text-decoration: none;
        }

        .link a:hover { text-decoration: underline; }

        .divider {
            border: none;
            border-top: 1px solid #1e1e1e;
            margin: 28px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <h1>MOUNA</h1>
            <p>Stratégie Digitale</p>
        </div>

        <div class="card">
            <h2>Créer un compte</h2>
            <p class="subtitle">Accès réservé aux membres de l'équipe</p>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Identifiant</label>
                    <input type="text" name="login" value="<?= htmlspecialchars($_POST['login'] ?? '') ?>" placeholder="Votre identifiant" required>
                </div>
                <div class="form-group">
                    <label>Mot de passe</label>
                    <input type="password" name="password" placeholder="Min. 6 caractères" required>
                </div>
                <div class="form-group">
                    <label>Confirmer le mot de passe</label>
                    <input type="password" name="confirm" placeholder="Répétez le mot de passe" required>
                </div>
                <button type="submit" class="btn">Créer mon compte</button>
            </form>

            <hr class="divider">
            <div class="link">Déjà membre ? <a href="login.php">Se connecter</a></div>
        </div>
    </div>
</body>
</html>
