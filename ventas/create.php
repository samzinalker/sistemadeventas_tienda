<?php
include '../app/config.php'; // $URL, $pdo, $fechaHora
include '../layout/sesion.php'; // Verifica sesión, establece $id_usuario_sesion, $rol_sesion, etc.
include '../layout/parte1.php'; // Cabecera HTML, CSS, jQuery, y menú lateral

// Necesitaremos las categorías para el modal de creación rápida de productos
require_once __DIR__ . '/../app/models/CategoriaModel.php';
$categoriaModel = new CategoriaModel($pdo);
$categorias_select_datos = [];
if (isset($id_usuario_sesion)) { // Asegurarse que $id_usuario_sesion esté disponible
    $categorias_select_datos = $categoriaModel->getCategoriasByUsuarioId($id_usuario_sesion);
}


// Para mostrar mensajes flash (SweetAlert)
// (Se podría mover a una variable y mostrarla antes de parte2.php para evitar output prematuro si hay errores)
// No obstante, si mensajes.php solo genera script JS cuando hay mensaje, está bien aquí.
include '../layout/mensajes.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-shopping-cart"></i> Registrar Nueva Venta</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?php echo $URL; ?>/">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo $URL; ?>/ventas/">Ventas</a></li>
                        <li class="breadcrumb-item active">Registrar Venta</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <form id="form-registrar-venta" method="POST">
                <div class="row">
                    <!-- Columna Izquierda: Datos de la Venta y Cliente -->
                    <div class="col-md-9">
                        <div class="card card-primary card-outline">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-file-invoice-dollar"></i> Datos de la Venta</h3>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="nro_venta_referencia">Nro. Venta (Ref.)</label>
                                            <input type="text" class="form-control" id="nro_venta_referencia" name="nro_venta_referencia" readonly>
                                            <input type="hidden" id="nro_venta_secuencial" name="nro_venta_secuencial">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="fecha_venta">Fecha de Venta <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control" id="fecha_venta" name="fecha_venta" value="<?php echo date('Y-m-d'); ?>" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="tipo_comprobante_venta">Tipo Comprobante</label>
                                            <select class="form-control" id="tipo_comprobante_venta" name="tipo_comprobante_venta">
                                                <option value="NOTA DE VENTA">NOTA DE VENTA</option>
                                                <option value="FACTURA">FACTURA</option>
                                                <option value="TICKET">TICKET</option>
                                                <option value="OTRO">OTRO</option>
                                            </select>
                                        </div>
                                    </div>
                                     <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="nro_comprobante_fisico_venta">Nro. Comprobante Físico</label>
                                            <input type="text" class="form-control" id="nro_comprobante_fisico_venta" name="nro_comprobante_fisico_venta" placeholder="Ej: 001-001-123456789">
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
                                                <input type="text" class="form-control" id="display_nombre_cliente_venta" placeholder="Ningún cliente seleccionado" readonly>
                                                <input type="hidden" id="id_cliente_venta" name="id_cliente_venta">
                                                <div class="input-group-append">
                                                    <button class="btn btn-primary" type="button" id="btn-gestionar-cliente-venta" title="Buscar o Crear Cliente">
                                                        <i class="fas fa-users"></i> Buscar / Crear
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div id="info_extra_cliente_venta" class="pt-4">
                                            <small id="display_documento_cliente_venta" class="form-text text-muted"><i>Seleccione un cliente...</i></small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card card-info card-outline mt-3">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-boxes"></i> Productos para la Venta</h3>
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
                                        <!-- Filas de productos añadidos dinámicamente -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                         <div class="form-group mt-3">
                            <label for="observaciones_venta">Observaciones Adicionales</label>
                            <textarea class="form-control" id="observaciones_venta" name="observaciones_venta" rows="2" placeholder="Cualquier nota relevante para esta venta..."></textarea>
                        </div>
                    </div>

                    <!-- Columna Derecha: Totales y Acciones -->
                    <div class="col-md-3">
                        <div class="card card-success card-outline">
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
                                 <div class="form-group row mb-1">
                                    <label for="descuento_general_venta" class="col-sm-6 col-form-label col-form-label-sm">Descuento (-):</label>
                                    <div class="col-sm-6">
                                        <input type="number" class="form-control form-control-sm text-right" id="descuento_general_venta" name="descuento_general_venta" value="0.00" step="0.01" min="0">
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
                                    <button type="submit" class="btn btn-primary btn-block" id="btn-guardar-venta">
                                        <i class="fas fa-save"></i> Guardar Venta
                                    </button>
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
<!-- Modal para Gestionar (Buscar o Crear) Cliente -->
<div class="modal fade" id="modal-gestionar-cliente-venta" tabindex="-1" aria-labelledby="modalGestionarClienteLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalGestionarClienteLabel"><i class="fas fa-users"></i> Gestionar Cliente para la Venta</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
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

