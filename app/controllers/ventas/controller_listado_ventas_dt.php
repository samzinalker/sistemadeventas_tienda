<?php
require_once __DIR__ . '/../../../app/config.php';
require_once __DIR__ . '/../../../app/models/VentasModel.php';

session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['id_usuario'])) { // <--- CAMBIO AQUÍ
    echo json_encode([
        "draw" => isset($_POST['draw']) ? intval($_POST['draw']) : 0,
        "recordsTotal" => 0,
        "recordsFiltered" => 0,
        "data" => [],
        "error" => "Usuario no autenticado."
    ]);
    exit;
}

$id_usuario_logueado = $_SESSION['id_usuario']; // <--- CAMBIO AQUÍ

// Parámetros de DataTables (vienen en $_POST cuando serverSide es true)
$params = $_POST;

try {
    $ventasModel = new VentasModel($pdo, $id_usuario_logueado);
    $resultado = $ventasModel->getVentasListadoDT($params);
    echo json_encode($resultado);

} catch (Exception $e) {
    error_log("Error en controller_listado_ventas_dt: " . $e->getMessage());
    echo json_encode([
        "draw" => isset($params['draw']) ? intval($params['draw']) : 0,
        "recordsTotal" => 0,
        "recordsFiltered" => 0,
        "data" => [],
        "error" => "Error al obtener el listado de ventas: " . $e->getMessage() // Podrías usar un mensaje más genérico en producción
    ]);
}
?>