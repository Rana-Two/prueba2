<?php
class Database {
    private $host = "localhost";
    private $db_name = "sistema_asistencia";
    private $username = "root";
    private $password = "";
    public $conn;

    public function __construct() {
        try {
            // Primero conectar sin base de datos
            $this->conn = new PDO(
                "mysql:host=" . $this->host,
                $this->username,
                $this->password,
                array(
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_general_ci"
                )
            );

            // Verificar si la base de datos existe
            $query = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = :dbname";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":dbname", $this->db_name);
            $stmt->execute();

            if ($stmt->rowCount() == 0) {
                // Crear la base de datos si no existe
                $this->conn->exec("CREATE DATABASE IF NOT EXISTS " . $this->db_name . " CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
                $this->conn->exec("USE " . $this->db_name);
                
                // Crear las tablas necesarias
                $this->createTables();
            } else {
                $this->conn->exec("USE " . $this->db_name);
            }
        } catch(PDOException $e) {
            echo "Error de conexión: " . $e->getMessage();
        }
    }

    private function createTables() {
        // Tabla de roles
        $this->conn->exec("CREATE TABLE IF NOT EXISTS roles (
            id INT PRIMARY KEY AUTO_INCREMENT,
            nombre VARCHAR(50) NOT NULL UNIQUE,
            descripcion TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

        // Tabla de usuarios
        $this->conn->exec("CREATE TABLE IF NOT EXISTS usuarios (
            id INT PRIMARY KEY AUTO_INCREMENT,
            nombre VARCHAR(100) NOT NULL,
            apellido VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            rol_id INT NOT NULL,
            estado BOOLEAN DEFAULT TRUE,
            reset_token VARCHAR(64) NULL,
            reset_expiry DATETIME NULL,
            ultimo_acceso TIMESTAMP NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (rol_id) REFERENCES roles(id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

        // Insertar roles básicos si no existen
        $stmt = $this->conn->query("SELECT COUNT(*) FROM roles");
        if ($stmt->fetchColumn() == 0) {
            $this->conn->exec("INSERT INTO roles (nombre, descripcion) VALUES
                ('admin', 'Administrador del sistema'),
                ('director', 'Director de colegio'),
                ('auxiliar', 'Auxiliar de control'),
                ('alumno', 'Estudiante')");
        }

        // Verificar si existe el usuario administrador
        $stmt = $this->conn->query("SELECT COUNT(*) FROM usuarios WHERE email = 'admin@sistema.com'");
        if ($stmt->fetchColumn() == 0) {
            $password_hash = password_hash('Admin123!', PASSWORD_DEFAULT);
            $this->conn->exec("INSERT INTO usuarios (nombre, apellido, email, password, rol_id, estado) VALUES
                ('Admin', 'Sistema', 'admin@sistema.com', '$password_hash', 1, 1)");
        }
    }

    public function getConnection() {
        return $this->conn;
    }
}
?> 