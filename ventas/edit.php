<?php
include '../app/config.php'; // $URL, $pdo, $fechaHora
include '../layout/sesion.php'; // Verifica sesión, establece $id_usuario_sesion, etc.


// Incluir modelos necesarios
require_once __DIR__ . '/../app/models/VentasModel.php';
require_once __DIR__ . '/../app/models/CategoriaModel.php';
require_once __DIR__ . '/../app/models/AlmacenModel.php'; // Añadido para obtener stock actual

// Verificar si se proporcionó un ID de venta
if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    $_SESSION['mensaje'] = "Error: ID de venta no válido.";
    $_SESSION['icono'] = "error";
    header('Location: ' . $URL . '/ventas/');
    exit;
}

$id_venta_get = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
$venta_info = null;
$detalles_venta = [];

if ($id_usuario_sesion) {
    try {
        $ventasModel = new VentasModel($pdo, $id_usuario_sesion);
        $almacenModel = new AlmacenModel($pdo); // Para obtener stock actual
        $venta_info = $ventasModel->getVentaById($id_venta_get);
        
        if ($venta_info) {
            // Verificar que la venta se pueda editar (solo estado PENDIENTE)
            if (strtoupper($venta_info['estado_venta']) !== 'PENDIENTE') {
                $_SESSION['mensaje'] = "Solo se pueden editar ventas en estado PENDIENTE. Estado actual: " . $venta_info['estado_venta'];
                $_SESSION['icono'] = "warning";
                header('Location: ' . $URL . '/ventas/show.php?id=' . $id_venta_get);
                exit;
            }
            
            $detalles_venta = $ventasModel->getDetallesVentaById($id_venta_get);
            
            // Obtener stock actual de cada producto
            foreach ($detalles_venta as $key => $detalle) {
                $producto = $almacenModel->getProductoByIdAndUsuarioId($detalle['id_producto'], $id_usuario_sesion);
                if ($producto) {
                    // Sumar el stock actual más la cantidad que ya estaba vendida para cálculo correcto
                    $detalles_venta[$key]['stock_actual'] = $producto['stock'] + floatval($detalle['cantidad']);
                } else {
                    $detalles_venta[$key]['stock_actual'] = floatval($detalle['cantidad']);
                }
            }
        } else {
            $_SESSION['mensaje'] = "Venta no encontrada o no tiene permiso para editarla.";
            $_SESSION['icono'] = "warning";
            header('Location: ' . $URL . '/ventas/');
            exit;
        }
    } catch (Exception $e) {
        $_SESSION['mensaje'] = "Error al cargar datos de la venta: " . $e->getMessage();
        $_SESSION['icono'] = "error";
        header('Location: ' . $URL . '/ventas/');
        exit;
    }
} else {
    $_SESSION['mensaje'] = "Error: Sesión de usuario no encontrada.";
    $_SESSION['icono'] = "error";
    header('Location: ' . $URL . '/login/');
    exit;
}

// Necesitaremos las categorías para el modal de creación rápida de productos
$categoriaModel = new CategoriaModel($pdo);
$categorias_select_datos = [];
if (isset($id_usuario_sesion)) {
    $categorias_select_datos = $categoriaModel->getCategoriasByUsuarioId($id_usuario_sesion);
}

