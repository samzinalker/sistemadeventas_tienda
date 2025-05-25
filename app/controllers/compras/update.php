<?php
require_once '../../config.php'; // $URL, $pdo, $fechaHora
require_once '../../utils/funciones_globales.php'; // Para setMensaje, redirigir, sanear
require_once '../../models/ComprasModel.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// AISLAMIENTO DE USUARIO: Verificación estricta de sesión
if (!isset($_SESSION['id_usuario'])) {
    setMensaje("Debe iniciar sesión para actualizar una compra.", "error");
    redirigir("/login");
    exit();
}
$id_usuario_sesion = (int)$_SESSION['id_usuario'];

// Verificar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    setMensaje("Método de solicitud no permitido.", "error");
    redirigir("/compras/");
    exit();
}

// --- 1. Recoger y validar ID de compra ---
$id_compra = filter_input(INPUT_POST, 'id_compra', FILTER_VALIDATE_INT);
if (!$id_compra) {
    setMensaje("ID de compra no válido o no proporcionado.", "error");
    redirigir("/compras/");
    exit();
}

// --- 2. Recoger datos de la cabecera de la compra ---
$id_proveedor = filter_input(INPUT_POST, 'id_proveedor_compra', FILTER_VALIDATE_INT);
$fecha_compra = filter_input(INPUT_POST, 'fecha_compra_compra', FILTER_SANITIZE_STRING);
$comprobante = filter_input(INPUT_POST, 'comprobante_compra', FILTER_SANITIZE_STRING);

// Los totales generales del formulario (el modelo los recalculará para seguridad)
$subtotal_general_form = filter_input(INPUT_POST, 'subtotal_general_compra_calculado', FILTER_VALIDATE_FLOAT);
$monto_iva_general_form = filter_input(INPUT_POST, 'monto_iva_general_calculado', FILTER_VALIDATE_FLOAT);
$total_general_form = filter_input(INPUT_POST, 'total_general_compra_calculado', FILTER_VALIDATE_FLOAT);

// --- 3. Recoger datos de los ítems de la compra (arrays) ---
$item_ids_productos = $_POST['item_id_producto'] ?? [];
$item_cantidades = $_POST['item_cantidad'] ?? [];
$item_precios_unitarios = $_POST['item_precio_unitario'] ?? [];
$item_porcentajes_iva = $_POST['item_porcentaje_iva'] ?? [];

// --- 4. Validaciones iniciales ---
$errores = [];

if (!$id_proveedor) {
    $errores[] = "Debe seleccionar un proveedor.";
}

if (empty($fecha_compra)) {
    $errores[] = "La fecha de compra es obligatoria.";
} else {
    // Validar formato de fecha
    $fecha_validar = DateTime::createFromFormat('Y-m-d', $fecha_compra);
    if (!$fecha_validar || $fecha_validar->format('Y-m-d') !== $fecha_compra) {
        $errores[] = "El formato de fecha de compra no es válido.";
    }
}

if (empty($item_ids_productos)) {
    $errores[] = "Debe tener al menos un producto en la compra.";
}

// Verificar consistencia de arrays
if (count($item_ids_productos) !== count($item_cantidades) ||
    count($item_ids_productos) !== count($item_precios_unitarios) ||
    count($item_ids_productos) !== count($item_porcentajes_iva)) {
    $errores[] = "Inconsistencia en los datos de los productos. Intente de nuevo.";
}

