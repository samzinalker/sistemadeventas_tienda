<?php
// 1. INCLUSIÓN DE ARCHIVOS Y CONFIGURACIÓN INICIAL
// -------------------------------------------------
// Ajusta las rutas según la ubicación de este controlador.
// __DIR__ se refiere al directorio actual (app/controllers/ventas/)
require_once __DIR__ . '/../../../app/config.php'; // Para $pdo y $URL
require_once __DIR__ . '/../../../app/models/VentasModel.php'; // Modelo de Ventas

session_start(); // Necesario para obtener el ID del usuario logueado

// Establecer el tipo de contenido de la respuesta a JSON.
// Todas las salidas de este script deben ser JSON para que el AJAX del frontend las procese correctamente.
header('Content-Type: application/json');

// 2. VERIFICACIÓN DE AUTENTICACIÓN Y MÉTODO DE SOLICITUD
// -----------------------------------------------------
if (!isset($_SESSION['id_usuario'])) { // <--- CAMBIO AQUÍ
    echo json_encode([
        'status' => 'error',
        'message' => 'Acceso no autorizado. Por favor, inicie sesión.'
    ]);
    exit; // Termina la ejecución si el usuario no está logueado.
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'status' => 'error',
        'message' => 'Método de solicitud no permitido.'
    ]);
    exit; // Solo se aceptan solicitudes POST.
}

// 3. RECOLECCIÓN Y VALIDACIÓN BÁSICA DE DATOS DE CABECERA DE LA VENTA
// -----------------------------------------------------------------
$id_usuario_logueado = $_SESSION['id_usuario']; // <--- CAMBIO AQUÍ

// Datos de la cabecera de la venta (provenientes del formulario)
// Es buena práctica validar cada uno de estos campos.
$id_cliente = filter_input(INPUT_POST, 'id_cliente_venta', FILTER_VALIDATE_INT);
$nro_venta_secuencial = filter_input(INPUT_POST, 'nro_venta_secuencial', FILTER_VALIDATE_INT);
$codigo_venta_referencia = filter_input(INPUT_POST, 'nro_venta_referencia', FILTER_SANITIZE_STRING);
$fecha_venta = filter_input(INPUT_POST, 'fecha_venta', FILTER_SANITIZE_STRING); // Se podría validar formato de fecha también
$tipo_comprobante = filter_input(INPUT_POST, 'tipo_comprobante_venta', FILTER_SANITIZE_STRING);
$nro_comprobante_fisico = filter_input(INPUT_POST, 'nro_comprobante_fisico_venta', FILTER_SANITIZE_STRING);
$observaciones = filter_input(INPUT_POST, 'observaciones_venta', FILTER_SANITIZE_STRING);

// Totales calculados (se deben validar o recalcular en el backend por seguridad)
// Usar FILTER_VALIDATE_FLOAT con FILTER_FLAG_ALLOW_FRACTION para decimales.
$subtotal_general_form = filter_input(INPUT_POST, 'subtotal_general_venta_calculado', FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
$monto_iva_general_form = filter_input(INPUT_POST, 'monto_iva_general_venta_calculado', FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
$descuento_general_form = filter_input(INPUT_POST, 'descuento_general_venta', FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
$total_general_form = filter_input(INPUT_POST, 'total_general_venta_calculado', FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_FRACTION);


// Validaciones básicas de campos obligatorios
if (!$id_cliente || !$nro_venta_secuencial || !$codigo_venta_referencia || !$fecha_venta) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Faltan datos obligatorios de la venta (cliente, número de venta o fecha).'
    ]);
    exit;
}
// Validar que los totales no sean false (si FILTER_VALIDATE_FLOAT falla)
if ($subtotal_general_form === false || $monto_iva_general_form === false || $descuento_general_form === false || $total_general_form === false) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Los montos totales de la venta no son válidos.'
    ]);
    exit;
}
if ($descuento_general_form < 0) {
     echo json_encode([
        'status' => 'error',
        'message' => 'El descuento general no puede ser negativo.'
    ]);
    exit;
}


// 4. RECOLECCIÓN Y VALIDACIÓN DE ÍTEMS DE LA VENTA
// ------------------------------------------------
$items_venta_form = isset($_POST['items']) && is_array($_POST['items']) ? $_POST['items'] : [];

if (empty($items_venta_form)) {
    echo json_encode([
        'status' => 'error',
        'message' => 'No se han añadido productos a la venta.'
    ]);
    exit;
}

