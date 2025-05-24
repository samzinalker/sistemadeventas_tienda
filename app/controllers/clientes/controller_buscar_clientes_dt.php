<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php'; 
require_once __DIR__ . '/../../models/ClienteModel.php';

$response = [
    "draw" => isset($_REQUEST['draw']) ? intval($_REQUEST['draw']) : 0,
    "recordsTotal" => 0,
    "recordsFiltered" => 0,
    "data" => [],
    "error" => null
];

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_usuario'])) {
    $response['error'] = 'Sesión no iniciada. Por favor, inicie sesión de nuevo.';
    echo json_encode($response);
    exit();
}
$id_usuario_logueado = (int)$_SESSION['id_usuario'];

if (empty($_REQUEST) || !isset($_REQUEST['draw'])) {
     $response['error'] = 'Solicitud de DataTables no válida.';
     echo json_encode($response);
     exit();
}

try {
    $clienteModel = new ClienteModel($pdo, $URL);
    $dataTableParams = $_REQUEST; 

    $clientesData = $clienteModel->buscarClientesDataTables($id_usuario_logueado, $dataTableParams);

    echo json_encode($clientesData);
    exit();

} catch (PDOException $e) {
    error_log("Error PDO en controller_buscar_clientes_dt.php: " . $e->getMessage());
    $response['error'] = "Error de base de datos al obtener clientes.";
    echo json_encode($response);
    exit();
} catch (Exception $e) {
    error_log("Error general en controller_buscar_clientes_dt.php: " . $e->getMessage());
    $response['error'] = "Error inesperado del servidor al obtener clientes (" . $e->getMessage() . ")";
    echo json_encode($response);
    exit();
}
?>