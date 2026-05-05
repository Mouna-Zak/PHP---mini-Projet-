<?php
require_once 'config.php';
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = trim($_POST['login'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($login) || empty($password)) {
        $error = "Veuillez remplir tous les champs.";
    } else {
        $db = getDB();
        $stmt = $db->prepare("SELECT id, password FROM users WHERE login = ?");
        $stmt->bind_param("s", $login);
        $stmt->execute();
        $stmt->bind_result($id, $hash);
        $stmt->fetch();

        if ($id && password_verify($password, $hash)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['user_login'] = $login;
            header('Location: dashboard.php');
            exit;
        } else {
            $error = "Identifiant ou mot de passe incorrect.";
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
    <title>Connexion — Mouna Agency</title>
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

        .form-group { margin-bottom: 20px; }

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
            padding: 12px 16px;
            color: #e8d5a3;
            font-family: 'Inter', sans-serif;
            font-size: 0.9rem;
            transition: border-color 0.3s;
            outline: none;
        }

        input:focus { border-color: #c9a84c; }

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

        .alert-error {
            padding: 12px 16px;
            font-size: 0.82rem;
            margin-bottom: 24px;
            background: rgba(180,50,50,0.15);
            border-left: 3px solid #b43232;
            color: #e07070;
        }

        .link {
            text-align: center;
            margin-top: 24px;
            font-size: 0.78rem;
            color: #555;
        }

        .link a { color: #c9a84c; text-decoration: none; }
        .link a:hover { text-decoration: underline; }

        .divider {
            border: none;
            border-top: 1px solid #1e1e1e;
            margin: 28px 0;
        }

        .gold-line {
            width: 40px;
            height: 2px;
            background: #c9a84c;
            margin: 0 auto 32px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">
            <h1>MOUNA</h1>
            <p>Communication & Marketing</p>
        </div>

        <div class="card">
            <h2>Connexion</h2>
            <p class="subtitle">Tableau de bord — Espace privé</p>

            <?php if ($error): ?>
                <div class="alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label>Identifiant</label>
                    <input type="text" name="login" value="<?= htmlspecialchars($_POST['login'] ?? '') ?>" placeholder="Votre identifiant" required>
                </div>
                <div class="form-group">
                    <label>Mot de passe</label>
                    <input type="password" name="password" placeholder="••••••••" required>
                </div>
                <button type="submit" class="btn">Accéder au tableau de bord</button>
            </form>

            <hr class="divider">
            <div class="link">Pas encore de compte ? <a href="register.php">S'inscrire</a></div>
        </div>
    </div>
</body>
</html>
