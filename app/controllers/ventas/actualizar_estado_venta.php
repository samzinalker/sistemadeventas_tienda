<?php
// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Incluir archivos necesarios
include '../../config.php';
require_once __DIR__ . '/../../models/VentasModel.php';

// Función para devolver respuesta JSON
function responder($status, $message, $data = []) {
    header('Content-Type: application/json');
    echo json_encode([
        'status' => $status,
        'message' => $message,
        'data' => $data
    ]);
    exit;
}

// Verificar si es una solicitud POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder('error', 'Método no permitido.');
}

// Verificar autenticación
if (!isset($_SESSION['id_usuario'])) {
    responder('error', 'Sesión expirada o usuario no autenticado.');
}

$id_usuario = $_SESSION['id_usuario'];

// Validar ID de venta
if (!isset($_POST['id_venta']) || !filter_var($_POST['id_venta'], FILTER_VALIDATE_INT)) {
    responder('error', 'ID de venta no válido.');
}

$id_venta = filter_var($_POST['id_venta'], FILTER_SANITIZE_NUMBER_INT);

// Validar estado
if (!isset($_POST['estado_venta']) || empty($_POST['estado_venta'])) {
    responder('error', 'Estado de venta no válido.');
}

$estado_venta = filter_var($_POST['estado_venta'], FILTER_SANITIZE_STRING);

// Validar que el estado sea uno de los permitidos
$estados_permitidos = ['PENDIENTE', 'PAGADA', 'ENTREGADA', 'ANULADA'];
if (!in_array($estado_venta, $estados_permitidos)) {
    responder('error', 'Estado de venta no válido. Debe ser uno de: ' . implode(', ', $estados_permitidos));
}

try {
    // Iniciar transacción
    $pdo->beginTransaction();
    
    // Actualizar el estado de la venta
    $sql = "UPDATE tb_ventas SET 
            estado_venta = :estado_venta,
            fyh_actualizacion = :fyh_actualizacion
            WHERE id_venta = :id_venta AND id_usuario = :id_usuario";
    
    $stmt = $pdo->prepare($sql);
    $fecha_actualizacion = date('Y-m-d H:i:s');
    
    $stmt->bindParam(':estado_venta', $estado_venta, PDO::PARAM_STR);
    $stmt->bindParam(':fyh_actualizacion', $fecha_actualizacion, PDO::PARAM_STR);
    $stmt->bindParam(':id_venta', $id_venta, PDO::PARAM_INT);
    $stmt->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    
    if (!$stmt->execute()) {
        $pdo->rollBack();
        responder('error', 'Error al actualizar el estado de la venta.');
    }
    
    // Confirmar la transacción
    $pdo->commit();
    
    responder('success', 'Estado de venta actualizado correctamente.', [
        'id_venta' => $id_venta,
        'estado_venta' => $estado_venta
    ]);
    
} catch (Exception $e) {
    // Si hay un error, revertir la transacción
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    responder('error', 'Error al actualizar el estado: ' . $e->getMessage());
}