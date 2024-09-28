<?php
require "cache.php";
// Récupérer les informations de l'utilisateur connecté
$user_id = $_SESSION['id_user'];
$user_query = "SELECT role FROM users WHERE id_user = $user_id";
$user_result = mysqli_query($conn, $user_query);
$role = '';
if ($user_row = mysqli_fetch_assoc($user_result)) {
    $role = $user_row['role'];
}
// Pagination
$itemsPerPage = 10;
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$offset = ($page - 1) * $itemsPerPage;

// Récupération des paiements groupés par mois et jour avec le nombre de membres payés et total
$query = "SELECT p.mois_paiement, p.jour_paiement, 
                 GROUP_CONCAT(m.nom, ' ', m.prenom, ' ', m.postnom ORDER BY m.nom SEPARATOR ', ') AS membres_payes,
                 COUNT(p.id_membre) AS nb_membres_payes,
                 (SELECT COUNT(*) FROM membres) AS nb_total_membres
          FROM paiement p
          JOIN membres m ON p.id_membre = m.id_membre
          GROUP BY p.mois_paiement, p.jour_paiement
          ORDER BY p.mois_paiement DESC, p.jour_paiement DESC
          LIMIT $offset, $itemsPerPage";
$result = mysqli_query($conn, $query);

// Récupération du nombre total de lignes sans LIMIT pour la pagination
$totalRowsQuery = "SELECT FOUND_ROWS() as totalRows";
$totalRowsResult = mysqli_query($conn, $totalRowsQuery);
$totalRows = mysqli_fetch_assoc($totalRowsResult)['totalRows'];

// Calcul du nombre total de pages pour la pagination
$totalPages = ceil($totalRows / $itemsPerPage);

$pageTitle = "Gestion de paiement";
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
        .table-responsive {
            overflow-y: auto;
        }
    </style>
</head>

<body>
    <!-- Menu -->
    <?php require "menu.php" ?>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h2 class="text-center">Gestion des Paiements</h2>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <div class="d-flex justify-content-between mb-3">
                                <input type="text" id="searchInput" class="form-control w-50" placeholder="Recherche...">
                                <a href="add_paiement.php" class="btn btn-primary">Ajouter paiement</a>
                            </div>
                            <table id="tablePaiements" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Mois de Paiement</th>
                                        <th>Jour de Paiement</th>
                                        <!-- <th>Membres Payés</th> -->
                                        <th>Membres Payés / Total Membres</th>
                                        <th>Actions</th> <!-- Nouvelle colonne pour les actions -->
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        echo "<tr>";
                                        echo "<td>{$row['mois_paiement']}</td>";
                                        echo "<td>{$row['jour_paiement']}</td>";
                                        // echo "<td>{$row['membres_payes']}</td>";
                                        echo "<td>{$row['nb_membres_payes']} / {$row['nb_total_membres']}</td>";
                                        echo "<td>";
                                        echo "<a href='view_paiement.php?mois={$row['mois_paiement']}&jour={$row['jour_paiement']}' class='btn btn-info btn-sm mr-2'>Visualiser</a>";
                                        echo "<a href='print_paiement.php?mois={$row['mois_paiement']}' class='btn btn-success btn-sm mr-2'>Imprimer</a>";
                                        echo "<a href='edit_paiement.php?mois={$row['mois_paiement']}&jour={$row['jour_paiement']}' class='btn btn-warning btn-sm'>Éditer</a>";
                                        echo "</td>";
                                        echo "</tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                        <!-- Pagination -->
                        <nav aria-label="Page navigation example">
                            <ul class="pagination justify-content-center mt-4">
                                <?php if ($page > 1) : ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page - 1; ?>" aria-label="Previous">
                                            <span aria-hidden="true">&laquo;</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                <?php for ($i = 1; $i <= $totalPages; $i++) : ?>
                                    <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                    </li>
                                <?php endfor; ?>
                                <?php if ($page < $totalPages) : ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo $page + 1; ?>" aria-label="Next">
                                            <span aria-hidden="true">&raquo;</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
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
    <script>
        $(document).ready(function() {
            // Filtrage fluide des résultats de recherche
            $('#searchInput').on('keyup', function() {
                var searchText = $(this).val().toLowerCase();
                $('#tablePaiements tbody tr').filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(searchText) > -1);
                });
            });
        });
    </script>
</body>

</html>

<?php
// Fermer la connexion à la base de données
mysqli_close($conn);
?>