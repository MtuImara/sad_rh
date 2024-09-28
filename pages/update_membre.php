<?php
session_start();

// Démarrer la session
session_start();

// Inclure le fichier de connexion à la base de données
include_once 'config/database.php';

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['id_user'])) {
    // Si l'utilisateur n'est pas connecté, rediriger vers la page de connexion
    header("Location: login.php");
    exit();
}

// En-têtes HTTP pour éviter le caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Récupération des données à mettre à jour
if (isset($_POST['id_membre'], $_POST['nouvelle_valeur'])) {
    $id_membre = $_POST['id_membre'];
    $nouvelle_valeur = $_POST['nouvelle_valeur'];
    $champ_modifie = $_POST['champ_modifie'];

    // Sélection de l'ancienne valeur
    $query_select = "SELECT $champ_modifie FROM membres WHERE id_membre = $id_membre";
    $result_select = mysqli_query($conn, $query_select);

    if ($result_select && mysqli_num_rows($result_select) > 0) {
        $ancienne_valeur = mysqli_fetch_assoc($result_select)[$champ_modifie];

        // Mise à jour du champ dans la table membres
        $query_update = "UPDATE membres SET $champ_modifie = '$nouvelle_valeur' WHERE id_membre = $id_membre";

        if (mysqli_query($conn, $query_update)) {
            // Enregistrement dans l'historique
            $id_utilisateur = $_SESSION['user_id'];
            $action = "Modification de $champ_modifie";
            $query_insert_historique = "INSERT INTO historique_membres (id_membre, id_utilisateur, action, champ_modifie, ancienne_valeur, nouvelle_valeur)
                                        VALUES ($id_membre, $id_utilisateur, '$action', '$champ_modifie', '$ancienne_valeur', '$nouvelle_valeur')";
            mysqli_query($conn, $query_insert_historique);

            $_SESSION['message'] = 'Membre mis à jour avec succès.';
        } else {
            $_SESSION['message'] = 'Erreur lors de la mise à jour du membre.';
        }
    } else {
        $_SESSION['message'] = 'Membre non trouvé.';
    }
} else {
    $_SESSION['message'] = 'Données manquantes pour la mise à jour du membre.';
}

// Fermer la connexion à la base de données
mysqli_close($conn);

// Redirection vers la page de gestion des membres
header('Location: gestion_membre.php');
exit();
