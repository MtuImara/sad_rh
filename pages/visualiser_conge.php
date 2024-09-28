<?php
// Démarrer la session
require "cache.php";

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['id_user'])) {
    header('Location: login.php');
    exit();
}

// Vérifier le rôle de l'utilisateur
$user_id = $_SESSION['id_user'];
$user_query = "SELECT role FROM users WHERE id_user = $user_id";
$user_result = mysqli_query($conn, $user_query);
$role = '';
if ($user_row = mysqli_fetch_assoc($user_result)) {
    $role = $user_row['role'];
}

// Seuls les RH et les superviseurs peuvent accéder à cette page
if ($role != 'rh' && $role != 'superviseur') {
    echo "Vous n'avez pas les droits pour accéder à cette page.";
    exit();
}

// Récupérer l'ID de la demande de congé
$id_demande = isset($_GET['id_demande']) ? intval($_GET['id_demande']) : 0;

// Requête pour récupérer les détails de la demande de congé
$demande_query = "
    SELECT d.id_demande, m.nom, m.prenom, d.date_debut, d.date_fin, d.type_conge, d.date_demande, d.superviseur, d.statut, 
           DATEDIFF(d.date_fin, d.date_debut) AS jours_pris
    FROM demandes_conge d
    JOIN membres m ON d.id_membre = m.id_membre
    WHERE d.id_demande = $id_demande
";
$demande_result = mysqli_query($conn, $demande_query);
$demande = mysqli_fetch_assoc($demande_result);

// Si la demande n'est pas trouvée
if (!$demande) {
    echo "Demande de congé introuvable.";
    exit();
}

// Page Title
$pageTitle = "Détails de la Demande de Congé";
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <style>
        .container {
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <!-- Menu -->
    <?php require "menu.php"; ?>
    <div class="container">
        <h2>Détails de la Demande de Congé</h2>
        <div class="card">
            <div class="card-header">
                Demande #<?php echo $demande['id_demande']; ?>
            </div>
            <div class="card-body">
                <h5 class="card-title"><?php echo htmlspecialchars($demande['nom'] . ' ' . $demande['prenom']); ?></h5>
                <p class="card-text"><strong>Date de Début :</strong> <?php echo htmlspecialchars($demande['date_debut']); ?></p>
                <p class="card-text"><strong>Date de Fin :</strong> <?php echo htmlspecialchars($demande['date_fin']); ?></p>
                <p class="card-text"><strong>Type de Congé :</strong> <?php echo htmlspecialchars($demande['type_conge']); ?></p>
                <p class="card-text"><strong>Date de Demande :</strong> <?php echo htmlspecialchars($demande['date_demande']); ?></p>
                <p class="card-text"><strong>Superviseur :</strong> <?php echo htmlspecialchars($demande['superviseur']); ?></p>
                <p class="card-text"><strong>Nombre de Jours Pris :</strong> <?php echo htmlspecialchars($demande['jours_pris']); ?></p>
                <p class="card-text"><strong>Statut :</strong>
                    <span class="<?php echo ($demande['statut'] == 'approuvé') ? 'text-success' : 'text-danger'; ?>">
                        <?php echo htmlspecialchars($demande['statut']); ?>
                    </span>
                </p>
            </div>
            <div class="card-footer text-right">
                <a href="gestion_conge.php" class="btn btn-primary">Retour à la Gestion des Congés</a>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>