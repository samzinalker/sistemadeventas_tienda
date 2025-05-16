<?php
include '../../config.php';

// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Comprobar autenticación
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['status' => 'error', 'message' => 'Sesión no iniciada']);
    exit;
}

// Validar parámetros
if (!isset($_GET['id_compra']) || !isset($_GET['id_producto']) || 
    !isset($_GET['cantidad_compra']) || !isset($_GET['stock_actual'])) {
    echo json_encode(['status' => 'error', 'message' => 'Parámetros incompletos']);
    exit;
}

// Capturar parámetros
$id_compra = $_GET['id_compra'];
$id_producto = $_GET['id_producto'];
$cantidad_compra = $_GET['cantidad_compra'];
$stock_actual = $_GET['stock_actual'];
$id_usuario_actual = $_SESSION['id_usuario'];

try {
    // Verificar permisos de usuario
    $stmt = $pdo->prepare("SELECT id_usuario FROM tb_compras WHERE id_compra = ?");
    $stmt->execute([$id_compra]);
    $compra = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$compra || $compra['id_usuario'] != $id_usuario_actual) {
        echo json_encode(['status' => 'error', 'message' => 'No tienes permiso para eliminar esta compra']);
        exit;
    }
    
    // Iniciar transacción
    $pdo->beginTransaction();
    
    // 1. Actualizar stock (restar la cantidad de la compra)
    $stock_final = $stock_actual - $cantidad_compra;
    $stmt = $pdo->prepare("UPDATE tb_almacen SET stock = ?, fyh_actualizacion = ? WHERE id_producto = ?");
    $stmt->execute([$stock_final, $fechaHora, $id_producto]);
    
    // 2. Eliminar la compra
    $stmt = $pdo->prepare("DELETE FROM tb_compras WHERE id_compra = ?");
    $stmt->execute([$id_compra]);
    
    // Confirmar cambios
    $pdo->commit();
    
    echo "eliminado_correctamente";
    
} catch (Exception $e) {
    // Revertir cambios en caso de error
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>