<?php
require_once('../app/config.php');
require_once('../app/utils/funciones_globales.php');
require_once('../layout/sesion.php');
require_once('../layout/parte1.php');

// Incluir modelos necesarios solo una vez
require_once('../app/models/ComprasModel.php');
require_once('../app/models/ProveedorModel.php');
require_once('../app/models/AlmacenModel.php');

$modulo_abierto = 'compras';
$pagina_activa = 'compras_edit';

// Validar que se envió un ID
if (!isset($_GET['id'])) {
    setMensaje("ID de compra no proporcionado.", "error");
    redirigir("/compras/");
    exit();
}

$id_compra = filter_var($_GET['id'], FILTER_VALIDATE_INT);
if (!$id_compra) {
    setMensaje("ID de compra no válido.", "error");
    redirigir("/compras/");
    exit();
}

// Validación estricta de sesión de usuario
if (!isset($_SESSION['id_usuario'])) {
    echo "<div class='container'>Error: No se pudo obtener el ID del usuario de la sesión. Por favor, inicie sesión nuevamente.</div>";
    include '../layout/parte2.php';
    exit;
}
$id_usuario_sesion = $_SESSION['id_usuario'];

// Obtener datos de la compra CON AISLAMIENTO DE USUARIO ESTRICTO
try {
    $compraModel = new CompraModel($pdo);
    $compra_datos = $compraModel->getCompraConDetallesPorId($id_compra, $id_usuario_sesion);
    
    if (!$compra_datos) {
        setMensaje("Compra no encontrada o no tiene permisos para editarla.", "error");
        redirigir("/compras/");
        exit();
    }
    
    // AISLAMIENTO DE USUARIO: Obtener SOLO los proveedores del usuario actual
    $proveedorModel = new ProveedorModel($pdo, $URL);
    $proveedores_datos = $proveedorModel->getProveedoresByUsuarioId($id_usuario_sesion);
    
    // AISLAMIENTO DE USUARIO: Obtener SOLO los productos del usuario actual
    $almacenModel = new AlmacenModel($pdo);
    $productos_datos = $almacenModel->getProductosByUsuarioId($id_usuario_sesion);
    
} catch (Exception $e) {
    error_log("Error al obtener datos de compra para editar: " . $e->getMessage());
    setMensaje("Error al cargar los datos de la compra.", "error");
    redirigir("/compras/");
    exit();
}

