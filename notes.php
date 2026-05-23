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
// VARIABLES MESSAGE / ERREUR
// =============================================
$message = '';
$erreur  = '';

// =============================================
// SUPPRESSION D'UNE NOTE
// =============================================
if (isset($_GET['supprimer']) && isset($_GET['confirme']) && $_GET['confirme'] == 'oui') {
    $id   = $_GET['supprimer'];
    $stmt = $pdo->prepare("DELETE FROM notes WHERE id_note = ?");
    $stmt->execute([$id]);
    $message = "Note supprimée avec succès.";
}

// =============================================
// AJOUT D'UNE NOTE
// =============================================
if (isset($_POST['action']) && $_POST['action'] == 'ajouter') {
    $id_etudiant  = $_POST['id_etudiant'];
    $id_matiere   = $_POST['id_matiere'];
    $note_interro = $_POST['note_interro'];
    $note_devoir  = $_POST['note_devoir'];

    if ($id_etudiant == '' || $id_matiere == '') {
        $erreur = "Veuillez sélectionner un étudiant et une matière.";
    } elseif ($note_interro < 0 || $note_interro > 20 || $note_devoir < 0 || $note_devoir > 20) {
        $erreur = "Les notes doivent être comprises entre 0 et 20.";
    } else {
        // Vérifier si une note existe déjà pour cet étudiant dans cette matière
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM notes WHERE id_etudiant = ? AND id_matiere = ?");
        $stmt->execute([$id_etudiant, $id_matiere]);
        $existe = $stmt->fetchColumn();

        if ($existe > 0) {
            $erreur = "Une note existe déjà pour cet étudiant dans cette matière.";
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO notes (id_etudiant, id_matiere, note_interro, note_devoir)
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$id_etudiant, $id_matiere, $note_interro, $note_devoir]);
            $message = "Note enregistrée avec succès.";
        }
    }
}

