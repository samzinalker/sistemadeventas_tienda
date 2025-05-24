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

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['id_categoria']) || !isset($_POST['nombre_categoria'])) {
    $response['message'] = 'Datos incompletos para actualizar.';
    echo json_encode($response);
    exit();
}

$id_categoria = filter_var($_POST['id_categoria'], FILTER_VALIDATE_INT);
$nombre_categoria_nuevo = trim($_POST['nombre_categoria']);

if (!$id_categoria || empty($nombre_categoria_nuevo)) {
    $response['message'] = 'ID de categoría o nombre no válidos.';
    $response['status'] = 'warning';
    echo json_encode($response);
    exit();
}

try {
    $categoriaModel = new CategoriaModel($pdo);
    $categoria_existente = $categoriaModel->getCategoriaByIdAndUsuarioId($id_categoria, $id_usuario_logueado);

    if (!$categoria_existente) {
        $response['message'] = 'No tiene permiso para modificar esta categoría o no existe.';
        echo json_encode($response);
        exit();
    }

    if ($categoriaModel->nombreCategoriaExisteParaUsuario($nombre_categoria_nuevo, $id_usuario_logueado, $id_categoria)) {
        $response['message'] = "El nombre de categoría '" . sanear($nombre_categoria_nuevo) . "' ya está en uso por otra de tus categorías.";
        $response['status'] = 'warning';
    } else {
        if ($categoriaModel->actualizarCategoria($id_categoria, $nombre_categoria_nuevo, $id_usuario_logueado, $fechaHora)) {
            $response['status'] = 'success';
            $response['message'] = "Categoría actualizada correctamente.";
        } else {
            $response['message'] = "Error al actualizar la categoría en la base de datos.";
        }
    }
} catch (PDOException $e) {
    error_log("Error de BD en update_de_categorias.php: " . $e->getMessage());
    $response['message'] = "Error en el sistema al actualizar la categoría.";
} catch (Exception $e) {
    error_log("Error general en update_de_categorias.php: " . $e->getMessage());
    $response['message'] = "Ocurrió un error inesperado.";
}

setMensaje($response['message'], $response['status']);
echo json_encode($response);
exit();
?>