include '../layout/mensajes.php';
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Editar Compra #<?php echo sanear($compra_datos['cabecera']['codigo_compra_referencia']); ?></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?php echo $URL; ?>">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo $URL; ?>/compras">Mis Compras</a></li>
                        <li class="breadcrumb-item active">Editar Compra</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <form action="<?php echo $URL; ?>/app/controllers/compras/update.php" method="POST" id="formEditarCompra">
                <input type="hidden" name="id_compra" value="<?php echo $id_compra; ?>">
                <input type="hidden" name="id_usuario_compra" value="<?php echo $id_usuario_sesion; ?>">
                
                <div class="row">
                    <!-- Columna Izquierda: Datos Generales y Productos -->
                    <div class="col-md-8">
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title">Datos Generales y Productos</h3>
                            </div>
                            <div class="card-body">
                                <!-- Datos del Proveedor y Fecha -->
                                <div class="row">
                                    <div class="col-md-7">
                                        <div class="form-group">
                                            <label for="proveedor">Proveedor</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="nombre_proveedor_compra_display" 
                                                       name="nombre_proveedor_compra_display" 
                                                       value="<?php echo sanear($compra_datos['cabecera']['nombre_proveedor'] ?? ''); ?>" 
                                                       placeholder="Seleccione un proveedor..." readonly>
                                                <input type="hidden" id="id_proveedor_compra" name="id_proveedor_compra" 
                                                       value="<?php echo $compra_datos['cabecera']['id_proveedor']; ?>" required>
                                                <div class="input-group-append">
                                                    <button type="button" class="btn btn-info" data-toggle="modal" data-target="#modalBuscarProveedor">
                                                        <i class="fas fa-search"></i> Buscar/Cambiar
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="detalle_proveedor_seleccionado" class="alert alert-light mt-0 mb-2 py-1 px-2" style="font-size:0.9em;">
                                            <small><strong>Empresa:</strong> <span id="info_empresa_proveedor"><?php echo sanear($compra_datos['cabecera']['empresa_proveedor'] ?? 'N/A'); ?></span> | 
                                            <strong>Celular:</strong> <span id="info_celular_proveedor"><?php echo sanear($compra_datos['cabecera']['celular_proveedor'] ?? 'N/A'); ?></span></small>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label for="fecha_compra_compra">Fecha de Compra</label>
                                            <input type="date" class="form-control" id="fecha_compra_compra" name="fecha_compra_compra" 
                                                   value="<?php echo sanear($compra_datos['cabecera']['fecha_compra']); ?>" required>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="nro_compra_referencia">Nro. Compra (Referencia Interna)</label>
                                            <input type="text" class="form-control" id="nro_compra_referencia" name="nro_compra_referencia"
                                                   value="<?php echo sanear($compra_datos['cabecera']['codigo_compra_referencia']); ?>" required readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="comprobante_compra">Comprobante (Factura/Boleta Nro.)</label>
                                            <input type="text" class="form-control" id="comprobante_compra" name="comprobante_compra" 
                                                   value="<?php echo sanear($compra_datos['cabecera']['comprobante'] ?? ''); ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <hr>
                                
                                <!-- Sección para Añadir Productos a la Lista -->
                                <h5>Añadir Producto a la Compra</h5>
                                <div class="row align-items-end">
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label for="temp_nombre_producto">Producto</label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="temp_nombre_producto" placeholder="Buscar producto..." readonly>
                                                <input type="hidden" id="temp_id_producto">
                                                <input type="hidden" id="temp_codigo_producto">
                                                <input type="hidden" id="temp_iva_predeterminado_producto">
                                                <input type="hidden" id="temp_precio_compra_sugerido_producto">
                                                <input type="hidden" id="temp_stock_actual_producto">
                                                <div class="input-group-append">
                                                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalBuscarProducto">
                                                        <i class="fas fa-search"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <small id="temp_producto_info" class="form-text text-muted" style="display:none;"></small>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="temp_cantidad">Cantidad</label>
                                            <input type="number" class="form-control" id="temp_cantidad" min="1" value="1">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="temp_precio_compra">Precio U.</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend"><span class="input-group-text">$</span></div>
                                                <input type="number" step="0.01" class="form-control" id="temp_precio_compra" min="0">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label for="temp_porcentaje_iva">IVA %</label>
                                            <input type="number" step="0.01" class="form-control" id="temp_porcentaje_iva" min="0" value="0">
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        <div class="form-group">
                                            <button type="button" class="btn btn-success btn-block" id="btnAnadirProductoALista" title="Añadir a la lista">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                
                                <hr>
                                
                                <!-- Tabla de Ítems de la Compra -->
                                <h5>Ítems de la Compra</h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover table-sm" id="tablaItemsCompra">
                                        <thead class="thead-light">
                                            <tr>
                                                <th style="width: 30px;">#</th>
                                                <th>Código</th>
                                                <th>Producto</th>
                                                <th style="width: 80px;">Cant.</th>
                                                <th style="width: 100px;">Precio U.</th>
                                                <th style="width: 70px;">IVA %</th>
                                                <th style="width: 100px;">Subtotal</th>
                                                <th style="width: 90px;">Monto IVA</th>
                                                <th style="width: 110px;">Total Ítem</th>
                                                <th style="width: 50px;">Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($compra_datos['detalles'] as $index => $detalle): ?>
                                            <tr>
                                                <td><?php echo $index + 1; ?></td>
                                                <td>
                                                    <?php echo sanear($detalle['codigo_producto'] ?? 'N/A'); ?>
                                                    <input type="hidden" name="item_id_producto[]" value="<?php echo $detalle['id_producto']; ?>">
                                                    <input type="hidden" name="item_codigo_producto[]" value="<?php echo sanear($detalle['codigo_producto'] ?? ''); ?>">
                                                    <input type="hidden" name="item_nombre_producto[]" value="<?php echo sanear($detalle['nombre_producto']); ?>">
                                                </td>
                                                <td><?php echo sanear($detalle['nombre_producto']); ?></td>
                                                <td>
                                                    <input type="number" name="item_cantidad[]" class="form-control form-control-sm item-cantidad" 
                                                           value="<?php echo intval($detalle['cantidad']); ?>" min="1" step="1" style="width:70px;" required>
                                                </td>
                                                <td>
                                                    <input type="number" name="item_precio_unitario[]" class="form-control form-control-sm item-precio" 
                                                           step="0.01" value="<?php echo number_format($detalle['precio_compra_unitario'], 2, '.', ''); ?>" min="0" style="width:90px;" required>
                                                </td>
                                                <td>
                                                    <input type="number" name="item_porcentaje_iva[]" class="form-control form-control-sm item-iva" 
                                                           step="0.01" value="<?php echo number_format($detalle['porcentaje_iva_item'], 2, '.', ''); ?>" min="0" style="width:60px;" required>
                                                </td>
                                                <td class="item-subtotal"><?php echo number_format($detalle['subtotal_item'], 2); ?></td>
                                                <td class="item-monto-iva"><?php echo number_format($detalle['monto_iva_item'], 2); ?></td>
                                                <td class="item-total"><?php echo number_format($detalle['total_item'], 2); ?></td>
                                                <td>
                                                    <button type="button" class="btn btn-danger btn-sm btn-eliminar-item">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                            
                                            <!-- Fila que aparece cuando no hay productos -->
                                            <tr id="filaNoItems" style="display: none;">
                                                <td colspan="10" class="text-center">No hay productos añadidos a la compra.</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Columna Derecha: Resumen y Totales -->
                    <div class="col-md-4">
                        <div class="card card-primary">
                            <div class="card-header">
                                <h3 class="card-title">Resumen de la Compra</h3>
                            </div>
                            <div class="card-body">
                                <div class="text-right">
                                    <h5>Subtotal General: <span class="text-muted">$<span id="subtotal_general_compra_display"><?php echo number_format($compra_datos['cabecera']['subtotal_general'], 2); ?></span></span></h5>
                                    <h5>IVA General: <span class="text-muted">$<span id="monto_iva_general_display"><?php echo number_format($compra_datos['cabecera']['monto_iva_general'], 2); ?></span></span></h5>
                                    <hr>
                                    <h3>Total Compra: <span class="text-primary">$<span id="total_general_compra_display"><?php echo number_format($compra_datos['cabecera']['total_general'], 2); ?></span></span></h3>
                                </div>
                                
                                <!-- Campos hidden para enviar los totales generales -->
                                <input type="hidden" name="subtotal_general_compra_calculado" id="subtotal_general_compra_hidden" value="<?php echo $compra_datos['cabecera']['subtotal_general']; ?>">
                                <input type="hidden" name="monto_iva_general_calculado" id="monto_iva_general_hidden" value="<?php echo $compra_datos['cabecera']['monto_iva_general']; ?>">
                                <input type="hidden" name="total_general_compra_calculado" id="total_general_compra_hidden" value="<?php echo $compra_datos['cabecera']['total_general']; ?>">
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary btn-block" id="btn_actualizar_compra">
                                    <i class="fas fa-save"></i> Actualizar Compra
                                </button>
                                <a href="<?php echo $URL; ?>/compras/" class="btn btn-secondary btn-block mt-2">
                                    <i class="fas fa-times"></i> Cancelar
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Buscar/Crear Producto -->
<div class="modal fade" id="modalBuscarProducto" tabindex="-1" role="dialog" aria-labelledby="modalBuscarProductoLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalBuscarProductoLabel">Buscar o Crear Producto para su Almacén</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs" id="productoTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="buscar-producto-tab" data-toggle="tab" href="#buscarProductoPane" role="tab" aria-controls="buscarProductoPane" aria-selected="true">Buscar Producto</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="crear-producto-tab" data-toggle="tab" href="#crearProductoPane" role="tab" aria-controls="crearProductoPane" aria-selected="false">Crear Nuevo Producto</a>
                    </li>
                </ul>
                <div class="tab-content" id="productoTabsContent">
                    <div class="tab-pane fade show active p-3" id="buscarProductoPane" role="tabpanel" aria-labelledby="buscar-producto-tab">
                        <p>Estos son los productos de su almacén personal.</p>
                        <table id="tablaProductosAlmacen" class="table table-bordered table-striped table-hover" style="width:100%">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Código</th>
                                    <th>Nombre</th>
                                    <th>Stock</th>
                                    <th>P. Compra Ref.</th>
                                    <th>IVA Prod. (%)</th>
                                    <th>Categoría</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <div class="tab-pane fade p-3" id="crearProductoPane" role="tabpanel" aria-labelledby="crear-producto-tab">
                        <h5>Registrar Nuevo Producto en su Almacén (Creación Rápida)</h5>
                        <form id="formNuevoProductoRapido" class="mt-3">
                            <input type="hidden" name="id_usuario_creador" value="<?php echo $id_usuario_sesion; ?>">
                            <input type="hidden" name="accion" value="crear_producto_almacen_rapido">
                            <input type="hidden" id="producto_iva_predeterminado_rapido_hidden" name="producto_iva_predeterminado">
                            <input type="hidden" id="producto_codigo_rapido_hidden" name="producto_codigo">

                            <div class="row">
                                <div class="col-md-4 form-group">
                                    <label for="producto_codigo_rapido_display">Código <small>(Se autogenera)</small></label>
                                    <input type="text" class="form-control" id="producto_codigo_rapido_display" readonly placeholder="Generando...">
                                </div>
                                <div class="col-md-8 form-group">
                                    <label for="producto_nombre_rapido">Nombre del Producto <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="producto_nombre_rapido" name="producto_nombre" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="producto_descripcion_rapido">Descripción</label>
                                <textarea class="form-control" id="producto_descripcion_rapido" name="producto_descripcion" rows="2"></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-3 form-group">
                                    <label for="producto_id_categoria_rapido">Categoría <span class="text-danger">*</span></label>
                                    <select class="form-control" id="producto_id_categoria_rapido" name="producto_id_categoria" required>
                                        <option value="">Cargando sus categorías...</option>
                                    </select>
                                </div>
                                <div class="col-md-3 form-group">
                                    <label for="producto_precio_compra_rapido">Precio Compra Ref. <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" class="form-control" id="producto_precio_compra_rapido" name="producto_precio_compra" min="0" required>
                                </div>
                                <div class="col-md-3 form-group">
                                    <label for="producto_precio_venta_rapido">Precio Venta Ref. <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" class="form-control" id="producto_precio_venta_rapido" name="producto_precio_venta" min="0" required>
                                </div>
                                <div class="col-md-3 form-group">
                                    <label for="producto_iva_rapido">IVA Predeterminado (%) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" class="form-control" id="producto_iva_rapido" name="producto_iva_predeterminado_visible" value="0" min="0" required>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 form-group">
                                    <label for="producto_stock_minimo_rapido">Stock Mínimo <small>(Opcional)</small></label>
                                    <input type="number" class="form-control" id="producto_stock_minimo_rapido" name="producto_stock_minimo" min="0">
                                </div>
                                <div class="col-md-4 form-group">
                                    <label for="producto_stock_maximo_rapido">Stock Máximo <small>(Opcional)</small></label>
                                    <input type="number" class="form-control" id="producto_stock_maximo_rapido" name="producto_stock_maximo" min="0">
                                </div>
                                <div class="col-md-4 form-group">
                                    <label for="producto_fecha_ingreso_rapido">Fecha Ingreso <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control" id="producto_fecha_ingreso_rapido" name="producto_fecha_ingreso" value="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-success"><i class="fas fa-plus-circle"></i> Guardar Nuevo Producto</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Buscar/Crear Proveedor -->
