<?php
require_once __DIR__ . '/../../../app/config.php'; // Ajusta la ruta según sea necesario
require_once __DIR__ . '/../../../app/models/VentasModel.php'; // Ajusta la ruta

session_start();

header('Content-Type: application/json');

// Verificar que el usuario esté logueado
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Usuario no autenticado.'
    ]);
    exit;
}

$id_usuario_logueado = $_SESSION['id_usuario'];

try {
    $ventasModel = new VentasModel($pdo, $id_usuario_logueado);
    
    $nro_secuencial = $ventasModel->getSiguienteNumeroVentaSecuencial();
    $codigo_venta_formateado = $ventasModel->formatearCodigoVenta($nro_secuencial);

    echo json_encode([
        'status' => 'success',
        'nro_secuencial' => $nro_secuencial,
        'codigo_venta' => $codigo_venta_formateado
    ]);

} catch (Exception $e) {
    // Loggear el error $e->getMessage() si tienes un sistema de logs
    echo json_encode([
        'status' => 'error',
        'message' => 'Error al generar el código de venta: ' . $e->getMessage()
    ]);
}
?>