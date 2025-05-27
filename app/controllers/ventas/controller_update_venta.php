<?php
// Iniciar sesión si no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Incluir archivos necesarios
include '../../config.php';
require_once __DIR__ . '/../../models/VentasModel.php';
require_once __DIR__ . '/../../models/AlmacenModel.php';
require_once __DIR__ . '/../../models/ClienteModel.php';

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

// Validar cliente
if (!isset($_POST['id_cliente_venta']) || !filter_var($_POST['id_cliente_venta'], FILTER_VALIDATE_INT)) {
    responder('error', 'Debe seleccionar un cliente válido.');
}

$id_cliente = filter_var($_POST['id_cliente_venta'], FILTER_SANITIZE_NUMBER_INT);

// Validar que haya items en la venta
if (!isset($_POST['items']) || !is_array($_POST['items']) || count($_POST['items']) === 0) {
    responder('error', 'La venta debe tener al menos un producto.');
}

// Inicializar modelos
try {
    $ventasModel = new VentasModel($pdo, $id_usuario);
    $almacenModel = new AlmacenModel($pdo);
    $clienteModel = new ClienteModel($pdo, $URL);
    
    // Verificar que la venta exista y esté en estado PENDIENTE
    $venta_actual = $ventasModel->getVentaById($id_venta);
    
    if (!$venta_actual) {
        responder('error', 'La venta que intenta editar no existe o no tiene permisos para editarla.');
    }
    
    if (strtoupper($venta_actual['estado_venta']) !== 'PENDIENTE') {
        responder('error', 'Solo se pueden editar ventas en estado PENDIENTE. Estado actual: ' . $venta_actual['estado_venta']);
    }
    
    // Verificar que el cliente exista
    $cliente = $clienteModel->getClienteByIdAndUsuarioId($id_cliente, $id_usuario);
    if (!$cliente) {
        responder('error', 'El cliente seleccionado no existe o no está asociado a su cuenta.');
    }
    
    // Obtener detalles actuales para comparar cambios de stock
    $detalles_actuales = $ventasModel->getDetallesVentaById($id_venta);
    
    // Iniciar transacción
    $pdo->beginTransaction();
    
    // 1. Revertir el stock de los productos originales (devolver stock al inventario)
    foreach ($detalles_actuales as $detalle) {
        if (!$almacenModel->ajustarStockProducto(
            $detalle['id_producto'], 
            floatval($detalle['cantidad']), 
            $id_usuario
        )) {
            $pdo->rollBack();
            responder('error', 'Error al revertir el stock del producto ID: ' . $detalle['id_producto']);
        }
    }
    
    // 2. Eliminar los detalles antiguos
    $sql_delete = "DELETE FROM tb_detalle_ventas WHERE id_venta = :id_venta";
    $stmt_delete = $pdo->prepare($sql_delete);
    $stmt_delete->bindParam(':id_venta', $id_venta, PDO::PARAM_INT);
    if (!$stmt_delete->execute()) {
        $pdo->rollBack();
        responder('error', 'Error al eliminar los detalles antiguos de la venta.');
    }
    
    // 3. Actualizar datos de la cabecera de venta
    $fecha_venta = filter_input(INPUT_POST, 'fecha_venta', FILTER_SANITIZE_STRING);
    $tipo_comprobante = filter_input(INPUT_POST, 'tipo_comprobante_venta', FILTER_SANITIZE_STRING);
    $nro_comprobante_fisico = filter_input(INPUT_POST, 'nro_comprobante_fisico_venta', FILTER_SANITIZE_STRING);
    $subtotal_general = filter_input(INPUT_POST, 'subtotal_general_venta_calculado', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $monto_iva_general = filter_input(INPUT_POST, 'monto_iva_general_venta_calculado', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $descuento_general = filter_input(INPUT_POST, 'descuento_general_venta', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $total_general = filter_input(INPUT_POST, 'total_general_venta_calculado', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $observaciones = filter_input(INPUT_POST, 'observaciones_venta', FILTER_SANITIZE_STRING);
    
    // Validar fecha
    if (!$fecha_venta || !strtotime($fecha_venta)) {
        $pdo->rollBack();
        responder('error', 'La fecha de venta no es válida.');
    }
    
    $fecha_actualizacion = date('Y-m-d H:i:s');
    
    $sql_update = "UPDATE tb_ventas SET 
                    id_cliente = :id_cliente,
                    fecha_venta = :fecha_venta,
                    tipo_comprobante = :tipo_comprobante,
                    nro_comprobante_fisico = :nro_comprobante_fisico,
                    subtotal_general = :subtotal_general,
                    monto_iva_general = :monto_iva_general,
                    descuento_general = :descuento_general,
                    total_general = :total_general,
                    observaciones = :observaciones,
                    fyh_actualizacion = :fyh_actualizacion
                  WHERE id_venta = :id_venta AND id_usuario = :id_usuario";
    
    $stmt_update = $pdo->prepare($sql_update);
    $stmt_update->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);
    $stmt_update->bindParam(':fecha_venta', $fecha_venta, PDO::PARAM_STR);
    $stmt_update->bindParam(':tipo_comprobante', $tipo_comprobante, PDO::PARAM_STR);
    $stmt_update->bindParam(':nro_comprobante_fisico', $nro_comprobante_fisico, PDO::PARAM_STR);
    $stmt_update->bindParam(':subtotal_general', $subtotal_general, PDO::PARAM_STR);
    $stmt_update->bindParam(':monto_iva_general', $monto_iva_general, PDO::PARAM_STR);
    $stmt_update->bindParam(':descuento_general', $descuento_general, PDO::PARAM_STR);
    $stmt_update->bindParam(':total_general', $total_general, PDO::PARAM_STR);
    $stmt_update->bindParam(':observaciones', $observaciones, PDO::PARAM_STR);
    $stmt_update->bindParam(':fyh_actualizacion', $fecha_actualizacion, PDO::PARAM_STR);
    $stmt_update->bindParam(':id_venta', $id_venta, PDO::PARAM_INT);
    $stmt_update->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    
    if (!$stmt_update->execute()) {
        $pdo->rollBack();
        responder('error', 'Error al actualizar la información de la venta.');
    }
    
    // 4. Insertar nuevos detalles y reducir stock
    $sql_insert = "INSERT INTO tb_detalle_ventas 
                    (id_venta, id_producto, cantidad, precio_venta_unitario, porcentaje_iva_item, 
                     monto_iva_item, subtotal_item, total_item, fyh_creacion, fyh_actualizacion)
                  VALUES 
                    (:id_venta, :id_producto, :cantidad, :precio_venta_unitario, :porcentaje_iva_item,
                     :monto_iva_item, :subtotal_item, :total_item, :fyh_creacion, :fyh_actualizacion)";
    
    $stmt_insert = $pdo->prepare($sql_insert);
    
    foreach ($_POST['items'] as $item) {
        $id_producto = isset($item['id_producto']) ? filter_var($item['id_producto'], FILTER_SANITIZE_NUMBER_INT) : 0;
        $cantidad = isset($item['cantidad']) ? filter_var($item['cantidad'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : 0;
        $precio_venta_unitario = isset($item['precio_venta_unitario']) ? filter_var($item['precio_venta_unitario'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : 0;
        $porcentaje_iva_item = isset($item['porcentaje_iva_item']) ? filter_var($item['porcentaje_iva_item'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION) : 0;
        
        if (!$id_producto || $cantidad <= 0 || $precio_venta_unitario < 0) {
            $pdo->rollBack();
            responder('error', 'Datos inválidos en uno de los productos: ID=' . $id_producto . ', Cantidad=' . $cantidad);
        }
        
        // Verificar stock disponible
        $producto_info = $almacenModel->getProductoByIdAndUsuarioId($id_producto, $id_usuario);
        if (!$producto_info) {
            $pdo->rollBack();
            responder('error', 'Producto no encontrado: ID=' . $id_producto);
        }
        
        // Stock actual más lo que ya se vendió en esta venta (si es el mismo producto)
        $stock_disponible = $producto_info['stock'];
        
        // Verificar si el stock es suficiente
        if ($cantidad > $stock_disponible) {
            $pdo->rollBack();
            responder('error', 'Stock insuficiente para el producto: ' . $producto_info['nombre'] . 
                      '. Disponible: ' . $stock_disponible . ', Solicitado: ' . $cantidad);
        }
        
        // Calcular valores monetarios
        $subtotal_item = $cantidad * $precio_venta_unitario;
        $monto_iva_item = $subtotal_item * ($porcentaje_iva_item / 100);
        $total_item = $subtotal_item + $monto_iva_item;
        
        // Insertar detalle
        $stmt_insert->bindParam(':id_venta', $id_venta, PDO::PARAM_INT);
        $stmt_insert->bindParam(':id_producto', $id_producto, PDO::PARAM_INT);
        $stmt_insert->bindParam(':cantidad', $cantidad, PDO::PARAM_STR);
        $stmt_insert->bindParam(':precio_venta_unitario', $precio_venta_unitario, PDO::PARAM_STR);
        $stmt_insert->bindParam(':porcentaje_iva_item', $porcentaje_iva_item, PDO::PARAM_STR);
        $stmt_insert->bindParam(':monto_iva_item', $monto_iva_item, PDO::PARAM_STR);
        $stmt_insert->bindParam(':subtotal_item', $subtotal_item, PDO::PARAM_STR);
        $stmt_insert->bindParam(':total_item', $total_item, PDO::PARAM_STR);
        $stmt_insert->bindParam(':fyh_creacion', $fecha_actualizacion, PDO::PARAM_STR);
        $stmt_insert->bindParam(':fyh_actualizacion', $fecha_actualizacion, PDO::PARAM_STR);
        
        if (!$stmt_insert->execute()) {
            $pdo->rollBack();
            responder('error', 'Error al insertar detalle para producto ID: ' . $id_producto);
        }
        
        // Reducir stock
        if (!$almacenModel->ajustarStockProducto($id_producto, -floatval($cantidad), $id_usuario)) {
            $pdo->rollBack();
            responder('error', 'Error al reducir stock para producto ID: ' . $id_producto);
        }
    }
    
    // Confirmar transacción
    $pdo->commit();
    
    // Respuesta exitosa
    responder('success', 'Venta actualizada correctamente.', ['id_venta' => $id_venta]);
    
} catch (Exception $e) {
    // Revertir cambios si hay un error
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    responder('error', 'Error al procesar la actualización: ' . $e->getMessage());
}