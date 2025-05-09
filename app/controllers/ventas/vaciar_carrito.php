<?php
// Incluir el archivo de configuración con una ruta absoluta
include ($_SERVER['DOCUMENT_ROOT'] . '/sistemadeventas/app/config.php');

// Iniciar la sesión si no está activa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario está autenticado
$id_usuario_actual = $_SESSION['id_usuario'] ?? null;
if (!$id_usuario_actual) {
    echo "Usuario no autenticado. No se puede vaciar el carrito.";
    exit();
}

try {
    $pdo->beginTransaction();

    // Vaciar el carrito solo del usuario actual
    $sentencia = $pdo->prepare("DELETE FROM tb_carrito WHERE id_usuario = :id_usuario");
    $sentencia->bindParam(':id_usuario', $id_usuario_actual, PDO::PARAM_INT);
    $sentencia->execute();

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    echo "Error al vaciar el carrito: " . $e->getMessage();
}
?>