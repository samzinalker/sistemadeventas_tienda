<?php
include ('../../config.php');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar autenticación
if (!isset($_SESSION['id_usuario'])) {
    echo "No autenticado";
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$id_producto = $_GET['id_producto'] ?? 0;
$stock_calculado = intval($_GET['stock_calculado'] ?? 0);

if (!$id_producto) {
    echo "Error: ID de producto no especificado";
    exit();
}

if ($stock_calculado < 0) {
    $stock_calculado = 0; // Garantizar que el stock nunca sea negativo
}

try {
    // Verificar que el producto pertenece al usuario actual
    $sql_check = "SELECT COUNT(*) FROM tb_almacen 
                 WHERE id_producto = :id_producto AND id_usuario = :id_usuario";
    $query_check = $pdo->prepare($sql_check);
    $query_check->bindParam(':id_producto', $id_producto, PDO::PARAM_INT);
    $query_check->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $query_check->execute();
    
    if ($query_check->fetchColumn() === 0) {
        echo "Error: No tienes permiso para modificar este producto";
        exit();
    }

    // Actualizar el stock del producto
    $sentencia = $pdo->prepare("UPDATE tb_almacen 
                                SET stock = :stock, 
                                    fyh_actualizacion = NOW() 
                                WHERE id_producto = :id_producto AND id_usuario = :id_usuario");
    $sentencia->bindParam('stock', $stock_calculado, PDO::PARAM_INT);
    $sentencia->bindParam('id_producto', $id_producto, PDO::PARAM_INT);
    $sentencia->bindParam('id_usuario', $id_usuario, PDO::PARAM_INT);

    if ($sentencia->execute()) {
        echo "Stock actualizado correctamente";
    } else {
        echo "Error al actualizar el stock";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}