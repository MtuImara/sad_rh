<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="#"><?php echo $pageTitle ?></a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarContent">
        <ul class="navbar-nav ml-auto">
            <?php if ($role != 'user'): ?>
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">Accueil</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="gestion_membre.php">Les Membres</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="gestion_presence.php">La présence</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="gestion_departement.php">Les Départements</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="gestion_projets.php">Les Projets</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="archive_membre.php">Archive</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="gestion_fichiers.php">Liste des fichiers</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="gestion_paiement.php">Paiement</a>
                </li>

            <?php endif; ?>
            <?php if ($role === 'admin'): ?>
                <li class="nav-item">
                    <a class="nav-link" href="gestion_users.php">Les Utilisateurs</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="historique.php">Historique</a>
                </li>
            <?php endif; ?>

            <?php if ($role === 'user'): ?>
                <li class="nav-item">
                    <a class="nav-link" href="profil.php">Profil</a>
                </li>

                <!-- Notifications Dropdown -->
                <li class="nav-item dropdown">
                    <?php
                    // Globaliser la connexion à la base de données
                    global $conn;

                    // Récupérer le nombre de notifications non lues
                    $notifications_count_query = "SELECT COUNT(*) as unread_count FROM notifications WHERE id_user = ? AND is_read = FALSE";
                    if ($notifications_count_stmt = mysqli_prepare($conn, $notifications_count_query)) {
                        mysqli_stmt_bind_param($notifications_count_stmt, 'i', $user_id);
                        mysqli_stmt_execute($notifications_count_stmt);
                        mysqli_stmt_bind_result($notifications_count_stmt, $unread_count);
                        mysqli_stmt_fetch($notifications_count_stmt);
                        mysqli_stmt_close($notifications_count_stmt);
                    }
                    ?>
                    <a class="nav-link dropdown-toggle" href="#" id="notificationsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fa fa-bell"></i>
                        <?php if ($unread_count > 0): ?>
                            <span class="badge badge-danger"><?php echo $unread_count; ?></span>
                        <?php endif; ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="notificationsDropdown">
                        <?php
                        // Récupérer les notifications non lues
                        $notifications_query = "SELECT * FROM notifications WHERE id_user = ? AND is_read = FALSE";
                        if ($notifications_stmt = mysqli_prepare($conn, $notifications_query)) {
                            mysqli_stmt_bind_param($notifications_stmt, 'i', $user_id);
                            mysqli_stmt_execute($notifications_stmt);
                            $notifications_result = mysqli_stmt_get_result($notifications_stmt);
                            mysqli_stmt_close($notifications_stmt);

                            if (mysqli_num_rows($notifications_result) > 0) {
                                while ($notification = mysqli_fetch_assoc($notifications_result)) {
                                    echo "<a class='dropdown-item' href='#'>";
                                    echo "<p>" . htmlspecialchars($notification['message']) . "</p>";
                                    echo "<small>" . htmlspecialchars($notification['date_notification']) . "</small>";
                                    echo "</a>";
                                }
                            } else {
                                echo "<a class='dropdown-item' href='#'>Aucune nouvelle notification</a>";
                            }
                        }
                        ?>
                    </div>
                </li>
            <?php endif; ?>

            <li class="nav-item">
                <a class="nav-link" href="gestion_conge.php">Liste des congés</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="logout.php">Déconnexion</a>
            </li>
        </ul>
    </div>
</nav>