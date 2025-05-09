<?php
session_start();
require_once 'config/database.php';
require_once 'config/config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

    if (empty($email)) {
        $error = 'Por favor, ingrese su correo electrónico';
    } else {
        try {
            $database = new Database();
            $db = $database->getConnection();

            // Verificar si el email existe
            $query = "SELECT id, nombre, apellido FROM usuarios WHERE email = :email";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':email', $email);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Generar token único
                $token = bin2hex(random_bytes(32));
                $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

                // Guardar token en la base de datos
                $query = "UPDATE usuarios SET reset_token = :token, reset_expiry = :expiry WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':token', $token);
                $stmt->bindParam(':expiry', $expiry);
                $stmt->bindParam(':id', $user['id']);
                $stmt->execute();

                // Enviar correo electrónico
                $reset_link = SITE_URL . "/reset-password.php?token=" . $token;
                $to = $email;
                $subject = "Recuperación de Contraseña - " . SITE_NAME;
                $message = "
                <html>
                <head>
                    <title>Recuperación de Contraseña</title>
                </head>
                <body>
                    <h2>Hola {$user['nombre']} {$user['apellido']},</h2>
                    <p>Has solicitado restablecer tu contraseña. Haz clic en el siguiente enlace para crear una nueva contraseña:</p>
                    <p><a href='{$reset_link}'>{$reset_link}</a></p>
                    <p>Este enlace expirará en 1 hora.</p>
                    <p>Si no solicitaste este cambio, por favor ignora este correo.</p>
                    <br>
                    <p>Saludos,<br>" . SITE_NAME . "</p>
                </body>
                </html>
                ";

                // Headers para correo HTML
                $headers = "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                $headers .= 'From: ' . SMTP_FROM_NAME . ' <' . SMTP_FROM . '>' . "\r\n";

                if (mail($to, $subject, $message, $headers)) {
                    $success = 'Se ha enviado un correo electrónico con instrucciones para restablecer tu contraseña.';
                } else {
                    $error = 'Error al enviar el correo electrónico. Por favor, intente más tarde.';
                }
            } else {
                // Por seguridad, mostrar el mismo mensaje aunque el email no exista
                $success = 'Si el correo electrónico existe en nuestra base de datos, recibirás instrucciones para restablecer tu contraseña.';
            }
        } catch (PDOException $e) {
            $error = 'Error en la base de datos: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            <h2 class="text-primary"><?php echo SITE_NAME; ?></h2>
                            <p class="text-muted">Recuperar Contraseña</p>
                        </div>

                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>

                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="email" class="form-label">Correo Electrónico</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control" id="email" name="email" required>
                                </div>
                                <div class="form-text">
                                    Ingresa el correo electrónico asociado a tu cuenta.
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i> Enviar Instrucciones
                                </button>
                            </div>
                        </form>

                        <div class="text-center mt-3">
                            <a href="login.php" class="text-muted">
                                <i class="fas fa-arrow-left"></i> Volver al inicio de sesión
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 