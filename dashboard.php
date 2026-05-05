<?php
require_once 'config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$db = getDB();
$success = '';
$error = '';

// Ajout étudiant
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $specialisation = trim($_POST['specialisation'] ?? '');
    $note = $_POST['note_moyenne'] ?? '';

    if (empty($nom) || empty($prenom) || empty($specialisation) || $note === '') {
        $error = "Tous les champs sont obligatoires.";
    } elseif (!is_numeric($note) || $note < 0 || $note > 20) {
        $error = "La note doit être un nombre entre 0 et 20.";
    } else {
        $stmt = $db->prepare("INSERT INTO etudiants (nom, prenom, specialisation, note_moyenne) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssd", $nom, $prenom, $specialisation, $note);
        $stmt->execute();
        $success = "Étudiant ajouté avec succès.";
    }
}

// Suppression
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $db->prepare("DELETE FROM etudiants WHERE id = ?")->execute([$id]);
    // Utilisation classique
    $stmt = $db->prepare("DELETE FROM etudiants WHERE id = ?");
    $stmt->bind_param("i", $id);
    // Already deleted above, skip re-execute to avoid double delete
}

// Récupération étudiants
$result = $db->query("SELECT * FROM etudiants ORDER BY nom ASC");
$etudiants = $result->fetch_all(MYSQLI_ASSOC);
$total = count($etudiants);
$moyenne_generale = $total > 0 ? array_sum(array_column($etudiants, 'note_moyenne')) / $total : 0;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — Mouna Agency</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            background-color: #0a0a0a;
            color: #e8d5a3;
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
        }

        /* SIDEBAR */
        .sidebar {
            position: fixed;
            top: 0; left: 0;
            width: 240px;
            height: 100vh;
            background: #0f0f0f;
            border-right: 1px solid #1e1e1e;
            display: flex;
            flex-direction: column;
            padding: 40px 0;
            z-index: 100;
        }

        .sidebar-logo {
            padding: 0 28px 40px;
            border-bottom: 1px solid #1e1e1e;
        }

        .sidebar-logo h1 {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            color: #c9a84c;
            letter-spacing: 0.1em;
        }

        .sidebar-logo p {
            font-size: 0.65rem;
            letter-spacing: 0.3em;
            text-transform: uppercase;
            color: #444;
            margin-top: 4px;
        }

        .sidebar-nav {
            padding: 32px 0;
            flex: 1;
        }

        .nav-label {
            font-size: 0.6rem;
            letter-spacing: 0.3em;
            text-transform: uppercase;
            color: #444;
            padding: 0 28px;
            margin-bottom: 12px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 28px;
            font-size: 0.82rem;
            color: #666;
            text-decoration: none;
            transition: all 0.2s;
            border-left: 2px solid transparent;
        }

        .nav-item.active, .nav-item:hover {
            color: #e8d5a3;
            border-left-color: #c9a84c;
            background: rgba(201,168,76,0.05);
        }

        .nav-item .icon { font-size: 1rem; }

        .sidebar-footer {
            padding: 24px 28px;
            border-top: 1px solid #1e1e1e;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
        }

        .user-avatar {
            width: 36px; height: 36px;
            background: #c9a84c;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            font-weight: 600;
            color: #0a0a0a;
        }

        .user-name {
            font-size: 0.82rem;
            color: #e8d5a3;
        }

        .user-role {
            font-size: 0.65rem;
            color: #555;
            letter-spacing: 0.05em;
        }

        .logout-btn {
            display: block;
            width: 100%;
            padding: 10px;
            background: transparent;
            border: 1px solid #2a2a2a;
            color: #666;
            font-family: 'Inter', sans-serif;
            font-size: 0.72rem;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            transition: all 0.3s;
        }

        .logout-btn:hover {
            border-color: #c9a84c;
            color: #c9a84c;
        }

        /* MAIN */
        .main {
            margin-left: 240px;
            padding: 40px 48px;
            min-height: 100vh;
        }

        .page-header {
            margin-bottom: 40px;
        }

        .page-header h2 {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            color: #e8d5a3;
        }

        .page-header p {
            color: #555;
            font-size: 0.82rem;
            margin-top: 6px;
        }

        /* STATS */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: #111;
            border: 1px solid #1e1e1e;
            padding: 24px;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 2px;
            background: linear-gradient(90deg, #c9a84c, transparent);
        }

        .stat-label {
            font-size: 0.65rem;
            letter-spacing: 0.25em;
            text-transform: uppercase;
            color: #555;
            margin-bottom: 12px;
        }

        .stat-value {
            font-family: 'Playfair Display', serif;
            font-size: 2.2rem;
            color: #c9a84c;
        }

        .stat-sub {
            font-size: 0.72rem;
            color: #444;
            margin-top: 4px;
        }

        /* ALERTS */
        .alert {
            padding: 14px 20px;
            font-size: 0.82rem;
            margin-bottom: 28px;
            border-left: 3px solid;
        }

        .alert-error { background: rgba(180,50,50,0.1); border-color: #b43232; color: #e07070; }
        .alert-success { background: rgba(50,180,100,0.08); border-color: #32b464; color: #60c080; }

        /* TABLE */
        .section-title {
            font-family: 'Playfair Display', serif;
            font-size: 1.1rem;
            color: #e8d5a3;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .section-title::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #1e1e1e;
        }

        .table-wrapper {
            background: #111;
            border: 1px solid #1e1e1e;
            margin-bottom: 40px;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead tr {
            border-bottom: 1px solid #1e1e1e;
        }

        th {
            padding: 14px 20px;
            text-align: left;
            font-size: 0.65rem;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #555;
            font-weight: 500;
        }

        td {
            padding: 16px 20px;
            font-size: 0.85rem;
            color: #b0a080;
            border-bottom: 1px solid #161616;
        }

        tr:last-child td { border-bottom: none; }

        tr:hover td { background: rgba(201,168,76,0.03); }

        .badge {
            display: inline-block;
            padding: 4px 12px;
            background: rgba(201,168,76,0.1);
            border: 1px solid rgba(201,168,76,0.2);
            color: #c9a84c;
            font-size: 0.72rem;
            letter-spacing: 0.05em;
        }

        .note {
            font-family: 'Playfair Display', serif;
            font-size: 1rem;
            color: #e8d5a3;
        }

        .note.high { color: #60c080; }
        .note.low { color: #e07070; }

        .btn-delete {
            background: transparent;
            border: 1px solid #2a2a2a;
            color: #555;
            padding: 6px 14px;
            font-size: 0.7rem;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
        }

        .btn-delete:hover {
            border-color: #b43232;
            color: #e07070;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #333;
        }

        .empty-state .empty-icon { font-size: 2.5rem; margin-bottom: 12px; }
        .empty-state p { font-size: 0.82rem; }

        /* FORM */
        .form-card {
            background: #111;
            border: 1px solid #1e1e1e;
            border-top: 2px solid #c9a84c;
            padding: 32px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group { display: flex; flex-direction: column; gap: 8px; }
        .form-group.full { grid-column: 1 / -1; }

        label {
            font-size: 0.65rem;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: #666;
        }

        input[type="text"],
        input[type="number"] {
            background: #1a1a1a;
            border: 1px solid #2a2a2a;
            padding: 11px 14px;
            color: #e8d5a3;
            font-family: 'Inter', sans-serif;
            font-size: 0.88rem;
            outline: none;
            transition: border-color 0.3s;
        }

        input:focus { border-color: #c9a84c; }

        .btn-submit {
            padding: 13px 32px;
            background: #c9a84c;
            color: #0a0a0a;
            border: none;
            font-family: 'Inter', sans-serif;
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            cursor: pointer;
            margin-top: 8px;
            transition: background 0.3s;
        }

        .btn-submit:hover { background: #e8c76a; }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="sidebar-logo">
        <h1>MOUNA</h1>
        <p>Stratégie Digitale</p>
    </div>
    <nav class="sidebar-nav">
        <div class="nav-label">Navigation</div>
        <a href="dashboard.php" class="nav-item active">
            <span class="icon">◈</span> Tableau de bord
        </a>
    </nav>
    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-avatar"><?= strtoupper(substr($_SESSION['user_login'], 0, 1)) ?></div>
            <div>
                <div class="user-name"><?= htmlspecialchars($_SESSION['user_login']) ?></div>
                <div class="user-role">Administrateur</div>
            </div>
        </div>
        <a href="logout.php" class="logout-btn">Déconnexion</a>
    </div>
</aside>

<main class="main">
    <div class="page-header">
        <h2>Tableau de bord</h2>
        <p>Gestion des étudiants — Stratégie Digitale</p>
    </div>

    <!-- Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-label">Total étudiants</div>
            <div class="stat-value"><?= $total ?></div>
            <div class="stat-sub">inscrits dans la formation</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Moyenne générale</div>
            <div class="stat-value"><?= number_format($moyenne_generale, 2) ?></div>
            <div class="stat-sub">sur 20 points</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Formation</div>
            <div class="stat-value" style="font-size:1.1rem; padding-top:8px;">Stratégie Digitale</div>
            <div class="stat-sub">Année en cours</div>
        </div>
    </div>

    <?php if ($error): ?>
        <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <!-- Table étudiants -->
    <div class="section-title">Liste des étudiants</div>
    <div class="table-wrapper">
        <?php if (empty($etudiants)): ?>
            <div class="empty-state">
                <div class="empty-icon">◇</div>
                <p>Aucun étudiant enregistré pour le moment.</p>
            </div>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Spécialisation</th>
                    <th>Note moyenne</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($etudiants as $i => $e): ?>
                <tr>
                    <td style="color:#333"><?= $i + 1 ?></td>
                    <td style="color:#e8d5a3; font-weight:500"><?= htmlspecialchars($e['nom']) ?></td>
                    <td><?= htmlspecialchars($e['prenom']) ?></td>
                    <td><span class="badge"><?= htmlspecialchars($e['specialisation']) ?></span></td>
                    <td>
                        <span class="note <?= $e['note_moyenne'] >= 14 ? 'high' : ($e['note_moyenne'] < 10 ? 'low' : '') ?>">
                            <?= number_format($e['note_moyenne'], 2) ?> / 20
                        </span>
                    </td>
                    <td>
                        <a href="?delete=<?= $e['id'] ?>" class="btn-delete"
                           onclick="return confirm('Supprimer cet étudiant ?')">Supprimer</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

    <!-- Formulaire ajout -->
    <div class="section-title">Ajouter un étudiant</div>
    <div class="form-card">
        <form method="POST">
            <div class="form-grid">
                <div class="form-group">
                    <label>Nom</label>
                    <input type="text" name="nom" placeholder="Ex: DUPONT" value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Prénom</label>
                    <input type="text" name="prenom" placeholder="Ex: Marie" value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>">
                </div>
                <div class="form-group full">
                    <label>Spécialisation</label>
                    <input type="text" name="specialisation" placeholder="Ex: Communication Digitale" value="<?= htmlspecialchars($_POST['specialisation'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Note moyenne (0 – 20)</label>
                    <input type="number" name="note_moyenne" step="0.01" min="0" max="20" placeholder="Ex: 14.50" value="<?= htmlspecialchars($_POST['note_moyenne'] ?? '') ?>">
                </div>
            </div>
            <button type="submit" class="btn-submit">Ajouter l'étudiant</button>
        </form>
    </div>
</main>

</body>
</html>
<?php $db->close(); ?>
