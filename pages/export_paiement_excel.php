<?php
// Inclusion du fichier de connexion à la base de données
include_once 'config/database.php';

// Inclusion de la bibliothèque PhpSpreadsheet
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

if (isset($_GET['mois'])) {
    $moisPaiement = $_GET['mois'];

    // Récupération des détails du paiement pour le mois spécifié
    $query = "SELECT m.nom, m.prenom, m.postnom, m.num_compte_banque, p.salaire_paye
              FROM paiement p
              JOIN membres m ON p.id_membre = m.id_membre
              WHERE p.mois_paiement = '$moisPaiement'";
    $result = mysqli_query($conn, $query);

    // Création d'une nouvelle feuille de calcul
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Ajout des en-têtes de colonnes
    $sheet->setCellValue('A1', 'Nom');
    $sheet->setCellValue('B1', 'Prénom');
    $sheet->setCellValue('C1', 'Postnom');
    $sheet->setCellValue('D1', 'Montant payé');
    $sheet->setCellValue('E1', 'N° Compte');

    // Ajout des données du tableau
    $rowNumber = 2;
    while ($row = mysqli_fetch_assoc($result)) {
        $sheet->setCellValue('A' . $rowNumber, $row['nom']);
        $sheet->setCellValue('B' . $rowNumber, $row['prenom']);
        $sheet->setCellValue('C' . $rowNumber, $row['postnom']);
        $sheet->setCellValue('D' . $rowNumber, $row['salaire_paye']);
        $sheet->setCellValue('E' . $rowNumber, $row['num_compte_banque']);
        $rowNumber++;
    }

    // Nom du fichier
    $fileName = "paiement_{$moisPaiement}.xlsx";

    // Écriture du fichier Excel
    $writer = new Xlsx($spreadsheet);
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $fileName . '"');
    $writer->save('php://output');
    exit();
} else {
    // Redirection si les paramètres ne sont pas définis
    header("Location: gestion_paiement.php");
    exit();
}

// Fermer la connexion à la base de données
mysqli_close($conn);
