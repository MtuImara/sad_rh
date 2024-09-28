<?php
// Inclusion du fichier de connexion à la base de données

require "cache.php";

// Vérification si l'ID du projet est passé en paramètre
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id_projet = $_GET['id'];

    // Vérification si l'utilisateur est connecté et a le rôle d'administrateur
    if (isset($_SESSION['id_user'])) {
        // Requête SQL pour supprimer le projet
        $query_delete = "DELETE FROM projects WHERE id_project = ?";
        $stmt_delete = mysqli_prepare($conn, $query_delete);
        mysqli_stmt_bind_param($stmt_delete, "i", $id_projet);

        // Exécution de la requête de suppression
        if (mysqli_stmt_execute($stmt_delete)) {
            $_SESSION['message'] = "Le projet a été supprimé avec succès.";
            // Enregistrement dans l'historique
            $action = "Suppression d'un projet";
            $details = "Détails de la modification";
            enregistrerHistorique($conn, $user_id, $action, $details);
        } else {
            $_SESSION['message'] = "Erreur lors de la suppression du projet : " . mysqli_error($conn);
        }

        // Fermeture de la requête préparée
        mysqli_stmt_close($stmt_delete);
    } else {
        $_SESSION['message'] = "Erreur : Vous n'avez pas les droits nécessaires pour supprimer ce projet.";
    }
} else {
    $_SESSION['message'] = "Erreur : ID du projet non spécifié.";
}

// Redirection vers la page de gestion des projets
header('Location: gestion_projets.php');
exit;

// Fermeture de la connexion à la base de données
mysqli_close($conn);
