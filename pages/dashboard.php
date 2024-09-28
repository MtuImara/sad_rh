<?php
require "cache.php";


// Récupérer les informations de l'utilisateur connecté
$user_id = $_SESSION['id_user'];
$user_query = "SELECT role FROM users WHERE id_user = $user_id";
$user_result = mysqli_query($conn, $user_query);
$role = '';

if ($user_row = mysqli_fetch_assoc($user_result)) {
    $role = $user_row['role'];
}

// Récupérer le nombre total de membres
$query_total_membres = "SELECT COUNT(*) as total FROM membres";
$result_total_membres = mysqli_query($conn, $query_total_membres);
$total_membres = mysqli_fetch_assoc($result_total_membres)['total'];

// Récupérer le nombre d'hommes et de femmes
$query_gender_count = "SELECT genre, COUNT(*) as count FROM membres GROUP BY genre";
$result_gender_count = mysqli_query($conn, $query_gender_count);
$gender_counts = [];
while ($row = mysqli_fetch_assoc($result_gender_count)) {
    $gender_counts[$row['genre']] = $row['count'];
}
$total_hommes = $gender_counts['homme'] ?? 0;
$total_femmes = $gender_counts['femme'] ?? 0;

// Récupérer le nombre de membres actifs et en congé
$query_status_count = "SELECT status, COUNT(*) as count FROM membres GROUP BY status";
$result_status_count = mysqli_query($conn, $query_status_count);
$status_counts = [];
while ($row = mysqli_fetch_assoc($result_status_count)) {
    $status_counts[$row['status']] = $row['count'];
}
$total_actifs = $status_counts['actif'] ?? 0;
$total_conge = $status_counts['en congé'] ?? 0;

// Récupérer le nombre de départements
$query_total_departments = "SELECT COUNT(*) as total FROM department";
$result_total_departments = mysqli_query($conn, $query_total_departments);
$total_departments = mysqli_fetch_assoc($result_total_departments)['total'];

// Récupérer les données des projets en cours
$query_projets = "SELECT nom_project, budget FROM projects";
$query_total_projets = "SELECT COUNT(*) as total FROM projects";
$result_total_projets = mysqli_query($conn, $query_total_projets);
$result_projets = mysqli_query($conn, $query_projets);
$total_projet = mysqli_fetch_assoc($result_total_projets)['total'];
$projets = [];
while ($row = mysqli_fetch_assoc($result_projets)) {
    $projets[] = $row;
}

// Fermer la connexion à la base de données
mysqli_close($conn);

$pageTitle = "Tableau de bord";
?>
<?php require "head.php"; ?>

<body>
    <?php require "menu.php"; ?>
    <div class="container mt-5">
        <h1 class="text-center">Tableau de Bord</h1>
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-header">Total Membres</div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $total_membres; ?></h5>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-success mb-3">
                    <div class="card-header">Hommes</div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $total_hommes; ?></h5>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-danger mb-3">
                    <div class="card-header">Femmes</div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $total_femmes; ?></h5>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-info mb-3">
                    <div class="card-header">Actifs</div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $total_actifs; ?></h5>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-warning mb-3">
                    <div class="card-header">En congé</div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $total_conge; ?></h5>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-secondary mb-3">
                    <div class="card-header">Départements</div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $total_departments; ?></h5>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-dark mb-3">
                    <div class="card-header">Projets</div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $total_projet; ?></h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-6">
                <h3>Projets en Cours</h3>
                <canvas id="projectsChart"></canvas>
            </div>
            <div class="col-md-6">
                <h3>Performances Hommes vs Femmes</h3>
                <canvas id="genderPerformanceChart"></canvas>
            </div>
        </div>

        <script>
            // Projets en Cours Chart
            var ctx1 = document.getElementById('projectsChart').getContext('2d');
            var projectsChart = new Chart(ctx1, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode(array_column($projets, 'nom_projet')); ?>,
                    datasets: [{
                        label: 'Budget des Projets',
                        data: <?php echo json_encode(array_column($projets, 'budget')); ?>,
                        backgroundColor: 'rgba(54, 162, 235, 0.2)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Performances Hommes vs Femmes Chart
            var ctx2 = document.getElementById('genderPerformanceChart').getContext('2d');
            var genderPerformanceChart = new Chart(ctx2, {
                type: 'pie',
                data: {
                    labels: ['Hommes', 'Femmes'],
                    datasets: [{
                        label: 'Performances',
                        data: [<?php echo $total_hommes; ?>, <?php echo $total_femmes; ?>],
                        backgroundColor: ['rgba(75, 192, 192, 0.2)', 'rgba(255, 99, 132, 0.2)'],
                        borderColor: ['rgba(75, 192, 192, 1)', 'rgba(255, 99, 132, 1)'],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true
                }
            });

            // Carte de la région du Sud
            var map = L.map('mapid').setView([-4.321, 15.313], 10); // Coordonnées de la région du Sud
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 18,
            }).addTo(map);

            // Ajouter des marqueurs ou des zones spécifiques de la région du Sud si nécessaire
            // Exemple : L.marker([latitude, longitude]).addTo(map);
        </script>
    </div>
</body>

</html>