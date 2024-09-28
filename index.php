<?php
// Démarrer la session
session_start();

// Vérifier si l'utilisateur est connecté en vérifiant la variable de session
if (!isset($_SESSION['username'])) {
    // Redirection vers la page de connexion
    header("Location: pages/login.php");
    exit(); // Assure la fin de l'exécution du script après la redirection
}

// Si l'utilisateur est connecté, tu peux continuer avec le contenu de index.php

// Exemple de contenu de la page protégée après la connexion
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accueil</title>
    <!-- Lien vers votre fichier de styles CSS -->
    <link rel="stylesheet" href="styles.css">
</head>

<body>
    <div class="container">
        <h1>Bienvenue sur l'application</h1>
        <p>Contenu protégé après connexion.</p>
        <!-- Ajoutez ici le contenu de votre application -->
    </div>
</body>

</html>