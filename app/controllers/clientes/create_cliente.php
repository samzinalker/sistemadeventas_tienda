<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../utils/funciones_globales.php';
require_once __DIR__ . '/../../models/ClienteModel.php';

$response = ['status' => 'error', 'message' => 'Error desconocido.'];

if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['id_usuario'])) {
    $response['message'] = 'Debe iniciar sesión.';
    $response['redirectTo'] = $URL . '/login/';
    echo json_encode($response);
    exit();
}

$id_usuario_logueado = (int)$_SESSION['id_usuario'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Método no permitido.';
    echo json_encode($response);
    exit();
}

// Validaciones básicas
$nombre_cliente = trim($_POST['nombre_cliente'] ?? '');
$tipo_documento = $_POST['tipo_cliente'] ?? '';
$celular_cliente = trim($_POST['celular_cliente'] ?? '');

if (empty($nombre_cliente) || empty($tipo_documento) || empty($celular_cliente)) {
    $response['message'] = 'Faltan campos obligatorios: Nombre, Tipo y Celular.';
    $response['status'] = 'warning';
    echo json_encode($response);
    exit();
}

try {
    $clienteModel = new ClienteModel($pdo, $URL);
    
    // Procesar documento
    $documento = trim($_POST['nit_ci_cliente'] ?? '');
    if (empty($documento) && $tipo_documento === 'consumidor_final') {
        $documento = '9999999999999'; // Documento genérico
    }
    
    // Validar documento si es necesario
    if (!empty($documento) && $documento !== '9999999999999') {
        $validacion = $clienteModel->validarDocumentoEcuatoriano($documento, $tipo_documento);
        if (!$validacion['valido']) {
            $response['message'] = $validacion['mensaje'];
            $response['status'] = 'warning';
            echo json_encode($response);
            exit();
        }
        
        // Verificar duplicados
        if ($clienteModel->documentoExisteParaOtroCliente($documento, $id_usuario_logueado)) {
            $response['message'] = 'Ya existe un cliente con este documento.';
            $response['status'] = 'warning';
            echo json_encode($response);
            exit();
        }
    }

    // Preparar datos
    $datos_cliente = [
        'nombre_cliente' => $nombre_cliente,
        'nit_ci_cliente' => $documento,
        'tipo_documento' => $tipo_documento,
        'celular_cliente' => $celular_cliente,
        'telefono_fijo' => trim($_POST['telefono_fijo'] ?? '') ?: null,
        'email_cliente' => trim($_POST['email_cliente'] ?? '') ?: null,
        'direccion' => trim($_POST['direccion'] ?? '') ?: null,
        'ciudad' => trim($_POST['ciudad'] ?? '') ?: null,
        'provincia' => trim($_POST['provincia'] ?? '') ?: null,
        'fecha_nacimiento' => $_POST['fecha_nacimiento'] ?: null,
        'observaciones' => trim($_POST['observaciones'] ?? '') ?: null,
        'estado' => $_POST['estado'] ?? 'activo',
        'id_usuario' => $id_usuario_logueado,
        'fyh_creacion' => $fechaHora,
        'fyh_actualizacion' => $fechaHora
    ];

    $cliente_id = $clienteModel->crearCliente($datos_cliente);
    
    if ($cliente_id) {
        $response['status'] = 'success';
        $response['message'] = "Cliente '{$nombre_cliente}' registrado exitosamente.";
        $response['cliente_id'] = $cliente_id;
    } else {
        $response['message'] = 'No se pudo registrar el cliente.';
    }

} catch (PDOException $e) {
    error_log("Error PDO en create_cliente.php: " . $e->getMessage());
    $response['message'] = 'Error de base de datos.';
} catch (Exception $e) {
    error_log("Error general en create_cliente.php: " . $e->getMessage());
    $response['message'] = 'Error del servidor.';
}

echo json_encode($response);
?>