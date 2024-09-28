<?php
require "cache.php";

$message = '';

// Vérification de l'existence de l'ID du membre à modifier dans l'URL
if (isset($_GET['id'])) {
    $id_membre = mysqli_real_escape_string($conn, $_GET['id']);

    // Récupération des données actuelles du membre
    $query = "SELECT * FROM membres WHERE id_membre = '$id_membre'";
    $result = mysqli_query($conn, $query);
    $membre = mysqli_fetch_assoc($result);

    // Récupération des données des tables department et project pour les dropdowns
    $query_departments = "SELECT * FROM department";
    $result_departments = mysqli_query($conn, $query_departments);

    $query_projects = "SELECT * FROM projects";
    $result_projects = mysqli_query($conn, $query_projects);

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Récupération et échappement des données du formulaire
        $nom = mysqli_real_escape_string($conn, $_POST['nom']);
        $prenom = mysqli_real_escape_string($conn, $_POST['prenom']);
        $postnom = mysqli_real_escape_string($conn, $_POST['postnom']);
        $adresse = mysqli_real_escape_string($conn, $_POST['adresse']);
        $numero_telephone = mysqli_real_escape_string($conn, $_POST['numero_telephone']);
        $email = mysqli_real_escape_string($conn, $_POST['email_professionel']);
        $genre = mysqli_real_escape_string($conn, $_POST['genre']);
        $date_naissance = mysqli_real_escape_string($conn, $_POST['date_naissance']);
        $date_debut = mysqli_real_escape_string($conn, $_POST['date_debut_contrat']);
        $date_fin = mysqli_real_escape_string($conn, $_POST['date_fin_contrat']);
        $type_contrat = mysqli_real_escape_string($conn, $_POST['type_contrat']);
        $id_department = mysqli_real_escape_string($conn, $_POST['department']);
        $fonction = mysqli_real_escape_string($conn, $_POST['fonction']);
        $matricule = mysqli_real_escape_string($conn, $_POST['matricule']);
        $id_projet = mysqli_real_escape_string($conn, $_POST['project']);

        // Ajout des autres champs
        $nom_pere = mysqli_real_escape_string($conn, $_POST['nom_pere']);
        $nom_mere = mysqli_real_escape_string($conn, $_POST['nom_mere']);
        $nationalite = mysqli_real_escape_string($conn, $_POST['nationalite']);
        $province_origine = mysqli_real_escape_string($conn, $_POST['province_origine']);
        $lieu_affectation = mysqli_real_escape_string($conn, $_POST['lieu_affectation']);
        $num_identification = mysqli_real_escape_string($conn, $_POST['num_identification']);
        $etat_civil = mysqli_real_escape_string($conn, $_POST['etat_civil']);
        $deuxieme_adresse = mysqli_real_escape_string($conn, $_POST['deuxieme_adresse']);
        $email_prive = mysqli_real_escape_string($conn, $_POST['email_prive']);
        $num_compte_banque = mysqli_real_escape_string($conn, $_POST['num_compte_banque']);
        $personne_reference = mysqli_real_escape_string($conn, $_POST['personne_reference']);
        $lieu_naissance = mysqli_real_escape_string($conn, $_POST['lieu_naissance']);
        $salaire = mysqli_real_escape_string($conn, $_POST['salaire']);
        $salaire_net = mysqli_real_escape_string($conn, $_POST['salaire_net']);
        $net_B = mysqli_real_escape_string($conn, $_POST['net_B']);
        $personne_contact = mysqli_real_escape_string($conn, $_POST['personne_contact']);
        $partenaire = mysqli_real_escape_string($conn, $_POST['partenaire']);
        $nombre_enfant = mysqli_real_escape_string($conn, $_POST['nombre_enfant']);

        // Vérification de l'unicité de l'adresse email, des noms et du matricule, en excluant l'ID actuel
        $check_email_query = "SELECT * FROM membres WHERE (email_professionel = '$email' OR email_prive = '$email_prive') AND id_membre != '$id_membre'";
        $check_noms_query = "SELECT * FROM membres WHERE nom = '$nom' AND prenom = '$prenom' AND postnom = '$postnom' AND id_membre != '$id_membre'";
        $check_matricule_query = "SELECT * FROM membres WHERE matricule = '$matricule' AND id_membre != '$id_membre'";

        $result_email_check = mysqli_query($conn, $check_email_query);
        $result_noms_check = mysqli_query($conn, $check_noms_query);
        $result_matricule_check = mysqli_query($conn, $check_matricule_query);

        if (mysqli_num_rows($result_email_check) > 0) {
            $message = "Erreur : Cette adresse email est déjà utilisée.";
        } elseif (mysqli_num_rows($result_noms_check) > 0) {
            $message = "Erreur : Ce nom, prénom, et postnom sont déjà utilisés.";
        } elseif (mysqli_num_rows($result_matricule_check) > 0) {
            $message = "Erreur : Ce matricule est déjà utilisé.";
        } else {
            // Vérification si le département a changé
            if ($id_department != $membre['id_department']) {
                // Décrémenter l'effectif de l'ancien département
                $update_old_department_query = "UPDATE department SET effectif = effectif - 1 WHERE id_department = '" . $membre['id_department'] . "'";
                mysqli_query($conn, $update_old_department_query);

                // Incrémenter l'effectif du nouveau département
                $update_new_department_query = "UPDATE department SET effectif = effectif + 1 WHERE id_department = '$id_department'";
                mysqli_query($conn, $update_new_department_query);
            }

            // Requête de mise à jour
            $update_query = "UPDATE membres SET 
                nom='$nom', 
                prenom='$prenom', 
                postnom='$postnom', 
                adresse='$adresse', 
                numero_telephone='$numero_telephone', 
                email_professionel='$email',
                email_prive='$email_prive', 
                genre='$genre', 
                date_naissance='$date_naissance', 
                date_debut_contrat='$date_debut', 
                date_fin_contrat='$date_fin', 
                type_contrat='$type_contrat', 
                id_department='$id_department', 
                fonction='$fonction', 
                matricule='$matricule', 
                id_project=" . ($id_projet ? "'$id_projet'" : "NULL") . ",
                nom_pere='$nom_pere',
                nom_mere='$nom_mere',
                nationalite='$nationalite',
                province_origine='$province_origine',
                lieu_affectation='$lieu_affectation',
                num_identification='$num_identification',
                etat_civil='$etat_civil',
                deuxieme_adresse='$deuxieme_adresse',
                num_compte_banque='$num_compte_banque',
                personne_reference='$personne_reference',
                lieu_naissance='$lieu_naissance',
                salaire='$salaire',
                salaire_net='$salaire_net',
                net_B='$net_B',
                personne_de_contact='$personne_contact',
                partenaire='$partenaire',
                nombre_enfant='$nombre_enfant'
        
                WHERE id_membre='$id_membre'";

            if (mysqli_query($conn, $update_query)) {
                $message = "Membre mis à jour avec succès.";

                // Enregistrement dans l'historique
                $action = "Modification d'un membre";
                $details = "Détails de la modification de $nom $prenom $postnom";
                enregistrerHistorique($conn, $user_id, $action, $details);

                // Redirection après succès
                header('Location: gestion_membre.php');
                exit();
            } else {
                $message = "Erreur: " . mysqli_error($conn);
            }
        }
    }
} else {
    // Redirection si l'ID du membre n'est pas fourni
    header("Location: gestion_membre.php?message=" . urlencode("Erreur : Aucun ID de membre fourni."));
    exit();
}