// =============================================
// MODIFICATION D'UNE NOTE
// =============================================
if (isset($_POST['action']) && $_POST['action'] == 'modifier') {
    $id_note      = $_POST['id_note'];
    $note_interro = $_POST['note_interro'];
    $note_devoir  = $_POST['note_devoir'];

    if ($note_interro < 0 || $note_interro > 20 || $note_devoir < 0 || $note_devoir > 20) {
        $erreur = "Les notes doivent être comprises entre 0 et 20.";
    } else {
        $stmt = $pdo->prepare("
            UPDATE notes SET note_interro = ?, note_devoir = ?
            WHERE id_note = ?
        ");
        $stmt->execute([$note_interro, $note_devoir, $id_note]);
        $message = "Note modifiée avec succès.";
    }
}

// =============================================
// CHARGEMENT DES DONNÉES
// =============================================
$etudiants = $pdo->query("
    SELECT e.id_etudiant, e.nom, e.prenom, e.matricule, c.nom_classe
    FROM etudiants e
    JOIN classes c ON e.id_classe = c.id_classe
    ORDER BY e.nom, e.prenom
")->fetchAll();

$matieres = $pdo->query("SELECT * FROM matieres ORDER BY nom_matiere")->fetchAll();

// Filtrage par étudiant
$filtre_etudiant = 0;
if (isset($_GET['etudiant'])) {
    $filtre_etudiant = $_GET['etudiant'];
}

if ($filtre_etudiant != 0) {
    $stmt = $pdo->prepare("
        SELECT n.id_note, n.note_interro, n.note_devoir, n.moyenne,
               e.nom, e.prenom, e.matricule,
               m.nom_matiere, m.coefficient
        FROM notes n
        JOIN etudiants e ON n.id_etudiant = e.id_etudiant
        JOIN matieres  m ON n.id_matiere  = m.id_matiere
        WHERE n.id_etudiant = ?
        ORDER BY m.nom_matiere
    ");
    $stmt->execute([$filtre_etudiant]);
    $notes = $stmt->fetchAll();
} else {
    $notes = $pdo->query("
        SELECT n.id_note, n.note_interro, n.note_devoir, n.moyenne,
               e.nom, e.prenom, e.matricule,
               m.nom_matiere, m.coefficient
        FROM notes n
        JOIN etudiants e ON n.id_etudiant = e.id_etudiant
        JOIN matieres  m ON n.id_matiere  = m.id_matiere
        ORDER BY e.nom, e.prenom, m.nom_matiere
    ")->fetchAll();
}

// Note à modifier
$note_edit = null;
if (isset($_GET['modifier'])) {
    $stmt = $pdo->prepare("
        SELECT n.*, e.nom, e.prenom, m.nom_matiere
        FROM notes n
        JOIN etudiants e ON n.id_etudiant = e.id_etudiant
        JOIN matieres  m ON n.id_matiere  = m.id_matiere
        WHERE n.id_note = ?
    ");
    $stmt->execute([$_GET['modifier']]);
    $note_edit = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notes — IRTM</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav>
    <a class="nav-logo" href="index.php">IRTM — Gestion des notes</a>
    <div>
        <a href="index.php">Accueil</a>
        <a href="etudiants.php">Étudiants</a>
        <a href="notes.php" class="active">Notes</a>
        <a href="releve.php">Relevé</a>
        <a href="connexion.php?action=deconnexion" style="color:#E74C3C; font-weight:600;">🔴 Déconnexion</a>
    </div>
</nav>

<div class="container">

    <!-- MESSAGES -->
    <?php if ($message != ''): ?>
        <div class="alert alert-success"><?= $message ?></div>
    <?php endif; ?>
    <?php if ($erreur != ''): ?>
        <div class="alert alert-danger"><?= $erreur ?></div>
    <?php endif; ?>

    <!-- FORMULAIRE AJOUT -->
    <?php if ($note_edit == null): ?>
    <div class="card">
        <h2>➕ Saisir une note</h2>
        <form method="POST">
            <input type="hidden" name="action" value="ajouter">

            <div class="form-row">
                <div class="form-groupe">
                    <label>Étudiant *</label>
                    <select name="id_etudiant" required>
                        <option value="">-- Choisir un étudiant --</option>
                        <?php foreach ($etudiants as $e): ?>
                            <option value="<?= $e['id_etudiant'] ?>">
                                <?= $e['nom'] ?> <?= $e['prenom'] ?>
                                (<?= $e['matricule'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-groupe">
                    <label>Matière *</label>
                    <select name="id_matiere" required>
                        <option value="">-- Choisir une matière --</option>
                        <?php foreach ($matieres as $m): ?>
                            <option value="<?= $m['id_matiere'] ?>">
                                <?= $m['nom_matiere'] ?> (coef. <?= $m['coefficient'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-groupe">
                    <label>Note d'interrogation * (0 - 20)</label>
                    <input type="number" name="note_interro" min="0" max="20"
                           step="0.25" placeholder="Ex : 14" required>
                </div>
                <div class="form-groupe">
                    <label>Note de devoir * (0 - 20)</label>
                    <input type="number" name="note_devoir" min="0" max="20"
                           step="0.25" placeholder="Ex : 16" required>
                </div>
            </div>

            <div style="background:var(--bg); border:1px solid #e3d9cf; border-radius:5px;
                        padding:12px 16px; margin-bottom:16px; font-size:13px; color:#555;">
                💡 Formule : <strong>Moyenne = 30% × Interrogation + 70% × Devoir</strong>
            </div>

            <button type="submit" class="btn btn-primary">💾 Enregistrer la note</button>
        </form>
    </div>

    <!-- FORMULAIRE MODIFICATION -->
    <?php else: ?>
    <div class="card">
        <h2>✏️ Modifier une note</h2>
        <p style="color:#555; margin-bottom:16px; font-size:14px;">
            Étudiant : <strong><?= $note_edit['nom'] ?> <?= $note_edit['prenom'] ?></strong>
            &nbsp;|&nbsp;
            Matière : <strong><?= $note_edit['nom_matiere'] ?></strong>
        </p>
        <form method="POST">
            <input type="hidden" name="action"  value="modifier">
            <input type="hidden" name="id_note" value="<?= $note_edit['id_note'] ?>">

            <div class="form-row">
                <div class="form-groupe">
                    <label>Note d'interrogation * (0 - 20)</label>
                    <input type="number" name="note_interro" min="0" max="20"
                           step="0.25" value="<?= $note_edit['note_interro'] ?>" required>
                </div>
                <div class="form-groupe">
                    <label>Note de devoir * (0 - 20)</label>
                    <input type="number" name="note_devoir" min="0" max="20"
                           step="0.25" value="<?= $note_edit['note_devoir'] ?>" required>
                </div>
            </div>

            <div style="display:flex; gap:10px; margin-top:6px;">
                <button type="submit" class="btn btn-warning">💾 Enregistrer</button>
                <a href="notes.php" class="btn btn-danger">✖ Annuler</a>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <!-- LISTE DES NOTES -->
    <div class="card">
        <h2>📋 Liste des notes</h2>

        <!-- Filtre par étudiant -->
        <form method="GET" style="margin-bottom:16px; display:flex; gap:10px; align-items:flex-end;">
            <div class="form-groupe" style="margin:0; flex:1;">
                <label>Filtrer par étudiant</label>
                <select name="etudiant" onchange="this.form.submit()">
                    <option value="0">-- Tous les étudiants --</option>
                    <?php foreach ($etudiants as $e): ?>
                        <option value="<?= $e['id_etudiant'] ?>"
                            <?php if ($filtre_etudiant == $e['id_etudiant']) echo 'selected'; ?>>
                            <?= $e['nom'] ?> <?= $e['prenom'] ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <?php if ($filtre_etudiant != 0): ?>
                <a href="notes.php" class="btn btn-danger" style="padding:10px 16px;">✖ Réinitialiser</a>
            <?php endif; ?>
        </form>

        <?php if (count($notes) == 0): ?>
            <p style="color:#888">Aucune note enregistrée.</p>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Étudiant</th>
                    <th>Matière</th>
                    <th>Interrogation</th>
                    <th>Devoir</th>
                    <th>Moyenne</th>
                    <th>Mention</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($notes as $n):
                    $moy = $n['moyenne'];
                    if ($moy >= 16)     { $mention = 'Très bien';  $badge = 'success'; }
                    elseif ($moy >= 14) { $mention = 'Bien';       $badge = 'success'; }
                    elseif ($moy >= 12) { $mention = 'Assez bien'; $badge = 'warning'; }
                    elseif ($moy >= 10) { $mention = 'Passable';   $badge = 'warning'; }
                    else                { $mention = 'Insuffisant';$badge = 'danger';  }
                ?>
                <tr>
                    <td><?= $n['nom'] ?> <?= $n['prenom'] ?></td>
                    <td><?= $n['nom_matiere'] ?></td>
                    <td><?= $n['note_interro'] ?> / 20</td>
                    <td><?= $n['note_devoir'] ?> / 20</td>
                    <td><strong><?= $moy ?> / 20</strong></td>
                    <td><span class="badge badge-<?= $badge ?>"><?= $mention ?></span></td>
                    <td style="display:flex; gap:6px;">
                        <a href="notes.php?modifier=<?= $n['id_note'] ?>"
                           class="btn btn-warning" style="padding:6px 12px; font-size:13px;">✏️</a>
                        <a href="notes.php?supprimer=<?= $n['id_note'] ?>&confirme=oui"
                           class="btn btn-danger" style="padding:6px 12px; font-size:13px;"
                           onclick="return confirm('Supprimer cette note ?')">🗑️</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

</div>
</body>
</html>
