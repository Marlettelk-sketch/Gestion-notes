<?php
require_once 'auth.php';
// =============================================
// CONNEXION À LA BASE DE DONNÉES
// =============================================
$host     = 'https://gestion-notes-nine.vercel.app/';
$dbname   = 'if0_41992281_XXX';
$user     = 'if0_41992281';
$password = 'WALxqqYFL6';

// =============================================
// STATISTIQUES
// =============================================
$nb_etudiants = $pdo->query("SELECT COUNT(*) FROM etudiants")->fetchColumn();
$nb_classes   = $pdo->query("SELECT COUNT(*) FROM classes")->fetchColumn();
$nb_matieres  = $pdo->query("SELECT COUNT(*) FROM matieres")->fetchColumn();
$nb_notes     = $pdo->query("SELECT COUNT(*) FROM notes")->fetchColumn();
$moy_generale = $pdo->query("SELECT ROUND(AVG(moyenne), 2) FROM notes")->fetchColumn();

// =============================================
// TOP 5 MEILLEURS ÉTUDIANTS
// =============================================
$stmt = $pdo->query("
    SELECT e.nom, e.prenom, c.nom_classe,
           ROUND(AVG(n.moyenne), 2) AS moy_generale
    FROM etudiants e
    JOIN classes c ON e.id_classe = c.id_classe
    JOIN notes n   ON e.id_etudiant = n.id_etudiant
    GROUP BY e.id_etudiant
    ORDER BY moy_generale DESC
    LIMIT 5
");
$top5 = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tableau de bord — IRTM</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav>
    <a class="nav-logo" href="index.php">IRTM — Gestion des notes</a>
    <div>
        <a href="index.php" class="active">Accueil</a>
        <a href="etudiants.php">Étudiants</a>
        <a href="notes.php">Notes</a>
        <a href="releve.php">Relevé</a>
        <a href="connexion.php?action=deconnexion" style="color:#E74C3C; font-weight:600;">🔴 Déconnexion</a>
    </div>
</nav>

<div class="container">

    <!-- STATISTIQUES -->
    <div class="card">
        <h2>Tableau de bord</h2>
        <div class="stats">
            <div class="stat-box">
                <h3><?= $nb_etudiants ?></h3>
                <p>Étudiants inscrits</p>
            </div>
            <div class="stat-box">
                <h3><?= $nb_classes ?></h3>
                <p>Classes</p>
            </div>
            <div class="stat-box">
                <h3><?= $nb_matieres ?></h3>
                <p>Matières</p>
            </div>
            <div class="stat-box">
                <h3><?= $nb_notes ?></h3>
                <p>Notes enregistrées</p>
            </div>
            <div class="stat-box">
                <h3><?= $moy_generale ? $moy_generale : '—' ?></h3>
                <p>Moyenne générale</p>
            </div>
        </div>
    </div>

    <!-- TOP 5 -->
    <div class="card">
        <h2>Top 5 — Meilleurs étudiants</h2>

        <?php if (count($top5) == 0): ?>
            <p style="color:#888">Aucune note enregistrée pour le moment.</p>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Rang</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Classe</th>
                    <th>Moyenne générale</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $rang = 1;
                foreach ($top5 as $e):
                    if ($rang == 1)      $medaille = '🥇';
                    elseif ($rang == 2)  $medaille = '🥈';
                    elseif ($rang == 3)  $medaille = '🥉';
                    else                 $medaille = $rang;

                    $moy = $e['moy_generale'];
                    if ($moy >= 10) $badge = 'success';
                    else            $badge = 'danger';
                ?>
                <tr>
                    <td><?= $medaille ?></td>
                    <td><?= $e['nom'] ?></td>
                    <td><?= $e['prenom'] ?></td>
                    <td><?= $e['nom_classe'] ?></td>
                    <td><span class="badge badge-<?= $badge ?>"><?= $moy ?> / 20</span></td>
                </tr>
                <?php
                $rang++;
                endforeach;
                ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

    <!-- ACCÈS RAPIDE -->
    <div class="card">
        <h2>Accès rapide</h2>
        <div style="display:flex; gap:12px; flex-wrap:wrap;">
            <a href="etudiants.php" class="btn btn-primary">👤 Gérer les étudiants</a>
            <a href="notes.php"     class="btn btn-success">📝 Saisir des notes</a>
            <a href="releve.php"    class="btn btn-warning">📊 Voir le relevé</a>
        </div>
    </div>

</div>
</body>
</html>
