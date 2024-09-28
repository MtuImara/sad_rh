<?php
// Inclusion du fichier de configuration de la base de données
require_once 'config/database.php';

class DepartmentController
{

    // Méthode pour créer un nouveau département
    public function createDepartment($nom_department, $description_department = null)
    {
        global $conn;

        // Préparation de la requête SQL d'insertion
        $sql = "INSERT INTO `department` (`nom_department`, `description_department`) 
                VALUES (?, ?)";

        // Utilisation des déclarations préparées pour éviter les injections SQL
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $nom_department, $description_department);

        // Exécution de la requête
        if (mysqli_stmt_execute($stmt)) {
            return true; // Création du département réussie
        } else {
            return false; // Échec de la création du département
        }
    }

    // Méthode pour récupérer tous les départements
    public function getAllDepartments()
    {
        global $conn;

        $departments = [];

        // Requête SQL pour récupérer tous les départements
        $sql = "SELECT `id_department`, `nom_department`, `description_department` FROM `department`";
        $result = mysqli_query($conn, $sql);

        // Récupération des résultats
        if (mysqli_num_rows($result) > 0) {
            while ($row = mysqli_fetch_assoc($result)) {
                $departments[] = [
                    'id_department' => $row['id_department'],
                    'nom_department' => $row['nom_department'],
                    'description_department' => $row['description_department']
                ];
            }
        }

        return $departments;
    }

    // Méthode pour récupérer un département par son ID
    public function getDepartmentById($id_department)
    {
        global $conn;

        $sql = "SELECT `id_department`, `nom_department`, `description_department` 
                FROM `department` WHERE `id_department` = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $id_department);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $id_department, $nom_department, $description_department);

        // Récupération des résultats
        if (mysqli_stmt_fetch($stmt)) {
            $department = [
                'id_department' => $id_department,
                'nom_department' => $nom_department,
                'description_department' => $description_department
            ];
            return $department;
        } else {
            return null; // Aucun département trouvé
        }
    }

    // Méthode pour mettre à jour un département
    public function updateDepartment($id_department, $nom_department, $description_department = null)
    {
        global $conn;

        // Préparation de la requête SQL de mise à jour
        $sql = "UPDATE `department` SET `nom_department` = ?, `description_department` = ? 
                WHERE `id_department` = ?";

        // Utilisation des déclarations préparées pour éviter les injections SQL
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ssi", $nom_department, $description_department, $id_department);

        // Exécution de la requête
        if (mysqli_stmt_execute($stmt)) {
            return true; // Mise à jour réussie du département
        } else {
            return false; // Échec de la mise à jour du département
        }
    }

    // Méthode pour supprimer un département par son ID
    public function deleteDepartment($id_department)
    {
        global $conn;

        // Préparation de la requête SQL de suppression
        $sql = "DELETE FROM `department` WHERE `id_department` = ?";

        // Utilisation des déclarations préparées pour éviter les injections SQL
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $id_department);

        // Exécution de la requête
        if (mysqli_stmt_execute($stmt)) {
            return true; // Suppression réussie du département
        } else {
            return false; // Échec de la suppression du département
        }
    }
}
