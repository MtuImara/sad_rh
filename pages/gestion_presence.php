<?php
// Démarrer la session et inclure les fichiers nécessaires
require "cache.php";

// Récupérer le rôle de l'utilisateur connecté
$user_id = $_SESSION['id_user'];
$user_query = "SELECT role FROM users WHERE id_user = ?";
$user_stmt = mysqli_prepare($conn, $user_query);
mysqli_stmt_bind_param($user_stmt, 'i', $user_id);
mysqli_stmt_execute($user_stmt);
$user_result = mysqli_stmt_get_result($user_stmt);
$role = '';

if ($user_row = mysqli_fetch_assoc($user_result)) {
    $role = $user_row['role'];
}

// Vérifier le rôle pour la gestion des présences
$is_admin = ($role == 'admin' || $role == 'rh');
$is_user = ($role == 'user');

// Récupérer les présences
$presence_query = "SELECT p.*, m.nom, m.prenom FROM presences p JOIN membres m ON p.id_membre = m.id_membre";
if (!$is_admin) {
    $presence_query .= " WHERE p.id_membre = ?";
}
$presence_query .= " ORDER BY p.date DESC";
$presence_stmt = mysqli_prepare($conn, $presence_query);
if (!$is_admin) {
    mysqli_stmt_bind_param($presence_stmt, 'i', $user_id);
}
mysqli_stmt_execute($presence_stmt);
$presence_result = mysqli_stmt_get_result($presence_stmt);

// Traitement de la présence (ajout ou modification)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_membre = $_POST['id_membre'];
    $date_presence = $_POST['date_presence'];
    $heure_entree = $_POST['heure_entree'];
    $heure_sortie = $_POST['heure_sortie'];

    // Requête d'insertion ou de mise à jour
    if (isset($_POST['id_presence'])) {
        // Mise à jour
        $update_query = "UPDATE presences 
                         SET date = ?, heure_entree = ?, heure_sortie = ? 
                         WHERE id = ?";
        $stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($stmt, 'sssi', $date_presence, $heure_entree, $heure_sortie, $_POST['id_presence']);
    } else {
        // Insertion
        $insert_query = "INSERT INTO presences (id_membre, date, heure_entree, heure_sortie) 
                         VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $insert_query);
        mysqli_stmt_bind_param($stmt, 'isss', $id_membre, $date_presence, $heure_entree, $heure_sortie);
    }

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['message'] = 'Présence enregistrée avec succès.';
    } else {
        $_SESSION['message'] = 'Erreur lors de l\'enregistrement de la présence.';
    }
    mysqli_stmt_close($stmt);
    header('Location: gestion_presence.php');
    exit();
}

$pageTitle = "Gestion des Présences";
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</head>

<body>
    <?php require "menu.php"; ?>

    <div class="container">
        <h2><?php echo $pageTitle; ?></h2>
        <!-- Bouton Ajouter un membre -->
        <div class="d-flex justify-content-between mb-3">
            <a href="add_presence.php" class="btn btn-primary">Ajouter présence</a>
        </div>

        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Membre</th>
                    <th>Heure d'entrée</th>
                    <th>Heure de sortie</th>
                    <th>Nombre d'heures de prestation</th> <!-- Nouvelle colonne -->
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($presence = mysqli_fetch_assoc($presence_result)) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($presence['date']); ?></td>
                        <td><?php echo htmlspecialchars($presence['nom'] . ' ' . $presence['prenom']); ?></td>
                        <td><?php echo htmlspecialchars($presence['heure_entree']); ?></td>
                        <td><?php echo htmlspecialchars($presence['heure_sortie']); ?></td>
                        <td>
                            <?php
                            // Calcul du nombre d'heures de prestation
                            $heure_entree = new DateTime($presence['heure_entree']);
                            $heure_sortie = new DateTime($presence['heure_sortie']);
                            $interval = $heure_entree->diff($heure_sortie);
                            echo $interval->format('%h heures %i minutes');
                            ?>
                        </td>
                        <td>
                            <a href="modifier_presence.php?id_presence=<?php echo $presence['id']; ?>" class="btn btn-warning btn-sm">
                                Modifier
                            </a>
                            <a href="supprimer_presence.php?id_presence=<?php echo $presence['id']; ?>" class="btn btn-danger btn-sm">
                                Supprimer
                            </a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>

</html>