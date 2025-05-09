<?php
function checkRole($required_role) {
    session_start();
    if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== $required_role) {
        header("Location: ../../login.php");
        exit();
    }
}

function getRoleName($role) {
    $roles = [
        'admin' => 'Administrador',
        'director' => 'Director',
        'docente' => 'Docente',
        'auxiliar' => 'Auxiliar',
        'alumno' => 'Alumno'
    ];
    return $roles[$role] ?? ucfirst($role);
}

function formatDate($date) {
    return date('d/m/Y H:i', strtotime($date));
}

function getCurrentUser() {
    if (!isset($_SESSION['user_id'])) {
        return null;
    }
    
    require_once __DIR__ . '/../config/database.php';
    $database = new Database();
    $db = $database->getConnection();
    
    $query = "SELECT u.*, r.nombre as rol_nombre 
              FROM usuarios u 
              JOIN roles r ON u.rol_id = r.id 
              WHERE u.id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $_SESSION['user_id']);
    $stmt->execute();
    
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
?> 