<?php
require_once '../../includes/functions.php';
checkRole('director');

require_once '../../config/database.php';
require_once '../../config/config.php';

$database = new Database();
$db = $database->getConnection();

// Obtener lista de clases con información del docente
$query = "SELECT c.*, d.nombre as docente_nombre, d.apellido as docente_apellido,
          (SELECT COUNT(*) FROM alumnos_clases WHERE clase_id = c.id) as total_alumnos
          FROM clases c 
          JOIN usuarios d ON c.docente_id = d.id 
          ORDER BY c.dia_semana, c.hora_inicio";
$stmt = $db->query($query);
$clases = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener lista de docentes para el formulario
$query = "SELECT id, nombre, apellido FROM usuarios u 
          JOIN roles r ON u.rol_id = r.id 
          WHERE r.nombre = 'docente' 
          ORDER BY nombre, apellido";
$stmt = $db->query($query);
$docentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <title>Gestión de Clases - <?php echo SITE_NAME; ?></title>
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
                        <a class="nav-link active" href="clases.php">Clases</a>
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Gestión de Clases</h2>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#nuevaClaseModal">
                <i class="fas fa-plus"></i> Nueva Clase
            </button>
        </div>

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
                            <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($clase['aula']); ?><br>
                            <i class="fas fa-users"></i> <?php echo $clase['total_alumnos']; ?> alumnos
                        </p>
                        <div class="d-flex justify-content-between align-items-center">
                            <button class="btn btn-info btn-sm" onclick="editarClase(<?php echo $clase['id']; ?>)">
                                <i class="fas fa-edit"></i> Editar
                            </button>
                            <button class="btn btn-success btn-sm" onclick="gestionarAlumnos(<?php echo $clase['id']; ?>)">
                                <i class="fas fa-user-graduate"></i> Alumnos
                            </button>
                            <button class="btn btn-danger btn-sm" onclick="eliminarClase(<?php echo $clase['id']; ?>)">
                                <i class="fas fa-trash"></i> Eliminar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if (empty($clases)): ?>
        <div class="alert alert-info">
            No hay clases registradas actualmente.
        </div>
        <?php endif; ?>
    </div>

    <!-- Modal Nueva Clase -->
    <div class="modal fade" id="nuevaClaseModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nueva Clase</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="procesar_clase.php" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre de la Clase</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                        </div>
                        <div class="mb-3">
                            <label for="docente_id" class="form-label">Docente</label>
                            <select class="form-select" id="docente_id" name="docente_id" required>
                                <option value="">Seleccione un docente</option>
                                <?php foreach ($docentes as $docente): ?>
                                <option value="<?php echo $docente['id']; ?>">
                                    <?php echo htmlspecialchars($docente['nombre'] . ' ' . $docente['apellido']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="dia_semana" class="form-label">Día de la Semana</label>
                            <select class="form-select" id="dia_semana" name="dia_semana" required>
                                <?php foreach ($dias_semana as $id => $dia): ?>
                                <option value="<?php echo $id; ?>"><?php echo $dia; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="hora_inicio" class="form-label">Hora de Inicio</label>
                                    <input type="time" class="form-control" id="hora_inicio" name="hora_inicio" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="hora_fin" class="form-label">Hora de Fin</label>
                                    <input type="time" class="form-control" id="hora_fin" name="hora_fin" required>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="aula" class="form-label">Aula</label>
                            <input type="text" class="form-control" id="aula" name="aula" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editarClase(id) {
            window.location.href = `editar_clase.php?id=${id}`;
        }

        function gestionarAlumnos(id) {
            window.location.href = `alumnos_clase.php?id=${id}`;
        }

        function eliminarClase(id) {
            if (confirm('¿Está seguro de eliminar esta clase?')) {
                window.location.href = `eliminar_clase.php?id=${id}`;
            }
        }
    </script>
</body>
</html> 