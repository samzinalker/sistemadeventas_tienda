<?php
include('../app/config.php');
include('../layout/sesion.php');
include('../layout/parte1.php');
include('../app/controllers/ventas/listado_de_ventas.php');
include('../app/controllers/almacen/listado_de_productos.php');
include('../app/controllers/clientes/listado_de_clientes.php');

// Consulta del carrito: SOLO productos del usuario actual y carrito abierto
$id_usuario_actual = $_SESSION['id_usuario'];
$sql_carrito = "SELECT c.*, 
                       pro.nombre AS nombre_producto, 
                       pro.descripcion AS descripcion, 
                       pro.precio_venta AS precio_venta, 
                       pro.stock AS stock 
                FROM tb_carrito c
                INNER JOIN tb_almacen pro ON c.id_producto = pro.id_producto
                WHERE c.id_usuario = :id_usuario AND c.nro_venta = 0";
$query_carrito = $pdo->prepare($sql_carrito);
$query_carrito->bindParam(':id_usuario', $id_usuario_actual, PDO::PARAM_INT);
$query_carrito->execute();
$carrito_datos = $query_carrito->fetchAll(PDO::FETCH_ASSOC);

// Para mostrar mensajes de éxito o error tras finalizar venta
$success_msg = '';
$error_msg = '';
if (isset($_GET['success'])) $success_msg = '¡Venta registrada correctamente!';
if (isset($_GET['error'])) $error_msg = urldecode($_GET['error']);

// Inicializar el array para almacenar el último IVA usado por producto si no existe
if(!isset($_SESSION['ultimo_iva_por_producto'])) {
    $_SESSION['ultimo_iva_por_producto'] = [];
}

