<?php
require "cache.php";
$id_demande = $_GET['id_demande'];
// Vérifier le rôle de l'utilisateur pour déterminer s'il a les droits nécessaires
$user_id = $_SESSION['id_user'];
$user_query = "SELECT role, email, username FROM users WHERE id_user = $user_id";
$user_result = mysqli_query($conn, $user_query);
$role = '';
$email = '';

if ($user_row = mysqli_fetch_assoc($user_result)) {
    $role = $user_row['role'];
    $email = $user_row['email'];
}
// Récupérer toutes les demandes de congé avec les détails du superviseur et du chef de département désignés
$demande_query = "
    SELECT d.id_demande, 
           m.nom, 
           m.prenom, 
           d.date_debut, 
           d.date_fin, 
           d.type_conge, 
           d.date_demande, 
           d.statut,
           (SELECT COUNT(*)
            FROM (
                SELECT ADDDATE(d.date_debut, INTERVAL t4.i*10 + t3.i DAY) as calc_date
                FROM 
                    (SELECT 0 i UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) t3,
                    (SELECT 0 i UNION ALL SELECT 1 UNION ALL SELECT 2 UNION ALL SELECT 3 UNION ALL SELECT 4 UNION ALL SELECT 5 UNION ALL SELECT 6 UNION ALL SELECT 7 UNION ALL SELECT 8 UNION ALL SELECT 9) t4
                WHERE ADDDATE(d.date_debut, INTERVAL t4.i*10 + t3.i DAY) BETWEEN d.date_debut AND d.date_fin
            ) calc_dates
            WHERE DAYOFWEEK(calc_dates.calc_date) NOT IN (1, 7) -- 1 = Dimanche, 7 = Samedi
           ) AS jours_pris,
           u.username AS superviseur_nom,
           c.username AS chef_departement_nom
    FROM demandes_conge d
    JOIN membres m ON d.id_membre = m.id_membre
    LEFT JOIN users u ON d.superviseur = u.id_user
    LEFT JOIN users c ON d.chef_departement = c.id_user
";
// Récupérer la demande de congé
$query = "SELECT * FROM demandes_conge WHERE id_demande = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_demande);
$stmt->execute();
$demande = $stmt->get_result()->fetch_assoc();

if ($demande['superviseur_nom'] == $user_row['username']) {
    if ($demande['superviseur_status'] == 'en attente') {
        $query = "UPDATE demandes_conge SET superviseur_status = 'approuvé' WHERE id_demande = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id_demande);
        $stmt->execute();
    }
} elseif ($demande['chef_departement_nom'] == $user_row['username']) {
    if ($demande['superviseur_status'] == 'approuvé' && $demande['chef_departement_status'] == 'en attente') {
        $query = "UPDATE demandes_conge SET chef_departement_status = 'accepté' WHERE id_demande = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id_demande);
        $stmt->execute();
    }
} elseif ($role == 'directeur') {
    if ($demande['superviseur_status'] == 'approuvé' && $demande['chef_departement_status'] == 'approuvé' && $demande['statut'] == 'en attente') {
        $query = "UPDATE demandes_conge SET statut = 'approuvé' WHERE id_demande = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id_demande);
        $stmt->execute();
    }
}
// Vérifiez si le statut de la demande est 'approuvé'
if ($demande['statut'] == 'approuvé') {
    // Mettez à jour le statut du membre
    $update_query = "UPDATE membres SET status = 'en congé' WHERE id_membre = ?";
    $update_stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($update_stmt, 'i', $demande['id_membre']);
    mysqli_stmt_execute($update_stmt);

    if (mysqli_stmt_affected_rows($update_stmt) > 0) {
        echo "Le statut du membre a été mis à jour avec succès.";
    } else {
        echo "Erreur lors de la mise à jour du statut du membre.";
    }

    mysqli_stmt_close($update_stmt);
}
header('Location: gestion_conge.php'); // Redirection vers la page des demandes
