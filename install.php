<?php
require_once 'config/database.php';
require_once 'config/config.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    
    echo "<h2>Iniciando instalación...</h2>";
    
    // Verificar si la base de datos existe
    $db->exec("CREATE DATABASE IF NOT EXISTS sistema_asistencia");
    $db->exec("USE sistema_asistencia");
    echo "<p>✓ Base de datos verificada</p>";
    
    // Verificar si estamos en la base de datos correcta
    $current_db = $db->query("SELECT DATABASE()")->fetchColumn();
    if ($current_db !== 'sistema_asistencia') {
        throw new Exception("No se pudo seleccionar la base de datos 'sistema_asistencia'");
    }
    echo "<p>✓ Base de datos seleccionada: " . $current_db . "</p>";
    
    // Verificar si la tabla usuarios existe
    $result = $db->query("SHOW TABLES LIKE 'usuarios'");
    if ($result->rowCount() == 0) {
        throw new Exception("La tabla 'usuarios' no existe. Por favor, crea primero la tabla de usuarios.");
    }
    echo "<p>✓ Tabla 'usuarios' verificada</p>";
    
    // Verificar si la tabla roles existe
    $result = $db->query("SHOW TABLES LIKE 'roles'");
    if ($result->rowCount() == 0) {
        throw new Exception("La tabla 'roles' no existe. Por favor, crea primero la tabla de roles.");
    }
    echo "<p>✓ Tabla 'roles' verificada</p>";
    
    // Crear tabla de clases
    $db->exec("DROP TABLE IF EXISTS clases");
    $db->exec("CREATE TABLE clases (
        id INT PRIMARY KEY AUTO_INCREMENT,
        nombre VARCHAR(100) NOT NULL,
        docente_id INT NOT NULL,
        dia_semana ENUM('Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo') NOT NULL,
        hora_inicio TIME NOT NULL,
        hora_fin TIME NOT NULL,
        aula VARCHAR(50) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (docente_id) REFERENCES usuarios(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // Verificar si la tabla clases se creó correctamente
    $result = $db->query("SHOW TABLES LIKE 'clases'");
    if ($result->rowCount() == 0) {
        throw new Exception("Error al crear la tabla 'clases'");
    }
    echo "<p>✓ Tabla 'clases' creada</p>";
    
    // Crear tabla de relación alumnos-clases
    $db->exec("DROP TABLE IF EXISTS alumnos_clases");
    $db->exec("CREATE TABLE alumnos_clases (
        id INT PRIMARY KEY AUTO_INCREMENT,
        alumno_id INT NOT NULL,
        clase_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (alumno_id) REFERENCES usuarios(id) ON DELETE CASCADE,
        FOREIGN KEY (clase_id) REFERENCES clases(id) ON DELETE CASCADE,
        UNIQUE KEY unique_alumno_clase (alumno_id, clase_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // Verificar si la tabla alumnos_clases se creó correctamente
    $result = $db->query("SHOW TABLES LIKE 'alumnos_clases'");
    if ($result->rowCount() == 0) {
        throw new Exception("Error al crear la tabla 'alumnos_clases'");
    }
    echo "<p>✓ Tabla 'alumnos_clases' creada</p>";
    
    // Crear tabla de asistencias
    $db->exec("DROP TABLE IF EXISTS asistencias");
    $db->exec("CREATE TABLE asistencias (
        id INT PRIMARY KEY AUTO_INCREMENT,
        alumno_id INT NOT NULL,
        clase_id INT NOT NULL,
        fecha DATE NOT NULL,
        estado ENUM('presente', 'ausente', 'tardanza') NOT NULL,
        observacion TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (alumno_id) REFERENCES usuarios(id) ON DELETE CASCADE,
        FOREIGN KEY (clase_id) REFERENCES clases(id) ON DELETE CASCADE,
        UNIQUE KEY unique_asistencia (alumno_id, clase_id, fecha)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    
    // Verificar si la tabla asistencias se creó correctamente
    $result = $db->query("SHOW TABLES LIKE 'asistencias'");
    if ($result->rowCount() == 0) {
        throw new Exception("Error al crear la tabla 'asistencias'");
    }
    echo "<p>✓ Tabla 'asistencias' creada</p>";
    
    // Verificar si hay docentes en la tabla usuarios
    $stmt = $db->query("SELECT id FROM usuarios WHERE rol_id = (SELECT id FROM roles WHERE nombre = 'docente') LIMIT 1");
    if ($stmt->rowCount() == 0) {
        throw new Exception("No hay docentes en la base de datos. Por favor, crea al menos un docente primero.");
    }
    echo "<p>✓ Docentes verificados</p>";
    
    // Insertar datos de ejemplo para clases
    $db->exec("INSERT INTO clases (nombre, docente_id, dia_semana, hora_inicio, hora_fin, aula) VALUES
        ('Matemáticas Básicas', 1, 'Lunes', '08:00:00', '10:00:00', 'Aula 101'),
        ('Física I', 1, 'Martes', '10:00:00', '12:00:00', 'Aula 102'),
        ('Química General', 2, 'Miércoles', '14:00:00', '16:00:00', 'Laboratorio 1'),
        ('Programación I', 2, 'Jueves', '16:00:00', '18:00:00', 'Aula 103'),
        ('Inglés Básico', 3, 'Viernes', '09:00:00', '11:00:00', 'Aula 104')");
    echo "<p>✓ Datos de ejemplo insertados en la tabla 'clases'</p>";
    
    // Verificar si hay alumnos en la tabla usuarios
    $stmt = $db->query("SELECT id FROM usuarios WHERE rol_id = (SELECT id FROM roles WHERE nombre = 'alumno') LIMIT 1");
    if ($stmt->rowCount() > 0) {
        // Insertar relaciones alumnos-clases
        $db->exec("INSERT INTO alumnos_clases (alumno_id, clase_id) VALUES
            (4, 1), -- Alumno 4 en Matemáticas Básicas
            (4, 2), -- Alumno 4 en Física I
            (5, 1), -- Alumno 5 en Matemáticas Básicas
            (5, 3), -- Alumno 5 en Química General
            (6, 2), -- Alumno 6 en Física I
            (6, 4) -- Alumno 6 en Programación I
        ");
        echo "<p>✓ Relaciones alumnos-clases insertadas</p>";
        
        // Insertar asistencias de ejemplo
        $db->exec("INSERT INTO asistencias (alumno_id, clase_id, fecha, estado, observacion) VALUES
            (4, 1, CURDATE(), 'presente', 'Asistencia normal'),
            (4, 2, CURDATE(), 'tardanza', 'Llegó 15 minutos tarde'),
            (5, 1, CURDATE(), 'ausente', 'Justificado por enfermedad'),
            (5, 3, CURDATE(), 'presente', 'Asistencia normal'),
            (6, 2, CURDATE(), 'presente', 'Asistencia normal'),
            (6, 4, CURDATE(), 'presente', 'Asistencia normal')");
        echo "<p>✓ Datos de asistencias insertados</p>";
    }
    
    echo "<h3 style='color: green;'>✓ Instalación completada exitosamente</h3>";
    echo "<p>Las tablas han sido creadas y los datos de ejemplo han sido insertados.</p>";
    echo "<p><a href='views/director/reportes.php' class='btn btn-primary'>Ir a Reportes</a></p>";
    
} catch (Exception $e) {
    echo "<h3 style='color: red;'>Error durante la instalación:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "<p>Por favor, asegúrate de que la base de datos 'sistema_asistencia' existe y que tienes los permisos necesarios.</p>";
}
?> 