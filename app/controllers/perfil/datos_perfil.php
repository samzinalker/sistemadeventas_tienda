<?php
// Este script se incluye desde perfil/index.php donde:
// - session_start() ya ha sido llamado.
// - config.php ($pdo, $URL, $fechaHora) ya está cargado.
// - funciones_globales.php (sanear(), setMensaje(), redirigir()) ya está cargado.
// - layout/sesion.php ya ha validado la sesión y $_SESSION['id_usuario'] está disponible.

// 1. Doble verificación de dependencias esenciales (aunque layout/sesion.php ya lo hace)
if (!isset($pdo, $URL, $_SESSION['id_usuario'])) {
    // Esto indica un problema en el flujo de inclusión o una sesión corrupta.
    // layout/sesion.php debería haber redirigido si no hay sesión.
    if (session_status() === PHP_SESSION_NONE) session_start(); // Asegurar sesión para setMensaje
    setMensaje("Error crítico: Faltan dependencias para cargar el perfil. Por favor, inicie sesión de nuevo.", "error");
    
    // Si $URL está disponible, usarlo para redirigir.
    if (isset($URL)) {
        redirigir($URL . '/login/');
    } else {
        // Fallback muy improbable si $URL no está definido.
        // Asumimos que estamos en app/controllers/perfil/
        header('Location: ../../../login/'); 
        exit();
    }
}

// 2. Incluir el Modelo de Usuario
// La ruta es relativa a este archivo (app/controllers/perfil/datos_perfil.php)
require_once __DIR__ . '/../../models/UsuarioModel.php'; 

// 3. Obtener el ID del usuario de la sesión (AISLAMIENTO DEL USUARIO)
// Todas las operaciones de perfil se basarán en este ID.
$id_usuario_logueado = (int)$_SESSION['id_usuario'];

// 4. Instanciar el modelo y obtener los datos del usuario
// CORRECCIÓN AQUÍ: Asegurarse de pasar todos los argumentos esperados por el constructor de UsuarioModel.
// El error indica que espera 2 argumentos, usualmente $pdo y $URL.
$usuarioModel = new UsuarioModel($pdo, $URL); // <--- LÍNEA CORREGIDA
$usuario_datos = $usuarioModel->getUsuarioById($id_usuario_logueado);

if (!$usuario_datos) {
    // Si por alguna razón no se encuentra el usuario (ej. cuenta eliminada mientras está logueado,
    // o un id_usuario inválido en la sesión).
    setMensaje("Error al cargar los datos de su perfil. Es posible que su sesión haya expirado o su cuenta ya no exista. Por favor, intente iniciar sesión nuevamente.", "error");
    redirigir($URL . '/login/'); // $URL está disponible aquí
}

// 5. Asignar variables para la vista (perfil/index.php)
// Estas variables serán usadas directamente en perfil/index.php
// Se aplica saneamiento aquí para asegurar que los datos del modelo son seguros antes de pasarlos.
$nombres = sanear($usuario_datos['nombres'] ?? 'N/A');
$email = sanear($usuario_datos['email'] ?? 'N/A'); // Este es el "usuario" y el "email"
$rol = sanear($usuario_datos['nombre_rol'] ?? 'Rol no definido'); // UsuarioModel debería devolver 'nombre_rol' a través del JOIN
$imagen_perfil_actual_db = $usuario_datos['imagen_perfil'] ?? null; // Nombre del archivo de imagen desde la BD
$fyh_creacion = $usuario_datos['fyh_creacion'] ?? '0000-00-00 00:00:00'; // Se formateará en la vista
$fyh_actualizacion = $usuario_datos['fyh_actualizacion'] ?? '0000-00-00 00:00:00'; // Se formateará en la vista

// 6. Determinar la URL de la imagen de perfil
$nombre_imagen_default = 'user_default.PNG'; // Asegúrate que el nombre coincida con tu archivo
$ruta_base_imagenes_perfil_fisica = __DIR__ . '/../../../public/images/perfiles/';
$url_base_imagenes_perfil = $URL . '/public/images/perfiles/';

if (!empty($imagen_perfil_actual_db) && $imagen_perfil_actual_db !== $nombre_imagen_default && file_exists($ruta_base_imagenes_perfil_fisica . $imagen_perfil_actual_db)) {
    $imagen_perfil_url = $url_base_imagenes_perfil . sanear($imagen_perfil_actual_db);
} else {
    $imagen_perfil_url = $url_base_imagenes_perfil . $nombre_imagen_default;
}

// 7. Preparar datos para repoblar el formulario de "Actualizar Datos Personales"
// Esto es útil si hubo un error en el controlador app/controllers/perfil/actualizar_datos.php
// y se redirigió de vuelta al perfil. El controlador de actualización debería guardar los datos
// en $_SESSION[$form_data_key_datos] antes de redirigir.

$form_data_key_datos = 'form_data_perfil_datos_' . $id_usuario_logueado; // Clave de sesión única por usuario
if (isset($_SESSION[$form_data_key_datos]) && is_array($_SESSION[$form_data_key_datos])) {
    $form_data_datos = $_SESSION[$form_data_key_datos];
    unset($_SESSION[$form_data_key_datos]); // Limpiar de la sesión después de usarla
} else {
    $form_data_datos = [];
}

// Variables para repoblar el formulario de datos personales:
// Priorizar datos de sesión (si hubo error previo), sino usar datos actuales de la BD.
$nombres_form = !empty($form_data_datos['nombres']) ? sanear($form_data_datos['nombres']) : $nombres;
$email_form = !empty($form_data_datos['email']) ? sanear($form_data_datos['email']) : $email;

// No es necesario repoblar los formularios de imagen o contraseña de esta manera,
// ya que esos campos se limpian por seguridad o por su naturaleza (el campo de archivo no se puede repoblar).
?>