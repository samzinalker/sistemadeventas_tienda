<?php
// Asumimos que la sesión ya está iniciada por el script que incluye este (la vista)
// y que config.php (que define $URL) también ha sido incluido.
// if (session_status() === PHP_SESSION_NONE) { session_start(); } // Ya no es estrictamente necesario aquí si la vista lo hace.

global $URL; // Para asegurar acceso a $URL
if (!isset($URL)) {
    die("Error crítico: La variable de configuración $URL no está disponible en layout/permisos.php.");
}

// Verificar si el usuario tiene una sesión activa
if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['rol'])) {
    // Establecer mensaje SOLO SI NO HAY OTRO MENSAJE MÁS IMPORTANTE YA ESTABLECIDO
    if (!isset($_SESSION['mensaje'])) {
        $_SESSION['mensaje'] = "Debes iniciar sesión para acceder a esta página.";
        $_SESSION['icono'] = "error";
    }
    header('Location: ' . rtrim($URL, '/') . '/login/');
    exit();
}

// Verificar si el rol del usuario es 'administrador'
// Este script asume que es para páginas de administración.
// Si tienes otras lógicas de permisos, necesitarás ajustarlo.
if (strtolower(trim($_SESSION['rol'])) !== 'administrador') {
    // Establecer mensaje SOLO SI NO HAY OTRO MENSAJE MÁS IMPORTANTE
    if (!isset($_SESSION['mensaje'])) {
        $_SESSION['mensaje'] = "No tienes permisos para acceder a esta página.";
        $_SESSION['icono'] = "error";
    }
    header('Location: ' . rtrim($URL, '/') . '/index.php'); // Redirigir a una página principal o dashboard
    exit();
}
?>