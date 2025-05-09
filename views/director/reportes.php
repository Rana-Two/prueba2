<?php
require_once '../../includes/functions.php';
checkRole('director');

require_once '../../config/config.php';

// Datos estáticos para las estadísticas
$stats = [
    'total_docentes' => 15,
    'total_alumnos' => 120,
    'total_auxiliares' => 5,
    'total_clases' => 25,
    'total_asistencias' => 850
];

// Datos de ejemplo para las asistencias de la última semana
$asistencias_semana = [
    ['dia' => '2024-03-20', 'total' => 150, 'presentes' => 135],
    ['dia' => '2024-03-19', 'total' => 150, 'presentes' => 142],
    ['dia' => '2024-03-18', 'total' => 150, 'presentes' => 138],
    ['dia' => '2024-03-17', 'total' => 150, 'presentes' => 145],
    ['dia' => '2024-03-16', 'total' => 150, 'presentes' => 140],
    ['dia' => '2024-03-15', 'total' => 150, 'presentes' => 132],
    ['dia' => '2024-03-14', 'total' => 150, 'presentes' => 148]
];

// Datos de ejemplo para las clases más populares
$clases_populares = [
    ['nombre' => 'Matemáticas Avanzadas', 'total_alumnos' => 45],
    ['nombre' => 'Programación Web', 'total_alumnos' => 38],
    ['nombre' => 'Física Cuántica', 'total_alumnos' => 32],
    ['nombre' => 'Inglés Conversacional', 'total_alumnos' => 28],
    ['nombre' => 'Historia del Arte', 'total_alumnos' => 25]
];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../../assets/css/style.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="../../index.php"><?php echo SITE_NAME; ?></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="usuariosDropdown" role="button" data-bs-toggle="dropdown">
                            Usuarios
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="usuarios.php?rol=docente">Docentes</a></li>
                            <li><a class="dropdown-item" href="usuarios.php?rol=alumno">Alumnos</a></li>
                            <li><a class="dropdown-item" href="usuarios.php?rol=auxiliar">Auxiliares</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="clases.php">Clases</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="reportes.php">Reportes</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?php echo $_SESSION['user_name']; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="perfil.php">Mi Perfil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../../logout.php">Cerrar Sesión</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <h2 class="mb-4">Reportes y Estadísticas</h2>

        <!-- Estadísticas Generales -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title">Usuarios</h5>
                        <p class="card-text">
                            <i class="fas fa-chalkboard-teacher"></i> <?php echo $stats['total_docentes']; ?> Docentes<br>
                            <i class="fas fa-user-graduate"></i> <?php echo $stats['total_alumnos']; ?> Alumnos<br>
                            <i class="fas fa-user-tie"></i> <?php echo $stats['total_auxiliares']; ?> Auxiliares
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">Clases</h5>
                        <p class="card-text">
                            <i class="fas fa-book"></i> <?php echo $stats['total_clases']; ?> Clases Activas
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-info text-white">
                    <div class="card-body">
                        <h5 class="card-title">Asistencias</h5>
                        <p class="card-text">
                            <i class="fas fa-clipboard-check"></i> <?php echo $stats['total_asistencias']; ?> Registros
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Gráfico de Asistencias -->
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Asistencias de la Última Semana</h5>
                        <canvas id="asistenciasChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Clases Populares -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Clases más Populares</h5>
                        <div class="list-group">
                            <?php foreach ($clases_populares as $clase): ?>
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($clase['nombre']); ?></h6>
                                    <small><?php echo $clase['total_alumnos']; ?> alumnos</small>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Gráfico de asistencias
        const ctx = document.getElementById('asistenciasChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode(array_column($asistencias_semana, 'dia')); ?>,
                datasets: [{
                    label: 'Presentes',
                    data: <?php echo json_encode(array_column($asistencias_semana, 'presentes')); ?>,
                    backgroundColor: 'rgba(40, 167, 69, 0.5)',
                    borderColor: 'rgb(40, 167, 69)',
                    borderWidth: 1
                }, {
                    label: 'Ausentes',
                    data: <?php echo json_encode(array_map(function($item) {
                        return $item['total'] - $item['presentes'];
                    }, $asistencias_semana)); ?>,
                    backgroundColor: 'rgba(220, 53, 69, 0.5)',
                    borderColor: 'rgb(220, 53, 69)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html> 