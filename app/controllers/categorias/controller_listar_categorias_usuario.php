<?php
header('Content-Type: application/json'); // Siempre primero para respuestas JSON

// Iniciar sesión si no está iniciada, para acceder a $_SESSION['id_usuario']
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Incluir archivos de configuración y modelos necesarios
require_once __DIR__ . '/../../config.php'; // Para $pdo, $URL
require_once __DIR__ . '/../../models/CategoriaModel.php';
// funciones_globales.php no parece ser estrictamente necesario aquí para listar.

$response = ['status' => 'error', 'message' => 'Error desconocido al cargar categorías.'];

// Verificar si el usuario está logueado
if (!isset($_SESSION['id_usuario'])) {
    $response['message'] = 'Sesión no iniciada. No se pueden cargar las categorías.';
    // $response['redirectTo'] = $URL . '/login/'; // Podrías añadir esto si quieres que JS redirija
    echo json_encode($response);
    exit();
}

$id_usuario_logueado = (int)$_SESSION['id_usuario'];

try {
    $categoriaModel = new CategoriaModel($pdo);
    $categorias_datos = $categoriaModel->getCategoriasByUsuarioId($id_usuario_logueado);

    // getCategoriasByUsuarioId devuelve un array (puede estar vacío si no hay categorías)
    // No es necesario verificar si es false, ya que el modelo siempre devuelve un array.
    $response['status'] = 'success';
    $response['data'] = $categorias_datos; // $categorias_datos será un array (posiblemente vacío)
    $response['message'] = 'Categorías cargadas.'; // Mensaje opcional

} catch (PDOException $e) {
    error_log("Error de BD en controller_listar_categorias_usuario.php: " . $e->getMessage());
    $response['message'] = "Error en el sistema al cargar categorías.";
} catch (Exception $e) {
    error_log("Error general en controller_listar_categorias_usuario.php: " . $e->getMessage());
    $response['message'] = "Ocurrió un error inesperado al cargar categorías.";
}

echo json_encode($response);
exit();
?>