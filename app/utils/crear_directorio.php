<?php
// Este script debe ejecutarse manualmente una vez para crear el directorio de imágenes de perfil

// Obtener la ruta base del proyecto subiendo dos niveles desde app/utils/
$baseDir = dirname(dirname(__DIR__)); // Sube dos niveles: desde app/utils/ hasta la raíz
$directorio = $baseDir . '/public/images/perfiles/';

if (!file_exists($directorio)) {
    mkdir($directorio, 0755, true);
    echo "Directorio de perfiles creado correctamente en: $directorio";
} else {
    echo "El directorio ya existe en: $directorio";
}
?>