<?php
require_once '../../includes/functions.php';
checkRole('director');

require_once '../../config/database.php';
require_once '../../config/config.php';

$database = new Database();
$db = $database->getConnection();

if (isset($_GET['id']) && isset($_GET['rol'])) {
    $id = (int)$_GET['id'];
    $rol = sanitize($_GET['rol']);

    try {
        // Verificar que el usuario existe y pertenece al rol especificado
        $query = "SELECT u.id FROM usuarios u 
                 JOIN roles r ON u.rol_id = r.id 
                 WHERE u.id = :id AND r.nombre = :rol";
        $stmt = $db->prepare($query);
        $stmt->execute(['id' => $id, 'rol' => $rol]);

        if ($stmt->rowCount() === 0) {
            setMessage('Usuario no encontrado', 'danger');
            redirect('usuarios.php?rol=' . $rol);
        }

        // Eliminar el usuario
        $query = "DELETE FROM usuarios WHERE id = :id";
        $stmt = $db->prepare($query);
        
        if ($stmt->execute(['id' => $id])) {
            setMessage('Usuario eliminado exitosamente', 'success');
        } else {
            setMessage('Error al eliminar el usuario', 'danger');
        }
    } catch (PDOException $e) {
        setMessage('Error en la base de datos: ' . $e->getMessage(), 'danger');
    }

    redirect('usuarios.php?rol=' . $rol);
} else {
    redirect('dashboard.php');
} 