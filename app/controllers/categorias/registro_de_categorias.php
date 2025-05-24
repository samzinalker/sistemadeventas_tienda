<?php
header('Content-Type: application/json'); // Indicar que la respuesta será JSON
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../utils/funciones_globales.php';
require_once __DIR__ . '/../../models/CategoriaModel.php';

$response = ['status' => 'error', 'message' => 'Error desconocido.'];

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_usuario'])) {
    $response['message'] = 'Debe iniciar sesión para realizar esta acción.';
    $response['redirectTo'] = $URL . '/login/'; // Sugerencia para el JS cliente
    echo json_encode($response);
    exit();
}
$id_usuario_logueado = (int)$_SESSION['id_usuario'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['nombre_categoria'])) {
    $response['message'] = 'Solicitud no válida o faltan datos.';
    echo json_encode($response);
    exit();
}

$nombre_categoria = trim($_POST['nombre_categoria']);

if (empty($nombre_categoria)) {
    $response['message'] = 'El nombre de la categoría no puede estar vacío.';
    $response['status'] = 'warning';
    echo json_encode($response);
    exit();
}

try {
    $categoriaModel = new CategoriaModel($pdo);

    if ($categoriaModel->nombreCategoriaExisteParaUsuario($nombre_categoria, $id_usuario_logueado)) {
        $response['message'] = "La categoría '" . sanear($nombre_categoria) . "' ya existe.";
        $response['status'] = 'warning';
    } else {
        $creadoId = $categoriaModel->crearCategoria($nombre_categoria, $id_usuario_logueado, $fechaHora);
        if ($creadoId) {
            $response['status'] = 'success';
            $response['message'] = "Categoría '" . sanear($nombre_categoria) . "' registrada correctamente.";
            // Podrías añadir el ID de la nueva categoría si el JS lo necesita: $response['new_category_id'] = $creadoId;
        } else {
            $response['message'] = "Error al registrar la categoría en la base de datos.";
        }
    }
} catch (PDOException $e) {
    error_log("Error de BD en registro_de_categorias.php: " . $e->getMessage());
    $response['message'] = "Error en el sistema al registrar la categoría.";
} catch (Exception $e) {
    error_log("Error general en registro_de_categorias.php: " . $e->getMessage());
    $response['message'] = "Ocurrió un error inesperado.";
}

// Guardar mensaje en sesión para mostrarlo con layout/mensajes.php si la página se recarga
// Esto es un fallback si el JS no maneja el mensaje directamente con SweetAlert desde el JSON
if (isset($response['message']) && isset($response['status'])) {
    setMensaje($response['message'], $response['status']);
}

echo json_encode($response);
exit();
?>