<?php
// Démarrer la session
require "cache.php";

// Récupérer les informations de l'utilisateur connecté
$user_id = $_SESSION['id_user'];
$user_query = "SELECT role, email FROM users WHERE id_user = $user_id";
$user_result = mysqli_query($conn, $user_query);
$role = '';
$user_email = '';
if ($user_row = mysqli_fetch_assoc($user_result)) {
    $role = $user_row['role'];
    $user_email = $user_row['email'];
}

// Récupérer les informations du membre
$membre_query = "SELECT id_membre, date_debut_contrat, (SELECT MAX(date_fin) FROM demandes_conge WHERE id_membre = m.id_membre) as dernier_conge, contrat FROM membres m WHERE email_professionel = '$user_email'";
$membre_result = mysqli_query($conn, $membre_query);

if ($membre_info = mysqli_fetch_assoc($membre_result)) {
    // Calcul du nombre de jours de congé en fonction du type de contrat
    $jours_conge_max = 22;
    switch ($membre_info['contrat']) {
        case 'moins de 3 mois':
            $jours_conge_max = 5;
            break;
        case '6 mois':
            $jours_conge_max = 11;
            break;
        case '12 mois':
            $jours_conge_max = 22;
            break;
    }

    // Récupérer le nombre de jours de congé déjà pris
    $conge_pris_query = "SELECT SUM(DATEDIFF(date_fin, date_debut) + 1) as jours_pris FROM demandes_conge WHERE id_membre = {$membre_info['id_membre']}";
    $conge_pris_result = mysqli_query($conn, $conge_pris_query);
    $conge_pris_row = mysqli_fetch_assoc($conge_pris_result);
    $conge_pris = $conge_pris_row['jours_pris'] ?? 0; // Si aucun congé pris, mettre à zéro
    $jours_restant = $jours_conge_max - $conge_pris;
} else {
    die("Erreur: Aucune information sur le membre avec l'adresse email professionnelle fournie.");
}

// Traitement du formulaire de demande de congé
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_membre = $_POST['id_membre'];
    $date_debut = $_POST['date_debut'];
    $date_fin = $_POST['date_fin'];
    $date_retour = $_POST['date_retour'];
    $raison = $_POST['raison'];
    $superviseur = $_POST['superviseur'];
    $departement = $_POST['departement'];
    $date_demande = date("Y-m-d");

    // Calcul du nombre de jours de congé demandé
    $nombre_jours_conge = (strtotime($date_fin) - strtotime($date_debut)) / (60 * 60 * 24) + 1;

    // Calcul du nombre de jours entre la date de fin du congé et la date de retour
    $jours_retour = (strtotime($date_retour) - strtotime($date_fin)) / (60 * 60 * 24);

    if ($nombre_jours_conge <= 0) {
        echo "La durée du congé ne peut pas être inférieure ou égale à zéro.";
    } elseif ($jours_retour <= 0) {
        echo "La date de retour doit être après la date de fin du congé.";
    } elseif ($nombre_jours_conge > $jours_restant) {
        echo "Le nombre de jours de congé demandés dépasse le nombre de jours restants.";
    } else {
        // Préparation de la requête SQL
        $sql = "INSERT INTO demandes_conge (id_membre, date_debut, date_fin, raison, date_demande, superviseur,chef_departement) VALUES (?, ?, ?, ?, ?, ?,?)";
        $stmt = mysqli_prepare($conn, $sql);

        // Vérification de la préparation de la requête
        if ($stmt === false) {
            die('Erreur de préparation de la requête : ' . htmlspecialchars(mysqli_error($conn)));
        }

        // Liaison des paramètres
        mysqli_stmt_bind_param($stmt, 'issssss', $id_membre, $date_debut, $date_fin, $raison, $date_demande, $superviseur, $departement);

        // Exécution de la requête
        if (mysqli_stmt_execute($stmt)) {
            echo "Demande de congé soumise avec succès.";
            // Enregistrement dans l'historique
            $action = "Demande de congé";
            $details = "Demande de congé du $date_debut au $date_fin pour le membre avec l'email $user_email.";
            enregistrerHistorique($conn, $user_id, $action, $details);
            // Redirection après traitement
            header('Location: confirmation.php');
            exit();
        } else {
            echo "Erreur lors de la soumission de la demande de congé : " . htmlspecialchars(mysqli_stmt_error($stmt));
        }

        // Fermeture de la requête
        mysqli_stmt_close($stmt);
    }
}


// Récupération des superviseurs pour le formulaire (utilisateurs)
$sql_superviseurs = "SELECT id_user, username FROM users";
$result_superviseurs = $conn->query($sql_superviseurs);
// Récupération des chef du département pour le formulaire (utilisateurs)
$sql_departement = "SELECT id_user, username FROM users";
$result_departement = $conn->query($sql_departement);


$pageTitle = "Demande de Congé";
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
        .form-container {
            margin-top: 20px;
        }
    </style>

</head>