<div class="modal fade" id="modalBuscarProveedor" tabindex="-1" role="dialog" aria-labelledby="modalBuscarProveedorLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalBuscarProveedorLabel">Buscar o Crear Proveedor Personal</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs" id="proveedorTabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="buscar-proveedor-tab" data-toggle="tab" href="#buscarProveedorPane" role="tab" aria-controls="buscarProveedorPane" aria-selected="true">Buscar Proveedor</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="crear-proveedor-tab" data-toggle="tab" href="#crearProveedorPane" role="tab" aria-controls="crearProveedorPane" aria-selected="false">Crear Nuevo Proveedor</a>
                    </li>
                </ul>
                <div class="tab-content" id="proveedorTabsContent">
                    <div class="tab-pane fade show active p-3" id="buscarProveedorPane" role="tabpanel" aria-labelledby="buscar-proveedor-tab">
                        <p>Estos son sus proveedores personales.</p>
                        <table id="tablaProveedores" class="table table-bordered table-striped table-hover" style="width:100%">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Empresa</th>
                                    <th>Celular</th>
                                    <th>Teléfono</th>
                                    <th>Email</th>
                                    <th>Dirección</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                    <div class="tab-pane fade p-3" id="crearProveedorPane" role="tabpanel" aria-labelledby="crear-proveedor-tab">
                        <h5>Registrar Nuevo Proveedor Personal</h5>
                        <form id="formNuevoProveedor" class="mt-3">
                            <input type="hidden" name="id_usuario_creador" value="<?php echo $id_usuario_sesion; ?>">
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label for="nuevo_proveedor_nombre">Nombre del Proveedor <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nuevo_proveedor_nombre" name="nombre_proveedor" required>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="nuevo_proveedor_empresa">Empresa</label>
                                    <input type="text" class="form-control" id="nuevo_proveedor_empresa" name="empresa">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label for="nuevo_proveedor_celular">Celular <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="nuevo_proveedor_celular" name="celular" required>
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="nuevo_proveedor_telefono">Teléfono <small>(Opcional)</small></label>
                                    <input type="text" class="form-control" id="nuevo_proveedor_telefono" name="telefono">
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label for="nuevo_proveedor_email">Email <small>(Opcional)</small></label>
                                    <input type="email" class="form-control" id="nuevo_proveedor_email" name="email">
                                </div>
                                <div class="col-md-6 form-group">
                                    <label for="nuevo_proveedor_direccion">Dirección</label>
                                    <input type="text" class="form-control" id="nuevo_proveedor_direccion" name="direccion">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-success"><i class="fas fa-plus-circle"></i> Guardar Nuevo Proveedor</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Productos disponibles para el JS -->
