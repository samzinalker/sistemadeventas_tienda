<?php
// Este script es incluido por compras/index.php, donde config, funciones_globales y sesion ya están cargados.
// Es importante que CompraModel esté disponible.
require_once __DIR__ . '/../../models/ComprasModel.php'; // Asegúrate que la ruta es correcta

// La sesión ya debería estar iniciada por layout/sesion.php, que es incluido por compras/index.php
// if (session_status() === PHP_SESSION_NONE) {
//     session_start(); 
// }

// Verificar que el id_usuario esté en la sesión. layout/sesion.php ya maneja la redirección si no.
if (!isset($_SESSION['id_usuario'])) {
    // Esta es una doble verificación. Si layout/sesion.php falla, esto podría atraparlo.
    // Normalmente, no se llegaría aquí si la sesión no está activa.
    if (isset($URL)) { // $URL se define en config.php
        setMensaje("Debe iniciar sesión para ver sus compras.", "error");
        redirigir('/login/'); 
    } else {
        // Fallback si $URL no está definida (situación de error grave)
        die("Error crítico: Sesión no iniciada y URL de configuración no disponible.");
    }
    exit();
}

$id_usuario_logueado = (int)$_SESSION['id_usuario'];

// Crear una instancia del modelo
$compraModel = new CompraModel($pdo); // $pdo se define en config.php

// Obtener las compras del usuario logueado
$compras_datos = $compraModel->getComprasPorUsuarioId($id_usuario_logueado);

// La variable $compras_datos ahora está disponible para ser usada en la vista (compras/index.php)
// No es necesario hacer echo ni json_encode aquí, ya que este script se incluye en la vista.
?>