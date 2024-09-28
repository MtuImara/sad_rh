<?php
require 'cache.php'; // Assurez-vous que le fichier cache.php est bien inclus pour la connexion à la base de données.

function logError($message)
{
    $logfile = 'error_log.txt'; // Assurez-vous que le fichier de log est accessible en écriture
    file_put_contents($logfile, date('Y-m-d H:i:s') . " - " . $message . PHP_EOL, FILE_APPEND);
}

if (isset($_POST['upload']) && isset($_FILES['file'])) {
    // Vérifier si le fichier est bien uploadé
    if ($_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['file']['tmp_name'];

        // Ouvrir le fichier CSV
        if (($handle = fopen($fileTmpPath, 'r')) !== FALSE) {
            // Lire les lignes du fichier CSV
            while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
                $date = $data[0];
                $noms = $data[1];
                $heure_entree = $data[2];
                $heure_sortie = $data[3];

                // Validation simple
                if (empty($date) || empty($noms) || empty($heure_entree) || empty($heure_sortie)) {
                    logError("Données manquantes dans le fichier CSV: " . implode(',', $data));
                    continue;
                }

                // Rechercher l'ID du membre par son nom
                $query = "SELECT id_membre FROM membres WHERE CONCAT(nom, ' ', prenom) = ?";
                if ($stmt = mysqli_prepare($conn, $query)) {
                    mysqli_stmt_bind_param($stmt, 's', $noms);
                    mysqli_stmt_execute($stmt);
                    mysqli_stmt_bind_result($stmt, $id_membre);
                    mysqli_stmt_fetch($stmt);
                    mysqli_stmt_close($stmt);

                    // Vérifier si un membre avec ce nom existe
                    if ($id_membre) {
                        // Préparer et exécuter la requête d'insertion
                        $insert_query = "INSERT INTO presences (id_membre, date, heure_entree, heure_sortie) VALUES (?, ?, ?, ?)";
                        if ($insert_stmt = mysqli_prepare($conn, $insert_query)) {
                            mysqli_stmt_bind_param($insert_stmt, 'isss', $id_membre, $date, $heure_entree, $heure_sortie);
                            mysqli_stmt_execute($insert_stmt);
                            mysqli_stmt_close($insert_stmt);
                        } else {
                            logError("Erreur de préparation de la requête d'insertion.");
                        }
                    } else {
                        logError("Aucun membre trouvé pour le nom: " . $noms);
                    }
                } else {
                    logError("Erreur de préparation de la requête de recherche du membre.");
                }
            }
            fclose($handle);
            echo "Importation réussie.";
            header('Location: gestion_presence.php');
            exit();
        } else {
            logError("Erreur lors de l'ouverture du fichier.");
            echo "Erreur lors de l'ouverture du fichier.";
        }
    } else {
        logError("Erreur lors de l'upload du fichier.");
        echo "Erreur lors de l'upload du fichier.";
    }
} else {
    echo "Aucun fichier sélectionné.";
}

mysqli_close($conn);
?>


<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Importation de Présence</title>
</head>

<body>
    <h1>Importation de Présence</h1>
    <form action="add_presence.php" method="post" enctype="multipart/form-data">
        <label for="file">Choisissez un fichier CSV :</label>
        <input type="file" name="file" id="file" accept=".csv">
        <button type="submit" name="upload">Importer</button>
    </form>
</body>

</html>