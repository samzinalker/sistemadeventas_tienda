<?php
include '../../config.php'; // $URL, $pdo, $fechaHora
include '../../utils/funciones_globales.php'; // Para setMensaje, redirigir, sanear
include '../../models/ComprasModel.php'; // Lo necesitaremos para llamar al método de creación
// AlmacenModel podría ser necesario si el controlador actualiza stock, pero es mejor que lo haga el ComprasModel dentro de una transacción.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_usuario'])) {
    setMensaje("Debe iniciar sesión para registrar una compra.", "error");
    redirigir("/login");
    exit();
}
$id_usuario_sesion = $_SESSION['id_usuario'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // --- 1. Recoger datos de la cabecera de la compra ---
    $id_proveedor = filter_input(INPUT_POST, 'id_proveedor_compra', FILTER_VALIDATE_INT);
    $fecha_compra = filter_input(INPUT_POST, 'fecha_compra_compra', FILTER_SANITIZE_STRING);
    $comprobante = filter_input(INPUT_POST, 'comprobante_compra', FILTER_SANITIZE_STRING);
    // El nro_compra_referencia (C-XXXXX) lo genera el JS y se muestra. El nro_compra (secuencial) y el código final
    // los determinará el modelo para asegurar la integridad.
     $codigo_compra_referencia_form = filter_input(INPUT_POST, 'nro_compra_referencia', FILTER_SANITIZE_STRING); // Informativo

    // Los totales generales del formulario son de JS, el modelo debe recalcularlos.
     $subtotal_general_form = filter_input(INPUT_POST, 'subtotal_general_compra_calculado', FILTER_VALIDATE_FLOAT);
     $monto_iva_general_form = filter_input(INPUT_POST, 'monto_iva_general_calculado', FILTER_VALIDATE_FLOAT);
     $total_general_form = filter_input(INPUT_POST, 'total_general_compra_calculado', FILTER_VALIDATE_FLOAT);


    // --- 2. Recoger datos de los ítems de la compra (serán arrays) ---
    $item_ids_productos = $_POST['item_id_producto'] ?? []; // Array de IDs
    $item_cantidades = $_POST['item_cantidad'] ?? [];       // Array de cantidades
    $item_precios_unitarios = $_POST['item_precio_unitario'] ?? []; // Array de precios
    $item_porcentajes_iva = $_POST['item_porcentaje_iva'] ?? [];    // Array de IVAs

    // --- 3. Validaciones ---
    $errores = [];
    if (!$id_proveedor) {
        $errores[] = "Debe seleccionar un proveedor.";
    }
    if (empty($fecha_compra)) { // Podrías añadir validación de formato de fecha
        $errores[] = "La fecha de compra es obligatoria.";
    }
    if (empty($item_ids_productos)) {
        $errores[] = "Debe añadir al menos un producto a la compra.";
    }
    if (count($item_ids_productos) !== count($item_cantidades) ||
        count($item_ids_productos) !== count($item_precios_unitarios) ||
        count($item_ids_productos) !== count($item_porcentajes_iva)) {
        $errores[] = "Inconsistencia en los datos de los productos. Intente de nuevo.";
    }

    $items_para_modelo = [];
    if (empty($errores)) {
        for ($i = 0; $i < count($item_ids_productos); $i++) {
            $id_prod = filter_var($item_ids_productos[$i], FILTER_VALIDATE_INT);
            $cantidad = filter_var($item_cantidades[$i], FILTER_VALIDATE_FLOAT); // Puede ser decimal si se permite
            $precio_u = filter_var($item_precios_unitarios[$i], FILTER_VALIDATE_FLOAT);
            $iva_pct = filter_var($item_porcentajes_iva[$i], FILTER_VALIDATE_FLOAT);

            if (!$id_prod || $cantidad === false || $precio_u === false || $iva_pct === false) {
                $errores[] = "Datos inválidos para el producto #" . ($i + 1) . ". Verifique cantidad, precio e IVA.";
                break;
            }
            if ($cantidad <= 0) {
                $errores[] = "La cantidad para el producto #" . ($i + 1) . " debe ser mayor a cero.";
                break;
            }
             if ($precio_u < 0) { // Permitir precio 0 si es necesario
                $errores[] = "El precio unitario para el producto #" . ($i + 1) . " no puede ser negativo.";
                break;
            }
            if ($iva_pct < 0) {
                $errores[] = "El IVA para el producto #" . ($i + 1) . " no puede ser negativo.";
                break;
            }
            
            // Recalcular subtotal, IVA y total del ítem en el backend
            $subtotal_item = $cantidad * $precio_u;
            $monto_iva_item = $subtotal_item * ($iva_pct / 100);
            $total_item = $subtotal_item + $monto_iva_item;

            $items_para_modelo[] = [
                'id_producto' => $id_prod,
                'cantidad' => $cantidad,
                'precio_compra_unitario' => $precio_u,
                'porcentaje_iva_item' => $iva_pct,
                'subtotal_item' => $subtotal_item,
                'monto_iva_item' => $monto_iva_item,
                'total_item' => $total_item,
                'fyh_creacion' => $fechaHora, // $fechaHora de config.php
                'fyh_actualizacion' => $fechaHora
            ];
        }
    }

    if (!empty($errores)) {
        setMensaje(implode("<br>", $errores), "error");
        // No redirigir inmediatamente para que el usuario vea los errores,
        // pero como no tenemos un "re-populate" del form, redirigimos a create
        redirigir("/compras/create.php");
        exit();
    }

    // --- 4. Preparar datos para el modelo ---
    $datos_cabecera_compra = [
        'id_usuario' => $id_usuario_sesion,
        'id_proveedor' => $id_proveedor,
        'fecha_compra' => $fecha_compra,
        'comprobante' => $comprobante ?: null, // Si está vacío, enviar null
        // nro_compra y codigo_compra_referencia serán generados por el modelo
        // los totales generales también serán calculados por el modelo a partir de los items
        'fyh_creacion' => $fechaHora,
        'fyh_actualizacion' => $fechaHora
    ];

    // --- 5. Llamar al modelo ---
    try {
        $compraModel = new CompraModel($pdo);
        // Este método registrarCompraConDetalles necesitará ser creado/actualizado en CompraModel.php
        $resultado = $compraModel->registrarCompraConDetalles($datos_cabecera_compra, $items_para_modelo);

        if ($resultado && !is_array($resultado)) { // Si devuelve el ID de la compra o true
            setMensaje("Compra registrada exitosamente con ID: " . $resultado, "success");
            redirigir("/compras/"); // Redirigir a la lista de compras
        } elseif (is_array($resultado) && isset($resultado['error'])) { // Si el modelo devuelve un error específico
             setMensaje("Error al registrar la compra: " . $resultado['error'], "error");
             redirigir("/compras/create.php");
        }
        else {
            setMensaje("Error al registrar la compra. Intente de nuevo.", "error");
            redirigir("/compras/create.php");
        }
    } catch (PDOException $e) {
        error_log("Error PDO en controller_create_compra.php: " . $e->getMessage());
        setMensaje("Error de base de datos al registrar la compra. Código: " . $e->getCode(), "error");
        redirigir("/compras/create.php");
    } catch (Exception $e) {
        error_log("Error general en controller_create_compra.php: " . $e->getMessage());
        setMensaje("Error inesperado del servidor. Código: " . $e->getCode(), "error");
        redirigir("/compras/create.php");
    }

} else {
    setMensaje("Método de solicitud no permitido.", "error");
    redirigir("/compras/");
    exit();
}
?>