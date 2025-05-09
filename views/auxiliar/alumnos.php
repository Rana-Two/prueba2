<?php
require_once '../../includes/functions.php';
checkRole('auxiliar');

require_once '../../config/database.php';
require_once '../../config/config.php';

$database = new Database();
$db = $database->getConnection();

// Obtener lista de alumnos
$query = "SELECT u.*, r.nombre as rol_nombre 
          FROM usuarios u 
          JOIN roles r ON u.rol_id = r.id 
          WHERE r.nombre = 'alumno' 
          ORDER BY u.nombre, u.apellido";
$stmt = $db->query($query);
$alumnos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Alumnos - <?php echo SITE_NAME; ?></title>
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
                        <a class="nav-link active" href="alumnos.php">Alumnos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="registrar_asistencia.php">Registrar Asistencia</a>
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
            <h2>Gestión de Alumnos</h2>
            <div>
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#registrarAsistenciaModal">
                    <i class="fas fa-clipboard-check"></i> Registrar Asistencia
                </button>
            </div>
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

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Apellido</th>
                                <th>Email</th>
                                <th>Asistencia Hoy</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($alumnos as $alumno): ?>
                            <tr>
                                <td><?php echo $alumno['id']; ?></td>
                                <td><?php echo htmlspecialchars($alumno['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($alumno['apellido']); ?></td>
                                <td><?php echo htmlspecialchars($alumno['email']); ?></td>
                                <td>
                                    <?php
                                    // Verificar asistencia de hoy
                                    $query = "SELECT estado FROM asistencias 
                                             WHERE alumno_id = :alumno_id 
                                             AND DATE(fecha) = CURDATE()";
                                    $stmt = $db->prepare($query);
                                    $stmt->execute(['alumno_id' => $alumno['id']]);
                                    $asistencia = $stmt->fetch(PDO::FETCH_ASSOC);
                                    
                                    if ($asistencia) {
                                        $badge_class = $asistencia['estado'] == 'presente' ? 'bg-success' : 'bg-danger';
                                        $estado = $asistencia['estado'] == 'presente' ? 'Presente' : 'Ausente';
                                        echo "<span class='badge {$badge_class}'>{$estado}</span>";
                                    } else {
                                        echo "<span class='badge bg-secondary'>No registrado</span>";
                                    }
                                    ?>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-info" onclick="verHistorial(<?php echo $alumno['id']; ?>)">
                                        <i class="fas fa-history"></i>
                                    </button>
                                    <button class="btn btn-sm btn-success" onclick="registrarAsistencia(<?php echo $alumno['id']; ?>)">
                                        <i class="fas fa-clipboard-check"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Registrar Asistencia -->
    <div class="modal fade" id="registrarAsistenciaModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Registrar Asistencia</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="procesar_asistencia.php" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="alumno_id" class="form-label">Alumno</label>
                            <select class="form-select" id="alumno_id" name="alumno_id" required>
                                <option value="">Seleccione un alumno</option>
                                <?php foreach ($alumnos as $alumno): ?>
                                <option value="<?php echo $alumno['id']; ?>">
                                    <?php echo htmlspecialchars($alumno['nombre'] . ' ' . $alumno['apellido']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="estado" class="form-label">Estado</label>
                            <select class="form-select" id="estado" name="estado" required>
                                <option value="presente">Presente</option>
                                <option value="ausente">Ausente</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="observacion" class="form-label">Observación</label>
                            <textarea class="form-control" id="observacion" name="observacion" rows="3"></textarea>
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
        function verHistorial(id) {
            window.location.href = `historial_asistencia.php?id=${id}`;
        }

        function registrarAsistencia(id) {
            document.getElementById('alumno_id').value = id;
            new bootstrap.Modal(document.getElementById('registrarAsistenciaModal')).show();
        }
    </script>
</body>
</html> 