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

// Requête pour récupérer la liste paginée des projets avec le nom de l'utilisateur qui les a ajoutés
$query = "SELECT projects.id_project, projects.nom_project, projects.description_project, projects.bailleur, projects.budget, projects.effectif, projects.create_by, users.username AS added_by
          FROM projects
          LEFT JOIN users ON projects.create_by = users.id_user
          LIMIT $start, $limit";
$result = mysqli_query($conn, $query);

// Requête pour compter le nombre total de projets
$count_query = "SELECT COUNT(*) AS total FROM projects";
$count_result = mysqli_query($conn, $count_query);
$count_row = mysqli_fetch_assoc($count_result);
$total = $count_row['total'];
$pages = ceil($total / $limit);

// Fermer la connexion à la base de données
mysqli_close($conn);

$pageTitle = "Gestion des projets";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <!-- Liens vers les fichiers CSS de Bootstrap -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Liens vers les fichiers JavaScript de Bootstrap (optionnel, pour les composants nécessitant JavaScript) -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <!-- Liens vers jQuery (requis pour Bootstrap JavaScript) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <!-- Votre propre fichier CSS personnalisé -->
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <!-- Menu -->
    <?php require "menu.php"; ?>
    <div class="container">
        <h1 class="mt-4 mb-4">Gestion des Projets</h1>

        <!-- Bouton pour ajouter un nouveau projet -->
        <div class="mb-4">
            <a href="add_project.php" class="btn btn-primary">
                Ajouter un Projet
            </a>
        </div>

        <!-- Tableau pour afficher la liste des projets -->
        <table class="table table-striped" id="projectsTable">
            <thead class="thead-dark">
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Nom du Projet</th>
                    <th scope="col">Ajouté par</th>
                    <th scope="col">Bailleur</th>
                    <th scope="col">Budget</th>
                    <th scope="col">Effectif</th>
                    <?php if ($role == 'admin') : ?>
                        <th scope="col">Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <?php if (mysqli_num_rows($result) > 0) : ?>
                <tbody>
                    <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                        <tr>
                            <th scope="row"><?php echo $row['id_project']; ?></th>
                            <td><?php echo $row['nom_project']; ?></td>
                            <td><?php echo $row['added_by']; ?></td>
                            <td><?php echo $row['bailleur']; ?></td>
                            <td><?php echo $row['budget']; ?></td>
                            <td><?php echo $row['effectif']; ?></td>
                            <?php if ($role == 'admin') : ?>
                                <td>
                                    <a href="edit_project.php?id=<?php echo $row['id_project']; ?>" class="btn btn-sm btn-primary">Modifier</a>
                                    <a href="delete_project.php?id=<?php echo $row['id_project']; ?>" class="btn btn-sm btn-danger delete-project" data-project-name="<?php echo $row['nom_project']; ?>">Supprimer</a>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            <?php else : ?>
                <tbody>
                    <tr>
                        <td colspan="<?php echo $role == 'admin' ? '7' : '6'; ?>" class="text-center">Aucun projet trouvé.</td>
                    </tr>
                </tbody>
            <?php endif; ?>
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

    <!-- Script JavaScript pour la confirmation de suppression -->
    <script>
        $(document).ready(function() {
            $('.delete-project').on('click', function(e) {
                e.preventDefault();
                var projectName = $(this).data('project-name');
                if (confirm("Êtes-vous sûr de vouloir supprimer le projet '" + projectName + "' ?")) {
                    window.location.href = $(this).attr('href');
                }
            });

            $('#searchInput').on('keyup', function() {
                var searchText = $(this).val().toLowerCase();
                $('#projectsTable tbody tr').each(function() {
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