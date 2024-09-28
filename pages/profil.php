<?php
// profil.php

// Inclusion du fichier de configuration de la base de données
require_once 'config/database.php';

// Démarrer la session
session_start();

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit();
}

// Récupérer l'ID de l'utilisateur depuis la session
$id_user = $_SESSION['id_user'];

// Requête SQL pour récupérer les informations de l'utilisateur
$sql = "SELECT `email`, `role`, `username`, `password`, `profile_picture` FROM `users` WHERE `id_user` = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id_user);
mysqli_stmt_execute($stmt);
mysqli_stmt_bind_result($stmt, $email, $role, $name, $hashed_password, $profile_picture);
mysqli_stmt_fetch($stmt);
mysqli_stmt_close($stmt);

// Vérifier si le formulaire de mise à jour du mot de passe a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Vérifier le mot de passe actuel
    if (password_verify($current_password, $hashed_password)) {
        // Vérifier que le nouveau mot de passe et la confirmation sont identiques
        if ($new_password === $confirm_password) {
            // Hacher le nouveau mot de passe
            $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            // Mettre à jour le mot de passe dans la base de données
            $update_sql = "UPDATE `users` SET `password` = ? WHERE `id_user` = ?";
            $update_stmt = mysqli_prepare($conn, $update_sql);
            mysqli_stmt_bind_param($update_stmt, "si", $new_hashed_password, $id_user);
            if (mysqli_stmt_execute($update_stmt)) {
                $message = "Mot de passe mis à jour avec succès.";
            } else {
                $message = "Erreur lors de la mise à jour du mot de passe.";
            }
            mysqli_stmt_close($update_stmt);
        } else {
            $message = "Le nouveau mot de passe et la confirmation ne correspondent pas.";
        }
    } else {
        $message = "Mot de passe actuel incorrect.";
    }
}

// Vérifier si le formulaire de mise à jour de la photo de profil a été soumis
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_picture'])) {
    $file = $_FILES['profile_picture'];
    $upload_dir = 'uploads/';
    $upload_file = $upload_dir . basename($file['name']);
    $upload_ok = 1;

    // Vérifier le type de fichier
    $image_file_type = strtolower(pathinfo($upload_file, PATHINFO_EXTENSION));
    if ($file['size'] > 500000) {
        $message = "Le fichier est trop volumineux.";
        $upload_ok = 0;
    }
    if ($image_file_type != 'jpg' && $image_file_type != 'png' && $image_file_type != 'jpeg' && $image_file_type != 'gif') {
        $message = "Seuls les fichiers JPG, JPEG, PNG et GIF sont autorisés.";
        $upload_ok = 0;
    }
    if ($upload_ok == 0) {
        $message = "Le fichier n'a pas été téléchargé.";
    } else {
        if (move_uploaded_file($file['tmp_name'], $upload_file)) {
            // Mettre à jour le chemin de la photo de profil dans la base de données
            $update_picture_sql = "UPDATE `users` SET `profile_picture` = ? WHERE `id_user` = ?";
            $update_picture_stmt = mysqli_prepare($conn, $update_picture_sql);
            mysqli_stmt_bind_param($update_picture_stmt, "si", $upload_file, $id_user);
            if (mysqli_stmt_execute($update_picture_stmt)) {
                $message = "Photo de profil mise à jour avec succès.";
                $profile_picture = $upload_file; // Mettre à jour la photo affichée
            } else {
                $message = "Erreur lors de la mise à jour de la photo de profil.";
            }
            mysqli_stmt_close($update_picture_stmt);
        } else {
            $message = "Erreur lors du téléchargement du fichier.";
        }
    }
}
// Supposons que l'adresse email de l'utilisateur connecté est stockée dans la session
$email_utilisateur = $_SESSION['email']; // Ou récupérer depuis la base de données
// Fermer la connexion à la base de données

