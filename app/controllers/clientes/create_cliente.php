<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php'; // $pdo, $URL, $fechaHora
require_once __DIR__ . '/../../utils/Validator.php'; // Para validaciones futuras si se requieren aquí
require_once __DIR__ . '/../../models/ClienteModel.php';

$response = ['status' => 'error', 'message' => 'Error desconocido al crear el cliente.'];

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

// --- Recolección y Validación de Datos ---
$nombre_cliente = trim($_POST['nombre_cliente'] ?? '');
$tipo_documento = trim($_POST['tipo_documento'] ?? ''); // Ajustado a 'tipo_documento'
$nit_ci_cliente_raw = trim($_POST['nit_ci_cliente'] ?? '');
$celular_cliente = trim($_POST['celular_cliente'] ?? '');
// No es obligatorio, pero tenerlo para la lógica de consumidor final
// $celular_cliente = trim($_POST['celular_cliente'] ?? '');


// Validaciones básicas de campos requeridos por el formulario
$required_fields_list = ['nombre_cliente', 'tipo_documento'];
// Celular es opcional en BD, pero tu JS lo puede marcar como requerido, aquí lo tomamos como opcional
// if (empty($celular_cliente)) $required_fields_list[] = 'celular_cliente'; 


$missingFields = [];
foreach($required_fields_list as $field) {
    if (empty($_POST[$field])) {
        $missingFields[] = ucfirst(str_replace('_', ' ', $field));
    }
}

if (!empty($missingFields)) {
    $response['message'] = 'Campos obligatorios faltantes: ' . implode(', ', $missingFields) . '.';
    $response['status'] = 'warning';
    echo json_encode($response);
    exit;
}

// Lógica para consumidor final y documento
$nit_ci_cliente = $nit_ci_cliente_raw;
if ($tipo_documento === 'consumidor_final') {
    if (empty($nit_ci_cliente_raw) || $nit_ci_cliente_raw === '9999999999') {
        $nit_ci_cliente = '9999999999'; // Documento genérico estándar
    }
    // Si se provee otro documento para consumidor final (aunque no es lo usual), se respeta.
} elseif (empty($nit_ci_cliente_raw) && ($tipo_documento === 'cedula' || $tipo_documento === 'ruc' || $tipo_documento === 'pasaporte')) {
    $response['message'] = 'El número de documento es obligatorio para el tipo ' . $tipo_documento . '.';
    $response['status'] = 'warning';
    echo json_encode($response);
    exit;
}


try {
    $clienteModel = new ClienteModel($pdo, $URL);

    // Validación de formato de documento (desde el Modelo)
    if (!empty($nit_ci_cliente) && $tipo_documento !== 'otro' && $tipo_documento !== 'extranjero') { // No validar 'otro' o 'extranjero' con este método
        $validacion_doc = $clienteModel->validarDocumentoEcuatoriano($nit_ci_cliente, $tipo_documento);
        if (!$validacion_doc['valido']) {
            $response['message'] = $validacion_doc['mensaje'];
            $response['status'] = 'warning';
            echo json_encode($response);
            exit();
        }
    }
    
    // Verificar si el documento ya existe para este usuario (excluyendo consumidor final genérico)
    if (!empty($nit_ci_cliente) && !($tipo_documento === 'consumidor_final' && $nit_ci_cliente === '9999999999')) {
        if ($clienteModel->documentoExisteParaOtroCliente($nit_ci_cliente, $tipo_documento, $id_usuario_logueado)) {
            $response['message'] = "Ya existe un cliente con el documento '$nit_ci_cliente' ($tipo_documento).";
            $response['status'] = 'warning';
            echo json_encode($response);
            exit();
        }
    }

    $fecha_nacimiento_raw = trim($_POST['fecha_nacimiento'] ?? '');
    $fecha_nacimiento = !empty($fecha_nacimiento_raw) ? $fecha_nacimiento_raw : null;


    $datos_cliente = [
        'id_usuario' => $id_usuario_logueado,
        'nombre_cliente' => $nombre_cliente,
        'tipo_documento' => $tipo_documento,
        'nit_ci_cliente' => $nit_ci_cliente ?: null, // Guardar null si está vacío
        'celular_cliente' => $celular_cliente ?: null,
        'telefono_fijo' => trim($_POST['telefono_fijo'] ?? '') ?: null,
        'email_cliente' => trim($_POST['email_cliente'] ?? '') ?: null,
        'direccion' => trim($_POST['direccion'] ?? '') ?: null,
        'ciudad' => trim($_POST['ciudad'] ?? '') ?: null,
        'provincia' => trim($_POST['provincia'] ?? '') ?: null,
        'fecha_nacimiento' => $fecha_nacimiento,
        'observaciones' => trim($_POST['observaciones'] ?? '') ?: null,
        'estado' => $_POST['estado'] ?? 'activo',
        'fyh_creacion' => $fechaHora, 
        'fyh_actualizacion' => $fechaHora
    ];

    $nuevo_cliente_id = $clienteModel->crearCliente($datos_cliente);

    if ($nuevo_cliente_id) {
        $response['status'] = 'success';
        $response['message'] = "Cliente '" . htmlspecialchars($nombre_cliente) . "' registrado exitosamente.";
        $response['cliente_id'] = $nuevo_cliente_id;
    } else {
        $response['message'] = 'Error al guardar el cliente en la base de datos. Verifique los logs.';
    }

} catch (PDOException $e) {
    error_log("Error PDO en create_cliente.php: " . $e->getMessage());
    if ($e->getCode() == '23000') { 
        $response['message'] = "Error de duplicidad: Ya existe un cliente con un documento similar para usted.";
    } else {
        $response['message'] = 'Error de base de datos al crear el cliente.';
    }
} catch (Exception $e) {
    error_log("Error general en create_cliente.php: " . $e->getMessage());
    $response['message'] = 'Error inesperado del servidor: ' . $e->getMessage();
}

echo json_encode($response);
?>