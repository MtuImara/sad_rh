<?php
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
$user_id = $_SESSION['id_user'];
function enregistrerHistorique($conn, $id_user, $action, $details)
{
    $stmt = $conn->prepare("INSERT INTO historique (id_user, action, details, date_action) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iss", $id_user, $action, $details);
    $stmt->execute();
    $stmt->close();
}