// Para mostrar mensajes flash (SweetAlert)
include '../layout/mensajes.php';
include '../layout/parte1.php'; // Cabecera HTML, CSS, jQuery, y menú lateral
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">
                        <i class="fas fa-edit"></i> Editar Venta: 
                        <?php echo htmlspecialchars($venta_info && $venta_info['codigo_venta_referencia'] ? $venta_info['codigo_venta_referencia'] : 'N/A'); ?>
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?php echo $URL; ?>/">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo $URL; ?>/ventas/">Ventas</a></li>
                        <li class="breadcrumb-item active">Editar Venta</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <form id="form-editar-venta" method="POST" action="<?php echo $URL; ?>/app/controllers/ventas/controller_update_venta.php">
                <input type="hidden" id="id_venta" name="id_venta" value="<?php echo $id_venta_get; ?>">
                <input type="hidden" id="nro_venta_secuencial" name="nro_venta_secuencial" value="<?php echo htmlspecialchars($venta_info['nro_venta_secuencial']); ?>">
                <!-- Campo oculto para forzar la actualización del estado -->
                <input type="hidden" id="actualizar_estado" name="actualizar_estado" value="1">
                
                <div class="row">
                    <!-- Columna Izquierda: Datos de la Venta y Cliente -->
                    <div class="col-md-9">
                        <div class="card card-warning card-outline">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-file-invoice-dollar"></i> Datos de la Venta</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="nro_venta_referencia">Nro. Venta (Ref.)</label>
                                            <input type="text" class="form-control" id="nro_venta_referencia" name="nro_venta_referencia" 
                                                   value="<?php echo htmlspecialchars($venta_info['codigo_venta_referencia']); ?>" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="fecha_venta">Fecha de Venta <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control" id="fecha_venta" name="fecha_venta" 
                                                   value="<?php echo htmlspecialchars($venta_info['fecha_venta']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="tipo_comprobante_venta">Tipo Comprobante</label>
                                            <select class="form-control" id="tipo_comprobante_venta" name="tipo_comprobante_venta">
                                                <option value="NOTA DE VENTA" <?php echo ($venta_info['tipo_comprobante'] == 'NOTA DE VENTA') ? 'selected' : ''; ?>>NOTA DE VENTA</option>
                                                <option value="FACTURA" <?php echo ($venta_info['tipo_comprobante'] == 'FACTURA') ? 'selected' : ''; ?>>FACTURA</option>
                                                <option value="TICKET" <?php echo ($venta_info['tipo_comprobante'] == 'TICKET') ? 'selected' : ''; ?>>TICKET</option>
                                                <option value="OTRO" <?php echo ($venta_info['tipo_comprobante'] == 'OTRO') ? 'selected' : ''; ?>>OTRO</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="nro_comprobante_fisico_venta">Nro. Comprobante Físico</label>
                                            <input type="text" class="form-control" id="nro_comprobante_fisico_venta" name="nro_comprobante_fisico_venta" 
                                                   value="<?php echo htmlspecialchars($venta_info['nro_comprobante_fisico']); ?>" placeholder="Ej: 001-001-123456789">
                                        </div>
                                    </div>
                                </div>

                                <hr>
                                <h5 class="mt-2 mb-2"><i class="fas fa-user-tag"></i> Datos del Cliente</h5>
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="form-group">
                                            <label>Cliente <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <input type="text" class="form-control" id="display_nombre_cliente_venta" 
                                                       value="<?php echo htmlspecialchars($venta_info['nombre_cliente']); ?>" readonly>
                                                <input type="hidden" id="id_cliente_venta" name="id_cliente_venta" value="<?php echo $venta_info['id_cliente']; ?>">
                                                <div class="input-group-append">
                                                    <button class="btn btn-warning" type="button" id="btn-gestionar-cliente-venta" title="Cambiar Cliente">
                                                        <i class="fas fa-users"></i> Cambiar Cliente
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div id="info_extra_cliente_venta" class="pt-4">
                                            <small id="display_documento_cliente_venta" class="form-text text-muted">
                                                <?php if (!empty($venta_info['nit_ci_cliente'])): ?>
                                                    <b>Doc:</b> <?php echo htmlspecialchars($venta_info['nit_ci_cliente']); ?>
                                                <?php else: ?>
                                                    <i>Cliente seleccionado.</i>
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card card-info card-outline mt-3">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-boxes"></i> Productos de la Venta</h3>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-sm btn-success" id="btn-modal-buscar-producto-venta">
                                        <i class="fas fa-search-plus"></i> Buscar y Añadir Producto
                                    </button>
                                    <button type="button" class="btn btn-sm btn-warning" id="btn-modal-crear-producto-rapido-venta">
                                        <i class="fas fa-plus-circle"></i> Crear Producto Rápido
                                    </button>
                                </div>
                            </div>
                            <div class="card-body table-responsive p-0" style="min-height: 200px;">
                                <table class="table table-sm table-hover table-striped" id="tabla_items_venta">
                                    <thead class="thead-light">
                                        <tr>
                                            <th style="width: 5%;"><i class="fas fa-trash-alt"></i></th>
                                            <th style="width: 10%;">Código</th>
                                            <th>Producto</th>
                                            <th style="width: 10%;">Stock Disp.</th>
                                            <th style="width: 12%;">Cantidad <span class="text-danger">*</span></th>
                                            <th style="width: 12%;">P. Venta Unit. <span class="text-danger">*</span></th>
                                            <th style="width: 10%;">% IVA</th>
                                            <th style="width: 15%;">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Los productos se cargarán dinámicamente -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="form-group mt-3">
                            <label for="observaciones_venta">Observaciones Adicionales</label>
                            <textarea class="form-control" id="observaciones_venta" name="observaciones_venta" rows="2" 
                                      placeholder="Cualquier nota relevante para esta venta..."><?php echo htmlspecialchars($venta_info['observaciones']); ?></textarea>
                        </div>
                    </div>

                    <!-- Columna Derecha: Totales y Acciones -->
                    <div class="col-md-3">
                        <div class="card card-warning card-outline">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-calculator"></i> Resumen y Totales</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group row mb-1">
                                    <label for="subtotal_general_venta_display" class="col-sm-6 col-form-label col-form-label-sm">Subtotal:</label>
                                    <div class="col-sm-6">
                                        <input type="text" class="form-control form-control-sm text-right" id="subtotal_general_venta_display" readonly value="0.00">
                                        <input type="hidden" id="subtotal_general_venta_calculado" name="subtotal_general_venta_calculado" value="0.00">
                                    </div>
                                </div>
                                <div class="form-group row mb-1">
                                    <label for="monto_iva_general_venta_display" class="col-sm-6 col-form-label col-form-label-sm">IVA Total:</label>
                                    <div class="col-sm-6">
                                        <input type="text" class="form-control form-control-sm text-right" id="monto_iva_general_venta_display" readonly value="0.00">
                                        <input type="hidden" id="monto_iva_general_venta_calculado" name="monto_iva_general_venta_calculado" value="0.00">
                                    </div>
                                </div>
                                <!-- Selector de Estado de Venta - Modificado para ser más visible -->
                                <div class="form-group row mb-2">
                                    <label for="estado_venta" class="col-sm-6 col-form-label font-weight-bold">Estado de Venta:</label>
                                    <div class="col-sm-6">
                                        <select class="form-control text-center" id="estado_venta" name="estado_venta" style="font-weight:bold; border: 2px solid #17a2b8;">
                                            <option value="PENDIENTE" <?php echo ($venta_info['estado_venta'] == 'PENDIENTE') ? 'selected' : ''; ?>>PENDIENTE</option>
                                            <option value="PAGADA" <?php echo ($venta_info['estado_venta'] == 'PAGADA') ? 'selected' : ''; ?>>PAGADA</option>
                                            <option value="ENTREGADA" <?php echo ($venta_info['estado_venta'] == 'ENTREGADA') ? 'selected' : ''; ?>>ENTREGADA</option>
                                            <option value="ANULADA" <?php echo ($venta_info['estado_venta'] == 'ANULADA') ? 'selected' : ''; ?>>ANULADA</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row mb-1">
                                    <label for="descuento_general_venta" class="col-sm-6 col-form-label col-form-label-sm">Descuento (-):</label>
                                    <div class="col-sm-6">
                                        <input type="number" class="form-control form-control-sm text-right" id="descuento_general_venta" name="descuento_general_venta" 
                                               value="<?php echo number_format(floatval($venta_info['descuento_general']), 2); ?>" step="0.01" min="0">
                                    </div>
                                </div>
                                <hr>
                                <div class="form-group row mb-2">
                                    <label for="total_general_venta_display" class="col-sm-5 col-form-label col-form-label font-weight-bold">TOTAL:</label>
                                    <div class="col-sm-7">
                                        <input type="text" class="form-control form-control text-right font-weight-bold" id="total_general_venta_display" readonly value="0.00" style="font-size: 1.2rem;">
                                        <input type="hidden" id="total_general_venta_calculado" name="total_general_venta_calculado" value="0.00">
                                    </div>
                                </div>
                                <hr>
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-warning btn-block" id="btn-actualizar-venta">
                                        <i class="fas fa-save"></i> Actualizar Venta
                                    </button>
                                    <!-- Botón para Validar Venta -->
                                    <button type="button" class="btn btn-success btn-block mt-2" id="btn-validar-venta">
                                        <i class="fas fa-check-circle"></i> Validar y Guardar
                                    </button>
                                    <a href="<?php echo $URL; ?>/ventas/show.php?id=<?php echo $id_venta_get; ?>" class="btn btn-info btn-block mt-2">
                                        <i class="fas fa-eye"></i> Ver Venta
                                    </a>
                                    <a href="<?php echo $URL; ?>/ventas/" class="btn btn-secondary btn-block mt-2">
                                        <i class="fas fa-ban"></i> Cancelar y Volver
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modales -->
<!-- Modal para Gestionar Cliente -->
<div class="modal fade" id="modal-gestionar-cliente-venta" tabindex="-1" aria-labelledby="modalGestionarClienteLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="modalGestionarClienteLabel"><i class="fas fa-users"></i> Cambiar Cliente para la Venta</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <h5><i class="fas fa-search"></i> Buscar Cliente Existente</h5>
                <div class="table-responsive mb-4">
                    <table id="tabla_buscar_clientes_gestion_dt" class="table table-bordered table-striped table-hover table-sm" style="width:100%;">
                        <thead class="thead-light">
                            <tr>
                                <th style="display:none;">ID</th>
                                <th>Nombre / Razón Social</th>
                                <th>Tipo Doc.</th>
                                <th>Nro. Documento</th>
                                <th>Celular</th>
                                <th>Email</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                <hr>
                <div class="text-center">
                    <p>¿No encuentra al cliente?</p>
                    <button type="button" class="btn btn-success" id="btn-abrir-modal-creacion-rapida-cliente-desde-gestion">
                        <i class="fas fa-user-plus"></i> Crear Nuevo Cliente Rápidamente
                    </button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Crear Cliente Rápido -->
