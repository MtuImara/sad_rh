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
// Définir les variables pour vérifier les rôles
$is_admin = ($role == 'admin');
$is_rh = ($role == 'rh');
$is_directeur = ($role == 'directeur');

// Vérifier si l'identifiant de la demande est passé en paramètre
if (isset($_GET['id_demande']) && is_numeric($_GET['id_demande'])) {
    $id_demande = intval($_GET['id_demande']);

    // Récupérer les détails de la demande
    $query = "SELECT * FROM demandes_conge WHERE id_demande = ?";
    if ($stmt = mysqli_prepare($conn, $query)) {
        mysqli_stmt_bind_param($stmt, 'i', $id_demande);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $demande = mysqli_fetch_assoc($result);
        mysqli_stmt_close($stmt);

        if (!$demande) {
            $_SESSION['message'] = 'Demande de congé non trouvée.';
            header('Location: gestion_conge.php');
            exit();
        }
    } else {
        $_SESSION['message'] = 'Erreur lors de la récupération de la demande de congé.';
        header('Location: gestion_conge.php');
        exit();
    }
} else {
    $_SESSION['message'] = 'Identifiant de demande invalide.';
    header('Location: gestion_conge.php');
    exit();
}
// Vérification si l'utilisateur connecté est le superviseur ou le chef de département désigné
$is_superviseur = ($user_id == $demande['superviseur']);
$is_chef_departement = ($user_id == $demande['chef_departement']);

// Traitement de la modification
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $date_debut = $_POST['date_debut'];
    $date_fin = $_POST['date_fin'];
    $type_conge = $_POST['type_conge'];
    $superviseur = $_POST['superviseur'];
    $superviseur_status = $_POST['superviseur_status'];
    $chef_departement = $_POST['chef_departement'];
    $chef_departement_status = $_POST['chef_departement_status'];
    $statut = $_POST['statut'];

    // Requête de mise à jour
    $update_query = "UPDATE demandes_conge 
                     SET date_debut = ?, date_fin = ?, type_conge = ?, superviseur = ?, superviseur_status=?,
                     chef_departement = ?, chef_departement_status=?, statut=?, archivage_time = ?
                     WHERE id_demande = ?";

    if ($stmt = mysqli_prepare($conn, $update_query)) {
        $archivage_time = ($statut == 'approuvé') ? date('Y-m-d H:i:s', strtotime('+1 day')) : NULL;
        mysqli_stmt_bind_param(
            $stmt,
            'sssssssssi',
            $date_debut,
            $date_fin,
            $type_conge,
            $superviseur,
            $superviseur_status,
            $chef_departement,
            $chef_departement_status,
            $statut,
            $archivage_time,
            $id_demande
        );
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['message'] = 'Demande de congé mise à jour avec succès.';

            // Ajouter une notification pour l'utilisateur
            if ($statut == 'approuvé') {
                // Récupérer l'email du membre associé à la demande de congé
                $email_query = "SELECT email_professionel FROM membres WHERE id_membre = ?";
                if ($email_stmt = mysqli_prepare($conn, $email_query)) {
                    mysqli_stmt_bind_param($email_stmt, 'i', $demande['id_membre']);
                    mysqli_stmt_execute($email_stmt);
                    $email_result = mysqli_stmt_get_result($email_stmt);
                    $email_row = mysqli_fetch_assoc($email_result);
                    $email_membre = $email_row['email_professionel'];
                    mysqli_stmt_close($email_stmt);
                }

                // Trouver l'utilisateur correspondant à cet email
                $user_query = "SELECT id_user FROM users WHERE email = ?";
                if ($user_stmt = mysqli_prepare($conn, $user_query)) {
                    mysqli_stmt_bind_param($user_stmt, 's', $email_membre);
                    mysqli_stmt_execute($user_stmt);
                    $user_result = mysqli_stmt_get_result($user_stmt);
                    $user_row = mysqli_fetch_assoc($user_result);
                    $user_id = $user_row['id_user'];
                    mysqli_stmt_close($user_stmt);
                }

                // Envoyer la notification à l'utilisateur trouvé
                if ($user_id) {
                    $notification_message = "Votre demande de congé a été approuvée.";
                    $notification_query = "INSERT INTO notifications (id_user, message) VALUES (?, ?)";
                    if ($notification_stmt = mysqli_prepare($conn, $notification_query)) {
                        mysqli_stmt_bind_param($notification_stmt, 'is', $user_id, $notification_message);
                        mysqli_stmt_execute($notification_stmt);
                        mysqli_stmt_close($notification_stmt);
                    }
                }

                // Mettre à jour le statut du membre associé
                $update_membre_query = "UPDATE membres SET status = 'en congé' WHERE id_membre = ?";
                if ($update_membre_stmt = mysqli_prepare($conn, $update_membre_query)) {
                    mysqli_stmt_bind_param($update_membre_stmt, 'i', $demande['id_membre']);
                    mysqli_stmt_execute($update_membre_stmt);
                    mysqli_stmt_close($update_membre_stmt);
                }
            }
        } else {
            $_SESSION['message'] = 'Erreur lors de la mise à jour de la demande de congé.';
        }
        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['message'] = 'Erreur lors de la préparation de la requête de mise à jour.';
    }

    mysqli_close($conn);
    header('Location: gestion_conge.php');
    exit();
}

