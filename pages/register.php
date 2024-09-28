<?php
// Inclusion du fichier de connexion à la base de données
require "cache.php";

$message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupération des données du formulaire
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);

    // Vérification si l'email existe déjà
    $check_email_query = "SELECT * FROM membres WHERE email = '$email'";
    $result = mysqli_query($conn, $check_email_query);

    if (mysqli_num_rows($result) > 0) {
        // Email déjà utilisé
        $message = 'Cette adresse email est déjà utilisée. Veuillez en choisir une autre.';
    } else {
        // Hashage du mot de passe
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Requête d'insertion des données dans la table "membres"
        $query = "INSERT INTO membres (nom, prenom, adresse, numero_telephone, email, password, role) 
                  VALUES ('', '', '', '', '$email', '$hashed_password', 'agent')";

        if (mysqli_query($conn, $query)) {
            // Inscription réussie
            $message = 'Inscription réussie. Connectez-vous maintenant.';
            $_SESSION['message'] = $message;
            header('Location: login.php');
            exit();
        } else {
            // En cas d'erreur lors de l'insertion
            $message = 'Erreur lors de l\'inscription. Veuillez réessayer.';
        }
    }
}

// Fermer la connexion à la base de données
mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
    <!-- Bootstrap CSS -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- CSS personnalisé -->
    <link rel="stylesheet" href="styles.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
</head>

<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h2 class="text-center">Inscription</h2>
                    </div>
                    <div class="card-body">
                        <form action="" method="POST" id="registerForm">
                            <div class="form-group">
                                <label for="username">Nom d'utilisateur</label>
                                <input type="text" class="form-control" id="username" name="username" placeholder="Nom d'utilisateur" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Adresse email</label>
                                <input type="email" class="form-control" id="email" name="email" placeholder="Adresse email" required>
                            </div>
                            <div class="form-group">
                                <label for="password">Mot de passe</label>
                                <div class="input-group">
                                    <input type="password" class="form-control" id="password" name="password" placeholder="Mot de passe" required>
                                    <div class="input-group-append">
                                        <span class="input-group-text toggle-password"><i class="fas fa-eye"></i></span>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-success btn-block">S'inscrire</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript Bootstrap -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <!-- jQuery (nécessaire pour Bootstrap JavaScript plugins) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <!-- JavaScript personnalisé -->
    <script src="js/custom.js"></script>
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        $(document).ready(function() {
            // Afficher/masquer le mot de passe
            $(".toggle-password").click(function() {
                $(this).toggleClass("active");
                var type = $(this).hasClass("active") ? "text" : "password";
                $("#password").attr("type", type);
            });

            <?php if (!empty($message)) : ?>
                toastr.error("<?php echo $message; ?>");
            <?php endif; ?>
        });
    </script>
</body>

</html>