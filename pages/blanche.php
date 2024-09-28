<?php
// Démarrer la session
require "cache.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

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

    // Récupérer l'email du demandeur à partir de l'id_user
    $user_id_demandeur = $demande['id_membre'];
    $user_query = "SELECT email FROM users WHERE id_user = ?";
    if ($user_stmt = mysqli_prepare($conn, $user_query)) {
        mysqli_stmt_bind_param($user_stmt, 'i', $user_id_demandeur);
        mysqli_stmt_execute($user_stmt);
        $user_result = mysqli_stmt_get_result($user_stmt);
        $user_row = mysqli_fetch_assoc($user_result);
        mysqli_stmt_close($user_stmt);

        if ($user_row) {
            $email_demandeur = $user_row['email'];
        } else {
            $email_demandeur = 'email@example.com'; // Valeur par défaut ou gestion d'erreur
        }
    } else {
        $email_demandeur = 'email@example.com'; // Valeur par défaut ou gestion d'erreur
    }
}

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
                     SET date_debut = ?, date_fin = ?, type_conge = ?, superviseur = ?, superviseur_status = ?,
                         chef_departement = ?, chef_departement_status = ?, statut = ?
                     WHERE id_demande = ?";

    if ($stmt = mysqli_prepare($conn, $update_query)) {
        mysqli_stmt_bind_param(
            $stmt,
            'ssssssssi',
            $date_debut,
            $date_fin,
            $type_conge,
            $superviseur,
            $superviseur_status,
            $chef_departement,
            $chef_departement_status,
            $statut,
            $id_demande
        );
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['message'] = 'Demande de congé mise à jour avec succès.';

            // Envoyer l'email de notification uniquement si le statut est "approuvé"
            if ($statut == 'approuvé') {
                // Créez une instance de PHPMailer
                $mail = new PHPMailer(true);

                try {
                    // Configuration du serveur SMTP
                    $mail->isSMTP();
                    $mail->Host = 'mail.netforafrica.net'; // Spécifiez votre serveur SMTP principal
                    $mail->SMTPAuth = true;
                    $mail->Username = 'echimanuka@tpordc.org'; // Votre adresse email SMTP
                    $mail->Password = 'Eddy@2023'; // Votre mot de passe SMTP
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 465; // Port SMTP

                    // Destinataire de l'email
                    $mail->setFrom('echimanuka@tpordc.org', 'TPO RDC');
                    $mail->addAddress($email_demandeur); // Utiliser l'email du demandeur

                    // Contenu de l'email
                    $mail->isHTML(true);
                    $mail->Subject = 'Statut de votre demande de congé';
                    $mail->Body    = 'Votre demande de congé a été <b>approuvée</b>.';

                    // Envoyer l'email
                    $mail->send();
                    $_SESSION['message'] .= ' Email de notification envoyé.';
                } catch (Exception $e) {
                    $_SESSION['message'] .= " L'email n'a pas pu être envoyé. Erreur de Mailer : {$mail->ErrorInfo}";
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
                        <option value="maternité" <?php echo ($demande['type_conge'] == 'maternité') ? 'selected' : ''; ?>>Maternité</option>
                        <option value="autre" <?php echo ($demande['type_conge'] == 'autre') ? 'selected' : ''; ?>>Autre</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label>Superviseur</label>
                    <select class="form-control" id="superviseur" name="superviseur"
                        <?php echo (!$is_superviseur && !$is_chef_departement) ? 'disabled' : ''; ?> required>
                        <?php while ($superviseur = mysqli_fetch_assoc($result_superviseurs)) : ?>
                            <option value="<?php echo $superviseur['id_user']; ?>"
                                <?php if ($demande['superviseur'] == $superviseur['id_user']) echo 'selected'; ?>>
                                <?php echo $superviseur['username']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label>Avis du superviseur</label>
                    <select name="superviseur_status" class="form-control"
                        <?php echo (!$is_superviseur && !$is_chef_departement) ? 'disabled' : ''; ?> required>
                        <option value="en attente" <?php echo ($demande['superviseur_status'] == 'en attente') ? 'selected' : ''; ?>>En attente</option>
                        <option value="approuvée" <?php echo ($demande['superviseur_status'] == 'approuvée') ? 'selected' : ''; ?>>Approuvée</option>
                        <option value="non approuvée" <?php echo ($demande['superviseur_status'] == 'non approuvée') ? 'selected' : ''; ?>>Non approuvée</option>
                    </select>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label>Chef de Département</label>
                    <select class="form-control" id="chef_departement" name="chef_departement"
                        <?php echo (!$is_superviseur && !$is_chef_departement) ? 'disabled' : ''; ?> required>
                        <?php while ($chef_departement = mysqli_fetch_assoc($result_departement)) : ?>
                            <option value="<?php echo $chef_departement['id_user']; ?>"
                                <?php if ($demande['chef_departement'] == $chef_departement['id_user']) echo 'selected'; ?>>
                                <?php echo $chef_departement['username']; ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group col-md-4">
                    <label>Avis du Chef du Département</label>
                    <select name="chef_departement_status" class="form-control"
                        <?php echo (!$is_superviseur && !$is_chef_departement) ? 'disabled' : ''; ?> required>
                        <option value="en attente" <?php echo ($demande['chef_departement_status'] == 'en attente') ? 'selected' : ''; ?>>En attente</option>
                        <option value="approuvée" <?php echo ($demande['chef_departement_status'] == 'approuvée') ? 'selected' : ''; ?>>Approuvée</option>
                        <option value="non approuvée" <?php echo ($demande['chef_departement_status'] == 'non approuvée') ? 'selected' : ''; ?>>Non approuvée</option>
                    </select>
                </div>
            </div>
            <?php if ($is_admin || $is_rh || $is_directeur): ?>
                <div class="form-row">
                    <div class="form-group col-md-8">
                        <label>Avis du Directeur</label>
                        <select name="statut" class="form-control" required>
                            <option value="en attente" <?php echo ($demande['statut'] == 'en attente') ? 'selected' : ''; ?>>En attente</option>
                            <option value="approuvé" <?php echo ($demande['statut'] == 'approuvé') ? 'selected' : ''; ?>>Approuvée</option>
                            <option value="non approuvé" <?php echo ($demande['statut'] == 'non approuvé') ? 'selected' : ''; ?>>Non approuvée</option>
                        </select>
                    </div>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <button type="submit" class="btn btn-primary"
                    <?php echo (!$is_superviseur && !$is_chef_departement) ? 'disabled' : ''; ?>>Enregistrer les modifications</button>
                <a href="gestion_conge.php" class="btn btn-secondary">Annuler</a>
            </div>

        </form>
    </div>
</body>

</html>