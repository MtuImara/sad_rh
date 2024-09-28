<?php
// Démarrer la session
require "cache.php";
// Récupérer les informations de l'utilisateur connecté
$user_id = $_SESSION['id_user'];
$user_query = "SELECT role FROM users WHERE id_user = $user_id";
$user_result = mysqli_query($conn, $user_query);
$role = '';
if ($user_row = mysqli_fetch_assoc($user_result)) {
    $role = $user_row['role'];
}
// Vérification des paramètres d'URL
if (isset($_GET['mois']) && isset($_GET['jour'])) {
    $moisPaiement = $_GET['mois'];
    $jourPaiement = $_GET['jour'];
    $mois_paiement = mysqli_real_escape_string($conn, $_GET['mois']);

    // Récupération des données actuelles du paiement pour le mois spécifié
    $query_paiements = "SELECT p.id_membre, p.salaire_paye, m.nom, m.prenom, m.postnom, m.net_B, m.id_project, m.id_department 
                        FROM paiement p
                        JOIN membres m ON p.id_membre = m.id_membre
                        WHERE p.mois_paiement = '$moisPaiement'";
    $result_paiements = mysqli_query($conn, $query_paiements);
    // Requête SQL pour récupérer les détails du paiement pour le mois et le jour spécifiés
    $query = "SELECT m.nom, m.prenom, m.postnom, p.salaire_paye, m.net_B, (m.net_B - p.salaire_paye) AS montant_restant
              FROM paiement p
              JOIN membres m ON p.id_membre = m.id_membre
              WHERE p.mois_paiement = '$moisPaiement' AND p.jour_paiement = '$jourPaiement'";
    $result = mysqli_query($conn, $query);

    // Requête SQL pour calculer les totaux
    $query_totals = "SELECT SUM(p.salaire_paye) AS total_salaire_paye, SUM(m.net_B) AS total_net_B, SUM(m.net_B - p.salaire_paye) AS total_montant_restant
                     FROM paiement p
                     JOIN membres m ON p.id_membre = m.id_membre
                     WHERE p.mois_paiement = '$moisPaiement' AND p.jour_paiement = '$jourPaiement'";
    $result_totals = mysqli_query($conn, $query_totals);
    $totals = mysqli_fetch_assoc($result_totals);
} else {
    // Redirection si les paramètres ne sont pas définis
    header("Location: gestion_paiement.php");
    exit();
}




$pageTitle = "Détails du Paiement de $mois_paiement";
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
    <!-- Custom CSS -->
    <style>
        body {
            background-color: #f8f9fa;
        }

        .card {
            margin-top: 50px;
            border: none;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .card-header {
            background-color: #007bff;
            color: white;
            text-align: center;
            font-size: 24px;
            padding: 20px;
            border-bottom: 2px solid #0056b3;
        }

        .table {
            margin-bottom: 0;
        }

        .table th,
        .table td {
            vertical-align: middle;
        }

        .card-footer {
            background-color: #f8f9fa;
            border-top: none;
            text-align: center;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }

        .table tfoot {
            font-weight: bold;
        }
    </style>
</head>

<body>
    <!-- Menu -->
    <?php require "menu.php" ?>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">
                        Détails du Paiement de <?php echo $mois_paiement; ?>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Nom</th>
                                        <th>Prénom</th>
                                        <th>Postnom</th>
                                        <th>Montant payé</th>
                                        <th>Salaire à payer</th>
                                        <th>Montant Restant</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        echo "<tr>";
                                        echo "<td>{$row['nom']}</td>";
                                        echo "<td>{$row['prenom']}</td>";
                                        echo "<td>{$row['postnom']}</td>";
                                        echo "<td>{$row['salaire_paye']}</td>";
                                        echo "<td>{$row['net_B']}</td>";
                                        echo "<td>{$row['montant_restant']}</td>";
                                        echo "</tr>";
                                    }
                                    ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="text-right">Total :</td>
                                        <td><?php echo number_format($totals['total_salaire_paye'], 2); ?></td>
                                        <td><?php echo number_format($totals['total_net_B'], 2); ?></td>
                                        <td><?php echo number_format($totals['total_montant_restant'], 2); ?></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer">
                        <a href="gestion_paiement.php" class="btn btn-primary">Retour</a>
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

<?php
// Fermer la connexion à la base de données
mysqli_close($conn);
?>