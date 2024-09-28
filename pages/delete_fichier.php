<?php
require "cache.php";
$message = '';

// Vérifier si l'ID du fichier est passé en paramètre
if (isset($_GET['id'])) {
    $id_fichier = intval($_GET['id']);

    // Récupérer les informations du fichier à partir de la base de données
    $query = "SELECT * FROM fichiers WHERE id_fichier = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 'i', $id_fichier);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $fichier = mysqli_fetch_assoc($result);

    if ($fichier) {
        // Supprimer le fichier de la base de données
        $delete_query = "DELETE FROM fichiers WHERE id_fichier = ?";
        $delete_stmt = mysqli_prepare($conn, $delete_query);
        mysqli_stmt_bind_param($delete_stmt, 'i', $id_fichier);

        if (mysqli_stmt_execute($delete_stmt)) {
            $message = "Fichier supprimé avec succès.";
            // Enregistrement dans l'historique
            $action = "Suppression d'un fichier";
            $details = "Détails de la modification";
            enregistrerHistorique($conn, $user_id, $action, $details);
            // Supprimer le fichier physique s'il existe
            if (file_exists($fichier['chemin_fichier'])) {
                unlink($fichier['chemin_fichier']);
            }
        } else {
            $message = "Erreur lors de la suppression du fichier : " . htmlspecialchars(mysqli_stmt_error($delete_stmt));
        }

        mysqli_stmt_close($delete_stmt);
    } else {
        $message = "Fichier non trouvé.";
    }

    mysqli_stmt_close($stmt);
} else {
    header('Location: gestion_fichiers.php');
    exit();
}

// Fermer la connexion à la base de données
mysqli_close($conn);

// Redirection avec message
header('Location: gestion_fichiers.php?message=' . urlencode($message));
exit();
