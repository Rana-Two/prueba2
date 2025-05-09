<?php
require_once '../../includes/functions.php';
checkRole('auxiliar');

require_once '../../config/database.php';
require_once '../../config/config.php';

$database = new Database();
$db = $database->getConnection();

// Obtener estadísticas
$stats = [
    'total_alumnos' => 0,
    'asistencias_hoy' => 0,
    'faltas_hoy' => 0
];

try {
    // Contar alumnos
    $query = "SELECT COUNT(*) as total FROM usuarios u 
              JOIN roles r ON u.rol_id = r.id 
              WHERE r.nombre = 'alumno'";
    $stmt = $db->query($query);
    $stats['total_alumnos'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Contar asistencias y faltas de hoy
    $query = "SELECT 
                SUM(CASE WHEN estado = 'presente' THEN 1 ELSE 0 END) as asistencias,
                SUM(CASE WHEN estado = 'ausente' THEN 1 ELSE 0 END) as faltas
              FROM asistencias 
              WHERE DATE(fecha) = CURDATE()";
    $stmt = $db->query($query);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $stats['asistencias_hoy'] = $result['asistencias'] ?? 0;
    $stats['faltas_hoy'] = $result['faltas'] ?? 0;
} catch (PDOException $e) {
    // Manejar error silenciosamente
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Auxiliar - <?php echo SITE_NAME; ?></title>
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
                        <a class="nav-link" href="registrar_asistencia.php">Registrar Asistencia</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="alumnos.php">Alumnos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="reportes.php">Reportes</a>
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
        <h2 class="mb-4">Dashboard del Auxiliar</h2>

        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card stat-card h-100">
                    <div class="card-body text-center">
                        <div class="stat-icon text-primary">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <h3 class="card-title"><?php echo $stats['total_alumnos']; ?></h3>
                        <p class="card-text">Total de Alumnos</p>
                        <a href="alumnos.php" class="btn btn-primary">Ver Alumnos</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card stat-card h-100">
                    <div class="card-body text-center">
                        <div class="stat-icon text-success">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h3 class="card-title"><?php echo $stats['asistencias_hoy']; ?></h3>
                        <p class="card-text">Asistencias Hoy</p>
                        <a href="registrar_asistencia.php" class="btn btn-success">Registrar Asistencia</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card stat-card h-100">
                    <div class="card-body text-center">
                        <div class="stat-icon text-danger">
                            <i class="fas fa-times-circle"></i>
                        </div>
                        <h3 class="card-title"><?php echo $stats['faltas_hoy']; ?></h3>
                        <p class="card-text">Faltas Hoy</p>
                        <a href="registrar_asistencia.php" class="btn btn-danger">Registrar Faltas</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Acciones Rápidas</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="registrar_asistencia.php" class="btn btn-primary">
                                <i class="fas fa-clipboard-check"></i> Registrar Asistencia
                            </a>
                            <a href="reportes.php" class="btn btn-success">
                                <i class="fas fa-file-alt"></i> Generar Reporte
                            </a>
                            <a href="alumnos.php" class="btn btn-info">
                                <i class="fas fa-search"></i> Buscar Alumno
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Últimas Asistencias Registradas</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">Juan Pérez</h6>
                                    <small class="text-muted">Hace 5 minutos</small>
                                </div>
                                <p class="mb-1">Estado: <span class="badge bg-success">Presente</span></p>
                            </div>
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">María García</h6>
                                    <small class="text-muted">Hace 10 minutos</small>
                                </div>
                                <p class="mb-1">Estado: <span class="badge bg-danger">Ausente</span></p>
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