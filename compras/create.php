<?php
include '../app/config.php'; // $URL, $pdo, $fechaHora
include '../layout/sesion.php';

// include '../layout/permisos.php'; // Futuro para permisos en especificos roles

include '../layout/parte1.php';

if (!isset($_SESSION['id_usuario'])) {
    echo "<div class='container'>Error: No se pudo obtener el ID del usuario de la sesión. Por favor, inicie sesión nuevamente.</div>";
    include '../layout/parte2.php';
    exit;
}
$id_usuario_sesion = $_SESSION['id_usuario'];

include '../layout/mensajes.php';
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Registrar Nueva Compra</h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?php echo $URL;?>/compras/">Mis Compras</a></li>
                        <li class="breadcrumb-item active">Registrar Nueva Compra</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <form action="<?php echo $URL;?>/app/controllers/compras/controller_create_compra.php" method="POST" id="formNuevaCompra">
                <input type="hidden" name="id_usuario_compra" value="<?php echo $id_usuario_sesion; ?>">
                
                <div class="row">
                    <!-- Columna Izquierda: Datos Generales y Selección de Productos -->
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
                                                <input type="text" class="form-control" id="nombre_proveedor_compra_display" name="nombre_proveedor_compra_display" placeholder="Seleccione un proveedor o cree uno nuevo" readonly>
                                                <input type="hidden" id="id_proveedor_compra" name="id_proveedor_compra" required>
                                                <div class="input-group-append">
                                                    <button type="button" class="btn btn-info" data-toggle="modal" data-target="#modalBuscarProveedor">
                                                        <i class="fas fa-search"></i> Buscar/Crear
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="detalle_proveedor_seleccionado" class="alert alert-light mt-0 mb-2 py-1 px-2" style="display:none; font-size:0.9em;">
                                            <small><strong>Empresa:</strong> <span id="info_empresa_proveedor"></span> | <strong>Celular:</strong> <span id="info_celular_proveedor"></span></small>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label for="fecha_compra_compra">Fecha de Compra</label>
                                            <input type="date" class="form-control" id="fecha_compra_compra" name="fecha_compra_compra" value="<?php echo date('Y-m-d'); ?>" required>
                                        </div>
                                    </div>
                                </div>
                                 <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="nro_compra_referencia">Nro. Compra (Referencia Interna)</label>
                                            <input type="text" class="form-control" id="nro_compra_referencia" name="nro_compra_referencia" required readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="comprobante_compra">Comprobante (Factura/Boleta Nro.)</label>
                                            <input type="text" class="form-control" id="comprobante_compra" name="comprobante_compra">
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
                                                <input type="hidden" id="temp_iva_predeterminado_producto"> <!-- Para referencia del IVA que se usó -->
                                                <input type="hidden" id="temp_precio_compra_sugerido_producto">
                                                <input type="hidden" id="temp_stock_actual_producto">
                                                <div class="input-group-append">
                                                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalBuscarProducto"><i class="fas fa-search"></i></button>
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
                                            <button type="button" class="btn btn-success btn-block" id="btnAnadirProductoALista" title="Añadir a la lista"><i class="fas fa-plus"></i></button>
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
                                            <!-- Las filas se agregarán dinámicamente aquí -->
                                            <tr id="filaNoItems">
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
                                    <h5>Subtotal General: <span class="text-muted">$<span id="subtotal_general_compra_display">0.00</span></span></h5>
                                    <h5>IVA General: <span class="text-muted">$<span id="monto_iva_general_display">0.00</span></span></h5>
                                    <hr>
                                    <h3>Total Compra: <span class="text-primary">$<span id="total_general_compra_display">0.00</span></span></h3>
                                </div>
                                
                                <!-- Campos hidden para enviar los totales generales -->
                                <input type="hidden" name="subtotal_general_compra_calculado" id="subtotal_general_compra_hidden">
                                <input type="hidden" name="monto_iva_general_calculado" id="monto_iva_general_hidden">
                                <input type="hidden" name="total_general_compra_calculado" id="total_general_compra_hidden">
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-save"></i> Registrar Compra</button>
                                <a href="<?php echo $URL;?>/compras/" class="btn btn-secondary btn-block mt-2"><i class="fas fa-times"></i> Cancelar</a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->

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

