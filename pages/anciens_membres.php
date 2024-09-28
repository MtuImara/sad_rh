<?php
// Inclure le fichier de connexion à la base de données
require_once 'cache.php';
$role = '';
// Récupérer les anciens membres depuis la base de données
$sql = "SELECT * FROM anciens_membre";
$result = mysqli_query($conn, $sql);

// Vérifier les erreurs de requête
if (!$result) {
    die("Erreur lors de la récupération des anciens membres : " . mysqli_error($conn));
}
$pageTitle = "Liste des membres sans contrat";
?>

<!DOCTYPE html>
<html lang="fr">

<?php require "head.php" ?>

<body>
    <!-- Menu -->
    <?php require "menu.php" ?>

    <div class="container mt-5">
        <h2 class="text-center mb-4">Anciens Membres</h2>

        <table class="table table-striped">
            <thead class="thead-dark">
                <tr>
                    <th scope="col">Nom</th>
                    <th scope="col">Matricule</th>
                    <th scope="col">Email</th>
                    <th scope="col">Genre</th>
                    <th scope="col">Téléphone</th>
                    <th scope="col">Date de Début Contrat</th>
                    <th scope="col">Date de Fin Contrat</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result) > 0) : ?>
                    <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                        <tr>
                            <td><?php echo $row['nom'] . ' ' . $row['prenom']; ?></td>
                            <td><?php echo $row['matricule']; ?></td>
                            <td><?php echo $row['email_professionel']; ?></td>
                            <td><?php echo $row['genre']; ?></td>
                            <td><?php echo $row['numero_telephone']; ?></td>
                            <td><?php echo $row['date_debut_contrat']; ?></td>
                            <td><?php echo $row['date_fin_contrat']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="8" class="text-center">Aucun ancien membre trouvé.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Inclure les scripts JavaScript de Bootstrap -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
</body>

</html>

<?php
// Fermer la connexion à la base de données
mysqli_close($conn);
?>