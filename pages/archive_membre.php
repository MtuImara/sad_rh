<?php
require "cache.php"; // Assurez-vous que ce fichier initialise la connexion à la base de données et les sessions

// Récupérer les informations de l'utilisateur connecté
$user_id = $_SESSION['id_user'];
$user_query = "SELECT role FROM users WHERE id_user = $user_id";
$user_result = mysqli_query($conn, $user_query);
$role = '';
if ($user_row = mysqli_fetch_assoc($user_result)) {
    $role = $user_row['role'];
}

// Requête pour récupérer la liste des membres dont le contrat est expiré
$query = "SELECT m.*, d.nom_department 
          FROM membres m 
          LEFT JOIN department d ON m.id_department = d.id_department
          WHERE m.date_fin_contrat < CURDATE()";
$result = mysqli_query($conn, $query);

// Fermer la connexion à la base de données
mysqli_close($conn);

$pageTitle = "Archive des Membres";
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
</head>

<body>
    <!-- Menu -->
    <?php require "menu.php" ?>
    <div class="container mt-5">
        <h2 class="text-center mb-4">Archive des Membres</h2>
        <!-- Barre de recherche -->
        <div class="mb-4">
            <input type="text" id="searchInput" class="form-control" placeholder="Rechercher un membre...">
        </div>
        <table class="table table-striped" id="membersTable">
            <thead class="thead-dark">
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Nom</th>
                    <th scope="col">Email</th>
                    <th scope="col">Département</th>
                    <th scope="col">Genre</th>
                    <th scope="col">Téléphone</th>
                    <th scope="col">Âge</th>
                    <th scope="col">Contrat</th>
                    <?php if ($role == 'admin') : ?>
                        <th scope="col">Créé le</th>
                    <?php endif; ?>
                    <th scope="col">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0) : ?>
                    <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                        <tr>
                            <th scope="row"><?php echo $row['id_membre']; ?></th>
                            <td><?php echo $row['nom'] . ' ' . $row['prenom']; ?></td>
                            <td><?php echo $row['email_professionel']; ?></td>
                            <td><?php echo $row['nom_department']; ?></td>
                            <td><?php echo $row['genre']; ?></td>
                            <td><?php echo $row['numero_telephone']; ?></td>
                            <td>
                                <?php
                                $date_naissance = $row['date_naissance'];

                                if ($date_naissance) {
                                    $date_naissance = new DateTime($date_naissance);
                                    $aujourdhui = new DateTime();
                                    $age = $date_naissance->diff($aujourdhui)->y; // Calcul de l'âge en années
                                    echo $age;
                                } else {
                                    echo 'N/A';
                                }
                                ?>
                            </td>
                            <td><?php echo $row['type_contrat']; ?></td>
                            <?php if ($role == 'admin') : ?>
                                <td><?php echo $row['created_at']; ?></td>
                            <?php endif; ?>
                            <td>
                                <a href="edit_membre.php?id=<?php echo $row['id_membre']; ?>" class="btn btn-sm btn-primary" title="Modifier"><span class="fa fa-edit"></span></a>
                                <?php if ($role == 'admin') : ?>
                                    <a href="delete_membre.php?id=<?php echo $row['id_membre']; ?>" class="btn btn-sm btn-danger delete-department" title="Supprimer" data-department-name="<?php echo $row['nom'] . ' ' . $row['prenom']; ?>"><span class="fa fa-trash"></span></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="<?php echo $role == 'admin' ? '10' : '9'; ?>" class="text-center">Aucun membre expiré trouvé.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- JavaScript Bootstrap -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery (nécessaire pour Bootstrap JavaScript plugins) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#searchInput').on('keyup', function() {
                var value = $(this).val().toLowerCase();
                $('#membersTable tbody tr').filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
                });
            });

            $('.delete-department').on('click', function(e) {
                e.preventDefault();
                var departmentName = $(this).data('department-name');
                if (confirm("Êtes-vous sûr de vouloir supprimer le staff '" + departmentName + "' ?")) {
                    window.location.href = $(this).attr('href');
                }
            });
        });
    </script>
</body>

</html>