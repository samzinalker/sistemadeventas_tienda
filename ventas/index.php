<?php
include ('../app/config.php');
include ('../layout/sesion.php');
include ('../layout/parte1.php');
include ('../app/controllers/ventas/listado_de_ventas.php');
include ('../app/controllers/clientes/listado_de_clientes.php');

// Verificar que solo se muestran ventas del usuario actual
$id_usuario_actual = $_SESSION['id_usuario'];
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="m-0">Mis Ventas Realizadas</h1>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">

            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Ventas registradas</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>

                        <div class="card-body" style="display: block;">
                            <div class="table table-responsive">
                                <table id="example1" class="table table-bordered table-striped table-sm">
                                    <thead>
                                    <tr>
                                        <th><center>Nro</center></th>
                                        <th><center>Nro de venta</center></th>
                                        <th><center>Fecha</center></th>
                                        <th><center>Cliente</center></th>
                                        <th><center>Productos</center></th>
                                        <th><center>Total pagado</center></th>
                                        <th><center>Acciones</center></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $contador = 0;
                                    foreach ($ventas_datos as $ventas_dato){
                                        $id_venta = $ventas_dato['id_venta'];
                                        $nro_venta = $ventas_dato['nro_venta'];
                                        $contador= $contador + 1;
                                        $fecha_venta = date('d/m/Y H:i', strtotime($ventas_dato['fyh_creacion']));
                                    ?>
                                    <tr>
                                        <td><center><?php echo $contador ?></center></td>
                                        <td><center><?php echo $nro_venta; ?></center></td>
                                        <td><center><?php echo $fecha_venta; ?></center></td>
                                        <td><center><?php echo $ventas_dato['nombre_cliente']; ?></center></td>
                                        <td>
                                            <center>
                                            <!-- Botón para abrir modal con productos -->
                                            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#Modal_productos<?php echo $id_venta; ?>">
                                                <i class="fa fa-shopping-basket"></i> Ver Productos
                                            </button>

                                            <!-- Modal productos -->
                                            <div class="modal fade" id="Modal_productos<?php echo $id_venta; ?>" tabindex="-1" role="dialog" aria-hidden="true">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header" style="background-color: #88ffff;">
                                                            <h5 class="modal-title">Productos de la venta #<?php echo $nro_venta; ?></h5>
                                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                <span aria-hidden="true">&times;</span>
                                                            </button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="table-responsive">
                                                                <table class="table table-bordered table-sm table-hover table-striped">
                                                                    <thead>
                                                                        <tr>
                                                                            <th style="background-color:#4d66ca;text-align:center">Nro</th>
                                                                            <th style="background-color:#4d66ca;text-align:center">Producto</th>
                                                                            <th style="background-color:#4d66ca;text-align:center">Descripción</th>
                                                                            <th style="background-color:#4d66ca;text-align:center">Cantidad</th>
                                                                            <th style="background-color:#4d66ca;text-align:center">Precio</th>
                                                                            <th style="background-color:#4d66ca;text-align:center">Subtotal</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                    <?php 
                                                                    $contador_de_carrito = 0;
                                                                    $cantidad_total = 0;
                                                                    $precio_unitario_total = 0;
                                                                    $precio_total = 0;

                                                                    // Consultar productos en esta venta
                                                                    $sql_carrito = "SELECT c.*, 
                                                                                  p.nombre as nombre_producto, 
                                                                                  p.descripcion as descripcion, 
                                                                                  p.precio_venta as precio_venta
                                                                            FROM tb_carrito c
                                                                            INNER JOIN tb_almacen p ON c.id_producto = p.id_producto
                                                                            WHERE c.nro_venta = :nro_venta 
                                                                            AND c.id_usuario = :id_usuario
                                                                            ORDER BY c.id_carrito ASC";
                                                                    $query_carrito = $pdo->prepare($sql_carrito);
                                                                    $query_carrito->bindParam(':nro_venta', $nro_venta, PDO::PARAM_INT);
                                                                    $query_carrito->bindParam(':id_usuario', $id_usuario_actual, PDO::PARAM_INT);
                                                                    $query_carrito->execute();
                                                                    $carrito_datos = $query_carrito->fetchAll(PDO::FETCH_ASSOC);
                                                                    
                                                                    foreach ($carrito_datos as $carrito_dato) { 
                                                                        $contador_de_carrito++;
                                                                        $cantidad_total += $carrito_dato['cantidad'];
                                                                        $subtotal = $carrito_dato['cantidad'] * $carrito_dato['precio_venta'];
                                                                        $precio_total += $subtotal;
                                                                    ?>
                                                                        <tr>
                                                                            <td><center><?php echo $contador_de_carrito; ?></center></td>
                                                                            <td><?php echo $carrito_dato['nombre_producto']; ?></td>
                                                                            <td><?php echo $carrito_dato['descripcion']; ?></td>
                                                                            <td><center><?php echo $carrito_dato['cantidad']; ?></center></td>
                                                                            <td><center><?php echo number_format($carrito_dato['precio_venta'], 2); ?></center></td>
                                                                            <td><center><?php echo number_format($subtotal, 2); ?></center></td>
                                                                        </tr>
                                                                    <?php } ?>
                                                                    <!-- Fila de totales -->
                                                                    <tr>
                                                                        <th colspan="3" style="background-color:#aef77d;text-align:right">Total:</th>
                                                                        <th style="text-align:center"><?php echo $cantidad_total; ?></th>
                                                                        <th></th>
                                                                        <th style="background-color:#e0f933;text-align:center">
                                                                            <?php echo number_format($precio_total, 2); ?>
                                                                        </th>
                                                                    </tr>
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            </center>
                                        </td>
                                        <td><center><h5>$<?php echo number_format($ventas_dato['total_pagado'], 2); ?></h5></center></td>
                                        <td>
                                            <center>
                                                <div class="btn-group">
                                                    <a href="show.php?id_venta=<?php echo $id_venta; ?>" class="btn btn-info btn-sm">
                                                        <i class="fa fa-eye"></i> Ver
                                                    </a>
                                                    <a href="delete.php?id_venta=<?php echo $id_venta; ?>&nro_venta=<?php echo $nro_venta; ?>" 
                                                       class="btn btn-danger btn-sm" 
                                                       onclick="return confirm('¿Está seguro que desea eliminar esta venta?')">
                                                        <i class="fa fa-trash"></i> Borrar
                                                    </a>
                                                    <a href="factura.php?id_venta=<?php echo $id_venta; ?>&nro_venta=<?php echo $nro_venta; ?>" 
                                                       class="btn btn-success btn-sm" 
                                                       target="_blank">
                                                        <i class="fa fa-print"></i> Imprimir
                                                    </a>
                                                </div>
                                            </center>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include ('../layout/mensajes.php'); ?>
<?php include ('../layout/parte2.php'); ?>

<script>
    $(function () {
        $("#example1").DataTable({
            "pageLength": 10,
            "language": {
                "emptyTable": "No hay ventas registradas",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ Ventas",
                "infoEmpty": "Mostrando 0 a 0 de 0 Ventas",
                "infoFiltered": "(Filtrado de _MAX_ total Ventas)",
                "lengthMenu": "Mostrar _MENU_ Ventas",
                "search": "Buscar:",
                "zeroRecords": "Sin resultados encontrados",
                "paginate": {
                    "first": "Primero",
                    "last": "Último",
                    "next": "Siguiente",
                    "previous": "Anterior"
                }
            },
            "responsive": true,
            "ordering": true,
            "order": [[0, "desc"]],
            buttons: [{
                extend: 'collection',
                text: 'Reportes',
                orientation: 'landscape',
                buttons: [{
                    text: 'Copiar',
                    extend: 'copy',
                }, {
                    extend: 'pdf'
                },{
                    extend: 'csv'
                },{
                    extend: 'excel'
                },{
                    text: 'Imprimir',
                    extend: 'print'
                }]
            },
            {
                extend: 'colvis',
                text: 'Visor de columnas',
                collectionLayout: 'fixed three-column'
            }],
        }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
    });
</script>