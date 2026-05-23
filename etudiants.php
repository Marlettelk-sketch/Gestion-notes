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
// SUPPRESSION D'UN ÉTUDIANT
// =============================================
if (isset($_GET['supprimer']) && isset($_GET['confirme']) && $_GET['confirme'] == 'oui') {
    $id   = $_GET['supprimer'];
    $stmt = $pdo->prepare("DELETE FROM etudiants WHERE id_etudiant = ?");
    $stmt->execute([$id]);
    $message = "Étudiant supprimé avec succès.";
}

// =============================================
// AJOUT D'UN ÉTUDIANT
// =============================================
if (isset($_POST['action']) && $_POST['action'] == 'ajouter') {
    $matricule      = $_POST['matricule'];
    $nom            = $_POST['nom'];
    $prenom         = $_POST['prenom'];
    $sexe           = $_POST['sexe'];
    $date_naissance = $_POST['date_naissance'];
    $lieu_naissance = $_POST['lieu_naissance'];
    $id_classe      = $_POST['id_classe'];

    if ($matricule == '' || $nom == '' || $prenom == '' || $sexe == '' || $id_classe == '') {
        $erreur = "Veuillez remplir tous les champs obligatoires.";
    } else {
        // Vérifier si le matricule existe déjà
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM etudiants WHERE matricule = ?");
        $stmt->execute([$matricule]);
        $existe = $stmt->fetchColumn();

        if ($existe > 0) {
            $erreur = "Ce matricule existe déjà.";
        } else {
            $stmt = $pdo->prepare("
                INSERT INTO etudiants (matricule, nom, prenom, sexe, date_naissance, lieu_naissance, id_classe)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$matricule, strtoupper($nom), $prenom, $sexe, $date_naissance, $lieu_naissance, $id_classe]);
            $message = "Étudiant ajouté avec succès.";
        }
    }
}

