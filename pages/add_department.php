<?php
// Démarrer la session
require "cache.php";

// Variable pour afficher les messages
$message = '';

// Traitement du formulaire lors de la soumission POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Nettoyage et validation des données reçues du formulaire
    $nom_department = mysqli_real_escape_string($conn, $_POST['nom_department']);
    $description_department = mysqli_real_escape_string($conn, $_POST['description_department']);

    // Vérifier si le nom du département est déjà utilisé
    $check_query = "SELECT id_department FROM department WHERE nom_department = '$nom_department'";
    $check_result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        $message = "Erreur : Le nom du département '$nom_department' existe déjà.";
    } else {
        // Requête SQL pour insérer un nouveau département
        $insert_query = "INSERT INTO department (nom_department, description_department) VALUES ('$nom_department', '$description_department')";

        // Exécution de la requête
        if (mysqli_query($conn, $insert_query)) {
            $message = "Le département a été ajouté avec succès.";
            // Enregistrement dans l'historique
            $action = "Création d'un département";
            $details = "On vient d'ajouter un nouveau département: $nom_department";
            enregistrerHistorique($conn, $user_id, $action, $details);
        } else {
            $message = "Erreur : " . mysqli_error($conn);
        }
    }

    // Redirection après traitement
    header('Location: gestion_departement.php');
    exit();
}

// Fermeture de la connexion à la base de données
mysqli_close($conn);