<?php
include '../layout/parte2.php';
?>
<script>
$(document).ready(function() {
    var tablaProductosAlmacen;
    var tablaProveedores; 
    var idUsuarioActual = <?php echo json_encode($id_usuario_sesion); ?>;
    var contadorItemsCompra = 0;

    // --- LÓGICA PARA GENERAR NRO DE COMPRA DE REFERENCIA ---
    function generarNroCompraReferencia() {
        $('#nro_compra_referencia').val('Generando...');
        $.ajax({
            url: '<?php echo $URL; ?>/app/controllers/compras/controller_generar_codigo_compra.php',
            type: 'POST', 
            dataType: 'json',
            success: function(response) {
                if (response && response.status === 'success' && response.codigo_compra) {
                    $('#nro_compra_referencia').val(response.codigo_compra);
                } else {
                    $('#nro_compra_referencia').val('Error REF');
                    Swal.fire('Error', response.message || 'No se pudo generar la referencia de compra.', 'error');
                }
            },
            error: function() {
                $('#nro_compra_referencia').val('Error Conexión REF');
                Swal.fire('Error', 'No se pudo conectar para generar la referencia de compra.', 'error');
            }
        });
    }

    // --- LÓGICA PARA MODAL DE PRODUCTOS ---
    function generarSiguienteCodigoProducto() {
        $('#producto_codigo_rapido_display').val('Generando...');
        $.ajax({
            url: '<?php echo $URL; ?>/app/controllers/almacen/controller_generar_siguiente_codigo.php',
            type: 'POST',
            data: { id_usuario: idUsuarioActual },
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
            // Podrías tomar el IVA del último producto añadido o un default
            $('#producto_iva_rapido').val( parseFloat($('#temp_porcentaje_iva').val()) || 0);
        }
    });

    function cargarCategoriasUsuario() {
        $.ajax({
            url: '<?php echo $URL; ?>/app/controllers/categorias/controller_listar_categorias_usuario.php', 
            type: 'POST', 
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

  
    // ... (código de inicialización de DataTables) ...
    $('#modalBuscarProducto').on('shown.bs.modal', function () {
        console.log("Modal #modalBuscarProducto mostrado.");

        // Primero, llama a las funciones que deben ejecutarse cuando se muestra el modal
        cargarCategoriasUsuario(); 
        if ($('#crear-producto-tab').hasClass('active')) { 
            generarSiguienteCodigoProducto();
            // Considera si esta línea de IVA debe estar aquí o solo cuando se crea un producto
             $('#producto_iva_rapido').val( parseFloat($('#temp_porcentaje_iva').val()) || 0); 
        }

        console.log("Intentando inicializar o recargar DataTables para #tablaProductosAlmacen.");
        if ($.fn.DataTable.isDataTable('#tablaProductosAlmacen')) {
            console.log("DataTables ya está inicializada. Recargando datos.");
            try {
                // Antes de recargar, puedes verificar la instancia si quieres depurar
                // console.log("Instancia de DataTables antes de recargar:", tablaProductosAlmacen);
                 if (tablaProductosAlmacen && tablaProductosAlmacen.settings) {
                    // console.log("Configuración AJAX de DataTables:", tablaProductosAlmacen.settings()[0].ajax);
                 }
                tablaProductosAlmacen.ajax.reload(null, false); // El 'null, false' evita resetear la paginación
            } catch (e) {
                console.error("Error al intentar recargar DataTables:", e);
                // Si la recarga falla, podría ser mejor destruir y reinicializar
                console.log("Intentando destruir y reinicializar DataTables debido a error en recarga.");
                $('#tablaProductosAlmacen').DataTable().destroy();
                $('#tablaProductosAlmacen tbody').empty(); // Limpiar tbody antes de reinicializar
                inicializarTablaProductos(); // Llama a la función de inicialización
            }
        } else {
            console.log("DataTables no está inicializada. Inicializando ahora.");
            inicializarTablaProductos(); // Llama a la función de inicialización
        }
    });


    function inicializarTablaProductos() {
        console.log("Función inicializarTablaProductos llamada.");
        try {
            tablaProductosAlmacen = $('#tablaProductosAlmacen').DataTable({
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "<?php echo $URL; ?>/app/controllers/almacen/controller_buscar_productos_dt.php",
                    "type": "POST",
                    "data": function (d) {
                        d.id_usuario = idUsuarioActual;
                        // console.log("Enviando datos AJAX para DataTables:", d); // Descomentar para depurar si es necesario
                    },
                    "error": function(jqXHR, textStatus, errorThrown) {
                        console.error("Error en AJAX de DataTables:", textStatus, errorThrown);
                        if (jqXHR.responseText) {
                            console.error("Respuesta del servidor (primeros 500 chars):", jqXHR.responseText.substring(0, 500));
                            // No mostrar alert() al usuario final por errores de AJAX de DataTables por defecto,
                            // ya que DataTables tiene su propio manejo visual (aunque básico).
                            // Considerar mostrar un mensaje más amigable si es un error recurrente o crítico.
                        }
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
            console.log("DataTables inicializada exitosamente.");
        } catch (e) {
            console.error("Error CRÍTICO durante la inicialización de DataTables:", e);
            // Considerar un mensaje más amigable o un log a servidor si es un error crítico.
        }
    }

        // Mover el manejador de clic FUERA de la inicialización, adjuntándolo a la tabla
        // para que funcione con las filas creadas por DataTables.
        $('#tablaProductosAlmacen tbody').on('click', '.seleccionar-producto-para-compra', function () {
        if (!tablaProductosAlmacen) {
            console.error("La instancia de tablaProductosAlmacen no está disponible.");
            return;
        }
        var fila = $(this).closest('tr');
        // Manejo para filas responsive colapsadas
        var datosFila;
        if (tablaProductosAlmacen.row(fila).child.isShown()) { // Si la fila "child" (responsive) está mostrándose
            datosFila = tablaProductosAlmacen.row(fila.prev('tr.dt-hasChild')).data(); // Obtener datos de la fila "parent"
        } else {
            datosFila = tablaProductosAlmacen.row(fila).data(); // Obtener datos de la fila actual
        }


        if (!datosFila) {
            console.error("No se pudieron obtener los datos de la fila para el producto seleccionado.");
            Swal.fire('Error', 'No se pudieron obtener los datos del producto. Intente de nuevo.', 'error');
            return;
        }

            console.log("--- Evento: Click en .seleccionar-producto-para-compra (Datos de Fila) ---");
            console.log("Datos de la Fila Completa:", datosFila); 
            console.log("ID del producto seleccionado:", datosFila.id_producto);
            console.log("Nombre del producto:", datosFila.nombre);
            console.log("IVA predeterminado (tb_almacen):", datosFila.iva_porcentaje_producto);
            console.log("IVA última compra (subconsulta):", datosFila.iva_ultima_compra);


            var idProducto = datosFila.id_producto;
            // Verificar si el producto ya está en la lista
            var yaEnLista = false;
            $('#tablaItemsCompra tbody tr').not('#filaNoItems').each(function() {
                if ($(this).find('input[name="item_id_producto[]"]').val() == idProducto) {
                    yaEnLista = true;
                    return false; // Salir del bucle
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

        // ***** INICIO DE LA MODIFICACIÓN PARA IVA DINÁMICO *****
        let ivaAplicar;
        // Verificar si iva_ultima_compra existe, no es null y es un número válido
        if (datosFila.hasOwnProperty('iva_ultima_compra') && datosFila.iva_ultima_compra !== null && !isNaN(parseFloat(datosFila.iva_ultima_compra))) {
            ivaAplicar = parseFloat(datosFila.iva_ultima_compra).toFixed(2);
            console.log("Aplicando IVA de última compra:", ivaAplicar + "%");
        } else {
            // Fallback al IVA predeterminado del producto si no hay última compra o es inválido
            ivaAplicar = parseFloat(datosFila.iva_porcentaje_producto || 0).toFixed(2);
            console.log("Aplicando IVA predeterminado del producto (tb_almacen):", ivaAplicar + "%");
        }
        
        $('#temp_iva_predeterminado_producto').val(ivaAplicar); // Campo oculto para referencia del IVA que se usó
        $('#temp_porcentaje_iva').val(ivaAplicar); // Campo visible para el IVA del ítem actual
        // ***** FIN DE LA MODIFICACIÓN PARA IVA DINÁMICO *****
        
        $('#temp_producto_info').html(`Cód: ${datosFila.codigo || 'N/A'} | Stock: ${datosFila.stock || 0} | IVA Aplicado: ${ivaAplicar}%`).show();
        $('#temp_cantidad').val(1).focus(); 
        
        $('#modalBuscarProducto').modal('hide');
    });
   
    // --- LÓGICA PARA AÑADIR PRODUCTO A LA TABLA DE ITEMS DE COMPRA ---
    $('#btnAnadirProductoALista').on('click', function() {
        // Corrección de logs para este botón
        console.log("--- Evento: Click en #btnAnadirProductoALista ---");
        console.log("ID del producto desde #temp_id_producto:", $('#temp_id_producto').val());
        console.log("Nombre del producto desde #temp_nombre_producto:", $('#temp_nombre_producto').val());
        console.log("IVA desde #temp_porcentaje_iva:", $('#temp_porcentaje_iva').val());

        var idProducto = $('#temp_id_producto').val();
        var codigoProducto = $('#temp_codigo_producto').val();
        var nombreProducto = $('#temp_nombre_producto').val();
        var cantidad = parseFloat($('#temp_cantidad').val()) || 0;
        var precioCompra = parseFloat($('#temp_precio_compra').val()) || 0;
        var porcentajeIva = parseFloat($('#temp_porcentaje_iva').val()) || 0; // Este es el IVA que se decidió (última compra o predeterminado)

        if (!idProducto) {
            Swal.fire('Atención', 'Debe seleccionar un producto.', 'warning'); return;
        }
        if (cantidad <= 0) {
            Swal.fire('Atención', 'La cantidad debe ser mayor a cero.', 'warning'); $('#temp_cantidad').focus(); return;
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
                <td><input type="number" name="item_cantidad[]" class="form-control form-control-sm item-cantidad" value="${cantidad}" min="1" style="width:70px;"></td>
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

        $('#temp_id_producto, #temp_codigo_producto, #temp_nombre_producto, #temp_iva_predeterminado_producto, #temp_precio_compra_sugerido_producto, #temp_stock_actual_producto').val('');
        $('#temp_cantidad').val(1);
        $('#temp_precio_compra').val(''); // Limpiar precio de compra
        $('#temp_porcentaje_iva').val(0); // Resetear IVA a 0 para el siguiente
        $('#temp_producto_info').hide().empty();
        $('#temp_nombre_producto').focus(); 
    });

    // --- RECALCULAR TOTALES DE UN ITEM SI SE MODIFICA EN LA TABLA ---
    $('#tablaItemsCompra tbody').on('change keyup', '.item-cantidad, .item-precio, .item-iva', function() {
        var fila = $(this).closest('tr');
        var cantidad = parseFloat(fila.find('.item-cantidad').val()) || 0;
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
            var cantidad = parseFloat(fila.find('.item-cantidad').val()) || 0;
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

    // --- LÓGICA PARA PROVEEDORES (MODAL Y FORMULARIO) ---
    $('#modalBuscarProveedor').on('shown.bs.modal', function () {
        if (!$.fn.DataTable.isDataTable('#tablaProveedores')) {
            tablaProveedores = $('#tablaProveedores').DataTable({
                "processing": true, "serverSide": true,
                "ajax": {"url": "<?php echo $URL; ?>/app/controllers/proveedores/controller_proveedores_serverside.php", "type": "POST"},
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
    
    // --- INICIALIZACIÓN Y VALIDACIÓN DEL FORMULARIO PRINCIPAL ---
    generarNroCompraReferencia();
    recalcularTotalesGenerales(); 

    $('#formNuevaCompra').on('submit', function(e){
        if (!$('#id_proveedor_compra').val()) { 
            e.preventDefault(); Swal.fire('Atención', 'Debe seleccionar un proveedor.', 'warning'); return false;
        }
        if ($('#tablaItemsCompra tbody tr').not('#filaNoItems').length === 0) {
            e.preventDefault(); Swal.fire('Atención', 'Debe añadir al menos un producto a la compra.', 'warning'); return false;
        }
        if (!$('#nro_compra_referencia').val() || $('#nro_compra_referencia').val() === 'Generando...' || $('#nro_compra_referencia').val().startsWith('Error')) {
            e.preventDefault(); Swal.fire('Atención', 'Espere a que se genere el Número de Compra o verifique si hay errores.', 'warning'); return false;
        }
        var itemsValidos = true;
        $('#tablaItemsCompra tbody tr').not('#filaNoItems').each(function() {
            var cantidad = parseFloat($(this).find('.item-cantidad').val()) || 0;
            if (cantidad <= 0) {
                itemsValidos = false;
                Swal.fire('Atención', 'Todas las cantidades de los productos deben ser mayores a cero.', 'warning');
                $(this).find('.item-cantidad').focus();
                return false; 
            }
        });
        if(!itemsValidos) {
            e.preventDefault();
            return false;
        }
    });
});
</script>