// Valor predeterminado de IVA (12% o 0%, según la preferencia)
$iva_predeterminado = 12.00;
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header. Contains page header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="m-0">Nueva Venta</h1>
                </div>
            </div>
        </div>
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">

        <?php if ($success_msg): ?>
            <div id="success-alert" class="alert alert-success"><?php echo $success_msg; ?></div>
            <script>
                setTimeout(function() {
                    $('#success-alert').fadeOut('slow', function() {
                        $(this).remove();
                        var urlWithoutParams = window.location.href.split('?')[0];
                        window.history.replaceState({}, document.title, urlWithoutParams);
                    });
                }, 3000);
            </script>
        <?php endif; ?>

        <?php if ($error_msg): ?>
            <div id="error-alert" class="alert alert-danger"><?php echo $error_msg; ?></div>
            <script>
                setTimeout(function() {
                    $('#error-alert').fadeOut('slow', function() {
                        $(this).remove();
                        var urlWithoutParams = window.location.href.split('?')[0];
                        window.history.replaceState({}, document.title, urlWithoutParams);
                    });
                }, 5000);
            </script>
        <?php endif; ?>

           <div class="row">
               <div class="col-md-12">
                   <div class="card card-outline card-primary">
                       <div class="card-header">
                            <?php 
                           $contador_de_ventas = 0;
                           foreach($ventas_datos as $ventas_dato){
                               $contador_de_ventas++;
                           }
                           ?>
                            <h3 class="card-title">
                                <i class="fa fa-shopping-bag"></i> Nueva Venta #<?php echo $contador_de_ventas + 1; ?>
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                       </div>
                       <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <h4 class="mr-3 mb-0"><i class="fas fa-shopping-cart text-primary"></i> Productos en Carrito</h4>
                                <button type="button" class="btn btn-primary btn-lg" data-toggle="modal" data-target="#modal-buscar_producto">
                                    <i class="fa fa-plus"></i> Agregar Producto
                                </button>
                            </div>

                            <!-- Modal para buscar producto (sólo muestra los productos del usuario) -->
                            <div class="modal fade" id="modal-buscar_producto">
                                <div class="modal-dialog modal-xl">
                                    <div class="modal-content">
                                        <div class="modal-header" style="background-color: #2c61a3; color: white">
                                            <h4 class="modal-title"><i class="fas fa-box-open mr-2"></i> Seleccionar Producto</h4>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="table-responsive">
                                                <table id="tabla_productos" class="table table-bordered table-striped table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Acción</th>
                                                            <th>Código</th>
                                                            <th>Categoría</th>
                                                            <th>Imagen</th>
                                                            <th>Nombre</th>
                                                            <th>Descripción</th>
                                                            <th>Stock</th>
                                                            <th>Precio Venta</th>
                                                            <th>Fecha</th>
                                                        </tr>
                                                    </thead>                                                
                                                    <tbody>
                                                        <?php
                                                        $contador = 0;
                                                        foreach ($productos_datos as $productos_dato){
                                                            $id_producto = $productos_dato['id_producto']; 
                                                            $stock_actual = $productos_dato['stock'];
                                                            // Obtener una versión corta de la descripción (máximo 30 caracteres)
                                                            $descripcion_corta = strlen($productos_dato['descripcion']) > 30 ? 
                                                                substr($productos_dato['descripcion'], 0, 30) . "..." : 
                                                                $productos_dato['descripcion'];
                                                            ?>
                                                            <tr>
                                                                <td><?php echo ++$contador; ?></td>
                                                                <td>
                                                                    <div class="btn-group">
                                                                        <button class="btn <?php echo ($stock_actual <= 0) ? 'btn-secondary' : 'btn-info'; ?> btn-sm" 
                                                                                id="btn_seleccionar<?php echo $id_producto;?>" 
                                                                                <?php if ($stock_actual <= 0): ?>
                                                                                    disabled title="Sin stock disponible"
                                                                                <?php endif; ?>>
                                                                            <i class="fas fa-check-circle"></i> Seleccionar
                                                                        </button>
                                                                        <button type="button" class="btn btn-secondary btn-sm" 
                                                                                data-toggle="modal" 
                                                                                data-target="#modal-descripcion<?php echo $id_producto;?>">
                                                                            <i class="fas fa-align-left"></i> Descripción
                                                                        </button>
                                                                    </div>
                                                                    <script>
                                                                        $('#btn_seleccionar<?php echo $id_producto;?>').click(function() {
                                                                            var id_producto = <?php echo $id_producto; ?>;
                                                                            $('#id_producto').val(id_producto);
                                                                            $('#producto').val('<?php echo htmlspecialchars($productos_dato['nombre']); ?>');
                                                                            $('#descripcion').val('<?php echo htmlspecialchars($productos_dato['descripcion']); ?>');
                                                                            $('#precio_venta').val('<?php echo $productos_dato['precio_venta']; ?>');
                                                                            $('#stock_actual').val('<?php echo $stock_actual; ?>');
                                                                            $('#cantidad').val(1);
                                                                            
                                                                            // Cargar el último porcentaje de IVA usado para este producto específico
                                                                            if (typeof ultimosIVA !== 'undefined' && ultimosIVA[id_producto]) {
                                                                                $('#porcentaje_iva').val(ultimosIVA[id_producto]);
                                                                            } else {
                                                                                $('#porcentaje_iva').val('<?php echo $iva_predeterminado; ?>');
                                                                            }
                                                                            
                                                                            $('#cantidad').focus();
                                                                            $('#cantidad').select();
                                                                            
                                                                            // Resaltar la sección de datos seleccionados
                                                                            $('.productos-seleccionados').addClass('highlight-selection');
                                                                            setTimeout(function() {
                                                                                $('.productos-seleccionados').removeClass('highlight-selection');
                                                                            }, 1000);
                                                                        });
                                                                    </script>
                                                                </td>
                                                                <td><?php echo $productos_dato['codigo'];?></td>
                                                                <td><?php echo $productos_dato['categoria'];?></td>
                                                                <td>
                                                                    <img src="<?php echo $URL."/almacen/img_productos/".$productos_dato['imagen'];?>" 
                                                                         width="50px" alt="Imagen producto" class="img-thumbnail">
                                                                </td>
                                                                <td><?php echo $productos_dato['nombre'];?></td>
                                                                <td>
                                                                    <?php echo $descripcion_corta; ?>
                                                                </td>
                                                                <td>
                                                                    <?php if ($stock_actual <= 0): ?>
                                                                        <span class="badge badge-danger">Agotado</span>
                                                                    <?php elseif ($stock_actual <= $productos_dato['stock_minimo']): ?>
                                                                        <span class="badge badge-warning"><?php echo $stock_actual; ?></span>
                                                                    <?php else: ?>
                                                                        <span class="badge badge-success"><?php echo $stock_actual; ?></span>
                                                                    <?php endif; ?>
                                                                </td>
                                                                <td><?php echo number_format($productos_dato['precio_venta'], 2);?></td>
                                                                <td><?php echo date('d/m/Y', strtotime($productos_dato['fecha_ingreso']));?></td>
                                                            </tr>
                                                            
                                                            <!-- Modal para mostrar la descripción completa -->
                                                            <div class="modal fade" id="modal-descripcion<?php echo $id_producto;?>" tabindex="-1" role="dialog" aria-hidden="true">
                                                                <div class="modal-dialog modal-dialog-centered">
                                                                    <div class="modal-content">
                                                                        <div class="modal-header" style="background-color: #343a40; color: white">
                                                                            <h5 class="modal-title">
                                                                                <i class="fas fa-file-alt mr-2"></i> Descripción de Producto
                                                                            </h5>
                                                                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                                                <span aria-hidden="true">&times;</span>
                                                                            </button>
                                                                        </div>
                                                                        <div class="modal-body">
                                                                            <div class="text-center mb-3">
                                                                                <img src="<?php echo $URL."/almacen/img_productos/".$productos_dato['imagen'];?>" 
                                                                                    style="max-height: 150px;" alt="Imagen producto" class="img-thumbnail">
                                                                            </div>
                                                                            <h4 class="text-center mb-3"><?php echo htmlspecialchars($productos_dato['nombre']); ?></h4>
                                                                            <div class="card">
                                                                                <div class="card-header">
                                                                                    <h5 class="card-title mb-0">Descripción Detallada</h5>
                                                                                </div>
                                                                                <div class="card-body">
                                                                                    <p class="card-text" style="white-space: pre-line;"><?php echo htmlspecialchars($productos_dato['descripcion']); ?></p>
                                                                                </div>
                                                                                <div class="card-footer bg-light">
                                                                                    <div class="d-flex justify-content-between">
                                                                                        <span><b>Código:</b> <?php echo htmlspecialchars($productos_dato['codigo']); ?></span>
                                                                                        <span><b>Precio:</b> $<?php echo number_format($productos_dato['precio_venta'], 2); ?></span>
                                                                                        <span><b>Stock:</b> <?php echo $stock_actual; ?></span>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                        <div class="modal-footer">
                                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                                                            <?php if ($stock_actual > 0): ?>
                                                                            <button type="button" class="btn btn-primary" onclick="$('#btn_seleccionar<?php echo $id_producto;?>').click(); $('#modal-descripcion<?php echo $id_producto;?>').modal('hide');">
                                                                                <i class="fas fa-cart-plus"></i> Seleccionar este producto
                                                                            </button>
                                                                            <?php endif; ?>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        <?php } ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                            
                                            <!-- Formulario para añadir al carrito -->
                                            <div class="row mt-4 productos-seleccionados">
                                                <div class="col-md-12">
                                                    <div class="alert alert-info">
                                                        <h5><i class="fas fa-info-circle mr-2"></i> <strong>Datos del producto seleccionado</strong></h5>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <input type="hidden" id="id_producto">
                                                        <input type="hidden" id="stock_actual">
                                                        <label><i class="fas fa-box mr-1"></i> Producto:</label>
                                                        <input type="text" id="producto" class="form-control" disabled>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label><i class="fas fa-file-alt mr-1"></i> Descripción:</label>
                                                        <div class="input-group">
                                                            <input type="text" id="descripcion" class="form-control" disabled>
                                                            <div class="input-group-append">
                                                                <button class="btn btn-outline-secondary" type="button" id="btn_ver_descripcion">
                                                                    <i class="fas fa-search"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-1">
                                                    <div class="form-group">
                                                        <label><i class="fas fa-sort-numeric-up mr-1"></i> Cantidad:</label>
                                                        <input type="number" id="cantidad" min="1" value="1" class="form-control">
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label><i class="fas fa-tag mr-1"></i> Precio unitario:</label>
                                                        <input type="text" id="precio_venta" class="form-control" disabled>
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label><i class="fas fa-percent mr-1"></i> <b>IVA (%):</b></label>
                                                        <input type="number" id="porcentaje_iva" min="0" step="0.01" 
                                                               value="<?php echo $iva_predeterminado; ?>" 
                                                               class="form-control" style="font-weight: bold; color: #2c61a3;">
                                                    </div>
                                                </div>
                                                <div class="col-md-1">
                                                    <div class="form-group">
                                                        <label>&nbsp;</label>
                                                        <button type="button" id="btn_registrar_carrito" class="btn btn-primary btn-block btn-lg">
                                                            <i class="fa fa-cart-plus"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Modal para ver la descripción completa desde el formulario -->
                                            <div class="modal fade" id="modal-descripcion-completa" tabindex="-1" role="dialog" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header" style="background-color: #343a40; color: white">
                                                            <h5 class="modal-title">
                                                                <i class="fas fa-file-alt mr-2"></i> Descripción Detallada
                                                            </h5>
                                                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <h4 class="text-center mb-3" id="modal-nombre-producto"></h4>
                                                            <div class="card">
                                                                <div class="card-body">
                                                                    <p class="card-text" id="modal-descripcion-texto" style="white-space: pre-line;"></p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="alert alert-warning mt-3">
                                                <i class="fas fa-exclamation-triangle mr-2"></i>
                                                <strong>Importante:</strong> El valor del IVA debe ser mayor o igual a 0. Este valor se recordará para cada producto específico en futuras selecciones.
                                            </div>

                                            <script>
                                                // Variable para almacenar los últimos valores de IVA por producto
                                                var ultimosIVA = <?php echo json_encode($_SESSION['ultimo_iva_por_producto']); ?>;
                                                
                                                // Mostrar descripción completa al hacer clic en el botón
                                                $('#btn_ver_descripcion').click(function() {
                                                    var descripcion = $('#descripcion').val();
                                                    var nombre = $('#producto').val();
                                                    
                                                    if (!descripcion || !nombre) {
                                                        Swal.fire({
                                                            icon: 'warning',
                                                            title: 'No hay producto seleccionado',
                                                            text: 'Por favor, seleccione un producto primero'
                                                        });
                                                        return;
                                                    }
                                                    
                                                    $('#modal-nombre-producto').text(nombre);
                                                    $('#modal-descripcion-texto').text(descripcion);
                                                    $('#modal-descripcion-completa').modal('show');
                                                });
                                                
                                                $('#btn_registrar_carrito').click(function() {
                                                    var id_producto = $('#id_producto').val();
                                                    var cantidad = $('#cantidad').val();
                                                    var stock_actual = $('#stock_actual').val();
                                                    var porcentaje_iva = $('#porcentaje_iva').val();
                                                    
                                                    // Validar selección de producto
                                                    if (id_producto == "") {
                                                        Swal.fire({
                                                            icon: 'warning',
                                                            title: 'Seleccione un producto',
                                                            text: 'Debe seleccionar un producto antes de agregar al carrito'
                                                        });
                                                        return;
                                                    }
                                                    
                                                    // Validar cantidad
                                                    if (cantidad == "" || cantidad <= 0) {
                                                        Swal.fire({
                                                            icon: 'warning',
                                                            title: 'Cantidad inválida',
                                                            text: 'Ingrese una cantidad mayor a cero'
                                                        });
                                                        return;
                                                    }
                                                    
                                                    // Validar stock
                                                    if (parseInt(cantidad) > parseInt(stock_actual)) {
                                                        Swal.fire({
                                                            icon: 'error',
                                                            title: 'Stock insuficiente',
                                                            text: 'Solo hay ' + stock_actual + ' unidades disponibles'
                                                        });
                                                        return;
                                                    }
                                                    
                                                    // Validar IVA (ahora permitimos 0 o mayor)
                                                    if (porcentaje_iva == "" || porcentaje_iva < 0) {
                                                        Swal.fire({
                                                            icon: 'warning',
                                                            title: 'IVA inválido',
                                                            text: 'El porcentaje de IVA debe ser mayor o igual a 0'
                                                        });
                                                        return;
                                                    }
                                                    
                                                    // Mostrar spinner mientras se procesa
                                                    $('#btn_registrar_carrito').html('<i class="fas fa-spinner fa-spin"></i>');
                                                    $('#btn_registrar_carrito').prop('disabled', true);
                                                    
                                                    // Almacenar el valor del IVA para este producto específico
                                                    ultimosIVA[id_producto] = porcentaje_iva;
                                                    
                                                    $.post("../app/controllers/ventas/agregar_al_carrito.php",
                                                        { 
                                                            id_producto: id_producto, 
                                                            cantidad: cantidad,
                                                            porcentaje_iva: porcentaje_iva
                                                        },
                                                        function(response) {
                                                            if (response.startsWith("ERROR")) {
                                                                Swal.fire({
                                                                    icon: 'error',
                                                                    title: 'Error',
                                                                    text: response.substring(6)
                                                                });
                                                                $('#btn_registrar_carrito').html('<i class="fa fa-cart-plus"></i>');
                                                                $('#btn_registrar_carrito').prop('disabled', false);
                                                            } else {
                                                                // Guardar el último porcentaje de IVA usado para este producto
                                                                $.post("../app/controllers/ventas/guardar_iva_producto.php",
                                                                    { 
                                                                        id_producto: id_producto,
                                                                        porcentaje_iva: porcentaje_iva 
                                                                    }
                                                                );
                                                                
                                                                // Recargar la tabla del carrito
                                                                $('#carrito_contenido').load('carrito_tabla.php?nocache=' + new Date().getTime(), function() {
                                                                    // Actualizar el campo correcto con el valor del total del carrito
                                                                    var nuevoTotal = $('#total_carrito').text();
                                                                    $('#total_a_cancelar').val(nuevoTotal);
                                                                    
                                                                    // Limpiar campos y cerrar modal
                                                                    $('#id_producto').val('');
                                                                    $('#producto').val('');
                                                                    $('#descripcion').val('');
                                                                    $('#precio_venta').val('');
                                                                    $('#cantidad').val('');
                                                                    $('#stock_actual').val('');
                                                                    $('#modal-buscar_producto').modal('hide');
                                                                    
                                                                    // Restaurar botón y mostrar notificación
                                                                    $('#btn_registrar_carrito').html('<i class="fa fa-cart-plus"></i>');
                                                                    $('#btn_registrar_carrito').prop('disabled', false);
                                                                    
                                                                    Swal.fire({
                                                                        icon: 'success',
                                                                        title: 'Producto agregado',
                                                                        text: 'El producto se agregó correctamente al carrito',
                                                                        showConfirmButton: false,
                                                                        timer: 1500
                                                                    });
                                                                });
                                                            }
                                                        }
                                                    );
                                                });

                                                // También permitir añadir con Enter en el campo cantidad
                                                $('#cantidad').on('keypress', function(e) {
                                                    if (e.which === 13) {
                                                        e.preventDefault();
                                                        $('#btn_registrar_carrito').click();
                                                    }
                                                });

                                                // Lo mismo para el campo de IVA
                                                $('#porcentaje_iva').on('keypress', function(e) {
                                                    if (e.which === 13) {
                                                        e.preventDefault();
                                                        $('#btn_registrar_carrito').click();
                                                    }
                                                });
                                            </script>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-3" id="carrito_contenido">
                                <?php include 'carrito_tabla.php'; ?>
                            </div>
                       </div>
                   </div>
               </div>
           </div>

            <!-- Resto del código permanece igual -->
            <!-- Datos del cliente y registro de venta -->
            <div class="row">
                <div class="col-md-9">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fa fa-user-check"></i> Datos del Cliente</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <button type="button" class="btn btn-primary btn-lg" data-toggle="modal"
                                    data-target="#modal-buscar_cliente">
                                <i class="fa fa-search"></i> Buscar Cliente
                            </button>
                            
                            <!-- Modal para buscar cliente -->
                            <div class="modal fade" id="modal-buscar_cliente">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header" style="background-color: #2c61a3; color: white">
                                            <h4 class="modal-title"><i class="fas fa-users mr-2"></i> Seleccionar Cliente</h4>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="table-responsive">
                                                <table id="tabla_clientes" class="table table-bordered table-striped table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>#</th>
                                                            <th>Acción</th>
                                                            <th>Nombre</th>
                                                            <th>NIT/CI</th>
                                                            <th>Celular</th>
                                                            <th>Email</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        $contador_clientes = 0;
                                                        foreach ($clientes_datos as $clientes_dato){
                                                            $id_cliente = $clientes_dato['id_cliente'];
                                                            ?>
                                                            <tr>
                                                                <td><?php echo ++$contador_clientes; ?></td>
                                                                <td>
                                                                    <button id="btn_seleccionar_cliente<?php echo $id_cliente;?>" class="btn btn-info btn-sm">
                                                                        <i class="fas fa-check-circle"></i> Seleccionar
                                                                    </button>
                                                                    <script>
                                                                        $('#btn_seleccionar_cliente<?php echo $id_cliente;?>').click(function(){
                                                                            $('#id_cliente').val('<?php echo $clientes_dato['id_cliente'];?>');
                                                                            $('#nombre_cliente').val('<?php echo htmlspecialchars($clientes_dato['nombre_cliente']);?>');
                                                                            $('#nit_ci_cliente').val('<?php echo htmlspecialchars($clientes_dato['nit_ci_cliente']);?>');
                                                                            $('#celular_cliente').val('<?php echo htmlspecialchars($clientes_dato['celular_cliente']);?>');
                                                                            $('#email_cliente').val('<?php echo htmlspecialchars($clientes_dato['email_cliente']);?>');
                                                                            $('#modal-buscar_cliente').modal('hide');
                                                                            
                                                                            // Mostrar alerta de éxito
                                                                            Swal.fire({
                                                                                icon: 'success',
                                                                                title: 'Cliente seleccionado',
                                                                                text: 'Cliente seleccionado correctamente',
                                                                                showConfirmButton: false,
                                                                                timer: 1500
                                                                            });

                                                                            // Resaltar brevemente los campos del cliente
                                                                            $('.cliente-info').addClass('highlight-selection');
                                                                            setTimeout(function() {
                                                                                $('.cliente-info').removeClass('highlight-selection');
                                                                            }, 1000);
                                                                        });
                                                                    </script>
                                                                </td>
                                                                <td><?php echo $clientes_dato['nombre_cliente']; ?></td>
                                                                <td><?php echo $clientes_dato['nit_ci_cliente']; ?></td>
                                                                <td><?php echo $clientes_dato['celular_cliente']; ?></td>
                                                                <td><?php echo $clientes_dato['email_cliente']; ?></td>
                                                            </tr>
                                                        <?php } ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-danger" data-dismiss="modal">
                                                <i class="fas fa-times"></i> Cerrar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mt-3 cliente-info">
                                <div class="col-md-12">
                                    <div class="alert alert-info">
                                        <i class="fas fa-user mr-2"></i> <strong>Datos del cliente seleccionado:</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="row cliente-info">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <input type="hidden" id="id_cliente">
                                        <label><i class="fas fa-user-tag mr-1"></i> Nombre del cliente:</label>
                                        <input type="text" class="form-control" id="nombre_cliente" readonly>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label><i class="fas fa-id-card mr-1"></i> NIT/CI del cliente:</label>
                                        <input type="text" class="form-control" id="nit_ci_cliente" readonly>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label><i class="fas fa-mobile-alt mr-1"></i> Celular del cliente:</label>
                                        <input type="text" class="form-control" id="celular_cliente" readonly>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label><i class="fas fa-envelope mr-1"></i> Correo del cliente:</label>
                                        <input type="text" class="form-control" id="email_cliente" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Registro de venta -->
                <div class="col-md-3">
                    <div class="card card-outline card-success">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fa fa-cash-register"></i> Finalizar Venta</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <form action="../app/controllers/ventas/finalizar_venta.php" method="POST" id="form_finalizar_venta">
                                <div class="form-group">
                                    <label><i class="fas fa-money-bill-wave mr-1"></i> Monto Total:</label>
                                    <input type="text" class="form-control form-control-lg" 
                                           style="text-align: center; font-weight: bold; background-color:#eef475; font-size: 24px;" 
                                           id="total_a_cancelar"
                                           value="<?php
                                                // Calcular precio_total actual
                                                $precio_total = 0;
                                                foreach ($carrito_datos as $carrito_dato) {
                                                    $precio_total += $carrito_dato['cantidad'] * $carrito_dato['precio_venta'];
                                                }
                                                echo number_format($precio_total,2);
                                           ?>" readonly>
                                </div>
                                <input type="hidden" name="id_cliente" id="id_cliente_hidden">
                                <button type="submit" class="btn btn-success btn-lg btn-block" id="btn_finalizar_venta" style="font-size: 18px;">
                                    <i class="fas fa-check-circle"></i> FINALIZAR VENTA
                                </button>
                            </form>
                            <script>
                               $('#form_finalizar_venta').submit(function(e) {
                                    // Obtener el valor del span que muestra el total del carrito
                                    var total_carrito = parseFloat($('#total_carrito').text().replace(/[^\d.-]/g, ''));
                                    
                                    // Verificar si hay productos en el carrito
                                    if (total_carrito <= 0) {
                                        e.preventDefault();
                                        Swal.fire({
                                            icon: 'warning',
                                            title: 'Carrito vacío',
                                            text: 'Debe agregar al menos un producto al carrito'
                                        });
                                        return false;
                                    }
                                    
                                    // Verificar si se ha seleccionado un cliente
                                    if (!$('#id_cliente').val()) {
                                        e.preventDefault();
                                        Swal.fire({
                                            icon: 'warning',
                                            title: 'Cliente no seleccionado',
                                            text: 'Debe seleccionar un cliente antes de finalizar la venta'
                                        });
                                        return false;
                                    }
                                    
                                    // Transferir ID del cliente y deshabilitar botón
                                    $('#id_cliente_hidden').val($('#id_cliente').val());
                                    $('#btn_finalizar_venta').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> PROCESANDO...');
                                    
                                    return true;
                                });
                            </script>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<?php include('../layout/mensajes.php'); ?>
