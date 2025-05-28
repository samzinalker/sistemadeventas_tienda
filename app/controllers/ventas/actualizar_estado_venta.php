<?php
// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Incluir archivos necesarios
include '../../config.php';
require_once __DIR__ . '/../../models/VentasModel.php';
require_once __DIR__ . '/../../models/AlmacenModel.php';

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
    
    // Si vamos a anular la venta, primero necesitamos obtener su estado actual
    // para verificar que no esté ya anulada
    if ($estado_venta === 'ANULADA') {
        $sqlCheckEstado = "SELECT estado_venta FROM tb_ventas 
                           WHERE id_venta = :id_venta AND id_usuario = :id_usuario";
        $stmtCheckEstado = $pdo->prepare($sqlCheckEstado);
        $stmtCheckEstado->bindParam(':id_venta', $id_venta, PDO::PARAM_INT);
        $stmtCheckEstado->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmtCheckEstado->execute();
        $estadoActual = $stmtCheckEstado->fetchColumn();
        
        if ($estadoActual === 'ANULADA') {
            $pdo->rollBack();
            responder('warning', 'Esta venta ya está anulada.');
        }
    }
    
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
    
    // Si se está anulando la venta, restaurar el inventario
    if ($estado_venta === 'ANULADA') {
        // Instanciar los modelos necesarios
        $ventasModel = new VentasModel($pdo, $id_usuario);
        $almacenModel = new AlmacenModel($pdo);
        
        // Obtener los detalles de la venta
        $detalles_venta = $ventasModel->getDetallesVentaById($id_venta);
        
        if (empty($detalles_venta)) {
            $pdo->rollBack();
            responder('error', 'No se encontraron detalles para esta venta.');
        }
        
        // Restaurar el inventario para cada producto vendido
        foreach ($detalles_venta as $detalle) {
            // La cantidad debe ser positiva para aumentar el stock
            $cantidad_a_restaurar = floatval($detalle['cantidad']);
            
            // Ajustar el stock (aumentar)
            if (!$almacenModel->ajustarStockProducto(
                $detalle['id_producto'], 
                $cantidad_a_restaurar, // Positivo para aumentar el stock
                $id_usuario
            )) {
                $pdo->rollBack();
                responder('error', 'Error al restaurar el stock del producto ID: ' . $detalle['id_producto']);
            }
        }
        
        // Registrar la anulación en un historial (opcional)
        // Esta parte se podría implementar si deseas mantener un registro de las anulaciones
        $motivo_anulacion = filter_input(INPUT_POST, 'motivo_anulacion', FILTER_SANITIZE_STRING) ?: 'Anulación desde sistema';
        
        $sql_historial = "INSERT INTO tb_historial_ventas 
                          (id_venta, id_usuario, accion, detalles, fyh_registro)
                          VALUES 
                          (:id_venta, :id_usuario, 'ANULACION', :detalles, :fyh_registro)";
        
        $stmt_historial = $pdo->prepare($sql_historial);
        $stmt_historial->bindParam(':id_venta', $id_venta, PDO::PARAM_INT);
        $stmt_historial->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmt_historial->bindParam(':detalles', $motivo_anulacion, PDO::PARAM_STR);
        $stmt_historial->bindParam(':fyh_registro', $fecha_actualizacion, PDO::PARAM_STR);
        
        // Si falla el registro del historial, no es crítico para la operación principal
        try {
            $stmt_historial->execute();
        } catch (Exception $e) {
            error_log('Error al registrar historial de anulación: ' . $e->getMessage());
            // No hacemos rollback por esto, continuamos con la transacción
        }
    }
    
    // Confirmar la transacción
    $pdo->commit();
    
    // Mensaje personalizado según el estado
    $mensaje = 'Estado de venta actualizado correctamente.';
    if ($estado_venta === 'ANULADA') {
        $mensaje = 'Venta anulada correctamente y stock restaurado al inventario.';
    }
    
    responder('success', $mensaje, [
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