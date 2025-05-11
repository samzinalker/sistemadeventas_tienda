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
                            <b>Productos en Carrito</b>
                            <button type="button" class="btn btn-primary" data-toggle="modal"
                                    data-target="#modal-buscar_producto">
                                <i class="fa fa-plus"></i> Agregar Producto
                            </button>

                            <!-- Modal para buscar producto (sólo muestra los productos del usuario) -->
                            <div class="modal fade" id="modal-buscar_producto">
                                <div class="modal-dialog modal-xl">
                                    <div class="modal-content">
                                        <div class="modal-header" style="background-color: #1d36b6;color: white">
                                            <h4 class="modal-title">Seleccionar Producto</h4>
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
                                                            ?>
                                                            <tr>
                                                                <td><?php echo ++$contador; ?></td>
                                                                <td>
                                                                    <button class="btn btn-info btn-sm" id="btn_seleccionar<?php echo $id_producto;?>" 
                                                                            <?php if ($stock_actual <= 0): ?>
                                                                                disabled title="Sin stock disponible"
                                                                            <?php endif; ?>>
                                                                        <i class="fas fa-check-circle"></i> Seleccionar
                                                                    </button>
                                                                    <script>
                                                                        $('#btn_seleccionar<?php echo $id_producto;?>').click(function() {
                                                                            $('#id_producto').val('<?php echo $id_producto; ?>');
                                                                            $('#producto').val('<?php echo htmlspecialchars($productos_dato['nombre']); ?>');
                                                                            $('#descripcion').val('<?php echo htmlspecialchars($productos_dato['descripcion']); ?>');
                                                                            $('#precio_venta').val('<?php echo $productos_dato['precio_venta']; ?>');
                                                                            $('#stock_actual').val('<?php echo $stock_actual; ?>');
                                                                            $('#cantidad').val(1);
                                                                            $('#cantidad').focus();
                                                                            $('#cantidad').select();
                                                                        });
                                                                    </script>
                                                                </td>
                                                                <td><?php echo $productos_dato['codigo'];?></td>
                                                                <td><?php echo $productos_dato['categoria'];?></td>
                                                                <td>
                                                                    <img src="<?php echo $URL."/almacen/img_productos/".$productos_dato['imagen'];?>" 
                                                                         width="50px" alt="Imagen producto">
                                                                </td>
                                                                <td><?php echo $productos_dato['nombre'];?></td>
                                                                <td><?php echo $productos_dato['descripcion'];?></td>
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
                                                        <?php } ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                            
                                            <!-- Formulario para añadir al carrito -->
                                            <div class="row mt-3">
                                                <div class="col-md-12">
                                                    <div class="alert alert-info">
                                                        <strong>Datos del producto seleccionado:</strong>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <input type="hidden" id="id_producto">
                                                        <input type="hidden" id="stock_actual">
                                                        <label>Producto:</label>
                                                        <input type="text" id="producto" class="form-control" disabled>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>Descripción:</label>
                                                        <input type="text" id="descripcion" class="form-control" disabled>
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label>Cantidad:</label>
                                                        <input type="number" id="cantidad" min="1" value="1" class="form-control">
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label>Precio unitario:</label>
                                                        <input type="text" id="precio_venta" class="form-control" disabled>
                                                    </div>
                                                </div>
                                                <div class="col-md-1">
                                                    <div class="form-group">
                                                        <label>&nbsp;</label>
                                                        <button type="button" id="btn_registrar_carrito" class="btn btn-primary btn-block">
                                                            <i class="fa fa-plus"></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <script>
                                                $('#btn_registrar_carrito').click(function() {
                                                    var id_producto = $('#id_producto').val();
                                                    var cantidad = $('#cantidad').val();
                                                    var stock_actual = $('#stock_actual').val();
                                                    
                                                    if (id_producto == "") {
                                                        Swal.fire({
                                                            icon: 'warning',
                                                            title: 'Seleccione un producto',
                                                            text: 'Debe seleccionar un producto antes de agregar al carrito'
                                                        });
                                                        return;
                                                    }
                                                    
                                                    if (cantidad == "" || cantidad <= 0) {
                                                        Swal.fire({
                                                            icon: 'warning',
                                                            title: 'Cantidad inválida',
                                                            text: 'Ingrese una cantidad mayor a cero'
                                                        });
                                                        return;
                                                    }
                                                    
                                                    if (parseInt(cantidad) > parseInt(stock_actual)) {
                                                        Swal.fire({
                                                            icon: 'error',
                                                            title: 'Stock insuficiente',
                                                            text: 'Solo hay ' + stock_actual + ' unidades disponibles'
                                                        });
                                                        return;
                                                    }
                                                    
                                                    // Mostrar spinner mientras se procesa
                                                    $('#btn_registrar_carrito').html('<i class="fas fa-spinner fa-spin"></i>');
                                                    $('#btn_registrar_carrito').prop('disabled', true);
                                                    
                                                    $.post("../app/controllers/ventas/agregar_al_carrito.php",
                                                        { id_producto: id_producto, cantidad: cantidad },
                                                        function(response) {
                                                            if (response.startsWith("ERROR")) {
                                                                Swal.fire({
                                                                    icon: 'error',
                                                                    title: 'Error',
                                                                    text: response.substring(6)
                                                                });
                                                                $('#btn_registrar_carrito').html('<i class="fa fa-plus"></i>');
                                                                $('#btn_registrar_carrito').prop('disabled', false);
                                                            } else {
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
                                                                    $('#btn_registrar_carrito').html('<i class="fa fa-plus"></i>');
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
                            <button type="button" class="btn btn-primary" data-toggle="modal"
                                    data-target="#modal-buscar_cliente">
                                <i class="fa fa-search"></i> Buscar Cliente
                            </button>
                            
                            <!-- Modal para buscar cliente -->
                            <div class="modal fade" id="modal-buscar_cliente">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header" style="background-color: #1d36b6;color: white">
                                            <h4 class="modal-title">Seleccionar Cliente</h4>
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
                            
                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <div class="alert alert-info">
                                        <strong>Datos del cliente seleccionado:</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <input type="hidden" id="id_cliente">
                                        <label>Nombre del cliente:</label>
                                        <input type="text" class="form-control" id="nombre_cliente" readonly>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>NIT/CI del cliente:</label>
                                        <input type="text" class="form-control" id="nit_ci_cliente" readonly>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Celular del cliente:</label>
                                        <input type="text" class="form-control" id="celular_cliente" readonly>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Correo del cliente:</label>
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
                                    <label>Monto Total:</label>
                                    <input type="text" class="form-control form-control-lg" 
                                           style="text-align: center; font-weight: bold; background-color:#eef475" 
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
                                <button type="submit" class="btn btn-success btn-lg btn-block" id="btn_finalizar_venta">
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
</script>