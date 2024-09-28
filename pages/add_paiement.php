<?php
// Démarrer la session
require "cache.php";
// Initialisation de la variable d'erreur
$erreur_message = '';

// Vérification de la soumission du formulaire
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupération et échappement des données du formulaire
    $mois_paiement = mysqli_real_escape_string($conn, $_POST['mois_paiement']);
    $jour_paiement = mysqli_real_escape_string($conn, $_POST['jour_paiement']);
    $membres = isset($_POST['membres']) ? $_POST['membres'] : [];

    // Validation et insertion des données dans la table paiement
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

        // Vérifier s'il y a déjà un paiement pour ce membre ce mois-ci
        $query_check_paiement = "SELECT * FROM paiement WHERE id_membre = '$id_membre' AND mois_paiement = '$mois_paiement'";
        $result_check_paiement = mysqli_query($conn, $query_check_paiement);

        if (mysqli_num_rows($result_check_paiement) == 0) {
            // Insérer les données dans la table paiement
            $query = "INSERT INTO paiement (id_membre, mois_paiement, jour_paiement, salaire_paye, etat) VALUES ('$id_membre', '$mois_paiement', '$jour_paiement', '$salaire_paye', '$etat')";
            mysqli_query($conn, $query);
        } else {
            $erreur_message = "Le membre $nom_membre $prenom_membre $postnom_membre a déjà été payé pour ce mois de $mois_paiement";
        }
    }
    // Enregistrement dans l'historique
    $action = "Ajout d'un payroll";
    $details = "Ajout payroll du mois de $mois_paiement";
    enregistrerHistorique($conn, $user_id, $action, $details);

    // Redirection après traitement si aucune erreur
    if (empty($erreur_message)) {
        header('Location: gestion_paiement.php');
        exit();
    }
}

// Récupération des membres pour affichage dans le formulaire
$query_membres = "SELECT id_membre, nom, prenom, postnom, net_B, id_project, id_department FROM membres";
$result_membres = mysqli_query($conn, $query_membres);

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
    <title>Ajouter Paiement</title>
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
                        <h2 class="text-center">Ajouter Paiement</h2>
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
                                    <select name="mois_paiement" class="form-control" id="mois_paiement" required>
                                        <option value="Janvier">Janvier</option>
                                        <option value="Février">Février</option>
                                        <option value="Mars">Mars</option>
                                        <option value="Avril">Avril</option>
                                        <option value="Mai">Mai</option>
                                        <option value="Juin">Juin</option>
                                        <option value="Juillet">Juillet</option>
                                        <option value="Août">Août</option>
                                        <option value="Septembre">Septembre</option>
                                        <option value="Octobre">Octobre</option>
                                        <option value="Novembre">Novembre</option>
                                        <option value="Décembre">Décembre</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="jour_paiement">Jour de Paiement</label>
                                    <input type="date" class="form-control" id="jour_paiement" name="jour_paiement" required>
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
                                        while ($row = mysqli_fetch_assoc($result_membres)) {
                                            if ($counter % 3 == 0 && $counter != 0) {
                                                echo "</div><div class='row'>";
                                            }
                                            echo "<div class='col-md-4 membre-row' data-projet='" . $row['id_project'] . "' data-department='" . $row['id_department'] . "'>";
                                            echo "<div class='form-check'>";
                                            echo "<input class='form-check-input membre-checkbox' type='checkbox' name='membres[]' value='" . $row['id_membre'] . "' id='membre_" . $row['id_membre'] . "'>";
                                            echo "<label class='form-check-label' for='membre_" . $row['id_membre'] . "'>" . $row['nom'] . " " . $row['postnom'] . " " . $row['prenom'] . " - " . $row['net_B'] . " $</label>";
                                            echo "<input type='number' class='form-control mt-2' name='salaire_paye[" . $row['id_membre'] . "]' placeholder='Montant payé' value='" . $row['net_B'] . "'>";
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