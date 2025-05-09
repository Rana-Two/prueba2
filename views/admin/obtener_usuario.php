<?php
session_start();
require_once '../../config/database.php';
require_once '../../config/config.php';

// Verificar si el usuario está logueado y es administrador
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    exit('No autorizado');
}

if (!isset($_GET['id'])) {
    http_response_code(400);
    exit('ID no proporcionado');
}

$database = new Database();
$db = $database->getConnection();

try {
    $query = "SELECT * FROM usuarios WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->execute(['id' => $_GET['id']]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        http_response_code(404);
        exit('Usuario no encontrado');
    }

    header('Content-Type: application/json');
    echo json_encode($usuario);
} catch (PDOException $e) {
    http_response_code(500);
    exit('Error al obtener el usuario');
} 