// --- 5. Validar y procesar ítems ---
$items_para_modelo = [];
if (empty($errores)) {
    for ($i = 0; $i < count($item_ids_productos); $i++) {
        $id_prod = filter_var($item_ids_productos[$i], FILTER_VALIDATE_INT);
        $cantidad_input = $item_cantidades[$i];
        $precio_u = filter_var($item_precios_unitarios[$i], FILTER_VALIDATE_FLOAT);
        $iva_pct = filter_var($item_porcentajes_iva[$i], FILTER_VALIDATE_FLOAT);

        // VALIDACIÓN ESTRICTA: Cantidad debe ser entero absoluto
        $cantidad = intval($cantidad_input);
        if (!is_numeric($cantidad_input) || $cantidad != $cantidad_input || $cantidad <= 0 || strpos($cantidad_input, '.') !== false) {
            $errores[] = "La cantidad para el producto #" . ($i + 1) . " debe ser un número entero positivo (sin decimales).";
            break;
        }

        if (!$id_prod || $precio_u === false || $iva_pct === false) {
            $errores[] = "Datos inválidos para el producto #" . ($i + 1) . ". Verifique ID, precio e IVA.";
            break;
        }

        if ($precio_u < 0) {
            $errores[] = "El precio unitario para el producto #" . ($i + 1) . " no puede ser negativo.";
            break;
        }

        if ($iva_pct < 0) {
            $errores[] = "El IVA para el producto #" . ($i + 1) . " no puede ser negativo.";
            break;
        }

        // Recalcular totales en el backend para seguridad
        $subtotal_item = $cantidad * $precio_u;
        $monto_iva_item = $subtotal_item * ($iva_pct / 100);
        $total_item = $subtotal_item + $monto_iva_item;

        $items_para_modelo[] = [
            'id_producto' => $id_prod,
            'cantidad' => $cantidad, // Entero estricto
            'precio_compra_unitario' => $precio_u,
            'porcentaje_iva_item' => $iva_pct,
            'subtotal_item' => $subtotal_item,
            'monto_iva_item' => $monto_iva_item,
            'total_item' => $total_item,
            'fyh_actualizacion' => $fechaHora
        ];
    }
}

// --- 6. Si hay errores, redirigir ---
if (!empty($errores)) {
    setMensaje(implode("<br>", $errores), "error");
    redirigir("/compras/edit.php?id=" . $id_compra);
    exit();
}

// --- 7. AISLAMIENTO DE USUARIO: Verificar que la compra pertenezca al usuario ---
try {
    $compraModel = new CompraModel($pdo);
    
    // Verificar propiedad de la compra ANTES de cualquier operación
    $compra_actual = $compraModel->getCompraConDetallesPorId($id_compra, $id_usuario_sesion);
    if (!$compra_actual) {
        setMensaje("Compra no encontrada o no tiene permisos para editarla.", "error");
        redirigir("/compras/");
        exit();
    }

    // --- 8. Preparar datos para el modelo ---
    $datos_cabecera_compra = [
        'id_proveedor' => $id_proveedor,
        'fecha_compra' => $fecha_compra,
        'comprobante' => $comprobante ?: null, // Si está vacío, enviar null
        'fyh_actualizacion' => $fechaHora
    ];

    // --- 9. Ejecutar actualización con AISLAMIENTO ESTRICTO ---
    $resultado = $compraModel->actualizarCompraConDetalles(
        $id_compra, 
        $id_usuario_sesion, // AISLAMIENTO: Pasar siempre el ID del usuario
        $datos_cabecera_compra, 
        $items_para_modelo
    );

    if ($resultado === true) {
        setMensaje("Compra #" . sanear($compra_actual['cabecera']['codigo_compra_referencia']) . " actualizada exitosamente.", "success");
        redirigir("/compras/");
    } else {
        setMensaje("Error al actualizar la compra. Es posible que algunos productos no estén disponibles en su inventario.", "error");
        redirigir("/compras/edit.php?id=" . $id_compra);
    }

} catch (PDOException $e) {
    error_log("Error PDO en update.php: " . $e->getMessage() . " | Usuario: " . $id_usuario_sesion . " | Compra: " . $id_compra);
    setMensaje("Error de base de datos al actualizar la compra. Código: " . $e->getCode(), "error");
    redirigir("/compras/edit.php?id=" . $id_compra);
} catch (Exception $e) {
    error_log("Error general en update.php: " . $e->getMessage() . " | Usuario: " . $id_usuario_sesion . " | Compra: " . $id_compra);
    setMensaje("Error inesperado del servidor al actualizar la compra.", "error");
    redirigir("/compras/edit.php?id=" . $id_compra);
}
?>