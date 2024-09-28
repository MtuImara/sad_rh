<?php
require "cache.php"; // Connexion à la base de données et session

// Vérifiez si l'utilisateur est connecté et a les droits nécessaires
if (!isset($_SESSION['id_user']) && $_SESSION['role'] != 'admin') {
    header("Location: login.php"); // Rediriger vers la page de connexion si non admin
    exit;
}

// Vérifier si l'ID du membre est passé en paramètre
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['message'] = 'ID invalide.';
    header("Location: gestion_membre.php");
    exit;
}

$id_membre = intval($_GET['id']);

// Mettre à jour la base de données pour archiver le membre
require "config/database.php"; // Inclure le fichier de connexion à la base de données

// Requête pour mettre fin au contrat du membre
$query = "UPDATE membres SET type_contrat = 'Archivé', date_fin_contrat = CURDATE() WHERE id_membre = $id_membre";
if (mysqli_query($conn, $query)) {
    $_SESSION['message'] = 'Membre archivé avec succès.';
    // Enregistrement dans l'historique
    $action = "Archivage d'un membre";
    $details = "Détails de la modification";
    enregistrerHistorique($conn, $user_id, $action, $details);
} else {
    $_SESSION['message'] = 'Erreur lors de l\'archivage du membre.';
}

mysqli_close($conn);

// Rediriger vers la page des membres archivés
header("Location: gestion_membre.php");
exit;
