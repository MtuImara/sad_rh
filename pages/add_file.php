<?php
// Démarrer la session
require "cache.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_membre = $_POST['id_membre'];
    $nom_fichier = $_FILES['fichier']['name'];
    $type_fichier = $_FILES['fichier']['type'];
    $chemin_fichier = 'uploads/' . basename($_FILES['fichier']['name']);
    $date_ajout = date("Y-m-d H:i:s");

    // Déplacer le fichier téléchargé dans le dossier uploads
    if (move_uploaded_file($_FILES['fichier']['tmp_name'], $chemin_fichier)) {
        // Préparation de la requête SQL
        $sql = "INSERT INTO fichiers (id_membre, nom_fichier, type_fichier, chemin_fichier, date_ajout) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);

        // Vérification de la préparation de la requête
        if ($stmt === false) {
            die('Erreur de préparation de la requête : ' . htmlspecialchars(mysqli_error($conn)));
        }

        // Liaison des paramètres
        mysqli_stmt_bind_param($stmt, 'issss', $id_membre, $nom_fichier, $type_fichier, $chemin_fichier, $date_ajout);

        // Exécution de la requête
        if (mysqli_stmt_execute($stmt)) {
            echo "Fichier ajouté avec succès.";
            // Enregistrement dans l'historique
            $action = "Ajout d'un nouveau";
            $details = "Fichier $nom_fichier avec succès.";
            enregistrerHistorique($conn, $user_id, $action, $details);
            // Redirection après traitement
            header('Location: gestion_fichiers.php');
            exit();
        } else {
            echo "Erreur lors de l'ajout du fichier : " . htmlspecialchars(mysqli_stmt_error($stmt));
        }

        // Fermeture de la requête
        mysqli_stmt_close($stmt);
    } else {
        echo "Erreur lors du téléchargement du fichier.";
    }

    // Fermeture de la connexion
    mysqli_close($conn);
}
