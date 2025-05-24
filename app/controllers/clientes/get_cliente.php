<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php'; // $pdo, $URL
require_once __DIR__ . '/../../models/ClienteModel.php';

$response = ['status' => 'error', 'message' => 'Cliente no encontrado o acceso denegado.'];

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_usuario'])) {
    $response['message'] = 'Debe iniciar sesión para ver los detalles del cliente.';
    $response['redirectTo'] = $URL . '/login/'; // Para posible redirección desde JS
    echo json_encode($response);
    exit();
}
$id_usuario_logueado = (int)$_SESSION['id_usuario'];

if (!isset($_GET['id_cliente'])) {
    $response['message'] = 'ID de cliente no proporcionado.';
    echo json_encode($response);
    exit();
}

$id_cliente = filter_var($_GET['id_cliente'], FILTER_VALIDATE_INT);
if (!$id_cliente) {
    $response['message'] = 'ID de cliente no válido.';
    echo json_encode($response);
    exit();
}

try {
    $clienteModel = new ClienteModel($pdo, $URL);
    $cliente_data = $clienteModel->getClienteByIdAndUsuarioId($id_cliente, $id_usuario_logueado);

    if ($cliente_data) {
        // Asegurarse de que la fecha de nacimiento esté en formato YYYY-MM-DD si no es null
        if (isset($cliente_data['fecha_nacimiento']) && !empty($cliente_data['fecha_nacimiento'])) {
            try {
                $dt = new DateTime($cliente_data['fecha_nacimiento']);
                $cliente_data['fecha_nacimiento'] = $dt->format('Y-m-d');
            } catch (Exception $e) {
                // Si la fecha no es válida, se podría enviar null o el valor original
                // Para el input type="date", es mejor enviar null si no es un formato válido
                $cliente_data['fecha_nacimiento'] = null; 
                error_log("Advertencia en get_cliente.php: Fecha de nacimiento no válida para cliente ID {$id_cliente}: " . $cliente_data['fecha_nacimiento']);
            }
        } else {
            $cliente_data['fecha_nacimiento'] = null; // Enviar null explícitamente si está vacío
        }

        $response['status'] = 'success';
        $response['data'] = $cliente_data;
        unset($response['message']); 
    } else {
        // El mensaje por defecto "Cliente no encontrado o acceso denegado" es adecuado.
    }
} catch (PDOException $e) {
    error_log("Error de BD en get_cliente.php para cliente ID {$id_cliente}: " . $e->getMessage());
    $response['message'] = "Error en el sistema al obtener datos del cliente (BD).";
} catch (Exception $e) {
    error_log("Error general en get_cliente.php para cliente ID {$id_cliente}: " . $e->getMessage());
    $response['message'] = "Error inesperado del servidor al obtener datos del cliente.";
}

echo json_encode($response);
?>