<?php include('../layout/parte2.php'); ?>

<style>
/* Estilos personalizados */
.highlight-selection {
    animation: highlight-pulse 1s ease;
}

@keyframes highlight-pulse {
    0% { box-shadow: 0 0 0 0 rgba(44, 97, 163, 0.4); }
    70% { box-shadow: 0 0 0 10px rgba(44, 97, 163, 0); }
    100% { box-shadow: 0 0 0 0 rgba(44, 97, 163, 0); }
}

.btn-primary {
    background-color: #2c61a3;
    border-color: #2c61a3;
}

.btn-primary:hover {
    background-color: #1d4580;
    border-color: #1d4580;
}

.card-primary.card-outline {
    border-top: 3px solid #2c61a3;
}

.card-success.card-outline {
    border-top: 3px solid #28a745;
}

.table th {
    background-color: #f4f6f9;
    font-weight: 600;
}

#total_a_cancelar {
    font-family: 'Arial', sans-serif;
    font-size: 26px !important;
    letter-spacing: 1px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

#porcentaje_iva {
    border: 2px solid #2c61a3;
    font-weight: bold;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.form-control:focus {
    border-color: #2c61a3;
    box-shadow: 0 0 0 0.2rem rgba(44, 97, 163, 0.25);
}

/* Estilos para el botón descripción */
.btn-group .btn {
    margin-right: 3px;
}

