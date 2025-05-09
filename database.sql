-- Crear tabla de clases
CREATE TABLE IF NOT EXISTS clases (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Crear tabla de relación alumnos-clases
CREATE TABLE IF NOT EXISTS alumnos_clases (
    id INT PRIMARY KEY AUTO_INCREMENT,
    alumno_id INT NOT NULL,
    clase_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (alumno_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (clase_id) REFERENCES clases(id) ON DELETE CASCADE,
    UNIQUE KEY unique_alumno_clase (alumno_id, clase_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Crear tabla de asistencias
CREATE TABLE IF NOT EXISTS asistencias (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertar algunos datos de ejemplo para clases
INSERT INTO clases (nombre, docente_id, dia_semana, hora_inicio, hora_fin, aula) VALUES
('Matemáticas Básicas', 1, 'Lunes', '08:00:00', '10:00:00', 'Aula 101'),
('Física I', 1, 'Martes', '10:00:00', '12:00:00', 'Aula 102'),
('Química General', 2, 'Miércoles', '14:00:00', '16:00:00', 'Laboratorio 1'),
('Programación I', 2, 'Jueves', '16:00:00', '18:00:00', 'Aula 103'),
('Inglés Básico', 3, 'Viernes', '09:00:00', '11:00:00', 'Aula 104');

-- Insertar algunas relaciones alumnos-clases
INSERT INTO alumnos_clases (alumno_id, clase_id) VALUES
(4, 1), -- Alumno 4 en Matemáticas Básicas
(4, 2), -- Alumno 4 en Física I
(5, 1), -- Alumno 5 en Matemáticas Básicas
(5, 3), -- Alumno 5 en Química General
(6, 2), -- Alumno 6 en Física I
(6, 4); -- Alumno 6 en Programación I

-- Insertar algunas asistencias de ejemplo
INSERT INTO asistencias (alumno_id, clase_id, fecha, estado, observacion) VALUES
(4, 1, CURDATE(), 'presente', 'Asistencia normal'),
(4, 2, CURDATE(), 'tardanza', 'Llegó 15 minutos tarde'),
(5, 1, CURDATE(), 'ausente', 'Justificado por enfermedad'),
(5, 3, CURDATE(), 'presente', 'Asistencia normal'),
(6, 2, CURDATE(), 'presente', 'Asistencia normal'),
(6, 4, CURDATE(), 'presente', 'Asistencia normal'); 