<?php
// Démarrer la session
require "cache.php";

$message = '';
$initial_data = [
    'nom' => '',
    'prenom' => '',
    'postnom' => '',
    'adresse' => '',
    'numero_telephone' => '',
    'email_prive' => '',
    'genre' => '',
    'date_naissance' => '',
    'date_debut_contrat' => '',
    'date_fin_contrat' => '',
    'type_contrat' => '',
    'department' => '',
    'fonction' => '',
    'matricule' => '',
    'project' => ''
];

// Récupération des données des tables department et projet
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
    $email = mysqli_real_escape_string($conn, $_POST['email_prive']);
    $genre = mysqli_real_escape_string($conn, $_POST['genre']);
    $date_naissance = mysqli_real_escape_string($conn, $_POST['date_naissance']);
    $date_debut = mysqli_real_escape_string($conn, $_POST['date_debut_contrat']);
    $date_fin = mysqli_real_escape_string($conn, $_POST['date_fin_contrat']);
    $type_contrat = mysqli_real_escape_string($conn, $_POST['type_contrat']);
    $id_department = mysqli_real_escape_string($conn, $_POST['department']);
    $fonction = mysqli_real_escape_string($conn, $_POST['fonction']);
    $matricule = mysqli_real_escape_string($conn, $_POST['matricule']);
    $id_projet = mysqli_real_escape_string($conn, $_POST['project']);

    // Vérifier l'unicité de l'adresse email dans les tables membres et users
    $check_email_query_membres = "SELECT * FROM membres WHERE email_prive = '$email'";
    $check_email_query_users = "SELECT * FROM users WHERE email = '$email'";

    // Vérifier l'unicité du nom, postnom, prénom
    $check_name_query_membres = "SELECT * FROM membres WHERE nom = '$nom' AND prenom = '$prenom' AND postnom = '$postnom'";

    // Vérifier l'unicité du matricule
    $check_matricule_query_membres = "SELECT * FROM membres WHERE matricule = '$matricule'";

    $result_email_check_membres = mysqli_query($conn, $check_email_query_membres);
    $result_email_check_users = mysqli_query($conn, $check_email_query_users);
    $result_name_check_membres = mysqli_query($conn, $check_name_query_membres);
    $result_matricule_check_membres = mysqli_query($conn, $check_matricule_query_membres);

    if (mysqli_num_rows($result_email_check_membres) > 0 || mysqli_num_rows($result_email_check_users) > 0) {
        $message = "Erreur : Cette adresse email est déjà utilisée.";
        $initial_data = $_POST; // Remplir les champs avec les données du formulaire sauf 'email'
        $initial_data['email_prive'] = ''; // Réinitialiser le champ email
    } elseif (mysqli_num_rows($result_name_check_membres) > 0) {
        $message = "Erreur : Un membre avec le même nom, prénom et postnom existe déjà.";
        $initial_data = $_POST; // Remplir les champs avec les données du formulaire sauf 'NOM'
        $initial_data['nom'] = ''; // Réinitialiser le champ NOM
    } elseif (mysqli_num_rows($result_matricule_check_membres) > 0) {
        $message = "Erreur : Ce matricule est déjà utilisé.";
        $initial_data = $_POST; // Remplir les champs avec les données du formulaire sauf 'NOM'
        $initial_data['matricule'] = ''; // Réinitialiser le champ NOM
    } else {
        // Insertion du nouveau membre dans la table membres
        $query = "INSERT INTO membres (nom, prenom, postnom, adresse, numero_telephone, email_prive, genre, date_naissance, date_debut_contrat, date_fin_contrat, type_contrat, id_department, fonction, matricule, id_project)
                  VALUES ('$nom', '$prenom', '$postnom', '$adresse', '$numero_telephone', '$email', '$genre', '$date_naissance', '$date_debut', '$date_fin', '$type_contrat', '$id_department', '$fonction', '$matricule', " . ($id_projet ? "'$id_projet'" : "NULL") . ")";

        if (mysqli_query($conn, $query)) {
            // Récupérer l'ID du membre ajouté
            $id_membre = mysqli_insert_id($conn);

            // Incrémenter l'effectif du département
            $update_department_query = "UPDATE department SET effectif = effectif + 1 WHERE id_department = '$id_department'";
            mysqli_query($conn, $update_department_query);

            // Créer un nom d'utilisateur unique (par exemple : prénom.nom)
            $username = strtolower($prenom . '.' . $nom);

            // Générer un mot de passe temporaire (par exemple : prenom1234)
            $password = password_hash(strtolower($prenom) . '1234', PASSWORD_DEFAULT);

            // Définir le rôle par défaut (par exemple : 'membre')
            $role = 'user';

            // Insérer le nouvel utilisateur dans la table users
            $query_user = "INSERT INTO users (username, email, password, role, created_at)
                           VALUES ('$username', '$email', '$password', '$role', NOW())";

            if (mysqli_query($conn, $query_user)) {
                $message = "Membre et utilisateur ajoutés avec succès.";
                // Enregistrement dans l'historique
                $action = "Ajout d'un membre";
                $details = "Création de $username";
                enregistrerHistorique($conn, $user_id, $action, $details);
                header('Location: gestion_membre.php');
                exit();
            } else {
                $message = "Erreur lors de la création de l'utilisateur: " . mysqli_error($conn);
            }
        } else {
            $message = "Erreur: " . mysqli_error($conn);
        }
    }
}

