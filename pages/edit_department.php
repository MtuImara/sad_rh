<?php
// Démarrer la session
require "cache.php";
// Vérifiez si l'utilisateur est connecté, sinon redirigez-le vers la page de connexion
if (!isset($_SESSION['id_user'])) {
    header('Location: login.php');
    exit();
}

// Inclusion du fichier de connexion à la base de données
include_once 'config/database.php';

// Vérification du rôle de l'utilisateur
$user_id = $_SESSION['id_user'];
$user_query = "SELECT role FROM users WHERE id_user = $user_id";
$user_result = mysqli_query($conn, $user_query);
$role = '';
if ($user_row = mysqli_fetch_assoc($user_result)) {
    $role = $user_row['role'];
}

// Récupération de l'ID du département à modifier depuis l'URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    // Redirection si l'ID n'est pas valide
    header('Location: gestion_departement.php');
    exit();
}
$id_department = $_GET['id'];

// Récupération des données du département depuis la base de données
$query = "SELECT * FROM department WHERE id_department = $id_department";
$result = mysqli_query($conn, $query);
$department = mysqli_fetch_assoc($result);

// Vérification si le département existe
if (!$department) {
    // Redirection si le département n'existe pas
    header('Location: gestion_departement.php');
    exit();
}

// Traitement du formulaire de modification lors de la soumission POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Nettoyage et validation des données reçues du formulaire
    $nom_department = mysqli_real_escape_string($conn, $_POST['nom_department']);
    $description_department = mysqli_real_escape_string($conn, $_POST['description_department']);

    // Requête SQL pour mettre à jour le département
    $update_query = "UPDATE department SET nom_department = '$nom_department', description_department = '$description_department' WHERE id_department = $id_department";

    // Exécution de la requête
    if (mysqli_query($conn, $update_query)) {
        $message = "Le département a été modifié avec succès.";
        // Enregistrement dans l'historique
        $action = "Modification d'un département";
        $details = "Détails de la modification de $nom_department";
        enregistrerHistorique($conn, $user_id, $action, $details);
        mysqli_close($conn);
        header('Location: gestion_departement.php');
        exit();
    } else {
        $message = "Erreur : " . mysqli_error($conn);
    }
}

// Fermeture de la connexion à la base de données
mysqli_close($conn);

$pageTitle = "Modifier Département";
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body>
    <!-- Menu -->
    <?php require "menu.php"; ?>
    <div class="container mt-5">
        <h2 class="text-center mb-4">Modifier Département</h2>

        <!-- Affichage des messages -->
        <?php if (!empty($message)) : ?>
            <div class="alert alert-info" role="alert">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- Formulaire de modification -->
        <form action="" method="POST">
            <div class="form-group">
                <label for="nom_department">Nom du département</label>
                <input type="text" class="form-control" id="nom_department" name="nom_department" value="<?php echo $department['nom_department']; ?>" required>
            </div>
            <div class="form-group">
                <label for="description_department">Description</label>
                <textarea class="form-control" id="description_department" name="description_department" rows="3"><?php echo $department['description_department']; ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
            <a href="gestion_department.php" class="btn btn-secondary">Annuler</a>
        </form>
    </div>

    <!-- JavaScript Bootstrap -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</body>

</html>