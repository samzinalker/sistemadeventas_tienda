<?php
// Este script es incluido por almacen/index.php, donde config, funciones_globales y sesion ya están cargados.
// Es importante que CategoriaModel también esté disponible si se usa en la vista principal para los modales.
require_once __DIR__ . '/../../models/AlmacenModel.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Asegurar que la sesión esté activa
}

if (!isset($_SESSION['id_usuario'])) {
    // Esta verificación es un fallback, layout/sesion.php debería haber manejado esto.
    if (isset($URL)) { redirigir('/login/'); } else { header('Location: ../../login/'); exit(); }
}
$id_usuario_logueado = (int)$_SESSION['id_usuario'];

$almacenModel = new AlmacenModel($pdo);
$productos_datos = $almacenModel->getProductosByUsuarioId($id_usuario_logueado);

// Para el modal de creación/edición de productos, necesitamos las categorías del usuario.
// Esto se puede cargar aquí o directamente en almacen/index.php antes de incluir este script.
// Si CategoriaModel ya está instanciado en almacen/index.php, puedes usar esa instancia.
// Por simplicidad, si no está ya cargado, lo hacemos aquí.
if (!isset($categorias_select_datos)) { // Evitar recargar si ya se hizo en la vista principal
    require_once __DIR__ . '/../../models/CategoriaModel.php';
    $categoriaModelLocal = new CategoriaModel($pdo);
    $categorias_select_datos = $categoriaModelLocal->getCategoriasByUsuarioId($id_usuario_logueado);
}
?>