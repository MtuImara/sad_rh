<?php
// Démarrer la session
require "cache.php";

$message = '';

// Vérification si l'ID du projet est passé en paramètre
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id_projet = $_GET['id'];

    // Requête SQL pour récupérer les informations du projet à modifier
    $query = "SELECT * FROM projects WHERE id_project = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $id_projet);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) == 1) {
        $projet = mysqli_fetch_assoc($result);
        $nom_projet = $projet['nom_project'];
        $description_projet = $projet['description_project'];
        $bailleur = $projet['bailleur'];
        $budget = $projet['budget'];

        // Calcul de l'effectif initial
        $query_effectif = "SELECT COUNT(*) AS count FROM membres WHERE id_project = ?";
        $stmt_effectif = mysqli_prepare($conn, $query_effectif);
        mysqli_stmt_bind_param($stmt_effectif, "i", $id_projet);
        mysqli_stmt_execute($stmt_effectif);
        $result_effectif = mysqli_stmt_get_result($stmt_effectif);
        $row_effectif = mysqli_fetch_assoc($result_effectif);
        $effectif = $row_effectif['count'];

        mysqli_stmt_close($stmt_effectif);
    } else {
        echo "Aucun projet trouvé.";
        exit();
    }
} else {
    echo "ID du projet non spécifié.";
    exit();
}

// Requête SQL pour récupérer tous les membres de l'organisation
$query_tous_membres = "SELECT * FROM membres";
$result_tous_membres = mysqli_query($conn, $query_tous_membres);

// Requête SQL pour récupérer tous les membres actuellement associés à ce projet
$query_membres_associés = "SELECT id_membre FROM membres WHERE id_project = ?";
$stmt_membres_associés = mysqli_prepare($conn, $query_membres_associés);
mysqli_stmt_bind_param($stmt_membres_associés, "i", $id_projet);
mysqli_stmt_execute($stmt_membres_associés);
$result_membres_associés = mysqli_stmt_get_result($stmt_membres_associés);

// Tableau pour stocker les ID des membres associés à ce projet
$membres_associés_ids = [];
while ($row = mysqli_fetch_assoc($result_membres_associés)) {
    $membres_associés_ids[] = $row['id_membre'];
}

// Fermeture de la requête préparée
mysqli_stmt_close($stmt_membres_associés);

// Vérification si la requête est de type POST (lorsque le formulaire est soumis)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Traitement pour la mise à jour des membres associés au projet
    $membres_post = isset($_POST['membres']) ? $_POST['membres'] : [];

    // Liste des membres à supprimer du projet
    $membres_a_supprimer = array_diff($membres_associés_ids, $membres_post);

    // Liste des membres à ajouter au projet
    $membres_a_ajouter = array_diff($membres_post, $membres_associés_ids);

    // Supprimer les membres désélectionnés du projet
    if (!empty($membres_a_supprimer)) {
        $query_remove_membre = "UPDATE membres SET id_project = NULL WHERE id_membre IN (" . implode(',', $membres_a_supprimer) . ")";
        mysqli_query($conn, $query_remove_membre);

        // Mettre à jour l'effectif
        $effectif -= count($membres_a_supprimer);
    }

    // Ajouter les membres sélectionnés au projet
    if (!empty($membres_a_ajouter)) {
        $query_add_membre = "UPDATE membres SET id_project = ? WHERE id_membre IN (" . implode(',', $membres_a_ajouter) . ")";
        $stmt_add_membre = mysqli_prepare($conn, $query_add_membre);
        mysqli_stmt_bind_param($stmt_add_membre, "i", $id_projet);
        mysqli_stmt_execute($stmt_add_membre);
        mysqli_stmt_close($stmt_add_membre);

        // Mettre à jour l'effectif
        $effectif += count($membres_a_ajouter);

        // Mettre à jour le projet dans les informations des membres ajoutés
        foreach ($membres_a_ajouter as $id_membre) {
            $query_update_membre = "UPDATE membres SET id_project = ? WHERE id_membre = ?";
            $stmt_update_membre = mysqli_prepare($conn, $query_update_membre);
            mysqli_stmt_bind_param($stmt_update_membre, "ii", $id_projet, $id_membre);
            mysqli_stmt_execute($stmt_update_membre);
            mysqli_stmt_close($stmt_update_membre);
        }
    }

    // Mise à jour de l'effectif dans la table des projets
    $query_update_effectif = "UPDATE projects SET effectif = ? WHERE id_project = ?";
    $stmt_update_effectif = mysqli_prepare($conn, $query_update_effectif);
    mysqli_stmt_bind_param($stmt_update_effectif, "ii", $effectif, $id_projet);
    mysqli_stmt_execute($stmt_update_effectif);
    mysqli_stmt_close($stmt_update_effectif);

    $message = "Les membres du projet ont été mis à jour avec succès.";
    // Enregistrement dans l'historique
    $action = "Modification d'un projet";
    $details = "Détails de la modification de $nom_projet";
    enregistrerHistorique($conn, $user_id, $action, $details);
    header('Location: gestion_projets.php');
    exit();
}


?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier un projet</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h2 class="text-center">Modifier un projet</h2>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($message)) : ?>
                            <div class="alert alert-success"><?php echo $message; ?></div>
                        <?php endif; ?>
                        <form action="" method="POST">
                            <div class="form-group">
                                <label for="nom_projet">Nom du projet</label>
                                <input type="text" class="form-control" id="nom_projet" name="nom_project" placeholder="Nom du projet" value="<?php echo htmlspecialchars($nom_projet); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="description_projet">Description du projet</label>
                                <textarea class="form-control" id="description_projet" name="description_project" rows="3" placeholder="Description du projet"><?php echo htmlspecialchars($description_projet); ?></textarea>
                            </div>
                            <div class="form-group">
                                <label for="bailleur">Bailleur</label>
                                <input type="text" class="form-control" id="bailleur" name="bailleur" placeholder="Bailleur" value="<?php echo htmlspecialchars($bailleur); ?>">
                            </div>
                            <div class="form-group">
                                <label for="budget">Budget</label>
                                <input type="text" class="form-control" id="budget" name="budget" placeholder="Budget" value="<?php echo htmlspecialchars($budget); ?>">
                            </div>
                            <div class="form-group">
                                <label for="effectif">Effectif</label>
                                <input type="text" class="form-control" id="effectif" name="effectif" placeholder="Effectif" value="<?php echo htmlspecialchars($effectif); ?>" readonly>
                            </div>
                            <div class="form-group">
                                <h3>Membres associés au projet</h3>
                                <?php
                                // Requête pour récupérer tous les membres de l'organisation
                                $query_tous_membres = "SELECT * FROM membres";
                                $result_tous_membres = mysqli_query($conn, $query_tous_membres);

                                // Boucle pour afficher les membres et leurs cases à cocher
                                while ($row = mysqli_fetch_assoc($result_tous_membres)) {
                                    $id_membre = $row['id_membre'];
                                    $nom_complet = $row['nom'] . ' ' . $row['prenom'];
                                    $checked = in_array($id_membre, $membres_associés_ids) ? 'checked' : '';
                                ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="membres[]" value="<?php echo $id_membre; ?>" <?php echo $checked; ?>>
                                        <label class="form-check-label"><?php echo $nom_complet; ?></label>
                                    </div>
                                <?php } ?>
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-primary">Enregistrer</button>
                                <a href="gestion_projets.php" class="btn btn-secondary">Annuler</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Bootstrap -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <!-- Font Awesome -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>
</body>

</html>