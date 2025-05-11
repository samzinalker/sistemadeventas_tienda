<?php
include('../../config.php');
session_start();

// Verificar si hay una sesión activa
if (!isset($_SESSION['id_usuario'])) {
    $_SESSION['mensaje'] = "Debes iniciar sesión para realizar esta acción";
    $_SESSION['icono'] = "error";
    header('Location: '.$URL.'/login');
    exit();
}

// Obtener el ID de usuario de la sesión
$id_usuario = $_SESSION['id_usuario'];

// Verificar que se haya enviado un archivo
if (!isset($_FILES['imagen']) || $_FILES['imagen']['error'] == UPLOAD_ERR_NO_FILE) {
    $_SESSION['mensaje'] = "No se ha seleccionado ninguna imagen";
    $_SESSION['icono'] = "error";
    header('Location: '.$URL.'/perfil');
    exit();
}

// Configuración para la subida de archivos
$directorio_destino = $_SERVER['DOCUMENT_ROOT'] . '/sistemadeventas/public/images/perfiles/';
$tipos_permitidos = ['image/jpeg', 'image/png', 'image/gif'];
$tamano_maximo = 2 * 1024 * 1024; // 2MB

// Obtener información del archivo
$archivo = $_FILES['imagen'];
$nombre_archivo = $archivo['name'];
$tipo_archivo = $archivo['type'];
$tamano_archivo = $archivo['size'];
$archivo_temporal = $archivo['tmp_name'];

// Verificar el tipo de archivo
if (!in_array($tipo_archivo, $tipos_permitidos)) {
    $_SESSION['mensaje'] = "Tipo de archivo no permitido. Solo se permiten imágenes JPG, PNG y GIF";
    $_SESSION['icono'] = "error";
    header('Location: '.$URL.'/perfil');
    exit();
}

// Verificar el tamaño del archivo
if ($tamano_archivo > $tamano_maximo) {
    $_SESSION['mensaje'] = "El archivo es demasiado grande. El tamaño máximo es 2MB";
    $_SESSION['icono'] = "error";
    header('Location: '.$URL.'/perfil');
    exit();
}

try {
    // Generar un nombre único para el archivo
    $extension = pathinfo($nombre_archivo, PATHINFO_EXTENSION);
    $nombre_unico = date('Y-m-d-H-i-s') . '_' . uniqid() . '.' . $extension;
    $ruta_completa = $directorio_destino . $nombre_unico;
    
    // Mover el archivo subido al directorio de destino
    if (move_uploaded_file($archivo_temporal, $ruta_completa)) {
        // Actualizar la información del usuario en la base de datos
        $sql = "UPDATE tb_usuarios SET 
                imagen_perfil = :imagen_perfil,
                fyh_actualizacion = :fyh_actualizacion
                WHERE id_usuario = :id_usuario";
                
        $query = $pdo->prepare($sql);
        $query->bindParam(':imagen_perfil', $nombre_unico);
        $query->bindParam(':fyh_actualizacion', $fechaHora);
        $query->bindParam(':id_usuario', $id_usuario);
        $query->execute();
        
        $_SESSION['mensaje'] = "Imagen de perfil actualizada correctamente";
        $_SESSION['icono'] = "success";
    } else {
        throw new Exception("Error al subir la imagen");
    }
} catch (Exception $e) {
    $_SESSION['mensaje'] = "Error al actualizar la imagen de perfil: " . $e->getMessage();
    $_SESSION['icono'] = "error";
}

header('Location: '.$URL.'/perfil');
exit();
?>