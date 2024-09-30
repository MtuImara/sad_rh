<?php
// Paramètres de connexion à la base de données
$host = 'junction.proxy.rlwy.net';
$username = 'root';
$password = 'QfovxIjUlrymmxQQLhGjoHZTRcnXqdDn';
$database = 'railway';
$port = "23784";
// $charset = 'utf8mb4';

// Connexion à MySQL
$conn = mysqli_connect($host, $username, $password, $database, $port);

// Vérifier la connexion
if (!$conn) {
    die("Échec de la connexion à la base de données : " . mysqli_connect_error());
}