// Fermer la connexion à la base de données
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <title>System_Information_Gestion</title>
    <link rel="shortcut icon" href="../img/tpo.ico" type="image/x-icon">
    <!-- Bootstrap CSS -->
    <link href="../css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" type="text/css" href="../css/font-awesome.min.css">
    <!-- Favicons -->
    <link href="../asset/img/apple-touch-icon.png" rel="apple-touch-icon">
    <script>
        function updateContractDates() {
            var typeContrat = document.getElementById('type_contrat').value;
            var dateDebutContrat = document.getElementById('date_debut_contrat');
            var dateFinContrat = document.getElementById('date_fin_contrat');

            if (typeContrat === 'CDI') {
                dateDebutContrat.required = false;
                dateFinContrat.required = false;
            } else {
                dateDebutContrat.required = true;
                dateFinContrat.required = true;
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('type_contrat').addEventListener('change', updateContractDates);
            updateContractDates(); // Initial check
        });

        function calculateNetSalary() {
            const salaire = parseFloat(document.getElementById('salaire').value);
            if (!isNaN(salaire)) {
                document.getElementById('salaire_net').value = (salaire * 0.70).toFixed(2);
            }
        }

        function afficherChampsSupplementaires() {
            var etatCivil = document.getElementById('etat_civil').value;
            if (etatCivil === 'celibataire') {
                document.getElementById('champs_personne_contact').style.display = 'block';
                document.getElementById('champs_partenaire').style.display = 'none';
            } else if (etatCivil === 'marie') {
                document.getElementById('champs_personne_contact').style.display = 'none';
                document.getElementById('champs_partenaire').style.display = 'block';
            }
        }
        // Initialiser l'affichage des champs au chargement de la page
        afficherChampsSupplementaires();
        // function calculateNetB() {
        //     const brutB = parseFloat(document.getElementById('brut_B').value);
        //     if (!isNaN(brutB)) {
        //         document.getElementById('net_B').value = (brutB * 0.70).toFixed(2);
        //     }
        // }
    </script>
