<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php'; // $pdo, $URL, $fechaHora
require_once __DIR__ . '/../../utils/Validator.php';
require_once __DIR__ . '/../../models/ClienteModel.php';

$response = ['status' => 'error', 'message' => 'Error desconocido al actualizar el cliente.'];

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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Método de solicitud no permitido.';
    echo json_encode($response);
    exit();
}

$id_cliente_update = filter_input(INPUT_POST, 'id_cliente_update', FILTER_VALIDATE_INT);
if (!$id_cliente_update) {
    $response['message'] = 'ID de cliente para actualizar no proporcionado o inválido.';
    echo json_encode($response);
    exit();
}

// Recolección de datos del formulario de edición
$nombre_cliente = trim($_POST['nombre_cliente_update'] ?? '');
$tipo_documento = trim($_POST['tipo_documento_update'] ?? '');
$nit_ci_cliente_raw = trim($_POST['nit_ci_cliente_update'] ?? '');
$celular_cliente = trim($_POST['celular_cliente_update'] ?? '');
// $celular_cliente = trim($_POST['celular_cliente_update'] ?? '');


$required_fields_list = ['nombre_cliente_update', 'tipo_documento_update'];
// if (empty($celular_cliente)) $required_fields_list[] = 'celular_cliente_update';


$missingFields = [];
foreach ($required_fields_list as $field_key) {
    if (empty($_POST[$field_key])) {
         // Convertir 'nombre_cliente_update' a 'Nombre cliente' para el mensaje
        $readable_field = ucfirst(str_replace('_', ' ', str_replace('_update', '', $field_key)));
        $missingFields[] = $readable_field;
    }
}

if (!empty($missingFields)) {
    $response['message'] = 'Campos obligatorios faltantes: ' . implode(', ', $missingFields) . '.';
    $response['status'] = 'warning';
    echo json_encode($response);
    exit;
}


$nit_ci_cliente = $nit_ci_cliente_raw;
if ($tipo_documento === 'consumidor_final') {
    if (empty($nit_ci_cliente_raw) || $nit_ci_cliente_raw === '9999999999') {
        $nit_ci_cliente = '9999999999';
    }
} elseif (empty($nit_ci_cliente_raw) && ($tipo_documento === 'cedula' || $tipo_documento === 'ruc' || $tipo_documento === 'pasaporte')) {
    $response['message'] = 'El número de documento es obligatorio para el tipo ' . $tipo_documento . '.';
    $response['status'] = 'warning';
    echo json_encode($response);
    exit;
}


try {
    $clienteModel = new ClienteModel($pdo, $URL);

    $cliente_existente = $clienteModel->getClienteByIdAndUsuarioId($id_cliente_update, $id_usuario_logueado);
    if (!$cliente_existente) {
        $response['message'] = 'Cliente no encontrado o no tiene permiso para modificarlo.';
        echo json_encode($response);
        exit();
    }
    
    if (!empty($nit_ci_cliente) && $tipo_documento !== 'otro' && $tipo_documento !== 'extranjero') {
        $validacion_doc = $clienteModel->validarDocumentoEcuatoriano($nit_ci_cliente, $tipo_documento);
        if (!$validacion_doc['valido']) {
            $response['message'] = $validacion_doc['mensaje'];
            $response['status'] = 'warning';
            echo json_encode($response);
            exit();
        }
    }

    if (!empty($nit_ci_cliente) && !($tipo_documento === 'consumidor_final' && $nit_ci_cliente === '9999999999')) {
        if ($clienteModel->documentoExisteParaOtroCliente($nit_ci_cliente, $tipo_documento, $id_usuario_logueado, $id_cliente_update)) {
            $response['message'] = "Ya existe OTRO cliente con el documento '$nit_ci_cliente' ($tipo_documento).";
            $response['status'] = 'warning';
            echo json_encode($response);
            exit();
        }
    }
    
    $fecha_nacimiento_raw = trim($_POST['fecha_nacimiento_update'] ?? '');
    $fecha_nacimiento = !empty($fecha_nacimiento_raw) ? $fecha_nacimiento_raw : null;

    $datos_para_actualizar = [
        'nombre_cliente' => $nombre_cliente,
        'tipo_documento' => $tipo_documento,
        'nit_ci_cliente' => $nit_ci_cliente ?: null,
        'celular_cliente' => $celular_cliente ?: null,
        'telefono_fijo' => trim($_POST['telefono_fijo_update'] ?? '') ?: null,
        'email_cliente' => trim($_POST['email_cliente_update'] ?? '') ?: null,
        'direccion' => trim($_POST['direccion_update'] ?? '') ?: null,
        'ciudad' => trim($_POST['ciudad_update'] ?? '') ?: null,
        'provincia' => trim($_POST['provincia_update'] ?? '') ?: null,
        'fecha_nacimiento' => $fecha_nacimiento,
        'observaciones' => trim($_POST['observaciones_update'] ?? '') ?: null,
        'estado' => $_POST['estado_update'] ?? 'activo',
        'fyh_actualizacion' => $fechaHora 
    ];

    if ($clienteModel->actualizarCliente($id_cliente_update, $id_usuario_logueado, $datos_para_actualizar)) {
        $response['status'] = 'success';
        $response['message'] = "Cliente '" . htmlspecialchars($nombre_cliente) . "' actualizado exitosamente.";
    } else {
        $response['message'] = 'No se pudo actualizar el cliente o no hubo cambios detectados.';
    }

} catch (PDOException $e) {
    error_log("Error PDO en update_cliente.php: " . $e->getMessage());
     if ($e->getCode() == '23000') { 
        $response['message'] = "Error de duplicidad: Ya existe un cliente con un documento similar para usted.";
    } else {
        $response['message'] = 'Error de base de datos al actualizar el cliente.';
    }
} catch (Exception $e) {
    error_log("Error general en update_cliente.php: " . $e->getMessage());
    $response['message'] = 'Error inesperado del servidor: ' . $e->getMessage();
}

echo json_encode($response);
?>