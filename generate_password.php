<?php
require_once 'config/database.php';

// Datos del usuario administrador
$nombre = 'Admin';
$apellido = 'Sistema';
$email = 'admin@sistema.com';
$password = 'Admin123!';
$rol_id = 1;

try {
    // Generar hash de la contraseña
    $hash = password_hash($password, PASSWORD_DEFAULT);
    echo "Contraseña original: " . $password . "\n";
    echo "Hash generado: " . $hash . "\n";

    // Conectar a la base de datos
    $database = new Database();
    $db = $database->getConnection();

    // Verificar si el usuario ya existe
    $query = "SELECT id FROM usuarios WHERE email = :email";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        // Actualizar usuario existente
        $query = "UPDATE usuarios SET 
                 password = :password,
                 nombre = :nombre,
                 apellido = :apellido,
                 rol_id = :rol_id,
                 estado = 1
                 WHERE email = :email";
        $stmt = $db->prepare($query);
    } else {
        // Insertar nuevo usuario
        $query = "INSERT INTO usuarios (nombre, apellido, email, password, rol_id) 
                 VALUES (:nombre, :apellido, :email, :password, :rol_id)";
        $stmt = $db->prepare($query);
    }

    // Vincular parámetros
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':apellido', $apellido);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $hash);
    $stmt->bindParam(':rol_id', $rol_id);

    // Ejecutar la consulta
    if ($stmt->execute()) {
        echo "Usuario administrador creado/actualizado correctamente.\n";
        echo "Puedes iniciar sesión con:\n";
        echo "Email: " . $email . "\n";
        echo "Contraseña: " . $password . "\n";
    } else {
        echo "Error al crear/actualizar el usuario.\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?> 