</head>

<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h2 class="text-center">Modifier un membre</h2>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($message)) : ?>
                            <div class="alert alert-danger"><?php echo $message; ?></div>
                        <?php endif; ?>
                        <form action="" method="POST" id="editMembreForm">
                            <div class="form-row">
                                <div class="form-group col-md-3">
                                    <label for="nom">Nom</label>
                                    <input type="text" class="form-control" id="nom" name="nom" value="<?php echo $membre['nom']; ?>" required>
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="postnom">Postnom</label>
                                    <input type="text" class="form-control" id="postnom" name="postnom" value="<?php echo $membre['postnom']; ?>" required>
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="prenom">Prénom</label>
                                    <input type="text" class="form-control" id="prenom" name="prenom" value="<?php echo $membre['prenom']; ?>" required>
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="genre">Genre</label>
                                    <select class="form-control" id="genre" name="genre" required>
                                        <option value="homme" <?php if ($membre['genre'] == 'homme') echo 'selected'; ?>>Homme</option>
                                        <option value="femme" <?php if ($membre['genre'] == 'femme') echo 'selected'; ?>>Femme</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-3">
                                    <label for="adresse">Adresse physique</label>
                                    <input type="text" class="form-control" id="adresse" name="adresse" value="<?php echo $membre['adresse']; ?>" required>
                                </div>
                                <!-- Champs conditionnels -->
                                <div class="form-group col-md-3" id="champs_situation">
                                    <label for="etat_civil">État civil :</label>
                                    <select name="etat_civil" class="form-control" id="etat_civil" onchange="afficherChampsSupplementaires()">
                                        <option value="celibataire" <?php echo ($membre['etat_civil'] == 'celibataire') ? 'selected' : ''; ?>>Célibataire</option>
                                        <option value="marie" <?php echo ($membre['etat_civil'] == 'marie') ? 'selected' : ''; ?>>Marié</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="numero_telephone">Numéro de téléphone</label>
                                    <input type="text" class="form-control" id="numero_telephone" name="numero_telephone" value="<?php echo $membre['numero_telephone']; ?>" required>
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="email">Email privé</label>
                                    <input type="email" class="form-control" id="email" name="email_prive" value="<?php echo $membre['email_prive']; ?>" required>
                                </div>

                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-3">
                                    <label for="date_naissance">Date de naissance</label>
                                    <input type="date" class="form-control" id="date_naissance" name="date_naissance" value="<?php echo $membre['date_naissance']; ?>" required>
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="lieu_naissance">Lieu de naissance</label>
                                    <input type="text" class="form-control" id="lieu_naissance" name="lieu_naissance" value="<?php echo $membre['lieu_naissance']; ?>">
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="nationalite">Nationalité</label>
                                    <input type="text" class="form-control" id="nationalite" name="nationalite" value="<?php echo $membre['nationalite']; ?>">
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="province_origine">Province d'origine</label>
                                    <input type="text" class="form-control" id="province_origine" name="province_origine" value="<?php echo $membre['province_origine']; ?>">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-3">
                                    <label for="matricule">Matricule</label>
                                    <input type="text" class="form-control" id="matricule" name="matricule" value="<?php echo $membre['matricule']; ?>" required>
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="department">Département</label>
                                    <select class="form-control" id="department" name="department" required>
                                        <?php while ($department = mysqli_fetch_assoc($result_departments)) : ?>
                                            <option value="<?php echo $department['id_department']; ?>" <?php if ($membre['id_department'] == $department['id_department']) echo 'selected'; ?>><?php echo $department['nom_department']; ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="fonction">Fonction</label>
                                    <input type="text" class="form-control" id="fonction" name="fonction" value="<?php echo $membre['fonction']; ?>" required>
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="project">Projet</label>
                                    <select class="form-control" id="project" name="project">
                                        <option value="">Aucun</option>
                                        <?php while ($project = mysqli_fetch_assoc($result_projects)) : ?>
                                            <option value="<?php echo $project['id_project']; ?>" <?php if ($membre['id_project'] == $project['id_project']) echo 'selected'; ?>><?php echo $project['nom_project']; ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="type_contrat">Type de contrat</label>
                                    <input type="text" class="form-control" id="type_contrat" name="type_contrat" value="<?php echo $membre['type_contrat']; ?>" required>
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="date_debut_contrat">Début du contrat</label>
                                    <input type="date" class="form-control" id="date_debut_contrat" name="date_debut_contrat" value="<?php echo $membre['date_debut_contrat']; ?>">
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="date_fin_contrat">Fin du contrat</label>
                                    <input type="date" class="form-control" id="date_fin_contrat" name="date_fin_contrat" value="<?php echo $membre['date_fin_contrat']; ?>">
                                </div>
                                <div class="form-group col-md-3 ">
                                    <label for="lieu_affectation">Lieu d'affectation</label>
                                    <input type="text" class="form-control" id="lieu_affectation" name="lieu_affectation" value="<?php echo $membre['lieu_affectation']; ?>">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-3">
                                    <label for="email_prive">Email professionel</label>
                                    <input type="email" class="form-control" id="email_pro" name="email_professionel" value="<?php echo $membre['email_professionel']; ?>">
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="salaire">Salaire</label>
                                    <input type="number" step="0.01" class="form-control" id="salaire" name="salaire" value="<?php echo $membre['salaire']; ?>" oninput="calculateNetSalary()" required>
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="salaire_net">Salaire Net</label>
                                    <input type="number" step="0.01" class="form-control" id="salaire_net" name="salaire_net" value="<?php echo $membre['salaire_net']; ?>" readonly>
                                </div>

                                <div class="form-group col-md-3">
                                    <label for="net_B">Net B</label>
                                    <input type="number" step="0.01" class="form-control" id="net_B" name="net_B" value="<?php echo $membre['net_B']; ?>">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label for="nom_pere">Nom du père</label>
                                    <input type="text" class="form-control" id="nom_pere" name="nom_pere" value="<?php echo $membre['nom_pere']; ?>">
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="nom_mere">Nom de la mère</label>
                                    <input type="text" class="form-control" id="nom_mere" name="nom_mere" value="<?php echo $membre['nom_mere']; ?>">
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="num_identification">Numéro d'ID</label>
                                    <input type="text" class="form-control" id="num_identification" name="num_identification" value="<?php echo $membre['num_identification']; ?>">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label for="num_compte_banque">Numéro de compte bancaire</label>
                                    <input type="text" class="form-control" id="num_compte_banque" name="num_compte_banque" value="<?php echo $membre['num_compte_banque']; ?>">
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="personne_reference">Personne de référence</label>
                                    <input type="text" class="form-control" id="personne_reference" name="personne_reference" value="<?php echo $membre['personne_reference']; ?>">
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="deuxieme_adresse">Deuxième adresse</label>
                                    <input type="text" class="form-control" id="deuxieme_adresse" name="deuxieme_adresse" value="<?php echo $membre['deuxieme_adresse']; ?>">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-4" id="champs_personne_contact" style="display: none;">
                                    <label for="personne_contact">Personne de contact :</label>
                                    <input type="text" class="form-control" name="personne_contact" id="personne_contact" value="<?php echo htmlspecialchars($membre['personne_contact'] ?? ''); ?>">
                                </div>

                                <div class="form-group col-md-4" id="champs_partenaire" style="display: none;">
                                    <label for="partenaire">Partenaire :</label>
                                    <input type="text" class="form-control" name="partenaire" id="partenaire" value="<?php echo htmlspecialchars($membre['partenaire'] ?? ''); ?>">
                                </div>

                                <div class="form-group col-md-4">
                                    <label for="nombre_enfants">Nombre d'enfants :</label>
                                    <input type="number" class="form-control" name="nombre_enfant" id="nombre_enfants" value="<?php echo htmlspecialchars($membre['nombre_enfant'] ?? ''); ?>">
                                </div>
                            </div>


                            <button type="submit" class="btn btn-primary">Mettre à jour</button>
                            <a href="gestion_membre.php" class="btn btn-secondary">Annuler</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>