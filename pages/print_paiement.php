<?php
// Inclusion du fichier de connexion à la base de données
include_once 'config/database.php';
$role = '';
// Vérification des paramètres d'URL
if (isset($_GET['mois'])) {
    $moisPaiement = $_GET['mois'];

    // Récupération des détails du paiement pour le mois spécifié
    $query = "SELECT m.nom, m.prenom, m.postnom, m.num_compte_banque, p.salaire_paye
              FROM paiement p
              JOIN membres m ON p.id_membre = m.id_membre
              WHERE p.mois_paiement = '$moisPaiement'";
    $result = mysqli_query($conn, $query);

    // Récupérer le nom du mois pour l'affichage
    $moisPaiementNom = date("F", mktime(0, 0, 0, (int)$moisPaiement, 1));
} else {
    // Redirection si les paramètres ne sont pas définis
    header("Location: gestion_paiement.php");
    exit();
}

$pageTitle = "Impression des Paiements";
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
    <!-- Custom CSS for printing -->
    <style>
        @media print {
            body {
                visibility: hidden;
            }

            .print-section,
            .print-section * {
                visibility: visible;
            }

            .print-section {
                position: absolute;
                left: 0;
                top: 0;
            }
        }
    </style>
</head>

<body>
    <!-- Menu -->
    <?php require "menu.php" ?>
    <div class="container mt-5 print-section">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h2 class="text-center">Impression des Paiements</h2>
                    </div>
                    <div class="card-body">
                        <h3 class="text-center">Détails du Paiement pour <?php echo $moisPaiementNom; ?></h3>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Nom</th>
                                        <th>Prénom</th>
                                        <th>Postnom</th>
                                        <th>Montant payé</th>
                                        <th>N° Compte</th>
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
                                        echo "<td>{$row['num_compte_banque']}</td>";
                                        echo "</tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer text-center">
                        <button class="btn btn-primary" onclick="window.print()">Imprimer</button>
                        <a href="export_paiement_excel.php?mois=<?php echo $moisPaiement; ?>" class="btn btn-success">Exporter en Excel</a>
                        <a href="gestion_paiement.php" class="btn btn-secondary">Retour</a>
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