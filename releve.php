<?php
require_once 'auth.php';
// =============================================
// CONNEXION À LA BASE DE DONNÉES
// =============================================
$host     = 'sql210.infinityfree.com';
$dbname   = 'if0_41992281_XXX';
$user     = 'if0_41992281';
$password = 'WALxqqYFL6';
// =============================================
// CHARGEMENT DES CLASSES
// =============================================
$classes = $pdo->query("SELECT * FROM classes ORDER BY nom_classe")->fetchAll();

// Filtre par classe
$filtre_classe = 0;
if (isset($_GET['classe'])) {
    $filtre_classe = $_GET['classe'];
}

// =============================================
// CHARGEMENT DES ÉTUDIANTS AVEC LEUR MOYENNE
// =============================================
if ($filtre_classe != 0) {
    $stmt = $pdo->prepare("
        SELECT e.id_etudiant, e.matricule, e.nom, e.prenom,
               c.nom_classe,
               ROUND(AVG(n.moyenne), 2) AS moy_generale,
               COUNT(n.id_note) AS nb_notes
        FROM etudiants e
        JOIN classes c ON e.id_classe = c.id_classe
        LEFT JOIN notes n ON e.id_etudiant = n.id_etudiant
        WHERE e.id_classe = ?
        GROUP BY e.id_etudiant
        ORDER BY moy_generale DESC
    ");
    $stmt->execute([$filtre_classe]);
    $etudiants = $stmt->fetchAll();
} else {
    $etudiants = $pdo->query("
        SELECT e.id_etudiant, e.matricule, e.nom, e.prenom,
               c.nom_classe,
               ROUND(AVG(n.moyenne), 2) AS moy_generale,
               COUNT(n.id_note) AS nb_notes
        FROM etudiants e
        JOIN classes c ON e.id_classe = c.id_classe
        LEFT JOIN notes n ON e.id_etudiant = n.id_etudiant
        GROUP BY e.id_etudiant
        ORDER BY moy_generale DESC
    ")->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relevé de notes — IRTM</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav>
    <a class="nav-logo" href="index.php">IRTM — Gestion des notes</a>
    <div>
        <a href="index.php">Accueil</a>
        <a href="etudiants.php">Étudiants</a>
        <a href="notes.php">Notes</a>
        <a href="releve.php" class="active">Relevé</a>
        <a href="connexion.php?action=deconnexion" style="color:#E74C3C; font-weight:600;">🔴 Déconnexion</a>
    </div>
</nav>

<div class="container">

    <div class="card">
        <h2>📊 Relevé de notes — Classement par ordre de mérite</h2>

        <!-- Filtre par classe -->
        <form method="GET" style="margin-bottom:20px; display:flex; gap:10px; align-items:flex-end;">
            <div class="form-groupe" style="margin:0; flex:1;">
                <label>Filtrer par classe</label>
                <select name="classe" onchange="this.form.submit()">
                    <option value="0">-- Toutes les classes --</option>
                    <?php foreach ($classes as $c): ?>
                        <option value="<?= $c['id_classe'] ?>"
                            <?php if ($filtre_classe == $c['id_classe']) echo 'selected'; ?>>
                            <?= $c['nom_classe'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if ($filtre_classe != 0): ?>
                <a href="releve.php" class="btn btn-danger" style="padding:10px 16px;">✖ Réinitialiser</a>
            <?php endif; ?>
            <button type="button" class="btn btn-print no-print"
                    onclick="window.print()">🖨️ Imprimer</button>
        </form>

        <?php if (count($etudiants) == 0): ?>
            <p style="color:#888">Aucun étudiant trouvé.</p>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Rang</th>
                    <th>Matricule</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Classe</th>
                    <th>Nb. matières</th>
                    <th>Moyenne générale</th>
                    <th>Mention</th>
                    <th class="no-print">Bulletin</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $rang = 1;
                foreach ($etudiants as $e):
                    $moy = $e['moy_generale'];

                    if ($moy == null) {
                        $mention = '—';
                        $badge   = 'warning';
                    } elseif ($moy >= 16) {
                        $mention = 'Très bien';
                        $badge   = 'success';
                    } elseif ($moy >= 14) {
                        $mention = 'Bien';
                        $badge   = 'success';
                    } elseif ($moy >= 12) {
                        $mention = 'Assez bien';
                        $badge   = 'warning';
                    } elseif ($moy >= 10) {
                        $mention = 'Passable';
                        $badge   = 'warning';
                    } else {
                        $mention = 'Insuffisant';
                        $badge   = 'danger';
                    }

                    if ($rang == 1)     $classe_rang = 'rang-1';
                    elseif ($rang == 2) $classe_rang = 'rang-2';
                    elseif ($rang == 3) $classe_rang = 'rang-3';
                    else                $classe_rang = '';
                ?>
                <tr>
                    <td><span class="<?= $classe_rang ?>"><?= $rang ?></span></td>
                    <td><?= $e['matricule'] ?></td>
                    <td><?= $e['nom'] ?></td>
                    <td><?= $e['prenom'] ?></td>
                    <td><?= $e['nom_classe'] ?></td>
                    <td style="text-align:center;"><?= $e['nb_notes'] ?></td>
                    <td><strong><?= $moy != null ? $moy . ' / 20' : '—' ?></strong></td>
                    <td><span class="badge badge-<?= $badge ?>"><?= $mention ?></span></td>
                    <td class="no-print">
                        <a href="bulletin.php?id=<?= $e['id_etudiant'] ?>"
                           class="btn btn-primary" style="padding:6px 12px; font-size:13px;">
                           📄 Voir
                        </a>
                    </td>
                </tr>
                <?php
                $rang++;
                endforeach;
                ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

</div>
</body>
</html>
