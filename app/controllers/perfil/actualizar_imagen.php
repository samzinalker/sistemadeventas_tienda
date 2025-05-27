<?php
// 1. Iniciar sesión (si no está activa)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Incluir configuración y dependencias
require_once __DIR__ . '/../../config.php'; // $pdo, $URL, $fechaHora
require_once __DIR__ . '/../../utils/funciones_globales.php'; // setMensaje, redirigir, sanear
require_once __DIR__ . '/../../models/UsuarioModel.php';
// No se necesita Validator.php aquí a menos que se añadan validaciones que lo requieran.

// 3. Verificar que el usuario esté logueado (AISLAMIENTO DEL USUARIO)
if (!isset($_SESSION['id_usuario'])) {
    setMensaje("Debe iniciar sesión para realizar esta acción.", "error");
    redirigir('/login/'); // ✅ CORRECCIÓN: Solo ruta relativa
}
$id_usuario_actualizar = (int)$_SESSION['id_usuario']; // Clave para todas las operaciones

// 4. Verificar que la solicitud sea POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setMensaje("Acceso no permitido. El método de solicitud no es válido.", "error");
    redirigir('/perfil/'); // ✅ CORRECCIÓN: Solo ruta relativa
}

// 5. Verificar que se haya enviado un archivo y no haya errores iniciales de subida
if (!isset($_FILES['imagen_perfil']) || empty($_FILES['imagen_perfil']['name'])) {
    setMensaje("No se ha seleccionado ninguna imagen para subir. Por favor, elija un archivo.", "warning");
    redirigir('/perfil/'); // ✅ CORRECCIÓN: Solo ruta relativa
}

// Verificar errores de subida del archivo (ej. UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE)
if ($_FILES['imagen_perfil']['error'] !== UPLOAD_ERR_OK) {
    $upload_error_messages = [
        UPLOAD_ERR_INI_SIZE   => "El archivo excede el tamaño máximo permitido por la configuración del servidor (upload_max_filesize).",
        UPLOAD_ERR_FORM_SIZE  => "El archivo excede el tamaño máximo permitido especificado en el formulario HTML.",
        UPLOAD_ERR_PARTIAL    => "El archivo fue solo parcialmente subido.",
        UPLOAD_ERR_NO_FILE    => "No se subió ningún archivo. Por favor, seleccione uno.", // Ya cubierto arriba, pero por completitud.
        UPLOAD_ERR_NO_TMP_DIR => "Falta una carpeta temporal en el servidor para almacenar el archivo subido.",
        UPLOAD_ERR_CANT_WRITE => "Error al escribir el archivo en el disco en el servidor.",
        UPLOAD_ERR_EXTENSION  => "Una extensión de PHP detuvo la subida del archivo.",
    ];
    $error_code = $_FILES['imagen_perfil']['error'];
    $mensaje_error = $upload_error_messages[$error_code] ?? "Error desconocido al subir el archivo (Código: {$error_code}).";
    setMensaje($mensaje_error, "error");
    redirigir('/perfil/'); // ✅ CORRECCIÓN: Solo ruta relativa
}

// 6. Configuración para la subida de archivos
$nombre_imagen_default = 'user_default.PNG'; // Definido para no borrar la imagen por defecto
$directorio_destino_fisico = __DIR__ . '/../../../public/images/perfiles/'; // Ruta física absoluta al directorio de imágenes de perfil
$tipos_mime_permitidos = ['image/jpeg', 'image/png', 'image/gif']; // Tipos MIME permitidos
$tamano_maximo_bytes = 2 * 1024 * 1024; // 2MB (ajustar según necesidad)

// 7. Obtener información del archivo subido
$archivo_subido = $_FILES['imagen_perfil'];
$nombre_original_archivo = $archivo_subido['name'];
$tipo_mime_archivo = mime_content_type($archivo_subido['tmp_name']); // Más fiable que ['type']
$tamano_archivo_bytes = $archivo_subido['size'];
$ruta_temporal_archivo = $archivo_subido['tmp_name'];

// 8. Validaciones del archivo
// Validar tipo MIME
if (!in_array($tipo_mime_archivo, $tipos_mime_permitidos)) {
    setMensaje("Tipo de archivo no permitido. Solo se aceptan imágenes en formato JPG, PNG o GIF.", "error");
    redirigir('/perfil/'); // ✅ CORRECCIÓN: Solo ruta relativa
}

// Validar tamaño del archivo
if ($tamano_archivo_bytes > $tamano_maximo_bytes) {
    setMensaje("El archivo de imagen es demasiado grande. El tamaño máximo permitido es " . ($tamano_maximo_bytes / 1024 / 1024) . "MB.", "error");
    redirigir('/perfil/'); // ✅ CORRECCIÓN: Solo ruta relativa
}

// Validar si el archivo es una imagen válida (intento básico de evitar archivos maliciosos renombrados)
$check_imagen = getimagesize($ruta_temporal_archivo);
if ($check_imagen === false) {
    setMensaje("El archivo seleccionado no parece ser una imagen válida o está corrupto.", "error");
    redirigir('/perfil/'); // ✅ CORRECCIÓN: Solo ruta relativa
}

