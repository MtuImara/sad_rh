<?php
// Démarrer la session
require "cache.php";

// Récupérer les informations de l'utilisateur connecté
$user_id = $_SESSION['id_user'];
$user_query = "SELECT role, email FROM users WHERE id_user = $user_id";
$user_result = mysqli_query($conn, $user_query);
$role = '';
$user_email = '';
if ($user_row = mysqli_fetch_assoc($user_result)) {
    $role = $user_row['role'];
    $user_email = $user_row['email'];
}

// Obtenir les informations de la dernière demande de congé de l'utilisateur
$demande_query = "SELECT * FROM demandes_conge WHERE id_membre = (SELECT id_membre FROM membres WHERE email_professionel = '$user_email') ORDER BY date_demande DESC LIMIT 1";
$demande_result = mysqli_query($conn, $demande_query);
$demande_info = mysqli_fetch_assoc($demande_result);

// Vérifier si des informations de demande ont été récupérées
if (!$demande_info) {
    die("Erreur: Impossible de récupérer les informations de la demande de congé.");
}

// Page Title
$pageTitle = "Confirmation de Demande de Congé";
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="refresh" content="5;url=gestion_conge.php">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap JavaScript et jQuery -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <style>
        .confirmation-container {
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <!-- Menu -->
    <?php require "menu.php"; ?>
    <div class="container confirmation-container">
        <h2>Confirmation de Demande de Congé</h2>
        <div class="alert alert-success" role="alert">
            <h4 class="alert-heading">Votre demande de congé a été soumise avec succès!</h4>
            <p>Voici les détails de votre demande :</p>
            <ul>
                <li><strong>Date de début du congé :</strong> <?php echo htmlspecialchars($demande_info['date_debut']); ?></li>
                <li><strong>Date de fin du congé :</strong> <?php echo htmlspecialchars($demande_info['date_fin']); ?></li>
                <li><strong>Raison :</strong> <?php echo htmlspecialchars($demande_info['raison']); ?></li>
                <li><strong>Superviseur :</strong> <?php echo htmlspecialchars($demande_info['superviseur']); ?></li>
                <li><strong>Date de demande :</strong> <?php echo htmlspecialchars($demande_info['date_demande']); ?></li>
            </ul>
            <hr>
            <p class="mb-0">Vous recevrez une notification une fois que votre demande aura été traitée.</p>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://stackpath.amazonaws.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</body>

</html>