$items_procesados = [];
foreach ($items_venta_form as $item_data) {
    $id_producto = filter_var($item_data['id_producto'], FILTER_VALIDATE_INT);
    $cantidad = filter_var($item_data['cantidad'], FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $precio_venta_unitario = filter_var($item_data['precio_venta_unitario'], FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    $porcentaje_iva_item = filter_var($item_data['porcentaje_iva_item'], FILTER_VALIDATE_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

    if (!$id_producto || $cantidad === false || $precio_venta_unitario === false || $porcentaje_iva_item === false) {
        echo json_encode([
            'status' => 'error',
            'message' => 'Datos inválidos en uno de los productos de la venta.'
        ]);
        exit;
    }
    if ($cantidad <= 0 || $precio_venta_unitario < 0 || $porcentaje_iva_item < 0) {
         echo json_encode([
            'status' => 'error',
            'message' => 'Cantidad, precio o IVA no pueden ser negativos o cero (cantidad).'
        ]);
        exit;
    }

    $items_procesados[] = [
        'id_producto' => $id_producto,
        'cantidad' => $cantidad,
        'precio_venta_unitario' => $precio_venta_unitario,
        'porcentaje_iva_item' => $porcentaje_iva_item
        // 'descuento_item' => 0 // Podrías añadirlo si lo implementas en la interfaz
    ];
}


// 5. PREPARACIÓN DE DATOS PARA EL MODELO Y LÓGICA DE NEGOCIO
// --------------------------------------------------------
// Es recomendable recalcular los totales en el backend para asegurar la integridad,
// aunque el frontend ya los envíe.
$subtotal_general_recalculado = 0;
$monto_iva_general_recalculado = 0;

foreach ($items_procesados as $item) {
    $subtotal_item = $item['cantidad'] * $item['precio_venta_unitario'];
    // $descuento_por_item = $item['descuento_item'] ?? 0; // Si se implementa descuento por item
    // $subtotal_item_con_descuento = $subtotal_item - $descuento_por_item;
    $subtotal_general_recalculado += $subtotal_item; // O $subtotal_item_con_descuento
    $monto_iva_general_recalculado += $subtotal_item * ($item['porcentaje_iva_item'] / 100);
}

$total_general_recalculado = ($subtotal_general_recalculado + $monto_iva_general_recalculado) - $descuento_general_form;

// Comparar totales (opcional, como una capa extra de seguridad)
// Podrías tener una pequeña tolerancia por errores de redondeo si las operaciones son complejas.
if (abs($total_general_recalculado - $total_general_form) > 0.01) { // Tolerancia de 1 centavo
    // Podrías loggear esta discrepancia. Por ahora, usaremos los valores recalculados.
    // O podrías devolver un error si la discrepancia es grande.
}


$datosVentaParaModelo = [
    'id_cliente' => $id_cliente,
    'nro_venta_secuencial' => $nro_venta_secuencial,
    'codigo_venta_referencia' => $codigo_venta_referencia,
    'fecha_venta' => $fecha_venta,
    'tipo_comprobante' => $tipo_comprobante ?: null, // Permite nulo si no se selecciona
    'nro_comprobante_fisico' => $nro_comprobante_fisico ?: null, // Permite nulo
    'subtotal_general' => $subtotal_general_recalculado, // Usar el recalculado
    'monto_iva_general' => $monto_iva_general_recalculado, // Usar el recalculado
    'descuento_general' => $descuento_general_form, // El descuento es directo del form
    'total_general' => $total_general_recalculado, // Usar el recalculado
    'observaciones' => $observaciones ?: null
];


// 6. INTERACCIÓN CON EL MODELO PARA REGISTRAR LA VENTA
// ----------------------------------------------------
try {
    $ventasModel = new VentasModel($pdo, $id_usuario_logueado);

    // Antes de registrar, verificar stock de cada producto ( crucial si no se hizo estrictamente en frontend)
    foreach ($items_procesados as $item_a_vender) {
        $stock_actual_producto = $ventasModel->verificarStockProducto($item_a_vender['id_producto']);
        if ($stock_actual_producto === null) {
            throw new Exception("El producto con ID {$item_a_vender['id_producto']} no fue encontrado o no pertenece al usuario.");
        }
        if ($stock_actual_producto < $item_a_vender['cantidad']) {
            // Obtener nombre del producto para el mensaje
            // (necesitarías un método en AlmacenModel o ProductoModel para esto, o añadirlo a verificarStockProducto)
            throw new Exception("Stock insuficiente para el producto ID {$item_a_vender['id_producto']}. Disponible: {$stock_actual_producto}, Solicitado: {$item_a_vender['cantidad']}.");
        }
    }

    // Si todas las validaciones de stock pasan, proceder a registrar.
    $id_venta_nueva = $ventasModel->registrarVentaConDetalles($datosVentaParaModelo, $items_procesados);

    // Si todo va bien, se devuelve el ID de la nueva venta.
    echo json_encode([
        'status' => 'success',
        'message' => '¡Venta registrada exitosamente!',
        'id_venta' => $id_venta_nueva
    ]);

} catch (PDOException $e) {
    // Error específico de la base de datos (PDO)
    // Loggear $e->getMessage() y $e->getCode() para depuración interna.
    error_log("Error PDO en controller_create_venta: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => 'Error de base de datos al registrar la venta. Por favor, intente más tarde.'
        // 'debug_message' => $e->getMessage() // Solo para desarrollo, no en producción
    ]);
} catch (Exception $e) {
    // Otros errores (lanzados desde el modelo o validaciones personalizadas)
    error_log("Error en controller_create_venta: " . $e->getMessage());
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage() // Muestra el mensaje de error específico de la excepción.
    ]);
}

?>