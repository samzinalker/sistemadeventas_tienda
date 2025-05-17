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

if (!$id_compra) {
    echo json_encode(['success' => false, 'message' => 'ID de compra no válido']);
    exit();
}

try {
    $pdo->beginTransaction();
    
    // Verificar que la compra pertenezca al usuario y obtener datos necesarios
    $consulta = $pdo->prepare("
        SELECT c.*, p.stock 
        FROM tb_compras c
        INNER JOIN tb_almacen p ON c.id_producto = p.id_producto
        WHERE c.id_compra = :id_compra AND c.id_usuario = :id_usuario
    ");
    $consulta->bindParam(':id_compra', $id_compra, PDO::PARAM_INT);
    $consulta->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $consulta->execute();
    
    if ($consulta->rowCount() === 0) {
        throw new Exception("No se encontró la compra o no tienes permisos para eliminarla");
    }
    
    $compra = $consulta->fetch(PDO::FETCH_ASSOC);
    $id_producto = $compra['id_producto'];
    $cantidad_compra = $compra['cantidad'];
    $stock_actual = $compra['stock'];
    
    // Verificar si hay suficiente stock para revertir la compra
    if ($stock_actual < $cantidad_compra) {
        throw new Exception("No se puede eliminar porque parte del stock ya ha sido vendido");
    }
    
    // Actualizar el stock del producto
    $nuevo_stock = $stock_actual - $cantidad_compra;
    $update_stock = $pdo->prepare("
        UPDATE tb_almacen 
        SET stock = :nuevo_stock, fyh_actualizacion = NOW() 
        WHERE id_producto = :id_producto
    ");
    $update_stock->bindParam(':nuevo_stock', $nuevo_stock, PDO::PARAM_INT);
    $update_stock->bindParam(':id_producto', $id_producto, PDO::PARAM_INT);
    
    if (!$update_stock->execute()) {
        throw new Exception("Error al actualizar el stock del producto");
    }
    
    // Eliminar el registro de compra
    $delete = $pdo->prepare("DELETE FROM tb_compras WHERE id_compra = :id_compra");
    $delete->bindParam(':id_compra', $id_compra, PDO::PARAM_INT);
    
    if (!$delete->execute()) {
        throw new Exception("Error al eliminar la compra");
    }
    
    // Todo salió bien, confirmar cambios
    $pdo->commit();
    
    echo json_encode([
        'success' => true, 
        'message' => 'Compra #' . $nro_compra . ' eliminada correctamente'
    ]);
    
} catch (Exception $e) {
    // Algo salió mal, revertir cambios
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    echo json_encode([
        'success' => false, 
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>