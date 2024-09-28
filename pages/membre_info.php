<?php
require "cache.php";

$message = '';
$email = isset($_GET['email']) ? mysqli_real_escape_string($conn, $_GET['email']) : '';

// Vérifier le rôle de l'utilisateur (supposons que vous avez une fonction ou une variable pour obtenir le rôle de l'utilisateur)
$role = 'user'; // Remplacez cela par la méthode pour obtenir le rôle de l'utilisateur courant

if ($email) {
    // Requête pour obtenir les informations du membre
    $query = "SELECT * FROM membres WHERE email_professionel = '$email' OR email_prive = '$email'";
    $result = mysqli_query($conn, $query);

    // Récupération des données des tables department et project pour les dropdowns
    $query_departments = "SELECT * FROM department";
    $result_departments = mysqli_query($conn, $query_departments);

    $query_projects = "SELECT * FROM projects";
    $result_projects = mysqli_query($conn, $query_projects);

    if ($result && mysqli_num_rows($result) > 0) {
        $membre = mysqli_fetch_assoc($result);
    } else {
        $message = "Aucun membre trouvé avec cet email.";
    }
} else {
    $message = "Aucun email fourni.";
}

mysqli_close($conn);
$pageTitle = "Mon profil";
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <script>
        function calculateNetSalary() {
            var salaire = parseFloat(document.getElementById('salaire').value) || 0;
            var salaireNet = salaire * 0.9; // Exemple de calcul : salaire net est 90% du salaire brut
            document.getElementById('salaire_net').value = salaireNet.toFixed(2);
        }
    </script>
</head>

