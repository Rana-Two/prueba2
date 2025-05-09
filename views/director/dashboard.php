<?php
require_once '../../includes/functions.php';
checkRole('director');

require_once '../../config/database.php';
require_once '../../config/config.php';

$database = new Database();
$db = $database->getConnection();

// Obtener estadísticas
$stats = [
    'total_docentes' => 0,
    'total_alumnos' => 0,
    'total_auxiliares' => 0
];

try {
    // Contar docentes
    $query = "SELECT COUNT(*) as total FROM usuarios u 
              JOIN roles r ON u.rol_id = r.id 
              WHERE r.nombre = 'docente'";
    $stmt = $db->query($query);
    $stats['total_docentes'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Contar alumnos
    $query = "SELECT COUNT(*) as total FROM usuarios u 
              JOIN roles r ON u.rol_id = r.id 
              WHERE r.nombre = 'alumno'";
    $stmt = $db->query($query);
    $stats['total_alumnos'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    // Contar auxiliares
    $query = "SELECT COUNT(*) as total FROM usuarios u 
              JOIN roles r ON u.rol_id = r.id 
              WHERE r.nombre = 'auxiliar'";
    $stmt = $db->query($query);
    $stats['total_auxiliares'] = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
} catch (PDOException $e) {
    // Manejar error silenciosamente
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Director - <?php echo SITE_NAME; ?></title>
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
                        <a class="nav-link" href="usuarios.php">Usuarios</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="roles.php">Roles</a>
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
        <h2 class="mb-4">Dashboard del Director</h2>

        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card stat-card h-100">
                    <div class="card-body text-center">
                        <div class="stat-icon text-primary">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <h3 class="card-title"><?php echo $stats['total_docentes']; ?></h3>
                        <p class="card-text">Docentes Registrados</p>
                        <a href="usuarios.php?rol=docente" class="btn btn-primary">Ver Docentes</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card stat-card h-100">
                    <div class="card-body text-center">
                        <div class="stat-icon text-success">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <h3 class="card-title"><?php echo $stats['total_alumnos']; ?></h3>
                        <p class="card-text">Alumnos Registrados</p>
                        <a href="usuarios.php?rol=alumno" class="btn btn-success">Ver Alumnos</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-4">
                <div class="card stat-card h-100">
                    <div class="card-body text-center">
                        <div class="stat-icon text-info">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <h3 class="card-title"><?php echo $stats['total_auxiliares']; ?></h3>
                        <p class="card-text">Auxiliares Registrados</p>
                        <a href="usuarios.php?rol=auxiliar" class="btn btn-info">Ver Auxiliares</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Actividad Reciente</h5>
                    </div>
                    <div class="card-body">
                        <div class="list-group">
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">Nuevo Usuario Registrado</h6>
                                    <small class="text-muted">Hace 5 minutos</small>
                                </div>
                                <p class="mb-1">Se registró un nuevo docente en el sistema</p>
                            </div>
                            <div class="list-group-item">
                                <div class="d-flex w-100 justify-content-between">
                                    <h6 class="mb-1">Actualización de Rol</h6>
                                    <small class="text-muted">Hace 2 horas</small>
                                </div>
                                <p class="mb-1">Se modificó el rol de un usuario</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Acciones Rápidas</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="usuarios.php?action=new" class="btn btn-primary">
                                <i class="fas fa-user-plus"></i> Nuevo Usuario
                            </a>
                            <a href="roles.php?action=new" class="btn btn-success">
                                <i class="fas fa-user-tag"></i> Nuevo Rol
                            </a>
                            <a href="reportes.php" class="btn btn-info">
                                <i class="fas fa-chart-bar"></i> Generar Reporte
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 