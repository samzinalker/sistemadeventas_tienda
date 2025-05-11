<?php
include('../../config.php');
if (session_status() == PHP_SESSION_NONE) session_start();

// Verificar autenticación
if (!isset($_SESSION['id_usuario'])) {
    echo "ERROR: Autenticación requerida";
    exit();
}

$id_usuario_actual = $_SESSION['id_usuario'];
$id_producto = $_POST['id_producto'] ?? 0;
$cantidad = intval($_POST['cantidad'] ?? 0);

// Validar parámetros
if (!$id_producto || $cantidad <= 0) {
    echo "ERROR: Datos inválidos. Producto y cantidad son obligatorios";
    exit();
}

// Verificar que el producto pertenezca al usuario actual
$sql_producto = "SELECT stock, precio_venta, nombre FROM tb_almacen 
                WHERE id_producto = :id_producto AND id_usuario = :id_usuario";
$query_producto = $pdo->prepare($sql_producto);
$query_producto->bindParam(':id_producto', $id_producto, PDO::PARAM_INT);
$query_producto->bindParam(':id_usuario', $id_usuario_actual, PDO::PARAM_INT);
$query_producto->execute();

if ($query_producto->rowCount() === 0) {
    echo "ERROR: Producto no encontrado o no tienes permisos para venderlo";
    exit();
}

// Obtener datos del producto
$producto = $query_producto->fetch(PDO::FETCH_ASSOC);
$stock_actual = $producto['stock'];
$nombre_producto = $producto['nombre'];

// Verificar si hay suficiente stock
if ($stock_actual < $cantidad) {
    echo "ERROR: Stock insuficiente para '$nombre_producto'. Disponible: $stock_actual";
    exit();
}

// Verificar si ya existe en el carrito
$sql_carrito = "SELECT id_carrito, cantidad FROM tb_carrito 
                WHERE id_usuario = :id_usuario AND id_producto = :id_producto AND nro_venta = 0";
$query_carrito = $pdo->prepare($sql_carrito);
$query_carrito->bindParam(':id_usuario', $id_usuario_actual, PDO::PARAM_INT);
$query_carrito->bindParam(':id_producto', $id_producto, PDO::PARAM_INT);
$query_carrito->execute();

try {
    $pdo->beginTransaction();
    
    if ($query_carrito->rowCount() > 0) {
        // Ya existe, actualizar cantidad
        $carrito = $query_carrito->fetch(PDO::FETCH_ASSOC);
        $cantidad_nueva = $carrito['cantidad'] + $cantidad;
        
        // Verificar nuevamente el stock con la cantidad actualizada
        if ($stock_actual < $cantidad_nueva) {
            throw new Exception("Stock insuficiente para la cantidad total. Disponible: $stock_actual");
        }
        
        $sql = "UPDATE tb_carrito SET cantidad = :cantidad, fyh_actualizacion = NOW() 
                WHERE id_carrito = :id_carrito AND id_usuario = :id_usuario";
        $query = $pdo->prepare($sql);
        $query->bindParam(':cantidad', $cantidad_nueva, PDO::PARAM_INT);
        $query->bindParam(':id_carrito', $carrito['id_carrito'], PDO::PARAM_INT);
        $query->bindParam(':id_usuario', $id_usuario_actual, PDO::PARAM_INT);
    } else {
        // No existe, insertar nuevo
        $sql = "INSERT INTO tb_carrito (id_usuario, id_producto, cantidad, nro_venta, fyh_creacion, fyh_actualizacion) 
                VALUES (:id_usuario, :id_producto, :cantidad, 0, NOW(), NOW())";
        $query = $pdo->prepare($sql);
        $query->bindParam(':id_usuario', $id_usuario_actual, PDO::PARAM_INT);
        $query->bindParam(':id_producto', $id_producto, PDO::PARAM_INT);
        $query->bindParam(':cantidad', $cantidad, PDO::PARAM_INT);
    }
    
    if ($query->execute()) {
        $pdo->commit();
        echo "OK";
    } else {
        throw new Exception("No se pudo actualizar el carrito");
    }
} catch (Exception $e) {
    $pdo->rollBack();
    echo "ERROR: " . $e->getMessage();
}