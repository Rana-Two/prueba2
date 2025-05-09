<?php
class User {
    private $conn;
    private $table_name = "usuarios";

    public $id;
    public $nombre;
    public $apellido;
    public $email;
    public $password;
    public $rol_id;
    public $estado;
    public $ultimo_acceso;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Crear nuevo usuario
    public function create() {
        try {
            // Verificar si el email ya existe
            if ($this->emailExists()) {
                return false;
            }

            // Hash de la contraseña
            $password_hash = password_hash($this->password, PASSWORD_DEFAULT);

            // Query para insertar
            $query = "INSERT INTO " . $this->table_name . "
                    (nombre, apellido, email, password, rol_id, estado)
                    VALUES
                    (:nombre, :apellido, :email, :password, :rol_id, :estado)";

            $stmt = $this->conn->prepare($query);

            // Sanitizar datos
            $this->nombre = htmlspecialchars(strip_tags($this->nombre));
            $this->apellido = htmlspecialchars(strip_tags($this->apellido));
            $this->email = htmlspecialchars(strip_tags($this->email));

            // Vincular valores
            $stmt->bindParam(":nombre", $this->nombre);
            $stmt->bindParam(":apellido", $this->apellido);
            $stmt->bindParam(":email", $this->email);
            $stmt->bindParam(":password", $password_hash);
            $stmt->bindParam(":rol_id", $this->rol_id);
            $stmt->bindParam(":estado", $this->estado);

            // Ejecutar query
            if ($stmt->execute()) {
                $this->id = $this->conn->lastInsertId();
                return true;
            }
            return false;
        } catch (PDOException $e) {
            return false;
        }
    }

    // Verificar si el email existe
    public function emailExists() {
        $query = "SELECT id FROM " . $this->table_name . " WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":email", $this->email);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    // Login
    public function login() {
        try {
            $query = "SELECT u.*, r.nombre as rol_nombre 
                     FROM " . $this->table_name . " u 
                     JOIN roles r ON u.rol_id = r.id 
                     WHERE u.email = :email AND u.estado = 1";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":email", $this->email);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (password_verify($this->password, $row['password'])) {
                    // Actualizar último acceso
                    $this->updateLastAccess($row['id']);
                    
                    return $row;
                }
            }
            return false;
        } catch (PDOException $e) {
            return false;
        }
    }

    // Actualizar último acceso
    private function updateLastAccess($user_id) {
        $query = "UPDATE " . $this->table_name . " 
                 SET ultimo_acceso = NOW() 
                 WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(":id", $user_id);
        $stmt->execute();
    }

    // Obtener todos los usuarios
    public function getAll() {
        try {
            $query = "SELECT u.*, r.nombre as rol_nombre 
                     FROM " . $this->table_name . " u 
                     JOIN roles r ON u.rol_id = r.id 
                     ORDER BY u.nombre, u.apellido";

            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            return $stmt;
        } catch (PDOException $e) {
            return false;
        }
    }

    // Obtener un usuario por ID
    public function getById() {
        try {
            $query = "SELECT u.*, r.nombre as rol_nombre 
                     FROM " . $this->table_name . " u 
                     JOIN roles r ON u.rol_id = r.id 
                     WHERE u.id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $this->id);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                return $stmt->fetch(PDO::FETCH_ASSOC);
            }
            return false;
        } catch (PDOException $e) {
            return false;
        }
    }

    // Actualizar usuario
    public function update() {
        try {
            $query = "UPDATE " . $this->table_name . "
                    SET nombre = :nombre,
                        apellido = :apellido,
                        email = :email,
                        rol_id = :rol_id,
                        estado = :estado
                    WHERE id = :id";

            $stmt = $this->conn->prepare($query);

            // Sanitizar datos
            $this->nombre = htmlspecialchars(strip_tags($this->nombre));
            $this->apellido = htmlspecialchars(strip_tags($this->apellido));
            $this->email = htmlspecialchars(strip_tags($this->email));

            // Vincular valores
            $stmt->bindParam(":nombre", $this->nombre);
            $stmt->bindParam(":apellido", $this->apellido);
            $stmt->bindParam(":email", $this->email);
            $stmt->bindParam(":rol_id", $this->rol_id);
            $stmt->bindParam(":estado", $this->estado);
            $stmt->bindParam(":id", $this->id);

            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    // Cambiar contraseña
    public function changePassword() {
        try {
            $password_hash = password_hash($this->password, PASSWORD_DEFAULT);

            $query = "UPDATE " . $this->table_name . "
                    SET password = :password
                    WHERE id = :id";

            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":password", $password_hash);
            $stmt->bindParam(":id", $this->id);

            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }

    // Eliminar usuario
    public function delete() {
        try {
            $query = "DELETE FROM " . $this->table_name . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(":id", $this->id);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
}
?> 