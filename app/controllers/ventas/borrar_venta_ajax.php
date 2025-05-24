<?php
include ('../../config.php');

// 1. Inicia la sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Verifica autenticación
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit();
}

// Verificar que sea una solicitud POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$id_venta = isset($_POST['id_venta']) ? intval($_POST['id_venta']) : 0;
$nro_venta = isset($_POST['nro_venta']) ? intval($_POST['nro_venta']) : 0;

if (!$id_venta || !$nro_venta) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos para eliminar la venta']);
    exit();
}

try {
    $pdo->beginTransaction();
    
    // 3. Verifica que la venta pertenece al usuario autenticado
    $verifica = $pdo->prepare("SELECT id_venta FROM tb_ventas 
                               WHERE id_venta = :id_venta AND id_usuario = :id_usuario");
    $verifica->bindParam(':id_venta', $id_venta, PDO::PARAM_INT);
    $verifica->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $verifica->execute();

    if ($verifica->rowCount() === 0) {
        throw new Exception("No tienes permiso para eliminar esta venta");
    }

    // Obtener los productos del carrito para devolver stock
    $sql_productos = "SELECT c.id_producto, c.cantidad, p.stock 
                      FROM tb_carrito c
                      JOIN tb_almacen p ON c.id_producto = p.id_producto
                      WHERE c.nro_venta = :nro_venta AND c.id_usuario = :id_usuario";
    $query_productos = $pdo->prepare($sql_productos);
    $query_productos->bindParam(':nro_venta', $nro_venta, PDO::PARAM_INT);
    $query_productos->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $query_productos->execute();
    $productos = $query_productos->fetchAll(PDO::FETCH_ASSOC);

    // Eliminar la venta
    $sentencia = $pdo->prepare("DELETE FROM tb_ventas 
                               WHERE id_venta = :id_venta AND id_usuario = :id_usuario");
    $sentencia->bindParam(':id_venta', $id_venta, PDO::PARAM_INT);
    $sentencia->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    
    if (!$sentencia->execute()) {
        throw new Exception("Error al intentar borrar la venta");
    }
    
    // Eliminar registros de carrito asociados
    $sentencia2 = $pdo->prepare("DELETE FROM tb_carrito 
                                WHERE nro_venta = :nro_venta AND id_usuario = :id_usuario");
    $sentencia2->bindParam(':nro_venta', $nro_venta, PDO::PARAM_INT);
    $sentencia2->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    
    if (!$sentencia2->execute()) {
        throw new Exception("Error al intentar borrar los productos de la venta");
    }

    // Restaurar stock de productos
    foreach ($productos as $producto) {
        $nuevo_stock = $producto['stock'] + $producto['cantidad'];
        $sql_update = "UPDATE tb_almacen 
                       SET stock = :nuevo_stock, fyh_actualizacion = NOW() 
                       WHERE id_producto = :id_producto AND id_usuario = :id_usuario";
        $update = $pdo->prepare($sql_update);
        $update->bindParam(':nuevo_stock', $nuevo_stock, PDO::PARAM_INT);
        $update->bindParam(':id_producto', $producto['id_producto'], PDO::PARAM_INT);
        $update->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        
        if (!$update->execute()) {
            throw new Exception("Error al actualizar el stock del producto #{$producto['id_producto']}");
        }
    }

    $pdo->commit();
    
    // Retornar respuesta de éxito en formato JSON
    echo json_encode([
        'success' => true, 
        'message' => 'Venta #' . $nro_venta . ' eliminada correctamente'
    ]);
    exit();

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode([
        'success' => false, 
        'message' => 'Error: ' . $e->getMessage()
    ]);
    exit();
}