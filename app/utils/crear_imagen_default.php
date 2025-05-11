<?php
// Este script se usa para crear una imagen de perfil por defecto si no existe

$directorio = $_SERVER['DOCUMENT_ROOT'] . '/sistemadeventas/public/images/perfiles/';
$destino = $directorio . 'user_default.png';

// Verificar si existe el directorio, si no, crearlo
if (!file_exists($directorio)) {
    mkdir($directorio, 0755, true);
    echo "Directorio creado en: $directorio<br>";
}

// Si no existe la imagen por defecto, crearla
if (!file_exists($destino)) {
    // Crear una imagen simple
    $img = imagecreate(200, 200);
    $fondo = imagecolorallocate($img, 52, 152, 219); // Azul
    $texto = imagecolorallocate($img, 255, 255, 255); // Blanco
    imagestring($img, 5, 40, 90, "Usuario Default", $texto);
    imagepng($img, $destino);
    imagedestroy($img);
    echo "Imagen de perfil por defecto creada en: $destino";
} else {
    echo "La imagen de perfil por defecto ya existe en: $destino";
}
?>