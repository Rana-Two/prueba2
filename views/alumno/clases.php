<?php
require_once '../../includes/functions.php';
checkRole('alumno');

require_once '../../config/database.php';
require_once '../../config/config.php';

$database = new Database();
$db = $database->getConnection();

// Obtener clases del alumno
$query = "SELECT c.*, d.nombre as docente_nombre, d.apellido as docente_apellido 
          FROM clases c 
          JOIN usuarios d ON c.docente_id = d.id 
          WHERE c.id IN (
              SELECT clase_id FROM alumnos_clases WHERE alumno_id = :alumno_id
          )
          ORDER BY c.dia_semana, c.hora_inicio";
$stmt = $db->prepare($query);
$stmt->execute(['alumno_id' => $_SESSION['user_id']]);
$clases = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Mapeo de días de la semana
$dias_semana = [
    1 => 'Lunes',
    2 => 'Martes',
    3 => 'Miércoles',
    4 => 'Jueves',
    5 => 'Viernes',
    6 => 'Sábado',
    7 => 'Domingo'
];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Clases - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../../assets/css/style.css" rel="stylesheet">
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
                    <li class="nav-item">
                        <a class="nav-link active" href="clases.php">Mis Clases</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="asistencias.php">Mis Asistencias</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="calificaciones.php">Calificaciones</a>
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
        <h2 class="mb-4">Mis Clases</h2>

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show" role="alert">
                <?php 
                echo $_SESSION['message'];
                unset($_SESSION['message']);
                unset($_SESSION['message_type']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <?php foreach ($clases as $clase): ?>
            <div class="col-md-6 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($clase['nombre']); ?></h5>
                        <h6 class="card-subtitle mb-2 text-muted">
                            Profesor: <?php echo htmlspecialchars($clase['docente_nombre'] . ' ' . $clase['docente_apellido']); ?>
                        </h6>
                        <p class="card-text">
                            <i class="fas fa-calendar"></i> <?php echo $dias_semana[$clase['dia_semana']]; ?><br>
                            <i class="fas fa-clock"></i> <?php echo date('h:i A', strtotime($clase['hora_inicio'])); ?> - 
                            <?php echo date('h:i A', strtotime($clase['hora_fin'])); ?><br>
                            <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($clase['aula']); ?>
                        </p>
                        <div class="d-flex justify-content-between align-items-center">
                            <button class="btn btn-primary btn-sm" onclick="verDetalles(<?php echo $clase['id']; ?>)">
                                <i class="fas fa-info-circle"></i> Ver Detalles
                            </button>
                            <button class="btn btn-success btn-sm" onclick="verAsistencias(<?php echo $clase['id']; ?>)">
                                <i class="fas fa-clipboard-check"></i> Ver Asistencias
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if (empty($clases)): ?>
        <div class="alert alert-info">
            No tienes clases asignadas actualmente.
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function verDetalles(id) {
            window.location.href = `detalle_clase.php?id=${id}`;
        }

        function verAsistencias(id) {
            window.location.href = `asistencias_clase.php?id=${id}`;
        }
    </script>
</body>
</html> 