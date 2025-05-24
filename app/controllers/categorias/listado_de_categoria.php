<?php
// Este script se incluye desde categorias/index.php donde config.php, funciones_globales.php y layout/sesion.php ya están cargados.
// Por lo tanto, $pdo, $URL, $_SESSION['id_usuario'] y las funciones globales deberían estar disponibles.

// 1. Asegurar que las dependencias necesarias estén disponibles
if (!isset($pdo) || !isset($URL) || !isset($_SESSION['id_usuario'])) {
    if (session_status() === PHP_SESSION_NONE) session_start();
    setMensaje("Error crítico: Faltan dependencias para listar categorías.", "error");
    if (isset($URL)) redirigir('/login/');
    else { header('Location: ../../login/'); exit(); }
}

// 2. Incluir el Modelo de Categoría
require_once __DIR__ . '/../../models/CategoriaModel.php'; // Ajusta la ruta si es diferente

// 3. Obtener el ID del usuario de la sesión
$id_usuario_logueado = (int)$_SESSION['id_usuario'];

// 4. Instanciar el modelo y obtener las categorías del usuario
$categoriaModel = new CategoriaModel($pdo);
$categorias_datos = $categoriaModel->getCategoriasByUsuarioId($id_usuario_logueado);

// La variable $categorias_datos ahora está lista para ser usada en la vista (categorias/index.php)
// y ya viene filtrada por el id_usuario.
?>