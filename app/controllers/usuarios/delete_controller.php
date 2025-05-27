<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../utils/funciones_globales.php';
require_once __DIR__ . '/../../models/UsuarioModel.php';

// Verificar sesión y permisos
if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['rol'])) {
    setMensaje("Acceso denegado. Sesión no iniciada.", "error");
    redirigir('/login.php');
}

if (strtolower(trim($_SESSION['rol'])) !== 'administrador') {
    setMensaje("No tienes permisos para realizar esta acción.", "error");
    redirigir('/usuarios/');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setMensaje("Acceso no permitido. Método incorrecto.", "error");
    redirigir('/usuarios/');
}

$id_usuario_eliminar = filter_input(INPUT_POST, 'id_usuario_a_eliminar', FILTER_VALIDATE_INT);

if (!$id_usuario_eliminar) {
    setMensaje("ID de usuario no válido para eliminación.", "error");
    redirigir('/usuarios/');
}

if ($id_usuario_eliminar == $_SESSION['id_usuario']) {
    setMensaje("No puedes eliminar tu propia cuenta de administrador.", "warning");
    redirigir('/usuarios/');
}

// ✅ NUEVO: Usar eliminación completa con respaldo
$usuarioModel = new UsuarioModel($pdo, $URL);
$resultado_eliminacion = $usuarioModel->eliminarUsuarioConRespaldoCompleto(
    $id_usuario_eliminar, 
    $_SESSION['id_usuario'], 
    $fechaHora
);

if ($resultado_eliminacion['success']) {
    $backup_info = $resultado_eliminacion['backup_info'];
    $datos_eliminados = $resultado_eliminacion['datos_eliminados'];
    
    // Log de auditoría detallado
    $log_mensaje = "ELIMINACIÓN COMPLETA DE USUARIO:\n";
    $log_mensaje .= "- Usuario eliminado ID: {$id_usuario_eliminar}\n";
    $log_mensaje .= "- Eliminado por: {$_SESSION['nombres']} (ID: {$_SESSION['id_usuario']})\n";
    $log_mensaje .= "- Fecha: {$fechaHora}\n";
    $log_mensaje .= "- Respaldo creado: {$backup_info['backup_folder']}\n";
    $log_mensaje .= "- Datos eliminados: " . json_encode($datos_eliminados);
    
    error_log($log_mensaje);
    
    // Mensaje de éxito con detalles
    $mensaje_usuario = $resultado_eliminacion['message'];
    if (!empty($datos_eliminados)) {
        $detalles = [];
        foreach ($datos_eliminados as $tipo => $cantidad) {
            if ($cantidad > 0) {
                $detalles[] = "$cantidad " . ucfirst($tipo);
            }
        }
        if (!empty($detalles)) {
            $mensaje_usuario .= " Datos eliminados: " . implode(', ', $detalles) . ".";
        }
    }
    
    setMensaje($mensaje_usuario, "success");
} else {
    setMensaje($resultado_eliminacion['message'], "error");
    
    // Log del error
    error_log("ERROR al eliminar usuario ID $id_usuario_eliminar: " . $resultado_eliminacion['message']);
}

redirigir('/usuarios/');
?>