/* Estilos para los modales de descripción */
.modal-header {
    border-radius: 3px 3px 0 0;
}

.modal-descripcion-texto {
    max-height: 300px;
    overflow-y: auto;
}

/* Limitar texto en celdas de la tabla */
table td {
    max-width: 200px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
</style>

<script>
    $(function () {
        // Configuración de DataTables para productos
        $("#tabla_productos").DataTable({
            "pageLength": 8,
            "language": {
                "emptyTable": "No hay productos disponibles",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ productos",
                "search": "Buscar:",
                "zeroRecords": "No se encontraron productos",
                "paginate": {
                    "first": "Primero",
                    "last": "Último",
                    "next": "Siguiente",
                    "previous": "Anterior"
                }
            },
            "responsive": true,
            "order": [[0, "asc"]]
        });
        
        // Configuración de DataTables para clientes
        $("#tabla_clientes").DataTable({
            "pageLength": 8,
            "language": {
                "emptyTable": "No hay clientes registrados",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ clientes",
                "search": "Buscar:",
                "zeroRecords": "No se encontraron clientes"
            },
            "responsive": true,
            "order": [[2, "asc"]]
        });
    });

    // Actualizar el total cuando cambia el carrito por cambios en IVA
    $(document).ready(function() {
        // Observador para detectar cambios en el contenido del carrito
        var carritoObserver = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                    // Esperar un poco a que se actualice completamente el DOM
                    setTimeout(function() {
                        var nuevoTotal = $('#total_carrito').text();
                        $('#total_a_cancelar').val(nuevoTotal);
                    }, 100);
                }
            });
        });
        
        // Configurar el observador para vigilar cambios en el carrito
        if (document.getElementById('carrito_contenido')) {
            var config = { childList: true, subtree: true };
            carritoObserver.observe(document.getElementById('carrito_contenido'), config);
            
            // También actualizar el total al cargar la página
            setTimeout(function() {
                var totalInicial = $('#total_carrito').text();
                if (totalInicial) {
                    $('#total_a_cancelar').val(totalInicial);
                }
            }, 200);
        }
        
        // Añadir un manejador de eventos para detectar cuando se actualiza el IVA
        $(document).on('iva_actualizado', function() {
            var nuevoTotal = $('#total_carrito').text();
            $('#total_a_cancelar').val(nuevoTotal);
        });
    });
</script>