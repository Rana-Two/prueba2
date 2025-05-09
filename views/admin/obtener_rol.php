<?php
session_start();
require_once '../../config/database.php';

// Verificar si el usuario está logueado y es administrador
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'No autorizado']);
    exit();
}

// Verificar si se proporcionó un ID
if (!isset($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'ID no proporcionado']);
    exit();
}

try {
    $database = new Database();
    $db = $database->getConnection();

    $query = "SELECT * FROM roles WHERE id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $_GET['id']);
    $stmt->execute();

    $rol = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$rol) {
        http_response_code(404);
        echo json_encode(['error' => 'Rol no encontrado']);
        exit();
    }

    echo json_encode($rol);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Error al obtener el rol']);
}
?> 