<?php
require "cache.php";

// Affichage du message s'il existe
if (isset($_SESSION['message'])) {
    echo '<script>alert("' . $_SESSION['message'] . '");</script>';
    unset($_SESSION['message']); // Suppression du message de la session
}

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

// Requête pour récupérer la liste paginée de tous les membres
$query = "SELECT m.*, d.nom_department 
          FROM membres m 
          LEFT JOIN department d ON m.id_department = d.id_department
          LIMIT $start, $limit";
$result = mysqli_query($conn, $query);

// Requête pour compter le nombre total de membres
$count_query = "SELECT COUNT(*) AS total FROM membres";
$count_result = mysqli_query($conn, $count_query);
$count_row = mysqli_fetch_assoc($count_result);
$total = $count_row['total'];
$pages = ceil($total / $limit);

function envoyerNotification($id_membre, $jours_restants)
{
    global $conn;

    // Récupérer les informations de l'administrateur
    $admin_email_query = "SELECT email FROM users WHERE role = 'admin'";
    $admin_result = mysqli_query($conn, $admin_email_query);
    $admin = mysqli_fetch_assoc($admin_result);

    // Récupérer les informations du membre
    $membre_query = "SELECT nom, prenom FROM membres WHERE id_membre = " . (int)$id_membre;
    $membre_result = mysqli_query($conn, $membre_query);
    $membre = mysqli_fetch_assoc($membre_result);

    // Préparer et envoyer l'email
    $to = $admin['email'];
    $subject = "Alerte : Contrat de " . $membre['nom'] . " " . $membre['prenom'] . " se termine bientôt";
    $message = "Le contrat de " . $membre['nom'] . " " . $membre['prenom'] . " arrive à terme dans " . $jours_restants . " jours.";
    $headers = "From: noreply@tpodrc.com";

    mail($to, $subject, $message, $headers);
}

// Fermer la connexion à la base de données
// mysqli_close($conn);

$pageTitle = "Gestion des membres";
?>


<?php require "head.php" ?>

<body>
    <!-- Menu -->
    <?php require "menu.php" ?>
    <div class="container mt-5">
        <h2 class="text-center mb-4"><?php echo $pageTitle; ?></h2>

        <!-- Bouton Ajouter un membre -->
        <div class="d-flex justify-content-between mb-3">
            <input type="text" id="searchInput" class="form-control w-50" placeholder="Rechercher des membres...">
            <a href="add_membre.php" class="btn btn-primary">Ajouter un membre</a>
            <a href="anciens_membres.php" class="btn btn-dark">Voir anciens membres</a>
        </div>

        <table class="table table-striped" id="membersTable">
            <thead class="thead-dark">
                <tr>

                    <th scope="col">Nom</th>
                    <th scope="col">Matricule</th>
                    <th scope="col">Email</th>
                    <th scope="col">Département</th>
                    <th scope="col">Genre</th>
                    <th scope="col">Téléphone</th>
                    <th scope="col">Contrat</th>
                    <?php if ($role == 'admin') : ?>
                        <th scope="col">Créé le</th>
                        <th scope="col">Action</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <?php if (mysqli_num_rows($result) > 0) : ?>
                <?php while ($row = mysqli_fetch_assoc($result)) :
                    $date_fin = new DateTime($row['date_fin_contrat']);
                    $aujourdhui = new DateTime();
                    $interval = $aujourdhui->diff($date_fin);
                    $jours_restants = $interval->days;

                    // Vérifier si le contrat est proche de la fin
                    $row_class = '';
                    if ($jours_restants <= 28 && $jours_restants > 15) {
                        $row_class = 'clignotant-rose'; // Changer la couleur en rose clignotant
                    } elseif ($jours_restants <= 15 && $jours_restants > 0) {
                        // Envoyer une notification à l'admin
                        $row_class = 'clignotant-rouge'; // Changer la couleur en rouge clignotant
                        envoyerNotification($row['id_membre'], $jours_restants);
                    } ?>
                    <tr class="<?php echo $row_class; ?>">
                        <!-- <th scope="row"><?php echo $row['id_membre']; ?></th> -->
                        <td><?php echo $row['nom'] . ' ' . $row['prenom']; ?></td>
                        <td><?php echo $row['matricule']; ?></td>
                        <td><?php echo $row['email_professionel']; ?></td>
                        <td><?php echo $row['nom_department']; ?></td>
                        <td><?php echo $row['genre']; ?></td>
                        <td><?php echo $row['numero_telephone']; ?></td>
                        <td>
                            <?php
                            $type_contrat = $row['type_contrat'];
                            $date_debut = $row['date_debut_contrat'];
                            $date_fin = $row['date_fin_contrat'];

                            if ($type_contrat === 'CDI') {
                                // Afficher une barre de progression pleine pour les CDI
                                $progress = 100;
                                $jours_restants = 'CDI';
                            } elseif ($date_debut && $date_fin) {
                                // Calculer la barre de progression pour les CDD
                                $date_debut = new DateTime($date_debut);
                                $date_fin = new DateTime($date_fin);
                                $aujourdhui = new DateTime();
                                $interval = $date_debut->diff($date_fin);
                                $jours_total = $interval->days;
                                $interval_restante = $aujourdhui->diff($date_fin);
                                $jours_restants = $interval_restante->days;
                                $progress = ($jours_restants / $jours_total) * 100;
                            } else {
                                $progress = 0;
                                $jours_restants = 'N/A';
                            }
                            ?>
                            <div class="progress">
                                <div class="progress-bar" role="progressbar" style="width: <?php echo $progress; ?>%;" aria-valuenow="<?php echo $progress; ?>" aria-valuemin="0" aria-valuemax="100">
                                    <?php echo $jours_restants; ?> jours restants
                                </div>
                            </div>
                        </td>

                        <?php if ($role == 'admin') : ?>
                            <td><?php echo $row['created_at']; ?></td>
                            <td>
                                <a href="edit_membre.php?id=<?php echo $row['id_membre']; ?>" class="btn btn-sm btn-primary" title="Modifier"><span class="fa fa-edit"></span></a>
                                <a href="delete_membre.php?id=<?php echo $row['id_membre']; ?>" class="btn btn-sm btn-danger delete-department" title="Supprimer" data-department-name="<?php echo $row['nom']; ?> <?php echo $row['prenom']; ?>"><span class="fa fa-trash"></span></a>
                                <a href="archiver.php?id=<?php echo $row['id_membre']; ?>" class="btn btn-sm btn-dark" title="Archiver"><span class="fa fa-archive"></span></a>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endwhile; ?>
            <?php else : ?>
                <tr>
                    <td colspan="<?php echo $role == 'admin' ? '11' : '9'; ?>" class="text-center">Aucun membre trouvé.</td>
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
        });
    </script>
    <!-- JavaScript pour la confirmation de suppression -->
    <script>
        $(document).ready(function() {
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