<?php
// Asegúrate de que los errores de PHP no se muestren directamente en producción,
// pero para desarrollo, tenerlos visibles puede ayudar a diagnosticar problemas
// como el que estamos viendo. Sin embargo, un echo directo como el del catch es problemático para AJAX.
// ini_set('display_errors', 1); // Descomenta solo para depuración profunda si es necesario
// error_reporting(E_ALL);    // Descomenta solo para depuración profunda si es necesario

if (!defined('SERVIDOR')) define('SERVIDOR','localhost');
if (!defined('USUARIO')) define('USUARIO','root');
if (!defined('PASSWORD')) define('PASSWORD',''); // Contraseña vacía para root es común en XAMPP/WAMP por defecto
if (!defined('BD')) define('BD','sistemadeventas');

$servidor = "mysql:dbname=".BD.";host=".SERVIDOR;

try {
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // ¡MUY IMPORTANTE!
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Opcional, pero útil
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
    ];
    $pdo = new PDO($servidor, USUARIO, PASSWORD, $options);
} catch(PDOException $e) {
    // NO USAR ECHO AQUÍ para respuestas AJAX.
    // En su lugar, registra el error y termina de una manera que DataTables pueda entender (o no) como un error de servidor.
    // Para un script que debe devolver JSON, lo ideal es que el script que lo incluye maneje este error.
    // Si este config.php se incluye en un script que devuelve JSON, este die() también romperá el JSON.
    // Lo mejor es dejar que la excepción se propague si $pdo no se puede crear,
    // y que el script principal (como controller_buscar_productos_dt.php) la capture.
    error_log("Error de conexión a la BD en config.php: " . $e->getMessage());
    // Si es un script que debe devolver JSON, considera esto:
    // header('Content-Type: application/json');
    // echo json_encode(["error" => "Error de conexión a la base de datos. Revise los logs del servidor."]);
    // exit;
    // O simplemente muere para que el error del servidor sea más genérico:
    die("Error crítico: No se pudo conectar a la base de datos. Por favor, contacte al administrador. Detalles: " . $e->getMessage());
}

// Verifica que la URL base sea correcta para tu proyecto.
// Si tu proyecto está en http://localhost/sistemadeventas1/, entonces debe ser:
// $URL = "http://localhost/sistemadeventas1";
// Si está en http://localhost/sistemadeventas/, entonces está bien:
$URL = "http://localhost/sistemadeventas";

date_default_timezone_set('America/Guayaquil');
$fechaHora = date('Y-m-d H:i:s');