<script>
const productosDisponibles = <?php echo json_encode($productos_datos); ?>;
</script>

<?php require_once('../layout/parte2.php'); ?>

<script>
$(document).ready(function() {
    var tablaProductosAlmacen;
    var tablaProveedores; 
    var idUsuarioActual = <?php echo json_encode($id_usuario_sesion); ?>;
    var contadorItemsCompra = <?php echo count($compra_datos['detalles']); ?>;

    // --- LÓGICA PARA MODAL DE PRODUCTOS ---
    function generarSiguienteCodigoProducto() {
        $('#producto_codigo_rapido_display').val('Generando...');
        $.ajax({
            url: '<?php echo $URL; ?>/app/controllers/almacen/controller_generar_siguiente_codigo.php',
            type: 'POST',
            data: { id_usuario: idUsuarioActual }, // AISLAMIENTO DE USUARIO
            dataType: 'json',
            success: function(response) {
                if (response && response.status === 'success' && response.nuevo_codigo) {
                    $('#producto_codigo_rapido_display').val(response.nuevo_codigo);
                    $('#producto_codigo_rapido_hidden').val(response.nuevo_codigo);
                } else {
                    $('#producto_codigo_rapido_display').val('Error al generar');
                    $('#producto_codigo_rapido_hidden').val('');
                    Swal.fire('Error', response.message || 'No se pudo generar el código del producto.', 'error');
                }
            },
            error: function() {
                $('#producto_codigo_rapido_display').val('Error de conexión');
                $('#producto_codigo_rapido_hidden').val('');
                Swal.fire('Error', 'No se pudo conectar para generar el código del producto.', 'error');
            }
        });
    }

    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        if ($(e.target).attr('href') === '#crearProductoPane' && $(e.target).closest('.modal').attr('id') === 'modalBuscarProducto') {
            generarSiguienteCodigoProducto();
            $('#producto_iva_rapido').val( parseFloat($('#temp_porcentaje_iva').val()) || 0);
        }
    });

    function cargarCategoriasUsuario() {
        $.ajax({
            url: '<?php echo $URL; ?>/app/controllers/categorias/controller_listar_categorias_usuario.php', 
            type: 'POST', 
            data: { id_usuario: idUsuarioActual }, // AISLAMIENTO DE USUARIO
            dataType: 'json',
            success: function(response) {
                var options = '<option value="">Seleccione una categoría</option>';
                if(response && response.status === 'success' && response.data && response.data.length > 0) {
                    response.data.forEach(function(cat) {
                        options += '<option value="' + cat.id_categoria + '">' + cat.nombre_categoria + '</option>';
                    });
                } else if (response && response.status === 'success' && response.data && response.data.length === 0) {
                    options = '<option value="">No tiene categorías. Puede crear en el módulo de Categorías.</option>';
                } else {
                     options = '<option value="">Error: ' + (response.message || 'No se pudo cargar categorías') + '</option>';
                }
                $('#producto_id_categoria_rapido').html(options);
            },
            error: function() {
                $('#producto_id_categoria_rapido').html('<option value="">Error de conexión al cargar categorías</option>');
            }
        });
    }

    // Inicializar modal de productos
    $('#modalBuscarProducto').on('shown.bs.modal', function () {
        cargarCategoriasUsuario(); 
        if ($('#crear-producto-tab').hasClass('active')) { 
            generarSiguienteCodigoProducto();
            $('#producto_iva_rapido').val( parseFloat($('#temp_porcentaje_iva').val()) || 0); 
        }

        if ($.fn.DataTable.isDataTable('#tablaProductosAlmacen')) {
            try {
                tablaProductosAlmacen.ajax.reload(null, false);
            } catch (e) {
                $('#tablaProductosAlmacen').DataTable().destroy();
                $('#tablaProductosAlmacen tbody').empty();
                inicializarTablaProductos();
            }
        } else {
            inicializarTablaProductos();
        }
    });

    function inicializarTablaProductos() {
        try {
            tablaProductosAlmacen = $('#tablaProductosAlmacen').DataTable({
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "<?php echo $URL; ?>/app/controllers/almacen/controller_buscar_productos_dt.php",
                    "type": "POST",
                    "data": function (d) {
                        d.id_usuario = idUsuarioActual; // AISLAMIENTO DE USUARIO ESTRICTO
                    },
                    "error": function(jqXHR, textStatus, errorThrown) {
                        console.error("Error en AJAX de DataTables:", textStatus, errorThrown);
                    }
                },
                "columns": [
                    { "data": "id_producto" },
                    { "data": "codigo" },
                    { "data": "nombre" },
                    { "data": "stock" },
                    { "data": "precio_compra", "render": $.fn.dataTable.render.number(',', '.', 2, '$') },
                    { "data": "iva_porcentaje_producto", "render": function(data, type, row){ return (parseFloat(data) || 0).toFixed(2) + '%';} }, 
                    { "data": "nombre_categoria" },
                    { 
                        "data": null, 
                        "title": "Acción", 
                        "orderable": false, 
                        "searchable": false,
                        "defaultContent": "<button type='button' class='btn btn-sm btn-success seleccionar-producto-para-compra'><i class='fas fa-check-circle'></i> Sel.</button>" 
                    }
                ],
                "language": {"url": "<?php echo $URL;?>/public/templeates/AdminLTE-3.2.0/plugins/datatables-plugins/i18n/es_es.json"},
                "responsive": true, 
                "lengthChange": true, 
                "autoWidth": false, 
                "pageLength": 5, 
                "lengthMenu": [5, 10, 25, 50]
            });
        } catch (e) {
            console.error("Error durante la inicialización de DataTables:", e);
        }
    }

    // Manejar selección de producto
    $('#tablaProductosAlmacen tbody').on('click', '.seleccionar-producto-para-compra', function () {
        if (!tablaProductosAlmacen) {
            console.error("La instancia de tablaProductosAlmacen no está disponible.");
            return;
        }
        
        var fila = $(this).closest('tr');
        var datosFila;
        if (tablaProductosAlmacen.row(fila).child.isShown()) {
            datosFila = tablaProductosAlmacen.row(fila.prev('tr.dt-hasChild')).data();
        } else {
            datosFila = tablaProductosAlmacen.row(fila).data();
        }

        if (!datosFila) {
            console.error("No se pudieron obtener los datos de la fila para el producto seleccionado.");
            Swal.fire('Error', 'No se pudieron obtener los datos del producto. Intente de nuevo.', 'error');
            return;
        }

        var idProducto = datosFila.id_producto;
        // Verificar si el producto ya está en la lista
        var yaEnLista = false;
        $('#tablaItemsCompra tbody tr').not('#filaNoItems').each(function() {
            if ($(this).find('input[name="item_id_producto[]"]').val() == idProducto) {
                yaEnLista = true;
                return false;
            }
        });

        if (yaEnLista) {
            Swal.fire('Atención', 'Este producto ya ha sido añadido a la lista.', 'warning');
            return;
        }
    
        $('#temp_id_producto').val(datosFila.id_producto);
        $('#temp_nombre_producto').val(datosFila.nombre);
        $('#temp_codigo_producto').val(datosFila.codigo || 'N/A');
        $('#temp_stock_actual_producto').val(datosFila.stock || 0);
        
        let precioCompraSugerido = parseFloat(datosFila.precio_compra || 0).toFixed(2);
        $('#temp_precio_compra_sugerido_producto').val(precioCompraSugerido);
        $('#temp_precio_compra').val(precioCompraSugerido > 0 ? precioCompraSugerido : '');

        // IVA dinámico
        let ivaAplicar;
        if (datosFila.hasOwnProperty('iva_ultima_compra') && datosFila.iva_ultima_compra !== null && !isNaN(parseFloat(datosFila.iva_ultima_compra))) {
            ivaAplicar = parseFloat(datosFila.iva_ultima_compra).toFixed(2);
        } else {
            ivaAplicar = parseFloat(datosFila.iva_porcentaje_producto || 0).toFixed(2);
        }
        
        $('#temp_iva_predeterminado_producto').val(ivaAplicar);
        $('#temp_porcentaje_iva').val(ivaAplicar);
        
        $('#temp_producto_info').html(`Cód: ${datosFila.codigo || 'N/A'} | Stock: ${datosFila.stock || 0} | IVA Aplicado: ${ivaAplicar}%`).show();
        $('#temp_cantidad').val(1).focus(); 
        
        $('#modalBuscarProducto').modal('hide');
    });
   
    // --- LÓGICA PARA AÑADIR PRODUCTO A LA TABLA DE ITEMS DE COMPRA ---
    $('#btnAnadirProductoALista').on('click', function() {
        var idProducto = $('#temp_id_producto').val();
        var codigoProducto = $('#temp_codigo_producto').val();
        var nombreProducto = $('#temp_nombre_producto').val();
        
        // VALIDACIÓN ESTRICTA: CANTIDAD DEBE SER ENTERO ABSOLUTO
        var cantidadInput = $('#temp_cantidad').val();
        var cantidad = parseInt(cantidadInput, 10);
        
        // Verificar que sea un entero válido y positivo
        if (!Number.isInteger(Number(cantidadInput)) || cantidad <= 0 || cantidadInput.includes('.')) {
            Swal.fire('Atención', 'La cantidad debe ser un número entero positivo (sin decimales).', 'warning'); 
            $('#temp_cantidad').focus().select(); 
            return;
        }
        
        var precioCompra = parseFloat($('#temp_precio_compra').val()) || 0;
        var porcentajeIva = parseFloat($('#temp_porcentaje_iva').val()) || 0;

        if (!idProducto) {
            Swal.fire('Atención', 'Debe seleccionar un producto.', 'warning'); return;
        }
        if (precioCompra < 0) { 
            Swal.fire('Atención', 'El precio de compra no puede ser negativo.', 'warning'); $('#temp_precio_compra').focus(); return;
        }
         if (porcentajeIva < 0) {
            Swal.fire('Atención', 'El porcentaje de IVA no puede ser negativo.', 'warning'); $('#temp_porcentaje_iva').focus(); return;
        }

        var yaEnLista = false;
        $('#tablaItemsCompra tbody tr').not('#filaNoItems').each(function() {
            if ($(this).find('input[name="item_id_producto[]"]').val() == idProducto) {
                yaEnLista = true; return false; 
            }
        });
        if (yaEnLista) {
            Swal.fire('Atención', 'Este producto ya está en la lista. Puede modificar la cantidad allí o eliminarlo y volver a añadirlo.', 'warning');
            return;
        }

        contadorItemsCompra++;
        var subtotalItem = cantidad * precioCompra;
        var montoIvaItem = subtotalItem * (porcentajeIva / 100);
        var totalItem = subtotalItem + montoIvaItem;

        var nuevaFila = `
            <tr>
                <td>${contadorItemsCompra}</td>
                <td>${codigoProducto}
                    <input type="hidden" name="item_id_producto[]" value="${idProducto}">
                    <input type="hidden" name="item_codigo_producto[]" value="${codigoProducto}">
                    <input type="hidden" name="item_nombre_producto[]" value="${nombreProducto}">
                </td>
                <td>${nombreProducto}</td>
                <td><input type="number" name="item_cantidad[]" class="form-control form-control-sm item-cantidad" value="${cantidad}" min="1" step="1" style="width:70px;"></td>
                <td><input type="number" name="item_precio_unitario[]" class="form-control form-control-sm item-precio" step="0.01" value="${precioCompra.toFixed(2)}" min="0" style="width:90px;"></td>
                <td><input type="number" name="item_porcentaje_iva[]" class="form-control form-control-sm item-iva" step="0.01" value="${porcentajeIva.toFixed(2)}" min="0" style="width:60px;"></td>
                <td class="item-subtotal">${subtotalItem.toFixed(2)}</td>
                <td class="item-monto-iva">${montoIvaItem.toFixed(2)}</td>
                <td class="item-total">${totalItem.toFixed(2)}</td>
                <td><button type="button" class="btn btn-danger btn-sm btn-eliminar-item"><i class="fas fa-trash"></i></button></td>
            </tr>
        `;
        
        $('#filaNoItems').hide(); 
        $('#tablaItemsCompra tbody').append(nuevaFila);
        recalcularTotalesGenerales();

        // Limpiar campos temporales
        $('#temp_id_producto, #temp_codigo_producto, #temp_nombre_producto, #temp_iva_predeterminado_producto, #temp_precio_compra_sugerido_producto, #temp_stock_actual_producto').val('');
        $('#temp_cantidad').val(1);
        $('#temp_precio_compra').val('');
        $('#temp_porcentaje_iva').val(0);
        $('#temp_producto_info').hide().empty();
        $('#temp_nombre_producto').focus(); 
    });

    // --- VALIDACIÓN ESTRICTA EN TIEMPO REAL PARA CANTIDADES ENTERAS ---
    $(document).on('input change', '.item-cantidad, #temp_cantidad', function() {
        var valor = $(this).val();
        var valorEntero = parseInt(valor, 10);
        
        // Si no es un entero válido o tiene decimales, corregir
        if (!Number.isInteger(Number(valor)) || valor.includes('.') || valorEntero <= 0) {
            if (valorEntero > 0) {
                $(this).val(valorEntero); // Quitar decimales si los hay
            } else {
                $(this).val(1); // Valor mínimo
            }
        }
        
        // Si estamos en la tabla, recalcular
        if ($(this).hasClass('item-cantidad')) {
            var fila = $(this).closest('tr');
            var cantidad = parseInt($(this).val()) || 1;
            var precio = parseFloat(fila.find('.item-precio').val()) || 0;
            var ivaPct = parseFloat(fila.find('.item-iva').val()) || 0;

            var subtotal = cantidad * precio;
            var montoIva = subtotal * (ivaPct / 100);
            var total = subtotal + montoIva;

            fila.find('.item-subtotal').text(subtotal.toFixed(2));
            fila.find('.item-monto-iva').text(montoIva.toFixed(2));
            fila.find('.item-total').text(total.toFixed(2));
            
            recalcularTotalesGenerales();
        }
    });

    // --- RECALCULAR TOTALES DE UN ITEM SI SE MODIFICA EN LA TABLA ---
    $('#tablaItemsCompra tbody').on('change keyup', '.item-precio, .item-iva', function() {
        var fila = $(this).closest('tr');
        var cantidad = parseInt(fila.find('.item-cantidad').val()) || 1; // ENTERO ESTRICTO
        var precio = parseFloat(fila.find('.item-precio').val()) || 0;
        var ivaPct = parseFloat(fila.find('.item-iva').val()) || 0;

        var subtotal = cantidad * precio;
        var montoIva = subtotal * (ivaPct / 100);
        var total = subtotal + montoIva;

        fila.find('.item-subtotal').text(subtotal.toFixed(2));
        fila.find('.item-monto-iva').text(montoIva.toFixed(2));
        fila.find('.item-total').text(total.toFixed(2));
        
        recalcularTotalesGenerales();
    });
    
    // --- ELIMINAR ITEM DE LA LISTA ---
    $('#tablaItemsCompra tbody').on('click', '.btn-eliminar-item', function() {
        $(this).closest('tr').remove();
        recalcularTotalesGenerales();
        if ($('#tablaItemsCompra tbody tr').not('#filaNoItems').length === 0) {
            $('#filaNoItems').show();
            contadorItemsCompra = 0; 
        } else {
            var count = 1;
            $('#tablaItemsCompra tbody tr').not('#filaNoItems').each(function(){
                $(this).find('td:first').text(count++);
            });
            contadorItemsCompra = count -1;
        }
    });

    // --- RECALCULAR TOTALES GENERALES DE LA COMPRA ---
    function recalcularTotalesGenerales() {
        var subtotalGeneral = 0;
        var montoIvaGeneral = 0;
        var totalGeneral = 0;

        $('#tablaItemsCompra tbody tr').not('#filaNoItems').each(function() {
            var fila = $(this);
            var cantidad = parseInt(fila.find('.item-cantidad').val()) || 1; // ENTERO ESTRICTO
            var precio = parseFloat(fila.find('.item-precio').val()) || 0;
            var ivaPct = parseFloat(fila.find('.item-iva').val()) || 0;

            var subtotalItem = cantidad * precio;
            var montoIvaItem = subtotalItem * (ivaPct / 100);
            
            subtotalGeneral += subtotalItem;
            montoIvaGeneral += montoIvaItem;
        });
        totalGeneral = subtotalGeneral + montoIvaGeneral;

        $('#subtotal_general_compra_display').text(subtotalGeneral.toFixed(2));
        $('#monto_iva_general_display').text(montoIvaGeneral.toFixed(2));
        $('#total_general_compra_display').text(totalGeneral.toFixed(2));

        $('#subtotal_general_compra_hidden').val(subtotalGeneral.toFixed(2));
        $('#monto_iva_general_hidden').val(montoIvaGeneral.toFixed(2));
        $('#total_general_compra_hidden').val(totalGeneral.toFixed(2));
    }

    // --- LÓGICA PARA PROVEEDORES (MODAL Y FORMULARIO) CON AISLAMIENTO ---
    $('#modalBuscarProveedor').on('shown.bs.modal', function () {
        if (!$.fn.DataTable.isDataTable('#tablaProveedores')) {
            tablaProveedores = $('#tablaProveedores').DataTable({
                "processing": true, "serverSide": true,
                "ajax": {
                    "url": "<?php echo $URL; ?>/app/controllers/proveedores/controller_proveedores_serverside.php", 
                    "type": "POST",
                    "data": function(d) {
                        d.id_usuario = idUsuarioActual; // AISLAMIENTO DE USUARIO
                    }
                },
                "columns": [
                    { "data": "id_proveedor", "title": "ID" }, { "data": "nombre_proveedor", "title": "Nombre" },
                    { "data": "empresa", "title": "Empresa" }, { "data": "celular", "title": "Celular" },
                    { "data": "telefono", "title": "Teléfono" }, { "data": "email", "title": "Email" },
                    { "data": "direccion", "title": "Dirección" },
                    { "data": null, "title": "Acción", "orderable": false, "searchable": false,
                        "render": function (data, type, row) {
                            return `<button type="button" class="btn btn-success btn-sm seleccionar-proveedor" 
                                data-id="${row.id_proveedor}" data-nombre="${row.nombre_proveedor}"
                                data-empresa="${row.empresa || 'N/A'}" data-celular="${row.celular || 'N/A'}">
                                <i class="fas fa-check-circle"></i> Seleccionar</button>`;
                        }
                    }
                ],
                "language": {"url": "<?php echo $URL;?>/public/templeates/AdminLTE-3.2.0/plugins/datatables-plugins/i18n/es_es.json"},
                "responsive": true, "lengthChange": true, "autoWidth": false, "pageLength": 5, "lengthMenu": [5, 10, 25, 50]
            });
        } else {
            tablaProveedores.ajax.reload(null, false); 
        }
    });

    $('#tablaProveedores tbody').on('click', '.seleccionar-proveedor', function () {
        $('#id_proveedor_compra').val($(this).data('id'));
        $('#nombre_proveedor_compra_display').val($(this).data('nombre'));
        $('#info_empresa_proveedor').text($(this).data('empresa'));
        $('#info_celular_proveedor').text($(this).data('celular'));
        $('#detalle_proveedor_seleccionado').show();
        $('#modalBuscarProveedor').modal('hide');
    });

    $('#formNuevoProveedor').on('submit', function(e) {
        e.preventDefault();
        var formData = $(this).serialize(); 
        $.ajax({
            url: '<?php echo $URL; ?>/app/controllers/proveedores/create.php',
            type: 'POST', data: formData, dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    Swal.fire('¡Éxito!', response.message || 'Proveedor creado.', 'success');
                    if(response.data && response.data.id_proveedor) { 
                         $('#id_proveedor_compra').val(response.data.id_proveedor);
                        $('#nombre_proveedor_compra_display').val(response.data.nombre_proveedor);
                        $('#info_empresa_proveedor').text(response.data.empresa || 'N/A');
                        $('#info_celular_proveedor').text(response.data.celular || 'N/A');
                        $('#detalle_proveedor_seleccionado').show();
                    } else if (response.creadoId) { 
                         $('#id_proveedor_compra').val(response.creadoId);
                         $('#nombre_proveedor_compra_display').val( $('#nuevo_proveedor_nombre').val());
                         $('#info_empresa_proveedor').text( $('#nuevo_proveedor_empresa').val() || 'N/A');
                         $('#info_celular_proveedor').text( $('#nuevo_proveedor_celular').val() || 'N/A');
                         $('#detalle_proveedor_seleccionado').show();
                    }
                    $('#modalBuscarProveedor').modal('hide');
                    $('#formNuevoProveedor')[0].reset();
                    if(tablaProveedores) tablaProveedores.ajax.reload(null, false);
                } else {
                    Swal.fire('Error', response.message || 'No se pudo crear.', 'error');
                }
            },
            error: function() { 
                Swal.fire('Error de Conexión', 'No se pudo conectar.', 'error');
            }
        });
    });
    
    // --- VALIDACIÓN DEL FORMULARIO PRINCIPAL ---
    $('#formEditarCompra').on('submit', function(e){
        if (!$('#id_proveedor_compra').val()) { 
            e.preventDefault(); Swal.fire('Atención', 'Debe seleccionar un proveedor.', 'warning'); return false;
        }
        if ($('#tablaItemsCompra tbody tr').not('#filaNoItems').length === 0) {
            e.preventDefault(); Swal.fire('Atención', 'Debe tener al menos un producto en la compra.', 'warning'); return false;
        }
        
        // VALIDACIÓN ESTRICTA: TODAS LAS CANTIDADES DEBEN SER ENTEROS POSITIVOS
        var itemsValidos = true;
        $('#tablaItemsCompra tbody tr').not('#filaNoItems').each(function() {
            var cantidadInput = $(this).find('.item-cantidad').val();
            var cantidad = parseInt(cantidadInput, 10);
            
            if (!Number.isInteger(Number(cantidadInput)) || cantidad <= 0 || cantidadInput.includes('.')) {
                itemsValidos = false;
                Swal.fire('Atención', 'Todas las cantidades deben ser números enteros positivos (sin decimales).', 'warning');
                $(this).find('.item-cantidad').focus().select();
                return false; 
            }
        });
        if(!itemsValidos) {
            e.preventDefault();
            return false;
        }
        
        $('#btn_actualizar_compra').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Actualizando...');
    });

    // Calcular totales iniciales
    recalcularTotalesGenerales();
});
</script>