<?php
// Démarrer la session
require "cache.php";

// Initialisation de la variable d'erreur
$erreur_message = '';

// Vérification de l'existence de l'ID du paiement mensuel à modifier dans l'URL
if (isset($_GET['mois']) && isset($_GET['jour'])) {
    $mois_paiement = mysqli_real_escape_string($conn, $_GET['mois']);
    $jour_paiement = mysqli_real_escape_string($conn, $_GET['jour']);

    // Récupération des données actuelles du paiement pour le mois spécifié
    $query_paiements = "SELECT p.id_membre, p.salaire_paye, m.nom, m.prenom, m.postnom, m.net_B, m.id_project, m.id_department 
                        FROM paiement p
                        JOIN membres m ON p.id_membre = m.id_membre
                        WHERE p.mois_paiement = '$mois_paiement'";
    $result_paiements = mysqli_query($conn, $query_paiements);

    // Si la soumission du formulaire est détectée
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $jour_paiement = mysqli_real_escape_string($conn, $_POST['jour_paiement']);
        $membres = isset($_POST['membres']) ? $_POST['membres'] : [];

        // Validation et mise à jour des données dans la table paiement
        foreach ($membres as $id_membre) {
            $salaire_paye = mysqli_real_escape_string($conn, $_POST['salaire_paye'][$id_membre]);

            // Récupérer le net_B du membre
            $query_membre = "SELECT nom, prenom, postnom, net_B FROM membres WHERE id_membre = '$id_membre'";
            $result_membre = mysqli_query($conn, $query_membre);
            $row_membre = mysqli_fetch_assoc($result_membre);
            $nom_membre = $row_membre['nom'];
            $prenom_membre = $row_membre['prenom'];
            $postnom_membre = $row_membre['postnom'];
            $net_B = $row_membre['net_B'];

            // Déterminer l'état du paiement
            if ($salaire_paye == $net_B) {
                $etat = 'payé';
            } elseif ($salaire_paye == 0) {
                $etat = 'en attente';
            } else {
                $etat = 'avance';
            }

            // Mettre à jour les données dans la table paiement
            $query = "UPDATE paiement SET jour_paiement='$jour_paiement', salaire_paye='$salaire_paye', etat='$etat' WHERE id_membre='$id_membre' AND mois_paiement='$mois_paiement'";
            mysqli_query($conn, $query);
        }
        // Enregistrement dans l'historique
        $action = "Modification d'un paiemement";
        $details = "Détails du mois de $mois_paiement";
        enregistrerHistorique($conn, $user_id, $action, $details);
        // Redirection après traitement si aucune erreur
        if (empty($erreur_message)) {
            header('Location: gestion_paiement.php');
            exit();
        }
    }
} else {
    $erreur_message = 'Mois et jour de paiement non spécifiés.';
}

// Récupération des projets pour affichage dans le formulaire
$query_projets = "SELECT id_project, nom_project FROM projects";
$result_projets = mysqli_query($conn, $query_projets);

// Récupération des départements pour affichage dans le formulaire
$query_departments = "SELECT id_department, nom_department FROM department";
$result_departments = mysqli_query($conn, $query_departments);
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Éditer Paiement</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>

<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h2 class="text-center">Éditer Paiement</h2>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($erreur_message)) : ?>
                            <div class="alert alert-danger erreur-message">
                                <?php echo $erreur_message; ?>
                            </div>
                        <?php endif; ?>
                        <form action="" method="POST">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label for="mois_paiement">Mois de Paiement</label>
                                    <input type="text" class="form-control" id="mois_paiement" name="mois_paiement" value="<?php echo $mois_paiement; ?>" readonly>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="jour_paiement">Jour de Paiement</label>
                                    <input type="date" class="form-control" id="jour_paiement" name="jour_paiement" value="<?php echo $jour_paiement; ?>" required>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>Filtrer par Projet</label>
                                    <select class="form-control" id="filter_projet">
                                        <option value="">Tous les Projets</option>
                                        <?php
                                        while ($row = mysqli_fetch_assoc($result_projets)) {
                                            echo "<option value='" . $row['id_project'] . "'>" . $row['nom_project'] . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label>Filtrer par Département</label>
                                    <select class="form-control" id="filter_department">
                                        <option value="">Tous les Départements</option>
                                        <?php
                                        while ($row = mysqli_fetch_assoc($result_departments)) {
                                            echo "<option value='" . $row['id_department'] . "'>" . $row['nom_department'] . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Sélectionner les Membres à Payer et Montant</label>
                                <div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="select_all">
                                        <label class="form-check-label" for="select_all">Tous</label>
                                    </div>
                                    <div class="row">
                                        <?php
                                        $counter = 0;
                                        while ($row = mysqli_fetch_assoc($result_paiements)) {
                                            if ($counter % 3 == 0 && $counter != 0) {
                                                echo "</div><div class='row'>";
                                            }
                                            echo "<div class='col-md-4 membre-row' data-projet='" . $row['id_project'] . "' data-department='" . $row['id_department'] . "'>";
                                            echo "<div class='form-check'>";
                                            echo "<input class='form-check-input membre-checkbox' type='checkbox' name='membres[]' value='" . $row['id_membre'] . "' id='membre_" . $row['id_membre'] . "' checked>";
                                            echo "<label class='form-check-label' for='membre_" . $row['id_membre'] . "'>" . $row['nom'] . " " . $row['postnom'] . " " . $row['prenom'] . " - " . $row['net_B'] . " $</label>";
                                            echo "<input type='number' class='form-control mt-2' name='salaire_paye[" . $row['id_membre'] . "]' placeholder='Montant payé' value='" . $row['salaire_paye'] . "'>";
                                            echo "</div></div>";
                                            $counter++;
                                        }
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">Enregistrer Paiement</button>
                            <a href="gestion_paiement.php" class="btn btn-secondary btn-block">Annuler</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <!-- JavaScript Bootstrap -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <!-- Font Awesome -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>
    <script>
        // Fonction pour sélectionner ou désélectionner tous les membres
        $("#select_all").click(function() {
            var isChecked = this.checked;
            $(".membre-checkbox").each(function() {
                $(this).prop('checked', isChecked);
            });
            filterMembres();
        });

        // Fonction pour filtrer les membres par projet
        $("#filter_projet").change(function() {
            filterMembres();
        });

        // Fonction pour filtrer les membres par département
        $("#filter_department").change(function() {
            filterMembres();
        });

        // Fonction pour filtrer les membres selon les sélections
        function filterMembres() {
            var selectedProjet = $("#filter_projet").val();
            var selectedDepartment = $("#filter_department").val();

            $(".membre-row").each(function() {
                var projet = $(this).data('projet');
                var department = $(this).data('department');
                var matchProjet = (selectedProjet === "" || selectedProjet == projet);
                var matchDepartment = (selectedDepartment === "" || selectedDepartment == department);

                if (matchProjet && matchDepartment) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }
    </script>
</body>

</html>

<?php
// Fermer la connexion à la base de données
mysqli_close($conn);
?>