// =============================================
// MODIFICATION D'UN ÉTUDIANT
// =============================================
if (isset($_POST['action']) && $_POST['action'] == 'modifier') {
    $id_etudiant    = $_POST['id_etudiant'];
    $matricule      = $_POST['matricule'];
    $nom            = $_POST['nom'];
    $prenom         = $_POST['prenom'];
    $sexe           = $_POST['sexe'];
    $date_naissance = $_POST['date_naissance'];
    $lieu_naissance = $_POST['lieu_naissance'];
    $id_classe      = $_POST['id_classe'];

    if ($matricule == '' || $nom == '' || $prenom == '' || $sexe == '' || $id_classe == '') {
        $erreur = "Veuillez remplir tous les champs obligatoires.";
    } else {
        $stmt = $pdo->prepare("
            UPDATE etudiants
            SET matricule = ?, nom = ?, prenom = ?, sexe = ?, date_naissance = ?, lieu_naissance = ?, id_classe = ?
            WHERE id_etudiant = ?
        ");
        $stmt->execute([$matricule, strtoupper($nom), $prenom, $sexe, $date_naissance, $lieu_naissance, $id_classe, $id_etudiant]);
        $message = "Étudiant modifié avec succès.";
    }
}

// =============================================
// CHARGEMENT DES DONNÉES
// =============================================
$classes = $pdo->query("SELECT * FROM classes ORDER BY nom_classe")->fetchAll();

$etudiants = $pdo->query("
    SELECT e.*, c.nom_classe
    FROM etudiants e
    JOIN classes c ON e.id_classe = c.id_classe
    ORDER BY e.nom, e.prenom
")->fetchAll();

// Étudiant à modifier
$etudiant_edit = null;
if (isset($_GET['modifier'])) {
    $stmt = $pdo->prepare("SELECT * FROM etudiants WHERE id_etudiant = ?");
    $stmt->execute([$_GET['modifier']]);
    $etudiant_edit = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Étudiants — IRTM</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<nav>
    <a class="nav-logo" href="index.php">IRTM — Gestion des notes</a>
    <div>
        <a href="index.php">Accueil</a>
        <a href="etudiants.php" class="active">Étudiants</a>
        <a href="notes.php">Notes</a>
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

    <!-- FORMULAIRE AJOUT / MODIFICATION -->
    <div class="card">
        <?php if ($etudiant_edit != null): ?>
            <h2>✏️ Modifier un étudiant</h2>
        <?php else: ?>
            <h2>➕ Ajouter un étudiant</h2>
        <?php endif; ?>

        <form method="POST">

            <?php if ($etudiant_edit != null): ?>
                <input type="hidden" name="action"      value="modifier">
                <input type="hidden" name="id_etudiant" value="<?= $etudiant_edit['id_etudiant'] ?>">
            <?php else: ?>
                <input type="hidden" name="action" value="ajouter">
            <?php endif; ?>

            <div class="form-row">
                <div class="form-groupe">
                    <label>Matricule *</label>
                    <input type="text" name="matricule" placeholder="Ex : IRTM-006" required
                           value="<?php if ($etudiant_edit != null) echo $etudiant_edit['matricule']; ?>">
                </div>
                <div class="form-groupe">
                    <label>Classe *</label>
                    <select name="id_classe" required>
                        <option value="">-- Choisir --</option>
                        <?php foreach ($classes as $c): ?>
                            <option value="<?= $c['id_classe'] ?>"
                                <?php if ($etudiant_edit != null && $etudiant_edit['id_classe'] == $c['id_classe']) echo 'selected'; ?>>
                                <?= $c['nom_classe'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-groupe">
                    <label>Nom *</label>
                    <input type="text" name="nom" placeholder="Nom de famille" required
                           value="<?php if ($etudiant_edit != null) echo $etudiant_edit['nom']; ?>">
                </div>
                <div class="form-groupe">
                    <label>Prénom *</label>
                    <input type="text" name="prenom" placeholder="Prénom" required
                           value="<?php if ($etudiant_edit != null) echo $etudiant_edit['prenom']; ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-groupe">
                    <label>Sexe *</label>
                    <select name="sexe" required>
                        <option value="">-- Choisir --</option>
                        <option value="M" <?php if ($etudiant_edit != null && $etudiant_edit['sexe'] == 'M') echo 'selected'; ?>>Masculin</option>
                        <option value="F" <?php if ($etudiant_edit != null && $etudiant_edit['sexe'] == 'F') echo 'selected'; ?>>Féminin</option>
                    </select>
                </div>
                <div class="form-groupe">
                    <label>Lieu de naissance</label>
                    <input type="text" name="lieu_naissance" placeholder="Ex : Cotonou"
                           value="<?php if ($etudiant_edit != null) echo $etudiant_edit['lieu_naissance']; ?>">
                </div>
            </div>

            <div class="form-groupe" style="max-width:300px;">
                <label>Date de naissance</label>
                <input type="date" name="date_naissance"
                       value="<?php if ($etudiant_edit != null) echo $etudiant_edit['date_naissance']; ?>">
            </div>

            <div style="display:flex; gap:10px; margin-top:6px;">
                <?php if ($etudiant_edit != null): ?>
                    <button type="submit" class="btn btn-warning">💾 Enregistrer les modifications</button>
                    <a href="etudiants.php" class="btn btn-danger">✖ Annuler</a>
                <?php else: ?>
                    <button type="submit" class="btn btn-primary">➕ Ajouter</button>
                <?php endif; ?>
            </div>

        </form>
    </div>

    <!-- LISTE DES ÉTUDIANTS -->
    <div class="card">
        <h2>📋 Liste des étudiants (<?= count($etudiants) ?>)</h2>

        <?php if (count($etudiants) == 0): ?>
            <p style="color:#888">Aucun étudiant enregistré.</p>
        <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Matricule</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Sexe</th>
                    <th>Date de naissance</th>
                    <th>Lieu de naissance</th>
                    <th>Classe</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($etudiants as $e): ?>
                <tr>
                    <td><?= $e['matricule'] ?></td>
                    <td><?= $e['nom'] ?></td>
                    <td><?= $e['prenom'] ?></td>
                    <td><?= $e['sexe'] == 'M' ? 'Masculin' : 'Féminin' ?></td>
                    <td>
                        <?php
                        if ($e['date_naissance'] != '') {
                            echo date('d/m/Y', strtotime($e['date_naissance']));
                        } else {
                            echo '—';
                        }
                        ?>
                    </td>
                    <td><?= $e['lieu_naissance'] != '' ? $e['lieu_naissance'] : '—' ?></td>
                    <td><?= $e['nom_classe'] ?></td>
                    <td style="display:flex; gap:6px; flex-wrap:wrap;">
                        <a href="bulletin.php?id=<?= $e['id_etudiant'] ?>"
                           class="btn btn-primary" style="padding:6px 12px; font-size:13px;">
                           📄 Bulletin
                        </a>
                        <a href="etudiants.php?modifier=<?= $e['id_etudiant'] ?>"
                           class="btn btn-warning" style="padding:6px 12px; font-size:13px;">
                           ✏️ Modifier
                        </a>
                        <a href="etudiants.php?supprimer=<?= $e['id_etudiant'] ?>&confirme=oui"
                           class="btn btn-danger" style="padding:6px 12px; font-size:13px;"
                           onclick="return confirm('Supprimer cet étudiant ? Toutes ses notes seront supprimées.')">
                           🗑️ Supprimer
                        </a>
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
