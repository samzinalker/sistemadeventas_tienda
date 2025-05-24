<?php
// --- Resumen del Archivo ---
// Nombre: app/controllers/almacen/controller_generar_siguiente_codigo.php
// Función: Este controlador se encarga de generar el siguiente código de producto disponible
//          para un usuario específico. Es invocado vía AJAX desde el formulario de creación
//          rápida de productos (por ejemplo, desde compras/create.php).
//          Utiliza AlmacenModel para interactuar con la base de datos.
// Método HTTP esperado: POST
// Parámetros POST esperados:
//   - id_usuario: El ID del usuario para el cual generar el código.
// Respuesta: JSON
//   - Éxito: {"status": "success", "nuevo_codigo": "P-XXXXX"}
//   - Error: {"status": "error", "message": "Mensaje de error detallado"}

// Inclusión de archivos de configuración y modelos necesarios.
require_once __DIR__ . '/../../config.php'; // Contiene $pdo, $URL, $fechaHora
require_once __DIR__ . '/../../models/AlmacenModel.php'; // Modelo para la lógica de almacén

// Establecer el tipo de contenido de la respuesta a JSON.
header('Content-Type: application/json');

// Respuesta por defecto en caso de errores no manejados explícitamente.
$response = ['status' => 'error', 'message' => 'Error desconocido al generar el código del producto.'];

// Iniciar la sesión si no está ya iniciada. Es crucial para acceder a $_SESSION.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verificar si el usuario está logueado. Si no, no se puede generar un código asociado a un usuario.
if (!isset($_SESSION['id_usuario'])) {
    $response['message'] = 'Sesión no iniciada o expirada. Por favor, inicie sesión de nuevo.';
    // Podrías añadir un redirectTo si el frontend lo maneja:
    // $response['redirectTo'] = $URL . '/login/';
    echo json_encode($response);
    exit();
}

// Verificar que la solicitud sea por método POST.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Método de solicitud no permitido. Se esperaba POST.';
    echo json_encode($response);
    exit();
}

// Obtener y validar el id_usuario enviado desde el frontend.
// Se espera que el JavaScript envíe el id_usuario del usuario actual (de la sesión del frontend).
$id_usuario_solicitado = filter_input(INPUT_POST, 'id_usuario', FILTER_VALIDATE_INT);

if (!$id_usuario_solicitado) {
    $response['message'] = 'ID de usuario no proporcionado o no válido en la solicitud.';
    echo json_encode($response);
    exit();
}

// Importante: Validar que el id_usuario solicitado coincida con el de la sesión actual.
// Esto previene que un usuario intente generar códigos para otro.
if ($id_usuario_solicitado !== (int)$_SESSION['id_usuario']) {
    $response['message'] = 'Conflicto de ID de usuario. Acción no permitida.';
    // Loggear este intento podría ser útil para seguridad:
    // error_log("Intento de generar código para usuario {$id_usuario_solicitado} desde sesión de usuario {$_SESSION['id_usuario']}");
    echo json_encode($response);
    exit();
}

try {
    // Instanciar el AlmacenModel, pasándole la conexión PDO.
    $almacenModel = new AlmacenModel($pdo);

    // Llamar al método del modelo que genera el siguiente código.
    // Este método ya existe en tu AlmacenModel.php.
    $nuevo_codigo = $almacenModel->generarCodigoProducto($id_usuario_solicitado);

    if ($nuevo_codigo) {
        $response['status'] = 'success';
        $response['nuevo_codigo'] = $nuevo_codigo;
        unset($response['message']); // Limpiar mensaje de error si todo fue bien.
    } else {
        // Esto podría ocurrir si generarCodigoProducto devuelve false o null por alguna razón interna.
        $response['message'] = 'El modelo no pudo generar un nuevo código.';
    }

} catch (PDOException $e) {
    // Capturar errores específicos de la base de datos.
    error_log("Error PDO en controller_generar_siguiente_codigo.php: " . $e->getMessage());
    $response['message'] = "Error de base de datos al generar el código.";
} catch (Exception $e) {
    // Capturar cualquier otra excepción general.
    error_log("Error general en controller_generar_siguiente_codigo.php: " . $e->getMessage());
    $response['message'] = "Error inesperado del servidor: " . $e->getMessage();
}

// Enviar la respuesta JSON al cliente.
echo json_encode($response);
exit();
?>