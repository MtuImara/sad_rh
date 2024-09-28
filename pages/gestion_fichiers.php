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

// Récupération des fichiers
$sql = "SELECT f.id_fichier, f.nom_fichier, f.chemin_fichier, f.date_ajout, m.nom, m.prenom
        FROM fichiers f
        JOIN membres m ON f.id_membre = m.id_membre";
$result = $conn->query($sql);

$sql2 = "SELECT id_membre, nom, prenom FROM membres";
$result2 = $conn->query($sql2);
$pageTitle = "Gestion des Fichiers";
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
        .table-responsive {
            margin-top: 20px;
        }

        .file-icon {
            font-size: 24px;
            color: #007bff;
        }

        .table td,
        .table th {
            vertical-align: middle;
        }

        .table thead th {
            background-color: #343a40;
            color: white;
        }
    </style>
</head>

<body>
    <!-- Menu -->
    <?php require "menu.php"; ?>
    <div class="container">
        <h2 class="mt-4">Liste des Fichiers</h2>
        <!-- Bouton Ajouter un département -->
        <div class="d-flex justify-content-between mb-3">
            <input type="text" id="searchInput" class="form-control w-50" placeholder="Rechercher...">
            <?php if ($role == 'admin') : ?>
                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#addDepartmentModal">
                    Ajouter un fichier
                </button>
            <?php endif; ?>
        </div>
        <?php
        // Connexion à la base de données
        include_once 'config/database.php';

        // Récupération des fichiers
        $query = "SELECT f.*, m.nom, m.prenom FROM fichiers f JOIN membres m ON f.id_membre = m.id_membre";
        $result = mysqli_query($conn, $query);
        ?>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Nom du fichier</th>
                        <th scope="col">Type de fichier</th>
                        <th scope="col">Nom du membre</th>
                        <th scope="col">Télécharger</th>
                        <th scope="col">Date d'ajout</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result) > 0) : ?>
                        <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                            <tr>
                                <th scope="row"><?php echo $row['id_fichier']; ?></th>
                                <td><?php echo $row['nom_fichier']; ?></td>
                                <td><?php echo $row['type_fichier']; ?></td>
                                <td><?php echo $row['nom'] . ' ' . $row['prenom']; ?></td>
                                <td><a href="<?php echo $row['chemin_fichier']; ?>" class="btn btn-info btn-sm" download>Télécharger</a></td>
                                <td><?php echo $row['date_ajout']; ?></td>
                                <td>
                                    <a href="edit_fichier.php?id=<?php echo $row['id_fichier']; ?>" class="btn btn-sm btn-primary">Modifier</a>
                                    <a href="delete_fichier.php?id=<?php echo $row['id_fichier']; ?>" class="btn btn-sm btn-danger">Supprimer</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="7" class="text-center">Aucun fichier trouvé.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <!-- Fenêtre modale pour ajouter un département -->
    <div class="modal fade" id="addDepartmentModal" tabindex="-1" role="dialog" aria-labelledby="addDepartmentModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addDepartmentModalLabel">Ajouter fichier</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Formulaire d'ajout de département -->
                    <form id="addDepartmentForm" action="add_file" method="post" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="id_membre">Membre</label>
                            <select class="form-control" id="id_membre" name="id_membre" required>
                                <?php
                                // Récupération des membres
                                $query = "SELECT id_membre, nom, prenom FROM membres";
                                $result = mysqli_query($conn, $query);

                                if (mysqli_num_rows($result) > 0) {
                                    while ($row = mysqli_fetch_assoc($result)) {
                                        echo "<option value='" . $row['id_membre'] . "'>" . $row['nom'] . " " . $row['prenom'] . "</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="fichier">Fichier</label>
                            <input type="file" class="form-control-file" id="fichier" name="fichier" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Ajouter</button>

                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

</body>

</html>