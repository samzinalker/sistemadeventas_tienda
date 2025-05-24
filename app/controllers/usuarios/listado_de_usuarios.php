<?php
// No necesitarías incluir config.php aquí si ya está incluido en el flujo principal (ej. en usuarios/index.php antes de este include)
// Pero sí necesitarás el modelo:
require_once __DIR__ . '/../../models/UsuarioModel.php'; // Ajusta la ruta según sea necesario

// Asumiendo que $pdo y $URL están disponibles desde app/config.php
if (!isset($pdo) || !isset($URL)) {
    // Esto es una salvaguarda, idealmente config.php ya está cargado
    include __DIR__ . '/../../config.php'; 
}

$usuarioModel = new UsuarioModel($pdo, $URL);
$usuarios_datos = $usuarioModel->getAllUsuarios();

// La variable $usuarios_datos ahora está lista para ser usada en la vista (usuarios/index.php)
?>