<div class="modal fade" id="modal-crear-cliente-directo-venta" tabindex="-1" aria-labelledby="modalCrearClienteDirectoVentaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modalCrearClienteDirectoVentaLabel"><i class="fas fa-user-plus"></i> Registrar Nuevo Cliente</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <form id="form-crear-cliente-directo-venta" method="POST">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-7">
                            <div class="form-group">
                                <label for="dv_nombre_cliente">Nombre Completo / Razón Social <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="dv_nombre_cliente" name="nombre_cliente" required>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="dv_tipo_documento">Tipo Documento <span class="text-danger">*</span></label>
                                <select class="form-control" id="dv_tipo_documento" name="tipo_documento" required>
                                    <option value="consumidor_final" selected>CONSUMIDOR FINAL</option>
                                    <option value="cedula">CÉDULA</option>
                                    <option value="ruc">RUC</option>
                                    <option value="pasaporte">PASAPORTE</option>
                                    <option value="extranjero">EXTRANJERO</option>
                                    <option value="otro">OTRO</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="dv_nit_ci_cliente" id="label_dv_documento">Nro. Documento</label>
                                <input type="text" class="form-control" id="dv_nit_ci_cliente" name="nit_ci_cliente">
                                <small id="dv_documento_help" class="form-text text-muted"></small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="dv_celular_cliente">Celular</label>
                                <input type="text" class="form-control" id="dv_celular_cliente" name="celular_cliente">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="dv_estado">Estado</label>
                                <select class="form-control" id="dv_estado" name="estado">
                                    <option value="activo" selected>Activo</option>
                                    <option value="inactivo">Inactivo</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="dv_email_cliente">Correo Electrónico</label>
                        <input type="email" class="form-control" id="dv_email_cliente" name="email_cliente">
                    </div>
                    <div id="dv_validation_errors" class="alert alert-danger" style="display: none;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-info"><i class="fas fa-save"></i> Guardar Cliente</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Buscar Productos -->
<div class="modal fade" id="modal-buscar-producto-venta" tabindex="-1" aria-labelledby="modalBuscarProductoVentaLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalBuscarProductoVentaLabel"><i class="fas fa-search"></i> Buscar Productos en Almacén</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table id="tabla_buscar_productos_venta_dt" class="table table-bordered table-striped table-hover table-sm" style="width:100%;">
                        <thead class="thead-light">
                            <tr>
                                <th>ID</th>
                                <th>Código</th>
                                <th>Nombre</th>
                                <th>Stock</th>
                                <th>P. Venta</th>
                                <th>% IVA Prod.</th>
                                <th>Categoría</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Crear Producto Rápido -->
