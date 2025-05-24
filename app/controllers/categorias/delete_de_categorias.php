<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../utils/funciones_globales.php';
require_once __DIR__ . '/../../models/CategoriaModel.php';

$response = ['status' => 'error', 'message' => 'Error desconocido.'];

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

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id_categoria'])) { // Cambiado a POST
    $response['message'] = 'ID de categoría no proporcionado o método incorrecto.';
    echo json_encode($response);
    exit();
}

$id_categoria = filter_var($_POST['id_categoria'], FILTER_VALIDATE_INT); // Cambiado a POST

if (!$id_categoria) {
    $response['message'] = 'ID de categoría no válido.';
    $response['status'] = 'warning';
    echo json_encode($response);
    exit();
}

try {
    $categoriaModel = new CategoriaModel($pdo);
    $categoria_a_eliminar = $categoriaModel->getCategoriaByIdAndUsuarioId($id_categoria, $id_usuario_logueado);

    if (!$categoria_a_eliminar) {
        $response['message'] = 'No tiene permiso para eliminar esta categoría o no existe.';
        echo json_encode($response);
        exit();
    }

    if ($categoriaModel->categoriaEnUsoPorUsuario($id_categoria, $id_usuario_logueado)) {
        $response['message'] = "No se puede eliminar la categoría '" . sanear($categoria_a_eliminar['nombre_categoria']) . "' porque está asignada a uno o más de tus productos.";
        $response['status'] = 'warning';
    } else {
        if ($categoriaModel->eliminarCategoria($id_categoria, $id_usuario_logueado)) {
            $response['status'] = 'success';
            $response['message'] = "Categoría '" . sanear($categoria_a_eliminar['nombre_categoria']) . "' eliminada correctamente.";
        } else {
            $response['message'] = "Error al eliminar la categoría de la base de datos.";
        }
    }
} catch (PDOException $e) {
    error_log("Error de BD en delete_de_categorias.php: " . $e->getMessage());
    $response['message'] = "Error en el sistema al eliminar la categoría.";
} catch (Exception $e) {
    error_log("Error general en delete_de_categorias.php: " . $e->getMessage());
    $response['message'] = "Ocurrió un error inesperado.";
}

setMensaje($response['message'], $response['status']);
echo json_encode($response);
exit();
?>