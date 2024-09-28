<?php
// Démarrer la session
require "cache.php";

// Vérifier que l'utilisateur est connecté et a les droits nécessaires
if (!isset($_SESSION['id_user']) || ($_SESSION['role'] != 'admin' && $_SESSION['role'] != 'rh')) {
    header('Location: login.php'); // Rediriger vers la page de connexion ou une page d'erreur
    exit();
}

// Vérifier si l'identifiant de la demande est passé en paramètre
if (isset($_GET['id_demande']) && is_numeric($_GET['id_demande'])) {
    $id_demande = intval($_GET['id_demande']);

    // Préparer et exécuter la requête de suppression
    $delete_query = "DELETE FROM demandes_conge WHERE id_demande = ?";
    if ($stmt = mysqli_prepare($conn, $delete_query)) {
        mysqli_stmt_bind_param($stmt, 'i', $id_demande);
        if (mysqli_stmt_execute($stmt)) {
            // Suppression réussie
            $_SESSION['message'] = 'Demande de congé supprimée avec succès.';
        } else {
            // Erreur lors de la suppression
            $_SESSION['message'] = 'Erreur lors de la suppression de la demande de congé.';
        }
        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['message'] = 'Erreur lors de la préparation de la requête de suppression.';
    }
} else {
    $_SESSION['message'] = 'Identifiant de demande invalide.';
}

// Fermer la connexion à la base de données
mysqli_close($conn);

// Rediriger vers la page de gestion des congés avec un message
header('Location: gestion_conge.php');
exit();
