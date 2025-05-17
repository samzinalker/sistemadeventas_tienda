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
$id_compra = isset($_POST['id_compra']) ? intval($_POST['id_compra']) : 0;
$nro_compra = isset($_POST['nro_compra']) ? intval($_POST['nro_compra']) : 0;

// Depuración
error_log("Recibida solicitud para eliminar compra: ID=$id_compra, NRO=$nro_compra");

if (!$id_compra) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos para eliminar la compra']);
    exit();
}

try {
    $pdo->beginTransaction();
    
    // 3. Verifica que la compra pertenece al usuario autenticado
    $verifica = $pdo->prepare("SELECT c.*, a.stock 
                              FROM tb_compras c 
                              INNER JOIN tb_almacen a ON c.id_producto = a.id_producto
                              WHERE c.id_compra = :id_compra AND c.id_usuario = :id_usuario");
    $verifica->bindParam(':id_compra', $id_compra, PDO::PARAM_INT);
    $verifica->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $verifica->execute();

    if ($verifica->rowCount() === 0) {
        throw new Exception("No tienes permiso para eliminar esta compra");
    }
    
    // Obtener datos de la compra para actualizar el stock
    $compra_data = $verifica->fetch(PDO::FETCH_ASSOC);
    $id_producto = $compra_data['id_producto'];
    $cantidad_compra = $compra_data['cantidad'];
    $stock_actual = $compra_data['stock'];
    
    // Verificar que haya suficiente stock para revertir
    if ($stock_actual < $cantidad_compra) {
        throw new Exception("No se puede eliminar la compra porque ya se ha vendido parte del stock");
    }
    
    // Actualizar stock del producto
    $nuevo_stock = $stock_actual - $cantidad_compra;
    $update_stock = $pdo->prepare("UPDATE tb_almacen SET 
                                 stock = :nuevo_stock, 
                                 fyh_actualizacion = NOW() 
                                 WHERE id_producto = :id_producto");
    $update_stock->bindParam(':nuevo_stock', $nuevo_stock, PDO::PARAM_INT);
    $update_stock->bindParam(':id_producto', $id_producto, PDO::PARAM_INT);
    
    if (!$update_stock->execute()) {
        throw new Exception("Error al actualizar el stock del producto");
    }
    
    // Eliminar la compra
    $delete_compra = $pdo->prepare("DELETE FROM tb_compras WHERE id_compra = :id_compra");
    $delete_compra->bindParam(':id_compra', $id_compra, PDO::PARAM_INT);
    
    if (!$delete_compra->execute()) {
        throw new Exception("Error al eliminar la compra");
    }

    $pdo->commit();
    
    // Depuración
    error_log("Compra eliminada correctamente: ID=$id_compra");
    
    // Retornar respuesta de éxito en formato JSON
    echo json_encode([
        'success' => true, 
        'message' => 'Compra #' . $nro_compra . ' eliminada correctamente'
    ]);
    exit();

} catch (Exception $e) {
    $pdo->rollBack();
    error_log("Error eliminando compra: " . $e->getMessage());
    echo json_encode([
        'success' => false, 
        'message' => 'Error: ' . $e->getMessage()
    ]);
    exit();
}