$pageTitle = "Mon profil";
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <!-- Lien vers votre fichier de styles CSS -->
    <link rel="stylesheet" href="styles.css">
    <!-- Intégration de Bootstrap CSS pour le style -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <!-- Menu -->
    <?php require "menu.php" ?>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">

                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h2 class="text-center">Profil de l'utilisateur</h2>
                    </div>
                    <div class="card-header">
                        <form action="membre_info.php" method="GET">
                            <input type="hidden" name="email" value="<?php echo htmlspecialchars($email_utilisateur); ?>">
                            <button type="submit" class="btn btn-success">Consulter mes informations</button>
                        </form>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center">
                            <div class="mr-3">
                                <?php if ($profile_picture): ?>
                                    <img src="<?php echo htmlspecialchars($profile_picture); ?>" alt="Photo de profil" class="img-thumbnail" style="width: 100px; height: 100px;">
                                <?php else: ?>
                                    <img src="default-profile.jpg" alt="Photo de profil" class="img-thumbnail" style="width: 100px; height: 100px;">
                                <?php endif; ?>
                            </div>
                            <div>
                                <p><strong>Nom :</strong> <?php echo htmlspecialchars($name); ?></p>
                                <p><strong>Email :</strong> <?php echo htmlspecialchars($email); ?></p>
                                <p><strong>Rôle :</strong> <?php echo htmlspecialchars($role); ?></p>
                            </div>
                        </div>

                        <!-- Formulaire pour la mise à jour du mot de passe -->
                        <form action="profil.php" method="POST">
                            <div class="form-group">
                                <label for="current_password">Mot de passe actuel :</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="button" id="toggleCurrentPassword">
                                            <i class="fa fa-eye" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="new_password">Nouveau mot de passe :</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="button" id="toggleNewPassword">
                                            <i class="fa fa-eye" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="confirm_password">Confirmer le nouveau mot de passe :</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                    <div class="input-group-append">
                                        <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                            <i class="fa fa-eye" aria-hidden="true"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" name="update_password" class="btn btn-primary">Mettre à jour le mot de passe</button>
                        </form>

                        <!-- Formulaire pour la mise à jour de la photo de profil -->
                        <form action="profil.php" method="POST" enctype="multipart/form-data" class="mt-4">
                            <div class="form-group">
                                <label for="profile_picture">Changer la photo de profil :</label>
                                <input type="file" class="form-control-file" id="profile_picture" name="profile_picture" accept="image/*">
                            </div>
                            <button type="submit" name="update_picture" class="btn btn-primary">Mettre à jour la photo</button>
                        </form>

                        <!-- Message de confirmation ou d'erreur -->
                        <?php if (isset($message)): ?>
                            <div class="alert alert-info mt-3">
                                <?php echo htmlspecialchars($message); ?>
                            </div>
                        <?php endif; ?>

                        <a href="logout.php" class="btn btn-danger mt-3">Se déconnecter</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Intégration de Bootstrap JS (optionnel) -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

    <!-- Intégration de Font Awesome pour les icônes -->
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>

    <!-- Script JavaScript pour afficher/masquer les mots de passe -->
    <script>
        document.getElementById('toggleCurrentPassword').addEventListener('click', function() {
            var passwordField = document.getElementById('current_password');
            var fieldType = passwordField.getAttribute('type');
            if (fieldType === 'password') {
                passwordField.setAttribute('type', 'text');
                this.innerHTML = '<i class="fa fa-eye-slash" aria-hidden="true"></i>';
            } else {
                passwordField.setAttribute('type', 'password');
                this.innerHTML = '<i class="fa fa-eye" aria-hidden="true"></i>';
            }
        });

        document.getElementById('toggleNewPassword').addEventListener('click', function() {
            var passwordField = document.getElementById('new_password');
            var fieldType = passwordField.getAttribute('type');
            if (fieldType === 'password') {
                passwordField.setAttribute('type', 'text');
                this.innerHTML = '<i class="fa fa-eye-slash" aria-hidden="true"></i>';
            } else {
                passwordField.setAttribute('type', 'password');
                this.innerHTML = '<i class="fa fa-eye" aria-hidden="true"></i>';
            }
        });

        document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
            var passwordField = document.getElementById('confirm_password');
            var fieldType = passwordField.getAttribute('type');
            if (fieldType === 'password') {
                passwordField.setAttribute('type', 'text');
                this.innerHTML = '<i class="fa fa-eye-slash" aria-hidden="true"></i>';
            } else {
                passwordField.setAttribute('type', 'password');
                this.innerHTML = '<i class="fa fa-eye" aria-hidden="true"></i>';
            }
        });
    </script>
</body>

</html>