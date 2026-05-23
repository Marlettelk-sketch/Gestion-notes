<?php
require_once 'auth.php';
// =============================================
// CONNEXION À LA BASE DE DONNÉES
// =============================================
$host     = 'sql210.infinityfree.com';
$dbname   = 'if0_41992281_XXX';
$user     = 'if0_41992281';
$password = 'WALxqqYFL6';

// Vérifier qu'un ID est passé en paramètre
if (!isset($_GET['id'])) {
    header("Location: releve.php");
    exit;
}

$id_etudiant = $_GET['id'];

// =============================================
// INFORMATIONS DE L'ÉTUDIANT
// =============================================
$stmt = $pdo->prepare("
    SELECT e.*, c.nom_classe
    FROM etudiants e
    JOIN classes c ON e.id_classe = c.id_classe
    WHERE e.id_etudiant = ?
");
$stmt->execute([$id_etudiant]);
$etudiant = $stmt->fetch();

if ($etudiant == null) {
    header("Location: releve.php");
    exit;
}

// =============================================
// NOTES DE L'ÉTUDIANT
// =============================================
$stmt = $pdo->prepare("
    SELECT m.nom_matiere, m.coefficient,
           n.note_interro, n.note_devoir, n.moyenne
    FROM notes n
    JOIN matieres m ON n.id_matiere = m.id_matiere
    WHERE n.id_etudiant = ?
    ORDER BY m.nom_matiere
");
$stmt->execute([$id_etudiant]);
$notes = $stmt->fetchAll();

// =============================================
// CALCUL MOYENNE GÉNÉRALE ET RANG
// =============================================
$moy_generale = null;
if (count($notes) > 0) {
    $stmt = $pdo->prepare("SELECT ROUND(AVG(moyenne), 2) FROM notes WHERE id_etudiant = ?");
    $stmt->execute([$id_etudiant]);
    $moy_generale = $stmt->fetchColumn();
}

// Calcul du rang dans la classe
$stmt = $pdo->prepare("
    SELECT COUNT(*) + 1 AS rang
    FROM (
        SELECT e.id_etudiant, AVG(n.moyenne) AS moy
        FROM etudiants e
        JOIN notes n ON e.id_etudiant = n.id_etudiant
        WHERE e.id_classe = ?
        GROUP BY e.id_etudiant
    ) AS classement
    WHERE moy > ?
");
$stmt->execute([$etudiant['id_classe'], $moy_generale]);
$rang = $stmt->fetchColumn();

// Nombre total d'étudiants dans la classe
$stmt = $pdo->prepare("SELECT COUNT(*) FROM etudiants WHERE id_classe = ?");
$stmt->execute([$etudiant['id_classe']]);
$total_classe = $stmt->fetchColumn();

// Mention
if ($moy_generale == null)      { $mention = '—'; }
elseif ($moy_generale >= 16)    { $mention = 'Très bien'; }
elseif ($moy_generale >= 14)    { $mention = 'Bien'; }
elseif ($moy_generale >= 12)    { $mention = 'Assez bien'; }
elseif ($moy_generale >= 10)    { $mention = 'Passable'; }
else                            { $mention = 'Insuffisant'; }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bulletin — <?= $etudiant['nom'] ?> <?= $etudiant['prenom'] ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        .bulletin-header {
            text-align: center;
            margin-bottom: 28px;
            padding-bottom: 16px;
            border-bottom: 3px double #1A3A6B;
        }

        .bulletin-header h1 {
            font-size: 18px;
            color: #1A3A6B;
            margin-bottom: 4px;
        }

        .bulletin-header p {
            color: #555;
            font-size: 14px;
        }

        .infos-etudiant {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 24px;
            background: #F7F3EE;
            border: 1px solid #e3d9cf;
            border-radius: 6px;
            padding: 16px;
        }

        .info-ligne {
            font-size: 14px;
            color: #444;
        }

        .info-ligne strong {
            color: #1A3A6B;
        }

        .recap {
            display: flex;
            gap: 16px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .recap-box {
            flex: 1;
            min-width: 130px;
            background: #1A3A6B;
            color: white;
            border-radius: 6px;
            padding: 16px;
            text-align: center;
        }

        .recap-box h3 { font-size: 22px; color: #D4B896; }
        .recap-box p  { font-size: 12px; color: #B8CCEE; margin-top: 4px; }

        .annee {
            text-align: center;
            color: #888;
            font-size: 13px;
            margin-top: 24px;
        }
    </style>
</head>
<body>

<nav class="no-print">
    <a class="nav-logo" href="index.php">IRTM — Gestion des notes</a>
    <div>
        <a href="index.php">Accueil</a>
        <a href="etudiants.php">Étudiants</a>
        <a href="notes.php">Notes</a>
        <a href="releve.php">Relevé</a>
        <a href="connexion.php?action=deconnexion" style="color:#E74C3C; font-weight:600;">🔴 Déconnexion</a>
    </div>
</nav>

<div class="container">

    <!-- BOUTONS NO-PRINT -->
    <div class="no-print" style="display:flex; gap:10px; margin-bottom:16px;">
        <a href="releve.php" class="btn btn-primary">← Retour au relevé</a>
        <button onclick="window.print()" class="btn btn-print">🖨️ Imprimer le bulletin</button>
    </div>

    <div class="card">

        <!-- EN-TÊTE BULLETIN -->
        <div class="bulletin-header">
            <h1>IRTM — Institut de Recherche en Technologie et Management</h1>
            <p>Bulletin de notes individuel — Année académique 2025-2026</p>
        </div>

        <!-- INFORMATIONS ÉTUDIANT -->
        <div class="infos-etudiant">
            <div class="info-ligne">
                <strong>Matricule :</strong> <?= $etudiant['matricule'] ?>
            </div>
            <div class="info-ligne">
                <strong>Classe :</strong> <?= $etudiant['nom_classe'] ?>
            </div>
            <div class="info-ligne">
                <strong>Nom :</strong> <?= $etudiant['nom'] ?>
            </div>
            <div class="info-ligne">
                <strong>Prénom :</strong> <?= $etudiant['prenom'] ?>
            </div>
            <div class="info-ligne">
                <strong>Sexe :</strong> <?= $etudiant['sexe'] == 'M' ? 'Masculin' : 'Féminin' ?>
            </div>
            <div class="info-ligne">
                <strong>Date de naissance :</strong>
                <?php
                if ($etudiant['date_naissance'] != '') {
                    echo date('d/m/Y', strtotime($etudiant['date_naissance']));
                } else {
                    echo '—';
                }
                ?>
            </div>
            <div class="info-ligne">
                <strong>Lieu de naissance :</strong>
                <?= $etudiant['lieu_naissance'] != '' ? $etudiant['lieu_naissance'] : '—' ?>
            </div>
        </div>

        <!-- TABLEAU DES NOTES -->
        <?php if (count($notes) == 0): ?>
            <p style="color:#888; text-align:center;">Aucune note enregistrée pour cet étudiant.</p>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Matière</th>
                    <th>Coefficient</th>
                    <th>Interrogation (/20)</th>
                    <th>Devoir (/20)</th>
                    <th>Moyenne (/20)</th>
                    <th>Mention</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($notes as $n):
                    $moy = $n['moyenne'];
                    if ($moy >= 16)     { $mention_n = 'Très bien';  $badge = 'success'; }
                    elseif ($moy >= 14) { $mention_n = 'Bien';       $badge = 'success'; }
                    elseif ($moy >= 12) { $mention_n = 'Assez bien'; $badge = 'warning'; }
                    elseif ($moy >= 10) { $mention_n = 'Passable';   $badge = 'warning'; }
                    else                { $mention_n = 'Insuffisant';$badge = 'danger';  }
                ?>
                <tr>
                    <td><?= $n['nom_matiere'] ?></td>
                    <td style="text-align:center;"><?= $n['coefficient'] ?></td>
                    <td style="text-align:center;"><?= $n['note_interro'] ?></td>
                    <td style="text-align:center;"><?= $n['note_devoir'] ?></td>
                    <td style="text-align:center;"><strong><?= $moy ?></strong></td>
                    <td><span class="badge badge-<?= $badge ?>"><?= $mention_n ?></span></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- RÉCAPITULATIF -->
        <div class="recap">
            <div class="recap-box">
                <h3><?= $moy_generale != null ? $moy_generale . '/20' : '—' ?></h3>
                <p>Moyenne générale</p>
            </div>
            <div class="recap-box">
                <h3><?= $rang ?> / <?= $total_classe ?></h3>
                <p>Rang dans la classe</p>
            </div>
            <div class="recap-box">
                <h3><?= $mention ?></h3>
                <p>Mention</p>
            </div>
            <div class="recap-box">
                <h3><?= count($notes) ?></h3>
                <p>Matières évaluées</p>
            </div>
        </div>
        <?php endif; ?>

        <p class="annee">
            Formule de calcul : Moyenne = 30% × Interrogation + 70% × Devoir
        </p>

    </div>
</div>
</body>
</html>
