<?php
// Paramètres de connexion à la base de données
$host = 'localhost';
$username = 'root';
$password = 'Asyst@123';
$database = 'final_tpo';

// Connexion à MySQL
$conn = mysqli_connect($host, $username, $password, $database);

// Vérifier la connexion
if (!$conn) {
    die("Échec de la connexion à la base de données : " . mysqli_connect_error());
}
