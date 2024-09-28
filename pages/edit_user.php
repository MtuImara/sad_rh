<?php
// Démarrer la session et inclure le fichier de connexion
require "cache.php";

// Initialiser les variables
$message = '';
$id_user = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Vérifier si l'identifiant de l'utilisateur est valide
if ($id_user <= 0) {
    $message = "Identifiant utilisateur invalide.";
} else {
    // Récupérer les informations de l'utilisateur à modifier
    $query = "SELECT * FROM users WHERE id_user = $id_user";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
    } else {
        $message = "Utilisateur non trouvé.";
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Récupération et échappement des données du formulaire
        $username = mysqli_real_escape_string($conn, $_POST['username']);
        $email = mysqli_real_escape_string($conn, $_POST['email']);
        $role = mysqli_real_escape_string($conn, $_POST['role']);
        $password = mysqli_real_escape_string($conn, $_POST['password']);

        // Vérifier si l'email est déjà utilisé par un autre utilisateur
        $check_email_query = "SELECT * FROM users WHERE email = '$email' AND id_user != $id_user";
        $result_email_check = mysqli_query($conn, $check_email_query);

        if (mysqli_num_rows($result_email_check) > 0) {
            $message = "Erreur : Cette adresse email est déjà utilisée.";
        } else {
            // Hash du mot de passe si il est modifié
            $password_query_part = '';
            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $password_query_part = ", password = '$hashed_password'";
            }

            // Mise à jour de l'utilisateur
            $update_query = "UPDATE users SET username = '$username', email = '$email', role = '$role' $password_query_part WHERE id_user = $id_user";

            if (mysqli_query($conn, $update_query)) {
                $message = "Utilisateur mis à jour avec succès.";
                // Enregistrement dans l'historique
                $action = "Modification d'un utilisateur";
                $details = "Détails de la modification de $username";
                enregistrerHistorique($conn, $user_id, $action, $details);
            } else {
                $message = "Erreur: " . mysqli_error($conn);
            }

            // Redirection après traitement
            header('Location: gestion_users.php');
            exit();
        }
    }

    // Fermer la connexion à la base de données
    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modifier l'utilisateur</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h2 class="text-center">Modifier l'utilisateur</h2>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($message)) : ?>
                            <div class="alert alert-danger"><?php echo $message; ?></div>
                        <?php endif; ?>
                        <?php if (!empty($user)) : ?>
                            <form action="" method="POST">
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="username">Nom d'utilisateur</label>
                                        <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="email">Adresse email</label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label for="role">Rôle</label>
                                        <select class="form-control" id="role" name="role" required>
                                            <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                                            <option value="rh" <?php echo $user['role'] === 'rh' ? 'selected' : ''; ?>>RH</option>
                                            <option value="user" <?php echo $user['role'] === 'user' ? 'selected' : ''; ?>>Utilisateur</option>
                                        </select>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <label for="password">Mot de passe</label>
                                        <input type="password" class="form-control" id="password" name="password" placeholder="Laisser vide pour ne pas modifier">
                                    </div>
                                </div>
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <button type="submit" class="btn btn-primary btn-block">Modifier</button>
                                    </div>
                                    <div class="form-group col-md-6">
                                        <a href="gestion_users.php" class="btn btn-secondary btn-block">Annuler</a>
                                    </div>
                                </div>
                            </form>
                        <?php else : ?>
                            <p>Utilisateur non trouvé.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Bootstrap -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>

</html>