// 9. Procesar y guardar el archivo
try {
    // CORRECCIÓN: Pasar ambos parámetros requeridos al constructor
    $usuarioModel = new UsuarioModel($pdo, $URL);

    // Obtener el nombre de la imagen de perfil anterior del usuario para borrarla después si es necesario.
    $datos_usuario_actual = $usuarioModel->getUsuarioById($id_usuario_actualizar);
    if (!$datos_usuario_actual) {
        // Muy improbable si la sesión es válida.
        setMensaje("Error crítico: No se pudieron obtener los datos del usuario actual.", "error");
        session_destroy(); // Por seguridad
        redirigir('/login/'); // ✅ CORRECCIÓN: Solo ruta relativa
    }
    $imagen_anterior_db = $datos_usuario_actual['imagen_perfil'] ?? null;

    // Generar un nombre único para el nuevo archivo para evitar colisiones y problemas con caracteres especiales.
    // Usar el ID de usuario y timestamp puede ser una buena estrategia para nombres únicos.
    $extension_archivo = strtolower(pathinfo($nombre_original_archivo, PATHINFO_EXTENSION));
    if (!in_array($extension_archivo, ['jpg', 'jpeg', 'png', 'gif'])) { // Doble chequeo de extensión
        setMensaje("Extensión de archivo no válida. Solo se permiten JPG, PNG, GIF.", "error");
        redirigir('/perfil/'); // ✅ CORRECCIÓN: Solo ruta relativa
    }
    $nombre_unico_nuevo_archivo = "user_{$id_usuario_actualizar}_" . time() . '.' . $extension_archivo;
    $ruta_completa_destino_fisico = $directorio_destino_fisico . $nombre_unico_nuevo_archivo;

    // Asegurarse que el directorio de destino exista y tenga permisos de escritura.
    if (!is_dir($directorio_destino_fisico)) {
        if (!mkdir($directorio_destino_fisico, 0755, true)) { // Intentar crear recursivamente
            error_log("Error crítico: No se pudo crear el directorio de destino para imágenes de perfil: {$directorio_destino_fisico}");
            setMensaje("Error del sistema: No se puede guardar la imagen debido a un problema con el directorio de destino.", "error");
            redirigir('/perfil/'); // ✅ CORRECCIÓN: Solo ruta relativa
        }
    }
    if (!is_writable($directorio_destino_fisico)) {
        error_log("Error crítico: El directorio de destino para imágenes de perfil no tiene permisos de escritura: {$directorio_destino_fisico}");
        setMensaje("Error del sistema: No se puede guardar la imagen debido a permisos incorrectos en el servidor.", "error");
        redirigir('/perfil/'); // ✅ CORRECCIÓN: Solo ruta relativa
    }
    
    // Mover el archivo subido desde la ubicación temporal al directorio de destino final.
    if (move_uploaded_file($ruta_temporal_archivo, $ruta_completa_destino_fisico)) {
        // Archivo físico movido con éxito. Ahora, actualizar la referencia en la base de datos.
        // $fechaHora viene de config.php y representa la fecha y hora actual.
        if ($usuarioModel->actualizarImagenPerfil($id_usuario_actualizar, $nombre_unico_nuevo_archivo, $fechaHora)) {
            // Base de datos actualizada correctamente.
            // Ahora, eliminar la imagen anterior si existía, no era la por defecto y es diferente de la nueva (aunque el nombre único debería garantizar esto último).
            if ($imagen_anterior_db && $imagen_anterior_db !== $nombre_imagen_default && $imagen_anterior_db !== $nombre_unico_nuevo_archivo) {
                $ruta_fisica_imagen_anterior = $directorio_destino_fisico . $imagen_anterior_db;
                if (file_exists($ruta_fisica_imagen_anterior)) {
                    @unlink($ruta_fisica_imagen_anterior); // Usar @ para suprimir errores si el archivo no se puede borrar por alguna razón.
                }
            }
            // Actualizar la imagen en la sesión si la almacenas allí (no parece ser el caso según tu estructura)
            // $_SESSION['imagen_perfil_url'] = $URL . '/public/images/perfiles/' . $nombre_unico_nuevo_archivo;
            setMensaje("Su imagen de perfil ha sido actualizada correctamente.", "success");
        } else {
            // Error al actualizar la base de datos, pero el archivo nuevo ya se subió.
            // Se debe intentar eliminar el archivo recién subido para evitar archivos huérfanos.
            @unlink($ruta_completa_destino_fisico);
            setMensaje("Error al guardar la referencia de la nueva imagen en la base de datos. No se realizaron cambios.", "error");
        }
    } else {
        // Falló la subida del archivo físico (move_uploaded_file).
        // Esto puede deberse a permisos, ruta incorrecta, etc.
        $php_errormsg = error_get_last()['message'] ?? 'Error desconocido';
        error_log("Error en move_uploaded_file en actualizar_imagen.php para usuario ID {$id_usuario_actualizar}: {$php_errormsg} (tmp: {$ruta_temporal_archivo}, dest: {$ruta_completa_destino_fisico})");
        setMensaje("Error crítico al mover el archivo de imagen subido al servidor. Por favor, contacte al administrador.", "error");
    }
} catch (PDOException $e) {
    error_log("Error de BD en actualizar_imagen.php para usuario ID {$id_usuario_actualizar}: " . $e->getMessage());
    setMensaje("Error del sistema al actualizar la imagen de perfil. Por favor, contacte al soporte si el problema persiste.", "error");
    // Si el archivo ya se subió y hubo error de BD, considerar eliminarlo si es posible (más complejo aquí).
} catch (Exception $e) {
    error_log("Error general en actualizar_imagen.php para usuario ID {$id_usuario_actualizar}: " . $e->getMessage());
    setMensaje("Ocurrió un error inesperado al actualizar su imagen de perfil. Por favor, intente más tarde.", "error");
}

// Siempre redirigir de vuelta a la página de perfil.
redirigir('/perfil/'); // ✅ CORRECCIÓN: Solo ruta relativa
?>