<?php
// Démarrer la session
require "cache.php";

$message = '';

// Vérifier si l'ID du fichier est passé en paramètre
if (isset($_GET['id'])) {
    $id_fichier = intval($_GET['id']);

    // Récupérer les informations du fichier à partir de la base de données
    $query = "SELECT * FROM fichiers WHERE id_fichier = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $id_fichier);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $fichier = mysqli_fetch_assoc($result);

    if (!$fichier) {
        $message = "Fichier non trouvé.";
    }

    mysqli_stmt_close($stmt);
} else {
    header('Location: gestion_fichiers.php');
    exit();
}

// Traitement du formulaire de modification
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_fichier = intval($_POST['id_fichier']);
    $id_membre = $_POST['id_membre'];
    $nom_fichier = $_POST['nom_fichier'];
    $type_fichier = $_POST['type_fichier'];
    $chemin_fichier = $fichier['chemin_fichier']; // Par défaut, conserver l'ancien chemin
    $date_ajout = $fichier['date_ajout']; // Conserver la date d'ajout initiale

    // Si un nouveau fichier est téléchargé, le déplacer et mettre à jour le chemin
    if ($_FILES['fichier']['name']) {
        $chemin_fichier = 'uploads/' . basename($_FILES['fichier']['name']);
        move_uploaded_file($_FILES['fichier']['tmp_name'], $chemin_fichier);
    }

    // Préparation de la requête de mise à jour
    $sql = "UPDATE fichiers SET id_membre = ?, nom_fichier = ?, type_fichier = ?, chemin_fichier = ? WHERE id_fichier = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 'isssi', $id_membre, $nom_fichier, $type_fichier, $chemin_fichier, $id_fichier);

    // Exécution de la requête
    if (mysqli_stmt_execute($stmt)) {
        $message = "Fichier mis à jour avec succès.";
        // Enregistrement dans l'historique
        $action = "Modification d'un fichier";
        $details = "Détails de la modification de $nom_fichier";
        enregistrerHistorique($conn, $user_id, $action, $details);
        // Redirection après mise à jour
        header('Location: gestion_fichiers.php');
        exit();
    } else {
        $message = "Erreur lors de la mise à jour du fichier : " . htmlspecialchars(mysqli_stmt_error($stmt));
    }

    mysqli_stmt_close($stmt);
}

// // Fermer la connexion à la base de données
// mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier le Fichier</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<body>
    <div class="container">
        <h2 class="mt-4">Modifier le Fichier</h2>
        <?php if ($message) : ?>
            <div class="alert alert-info"><?php echo $message; ?></div>
        <?php endif; ?>
        <form action="edit_fichier.php?id=<?php echo $id_fichier; ?>" method="post" enctype="multipart/form-data">
            <input type="hidden" name="id_fichier" value="<?php echo $id_fichier; ?>">
            <div class="form-group">
                <label for="id_membre">Membre</label>
                <select class="form-control" id="id_membre" name="id_membre" required>
                    <?php
                    // Récupération des membres
                    $query = "SELECT id_membre, nom, prenom FROM membres";
                    $result = mysqli_query($conn, $query);

                    if (mysqli_num_rows($result) > 0) {
                        while ($row = mysqli_fetch_assoc($result)) {
                            $selected = $row['id_membre'] == $fichier['id_membre'] ? 'selected' : '';
                            echo "<option value='" . $row['id_membre'] . "' $selected>" . $row['nom'] . " " . $row['prenom'] . "</option>";
                        }
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="nom_fichier">Nom du Fichier</label>
                <input type="text" class="form-control" id="nom_fichier" name="nom_fichier" value="<?php echo $fichier['nom_fichier']; ?>" required>
            </div>
            <div class="form-group">
                <label for="type_fichier">Type de Fichier</label>
                <input type="text" class="form-control" id="type_fichier" name="type_fichier" value="<?php echo $fichier['type_fichier']; ?>" required>
            </div>
            <div class="form-group">
                <label for="fichier">Changer de Fichier (optionnel)</label>
                <input type="file" class="form-control-file" id="fichier" name="fichier">
            </div>
            <button type="submit" class="btn btn-primary">Mettre à Jour</button>
            <a href="gestion_fichiers.php" class="btn btn-secondary">Annuler</a>
        </form>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>