<?php
session_start();
require_once '../../config/database.php';
require_once '../../config/config.php';
require_once '../../classes/User.php';

// Verificar si el usuario está logueado y es administrador
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../../login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'crear':
            $nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING);
            $apellido = filter_input(INPUT_POST, 'apellido', FILTER_SANITIZE_STRING);
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $password = $_POST['password'];
            $rol_id = filter_input(INPUT_POST, 'rol_id', FILTER_SANITIZE_NUMBER_INT);

            try {
                // Verificar si el email ya existe
                $query = "SELECT id FROM usuarios WHERE email = :email";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':email', $email);
                $stmt->execute();

                if ($stmt->rowCount() > 0) {
                    $_SESSION['mensaje'] = "El email ya está registrado";
                    $_SESSION['tipo_mensaje'] = "danger";
                    header("Location: usuarios.php");
                    exit();
                }

                // Crear nuevo usuario
                $query = "INSERT INTO usuarios (nombre, apellido, email, password, rol_id) 
                         VALUES (:nombre, :apellido, :email, :password, :rol_id)";
                $stmt = $db->prepare($query);
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                
                $stmt->bindParam(':nombre', $nombre);
                $stmt->bindParam(':apellido', $apellido);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':password', $password_hash);
                $stmt->bindParam(':rol_id', $rol_id);
                
                if ($stmt->execute()) {
                    $_SESSION['mensaje'] = "Usuario creado correctamente";
                    $_SESSION['tipo_mensaje'] = "success";
                } else {
                    $_SESSION['mensaje'] = "Error al crear el usuario";
                    $_SESSION['tipo_mensaje'] = "danger";
                }
            } catch (PDOException $e) {
                $_SESSION['mensaje'] = "Error al crear el usuario: " . $e->getMessage();
                $_SESSION['tipo_mensaje'] = "danger";
            }
            break;

        case 'editar':
            $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
            $nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING);
            $apellido = filter_input(INPUT_POST, 'apellido', FILTER_SANITIZE_STRING);
            $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
            $rol_id = filter_input(INPUT_POST, 'rol_id', FILTER_SANITIZE_NUMBER_INT);

            try {
                // Verificar si el email ya existe (excluyendo el usuario actual)
                $query = "SELECT id FROM usuarios WHERE email = :email AND id != :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':id', $id);
                $stmt->execute();

                if ($stmt->rowCount() > 0) {
                    $_SESSION['mensaje'] = "El email ya está registrado";
                    $_SESSION['tipo_mensaje'] = "danger";
                    header("Location: usuarios.php");
                    exit();
                }

                // Actualizar usuario
                $query = "UPDATE usuarios SET nombre = :nombre, apellido = :apellido, 
                         email = :email, rol_id = :rol_id WHERE id = :id";
                $stmt = $db->prepare($query);
                
                $stmt->bindParam(':nombre', $nombre);
                $stmt->bindParam(':apellido', $apellido);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':rol_id', $rol_id);
                $stmt->bindParam(':id', $id);
                
                if ($stmt->execute()) {
                    $_SESSION['mensaje'] = "Usuario actualizado correctamente";
                    $_SESSION['tipo_mensaje'] = "success";
                } else {
                    $_SESSION['mensaje'] = "Error al actualizar el usuario";
                    $_SESSION['tipo_mensaje'] = "danger";
                }
            } catch (PDOException $e) {
                $_SESSION['mensaje'] = "Error al actualizar el usuario: " . $e->getMessage();
                $_SESSION['tipo_mensaje'] = "danger";
            }
            break;

        case 'eliminar':
            $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);

            try {
                $query = "DELETE FROM usuarios WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':id', $id);
                
                if ($stmt->execute()) {
                    $_SESSION['mensaje'] = "Usuario eliminado correctamente";
                    $_SESSION['tipo_mensaje'] = "success";
                } else {
                    $_SESSION['mensaje'] = "Error al eliminar el usuario";
                    $_SESSION['tipo_mensaje'] = "danger";
                }
            } catch (PDOException $e) {
                $_SESSION['mensaje'] = "Error al eliminar el usuario: " . $e->getMessage();
                $_SESSION['tipo_mensaje'] = "danger";
            }
            break;
    }

    header("Location: usuarios.php");
    exit();
}
?> 