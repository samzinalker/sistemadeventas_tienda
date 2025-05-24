<?php
// Este script debe ejecutarse manualmente una vez para crear el directorio de imágenes de perfil
$directorio = $_SERVER['DOCUMENT_ROOT'] . '/sistemadeventas/public/images/perfiles/';
if (!file_exists($directorio)) {
    mkdir($directorio, 0755, true);
    echo "Directorio de perfiles creado correctamente en: $directorio";
} else {
    echo "El directorio ya existe en: $directorio";
}
?>