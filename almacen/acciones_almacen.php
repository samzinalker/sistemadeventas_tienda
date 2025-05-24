<?php
// --- Resumen del Archivo ---
// Nombre: almacen/acciones_almacen.php
// Función: Maneja acciones CRUD para productos del almacén, especialmente la creación rápida.
//          Es invocado vía AJAX desde compras/create.php (modal de creación rápida).
// Método HTTP esperado: POST
// Parámetros POST esperados para 'crear_producto_almacen_rapido':
//   - accion: 'crear_producto_almacen_rapido'
//   - id_usuario_creador: ID del usuario
//   - producto_codigo: Código del producto (generado por controller_generar_siguiente_codigo.php)
//   - producto_nombre: Nombre
//   - producto_descripcion: Descripción (opcional)
//   - producto_id_categoria: ID de la categoría
//   - producto_precio_compra: Precio de compra
//   - producto_precio_venta: Precio de venta
//   - producto_iva_predeterminado: IVA predeterminado del producto (NUEVO)
//   - producto_stock_minimo: Stock mínimo (opcional)
//   - producto_stock_maximo: Stock máximo (opcional)
//   - producto_fecha_ingreso: Fecha de ingreso
// Respuesta: JSON
//   - Éxito: {"status": "success", "message": "...", "producto": { ...datos del producto... }}
//   - Error: {"status": "error", "message": "..."}

require_once __DIR__ . '/../app/config.php';
require_once __DIR__ . '/../app/models/AlmacenModel.php';

