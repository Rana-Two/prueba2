<?php
require_once '../../includes/functions.php';
checkRole('docente');

require_once '../../config/database.php';
require_once '../../config/config.php';

$database = new Database();
$db = $database->getConnection();

// Obtener estadísticas
$stats = [
    'total_alumnos' => 0,
    'total_clases' => 0,
    'total_asistencias' => 0
];

try {
    // Contar alumnos
    $query = "SELECT COUNT(*) as total FROM usuarios u 
              JOIN roles r ON u.rol_id = r.id 
              WHERE r.nombre = 'alumno'";
    $stmt = $db->query($query);
    $stats['total_alumnos'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Contar clases
    $query = "SELECT COUNT(*) as total FROM clases";
    $stmt = $db->query($query);
    $stats['total_clases'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Contar asistencias
    $query = "SELECT COUNT(*) as total FROM asistencias";
    $stmt = $db->query($query);
    $stats['total_asistencias'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
} catch (PDOException $e) {
    // Manejar error silenciosamente
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Docente - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../../assets/css/style.css" rel="stylesheet">
    <style>
        .stat-card {
            transition: transform 0.2s;
        }
        .stat-card:hover {
            transform: translateY(-5px);
        }
        .stat-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
        }
    </style>
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
                        <a class="nav-link active" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="clases.php">Mis Clases</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="alumnos.php">Alumnos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="asistencias.php">Asistencias</a>
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
        <h2 class="mb-4">Dashboard del Docente</h2>

        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card stat-card h-100">
                    <div class="card-body text-center">
                        <div class="stat-icon text-primary">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <h3 class="card-title"><?php echo $stats['total_alumnos']; ?></h3>
                        <p class="card-text">Alumnos Registrados</p>
                        <a href="alumnos.php" class="btn btn-primary">Ver Alumnos</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card stat-card h-100">
                    <div class="card-body text-center">
                        <div class="stat-icon text-success">
                            <i class="fas fa-chalkboard"></i>
                        </div>
                        <h3 class="card-title"><?php echo $stats['total_clases']; ?></h3>
                        <p class="card-text">Clases Programadas</p>
                        <a href="clases.php" class="btn btn-success">Ver Clases</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card stat-card h-100">
                    <div class="card-body text-center">
                        <div class="stat-icon text-info">
                            <i class="fas fa-clipboard-check"></i>
                        </div>
                        <h3 class="card-title"><?php echo $stats['total_asistencias']; ?></h3>
                        <p class="card-text">Asistencias Registradas</p>
                        <a href="asistencias.php" class="btn btn-info">Ver Asistencias</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Próximas Clases</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">Matemáticas</h6>
                                    <small class="text-muted">Hoy 10:00 AM</small>
                                </div>
                                <p class="mb-1">Tema: Álgebra Lineal</p>
                            </div>
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">Física</h6>
                                    <small class="text-muted">Hoy 2:00 PM</small>
                                </div>
                                <p class="mb-1">Tema: Mecánica Clásica</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Actividad Reciente</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">Asistencia Registrada</h6>
                                    <small class="text-muted">Hace 5 minutos</small>
                                </div>
                                <p class="mb-1">Clase de Matemáticas - 25 alumnos presentes</p>
                            </div>
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">Nueva Evaluación</h6>
                                    <small class="text-muted">Hace 2 horas</small>
                                </div>
                                <p class="mb-1">Examen parcial de Física programado</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 