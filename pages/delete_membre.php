<?php
require "cache.php";

// Vérification de l'existence de l'ID du membre à supprimer dans l'URL
if (isset($_GET['id'])) {
    $id = mysqli_real_escape_string($conn, $_GET['id']);

    // Requête pour vérifier si le membre existe
    $check_membre_query = "SELECT * FROM membres WHERE id_membre = '$id'";
    $result_check_membre = mysqli_query($conn, $check_membre_query);

    if (mysqli_num_rows($result_check_membre) > 0) {
        // Récupérer l'ID du département avant la suppression
        $membre_data = mysqli_fetch_assoc($result_check_membre);
        $id_department = $membre_data['id_department'];

        // Requête pour supprimer le membre
        $delete_query = "DELETE FROM membres WHERE id_membre = '$id'";

        if (mysqli_query($conn, $delete_query)) {
            // Décrémenter l'effectif du département
            $update_department_query = "UPDATE department SET effectif = effectif - 1 WHERE id_department = '$id_department'";
            mysqli_query($conn, $update_department_query);

            $message = "Membre supprimé avec succès.";
            // Enregistrement dans l'historique
            $action = "Suppression d'un membre";
            $details = "ID du membre supprimé : $id";
            enregistrerHistorique($conn, $user_id, $action, $details);
        } else {
            $message = "Erreur lors de la suppression : " . mysqli_error($conn);
        }
    } else {
        $message = "Erreur : Le membre n'existe pas.";
    }

    // Redirection après traitement
    header("Location: gestion_membre.php?message=" . urlencode($message));
    exit();
} else {
    // Redirection si l'ID du membre n'est pas fourni
    header("Location: gestion_membre.php?message=" . urlencode("Erreur : Aucun ID de membre fourni."));
    exit();
}

// Fermer la connexion à la base de données
mysqli_close($conn);
