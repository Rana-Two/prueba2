<?php
session_start();
require_once 'config/database.php';
require_once 'config/config.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Redirigir según el rol
switch ($_SESSION['user_role']) {
    case 'admin':
        header("Location: views/admin/dashboard.php");
        break;
    case 'director':
        header("Location: views/director/dashboard.php");
        break;
    case 'auxiliar':
        header("Location: views/auxiliar/dashboard.php");
        break;
    case 'alumno':
        header("Location: views/alumno/dashboard.php");
        break;
    default:
        // Si el rol no es válido, cerrar sesión
        session_destroy();
        header("Location: login.php");
        break;
}
exit(); 