<div class="modal fade" id="modal-crear-producto-rapido-venta" tabindex="-1" aria-labelledby="modalCrearProductoRapidoVentaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="modalCrearProductoRapidoVentaLabel"><i class="fas fa-plus-circle"></i> Crear Nuevo Producto (Rápido)</h5>
                <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            </div>
            <form id="form-crear-producto-rapido-venta" method="POST">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="vr_producto_codigo">Código Producto</label>
                                <input type="text" class="form-control" id="vr_producto_codigo" name="producto_codigo" readonly>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <div class="form-group">
                                <label for="vr_producto_nombre">Nombre Producto <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="vr_producto_nombre" name="producto_nombre" required>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="vr_producto_descripcion">Descripción</label>
                        <textarea class="form-control" id="vr_producto_descripcion" name="producto_descripcion" rows="2"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="vr_producto_id_categoria">Categoría <span class="text-danger">*</span></label>
                                <select class="form-control" id="vr_producto_id_categoria" name="producto_id_categoria" required>
                                    <option value="">Seleccione...</option>
                                    <?php if (!empty($categorias_select_datos)): ?>
                                        <?php foreach ($categorias_select_datos as $categoria): ?>
                                            <option value="<?php echo $categoria['id_categoria']; ?>"><?php echo htmlspecialchars($categoria['nombre_categoria']); ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="vr_producto_fecha_ingreso">Fecha Ingreso <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="vr_producto_fecha_ingreso" name="producto_fecha_ingreso" value="<?php echo date('Y-m-d'); ?>" required>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="vr_producto_precio_compra">Precio Compra <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="vr_producto_precio_compra" name="producto_precio_compra" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="vr_producto_precio_venta">Precio Venta <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="vr_producto_precio_venta" name="producto_precio_venta" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                           <div class="form-group">
                                <label for="vr_producto_iva_predeterminado">IVA Predeterminado (%) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="vr_producto_iva_predeterminado" name="producto_iva_predeterminado" step="0.01" min="0" value="12.00" required>
                                <small class="form-text text-muted">Ej: 12 para 12%, 0 si no aplica.</small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="vr_producto_stock_inicial">Stock Inicial <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="vr_producto_stock_inicial" name="producto_stock_inicial" min="1" value="1" required>
                                <small class="form-text text-muted">Cantidad inicial en inventario para la venta.</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="vr_producto_stock_minimo">Stock Mínimo</label>
                                <input type="number" class="form-control" id="vr_producto_stock_minimo" name="producto_stock_minimo" min="0">
                                <small class="form-text text-muted">Alerta cuando llegue a este nivel.</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="vr_producto_stock_maximo">Stock Máximo</label>
                                <input type="number" class="form-control" id="vr_producto_stock_maximo" name="producto_stock_maximo" min="0">
                                <small class="form-text text-muted">Capacidad máxima de almacenamiento.</small>
                            </div>
                        </div>
                    </div>
                     <div id="vr_validation_errors" class="alert alert-danger" style="display: none;"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning text-dark"><i class="fas fa-save"></i> Crear Producto</button>
                </div>
            </form>
        </div>
    </div>
</div>




<?php include '../layout/parte2.php'; ?>

<script>
// Variable global para la tabla de items de venta
var itemsVenta = {}; // Almacenará los productos añadidos
var itemsOriginales = {}; // Para controlar cambios de stock

