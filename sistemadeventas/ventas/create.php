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
                    <h1 class="m-0">Ventas</h1>
                </div>
            </div>
        </div>
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">

            <?php if ($success_msg): ?>
                <div class="alert alert-success"><?php echo $success_msg; ?></div>
            <?php endif; ?>
            <?php if ($error_msg): ?>
                <div class="alert alert-danger"><?php echo $error_msg; ?></div>
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
                        <i class="fa fa-shopping-bag"></i> Venta Nro 
                        <input type="text" style="text-align: center;" value="<?php echo $contador_de_ventas + 1; ?>" disabled>
                    </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                       </div>
                       <div class="card-body">
                            <b>Carrito</b>
                            <button type="button" class="btn btn-primary" data-toggle="modal"
                                    data-target="#modal-buscar_producto">
                                <i class="fa fa-search"></i>
                                Buscar producto
                            </button>

                            <!-- Modal para buscar producto -->
                            <div class="modal fade" id="modal-buscar_producto">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header" style="background-color: #1d36b6;color: white">
                                            <h4 class="modal-title">Búsqueda de producto</h4>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="table-responsive">
                                                <table id="tabla_productos" class="table table-bordered table-striped table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>Nro</th>
                                                            <th>Seleccionar</th>
                                                            <th>Código</th>
                                                            <th>Categoría</th>
                                                            <th>Imagen</th>
                                                            <th>Nombre</th>
                                                            <th>Descripción</th>
                                                            <th>Stock</th>
                                                            <th>Precio compra</th>
                                                            <th>Precio venta</th>
                                                            <th>Fecha ingreso</th>
                                                            <th>Usuario</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        $contador = 0;
                                                        foreach ($productos_datos as $productos_dato){
                                                            $id_producto = $productos_dato['id_producto']; ?>
                                                            <tr>
                                                                <td><?php echo ++$contador; ?></td>
                                                                <td>
                                                                    <button class="btn btn-info" id="btn_seleccionar<?php echo $id_producto;?>">
                                                                        Seleccionar
                                                                    </button>
                                                                    <script>
                                                                        $('#btn_seleccionar<?php echo $id_producto;?>').click(function () {
                                                                            $('#id_producto').val('<?php echo $id_producto; ?>');
                                                                            $('#producto').val('<?php echo htmlspecialchars($productos_dato['nombre']); ?>');
                                                                            $('#descripcion').val('<?php echo htmlspecialchars($productos_dato['descripcion']); ?>');
                                                                            $('#precio_venta').val('<?php echo $productos_dato['precio_venta']; ?>');
                                                                            $('#cantidad').focus();
                                                                        });
                                                                    </script>
                                                                </td>
                                                                <td><?php echo $productos_dato['codigo'];?></td>
                                                                <td><?php echo $productos_dato['categoria'];?></td>
                                                                <td>
                                                                    <img src="<?php echo $URL."/almacen/img_productos/".$productos_dato['imagen'];?>" width="50px" alt="img">
                                                                </td>
                                                                <td><?php echo $productos_dato['nombre'];?></td>
                                                                <td><?php echo $productos_dato['descripcion'];?></td>
                                                                <td><?php echo $productos_dato['stock'];?></td>
                                                                <td><?php echo $productos_dato['precio_compra'];?></td>
                                                                <td><?php echo $productos_dato['precio_venta'];?></td>
                                                                <td><?php echo $productos_dato['fecha_ingreso'];?></td>
                                                                <td><?php echo $productos_dato['email'];?></td>
                                                            </tr>
                                                        <?php } ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <input type="hidden" id="id_producto">
                                                        <label>Producto</label>
                                                        <input type="text" id="producto" class="form-control" disabled>
                                                    </div>
                                                </div>
                                                <div class="col-md-5">
                                                    <div class="form-group">
                                                        <label>Descripción</label>
                                                        <input type="text" id="descripcion" class="form-control" disabled>
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label>Cantidad</label>
                                                        <input type="number" id="cantidad" min="1" class="form-control">
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label>Precio unitario</label>
                                                        <input type="text" id="precio_venta" class="form-control" disabled>
                                                    </div>
                                                </div>
                                            </div>
                                            <button style="float: right;" id="btn_registrar_carrito" class="btn btn-primary">Agregar al carrito</button>
                                            <div id="respuesta_carrito"></div>
                                            <script>
                                                $('#btn_registrar_carrito').click(function(){
                                                    var id_producto= $('#id_producto').val();
                                                    var cantidad= $('#cantidad').val();
                                                    if(id_producto==""){
                                                        alert("Debe seleccionar un producto.");
                                                    }else if(cantidad=="" || cantidad <= 0){
                                                        alert("Debe ingresar una cantidad válida.");
                                                    }else{
                                                        $.post("../app/controllers/ventas/agregar_al_carrito.php",
                                                            {id_producto:id_producto, cantidad:cantidad},
                                                            function (datos) {
                                                              // Recarga la tabla y luego actualiza el monto
                                                        $('#carrito_contenido').load('carrito_tabla.php?nocache=' + new Date().getTime(), function() {
                                                            var nuevoTotal = $('#total_carrito').text();
                                                            $('#monto_total').text(nuevoTotal);
                                                        });
                                                        // Limpiar los campos y cerrar modal
                                                        $('#id_producto').val('');
                                                        $('#producto').val('');
                                                        $('#descripcion').val('');
                                                        $('#precio_venta').val('');
                                                        $('#cantidad').val('');
                                                        $('#modal-buscar_producto').modal('hide');
                                                            }
                                                        );
                                                    }
                                                });
                                            </script>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <br><br>
                            <div id="carrito_contenido">
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
                            <h3 class="card-title"><i class="fa fa-user-check"></i> Datos del cliente</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <b>Cliente</b>
                            <button type="button" class="btn btn-primary" data-toggle="modal"
                                    data-target="#modal-buscar_cliente">
                                <i class="fa fa-search"></i>
                                Buscar cliente
                            </button>
                            <!-- Modal para buscar cliente -->
                            <div class="modal fade" id="modal-buscar_cliente">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header" style="background-color: #1d36b6;color: white">
                                            <h4 class="modal-title">Búsqueda de cliente</h4>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="table-responsive">
                                                <table id="tabla_clientes" class="table table-bordered table-striped table-sm">
                                                    <thead>
                                                        <tr>
                                                            <th>Nro</th>
                                                            <th>Seleccionar</th>
                                                            <th>Nombre</th>
                                                            <th>Nit/CI</th>
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
                                                                    <button id="btn_seleccionar_cliente<?php echo $id_cliente;?>" class="btn btn-info">Seleccionar</button>
                                                                    <script>
                                                                        $('#btn_seleccionar_cliente<?php echo $id_cliente;?>').click(function(){
                                                                            $('#id_cliente').val('<?php echo $clientes_dato['id_cliente'];?>');
                                                                            $('#nombre_cliente').val('<?php echo htmlspecialchars($clientes_dato['nombre_cliente']);?>');
                                                                            $('#nit_ci_cliente').val('<?php echo htmlspecialchars($clientes_dato['nit_ci_cliente']);?>');
                                                                            $('#celular_cliente').val('<?php echo htmlspecialchars($clientes_dato['celular_cliente']);?>');
                                                                            $('#email_cliente').val('<?php echo htmlspecialchars($clientes_dato['email_cliente']);?>');
                                                                            $('#modal-buscar_cliente').modal('hide');
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
                                    </div>
                                </div>
                            </div>
                            <br><br>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <input type="hidden" name="id_cliente" id="id_cliente">
                                        <label>Nombre del cliente</label>
                                        <input type="text" class="form-control" id="nombre_cliente" disabled>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Nit/Ci del cliente</label>
                                        <input type="text" class="form-control" id="nit_ci_cliente" disabled>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Celular del cliente</label>
                                        <input type="text" class="form-control" id="celular_cliente" disabled>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Correo del cliente</label>
                                        <input type="text" class="form-control" id="email_cliente" disabled>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Registro de venta -->
                <div class="col-md-3">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fa fa-shopping-basket"></i> Registrar venta</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <form action="../app/controllers/ventas/finalizar_venta.php" method="POST" id="form_finalizar_venta">
                                <div class="form-group">
                                    <label>Monto a cancelar</label>
                                    <input type="text" class="form-control" style="text-align: center; background-color:#eef475" id="total_a_cancelar"
                                    value="<?php
                                    // Calcular precio_total actual
                                    $precio_total = 0;
                                    foreach ($carrito_datos as $carrito_dato) {
                                        $precio_total += $carrito_dato['cantidad'] * $carrito_dato['precio_venta'];
                                    }
                                    echo number_format($precio_total,2);
                                    
                                    ?>" disabled>
                                </div>
                                <input type="hidden" name="id_cliente" id="id_cliente_hidden">
                                <button type="submit" class="btn btn-success btn-block">Finalizar venta</button>
                            </form>
                            <script>
                                $('#form_finalizar_venta').submit(function(e){
                                    // Validar que el cliente esté seleccionado
                                    if (!$('#id_cliente').val()) {
                                        alert('Debe seleccionar un cliente antes de finalizar la venta');
                                        e.preventDefault();
                                    } else {
                                        $('#id_cliente_hidden').val($('#id_cliente').val());
                                    }
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
        $("#tabla_productos").DataTable({
            "pageLength": 5,
            "language": {
                "emptyTable": "No hay información",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ Productos",
                "infoEmpty": "Mostrando 0 a 0 de 0 Productos",
                "infoFiltered": "(Filtrado de _MAX_ total Productos)",
                "lengthMenu": "Mostrar _MENU_ Productos",
                "loadingRecords": "Cargando...",
                "processing": "Procesando...",
                "search": "Buscador:",
                "zeroRecords": "Sin resultados encontrados",
                "paginate": {
                    "first": "Primero",
                    "last": "Ultimo",
                    "next": "Siguiente",
                    "previous": "Anterior"
                }
            },
            "responsive": true, "lengthChange": true, "autoWidth": false,
        });
        $("#tabla_clientes").DataTable({
            "pageLength": 5,
            "language": {
                "emptyTable": "No hay información",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ Clientes",
                "infoEmpty": "Mostrando 0 a 0 de 0 Clientes",
                "infoFiltered": "(Filtrado de _MAX_ total Clientes)",
                "lengthMenu": "Mostrar _MENU_ Clientes",
                "loadingRecords": "Cargando...",
                "processing": "Procesando...",
                "search": "Buscador:",
                "zeroRecords": "Sin resultados encontrados",
                "paginate": {
                    "first": "Primero",
                    "last": "Ultimo",
                    "next": "Siguiente",
                    "previous": "Anterior"
                }
            },
            "responsive": true, "lengthChange": true, "autoWidth": false,
        });
    });
</script>