// Récupération des superviseurs pour le formulaire
$sql_superviseurs = "SELECT id_user, username FROM users";
$result_superviseurs = $conn->query($sql_superviseurs);

// Récupération des chefs de département pour le formulaire
$sql_departement = "SELECT id_user, username FROM users";
$result_departement = $conn->query($sql_departement);

$pageTitle = "Editer Congé";
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>

<body>
    <!-- Menu -->
    <?php require "menu.php"; ?>
    <div class="container">
        <h2>Modifier Demande de Congé</h2>

        <!-- Affichage des messages -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-info">
                <?php echo htmlspecialchars($_SESSION['message']); ?>
                <?php unset($_SESSION['message']); ?>
            </div>
        <?php endif; ?>

        <!-- Formulaire de modification -->
        <form method="post">
            <div class="form-row">
                <div class="form-group col-md-3">
                    <label>Date de Début</label>
                    <input type="date" name="date_debut" class="form-control"
                        value="<?php echo htmlspecialchars($demande['date_debut']); ?>"
                        <?php echo (!$is_superviseur && !$is_chef_departement) ? 'readonly' : ''; ?> required>
                </div>
                <div class="form-group col-md-3">
                    <label>Date de Fin</label>
                    <input type="date" name="date_fin" class="form-control"
                        value="<?php echo htmlspecialchars($demande['date_fin']); ?>"
                        <?php echo (!$is_superviseur && !$is_chef_departement) ? 'readonly' : ''; ?> required>
                </div>
                <div class="form-group col-md-3">
                    <label>Type de Congé</label>
                    <select name="type_conge" class="form-control"
                        <?php echo (!$is_superviseur && !$is_chef_departement) ? 'disabled' : ''; ?> required>
                        <option value="annuel" <?php echo ($demande['type_conge'] == 'annuel') ? 'selected' : ''; ?>>Annuel</option>
                        <option value="maladie" <?php echo ($demande['type_conge'] == 'maladie') ? 'selected' : ''; ?>>Maladie</option>
                        <option value="autre" <?php echo ($demande['type_conge'] == 'autre') ? 'selected' : ''; ?>>Autre</option>
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label>Superviseur</label>
                    <select name="superviseur" class="form-control"
                        <?php echo (!$is_superviseur) ? 'disabled' : ''; ?> required>
                        <?php while ($row = $result_superviseurs->fetch_assoc()): ?>
                            <option value="<?php echo $row['id_user']; ?>"
                                <?php echo ($row['id_user'] == $demande['superviseur']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($row['username']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label>Status Superviseur</label>
                    <select name="superviseur_status" class="form-control"
                        <?php echo (!$is_superviseur) ? 'disabled' : ''; ?> required>
                        <option value="en attente" <?php echo ($demande['superviseur_status'] == 'en attente') ? 'selected' : ''; ?>>En attente</option>
                        <option value="accepté" <?php echo ($demande['superviseur_status'] == 'accepté') ? 'selected' : ''; ?>>Accepté</option>
                        <option value="refusé" <?php echo ($demande['superviseur_status'] == 'refusé') ? 'selected' : ''; ?>>Refusé</option>
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label>Chef de Département</label>
                    <select name="chef_departement" class="form-control"
                        <?php echo (!$is_chef_departement) ? 'disabled' : ''; ?> required>
                        <?php while ($row = $result_departement->fetch_assoc()): ?>
                            <option value="<?php echo $row['id_user']; ?>"
                                <?php echo ($row['id_user'] == $demande['chef_departement']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($row['username']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label>Status Chef de Département</label>
                    <select name="chef_departement_status" class="form-control"
                        <?php echo (!$is_chef_departement) ? 'disabled' : ''; ?> required>
                        <option value="en attente" <?php echo ($demande['chef_departement_status'] == 'en attente') ? 'selected' : ''; ?>>En attente</option>
                        <option value="accepté" <?php echo ($demande['chef_departement_status'] == 'accepté') ? 'selected' : ''; ?>>Accepté</option>
                        <option value="refusé" <?php echo ($demande['chef_departement_status'] == 'refusé') ? 'selected' : ''; ?>>Refusé</option>
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label>Statut</label>
                    <select name="statut" class="form-control" required>
                        <option value="en attente" <?php echo ($demande['statut'] == 'en attente') ? 'selected' : ''; ?>>En attente</option>
                        <option value="approuvé" <?php echo ($demande['statut'] == 'approuvé') ? 'selected' : ''; ?>>Approuvé</option>
                        <option value="refusé" <?php echo ($demande['statut'] == 'refusé') ? 'selected' : ''; ?>>Refusé</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Enregistrer les modifications</button>
        </form>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>