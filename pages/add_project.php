<?php
// Démarrer la session
require "cache.php";

$message = '';

// Vérification si la requête est de type POST (lorsque le formulaire est soumis)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupération et échappement des données du formulaire
    $nom_projet = mysqli_real_escape_string($conn, $_POST['nom_projet']);
    $description_projet = mysqli_real_escape_string($conn, $_POST['description_projet']);
    $bailleur = mysqli_real_escape_string($conn, $_POST['bailleur']);
    $budget = mysqli_real_escape_string($conn, $_POST['budget']);

    // Récupération des membres sélectionnés et calcul de l'effectif
    $effectif = 0;
    if (isset($_POST['membres'])) {
        $membres = $_POST['membres'];
        $effectif = count($membres);
    }

    // Récupération de l'ID de l'utilisateur en ligne depuis la session
    session_start(); // Démarrage de la session si ce n'est pas déjà fait
    if (isset($_SESSION['id_user'])) {
        $created_by = $_SESSION['id_user'];

        // Requête d'insertion dans la table projects
        $query_insert = "INSERT INTO projects (nom_project, description_project, bailleur, budget, effectif, create_by)
                         VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_insert = mysqli_prepare($conn, $query_insert);
        mysqli_stmt_bind_param($stmt_insert, "sssssi", $nom_projet, $description_projet, $bailleur, $budget, $effectif, $created_by);

        // Exécution de la requête d'insertion
        if (mysqli_stmt_execute($stmt_insert)) {
            $message = "Le projet a été ajouté avec succès.";
            // Enregistrement dans l'historique
            $action = "Ajout d'un nouveau projet";
            $details = "Nom du projet $nom_projet";
            enregistrerHistorique($conn, $user_id, $action, $details);
            // Redirection après traitement
            header('Location: gestion_projets.php');
            exit; // Assurez-vous de sortir du script après une redirection
        } else {
            $message = "Erreur : " . mysqli_error($conn);
        }

        // Fermeture de la requête préparée
        mysqli_stmt_close($stmt_insert);
    } else {
        $message = "Erreur : ID utilisateur non trouvé dans la session.";
    }

    // Fermeture de la connexion à la base de données
    mysqli_close($conn);
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajouter un projet</title>
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
                        <h2 class="text-center">Ajouter un projet</h2>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($message)) : ?>
                            <div class="alert alert-<?php echo isset($success) ? 'success' : 'danger'; ?>"><?php echo $message; ?></div>
                        <?php endif; ?>
                        <form id="add_project" action="" method="POST">
                            <div class="form-group">
                                <label for="nom_projet">Nom du projet</label>
                                <input type="text" class="form-control" id="nom_projet" name="nom_projet" required>
                            </div>
                            <div class="form-group">
                                <label for="description_projet">Description du projet</label>
                                <textarea class="form-control" id="description_projet" name="description_projet" rows="3"></textarea>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="bailleur">Bailleur</label>
                                    <input type="text" class="form-control" id="bailleur" name="bailleur">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="budget">Budget</label>
                                    <input type="number" class="form-control" id="budget" name="budget">
                                </div>
                            </div>
                            <!-- Interface pour sélectionner les membres du projet -->
                            <div class="form-group">
                                <label for="membres">Membres du projet</label>
                                <div>
                                    <?php
                                    // Récupération des membres disponibles
                                    $query_membres = "SELECT id_membre, nom, prenom FROM membres";
                                    $result_membres = mysqli_query($conn, $query_membres);

                                    if (mysqli_num_rows($result_membres) > 0) {
                                        while ($row_membre = mysqli_fetch_assoc($result_membres)) {
                                    ?>
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" id="membre_<?php echo $row_membre['id_membre']; ?>" name="membres[]" value="<?php echo $row_membre['id_membre']; ?>">
                                                <label class="form-check-label" for="membre_<?php echo $row_membre['id_membre']; ?>">
                                                    <?php echo htmlspecialchars($row_membre['nom'] . ' ' . $row_membre['prenom']); ?>
                                                </label>
                                            </div>
                                    <?php
                                        }
                                    } else {
                                        echo "Aucun membre disponible.";
                                    }
                                    ?>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Ajouter</button>
                            <a href="gestion_projets.php" class="btn btn-secondary">Annuler</a>
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