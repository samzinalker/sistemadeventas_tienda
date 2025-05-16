<?php
include('../../config.php');
if (session_status() == PHP_SESSION_NONE) session_start();

// Verificar autenticación
if (!isset($_SESSION['id_usuario'])) {
    echo json_encode(['status' => 'error', 'message' => 'No autenticado']);
    exit();
}

// Obtener el porcentaje de IVA desde POST
$porcentaje_iva = isset($_POST['porcentaje_iva']) ? floatval($_POST['porcentaje_iva']) : 0;

// Validar que no sea 0 o negativo
if ($porcentaje_iva <= 0) {
    echo json_encode(['status' => 'error', 'message' => 'El porcentaje de IVA debe ser mayor a 0']);
    exit();
}

// Guardar en la sesión
$_SESSION['ultimo_porcentaje_iva'] = $porcentaje_iva;

echo json_encode(['status' => 'success', 'porcentaje_iva' => $porcentaje_iva]);