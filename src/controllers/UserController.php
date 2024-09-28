<?php
// Inclusion du fichier de configuration de la base de données
require_once 'config/database.php';

class UserController
{

    // Méthode pour enregistrer un nouvel utilisateur
    public function registerUser($username, $email, $password, $role = 'user')
    {
        global $conn;

        // Hashage du mot de passe
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Préparation de la requête SQL d'insertion
        $sql = "INSERT INTO `users` (`username`, `email`, `password`, `role`) 
                VALUES (?, ?, ?, ?)";

        // Utilisation des déclarations préparées pour éviter les injections SQL
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssss", $username, $email, $hashed_password, $role);

        // Exécution de la requête
        if (mysqli_stmt_execute($stmt)) {
            return true; // Enregistrement réussi
        } else {
            return false; // Échec de l'enregistrement
        }
    }

    // Méthode pour vérifier les informations de connexion de l'utilisateur
    public function authenticateUser($email, $password)
    {
        global $conn;

        // Récupération du mot de passe hashé de la base de données pour l'email donné
        $sql = "SELECT `id_user`, `password` FROM `users` WHERE `email` = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $id_user, $hashed_password);

        // Vérification du mot de passe hashé avec password_verify
        if (mysqli_stmt_fetch($stmt)) {
            if (password_verify($password, $hashed_password)) {
                return $id_user; // Authentification réussie, retourne l'id de l'utilisateur
            }
        }

        return false; // Échec de l'authentification
    }

    // Méthode pour obtenir les informations d'un utilisateur par son ID
    public function getUserById($id_user)
    {
        global $conn;

        $sql = "SELECT `id_user`, `username`, `email`, `role`, `created_at` FROM `users` WHERE `id_user` = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $id_user);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $id_user, $username, $email, $role, $created_at);

        // Récupération des résultats
        if (mysqli_stmt_fetch($stmt)) {
            $user = [
                'id_user' => $id_user,
                'username' => $username,
                'email' => $email,
                'role' => $role,
                'created_at' => $created_at
            ];
            return $user;
        } else {
            return null; // Aucun utilisateur trouvé
        }
    }
}
