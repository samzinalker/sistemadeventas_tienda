<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php'; // $pdo, $URL, $fechaHora
require_once __DIR__ . '/../../models/ClienteModel.php';

$response = ['status' => 'error', 'message' => 'Error desconocido.'];

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_usuario'])) {
    $response['message'] = 'Debe iniciar sesión para esta acción.';
    $response['redirectTo'] = $URL . '/login/';
    echo json_encode($response);
    exit();
}
$id_usuario_logueado = (int)$_SESSION['id_usuario'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { // Asegurarse que sea POST si es una acción de creación
    $response['message'] = 'Método de solicitud no permitido.';
    echo json_encode($response);
    exit();
}

try {
    $clienteModel = new ClienteModel($pdo, $URL);
    $consumidor_final_id = $clienteModel->obtenerOCrearConsumidorFinal($id_usuario_logueado, $fechaHora);

    if ($consumidor_final_id) {
        $response['status'] = 'success';
        $response['message'] = 'Cliente "CONSUMIDOR FINAL" verificado/creado exitosamente.';
        $response['id_cliente'] = $consumidor_final_id;
    } else {
        $response['message'] = 'No se pudo verificar o crear el cliente "CONSUMIDOR FINAL".';
    }

} catch (PDOException $e) {
    error_log("Error PDO en crear_consumidor_final.php: " . $e->getMessage());
    $response['message'] = 'Error de base de datos.';
} catch (Exception $e) {
    error_log("Error general en crear_consumidor_final.php: " . $e->getMessage());
    $response['message'] = 'Error del servidor.';
}

echo json_encode($response);
?>