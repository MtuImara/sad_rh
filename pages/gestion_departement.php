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

// Pagination
$limit = 6; // Nombre d'éléments par page
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? $_GET['page'] : 1;
$start = ($page - 1) * $limit;

// Requête pour récupérer la liste paginée des départements
$query = "SELECT * FROM department LIMIT $start, $limit";
$result = mysqli_query($conn, $query);

// Requête pour compter le nombre total de départements
$count_query = "SELECT COUNT(*) AS total FROM department";
$count_result = mysqli_query($conn, $count_query);
$count_row = mysqli_fetch_assoc($count_result);
$total = $count_row['total'];
$pages = ceil($total / $limit);

// Fermer la connexion à la base de données
mysqli_close($conn);

$pageTitle = "Gestion des départements";
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap JavaScript et jQuery -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body>
    <!-- Menu -->
    <?php require "menu.php"; ?>
    <div class="container mt-5">
        <h2 class="text-center mb-4">Liste des Départements</h2>

        <!-- Bouton Ajouter un département -->
        <div class="d-flex justify-content-between mb-3">
            <input type="text" id="searchInput" class="form-control w-50" placeholder="Rechercher des départements...">
            <?php if ($role == 'admin') : ?>
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addDepartmentModal">
                    Ajouter un département
                </button>
            <?php endif; ?>
        </div>

        <table class="table table-striped" id="departmentsTable">
            <thead class="thead-dark">
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Nom du Département</th>
                    <th scope="col">Description</th>
                    <th scope="col">Effectif</th>
                    <?php if ($role == 'admin') : ?>
                        <th scope="col">Action</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0) : ?>
                    <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                        <tr>
                            <th scope="row"><?php echo $row['id_department']; ?></th>
                            <td><?php echo $row['nom_department']; ?></td>
                            <td><?php echo $row['description_department']; ?></td>
                            <td><?php echo $row['effectif']; ?></td>
                            <?php if ($role == 'admin') : ?>
                                <td>
                                    <a href="edit_department.php?id=<?php echo $row['id_department']; ?>" class="btn btn-sm btn-primary">Modifier</a>
                                    <a href="delete_department.php?id=<?php echo $row['id_department']; ?>" class="btn btn-sm btn-danger delete-department" data-department-name="<?php echo $row['nom_department']; ?>">Supprimer</a>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endwhile; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="<?php echo $role == 'admin' ? '4' : '3'; ?>" class="text-center">Aucun département trouvé.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <nav aria-label="Page navigation example">
            <ul class="pagination justify-content-center">
                <?php for ($i = 1; $i <= $pages; $i++) : ?>
                    <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>
            </ul>
        </nav>
    </div>

    <!-- Fenêtre modale pour ajouter un département -->
    <div class="modal fade" id="addDepartmentModal" tabindex="-1" role="dialog" aria-labelledby="addDepartmentModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addDepartmentModalLabel">Ajouter un département</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Formulaire d'ajout de département -->
                    <form id="addDepartmentForm" action="add_department.php" method="POST">
                        <div class="form-group">
                            <label for="nom_department">Nom du département</label>
                            <input type="text" class="form-control" id="nom_department" name="nom_department" required>
                            <div id="departmentError" class="text-danger mt-1"></div>
                        </div>
                        <div class="form-group">
                            <label for="description_department">Description</label>
                            <textarea class="form-control" id="description_department" name="description_department" rows="3"></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">Ajouter</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <!-- JavaScript pour la confirmation de suppression -->
    <script>
        $(document).ready(function() {
            // Afficher une alerte pop-up si un message d'erreur est présent dans l'URL
            const urlParams = new URLSearchParams(window.location.search);
            const message = urlParams.get('message');

            if (message) {
                alert(decodeURIComponent(message));
            }

            // Confirmer la suppression d'un département
            $('.delete-department').on('click', function(e) {
                e.preventDefault();
                var departmentName = $(this).data('department-name');
                if (confirm("Êtes-vous sûr de vouloir supprimer le département '" + departmentName + "' ?")) {
                    window.location.href = $(this).attr('href');
                }
            });

            // Fonction de recherche
            $('#searchInput').on('keyup', function() {
                var searchText = $(this).val().toLowerCase();
                $('#departmentsTable tbody tr').each(function() {
                    var found = false;
                    $(this).find('td').each(function() {
                        var cellText = $(this).text().toLowerCase();
                        if (cellText.indexOf(searchText) !== -1) {
                            found = true;
                            return false; // Sortir de la boucle each()
                        }
                    });
                    $(this).toggle(found); // Afficher ou cacher la ligne en fonction de la recherche
                });
            });
        });
    </script>

    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>


</body>

</html>