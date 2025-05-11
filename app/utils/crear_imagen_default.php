<?php
// Script alternativo que no requiere la extensión GD

$directorio = $_SERVER['DOCUMENT_ROOT'] . '/sistemadeventas/public/images/perfiles/';
$destino = $directorio . 'user_default.png';

// Verificar si existe el directorio, si no, crearlo
if (!file_exists($directorio)) {
    mkdir($directorio, 0755, true);
    echo "Directorio creado en: $directorio<br>";
}

echo "Para completar la configuración:<br>";
echo "1. Descarga cualquier imagen de perfil por defecto<br>";
echo "2. Renómbrala a 'user_default.png'<br>";
echo "3. Colócala manualmente en: $directorio<br>";
echo "<br>O si tienes una imagen, puedes usar el formulario a continuación para subirla:<br>";
?>

<!-- Formulario para subir la imagen directamente -->
<form method="post" enctype="multipart/form-data">
    <input type="file" name="imagen" accept="image/jpeg,image/png,image/gif" required>
    <button type="submit">Subir imagen por defecto</button>
</form>

<?php
// Procesar la carga de la imagen
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['imagen'])) {
    if ($_FILES['imagen']['error'] === 0) {
        $tmp_name = $_FILES['imagen']['tmp_name'];
        if (move_uploaded_file($tmp_name, $destino)) {
            echo "<br>¡Imagen de perfil por defecto subida correctamente a: $destino!";
        } else {
            echo "<br>Error al mover la imagen. Verifica los permisos del directorio.";
        }
    } else {
        echo "<br>Error al subir la imagen: " . $_FILES['imagen']['error'];
    }
}
?>