header('Content-Type: application/json');
$response = ['status' => 'error', 'message' => 'Acción no reconocida o datos incompletos.'];

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_usuario'])) {
    $response['message'] = 'Sesión no iniciada.';
    echo json_encode($response);
    exit;
}
$id_usuario_actual = $_SESSION['id_usuario'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    $almacenModel = new AlmacenModel($pdo);
    $accion = $_POST['accion'];

    if ($accion === 'crear_producto_almacen_rapido') {
        // Validar que el id_usuario_creador coincida con la sesión
        $id_usuario_creador_post = filter_input(INPUT_POST, 'id_usuario_creador', FILTER_VALIDATE_INT);
        if ($id_usuario_creador_post !== $id_usuario_actual) {
            $response['message'] = 'Error de validación de usuario.';
            echo json_encode($response);
            exit;
        }

        // Recoger y sanitizar datos
        $producto_codigo = filter_input(INPUT_POST, 'producto_codigo', FILTER_SANITIZE_STRING);
        $producto_nombre = filter_input(INPUT_POST, 'producto_nombre', FILTER_SANITIZE_STRING);
        $producto_descripcion = filter_input(INPUT_POST, 'producto_descripcion', FILTER_SANITIZE_STRING);
        $producto_id_categoria = filter_input(INPUT_POST, 'producto_id_categoria', FILTER_VALIDATE_INT);
        
        // Para campos decimales/flotantes, validar y convertir
        $producto_precio_compra_str = filter_input(INPUT_POST, 'producto_precio_compra', FILTER_SANITIZE_STRING);
        $producto_precio_venta_str = filter_input(INPUT_POST, 'producto_precio_venta', FILTER_SANITIZE_STRING);
        $producto_iva_predeterminado_str = filter_input(INPUT_POST, 'producto_iva_predeterminado', FILTER_SANITIZE_STRING);

        $producto_precio_compra = filter_var($producto_precio_compra_str, FILTER_VALIDATE_FLOAT);
        $producto_precio_venta = filter_var($producto_precio_venta_str, FILTER_VALIDATE_FLOAT);
        $producto_iva_predeterminado = filter_var($producto_iva_predeterminado_str, FILTER_VALIDATE_FLOAT);


        $producto_stock_minimo = filter_input(INPUT_POST, 'producto_stock_minimo', FILTER_VALIDATE_INT, ['options' => ['default' => null]]);
        $producto_stock_maximo = filter_input(INPUT_POST, 'producto_stock_maximo', FILTER_VALIDATE_INT, ['options' => ['default' => null]]);
        $producto_fecha_ingreso = filter_input(INPUT_POST, 'producto_fecha_ingreso', FILTER_SANITIZE_STRING); // Validar formato fecha si es necesario

        // Validaciones básicas
        if (empty($producto_codigo) || empty($producto_nombre) || $producto_id_categoria === false ||
            $producto_precio_compra === false || $producto_precio_venta === false ||
            $producto_iva_predeterminado === false || // IVA es requerido
            empty($producto_fecha_ingreso)) {
            
            $missing_fields = [];
            if (empty($producto_codigo)) $missing_fields[] = 'Código';
            if (empty($producto_nombre)) $missing_fields[] = 'Nombre';
            if ($producto_id_categoria === false) $missing_fields[] = 'Categoría';
            if ($producto_precio_compra === false) $missing_fields[] = 'Precio Compra';
            if ($producto_precio_venta === false) $missing_fields[] = 'Precio Venta';
            if ($producto_iva_predeterminado === false) $missing_fields[] = 'IVA Predeterminado';
            if (empty($producto_fecha_ingreso)) $missing_fields[] = 'Fecha Ingreso';

            $response['message'] = 'Faltan datos requeridos o son inválidos: ' . implode(', ', $missing_fields);
            // Para depuración más detallada:
            // $response['debug_iva_str'] = $producto_iva_predeterminado_str;
            // $response['debug_iva_float'] = $producto_iva_predeterminado;
            echo json_encode($response);
            exit;
        }
        
        // El stock inicial al crear un producto desde aquí suele ser 0, ya que se añade con una compra.
        $stock_inicial = 0; 

        $datos_nuevo_producto = [
            'codigo' => $producto_codigo,
            'nombre' => $producto_nombre,
            'descripcion' => $producto_descripcion ?: null,
            'stock' => $stock_inicial, 
            'stock_minimo' => $producto_stock_minimo,
            'stock_maximo' => $producto_stock_maximo,
            'precio_compra' => $producto_precio_compra,
            'precio_venta' => $producto_precio_venta,
            'iva_predeterminado' => $producto_iva_predeterminado, // NUEVO CAMPO
            'fecha_ingreso' => $producto_fecha_ingreso,
            'imagen' => null, // La creación rápida no maneja imagen
            'id_usuario' => $id_usuario_actual,
            'id_categoria' => $producto_id_categoria,
            'fyh_creacion' => $fechaHora, // $fechaHora viene de config.php
            'fyh_actualizacion' => $fechaHora
        ];

        try {
            $id_nuevo_producto = $almacenModel->crearProducto($datos_nuevo_producto);
            if ($id_nuevo_producto) {
                $producto_creado = $almacenModel->getProductoByIdAndUsuarioId(intval($id_nuevo_producto), $id_usuario_actual);
                if ($producto_creado) {
                    // Asegurar que el producto devuelto incluya el iva_predeterminado
                    // getProductoByIdAndUsuarioId ya lo hace si la columna existe y tiene valor.
                    $response['status'] = 'success';
                    $response['message'] = 'Producto "' . htmlspecialchars($producto_creado['nombre']) . '" creado exitosamente.';
                    // Devolver el objeto producto completo, incluyendo el iva_predeterminado que ahora está en la tabla
                    $response['producto'] = $producto_creado; 
                } else {
                     $response['message'] = 'Producto creado, pero no se pudo recuperar para la respuesta.';
                }
            } else {
                $response['message'] = 'Error al guardar el producto en la base de datos.';
            }
        } catch (PDOException $e) {
            error_log("Error PDO en acciones_almacen.php (crear producto): " . $e->getMessage());
            $response['message'] = 'Error de base de datos al crear el producto.';
        } catch (Exception $e) {
            error_log("Error general en acciones_almacen.php (crear producto): " . $e->getMessage());
            $response['message'] = 'Error inesperado del servidor al crear el producto.';
        }
    }
    // Aquí podrían ir otras acciones como 'actualizar_producto_rapido', 'eliminar_producto_rapido'
}

echo json_encode($response);
?>