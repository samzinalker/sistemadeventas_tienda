<?php
include ('../app/config.php');
include ('../layout/sesion.php');

include ('../layout/parte1.php');


include ('../app/controllers/ventas/listado_de_ventas.php');
include ('../app/controllers/clientes/listado_de_clientes.php');

?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="m-0">Listado de ventas realizadas</h1>
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
                            <h3 class="card-title">Ventas registrados</h3>
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
                                        <th><center>Producto</center></th>
                                        <th><center>Cliente</center></th>
                                        <th><center>Total pagado</center></th>
                                        <th><center>Acciones</center></th>
                                        
                                        
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $contador = 0;
                                    foreach ($ventas_datos as $ventas_dato){
                                        $id_venta = $ventas_dato['id_venta'];
                                        $contador= $contador + 1;
                                         ?>
                                        <tr>
                                             <td><center><?php echo $contador ?></center></td>
                                             <td><center><?php echo $ventas_dato['nro_venta']; ?></center></td>
                                             <td><center>
                                                

<!-- Button trigger modal -->
<button type="button" class="btn btn-primary" data-toggle="modal" data-target="#Modal_productos<?php echo $id_venta; ?>">
  <i class="fa fa-shopping-basket"></i>Productos
</button>

<!-- Modal -->
<div class="modal fade" id="Modal_productos<?php echo $id_venta; ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document"> <!-- Agregada clase modal-lg -->
    <div class="modal-content">
      <div class="modal-header" style="background-color: #88ffff;">
        <h5 class="modal-title" id="exampleModalLabel">Productos de la venta Nro <?php echo $ventas_dato['nro_venta']; ?></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      
      <div class="modal-body"> <!-- Aquí va tu tabla -->
       
      


      <div class="modal-body">
    <div class="table-responsive">
        <table class="table table-bordered table-sm table-hover table-striped">
            <thead>
                <tr>
                    <th style="background-color:#4d66ca;text-align: center">Nro</th>
                    <th style="background-color:#4d66ca;text-align: center">Productos</th>
                    <th style="background-color:#4d66ca;text-align: center">Descripción</th>
                    <th style="background-color:#4d66ca;text-align: center">Cantidad</th>
                    <th style="background-color:#4d66ca;text-align: center">Precio Unitario</th>
                    <th style="background-color:#4d66ca;text-align: center">Precio Subtotal</th>                    
                </tr>
            </thead>
            <tbody>
                <?php 
                $contador_de_carrito = 0;
                $cantidad_total = 0;
                $precio_unitario_total = 0;
                $precio_total = 0;

                $nro_venta = $ventas_dato['nro_venta'];
                $sql_carrito = "SELECT *, pro.nombre as nombre_producto, pro.descripcion as descripcion, pro.precio_venta as precio_venta, pro.stock as stock 
                              FROM tb_carrito AS carr 
                              INNER JOIN tb_almacen as pro 
                              ON carr.id_producto = pro.id_producto 
                              WHERE nro_venta = '$nro_venta' 
                              ORDER BY id_carrito ASC";
                
                $query_carrito = $pdo->prepare($sql_carrito);
                $query_carrito->execute();
                $carrito_datos = $query_carrito->fetchAll(PDO::FETCH_ASSOC);
                
                foreach ($carrito_datos as $carrito_dato) { 
                    $id_carrito = $carrito_dato['id_carrito'];
                    $contador_de_carrito++;
                    $cantidad_total += $carrito_dato['cantidad'];
                    $precio_unitario_total += floatval($carrito_dato['precio_venta']);
                ?>
                <tr>
                    <td>
                        <center>
                        <?php echo $contador_de_carrito; ?>
                        <input type="hidden" value="<?php echo $carrito_dato['id_producto']; ?>" id="id_producto<?php echo $contador_de_carrito; ?>">
                        </center>
                    </td>
                    <td>
                       <center>
                       <?php echo $carrito_dato['nombre_producto']; ?>
                       </center>
                    </td>
                    <td>
                        <center>
                        <?php echo $carrito_dato['descripcion']; ?>
                        </center>
                    </td>
                    <td id="cantidad_carrito<?php echo $contador_de_carrito; ?>">
                       <center>
                       <?php echo $carrito_dato['cantidad']; ?>
                        <input type="hidden" value="<?php echo $carrito_dato['stock']; ?>" id="stock_de_inventario<?php echo $contador_de_carrito; ?>">
                       </center>
                    </td>
                    <td>
                        <center>
                        <?php echo number_format($carrito_dato['precio_venta'], 2); ?>
                        </center>
                    </td>
                    <td>
                        <center><?php
                        $subtotal = $carrito_dato['cantidad'] * $carrito_dato['precio_venta'];
                        $precio_total += $subtotal;
                        echo number_format($subtotal, 2);
                        ?></center>
                    </td>
                   
                </tr>
                <?php } ?>
                <tr>
                    <th colspan="3" style="background-color:#aef77d; text-align: right; padding: 12px">Total</th>
                    <th style="background-color: #f8f9fa; text-align: center; padding: 12px"><?php echo $cantidad_total; ?></th>
                    <th style="background-color: #f8f9fa; text-align: center; padding: 12px"><?php echo number_format($precio_unitario_total, 2); ?></th>
                    <th style="background-color:#e0f933; text-align: center; padding: 12px"><?php echo number_format($precio_total, 2); ?></th>
                    <td style="padding: 12px"></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>  




      </div>

      
    </div>
  </div>
</div>






















                                             
                                             </td>
                                             <td><center>


 

                                                <!-- Button trigger modal -->
                                                <button type="button" class="btn btn-warning"
                                                 data-toggle="modal" data-target="#Modal_clientes<?php echo $id_venta; ?>">
                                                <i class="fa fa-shopping-basket"></i><?php echo $ventas_dato['nombre_cliente'] ?>
                                                </button>
                                                
                                                <!-- Modal -->
                                                                       <!-- modal para visualizar el formulario para agregar clientes -->
                                       <div class="modal fade" id="Modal_clientes<?php echo $id_venta; ?>">
                                           <div class="modal-dialog modal-sm">
                                               <div class="modal-content">
                                                   <div class="modal-header" style="background-color: #8080ff;color: white">
                                                       <h4 class="modal-title"> cliente</h4>
                                                       <div style="width: 10px;"></div>
                                                       <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                             <span aria-hidden="true"></span>
                                                       </button>
                                                   </div>

                                                 <?php 
                                                 $sql_cliente= "SELECT * FROM tb_clientes where id_cliente = 'id_cliente";
                                                 $query_clientes= $pdo->prepare($sql_clientes);
                                                 $query_clientes->execute();
                                                 $clientes_datos= $query_clientes->fetchAll(PDO::FETCH_ASSOC);
                                                 foreach($clientes_datos as $clientes_dato){
                                                    $nombre_cliente= $clientes_dato['nombre_cliente'];
                                                    $nit_ci_cliente= $clientes_dato['nit_ci_cliente'];
                                                    $celular_cliente= $clientes_dato['celular_cliente'];
                                                    $email_cliente= $clientes_dato['email_cliente'];
                                                 }
                                                ?>
                                                   <div class="modal-body">
                                                       
                                                       <div class="form-group">
                                                           <label for="">Nombre del cliente</label>
                                                           <input type="text" value="<?php echo $nombre_cliente; ?>" name="nombre_cliente" class="form-control" disabled>
                                                       </div>
                                                       <div class="form-group">
                                                           <label for="">Nit/Ci del cliente</label>
                                                           <input type="text" value="<?php echo $nit_ci_cliente; ?>" name="nit_ci_cliente" class="form-control" disabled>
                                                       </div>
                                                       <div class="form-group">
                                                           <label for="">Celular del cliente</label>
                                                           <input type="text" value="<?php echo $celular_cliente; ?>" name="celular_cliente" class="form-control" disabled>
                                                       </div>
                                                       <div class="form-group">
                                                           <label for="">Correo del cliente</label>
                                                           <input type="text" value="<?php echo $email_cliente; ?>" name="email_cliente" class="form-control" disabled>
                                                       </div>
                                                    <hr>
                                                   </div>
                                               </div>
                                               <!-- /.modal-content -->
                                           </div>
                                       </div>
                                           <!-- /.modal-dialog -->  
                                                
                                                
                                                



                                                
                                                      </div>
                                                
                                                      
                                                    </div>
                                                  </div>
                                                </div>
                                             
                                            </center></td>
                                             <td><center><h5> $. <?php echo $precio_total ?></button></h5></center></td>
                                             <td>
                                                <center>
                                                   <a href="show.php?id_venta=<?php echo $id_venta; ?>" class="btn btn-info"><i class="fa fa-eye"></i>Ver</a>
                                                   <a href="delete.php?id_venta=<?php echo $id_venta; ?>&nro_venta=<?php echo $nro_venta;?>" class="btn btn-danger"><i class="fa fa-trash"></i>Borrar</a>
                                                   
                                                   <a href="factura.php?id_venta=<?php echo $id_venta; ?>&nro_venta=<?php echo $nro_venta;?>" class="btn btn-success"><i class="fa fa-print"></i>Imprimir</a>
                                                </center>
                                             </td>   
                                             
                                        </tr>
                                        <?php
                                    }
                                    ?>
                                    </tbody>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->


<?php include ('../layout/mensajes.php'); ?>
<?php include ('../layout/parte2.php'); ?>


<script>
    $(function () {
        $("#example1").DataTable({
            "pageLength": 5,
            "language": {
                "emptyTable": "No hay información",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ Compras",
                "infoEmpty": "Mostrando 0 a 0 de 0 Compras",
                "infoFiltered": "(Filtrado de _MAX_ total Compras)",
                "infoPostFix": "",
                "thousands": ",",
                "lengthMenu": "Mostrar _MENU_ Compras",
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
                }
                ]
            },
                {
                    extend: 'colvis',
                    text: 'Visor de columnas',
                    collectionLayout: 'fixed three-column'
                }
            ],
        }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
    });
</script>

</script>