// Fermer la connexion à la base de données
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="fr">

<?php require "head.php" ?>


<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h2 class="text-center">Ajouter un membre</h2>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($message)) : ?>
                            <div class="alert alert-danger"><?php echo $message; ?></div>
                        <?php endif; ?>
                        <form action="" method="POST" id="addMembreForm" onsubmit="return validateForm()">
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label for="nom">Nom</label>
                                    <input type="text" class="form-control" id="nom" name="nom" value="<?php echo htmlspecialchars($initial_data['nom']); ?>" placeholder="Nom" required>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="postnom">Postnom</label>
                                    <input type="text" class="form-control" id="postnom" name="postnom" value="<?php echo htmlspecialchars($initial_data['postnom']); ?>" placeholder="Postnom" required>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="prenom">Prénom</label>
                                    <input type="text" class="form-control" id="prenom" name="prenom" value="<?php echo htmlspecialchars($initial_data['prenom']); ?>" placeholder="Prénom" required>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label for="adresse">Adresse physique</label>
                                    <input type="text" class="form-control" id="adresse" name="adresse" value="<?php echo htmlspecialchars($initial_data['adresse']); ?>" placeholder="Adresse" required>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="numero_telephone">Numéro de téléphone</label>
                                    <input type="text" class="form-control" id="numero_telephone" name="numero_telephone" value="<?php echo htmlspecialchars($initial_data['numero_telephone']); ?>" placeholder="Numéro de téléphone" required>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="email">Email privé</label>
                                    <input type="email" class="form-control" id="email" name="email_prive" value="<?php echo htmlspecialchars($initial_data['email_prive']); ?>" placeholder="Adresse email" required>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label for="matricule">Matricule</label>
                                    <input type="text" class="form-control" id="matricule" name="matricule" value="<?php echo htmlspecialchars($initial_data['matricule']); ?>" placeholder="Matricule" required>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="genre">Genre</label>
                                    <select class="form-control" id="genre" name="genre" required>
                                        <option value="homme" <?php echo ($initial_data['genre'] == 'homme') ? 'selected' : ''; ?>>Homme</option>
                                        <option value="femme" <?php echo ($initial_data['genre'] == 'femme') ? 'selected' : ''; ?>>Femme</option>
                                    </select>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="date_naissance">Date de naissance</label>
                                    <input type="date" class="form-control" id="date_naissance" name="date_naissance" value="<?php echo htmlspecialchars($initial_data['date_naissance']); ?>" required>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label for="department">Département</label>
                                    <select class="form-control" id="department" name="department" required>
                                        <?php
                                        while ($row = mysqli_fetch_assoc($result_departments)) {
                                            $selected = ($initial_data['department'] == $row['id_department']) ? 'selected' : '';
                                            echo "<option value='{$row['id_department']}' $selected>{$row['nom_department']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="fonction">Fonction</label>
                                    <input type="text" class="form-control" id="fonction" name="fonction" value="<?php echo htmlspecialchars($initial_data['fonction']); ?>" placeholder="Fonction" required>
                                </div>
                                <div class="form-group col-md-4">
                                    <label for="type_contrat">Type de contrat</label>
                                    <select class="form-control" id="type_contrat" name="type_contrat" required onchange="toggleDateFields(this.value)">
                                        <option value="CDI" <?php echo ($initial_data['type_contrat'] == 'CDI') ? 'selected' : ''; ?>>CDI</option>
                                        <option value="CDD" <?php echo ($initial_data['type_contrat'] == 'CDD') ? 'selected' : ''; ?>>CDD</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-row" id="datesContrat" style="display: none;">
                                <div class="form-group col-md-6">
                                    <label for="date_debut_contrat">Date de début de contrat</label>
                                    <input type="date" class="form-control" id="date_debut_contrat" name="date_debut_contrat" value="<?php echo htmlspecialchars($initial_data['date_debut_contrat']); ?>" placeholder="Date de début de contrat">
                                </div>
                                <div class="form-group col-md-6">
                                    <label for="date_fin_contrat">Date de fin de contrat</label>
                                    <input type="date" class="form-control" id="date_fin_contrat" name="date_fin_contrat" value="<?php echo htmlspecialchars($initial_data['date_fin_contrat']); ?>" placeholder="Date de fin de contrat">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label for="project">Projet</label>
                                    <select class="form-control" id="project" name="project">
                                        <option value="">Aucun</option>
                                        <?php
                                        while ($row = mysqli_fetch_assoc($result_projects)) {
                                            $selected = ($initial_data['project'] == $row['id_project']) ? 'selected' : '';
                                            echo "<option value='{$row['id_project']}' $selected>{$row['nom_project']}</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">Ajouter</button>
                            <a href="gestion_membre.php" class="btn btn-dark">Annuler</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS, Popper.js, and jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        function toggleDateFields(value) {
            if (value === 'CDD') {
                document.getElementById('datesContrat').style.display = 'block';
            } else {
                document.getElementById('datesContrat').style.display = 'none';
            }
        }

        function checkAge() {
            var dob = new Date(document.getElementById('date_naissance').value);
            var today = new Date();
            var age = today.getFullYear() - dob.getFullYear();
            var m = today.getMonth() - dob.getMonth();
            if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) {
                age--;
            }

            if (age < 18) {
                alert("L'âge du membre doit être supérieur ou égal à 18 ans.");
                return false;
            }
            return true;
        }

        function checkMatriculeUnique(matricule) {
            // Logique pour vérifier si le matricule est unique
            // Vous devrez peut-être faire une requête AJAX pour vérifier l'unicité du matricule côté serveur.
            // Exemple simplifié ci-dessous
            var existingMatricules = ["M001", "M002", "M003"]; // Remplacez par une requête à votre base de données
            if (existingMatricules.includes(matricule)) {
                alert("Le matricule doit être unique.");
                return false;
            }
            return true;
        }

        function checkContractDates() {
            var startDate = new Date(document.getElementById('date_debut_contrat').value);
            var endDate = new Date(document.getElementById('date_fin_contrat').value);

            if (endDate <= startDate) {
                alert("La 'Date de fin de contrat' ne peut pas être antérieure ou égale à la 'Date de début de contrat'.");
                return false;
            }
            return true;
        }

        function validateForm() {
            var matricule = document.getElementById('matricule').value;
            if (!checkMatriculeUnique(matricule)) {
                return false;
            }

            if (!checkAge()) {
                return false;
            }

            if (document.getElementById('type_contrat').value === 'CDD') {
                if (!checkContractDates()) {
                    return false;
                }
            }

            // Si toutes les validations sont passées, soumettez le formulaire
            return true;
        }
    </script>

</body>

</html>