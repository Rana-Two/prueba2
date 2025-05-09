<?php
session_start();
require_once '../../config/database.php';
require_once '../../config/config.php';

// Verificar si el usuario está logueado y es administrador
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Obtener roles
$query = "SELECT r.*, COUNT(u.id) as total_usuarios 
          FROM roles r 
          LEFT JOIN usuarios u ON r.id = u.rol_id 
          GROUP BY r.id 
          ORDER BY r.nombre";
$stmt = $db->query($query);
$roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Mostrar mensajes
$mensaje = $_SESSION['mensaje'] ?? '';
$tipo_mensaje = $_SESSION['tipo_mensaje'] ?? '';
unset($_SESSION['mensaje'], $_SESSION['tipo_mensaje']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Roles - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../../assets/css/style.css" rel="stylesheet">
    <style>
        .role-card {
            transition: transform 0.2s;
        }
        .role-card:hover {
            transform: translateY(-5px);
        }
        .role-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
        }
        .action-buttons .btn {
            margin: 0 2px;
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
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="usuarios.php">Usuarios</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="roles.php">Roles</a>
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
        <?php if ($mensaje): ?>
        <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show" role="alert">
            <?php echo $mensaje; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Gestión de Roles</h2>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#nuevoRolModal">
                <i class="fas fa-plus"></i> Nuevo Rol
            </button>
        </div>

        <div class="row">
            <?php foreach ($roles as $rol): ?>
            <div class="col-md-4 mb-4">
                <div class="card role-card h-100">
                    <div class="card-body text-center">
                        <div class="role-icon">
                            <?php
                            $icon = 'fa-users';
                            switch (strtolower($rol['nombre'])) {
                                case 'admin':
                                    $icon = 'fa-user-shield';
                                    break;
                                case 'director':
                                    $icon = 'fa-user-tie';
                                    break;
                                case 'docente':
                                    $icon = 'fa-chalkboard-teacher';
                                    break;
                                case 'auxiliar':
                                    $icon = 'fa-user-check';
                                    break;
                                case 'alumno':
                                    $icon = 'fa-user-graduate';
                                    break;
                            }
                            ?>
                            <i class="fas <?php echo $icon; ?> text-primary"></i>
                        </div>
                        <h5 class="card-title"><?php echo ucfirst($rol['nombre']); ?></h5>
                        <p class="card-text"><?php echo $rol['descripcion']; ?></p>
                        <div class="d-flex justify-content-center">
                            <span class="badge bg-info mb-3">
                                <?php echo $rol['total_usuarios']; ?> usuarios
                            </span>
                        </div>
                        <div class="action-buttons">
                            <button class="btn btn-sm btn-info" onclick="editarRol(<?php echo $rol['id']; ?>)">
                                <i class="fas fa-edit"></i> Editar
                            </button>
                            <?php if ($rol['total_usuarios'] == 0): ?>
                            <button class="btn btn-sm btn-danger" onclick="eliminarRol(<?php echo $rol['id']; ?>)">
                                <i class="fas fa-trash"></i> Eliminar
                            </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Modal Nuevo Rol -->
    <div class="modal fade" id="nuevoRolModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nuevo Rol</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="nuevoRolForm" action="procesar_rol.php" method="POST">
                    <input type="hidden" name="action" value="crear">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                        </div>
                        <div class="mb-3">
                            <label for="descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="3"></textarea>
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

    <!-- Modal Editar Rol -->
    <div class="modal fade" id="editarRolModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Rol</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editarRolForm" action="procesar_rol.php" method="POST">
                    <input type="hidden" name="action" value="editar">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_nombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="edit_nombre" name="nombre" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_descripcion" class="form-label">Descripción</label>
                            <textarea class="form-control" id="edit_descripcion" name="descripcion" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editarRol(id) {
            // Obtener datos del rol mediante AJAX
            fetch(`obtener_rol.php?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('edit_id').value = data.id;
                    document.getElementById('edit_nombre').value = data.nombre;
                    document.getElementById('edit_descripcion').value = data.descripcion;
                    
                    new bootstrap.Modal(document.getElementById('editarRolModal')).show();
                });
        }

        function eliminarRol(id) {
            if (confirm('¿Está seguro de eliminar este rol?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'procesar_rol.php';
                
                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'eliminar';
                
                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'id';
                idInput.value = id;
                
                form.appendChild(actionInput);
                form.appendChild(idInput);
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html> 