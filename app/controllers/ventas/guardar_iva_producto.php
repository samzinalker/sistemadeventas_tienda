<?php
include('../../config.php');
if (session_status() == PHP_SESSION_NONE) session_start();

// Verificar autenticación
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['status' => 'error', 'message' => 'No autenticado']);
    exit();
}

// Obtener datos del POST
$id_producto = isset($_POST['id_producto']) ? intval($_POST['id_producto']) : 0;
$porcentaje_iva = isset($_POST['porcentaje_iva']) ? floatval($_POST['porcentaje_iva']) : 0;

// Validar datos
if ($id_producto <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'ID de producto inválido']);
    exit();
}

// Validar que el IVA no sea negativo
if ($porcentaje_iva < 0) {
    $porcentaje_iva = 0;
}

// Inicializar el array para IVA por producto si no existe
if (!isset($_SESSION['ultimo_iva_por_producto'])) {
    $_SESSION['ultimo_iva_por_producto'] = [];
}

// Guardar el valor de IVA para este producto específico
$_SESSION['ultimo_iva_por_producto'][$id_producto] = $porcentaje_iva;

echo json_encode([
    'status' => 'success',
    'id_producto' => $id_producto,
    'porcentaje_iva' => $porcentaje_iva
]);