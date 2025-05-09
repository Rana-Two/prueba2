<?php
require_once '../../includes/functions.php';
checkRole('director');

require_once '../../config/database.php';
require_once '../../config/config.php';

$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = sanitize($_POST['nombre']);
    $apellido = sanitize($_POST['apellido']);
    $email = sanitize($_POST['email']);
    $password = $_POST['password'];
    $rol_id = (int)$_POST['rol_id'];
    $id = isset($_POST['id']) ? (int)$_POST['id'] : null;

    try {
        // Verificar si el email ya existe
        $query = "SELECT id FROM usuarios WHERE email = :email";
        if ($id) {
            $query .= " AND id != :id";
        }
        $stmt = $db->prepare($query);
        $stmt->bindParam(':email', $email);
        if ($id) {
            $stmt->bindParam(':id', $id);
        }
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            setMessage('El email ya está registrado', 'danger');
            redirect('usuarios.php?rol=' . getRoleName($rol_id));
        }

        if ($id) {
            // Actualizar usuario existente
            if (!empty($password)) {
                $query = "UPDATE usuarios SET nombre = :nombre, apellido = :apellido, 
                         email = :email, password = :password, rol_id = :rol_id 
                         WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':password', password_hash($password, PASSWORD_DEFAULT));
            } else {
                $query = "UPDATE usuarios SET nombre = :nombre, apellido = :apellido, 
                         email = :email, rol_id = :rol_id WHERE id = :id";
                $stmt = $db->prepare($query);
            }
            $stmt->bindParam(':id', $id);
        } else {
            // Crear nuevo usuario
            $query = "INSERT INTO usuarios (nombre, apellido, email, password, rol_id) 
                     VALUES (:nombre, :apellido, :email, :password, :rol_id)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':password', password_hash($password, PASSWORD_DEFAULT));
        }

        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':apellido', $apellido);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':rol_id', $rol_id);

        if ($stmt->execute()) {
            setMessage('Usuario ' . ($id ? 'actualizado' : 'creado') . ' exitosamente', 'success');
        } else {
            setMessage('Error al ' . ($id ? 'actualizar' : 'crear') . ' el usuario', 'danger');
        }
    } catch (PDOException $e) {
        setMessage('Error en la base de datos: ' . $e->getMessage(), 'danger');
    }

    redirect('usuarios.php?rol=' . getRoleName($rol_id));
} else {
    redirect('dashboard.php');
} 