<?php
session_start();

// Vérifiez si l'utilisateur est connecté et a le rôle "it"
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'it') {
    header('Location: login.php');
    exit();
}

// Inclusion du fichier de connexion à la base de données
include_once 'config.php';

// Récupérer les informations de l'utilisateur connecté
$user_id = $_SESSION['user_id'];
$user_query = "SELECT role FROM membres WHERE id_membre = $user_id";
$user_result = mysqli_query($conn, $user_query);
$user_role = '';
if ($user_row = mysqli_fetch_assoc($user_result)) {
    $user_role = $user_row['role'];
}
// Récupérer tous les enregistrements de l'historique des membres
$query = "SELECT h.id_historique, h.id_membre, h.id_utilisateur, h.action, h.champ_modifie, h.ancienne_valeur, h.nouvelle_valeur, h.date_modification, m.nom, m.prenom 
          FROM historique_membres h
          JOIN membres m ON h.id_membre = m.id_membre
          ORDER BY h.date_modification DESC";
$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Historique des modifications des membres</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }

        table,
        th,
        td {
            border: 1px solid black;
        }

        th,
        td {
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>

<body>
    <h1>Historique des modifications des membres</h1>
    <h2>Historique des modifications</h2>
    <table border="1">
        <thead>
            <tr>
                <th>Champ modifié</th>
                <th>Ancienne valeur</th>
                <th>Nouvelle valeur</th>
                <th>Date de modification</th>
                <th>Modifié par</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // Inclusion du fichier de connexion à la base de données
            include_once 'config.php';

            // Récupérer l'historique des modifications
            $query_historique = "SELECT * FROM historique_membres WHERE id_membre = $id_membre ORDER BY date_modification DESC";
            $result_historique = mysqli_query($conn, $query_historique);

            if (mysqli_num_rows($result_historique) > 0) {
                while ($historique = mysqli_fetch_assoc($result_historique)) {
                    $utilisateur_query = "SELECT prenom, nom FROM membres WHERE id_membre = " . $historique['id_utilisateur'];
                    $utilisateur_result = mysqli_query($conn, $utilisateur_query);
                    $utilisateur = mysqli_fetch_assoc($utilisateur_result);
                    echo "<tr>
                            <td>{$historique['champ_modifie']}</td>
                            <td>{$historique['ancienne_valeur']}</td>
                            <td>{$historique['nouvelle_valeur']}</td>
                            <td>{$historique['date_modification']}</td>
                            <td>{$utilisateur['prenom']} {$utilisateur['nom']}</td>
                          </tr>";
                }
            } else {
                echo "<tr><td colspan='5'>Aucune modification trouvée.</td></tr>";
            }

            mysqli_close($conn);
            ?>
        </tbody>
    </table>
</body>

</html>

<?php
$conn->close();
?>