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

// Obtener datos del usuario
$query = "SELECT u.*, r.nombre as rol_nombre 
          FROM usuarios u 
          JOIN roles r ON u.rol_id = r.id 
          WHERE u.id = :id";
$stmt = $db->prepare($query);
$stmt->bindParam(':id', $_SESSION['user_id']);
$stmt->execute();
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

// Procesar actualización de perfil
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'actualizar_perfil') {
            $nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING);
            $apellido = filter_input(INPUT_POST, 'apellido', FILTER_SANITIZE_STRING);
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

            try {
                $query = "UPDATE usuarios SET nombre = :nombre, apellido = :apellido, email = :email WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':nombre', $nombre);
                $stmt->bindParam(':apellido', $apellido);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':id', $_SESSION['user_id']);
                
                if ($stmt->execute()) {
                    $_SESSION['mensaje'] = "Perfil actualizado correctamente";
                    $_SESSION['tipo_mensaje'] = "success";
                    header("Location: perfil.php");
                    exit();
                }
            } catch (PDOException $e) {
                $_SESSION['mensaje'] = "Error al actualizar el perfil";
                $_SESSION['tipo_mensaje'] = "danger";
            }
        } elseif ($_POST['action'] === 'cambiar_password') {
            $password_actual = $_POST['password_actual'];
            $password_nuevo = $_POST['password_nuevo'];
            $password_confirmar = $_POST['password_confirmar'];

            if ($password_nuevo === $password_confirmar) {
                try {
                    $query = "SELECT password FROM usuarios WHERE id = :id";
                    $stmt = $db->prepare($query);
                    $stmt->bindParam(':id', $_SESSION['user_id']);
                    $stmt->execute();
                    $usuario_db = $stmt->fetch(PDO::FETCH_ASSOC);

                    if (password_verify($password_actual, $usuario_db['password'])) {
                        $password_hash = password_hash($password_nuevo, PASSWORD_DEFAULT);
                        $query = "UPDATE usuarios SET password = :password WHERE id = :id";
                        $stmt = $db->prepare($query);
                        $stmt->bindParam(':password', $password_hash);
                        $stmt->bindParam(':id', $_SESSION['user_id']);
                        
                        if ($stmt->execute()) {
                            $_SESSION['mensaje'] = "Contraseña actualizada correctamente";
                            $_SESSION['tipo_mensaje'] = "success";
                            header("Location: perfil.php");
                            exit();
                        }
                    } else {
                        $_SESSION['mensaje'] = "La contraseña actual es incorrecta";
                        $_SESSION['tipo_mensaje'] = "danger";
                    }
                } catch (PDOException $e) {
                    $_SESSION['mensaje'] = "Error al actualizar la contraseña";
                    $_SESSION['tipo_mensaje'] = "danger";
                }
            } else {
                $_SESSION['mensaje'] = "Las contraseñas nuevas no coinciden";
                $_SESSION['tipo_mensaje'] = "danger";
            }
        }
    }
}

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
    <title>Mi Perfil - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="../../assets/css/style.css" rel="stylesheet">
    <style>
        .profile-header {
            background: linear-gradient(135deg, #4b6cb7 0%, #182848 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }
        .profile-avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 5px solid white;
            box-shadow: 0 0 20px rgba(0,0,0,0.2);
            background-color: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 4rem;
            color: #6c757d;
        }
        .profile-stats {
            background: white;
            border-radius: 10px;
            padding: 1rem;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .profile-stats .stat-item {
            text-align: center;
            padding: 1rem;
            border-right: 1px solid #dee2e6;
        }
        .profile-stats .stat-item:last-child {
            border-right: none;
        }
        .profile-stats .stat-value {
            font-size: 1.5rem;
            font-weight: bold;
            color: #4b6cb7;
        }
        .profile-stats .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
        }
        .profile-content {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .nav-pills .nav-link {
            color: #495057;
        }
        .nav-pills .nav-link.active {
            background-color: #4b6cb7;
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
                        <a class="nav-link" href="roles.php">Roles</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?php echo $_SESSION['user_name']; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item active" href="perfil.php">Mi Perfil</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../../logout.php">Cerrar Sesión</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="profile-header">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-3 text-center">
                    <div class="profile-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                </div>
                <div class="col-md-9">
                    <h1><?php echo $usuario['nombre'] . ' ' . $usuario['apellido']; ?></h1>
                    <p class="mb-0">
                        <i class="fas fa-envelope"></i> <?php echo $usuario['email']; ?> |
                        <i class="fas fa-user-tag"></i> <?php echo ucfirst($usuario['rol_nombre']); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="container">
        <?php if ($mensaje): ?>
        <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show" role="alert">
            <?php echo $mensaje; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="row mb-4">
            <div class="col-md-12">
                <div class="profile-stats">
                    <div class="row">
                        <div class="col-md-4 stat-item">
                            <div class="stat-value">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <div class="stat-label">Último Acceso</div>
                        </div>
                        <div class="col-md-4 stat-item">
                            <div class="stat-value">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="stat-label">Tiempo en Sesión</div>
                        </div>
                        <div class="col-md-4 stat-item">
                            <div class="stat-value">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <div class="stat-label">Nivel de Acceso</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-3">
                <div class="profile-content mb-4">
                    <div class="nav flex-column nav-pills" role="tablist">
                        <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#informacion">
                            <i class="fas fa-user-circle"></i> Información Personal
                        </button>
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#seguridad">
                            <i class="fas fa-lock"></i> Seguridad
                        </button>
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#actividad">
                            <i class="fas fa-history"></i> Actividad Reciente
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-md-9">
                <div class="profile-content">
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="informacion">
                            <h3 class="mb-4">Información Personal</h3>
                            <form action="perfil.php" method="POST">
                                <input type="hidden" name="action" value="actualizar_perfil">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="nombre" class="form-label">Nombre</label>
                                        <input type="text" class="form-control" id="nombre" name="nombre" 
                                               value="<?php echo $usuario['nombre']; ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="apellido" class="form-label">Apellido</label>
                                        <input type="text" class="form-control" id="apellido" name="apellido" 
                                               value="<?php echo $usuario['apellido']; ?>" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Correo Electrónico</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo $usuario['email']; ?>" required>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Guardar Cambios
                                </button>
                            </form>
                        </div>
                        <div class="tab-pane fade" id="seguridad">
                            <h3 class="mb-4">Cambiar Contraseña</h3>
                            <form action="perfil.php" method="POST">
                                <input type="hidden" name="action" value="cambiar_password">
                                <div class="mb-3">
                                    <label for="password_actual" class="form-label">Contraseña Actual</label>
                                    <input type="password" class="form-control" id="password_actual" 
                                           name="password_actual" required>
                                </div>
                                <div class="mb-3">
                                    <label for="password_nuevo" class="form-label">Nueva Contraseña</label>
                                    <input type="password" class="form-control" id="password_nuevo" 
                                           name="password_nuevo" required>
                                </div>
                                <div class="mb-3">
                                    <label for="password_confirmar" class="form-label">Confirmar Nueva Contraseña</label>
                                    <input type="password" class="form-control" id="password_confirmar" 
                                           name="password_confirmar" required>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-key"></i> Cambiar Contraseña
                                </button>
                            </form>
                        </div>
                        <div class="tab-pane fade" id="actividad">
                            <h3 class="mb-4">Actividad Reciente</h3>
                            <div class="list-group">
                                <div class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h5 class="mb-1">Inicio de Sesión</h5>
                                        <small class="text-muted">Hace 5 minutos</small>
                                    </div>
                                    <p class="mb-1">Sesión iniciada desde Chrome en Windows</p>
                                </div>
                                <div class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h5 class="mb-1">Actualización de Perfil</h5>
                                        <small class="text-muted">Hace 2 horas</small>
                                    </div>
                                    <p class="mb-1">Información personal actualizada</p>
                                </div>
                                <div class="list-group-item">
                                    <div class="d-flex w-100 justify-content-between">
                                        <h5 class="mb-1">Cambio de Contraseña</h5>
                                        <small class="text-muted">Hace 3 días</small>
                                    </div>
                                    <p class="mb-1">Contraseña actualizada exitosamente</p>
                                </div>
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