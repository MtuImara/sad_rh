<?php
// Démarrer la session et inclure le fichier de connexion
require "cache.php";

// Initialiser les variables
$message = '';
$id_user = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Vérifier si l'identifiant de l'utilisateur est valide
if ($id_user > 0) {
    // Supprimer l'utilisateur de la base de données
    $query = "DELETE FROM users WHERE id_user = $id_user";

    if (mysqli_query($conn, $query)) {
        $message = "Utilisateur supprimé avec succès.";
        // Enregistrement dans l'historique
        $action = "Suppression d'un utilisateur";
        $details = "Détails de la modification";
        enregistrerHistorique($conn, $user_id, $action, $details);
    } else {
        $message = "Erreur: " . mysqli_error($conn);
    }

    // Redirection après traitement
    header('Location: gestion_users.php');
    exit();
} else {
    $message = "Identifiant utilisateur invalide.";
}

// Fermer la connexion à la base de données
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supprimer l'utilisateur</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-danger text-white">
                        <h2 class="text-center">Suppression de l'utilisateur</h2>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($message)) : ?>
                            <div class="alert alert-<?php echo strpos($message, 'Erreur') !== false ? 'danger' : 'success'; ?>">
                                <?php echo $message; ?>
                            </div>
                        <?php endif; ?>
                        <div class="text-center">
                            <a href="gestion_users.php" class="btn btn-secondary">Retour à la gestion des utilisateurs</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Bootstrap -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script>
        // Fonction pour confirmer la suppression d'un utilisateur
        function confirmDelete(id_user) {
            if (confirm("Êtes-vous sûr de vouloir supprimer cet utilisateur ?")) {
                window.location.href = 'delete_user.php?id_user=' + id_user;
            }
        }
    </script>
</body>

</html>