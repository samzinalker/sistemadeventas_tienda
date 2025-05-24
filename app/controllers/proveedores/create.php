<?php
// Resumen: Controlador para crear un nuevo proveedor.
// Recibe datos vía POST, los valida (incluyendo emails con caracteres Unicode/IDN), 
// y utiliza ProveedorModel para la inserción.
// Devuelve una respuesta JSON indicando el resultado de la operación.

header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';        // Define $pdo, $URL, $fechaHora
require_once __DIR__ . '/../../utils/funciones_globales.php'; // Para sanear()
require_once __DIR__ . '/../../models/ProveedorModel.php';

$response = ['status' => 'error', 'message' => 'Error desconocido al crear el proveedor.'];

if (session_status() === PHP_SESSION_NONE) { session_start(); }

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

// Validación de campos
$nombre_proveedor = trim($_POST['nombre_proveedor'] ?? '');
$celular = trim($_POST['celular'] ?? '');
$empresa = trim($_POST['empresa'] ?? ''); // Empresa es opcional según el HTML, pero si es requerida, añadir validación
$direccion = trim($_POST['direccion'] ?? ''); // Dirección es opcional según el HTML
$telefono = trim($_POST['telefono'] ?? null); 
$email = trim($_POST['email'] ?? null);       

// Validaciones básicas
if (empty($nombre_proveedor)) {
    $response['message'] = 'El nombre del proveedor es requerido.';
    $response['status'] = 'warning';
    echo json_encode($response);
    exit();
}
if (empty($celular)) {
    $response['message'] = 'El celular del proveedor es requerido.';
    $response['status'] = 'warning';
    echo json_encode($response);
    exit();
}

// Validación de Email con soporte para Unicode (IDN)
if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL, FILTER_FLAG_EMAIL_UNICODE)) {
    $response['message'] = 'El formato del email no es válido.';
    $response['status'] = 'warning';
    echo json_encode($response);
    exit();
}
// Aquí podrías añadir más validaciones: longitud de campos, etc.



try {
    $proveedorModel = new ProveedorModel($pdo, $URL);

    $datos_proveedor = [
        'nombre_proveedor' => $nombre_proveedor,
        'celular' => $celular,
        'telefono' => $telefono ?: null,
        'empresa' => $empresa ?: null, // Guardar null si está vacío y es opcional
        'email' => $email ?: null,
        'direccion' => $direccion ?: null, // Guardar null si está vacío y es opcional
        'id_usuario' => $id_usuario_logueado,
        'fyh_creacion' => $fechaHora,
        'fyh_actualizacion' => $fechaHora
    ];

    $creadoId = $proveedorModel->crearProveedor($datos_proveedor);

    if ($creadoId) {
        $response['status'] = 'success';
        $response['message'] = "Proveedor '" . sanear($nombre_proveedor) . "' registrado correctamente.";
    } else {
        $response['message'] = "No se pudo registrar el proveedor en la base de datos.";
    }

} catch (PDOException $e) {
    error_log("PDO Error en create_proveedor.php: " . $e->getMessage());
    $response['message'] = "Error de base de datos al crear el proveedor.";
} catch (Exception $e) {
    error_log("General Error en create_proveedor.php: " . $e->getMessage());
    $response['message'] = "Error inesperado del servidor al crear el proveedor.";
}

echo json_encode($response);
?>