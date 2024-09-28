<?php
// Démarrer la session
require "cache.php";

// Vérifier le rôle de l'utilisateur pour déterminer s'il a les droits nécessaires
$user_id = $_SESSION['id_user'];
$user_query = "SELECT role, email, username FROM users WHERE id_user = $user_id";
$user_result = mysqli_query($conn, $user_query);
$role = '';
$email = '';

if ($user_row = mysqli_fetch_assoc($user_result)) {
    $role = $user_row['role'];
    $email = $user_row['email'];
}

// Récupérer toutes les demandes de congé avec les détails du superviseur et du chef de département désignés
$demande_query = "
    SELECT d.id_demande, 
           m.nom, 
           m.prenom, 
           d.date_debut, 
           d.date_fin, 
           d.type_conge, 
           d.date_demande, 
           d.statut,
           d.superviseur_status,
           d.chef_departement_status,
           (SELECT COUNT(*)
            FROM (
                SELECT ADDDATE(d.date_debut, INTERVAL t4.i*10 + t3.i DAY) as calc_date
                FROM 
                    (SELECT 0 i UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) t3,
                    (SELECT 0 i UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) t4
                WHERE ADDDATE(d.date_debut, INTERVAL t4.i*10 + t3.i DAY) BETWEEN d.date_debut AND d.date_fin
            ) calc_dates
            WHERE DAYOFWEEK(calc_dates.calc_date) NOT IN (1, 7) -- 1 = Dimanche, 7 = Samedi
           ) AS jours_pris,
           u.username AS superviseur_nom,
           c.username AS chef_departement_nom
    FROM demandes_conge d
    JOIN membres m ON d.id_membre = m.id_membre
    LEFT JOIN users u ON d.superviseur = u.id_user
    LEFT JOIN users c ON d.chef_departement = c.id_user
";

// Ajouter une condition supplémentaire pour restreindre les résultats si l'utilisateur n'est pas admin, rh, ou chef de département
if ($role != 'admin' && $role != 'rh' && $role != 'chef_departement') {
    $demande_query .= " WHERE m.email_professionel = '$email'";
}

$demande_query .= " ORDER BY d.date_demande DESC";

$demande_result = mysqli_query($conn, $demande_query);
// Script à exécuter

// Page Title
$pageTitle = "Gestion des Congés";
?>


<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap JavaScript et jQuery -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <style>
        .container {
            margin-top: 20px;
        }

        .status-approved {
            color: green;
        }

        .status-rejected {
            color: red;
        }
    </style>
</head>

<body>
    <!-- Menu -->
    <?php require "menu.php"; ?>
    <div class="container">
        <h2>Gestion des Congés</h2>
        <!-- Bouton Ajouter un membre -->
        <div class="d-flex justify-content-between mb-3">
            <a href="demande_conge.php" class="btn btn-primary">Demander congé ?</a>
        </div>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Date de Demande</th>
                    <th>Nom</th>
                    <th>Date de Début</th>
                    <th>Date de Fin</th>
                    <th>Type</th>
                    <th>Superviseur</th>
                    <th>Jours Pris</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($demande = mysqli_fetch_assoc($demande_result)) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($demande['date_demande']); ?></td>
                        <td><?php echo htmlspecialchars($demande['nom'] . ' ' . $demande['prenom']); ?></td>
                        <td><?php echo htmlspecialchars($demande['date_debut']); ?></td>
                        <td><?php echo htmlspecialchars($demande['date_fin']); ?></td>
                        <td><?php echo htmlspecialchars($demande['type_conge']); ?></td>
                        <td><?php echo htmlspecialchars($demande['superviseur_nom']); ?></td>
                        <td><?php echo htmlspecialchars($demande['jours_pris']); ?></td>
                        <td class="<?php echo ($demande['statut'] == 'approuvé') ? 'status-approved' : 'status-rejected'; ?>">
                            <?php echo htmlspecialchars($demande['statut']); ?>
                        </td>
                        <td>
                            <a href="modifier_conge.php?id_demande=<?php echo $demande['id_demande']; ?>" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php if ($role == 'admin' || $role == 'rh') : ?>
                                <a href="supprimer_conge.php?id_demande=<?php echo $demande['id_demande']; ?>" class="btn btn-danger btn-sm">
                                    <i class="fas fa-trash"></i>
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>

        </table>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://stackpath.amazonaws.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</body>

</html>