<?php
// login_process.php

// Inclusion du fichier de configuration de la base de données
require_once 'config/database.php';

// Date d'aujourd'hui
$today = new DateTime();

// Sélectionner les membres dont le contrat a expiré
$query = "SELECT * FROM membres WHERE date_fin_contrat <= CURDATE()";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        // Insertion des membres expirés dans la table 'anciens_membre'
        $archive_query = "INSERT INTO anciens_membre 
            (nom, prenom, matricule, email_professionel, genre, numero_telephone, type_contrat, date_debut_contrat, date_fin_contrat, id_department)
            VALUES 
            ('" . mysqli_real_escape_string($conn, $row['nom']) . "',
             '" . mysqli_real_escape_string($conn, $row['prenom']) . "',
             '" . mysqli_real_escape_string($conn, $row['matricule']) . "',
             '" . mysqli_real_escape_string($conn, $row['email_professionel']) . "',
             '" . mysqli_real_escape_string($conn, $row['genre']) . "',
             '" . mysqli_real_escape_string($conn, $row['numero_telephone']) . "',
             '" . mysqli_real_escape_string($conn, $row['type_contrat']) . "',
             '" . mysqli_real_escape_string($conn, $row['date_debut_contrat']) . "',
             '" . mysqli_real_escape_string($conn, $row['date_fin_contrat']) . "',
             '" . (int)$row['id_department'] . "')";

        if (mysqli_query($conn, $archive_query)) {
            // Suppression du membre de la table 'membres'
            $delete_query = "DELETE FROM membres WHERE id_membre = " . (int)$row['id_membre'];
            mysqli_query($conn, $delete_query);
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Requête SQL pour récupérer le mot de passe hashé et le rôle de l'utilisateur par email
    $sql = "SELECT `id_user`, `password`, `role` FROM `users` WHERE `email` = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $id_user, $hashed_password, $role);

    // Vérification du mot de passe hashé avec password_verify
    if (mysqli_stmt_fetch($stmt)) {
        if (password_verify($password, $hashed_password)) {
            // Authentification réussie, démarrer la session
            session_start();
            $_SESSION['id_user'] = $id_user;
            $_SESSION['email'] = $email;
            $_SESSION['role'] = $role; // Stocker le rôle dans la session

            // Redirection en fonction du rôle
            if ($role === 'admin' || 'rh') {
                header("Location: dashboard.php");
            } elseif ($role === 'user') {
                header("Location: profil.php");
            } else {
                header("Location: index.php"); // Redirection par défaut si le rôle n'est pas reconnu
            }
            exit();
        }
    }

    // Échec de l'authentification, rediriger vers la page de connexion avec un message d'erreur
    header("Location: login.php?error=auth_failed");
    exit();
} else {
    // Redirection vers la page de connexion si la méthode n'est pas POST
    header("Location: login.php");
    exit();
}