$(document).ready(function() {
    const URL_BASE = '<?php echo $URL; ?>';
    const ID_USUARIO_LOGUEADO = <?php echo isset($id_usuario_sesion) ? intval($id_usuario_sesion) : 'null'; ?>;
    const ID_VENTA_EDITAR = <?php echo $id_venta_get; ?>;
    
    if (ID_USUARIO_LOGUEADO === null) {
        Swal.fire('Error de Sesión', 'No se pudo identificar al usuario. Por favor, recargue la página o inicie sesión de nuevo.', 'error');
    }

    let tablaBuscarProductosVentaDT;
    let tablaBuscarClientesGestionDT;
    
    // Variable para controlar si se debe actualizar el estado a PAGADA
    let actualizarAPagada = false;

    // Cargar datos existentes de la venta
    cargarDatosVentaExistente();

    // Evento para el botón de validar venta - MODIFICADO
    $('#btn-validar-venta').click(function(e) {
        e.preventDefault(); // Prevenir comportamiento por defecto
        
        // Mostrar mensaje de confirmación
        Swal.fire({
            title: '¿Validar esta venta?',
            text: "Esto cambiará el estado de la venta a PAGADA.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, validar venta',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Marcar que queremos actualizar a PAGADA
                actualizarAPagada = true;
                
                // Establecer estado a PAGADA
                $('#estado_venta').val('PAGADA');
                
                // Crear y enviar una solicitud AJAX directamente
                enviarFormularioActualizacion();
            }
        });
    });

    // Función para enviar el formulario de actualización
    function enviarFormularioActualizacion() {
        $('#btn-actualizar-venta').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Actualizando...');
        
        if (Object.keys(itemsVenta).length === 0) {
            Swal.fire('Error', 'Debe tener al menos un producto en la venta.', 'error');
            $('#btn-actualizar-venta').prop('disabled', false).html('<i class="fas fa-save"></i> Actualizar Venta'); 
            return;
        }
        if (!$('#id_cliente_venta').val()) {
             Swal.fire('Error', 'Debe seleccionar un cliente.', 'error');
            $('#btn-actualizar-venta').prop('disabled', false).html('<i class="fas fa-save"></i> Actualizar Venta'); 
            return;
        }
        
        // Recopilar datos del formulario
        var formData = new FormData(document.getElementById('form-editar-venta'));
        
        // Asegurar que el estado se envía correctamente
        formData.set('estado_venta', $('#estado_venta').val());
        
        // Si estamos actualizando a PAGADA, forzar el valor
        if (actualizarAPagada) {
            formData.set('estado_venta', 'PAGADA');
            formData.append('validar_venta', 'true');
        }
        
        // Añadir los productos
        let itemIndex = 0;
        for (const idProducto in itemsVenta) {
            const item = itemsVenta[idProducto];
            formData.append(`items[${itemIndex}][id_producto]`, item.id);
            formData.append(`items[${itemIndex}][cantidad]`, item.cantidad);
            formData.append(`items[${itemIndex}][precio_venta_unitario]`, item.precio_venta_unitario);
            formData.append(`items[${itemIndex}][porcentaje_iva_item]`, item.porcentaje_iva);
            itemIndex++;
        }
        
        // Enviar mediante Fetch API (más moderno y controlable)
        fetch(`${URL_BASE}/app/controllers/ventas/controller_update_venta.php`, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            console.log("Respuesta del servidor:", data);
            if (data.status === 'success') {
                Swal.fire({
                    title: actualizarAPagada ? '¡Venta Validada!' : '¡Venta Actualizada!', 
                    text: data.message, 
                    icon: 'success', 
                    confirmButtonText: 'Aceptar'
                }).then(() => { 
                    // Si la actualización fue exitosa pero el estado no cambió, hacemos una segunda petición
                    if (actualizarAPagada) {
                        actualizarEstadoDirectamente(ID_VENTA_EDITAR);
                    } else {
                        window.location.href = `${URL_BASE}/ventas/show.php?id=${ID_VENTA_EDITAR}`;
                    }
                });
            } else {
                Swal.fire('Error', data.message || 'No se pudo actualizar la venta.', 'error');
                $('#btn-actualizar-venta').prop('disabled', false).html('<i class="fas fa-save"></i> Actualizar Venta');
            }
        })
        .catch(error => {
            console.error("Error en la petición:", error);
            Swal.fire('Error de Servidor', 'No se pudo contactar al servidor.', 'error');
            $('#btn-actualizar-venta').prop('disabled', false).html('<i class="fas fa-save"></i> Actualizar Venta');
        });
    }
    
    // Función para actualizar directamente el estado (segunda petición)
    function actualizarEstadoDirectamente(idVenta) {
        // Crear una petición específica para actualizar el estado
        fetch(`${URL_BASE}/app/controllers/ventas/actualizar_estado_venta.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id_venta=${idVenta}&estado_venta=PAGADA`
        })
        .then(response => response.json())
        .then(data => {
            console.log("Respuesta actualización estado:", data);
            // Redirigir independientemente del resultado
            window.location.href = `${URL_BASE}/ventas/show.php?id=${ID_VENTA_EDITAR}`;
        })
        .catch(error => {
            console.error("Error en actualización de estado:", error);
            // Redirigir de todos modos
            window.location.href = `${URL_BASE}/ventas/show.php?id=${ID_VENTA_EDITAR}`;
        });
    }

    function cargarDatosVentaExistente() {
        console.log("Cargando datos de la venta existente...");
        
        // Asegurarse de que se muestre el número de referencia de la venta
        console.log("Nro. Venta (Ref.):", '<?php echo addslashes($venta_info['codigo_venta_referencia']); ?>');
        $('#nro_venta_referencia').val('<?php echo addslashes($venta_info['codigo_venta_referencia']); ?>');
        
        // Asegurarse de que se seleccione el cliente correcto
        $('#id_cliente_venta').val('<?php echo $venta_info['id_cliente']; ?>');
        $('#display_nombre_cliente_venta').val('<?php echo addslashes($venta_info['nombre_cliente']); ?>');
        
        let docInfo = '<i>Cliente seleccionado.</i>';
        <?php if (!empty($venta_info['nit_ci_cliente'])): ?>
            docInfo = `<b>Doc:</b> <?php echo addslashes($venta_info['nit_ci_cliente']); ?>`;
        <?php endif; ?>
        $('#display_documento_cliente_venta').html(docInfo);
        
        // Cargar los items de la venta existente
        <?php if (!empty($detalles_venta)): ?>
            <?php foreach ($detalles_venta as $detalle): ?>
                itemsVenta[<?php echo $detalle['id_producto']; ?>] = {
                    id: <?php echo $detalle['id_producto']; ?>,
                    codigo: '<?php echo addslashes($detalle['codigo_producto']); ?>',
                    nombre: '<?php echo addslashes($detalle['nombre_producto']); ?>',
                    stock_disponible: parseFloat('<?php echo isset($detalle['stock_actual']) ? $detalle['stock_actual'] : $detalle['cantidad']; ?>'), // Stock actual + cantidad vendida
                    cantidad: parseInt('<?php echo $detalle['cantidad']; ?>'), // Forzar entero
                    precio_venta_unitario: parseFloat('<?php echo $detalle['precio_venta_unitario']; ?>'),
                    porcentaje_iva: parseFloat('<?php echo $detalle['porcentaje_iva_item']; ?>')
                };
                
                // Guardar items originales para control de stock
                itemsOriginales[<?php echo $detalle['id_producto']; ?>] = {
                    cantidad: parseInt('<?php echo $detalle['cantidad']; ?>') // Forzar entero
                };
                
                console.log("Producto cargado:", "<?php echo addslashes($detalle['nombre_producto']); ?>", 
                            "Cantidad:", parseInt('<?php echo $detalle['cantidad']; ?>'),
                            "Stock disponible:", parseFloat('<?php echo isset($detalle['stock_actual']) ? $detalle['stock_actual'] : $detalle['cantidad']; ?>'));
            <?php endforeach; ?>
        <?php endif; ?>
        
        renderizarTablaItemsVenta();
        calcularTotalesVenta();
    }

    function configurarCampoDocumento(idSelectTipoDoc, idInputDocumento, idHelpText, idLabelDocumento) {
        const selectTipoDoc = $('#' + idSelectTipoDoc);
        const inputDocumento = $('#' + idInputDocumento);
        const helpText = $('#' + idHelpText);
        const labelDocumento = $('#' + idLabelDocumento);

        function actualizarCampos() {
            const tipoSeleccionado = selectTipoDoc.val();
            inputDocumento.val('');
            switch (tipoSeleccionado) {
                case 'consumidor_final':
                    inputDocumento.prop('disabled', true).prop('required', false).attr('placeholder', 'N/A para Consumidor Final');
                    labelDocumento.text('Nro. Documento');
                    helpText.text('No se requiere número para Consumidor Final.');
                    break;
                case 'cedula':
                    inputDocumento.prop('disabled', false).prop('required', true).attr('placeholder', 'Ej: 0102030405 (10 dígitos)');
                    inputDocumento.attr('maxlength', 10).attr('pattern', '\\d{10}');
                    labelDocumento.text('Nro. Cédula *');
                    helpText.text('Ingrese los 10 dígitos de la cédula.');
                    break;
                case 'ruc':
                    inputDocumento.prop('disabled', false).prop('required', true).attr('placeholder', 'Ej: 0102030405001 (13 dígitos)');
                    inputDocumento.attr('maxlength', 13).attr('pattern', '\\d{13}');
                    labelDocumento.text('Nro. RUC *');
                    helpText.text('Ingrese los 13 dígitos del RUC.');
                    break;
                case 'pasaporte':
                    inputDocumento.prop('disabled', false).prop('required', true).attr('placeholder', 'Ej: A12345678');
                    inputDocumento.removeAttr('pattern').attr('maxlength', 20);
                    labelDocumento.text('Nro. Pasaporte *');
                    helpText.text('Ingrese el número de pasaporte.');
                    break;
                default:
                    inputDocumento.prop('disabled', false).prop('required', false).attr('placeholder', 'Ingrese identificación');
                    inputDocumento.removeAttr('pattern').attr('maxlength', 30);
                    labelDocumento.text('Nro. Documento');
                    helpText.text('Ingrese el documento de identificación correspondiente.');
            }
        }
        selectTipoDoc.on('change', actualizarCampos);
        actualizarCampos();
    }

    configurarCampoDocumento('dv_tipo_documento', 'dv_nit_ci_cliente', 'dv_documento_help', 'label_dv_documento');

    // --- GESTIÓN DE CLIENTES ---
    $('#btn-gestionar-cliente-venta').click(function() {
        $('#modal-gestionar-cliente-venta').modal('show');
        if ($.fn.DataTable.isDataTable('#tabla_buscar_clientes_gestion_dt')) {
            tablaBuscarClientesGestionDT.ajax.reload();
        } else {
            tablaBuscarClientesGestionDT = $('#tabla_buscar_clientes_gestion_dt').DataTable({
                processing: true, 
                serverSide: true,
                ajax: {
                    url: `${URL_BASE}/app/controllers/clientes/controller_listado_clientes_dt.php`,
                    type: 'POST',
                    error: function (jqXHR, textStatus, errorThrown) {
                        console.error("Error AJAX de DataTables:", textStatus, errorThrown);
                        console.error("Respuesta:", jqXHR.responseText);
                    }
                },
                columns: [
                    { data: 'id_cliente', visible: false },
                    { data: 'nombre_cliente' },
                    { data: 'tipo_documento', render: function(data){ return data ? data.replace(/_/g, ' ').toUpperCase() : 'N/A';} },
                    { data: 'nit_ci_cliente' },
                    { data: 'celular_cliente' },
                    { data: 'email_cliente' },
                    { data: null, orderable: false, searchable: false, className: 'text-center',
                        render: function(data, type, row) {
                            return `<button type="button" class="btn btn-xs btn-warning btn-seleccionar-cliente-gestion" title="Seleccionar este Cliente"><i class="fas fa-check-circle"></i> Seleccionar</button>`;
                        }
                    }
                ],
                language: { url: `${URL_BASE}/public/templeates/AdminLTE-3.2.0/plugins/datatables-plugins/i18n/es_es.json`},
                responsive: true, 
                pageLength: 5, 
                lengthChange: false,
            });
        }
    });

    $('#tabla_buscar_clientes_gestion_dt tbody').on('click', '.btn-seleccionar-cliente-gestion', function() {
        var data = tablaBuscarClientesGestionDT.row($(this).parents('tr')).data();
        if (data) {
            seleccionarClienteParaVenta(data);
            $('#modal-gestionar-cliente-venta').modal('hide');
        }
    });

    function seleccionarClienteParaVenta(clienteData) {
        $('#id_cliente_venta').val(clienteData.id_cliente);
        $('#display_nombre_cliente_venta').val(clienteData.nombre_cliente || 'Cliente no especificado');
        let docInfo = '<i>Cliente seleccionado.</i>';
        if (clienteData.nit_ci_cliente && clienteData.tipo_documento) {
            docInfo = `<b>Doc:</b> ${clienteData.tipo_documento.replace(/_/g, ' ').toUpperCase()}: ${clienteData.nit_ci_cliente}`;
        }
        $('#display_documento_cliente_venta').html(docInfo);
    }

    $('#btn-abrir-modal-creacion-rapida-cliente-desde-gestion').click(function() {
        $('#modal-gestionar-cliente-venta').modal('hide');
        $('#form-crear-cliente-directo-venta')[0].reset();
        $('#dv_validation_errors').hide().html('');
        configurarCampoDocumento('dv_tipo_documento', 'dv_nit_ci_cliente', 'dv_documento_help', 'label_dv_documento');
        $('#modal-crear-cliente-directo-venta').modal('show');
    });

    $('#form-crear-cliente-directo-venta').submit(function(e) {
        e.preventDefault();
        $('#dv_validation_errors').hide().html('');
        const formData = $(this).serialize();
        $.ajax({
            url: `${URL_BASE}/app/controllers/clientes/create_cliente.php`,
            type: 'POST', 
            data: formData, 
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#modal-crear-cliente-directo-venta').modal('hide');
                    Swal.fire('¡Éxito!', response.message, 'success');
                    if (response.cliente_id && response.data_cliente) {
                        seleccionarClienteParaVenta(response.data_cliente);
                    }
                } else {
                    $('#dv_validation_errors').html(response.message || 'Error desconocido.').show();
                }
            },
            error: function() { 
                $('#dv_validation_errors').html('Error de conexión.').show(); 
            }
        });
    });

    // --- PRODUCTOS ---
    $('#btn-modal-buscar-producto-venta').click(function() {
        if ($.fn.DataTable.isDataTable('#tabla_buscar_productos_venta_dt')) {
            tablaBuscarProductosVentaDT.ajax.reload();
        } else {
            tablaBuscarProductosVentaDT = $('#tabla_buscar_productos_venta_dt').DataTable({
                processing: true, 
                serverSide: true,
                ajax: {
                    url: `${URL_BASE}/app/controllers/almacen/controller_buscar_productos_dt.php`,
                    type: 'POST',
                    data: { id_usuario: ID_USUARIO_LOGUEADO }
                },
                columns: [
                    { data: 'id_producto', visible: false }, 
                    { data: 'codigo' }, 
                    { data: 'nombre' },
                    { data: 'stock', className: 'text-center' },
                    { data: 'precio_venta', className: 'text-right', render: $.fn.dataTable.render.number(',', '.', 2, '$ ') },
                    { data: 'iva_porcentaje_producto', className: 'text-center', render: function(data){ return (data ? parseFloat(data).toFixed(2) : '0.00') + '%';} },
                    { data: 'nombre_categoria' },
                    { data: null, orderable: false, searchable: false, className: 'text-center',
                        render: function(data, type, row) {
                            return `<button type="button" class="btn btn-xs btn-success btn-seleccionar-producto-venta" title="Añadir a la Venta"><i class="fas fa-plus-circle"></i></button>`;
                        }
                    }
                ],
                language: { url: `${URL_BASE}/public/templeates/AdminLTE-3.2.0/plugins/datatables-plugins/i18n/es_es.json`},
                responsive: true, 
                pageLength: 5, 
                lengthChange: false,
            });
        }
        $('#modal-buscar-producto-venta').modal('show');
    });

    $('#tabla_buscar_productos_venta_dt tbody').on('click', '.btn-seleccionar-producto-venta', function() {
        var data = tablaBuscarProductosVentaDT.row($(this).parents('tr')).data();
        if (data) { 
            anadirProductoAVenta(data); 
        }
    });

    function anadirProductoAVenta(producto) {
        if (itemsVenta[producto.id_producto]) {
            Swal.fire('Aviso', 'El producto ya está en la lista. Puede modificar la cantidad.', 'info'); 
            return;
        }
        
        // Para edición, el stock disponible incluye lo que ya se vendió de este producto
        let stockDisponible = parseFloat(producto.stock);
        if (itemsOriginales[producto.id_producto]) {
            stockDisponible += itemsOriginales[producto.id_producto].cantidad;
        }
        
        if (stockDisponible <= 0) {
            Swal.fire('Stock Agotado', 'Este producto no tiene stock disponible.', 'warning'); 
            return;
        }
        
        itemsVenta[producto.id_producto] = {
            id: producto.id_producto, 
            codigo: producto.codigo, 
            nombre: producto.nombre,
            stock_disponible: stockDisponible, 
            cantidad: 1, // Siempre entero
            precio_venta_unitario: parseFloat(producto.precio_venta || 0),
            porcentaje_iva: parseFloat(producto.iva_porcentaje_producto || 0)
        };
        renderizarTablaItemsVenta(); 
        calcularTotalesVenta();
    }

    function renderizarTablaItemsVenta() {
        const tbody = $('#tabla_items_venta tbody');
        tbody.empty();
        if (Object.keys(itemsVenta).length === 0) {
            tbody.html('<tr><td colspan="8" class="text-center py-3"><small>Aún no ha añadido productos a la venta.</small></td></tr>'); 
            return;
        }
        for (const id in itemsVenta) {
            const item = itemsVenta[id];
            const subtotalItem = item.cantidad * item.precio_venta_unitario;
            let fila = `
                <tr data-id-producto="${item.id}">
                    <td><button type="button" class="btn btn-xs btn-danger btn-remover-item-venta"><i class="fas fa-times"></i></button></td>
                    <td>${item.codigo}</td>
                    <td>${item.nombre}</td>
                    <td class="text-center">${Math.floor(item.stock_disponible)}</td>
                    <td><input type="number" class="form-control form-control-sm item-cantidad-venta" value="${item.cantidad}" min="1" max="${Math.floor(item.stock_disponible)}" step="1" style="width: 70px;" onchange="this.value=parseInt(this.value) || 1"></td>
                    <td><input type="number" class="form-control form-control-sm item-precio-venta" value="${item.precio_venta_unitario.toFixed(2)}" min="0" step="0.01" style="width: 90px;"></td>
                    <td><input type="number" class="form-control form-control-sm item-porcentaje-iva-venta" value="${item.porcentaje_iva.toFixed(2)}" min="0" step="0.01" style="width: 70px;"></td>
                    <td class="text-right item-subtotal-display-venta">${subtotalItem.toFixed(2)}</td>
                </tr>`;
            tbody.append(fila);
        }
    }
    
    $('#tabla_items_venta tbody').on('change keyup', '.item-cantidad-venta, .item-precio-venta, .item-porcentaje-iva-venta', function() {
        const fila = $(this).closest('tr');
        const idProducto = fila.data('id-producto');
        if (!itemsVenta[idProducto]) return;
        
        // Forzar que la cantidad sea un entero
        let cantidad = parseInt(fila.find('.item-cantidad-venta').val()) || 1;
        
        const precioVenta = parseFloat(fila.find('.item-precio-venta').val());
        const porcentajeIva = parseFloat(fila.find('.item-porcentaje-iva-venta').val());
        
        if (isNaN(cantidad) || cantidad < 1) cantidad = 1;
        
        // Limitar la cantidad al stock disponible (como entero)
        let stockDisponibleEntero = Math.floor(itemsVenta[idProducto].stock_disponible);
        if (cantidad > stockDisponibleEntero) {
            cantidad = stockDisponibleEntero;
            Swal.fire('Límite de Stock', `La cantidad no puede exceder el stock disponible (${stockDisponibleEntero}).`, 'warning');
            fila.find('.item-cantidad-venta').val(cantidad);
        }
        
        itemsVenta[idProducto].cantidad = cantidad;
        itemsVenta[idProducto].precio_venta_unitario = isNaN(precioVenta) ? 0 : precioVenta;
        itemsVenta[idProducto].porcentaje_iva = isNaN(porcentajeIva) ? 0 : porcentajeIva;
        const subtotalItem = itemsVenta[idProducto].cantidad * itemsVenta[idProducto].precio_venta_unitario;
        fila.find('.item-subtotal-display-venta').text(subtotalItem.toFixed(2));
        calcularTotalesVenta();
    });

    $('#tabla_items_venta tbody').on('click', '.btn-remover-item-venta', function() {
        const idProducto = $(this).closest('tr').data('id-producto');
        delete itemsVenta[idProducto];
        renderizarTablaItemsVenta(); 
        calcularTotalesVenta();
    });

    $('#btn-modal-crear-producto-rapido-venta').click(function() {
        $('#form-crear-producto-rapido-venta')[0].reset();
        $('#vr_validation_errors').hide().html('');
        $.ajax({
            url: `${URL_BASE}/app/controllers/almacen/controller_generar_siguiente_codigo.php`, 
            type: 'POST',
            data: { id_usuario: ID_USUARIO_LOGUEADO }, 
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') $('#vr_producto_codigo').val(response.nuevo_codigo);
                else Swal.fire('Error', 'No se pudo generar código para el producto.', 'error');
            }
        });
        $('#vr_producto_fecha_ingreso').val(new Date().toISOString().slice(0,10));
        $('#modal-crear-producto-rapido-venta').modal('show');
    });

    $('#form-crear-producto-rapido-venta').submit(function(e) {
        e.preventDefault(); 
        $('#vr_validation_errors').hide().html('');
        var formData = $(this).serializeArray();
        formData.push({name: "accion", value: "crear_producto_almacen_rapido"});
        formData.push({name: "id_usuario_creador", value: ID_USUARIO_LOGUEADO});
        $.ajax({
            url: `${URL_BASE}/almacen/acciones_almacen.php`, 
            type: 'POST', 
            data: $.param(formData), 
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success' && response.producto) {
                    $('#modal-crear-producto-rapido-venta').modal('hide');
                    Swal.fire('¡Éxito!', response.message, 'success');
                    anadirProductoAVenta(response.producto);
                } else {
                     $('#vr_validation_errors').html(response.message || 'Error desconocido.').show();
                }
            },
            error: function() { 
                $('#vr_validation_errors').html('Error de conexión.').show(); 
            }
        });
    });
    
    $('#vr_producto_stock_inicial').on('change blur', function() {
        const stockValue = parseInt($(this).val());
        if (isNaN(stockValue) || stockValue < 1) {
            $(this).val(1);
            Swal.fire({
                icon: 'info',
                title: 'Stock Inicial',
                text: 'El stock inicial debe ser al menos 1 unidad para poder realizar ventas.',
                timer: 3000
            });
        }
    });
    
    $('#descuento_general_venta').on('change keyup', calcularTotalesVenta);

    function calcularTotalesVenta() {
        let subtotalGeneral = 0, ivaGeneral = 0;
        for (const id in itemsVenta) {
            const item = itemsVenta[id];
            const subtotalItem = item.cantidad * item.precio_venta_unitario;
            const ivaItem = subtotalItem * (item.porcentaje_iva / 100);
            subtotalGeneral += subtotalItem; 
            ivaGeneral += ivaItem;
        }
        let descuentoGeneral = parseFloat($('#descuento_general_venta').val()) || 0;
        if (descuentoGeneral < 0) descuentoGeneral = 0;
        let totalAntesDescuento = subtotalGeneral + ivaGeneral;
        if (descuentoGeneral > totalAntesDescuento) {
            descuentoGeneral = totalAntesDescuento;
            Swal.fire('Aviso', 'El descuento no puede ser mayor al total.', 'warning');
            $('#descuento_general_venta').val(descuentoGeneral.toFixed(2));
        }
        const totalGeneral = totalAntesDescuento - descuentoGeneral;
        $('#subtotal_general_venta_display').val(subtotalGeneral.toFixed(2));
        $('#monto_iva_general_venta_display').val(ivaGeneral.toFixed(2));
        $('#total_general_venta_display').val(totalGeneral.toFixed(2));
        $('#subtotal_general_venta_calculado').val(subtotalGeneral.toFixed(2));
        $('#monto_iva_general_venta_calculado').val(ivaGeneral.toFixed(2));
        $('#total_general_venta_calculado').val(totalGeneral.toFixed(2));
    }

    // Modificar el manejador del formulario
    $('#form-editar-venta').submit(function(e) {
        e.preventDefault();
        
        // Establecer actualizarAPagada a false para una actualización normal
        actualizarAPagada = false;
        
        // Usar la función de envío
        enviarFormularioActualizacion();
    });
});
</script>