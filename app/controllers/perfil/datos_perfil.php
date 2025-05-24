<?php
// Este script se incluye desde perfil/index.php donde config.php y layout/sesion.php ya están cargados.
// Por lo tanto, $pdo, $URL, y $_SESSION['id_usuario'] deberían estar disponibles.

// 1. Asegurar que las dependencias necesarias estén disponibles
if (!isset($pdo) || !isset($URL) || !isset($_SESSION['id_usuario'])) {
    // Esto sería un error en el flujo de inclusión.
    // layout/sesion.php ya debería haber manejado la redirección si no hay sesión.
    if (session_status() === PHP_SESSION_NONE) session_start(); // Por si acaso para setMensaje
    setMensaje("Error crítico: Faltan dependencias para cargar el perfil.", "error");
    if (isset($URL)) {
        redirigir('/login/');
    } else {
        // Fallback si $URL no está definido (muy improbable aquí)
        header('Location: ../../login/'); // Ajustar ruta si es necesario
        exit();
    }
}

// 2. Incluir el Modelo de Usuario
require_once __DIR__ . '/../../models/UsuarioModel.php'; // Ajusta la ruta si es diferente

// 3. Obtener el ID del usuario de la sesión
$id_usuario_logueado = (int)$_SESSION['id_usuario'];

// 4. Instanciar el modelo y obtener los datos del usuario
$usuarioModel = new UsuarioModel($pdo, $URL);
$usuario_datos = $usuarioModel->getUsuarioById($id_usuario_logueado);

if (!$usuario_datos) {
    // Si por alguna razón no se encuentra el usuario (ej. eliminado mientras está logueado)
    setMensaje("Error al cargar los datos del perfil. Intente iniciar sesión nuevamente.", "error");
    redirigir('/login/');
}

// 5. Asignar variables para la vista (perfil/index.php)
// Estas variables serán usadas directamente en perfil/index.php
$nombres = sanear($usuario_datos['nombres']);
$email = sanear($usuario_datos['email']); // Este es el "usuario" y el "email"
$rol = sanear($usuario_datos['nombre_rol']); // UsuarioModel devuelve 'nombre_rol'
$imagen_perfil_actual = sanear($usuario_datos['imagen_perfil'] ?? 'user_default.png');
$fyh_creacion = sanear($usuario_datos['fyh_creacion']); // Se formateará en la vista
$fyh_actualizacion = sanear($usuario_datos['fyh_actualizacion']); // Se formateará en la vista

// Verificar si la imagen de perfil existe físicamente, si no, usar la default
$ruta_fisica_imagen = __DIR__ . '/../../../public/images/perfiles/' . $imagen_perfil_actual;
if (empty($imagen_perfil_actual) || $imagen_perfil_actual === 'user_default.png' || !file_exists($ruta_fisica_imagen)) {
    $imagen_perfil_url = $URL . '/public/images/perfiles/user_default.png';
} else {
    $imagen_perfil_url = $URL . '/public/images/perfiles/' . $imagen_perfil_actual;
}

// Guardar datos del formulario en sesión para repoblar si vienen de un error en otro controlador
// Esto es para el formulario de actualizar datos personales
$form_data_key_datos = 'form_data_perfil_datos_' . $id_usuario_logueado;
$form_data_datos = $_SESSION[$form_data_key_datos] ?? [];
unset($_SESSION[$form_data_key_datos]); // Limpiar después de usar

// Estas variables se usarán para repoblar el formulario de datos personales
$nombres_form = !empty($form_data_datos['nombres']) ? sanear($form_data_datos['nombres']) : $nombres;
$email_form = !empty($form_data_datos['email']) ? sanear($form_data_datos['email']) : $email;

// No necesitamos repoblar el formulario de imagen o contraseña de esta manera,
// ya que esos campos se limpian por seguridad o su naturaleza.
?>