<!-- Modal para Crear Cliente Rápido (el que ya tenías) -->
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
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="vr_producto_stock_minimo">Stock Mínimo</label>
                                <input type="number" class="form-control" id="vr_producto_stock_minimo" name="producto_stock_minimo" min="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="vr_producto_stock_maximo">Stock Máximo</label>
                                <input type="number" class="form-control" id="vr_producto_stock_maximo" name="producto_stock_maximo" min="0">
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
<!-- Select2 ya no es necesario para el cliente principal, pero podría usarse en otros lugares. Lo dejo por si acaso, pero podría eliminarse si no se usa. -->
<!-- <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" /> -->
<!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@ttskch/select2-bootstrap4-theme@x.x.x/dist/select2-bootstrap4.min.css"> -->
<!-- <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.full.min.js"></script> -->

<script>
// Variable global para la tabla de items de venta
var itemsVenta = {}; // Almacenará los productos añadidos

$(document).ready(function() {
    const URL_BASE = '<?php echo $URL; ?>';
    // Asegurarse que $id_usuario_sesion esté disponible y sea un número
    const ID_USUARIO_LOGUEADO = <?php echo isset($id_usuario_sesion) ? intval($id_usuario_sesion) : 'null'; ?>;
    if (ID_USUARIO_LOGUEADO === null) {
        Swal.fire('Error de Sesión', 'No se pudo identificar al usuario. Por favor, recargue la página o inicie sesión de nuevo.', 'error');
        // Podrías deshabilitar el formulario aquí o redirigir
    }

    let tablaBuscarProductosVentaDT;
    let tablaBuscarClientesGestionDT;

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
                default: // extranjero, otro
                    inputDocumento.prop('disabled', false).prop('required', false).attr('placeholder', 'Ingrese identificación');
                    inputDocumento.removeAttr('pattern').attr('maxlength', 30);
                    labelDocumento.text('Nro. Documento');
                    helpText.text('Ingrese el documento de identificación correspondiente.');
            }
        }
        selectTipoDoc.on('change', actualizarCampos);
        actualizarCampos();
    }

    // --- INICIALIZACIONES ---
    generarNumeroVenta();
    renderizarTablaItemsVenta(); // Para mostrar el mensaje de "sin productos" al inicio
    // La siguiente llamada a configurarCampoDocumento es para el modal de creación rápida de cliente.
    // Se llama cuando el modal se abre y también aquí para asegurar que esté lista si el modal se muestra por error o directamente.
    configurarCampoDocumento('dv_tipo_documento', 'dv_nit_ci_cliente', 'dv_documento_help', 'label_dv_documento');


    // --- NÚMERO DE VENTA ---
    function generarNumeroVenta() {
        $.ajax({
            url: `${URL_BASE}/app/controllers/ventas/controller_generar_codigo_venta.php`,
            type: 'POST',
            dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    $('#nro_venta_referencia').val(response.codigo_venta);
                    $('#nro_venta_secuencial').val(response.nro_secuencial);
                } else {
                    Swal.fire('Error', response.message || 'No se pudo generar el número de venta.', 'error');
                }
            },
            error: function() { Swal.fire('Error', 'Error al contactar para generar número de venta.', 'error');}
        });
    }

    // --- GESTIÓN DE CLIENTES (NUEVA LÓGICA) ---
    $('#btn-gestionar-cliente-venta').click(function() {
        $('#modal-gestionar-cliente-venta').modal('show');
        if ($.fn.DataTable.isDataTable('#tabla_buscar_clientes_gestion_dt')) {
            tablaBuscarClientesGestionDT.ajax.reload();
        } else {
            tablaBuscarClientesGestionDT = $('#tabla_buscar_clientes_gestion_dt').DataTable({
                processing: true, serverSide: true,
                ajax: {
                    url: `${URL_BASE}/app/controllers/clientes/controller_listado_clientes_dt.php`,
    type: 'POST',
    error: function (jqXHR, textStatus, errorThrown) {
        console.error("Error AJAX de DataTables:");
        console.error("Status: ", textStatus);
        console.error("Error Thrown: ", errorThrown);
        console.error("Respuesta del servidor (jqXHR.responseText):");
        console.error(jqXHR.responseText); // Esto mostrará la respuesta cruda
        alert("Error al cargar datos de clientes. Revise la consola (F12) para más detalles. Respuesta del servidor: " + jqXHR.responseText.substring(0, 500) + "...");
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
                            return `<button type="button" class="btn btn-xs btn-primary btn-seleccionar-cliente-gestion" title="Seleccionar este Cliente"><i class="fas fa-check-circle"></i> Seleccionar</button>`;
                        }
                    }
                ],
                language: { url: `${URL_BASE}/public/templeates/AdminLTE-3.2.0/plugins/datatables-plugins/i18n/es_es.json`},
                responsive: true, pageLength: 5, lengthChange: false,
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
        $('#display_documento_cliente_venta').html(docInfo); // Usar .html() para el <b>
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
            type: 'POST', data: formData, dataType: 'json',
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
            error: function() { $('#dv_validation_errors').html('Error de conexión.').show(); }
        });
    });

    // --- PRODUCTOS ---
    $('#btn-modal-buscar-producto-venta').click(function() {
        if ($.fn.DataTable.isDataTable('#tabla_buscar_productos_venta_dt')) {
            tablaBuscarProductosVentaDT.ajax.reload();
        } else {
            tablaBuscarProductosVentaDT = $('#tabla_buscar_productos_venta_dt').DataTable({
                processing: true, serverSide: true,
                ajax: {
                    url: `${URL_BASE}/app/controllers/almacen/controller_buscar_productos_dt.php`,
                    type: 'POST',
                    data: { id_usuario: ID_USUARIO_LOGUEADO }
                },
                columns: [
                    { data: 'id_producto', visible: false }, { data: 'codigo' }, { data: 'nombre' },
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
                responsive: true, pageLength: 5, lengthChange: false,
            });
        }
        $('#modal-buscar-producto-venta').modal('show');
    });

    $('#tabla_buscar_productos_venta_dt tbody').on('click', '.btn-seleccionar-producto-venta', function() {
        var data = tablaBuscarProductosVentaDT.row($(this).parents('tr')).data();
        if (data) { anadirProductoAVenta(data); }
    });

    function anadirProductoAVenta(producto) {
        if (itemsVenta[producto.id_producto]) {
            Swal.fire('Aviso', 'El producto ya está en la lista. Puede modificar la cantidad.', 'info'); return;
        }
        if (parseFloat(producto.stock) <= 0) {
            Swal.fire('Stock Agotado', 'Este producto no tiene stock disponible.', 'warning'); return;
        }
        itemsVenta[producto.id_producto] = {
            id: producto.id_producto, codigo: producto.codigo, nombre: producto.nombre,
            stock_disponible: parseFloat(producto.stock), cantidad: 1,
            precio_venta_unitario: parseFloat(producto.precio_venta || 0),
            porcentaje_iva: parseFloat(producto.iva_porcentaje_producto || 0)
        };
        renderizarTablaItemsVenta(); calcularTotalesVenta();
    }

    function renderizarTablaItemsVenta() {
        const tbody = $('#tabla_items_venta tbody');
        tbody.empty();
        if (Object.keys(itemsVenta).length === 0) {
            tbody.html('<tr><td colspan="8" class="text-center py-3"><small>Aún no ha añadido productos a la venta.</small></td></tr>'); return;
        }
        for (const id in itemsVenta) {
            const item = itemsVenta[id];
            const subtotalItem = item.cantidad * item.precio_venta_unitario;
            let fila = `
                <tr data-id-producto="${item.id}">
                    <td><button type="button" class="btn btn-xs btn-danger btn-remover-item-venta"><i class="fas fa-times"></i></button></td>
                    <td>${item.codigo}</td><td>${item.nombre}</td><td class="text-center">${item.stock_disponible}</td>
                    <td><input type="number" class="form-control form-control-sm item-cantidad-venta" value="${item.cantidad}" min="1" max="${item.stock_disponible}" step="1" style="width: 70px;"></td>
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
        let cantidad = parseFloat(fila.find('.item-cantidad-venta').val());
        const precioVenta = parseFloat(fila.find('.item-precio-venta').val());
        const porcentajeIva = parseFloat(fila.find('.item-porcentaje-iva-venta').val());
        if (isNaN(cantidad) || cantidad < 1) cantidad = 1;
        if (cantidad > itemsVenta[idProducto].stock_disponible) {
            cantidad = itemsVenta[idProducto].stock_disponible;
            Swal.fire('Límite de Stock', `La cantidad no puede exceder el stock disponible (${itemsVenta[idProducto].stock_disponible}).`, 'warning');
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
        renderizarTablaItemsVenta(); calcularTotalesVenta();
    });

    $('#btn-modal-crear-producto-rapido-venta').click(function() {
        $('#form-crear-producto-rapido-venta')[0].reset();
        $('#vr_validation_errors').hide().html('');
        $.ajax({
            url: `${URL_BASE}/app/controllers/almacen/controller_generar_siguiente_codigo.php`, type: 'POST',
            data: { id_usuario: ID_USUARIO_LOGUEADO }, dataType: 'json',
            success: function(response) {
                if (response.status === 'success') $('#vr_producto_codigo').val(response.nuevo_codigo);
                else Swal.fire('Error', 'No se pudo generar código para el producto.', 'error');
            }
        });
        $('#vr_producto_fecha_ingreso').val(new Date().toISOString().slice(0,10));
        $('#modal-crear-producto-rapido-venta').modal('show');
    });

    $('#form-crear-producto-rapido-venta').submit(function(e) {
        e.preventDefault(); $('#vr_validation_errors').hide().html('');
        var formData = $(this).serializeArray();
        formData.push({name: "accion", value: "crear_producto_almacen_rapido"});
        formData.push({name: "id_usuario_creador", value: ID_USUARIO_LOGUEADO});
        $.ajax({
            url: `${URL_BASE}/almacen/acciones_almacen.php`, type: 'POST', data: $.param(formData), dataType: 'json',
            success: function(response) {
                if (response.status === 'success' && response.producto) {
                    $('#modal-crear-producto-rapido-venta').modal('hide');
                    Swal.fire('¡Éxito!', response.message, 'success');
                    anadirProductoAVenta(response.producto);
                } else {
                     $('#vr_validation_errors').html(response.message || 'Error desconocido.').show();
                }
            },
            error: function() { $('#vr_validation_errors').html('Error de conexión.').show(); }
        });
    });
    
    $('#descuento_general_venta').on('change keyup', calcularTotalesVenta);

    function calcularTotalesVenta() {
        let subtotalGeneral = 0, ivaGeneral = 0;
        for (const id in itemsVenta) {
            const item = itemsVenta[id];
            const subtotalItem = item.cantidad * item.precio_venta_unitario;
            const ivaItem = subtotalItem * (item.porcentaje_iva / 100);
            subtotalGeneral += subtotalItem; ivaGeneral += ivaItem;
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

    $('#form-registrar-venta').submit(function(e) {
        e.preventDefault();
        $('#btn-guardar-venta').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');
        if (Object.keys(itemsVenta).length === 0) {
            Swal.fire('Error', 'Debe añadir al menos un producto a la venta.', 'error');
            $('#btn-guardar-venta').prop('disabled', false).html('<i class="fas fa-save"></i> Guardar Venta'); return;
        }
        if (!$('#id_cliente_venta').val()) {
             Swal.fire('Error', 'Debe seleccionar un cliente.', 'error');
            $('#btn-guardar-venta').prop('disabled', false).html('<i class="fas fa-save"></i> Guardar Venta'); return;
        }
        var formData = $(this).serializeArray();
        let itemIndex = 0;
        for (const idProducto in itemsVenta) {
            const item = itemsVenta[idProducto];
            formData.push({name: `items[${itemIndex}][id_producto]`, value: item.id});
            formData.push({name: `items[${itemIndex}][cantidad]`, value: item.cantidad});
            formData.push({name: `items[${itemIndex}][precio_venta_unitario]`, value: item.precio_venta_unitario});
            formData.push({name: `items[${itemIndex}][porcentaje_iva_item]`, value: item.porcentaje_iva});
            itemIndex++;
        }
        $.ajax({
            url: `${URL_BASE}/app/controllers/ventas/controller_create_venta.php`,
            type: 'POST', data: $.param(formData), dataType: 'json',
            success: function(response) {
                if (response.status === 'success') {
                    Swal.fire({title: '¡Venta Registrada!', text: response.message, icon: 'success', confirmButtonText: 'Aceptar'})
                    .then(() => { window.location.href = `${URL_BASE}/ventas/show.php?id=${response.id_venta}`; });
                } else {
                    Swal.fire('Error', response.message || 'No se pudo registrar la venta.', 'error');
                }
            },
            error: function(xhr) { Swal.fire('Error de Servidor', 'No se pudo contactar al servidor.', 'error'); console.error(xhr.responseText);},
            complete: function() { $('#btn-guardar-venta').prop('disabled', false).html('<i class="fas fa-save"></i> Guardar Venta');}
        });
    });
});
</script>