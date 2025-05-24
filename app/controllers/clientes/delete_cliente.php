<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php'; // $pdo, $URL
// require_once __DIR__ . '/../../utils/funciones_globales.php'; // No se usa directamente aquí setMensaje
require_once __DIR__ . '/../../models/ClienteModel.php';

$response = ['status' => 'error', 'message' => 'Error desconocido al intentar eliminar el cliente.'];

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_usuario'])) {
    $response['message'] = 'Debe iniciar sesión para realizar esta acción.';
    $response['redirectTo'] = $URL . '/login/';
    echo json_encode($response);
    exit();
}
$id_usuario_logueado = (int)$_SESSION['id_usuario'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id_cliente'])) {
    $response['message'] = 'Solicitud no válida o ID de cliente no proporcionado.';
    echo json_encode($response);
    exit();
}

$id_cliente_a_eliminar = filter_var($_POST['id_cliente'], FILTER_VALIDATE_INT);
if (!$id_cliente_a_eliminar) {
    $response['message'] = 'ID de cliente proporcionado no es válido.';
    $response['status'] = 'warning';
    echo json_encode($response);
    exit();
}

try {
    $clienteModel = new ClienteModel($pdo, $URL);

    // Es buena práctica verificar que el cliente pertenezca al usuario antes de intentar obtener su nombre
    // o incluso antes de intentar la eliminación, aunque el modelo también lo hace.
    $cliente_existente = $clienteModel->getClienteByIdAndUsuarioId($id_cliente_a_eliminar, $id_usuario_logueado);
    if (!$cliente_existente) {
        $response['message'] = 'Cliente no encontrado o no tiene permiso para eliminarlo.';
        $response['status'] = 'warning';
        echo json_encode($response);
        exit();
    }
    
    $nombre_cliente_para_mensaje = htmlspecialchars($cliente_existente['nombre_cliente']);

    // Prevenir la eliminación del "Consumidor Final" genérico si se desea
    if (strtoupper($cliente_existente['nombre_cliente']) === 'CONSUMIDOR FINAL' && 
        $cliente_existente['tipo_documento'] === 'consumidor_final' && 
        ($cliente_existente['nit_ci_cliente'] === '9999999999' || $cliente_existente['nit_ci_cliente'] === '9999999999999')) {
         $response['message'] = "El cliente 'CONSUMIDOR FINAL' genérico no puede ser eliminado directamente desde esta acción.";
         $response['status'] = 'warning';
         echo json_encode($response);
         exit();
    }

    // La lógica de verificar ventas asociadas está ahora en el Modelo (eliminarCliente)
    // Si el modelo devuelve false, puede ser por ventas asociadas (si implementaste esa verificación allí)
    // o por otras razones.
    if ($clienteModel->eliminarCliente($id_cliente_a_eliminar, $id_usuario_logueado)) {
        $response['status'] = 'success';
        $response['message'] = "Cliente '" . $nombre_cliente_para_mensaje . "' eliminado exitosamente.";
    } else {
        // Verificar si el cliente aún existe; si no, es un error. Si existe, puede tener ventas.
        $cliente_aun_existe = $clienteModel->getClienteByIdAndUsuarioId($id_cliente_a_eliminar, $id_usuario_logueado);
        if ($cliente_aun_existe) {
             $response['message'] = "No se pudo eliminar el cliente '" . $nombre_cliente_para_mensaje . "'. Es posible que tenga ventas asociadas. Considere marcarlo como inactivo.";
        } else {
             $response['message'] = "No se pudo eliminar el cliente '" . $nombre_cliente_para_mensaje . "'. Es posible que ya haya sido eliminado o haya ocurrido un error.";
        }
        $response['status'] = 'warning'; // O 'error' dependiendo de la causa
    }

} catch (PDOException $e) {
    error_log("Error PDO en delete_cliente.php para cliente ID {$id_cliente_a_eliminar}: " . $e->getMessage());
    // El modelo ClienteModel->eliminarCliente ya no lanza excepción por FK directamente, sino devuelve false.
    // Esta captura es para otros errores de BD.
    $response['message'] = 'Error de base de datos al intentar eliminar el cliente.';
} catch (Exception $e) { // Captura la excepción si el modelo la lanza (ej. por ventas asociadas)
    error_log("Error general en delete_cliente.php para cliente ID {$id_cliente_a_eliminar}: " . $e->getMessage());
    $response['message'] = $e->getMessage(); // Muestra el mensaje de la excepción
}

echo json_encode($response);
?>