<body>
    <!-- Menu -->
    <?php require "menu.php" ?>
    <div class="container mt-5">
        <h1>Informations du Membre</h1>
        <form action="" method="POST" id="editMembreForm">
            <div class="form-row">
                <div class="form-group col-md-2">
                    <label for="nom">Nom</label>
                    <input type="text" class="form-control" id="nom" name="nom" value="<?php echo $membre['nom']; ?>" readonly>
                </div>
                <div class="form-group col-md-2">
                    <label for="postnom">Postnom</label>
                    <input type="text" class="form-control" id="postnom" name="postnom" value="<?php echo $membre['postnom']; ?>" readonly>
                </div>
                <div class="form-group col-md-2">
                    <label for="prenom">Prénom</label>
                    <input type="text" class="form-control" id="prenom" name="prenom" value="<?php echo $membre['prenom']; ?>" readonly>
                </div>
                <div class="form-group col-md-2">
                    <label for="genre">Genre</label>
                    <select class="form-control" id="genre" name="genre" disabled>
                        <option value="homme" <?php if ($membre['genre'] == 'homme') echo 'selected'; ?>>Homme</option>
                        <option value="femme" <?php if ($membre['genre'] == 'femme') echo 'selected'; ?>>Femme</option>
                    </select>
                </div>
                <div class="form-group col-md-2">
                    <label for="adresse">Adresse physique</label>
                    <input type="text" class="form-control" id="adresse" name="adresse" value="<?php echo $membre['adresse']; ?>" readonly>
                </div>
                <div class="form-group col-md-2">
                    <label for="etat_civil">État civil</label>
                    <select class="form-control" id="etat_civil" name="etat_civil" disabled>
                        <option value="marié" <?php if ($membre['etat_civil'] == 'marié') echo 'selected'; ?>>Marié</option>
                        <option value="célibataire" <?php if ($membre['etat_civil'] == 'célibataire') echo 'selected'; ?>>Célibataire</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-2">
                    <label for="numero_telephone">Numéro de téléphone</label>
                    <input type="text" class="form-control" id="numero_telephone" name="numero_telephone" value="<?php echo $membre['numero_telephone']; ?>" readonly>
                </div>
                <div class="form-group col-md-2">
                    <label for="email">Email privé</label>
                    <input type="email" class="form-control" id="email" name="email_prive" value="<?php echo $membre['email_prive']; ?>" readonly>
                </div>
                <div class="form-group col-md-2">
                    <label for="date_naissance">Date de naissance</label>
                    <input type="date" class="form-control" id="date_naissance" name="date_naissance" value="<?php echo $membre['date_naissance']; ?>" readonly>
                </div>
                <div class="form-group col-md-2">
                    <label for="lieu_naissance">Lieu de naissance</label>
                    <input type="text" class="form-control" id="lieu_naissance" name="lieu_naissance" value="<?php echo $membre['lieu_naissance']; ?>" readonly>
                </div>
                <div class="form-group col-md-2">
                    <label for="nationalite">Nationalité</label>
                    <input type="text" class="form-control" id="nationalite" name="nationalite" value="<?php echo $membre['nationalite']; ?>" readonly>
                </div>
                <div class="form-group col-md-2">
                    <label for="province_origine">Province d'origine</label>
                    <input type="text" class="form-control" id="province_origine" name="province_origine" value="<?php echo $membre['province_origine']; ?>" readonly>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-2">
                    <label for="matricule">Matricule</label>
                    <input type="text" class="form-control" id="matricule" name="matricule" value="<?php echo $membre['matricule']; ?>" readonly>
                </div>
                <div class="form-group col-md-2">
                    <label for="department">Département</label>
                    <select class="form-control" id="department" name="department" disabled>
                        <?php while ($department = mysqli_fetch_assoc($result_departments)) : ?>
                            <option value="<?php echo $department['id_department']; ?>" <?php if ($membre['id_department'] == $department['id_department']) echo 'selected'; ?>><?php echo $department['nom_department']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group col-md-2">
                    <label for="fonction">Fonction</label>
                    <input type="text" class="form-control" id="fonction" name="fonction" value="<?php echo $membre['fonction']; ?>" readonly>
                </div>
                <div class="form-group col-md-2">
                    <label for="project">Projet</label>
                    <select class="form-control" id="project" name="project" disabled>
                        <option value="">Aucun</option>
                        <?php while ($project = mysqli_fetch_assoc($result_projects)) : ?>
                            <option value="<?php echo $project['id_project']; ?>" <?php if ($membre['id_project'] == $project['id_project']) echo 'selected'; ?>><?php echo $project['nom_project']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group col-md-2">
                    <label for="type_contrat">Type de contrat</label>
                    <input type="text" class="form-control" id="type_contrat" name="type_contrat" value="<?php echo $membre['type_contrat']; ?>" readonly>
                </div>
                <div class="form-group col-md-2">
                    <label for="date_debut_contrat">Début du contrat</label>
                    <input type="date" class="form-control" id="date_debut_contrat" name="date_debut_contrat" value="<?php echo $membre['date_debut_contrat']; ?>" readonly>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-2">
                    <label for="date_fin_contrat">Fin du contrat</label>
                    <input type="date" class="form-control" id="date_fin_contrat" name="date_fin_contrat" value="<?php echo $membre['date_fin_contrat']; ?>" readonly>
                </div>
                <div class="form-group col-md-2">
                    <label for="lieu_affectation">Lieu d'affectation</label>
                    <input type="text" class="form-control" id="lieu_affectation" name="lieu_affectation" value="<?php echo $membre['lieu_affectation']; ?>" readonly>
                </div>
                <div class="form-group col-md-2">
                    <label for="email_prive">Email professionnel</label>
                    <input type="email" class="form-control" id="email_pro" name="email_professionel" value="<?php echo $membre['email_professionel']; ?>" readonly>
                </div>
                <div class="form-group col-md-2">
                    <label for="net_B">Salaire</label>
                    <input type="number" step="0.01" class="form-control" id="net_B" name="net_B" value="<?php echo $membre['net_B']; ?>" readonly>
                </div>
                <div class="form-group col-md-2">
                    <label for="nom_pere">Nom du père</label>
                    <input type="text" class="form-control" id="nom_pere" name="nom_pere" value="<?php echo $membre['nom_pere']; ?>" readonly>
                </div>
                <div class="form-group col-md-2">
                    <label for="nom_mere">Nom de la mère</label>
                    <input type="text" class="form-control" id="nom_mere" name="nom_mere" value="<?php echo $membre['nom_mere']; ?>" readonly>
                </div>

            </div>
            <div class="form-row">
                <div class="form-group col-md-2">
                    <label for="num_identification">Numéro d'ID</label>
                    <input type="text" class="form-control" id="num_identification" name="num_identification" value="<?php echo $membre['num_identification']; ?>" readonly>
                </div>
                <div class="form-group col-md-2">
                    <label for="num_compte_banque">N° de compte bancaire</label>
                    <input type="text" class="form-control" id="num_compte_banque" name="num_compte_banque" value="<?php echo $membre['num_compte_banque']; ?>" readonly>
                </div>
                <div class="form-group col-md-2">
                    <label for="personne_reference">Personne de référence</label>
                    <input type="text" class="form-control" id="personne_reference" name="personne_reference" value="<?php echo $membre['personne_reference']; ?>" readonly>
                </div>
                <div class="form-group col-md-2">
                    <label for="deuxieme_adresse">Deuxième adresse</label>
                    <input type="text" class="form-control" id="deuxieme_adresse" name="deuxieme_adresse" value="<?php echo $membre['deuxieme_adresse']; ?>" readonly>
                </div>
            </div>
            <a href="profil.php" class="btn btn-secondary">Retour sur profil</a>
        </form>
    </div>
</body>

</html>