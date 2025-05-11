<?php
// Archivo: app/controllers/compras/verificacion_usuario.php

// 1. Asegurar que la sesión está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Verificar que el usuario está autenticado
if (!isset($_SESSION['id_usuario'])) {
    // Redireccionar al login si no hay sesión
    $_SESSION['mensaje'] = "Debe iniciar sesión para acceder";
    $_SESSION['icono'] = "error";
    header('Location: ' . $URL . '/login.php');
    exit();
}

// 3. Obtener el nombre del archivo actual para verificación condicional
$ruta_actual = $_SERVER['SCRIPT_NAME'];
$nombre_archivo = basename($ruta_actual);

// 4. Para show.php, update.php y delete.php verificar acceso a la compra específica
if (in_array($nombre_archivo, ['show.php', 'update.php', 'delete.php'])) {
    if (isset($_GET['id'])) {
        $id_compra_get = $_GET['id'];
        $id_usuario_actual = $_SESSION['id_usuario'];

        // Consulta para verificar que la compra pertenezca al usuario
        $verificar = $pdo->prepare("SELECT id_usuario FROM tb_compras WHERE id_compra = :id_compra");
        $verificar->bindParam(':id_compra', $id_compra_get, PDO::PARAM_INT);
        $verificar->execute();
        $compra = $verificar->fetch(PDO::FETCH_ASSOC);

        if (!$compra || $compra['id_usuario'] != $id_usuario_actual) {
            $_SESSION['mensaje'] = "No tienes permiso para ver esta compra";
            $_SESSION['icono'] = "error";
            header("Location: $URL/compras");
            exit();
        }
    } else {
        // Si no hay ID en la URL en estos archivos, redireccionar
        $_SESSION['mensaje'] = "Acceso incorrecto, falta ID de la compra";
        $_SESSION['icono'] = "error";
        header("Location: $URL/compras");
        exit();
    }
}

// 5. Para index.php y create.php, solo verificar autenticación (ya hecho arriba)
// No hacer más verificaciones específicas que puedan causar bucles
?>