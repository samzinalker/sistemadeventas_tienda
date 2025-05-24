<?php
// 1. Iniciar sesión
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Incluir configuración y dependencias
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../utils/funciones_globales.php';
require_once __DIR__ . '/../../models/UsuarioModel.php';

// 3. Verificar que el usuario esté logueado
if (!isset($_SESSION['id_usuario'])) {
    setMensaje("Debe iniciar sesión para realizar esta acción.", "error");
    redirigir('/login/');
}
$id_usuario_actualizar = (int)$_SESSION['id_usuario'];

// 4. Verificar que la solicitud sea POST y que se haya enviado un archivo
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setMensaje("Acceso no permitido.", "error");
    redirigir('/perfil/');
}

if (!isset($_FILES['imagen_perfil']) || $_FILES['imagen_perfil']['error'] == UPLOAD_ERR_NO_FILE) {
    setMensaje("No se ha seleccionado ninguna imagen para subir.", "warning"); // Cambiado a warning
    redirigir('/perfil/');
}

// 5. Configuración para la subida de archivos
$directorio_destino_fisico = __DIR__ . '/../../../public/images/perfiles/'; // Ruta física absoluta
$tipos_permitidos = ['image/jpeg', 'image/png', 'image/gif'];
$tamano_maximo_bytes = 2 * 1024 * 1024; // 2MB

// 6. Obtener información del archivo subido
$archivo_subido = $_FILES['imagen_perfil'];
$nombre_original_archivo = $archivo_subido['name'];
$tipo_mime_archivo = $archivo_subido['type'];
$tamano_archivo_bytes = $archivo_subido['size'];
$ruta_temporal_archivo = $archivo_subido['tmp_name'];
$codigo_error_upload = $archivo_subido['error'];

// Verificar errores de subida
if ($codigo_error_upload !== UPLOAD_ERR_OK) {
    // Manejar diferentes errores de subida si es necesario
    setMensaje("Error al subir el archivo. Código: " . $codigo_error_upload, "error");
    redirigir('/perfil/');
}

// 7. Validaciones del archivo
if (!in_array($tipo_mime_archivo, $tipos_permitidos)) {
    setMensaje("Tipo de archivo no permitido. Solo se aceptan imágenes JPG, PNG y GIF.", "error");
    redirigir('/perfil/');
}

if ($tamano_archivo_bytes > $tamano_maximo_bytes) {
    setMensaje("El archivo de imagen es demasiado grande. El tamaño máximo permitido es 2MB.", "error");
    redirigir('/perfil/');
}

// 8. Procesar y guardar el archivo
try {
    $usuarioModel = new UsuarioModel($pdo, $URL);

    // Obtener nombre de la imagen anterior para borrarla después si es necesario
    $datos_usuario_actual = $usuarioModel->getUsuarioById($id_usuario_actualizar);
    $imagen_anterior = $datos_usuario_actual['imagen_perfil'] ?? null;

    // Generar un nombre único para el nuevo archivo
    $extension_archivo = strtolower(pathinfo($nombre_original_archivo, PATHINFO_EXTENSION));
    $nombre_unico_nuevo_archivo = date('YmdHis') . '_' . uniqid() . '.' . $extension_archivo;
    $ruta_completa_destino_fisico = $directorio_destino_fisico . $nombre_unico_nuevo_archivo;

    // Mover el archivo subido al directorio de destino
    if (move_uploaded_file($ruta_temporal_archivo, $ruta_completa_destino_fisico)) {
        // Archivo movido con éxito, ahora actualizar la base de datos
        // $fechaHora viene de config.php
        if ($usuarioModel->actualizarImagenPerfil($id_usuario_actualizar, $nombre_unico_nuevo_archivo, $fechaHora)) {
            // Si la BD se actualizó, eliminar la imagen anterior (si no es la default y existe)
            if ($imagen_anterior && $imagen_anterior !== 'user_default.png' && file_exists($directorio_destino_fisico . $imagen_anterior)) {
                @unlink($directorio_destino_fisico . $imagen_anterior); // Suprimir error si no se puede borrar
            }
            setMensaje("Imagen de perfil actualizada correctamente.", "success");
        } else {
            // No se pudo actualizar la BD, pero el archivo ya se subió. Intentar borrar el archivo subido.
            @unlink($ruta_completa_destino_fisico);
            setMensaje("Error al guardar la referencia de la nueva imagen. No se realizaron cambios.", "error");
        }
    } else {
        // Falló la subida del archivo físico
        setMensaje("Error crítico al mover el archivo de imagen subido.", "error");
    }
} catch (PDOException $e) {
    error_log("Error de BD en actualizar_imagen.php: " . $e->getMessage());
    setMensaje("Error en el sistema al actualizar la imagen. Por favor, intente más tarde.", "error");
} catch (Exception $e) {
    error_log("Error general en actualizar_imagen.php: " . $e->getMessage());
    setMensaje("Ocurrió un error inesperado al actualizar la imagen. Por favor, intente más tarde.", "error");
}

redirigir('/perfil/');
?>