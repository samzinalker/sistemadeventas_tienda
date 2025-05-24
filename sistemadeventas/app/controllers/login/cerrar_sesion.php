<?php
include ('../../config.php');

// Verificar si la sesión está iniciada antes de manipularla
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Limpiar específicamente las variables de sesión
if (isset($_SESSION['sesion_email']) || isset($_SESSION['id_usuario']) || isset($_SESSION['rol'])) {
    unset($_SESSION['sesion_email']);
    unset($_SESSION['id_usuario']);
    unset($_SESSION['rol']);
}

// Destruir la sesión completamente
session_destroy();

// Redirigir al usuario a la página de inicio o login
header('Location: '.$URL.'/');
exit();
?>