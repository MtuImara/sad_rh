<?php
// Inclure les informations de connexion à la base de données
require "cache.php";

// Script pour archiver les demandes de congé approuvées
$archive_query = "
    INSERT INTO archive_conge (SELECT * FROM demandes_conge WHERE archivage_time <= NOW());
    DELETE FROM demandes_conge WHERE archivage_time <= NOW();
";

if (mysqli_query($conn, $archive_query)) {
    echo "Archivage des demandes terminé.";
} else {
    echo "Erreur lors de l'archivage des demandes.";
}

// Fermer la connexion
mysqli_close($conn);
