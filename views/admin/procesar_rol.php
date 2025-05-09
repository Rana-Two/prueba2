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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'crear':
            $nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING);
            $descripcion = filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_STRING);

            try {
                $query = "INSERT INTO roles (nombre, descripcion) VALUES (:nombre, :descripcion)";
                $stmt = $db->prepare($query);
                $stmt->execute([
                    'nombre' => $nombre,
                    'descripcion' => $descripcion
                ]);

                $_SESSION['mensaje'] = 'Rol creado correctamente.';
                $_SESSION['tipo_mensaje'] = 'success';
            } catch (PDOException $e) {
                $_SESSION['mensaje'] = 'Error al crear el rol: ' . $e->getMessage();
                $_SESSION['tipo_mensaje'] = 'danger';
            }
            break;

        case 'editar':
            $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);
            $nombre = filter_input(INPUT_POST, 'nombre', FILTER_SANITIZE_STRING);
            $descripcion = filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_STRING);

            try {
                $query = "UPDATE roles SET nombre = :nombre, descripcion = :descripcion WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->execute([
                    'nombre' => $nombre,
                    'descripcion' => $descripcion,
                    'id' => $id
                ]);

                $_SESSION['mensaje'] = 'Rol actualizado correctamente.';
                $_SESSION['tipo_mensaje'] = 'success';
            } catch (PDOException $e) {
                $_SESSION['mensaje'] = 'Error al actualizar el rol: ' . $e->getMessage();
                $_SESSION['tipo_mensaje'] = 'danger';
            }
            break;

        case 'eliminar':
            $id = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT);

            // Verificar si hay usuarios con este rol
            $query = "SELECT COUNT(*) as total FROM usuarios WHERE rol_id = :id";
            $stmt = $db->prepare($query);
            $stmt->execute(['id' => $id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result['total'] > 0) {
                $_SESSION['mensaje'] = 'No se puede eliminar el rol porque hay usuarios asignados a él.';
                $_SESSION['tipo_mensaje'] = 'danger';
            } else {
                try {
                    $query = "DELETE FROM roles WHERE id = :id";
                    $stmt = $db->prepare($query);
                    $stmt->execute(['id' => $id]);

                    $_SESSION['mensaje'] = 'Rol eliminado correctamente.';
                    $_SESSION['tipo_mensaje'] = 'success';
                } catch (PDOException $e) {
                    $_SESSION['mensaje'] = 'Error al eliminar el rol: ' . $e->getMessage();
                    $_SESSION['tipo_mensaje'] = 'danger';
                }
            }
            break;
    }
}

header("Location: roles.php");
exit(); 