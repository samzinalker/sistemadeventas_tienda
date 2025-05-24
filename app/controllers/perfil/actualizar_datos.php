<?php
// 1. Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Incluir configuración y dependencias
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../utils/Validator.php';
require_once __DIR__ . '/../../utils/funciones_globales.php';
require_once __DIR__ . '/../../models/UsuarioModel.php';

// 3. Verificar que el usuario esté logueado
if (!isset($_SESSION['id_usuario'])) {
    setMensaje("Debe iniciar sesión para realizar esta acción.", "error");
    redirigir('/login/');
}
$id_usuario_actualizar = (int)$_SESSION['id_usuario'];

// 4. Verificar que la solicitud sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setMensaje("Acceso no permitido.", "error");
    redirigir('/perfil/');
}

// 5. Obtener y sanear datos del formulario
$nombres_nuevos = trim($_POST['nombres'] ?? '');
$email_nuevo = trim($_POST['email'] ?? ''); // Este es el "usuario" y "email"

// Guardar datos en sesión para repoblar el formulario en caso de error
$form_data_key = 'form_data_perfil_datos_' . $id_usuario_actualizar;
$_SESSION[$form_data_key] = $_POST;

// 6. Validaciones
if (empty($nombres_nuevos) || empty($email_nuevo)) {
    setMensaje("El nombre y el email/usuario son obligatorios.", "warning");
    redirigir('/perfil/');
}

if (!Validator::isValidEmail($email_nuevo)) {
    setMensaje("El formato del email/usuario no es válido.", "error");
    redirigir('/perfil/');
}

// 7. Lógica de actualización usando el Modelo
try {
    $usuarioModel = new UsuarioModel($pdo, $URL);

    // Verificar si el nuevo email ya está en uso por OTRO usuario
    if ($usuarioModel->emailExiste($email_nuevo, $id_usuario_actualizar)) {
        setMensaje("El email/usuario '" . sanear($email_nuevo) . "' ya está en uso por otra cuenta.", "error");
        redirigir('/perfil/');
    }

    // Obtener el rol actual del usuario (no se cambia desde el perfil)
    $usuario_actual_data = $usuarioModel->getUsuarioById($id_usuario_actualizar);
    if (!$usuario_actual_data) { // Muy improbable si está logueado
        setMensaje("Error al obtener datos del usuario actual.", "error");
        redirigir('/perfil/');
    }
    $id_rol_actual = $usuario_actual_data['id_rol'];

    // Actualizar los datos del usuario (nombre, email, pero no el rol)
    // $fechaHora viene de config.php
    $actualizado = $usuarioModel->actualizarUsuario(
        $id_usuario_actualizar,
        $nombres_nuevos,
        $email_nuevo,
        $id_rol_actual, // Se mantiene el rol actual del usuario
        $fechaHora
    );

    if ($actualizado) {
        // Actualizar los datos en la sesión para que se reflejen inmediatamente
        $_SESSION['nombres'] = $nombres_nuevos;
        $_SESSION['sesion_email'] = $email_nuevo; // 'sesion_email' es usado por layout/sesion.php

        unset($_SESSION[$form_data_key]); // Limpiar datos del formulario de la sesión en caso de éxito
        setMensaje("Datos personales actualizados correctamente.", "success");
        redirigir('/perfil/');
    } else {
        setMensaje("No se pudieron actualizar los datos personales. Inténtelo de nuevo.", "error");
        redirigir('/perfil/');
    }

} catch (PDOException $e) {
    error_log("Error de BD en actualizar_datos.php: " . $e->getMessage());
    setMensaje("Error en el sistema al actualizar datos. Por favor, intente más tarde.", "error");
    redirigir('/perfil/');
} catch (Exception $e) {
    error_log("Error general en actualizar_datos.php: " . $e->getMessage());
    setMensaje("Ocurrió un error inesperado. Por favor, intente más tarde.", "error");
    redirigir('/perfil/');
}
?>