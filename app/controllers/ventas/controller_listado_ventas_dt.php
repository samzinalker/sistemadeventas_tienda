<?php
require_once __DIR__ . '/../../../app/config.php';
require_once __DIR__ . '/../../../app/models/VentasModel.php';

session_start();
header('Content-Type: application/json');

// Verificar la autenticación del usuario
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode([
        "draw" => isset($_POST['draw']) ? intval($_POST['draw']) : 0,
        "recordsTotal" => 0,
        "recordsFiltered" => 0,
        "data" => [],
        "error" => "Usuario no autenticado."
    ]);
    exit;
}

$id_usuario_logueado = $_SESSION['id_usuario'];

// Capturar todos los parámetros de DataTables
$params = $_POST;

// Añadir filtro de estado si está presente
if (isset($_POST['filtro_estado']) && $_POST['filtro_estado'] !== 'todos') {
    $params['filtro_estado'] = $_POST['filtro_estado'];
}

try {
    // Registrar la solicitud para depuración (opcional)
    error_log("Solicitud de listado de ventas. Usuario: $id_usuario_logueado. Fecha: " . date('Y-m-d H:i:s') . ". Filtro estado: " . ($params['filtro_estado'] ?? 'todos'));
    
    // Instanciar el modelo y obtener los datos
    $ventasModel = new VentasModel($pdo, $id_usuario_logueado);
    $resultado = $ventasModel->getVentasListadoDT($params);
    
    // Devolver los resultados como JSON
    echo json_encode($resultado);

} catch (Exception $e) {
    // Registrar el error
    error_log("Error en controller_listado_ventas_dt: " . $e->getMessage());
    
    // Devolver respuesta de error
    echo json_encode([
        "draw" => isset($params['draw']) ? intval($params['draw']) : 0,
        "recordsTotal" => 0,
        "recordsFiltered" => 0,
        "data" => [],
        "error" => "Error al obtener el listado de ventas: " . $e->getMessage()
    ]);
}
?>