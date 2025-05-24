<?php
// Este script se incluye desde la vista (ej. roles/index.php)
// donde app/config.php ya debería estar cargado.

// Requerir el modelo de Rol
require_once __DIR__ . '/../../models/RolModel.php';

// Asegurarse de que $pdo esté disponible (debería estarlo si config.php se incluyó antes)
if (!isset($pdo)) {
    // Esto es una salvaguarda, idealmente config.php ya está cargado
    // y no debería ser necesario incluirlo aquí directamente.
    // Si esto se ejecuta, revisa el orden de inclusión en la vista.
    // error_log("Advertencia: config.php no fue incluido antes de listado_de_roles.php. Incluyendo ahora.");
    // require_once __DIR__ . '/../../config.php'; 
    // Es mejor que la vista que incluye este controlador asegure que $pdo existe.
    die("Error: La conexión a la base de datos (pdo) no está disponible. Asegúrate de que app/config.php se incluye antes.");
}

// Instanciar el modelo
$rolModel = new RolModel($pdo);
// Obtener todos los roles
$roles_datos = $rolModel->getAllRoles();

// La variable $roles_datos ahora está lista para ser usada en la vista (roles/index.php)
?>