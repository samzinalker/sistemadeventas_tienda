<?php
include('../../config.php');
if (session_status() == PHP_SESSION_NONE) session_start();

// Verificar autenticación
if (!isset($_SESSION['id_usuario'])) {
    $_SESSION['mensaje'] = "Debe iniciar sesión para realizar esta acción";
    $_SESSION['icono'] = "error";
    header('Location: ' . $URL . '/login.php');
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$id_carrito = $_POST['id_carrito'] ?? 0;

if (!$id_carrito) {
    $_SESSION['mensaje'] = "ID de carrito no especificado";
    $_SESSION['icono'] = "error";
    header('Location: ' . $URL . '/ventas/create.php');
    exit();
}

try {
    // Verificar que el item del carrito pertenezca al usuario
    $sql_check = "SELECT COUNT(*) FROM tb_carrito 
                 WHERE id_carrito = :id_carrito AND id_usuario = :id_usuario AND nro_venta = 0";
    $query_check = $pdo->prepare($sql_check);
    $query_check->bindParam(':id_carrito', $id_carrito, PDO::PARAM_INT);
    $query_check->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $query_check->execute();
    
    if ($query_check->fetchColumn() === 0) {
        throw new Exception("No tienes permiso para eliminar este item o ya ha sido procesado");
    }

    // Eliminar el item del carrito
    $sql = "DELETE FROM tb_carrito 
            WHERE id_carrito = :id_carrito AND id_usuario = :id_usuario AND nro_venta = 0";
    $query = $pdo->prepare($sql);
    $query->bindParam(':id_carrito', $id_carrito, PDO::PARAM_INT);
    $query->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    
    if (!$query->execute()) {
        throw new Exception("Error al eliminar el item del carrito");
    }

    header('Location: ' . $URL . '/ventas/create.php');
    exit();
    
} catch (Exception $e) {
    $_SESSION['mensaje'] = "Error: " . $e->getMessage();
    $_SESSION['icono'] = "error";
    header('Location: ' . $URL . '/ventas/create.php');
    exit();
}