<body>
    <!-- Menu -->
    <?php require "menu.php"; ?>
    <div class="container form-container">
        <h2>Demande de Congé</h2>
        <form action="demande_conge.php" method="post" onsubmit="return validateDates();">
            <div class="form-row">
                <div class="form-group col-md-3">
                    <label for="">Mail</label>
                    <select class="form-control" id="id_membre" name="id_membre" required>
                        <?php
                        $user_id_query = "SELECT id_membre FROM membres WHERE email_professionel = '$user_email'";
                        $user_id_result = mysqli_query($conn, $user_id_query);
                        if ($user_id_row = mysqli_fetch_assoc($user_id_result)) {
                            $user_id_membre = $user_id_row['id_membre'];
                            echo "<option value='$user_id_membre' selected>$user_email</option>";
                        }
                        ?>
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label for="date_debut_contrat">Date de début du contrat</label>
                    <input type="text" class="form-control" id="date_debut_contrat" value="<?php echo $membre_info['date_debut_contrat']; ?>" disabled>
                </div>
                <div class="form-group col-md-3">
                    <label for="dernier_conge">Date du dernier congé</label>
                    <!-- Ajoutez un champ caché pour stocker la date du dernier congé -->
                    <input type="hidden" id="dernier_conge_date" value="<?php echo $membre_info['dernier_conge']; ?>">
                    <input type="text" class="form-control" id="dernier_conge" value="<?php echo $membre_info['dernier_conge']; ?>" disabled>
                </div>
                <div class="form-group col-md-3">
                    <label for="jours_restant">Nombre de jours de congé restants</label>
                    <input type="text" class="form-control" id="jours_restant" value="<?php echo $jours_restant; ?>" disabled>
                </div>
            </div>

            <div class="form-group">
                <label for="type_conge">Type de congé demandé</label>
                <select class="form-control" id="type_conge" name="type_conge" required>
                    <option value="annuel">Annuel</option>
                    <option value="maladie">Maladie</option>
                    <option value="maternité">Maternité</option>
                    <!-- Ajouter d'autres types de congé si nécessaire -->
                </select>
            </div>

            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="date_debut">Date de début du congé</label>
                    <input type="date" class="form-control" id="date_debut" name="date_debut" required>
                </div>
                <div class="form-group col-md-4">
                    <label for="date_fin">Date de fin du congé</label>
                    <input type="date" class="form-control" id="date_fin" name="date_fin" required>
                </div>
                <div class="form-group col-md-4">
                    <label for="date_retour">Date de retour</label>
                    <input type="date" class="form-control" id="date_retour" name="date_retour" required>
                </div>
            </div>

            <div class="form-group">
                <label for="raison">Raison</label>
                <textarea class="form-control" id="raison" name="raison" rows="3" required></textarea>
            </div>
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label for="superviseur">Superviseur</label>
                    <select class="form-control" id="superviseur" name="superviseur" required>
                        <?php while ($superviseur = mysqli_fetch_assoc($result_superviseurs)) : ?>
                            <option value="<?php echo $superviseur['id_user']; ?>">
                                <?php echo $superviseur['username']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label for="departement"> Responsable du département</label>
                    <select class="form-control" id="departement" name="departement" required>
                        <?php while ($departement = mysqli_fetch_assoc($result_departement)) : ?>
                            <option value="<?php echo $departement['id_user']; ?>">
                                <?php echo $departement['username']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
            </div>


            <button type="submit" class="btn btn-primary">Soumettre</button>

            <a href="gestion_conge.php" class="btn btn-secondary">Annuler</a>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://stackpath.amazonaws.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
    <script>
        function isValidWeekday(dateString) {
            const date = new Date(dateString);
            const day = date.getUTCDay();
            // Le jour 0 est dimanche (dimanche = 0, lundi = 1, ..., samedi = 6)
            return day >= 1 && day <= 5; // Retourne vrai si le jour est entre lundi (1) et vendredi (5)
        }

        function validateDates() {
            const dateDebut = document.getElementById('date_debut').value;
            const dateFin = document.getElementById('date_fin').value;
            const dateRetour = document.getElementById('date_retour').value;
            const dernierConge = document.getElementById('dernier_conge_date').value;

            // Récupération de la date actuelle
            const currentDate = new Date();
            currentDate.setHours(0, 0, 0, 0); // Ignore l'heure pour ne comparer que les dates

            // Vérification des jours de semaine
            if (!isValidWeekday(dateDebut)) {
                alert("La date de début du congé doit être un jour de semaine (lundi à vendredi).");
                return false;
            }

            if (!isValidWeekday(dateFin)) {
                alert("La date de fin du congé doit être un jour de semaine (lundi à vendredi).");
                return false;
            }

            if (!isValidWeekday(dateRetour)) {
                alert("La date de retour doit être un jour de semaine (lundi à vendredi).");
                return false;
            }

            // Calcul du nombre de jours entre les dates
            const startDate = new Date(dateDebut);
            const endDate = new Date(dateFin);
            const returnDate = new Date(dateRetour);
            const dernierCongeDate = new Date(dernierConge);

            // Vérification que la date de début n'est pas antérieure à la date actuelle
            if (startDate < currentDate) {
                alert("La date de début du congé ne peut pas être antérieure à la date actuelle.");
                return false;
            }

            if (diffDaysDebutFin <= 0) {
                alert("La date de fin du congé doit être postérieure à la date de début.");
                return false;
            }

            if (diffDaysFinRetour <= 0) {
                alert("La date de retour doit être postérieure à la date de fin du congé.");
                return false;
            }

            if (startDate <= dernierCongeDate) {
                alert("La date de début du congé ne peut pas être antérieure à la date du dernier congé pris.");
                return false;
            }

            return true;
        }
    </script>

</body>

</html>