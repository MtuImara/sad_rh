<?php
require "cache.php";

// Vérification de l'existence de l'ID du département à supprimer dans l'URL
if (isset($_GET['id'])) {
    $id_department = mysqli_real_escape_string($conn, $_GET['id']);

    // Requête pour vérifier si le département existe et récupérer son effectif
    $check_department_query = "SELECT * FROM department WHERE id_department = '$id_department'";
    $result_check_department = mysqli_query($conn, $check_department_query);

    if (mysqli_num_rows($result_check_department) > 0) {
        $department_data = mysqli_fetch_assoc($result_check_department);
        $effectif = $department_data['effectif'];

        // Vérification si l'effectif est supérieur à 0
        if ($effectif > 0) {
            $message = "Erreur : Impossible de supprimer un département qui contient des membres.";
            // Redirection après traitement
            header("Location: gestion_departement.php?message=" . urlencode($message));
            exit();
        } else {
            // Requête pour supprimer le département
            $delete_query = "DELETE FROM department WHERE id_department = '$id_department'";

            if (mysqli_query($conn, $delete_query)) {
                $message = "Département supprimé avec succès.";
                header("Location: gestion_departement.php?");
                exit();
                // Enregistrement dans l'historique
                $action = "Suppression d'un département";
                $details = "ID du département supprimé : $id_department";
                enregistrerHistorique($conn, $user_id, $action, $details);
            } else {
                $message = "Erreur lors de la suppression : " . mysqli_error($conn);
            }
        }
    } else {
        $message = "Erreur : Le département n'existe pas.";
    }

    // Redirection après traitement
    header("Location: gestion_department.php?message=" . urlencode($message));
    exit();
} else {
    // Redirection si l'ID du département n'est pas fourni
    header("Location: gestion_department.php?message=" . urlencode("Erreur : Aucun ID de département fourni."));
    exit();
}

// Fermer la connexion à la base de données
mysqli_close($conn);
