<?php


// Verificar si el usuario tiene una sesión activa
if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['rol'])) {
    $_SESSION['mensaje'] = "Debes iniciar sesión para acceder a esta página.";
    $_SESSION['icono'] = "error";
    header('Location: /sistemadeventas/login');
    exit();
}

// Verificar si el rol del usuario es 'administrador'
if (strtolower(trim($_SESSION['rol'])) !== 'administrador') {
    $_SESSION['mensaje'] = "No tienes permisos para acceder a esta página.";
    $_SESSION['icono'] = "error";
    header('Location: /sistemadeventas/index.php');
    exit();
}
?>