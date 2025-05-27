<?php
// 1. Iniciar sesión (si no está activa)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Incluir configuración y dependencias
require_once __DIR__ . '/../../config.php'; // $pdo, $URL, $fechaHora
require_once __DIR__ . '/../../utils/Validator.php';
require_once __DIR__ . '/../../utils/funciones_globales.php'; // setMensaje, redirigir, sanear
require_once __DIR__ . '/../../models/UsuarioModel.php';

// 3. Verificar que el usuario esté logueado (AISLAMIENTO DEL USUARIO)
if (!isset($_SESSION['id_usuario'])) {
    setMensaje("Debe iniciar sesión para realizar esta acción.", "error");
    redirigir($URL . '/login/'); // $URL está definido en config.php
}
$id_usuario_actualizar = (int)$_SESSION['id_usuario']; // Clave para todas las operaciones de BD

// 4. Verificar que la solicitud sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setMensaje("Acceso no permitido. El método de solicitud no es válido.", "error");
    redirigir('/perfil/');
}

// 5. Obtener y sanear (inicialmente) datos del formulario
// Usamos trim() para quitar espacios al inicio/final. El saneamiento final se hará antes de la BD si es necesario.
$nombres_nuevos = trim($_POST['nombres'] ?? '');
$email_nuevo = trim($_POST['email'] ?? ''); // Este campo se usa como 'usuario' y 'email'

// 6. Guardar datos en sesión para repoblar el formulario en caso de error y redirección
// La clave de sesión es única por usuario para evitar conflictos.
$form_data_key = 'form_data_perfil_datos_' . $id_usuario_actualizar;
$_SESSION[$form_data_key] = ['nombres' => $nombres_nuevos, 'email' => $email_nuevo];

// 7. Validaciones de los datos recibidos
if (empty($nombres_nuevos)) {
    setMensaje("El campo 'Nombre Completo' no puede estar vacío.", "warning");
    redirigir('/perfil/');
}
if (strlen($nombres_nuevos) > 255) { // Ejemplo de límite, ajustar según BD
    setMensaje("El nombre completo es demasiado largo (máximo 255 caracteres).", "warning");
    redirigir('/perfil/');
}

if (empty($email_nuevo)) {
    setMensaje("El campo 'Usuario / Email de Contacto' no puede estar vacío.", "warning");
    redirigir('/perfil/');
}
if (!Validator::isValidEmail($email_nuevo)) {
    setMensaje("El formato del 'Usuario / Email de Contacto' no es válido.", "error");
    redirigir('/perfil/');
}
if (strlen($email_nuevo) > 255) { // Ejemplo de límite, ajustar según BD
    setMensaje("El email/usuario es demasiado largo (máximo 255 caracteres).", "warning");
    redirigir('/perfil/');
}


// 8. Lógica de actualización usando el Modelo
try {
    // CORRECCIÓN AQUÍ: Asegurarse de pasar todos los argumentos esperados por el constructor de UsuarioModel.
    $usuarioModel = new UsuarioModel($pdo, $URL); // <--- LÍNEA CORREGIDA (línea 62 en tu error)

    // Verificar si el nuevo email/usuario ya está en uso por OTRO usuario.
    // El método emailExiste debe aceptar un segundo parámetro opcional para excluir el ID del usuario actual.
    if ($usuarioModel->emailExiste($email_nuevo, $id_usuario_actualizar)) {
        setMensaje("El email/usuario '" . sanear($email_nuevo) . "' ya está registrado por otra cuenta. Por favor, elija uno diferente.", "error");
        redirigir('/perfil/');
    }

    // Obtener el rol actual del usuario. El rol no se modifica desde la página de perfil del usuario.
    // Esta consulta también sirve para confirmar que el usuario aún existe, aunque es redundante si la sesión es válida.
    $usuario_actual_data = $usuarioModel->getUsuarioById($id_usuario_actualizar);
    if (!$usuario_actual_data) {
        // Esto sería muy raro si la sesión es válida y el ID es correcto.
        setMensaje("Error crítico: No se pudieron obtener los datos del usuario actual para la actualización.", "error");
        // Invalidar sesión por seguridad y redirigir al login
        session_destroy();
        redirigir($URL . '/login/');
    }
    $id_rol_actual = (int)$usuario_actual_data['id_rol'];

    // $fechaHora es una variable global definida en config.php con la fecha y hora actual del servidor.
    // Preparamos los datos finales para la actualización.
    $datos_para_actualizar = [
        'nombres' => $nombres_nuevos, // Ya se hizo trim
        'email' => $email_nuevo,       // Ya se hizo trim y validación de formato
        'id_rol' => $id_rol_actual,  // El rol se mantiene, no se actualiza desde aquí
        'fyh_actualizacion' => $fechaHora // Fecha y hora de la modificación
    ];
    
    // Intentar actualizar en la base de datos
    $actualizado = $usuarioModel->actualizarUsuario($id_usuario_actualizar, $datos_para_actualizar);

    if ($actualizado) {
        // Éxito: Actualizar los datos en la sesión para que se reflejen inmediatamente en la UI.
        $_SESSION['nombres'] = $nombres_nuevos; // Usado en el layout/header, por ejemplo
        $_SESSION['sesion_email'] = $email_nuevo; // Usado por layout/sesion.php y potencialmente en otros lugares
        // $_SESSION['usuario'] podría ser otro nombre para el email/usuario en sesión. Si es así, actualizar también:
        // $_SESSION['usuario'] = $email_nuevo;


        unset($_SESSION[$form_data_key]); // Limpiar datos del formulario de la sesión en caso de éxito.
        setMensaje("Sus datos personales han sido actualizados correctamente.", "success");
    } else {
        // Falla en la actualización de la BD (sin excepción PDO), podría ser que no hubo cambios o un error lógico.
        setMensaje("No se pudieron actualizar sus datos personales en este momento. Es posible que no haya realizado cambios o haya ocurrido un error. Inténtelo de nuevo.", "error");
    }

} catch (PDOException $e) {
    // Error específico de la base de datos.
    error_log("Error de BD en actualizar_datos.php para usuario ID {$id_usuario_actualizar}: " . $e->getMessage());
    setMensaje("Error del sistema al intentar actualizar sus datos. Por favor, contacte al soporte si el problema persiste.", "error");
    // No limpiar $_SESSION[$form_data_key] para que el usuario pueda reintentar sin perder los datos.
} catch (Exception $e) {
    // Otro tipo de error inesperado.
    error_log("Error general en actualizar_datos.php para usuario ID {$id_usuario_actualizar}: " . $e->getMessage());
    setMensaje("Ocurrió un error inesperado al procesar su solicitud. Por favor, intente más tarde.", "error");
}

// Siempre redirigir de vuelta a la página de perfil.
redirigir('/perfil/');
?>