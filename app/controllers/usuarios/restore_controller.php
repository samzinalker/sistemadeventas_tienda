<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../utils/funciones_globales.php';
require_once __DIR__ . '/../../models/UsuarioModel.php';

// Verificar permisos de administrador
if (!isset($_SESSION['id_usuario']) || strtolower(trim($_SESSION['rol'])) !== 'administrador') {
    setMensaje("No tienes permisos para realizar esta acción.", "error");
    redirigir('/usuarios/');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setMensaje("Acceso no permitido.", "error");
    redirigir('/usuarios/eliminados.php');
}

$id_usuario_restaurar = filter_input(INPUT_POST, 'id_usuario_a_restaurar', FILTER_VALIDATE_INT);

if (!$id_usuario_restaurar) {
    setMensaje("ID de usuario no válido.", "error");
    redirigir('/usuarios/eliminados.php');
}

$usuarioModel = new UsuarioModel($pdo, $URL);
$resultado = $usuarioModel->restaurarUsuario($id_usuario_restaurar, $fechaHora);

if ($resultado['success']) {
    // Log de auditoría
    $usuario_restaurado = $resultado['usuario_restaurado'];
    error_log("Usuario restaurado: {$usuario_restaurado['nombres']} (ID: {$usuario_restaurado['id_usuario']}) por admin ID: {$_SESSION['id_usuario']} el {$fechaHora}");
    
    setMensaje($resultado['message'], "success");
} else {
    setMensaje($resultado['message'], "error");
}

redirigir('/usuarios/eliminados.php');
?>