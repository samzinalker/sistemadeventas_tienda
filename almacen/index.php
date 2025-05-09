<?php
include ('../app/config.php');
include ('../layout/sesion.php');

include ('../layout/parte1.php');


include ('../app/controllers/almacen/listado_de_productos.php');


?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="m-0">Listado de productos</h1>
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
                            <h3 class="card-title">Productos registrados</h3>
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
                                       <th><center>Código</center></th>
                                       <th><center>Categoría</center></th>
                                       <th><center>Imagen</center></th>
                                       <th><center>Nombre</center></th>
                                       <th><center>Descripción</center></th>
                                       <th><center>Stock</center></th>
                                       <th><center>Precio compra</center></th>
                                       <th><center>Precio venta</center></th>
                                       <th><center>Fecha compra</center></th>
                                       <th><center>Usuario</center></th>
                                       <th><center>Acciones</center></th>
                                   </tr>
                                   </thead>
                                   <tbody>
                                   <?php
                                   $contador = 0;
                                   foreach ($productos_datos as $productos_dato){
                                       $id_producto = $productos_dato['id_producto']; ?>
                                       <tr>
                                           <td><?php echo $contador = $contador + 1; ?></td>
                                           <td><?php echo $productos_dato['codigo'];?></td>
                                           <td><?php echo $productos_dato['categoria'];?></td>
                                           <td>
                                               <img src="<?php echo $URL."/almacen/img_productos/".$productos_dato['imagen'];?>" width="50px" alt="asdf">
                                           </td>
                                           <td><?php echo $productos_dato['nombre'];?></td>
                                           <td><?php echo $productos_dato['descripcion'];?></td>
                                           <?php
                                           $stock_actual = $productos_dato['stock'];
                                           $stock_maximo = $productos_dato['stock_maximo'];
                                           $stock_minimo = $productos_dato['stock_minimo'];
                                           if($stock_actual < $stock_minimo){ ?>
                                               <td style="background-color: #ee868b"><center><?php echo $productos_dato['stock'];?></center></td>
                                           <?php
                                           }
                                           else if($stock_actual > $stock_maximo){ ?>
                                               <td style="background-color: #8ac68d"><center><?php echo $productos_dato['stock'];?></center></td>
                                           <?php
                                           }else{ ?>
                                               <td><center><?php echo $productos_dato['stock'];?></center></td>
                                           <?php
                                           }
                                           ?>

                                           <td><?php echo $productos_dato['precio_compra'];?></td>
                                           <td><?php echo $productos_dato['precio_venta'];?></td>
                                           <td><?php echo $productos_dato['fecha_ingreso'];?></td>
                                           <td><?php echo $productos_dato['email'];?></td>
                                           <td>
                                               <center>
                                                   <div class="btn-group">
                                                       <a href="show.php?id=<?php echo $id_producto; ?>" type="button" class="btn btn-info btn-sm"><i class="fa fa-eye"></i> Ver</a>
                                                       <a href="update.php?id=<?php echo $id_producto; ?>" type="button" class="btn btn-success btn-sm"><i class="fa fa-pencil-alt"></i> Editar</a>
                                                       <a href="delete.php?id=<?php echo $id_producto; ?>" type="button" class="btn btn-danger btn-sm"><i class="fa fa-trash"></i> Borrar</a>
                                                   </div>
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
                "info": "Mostrando _START_ a _END_ de _TOTAL_ Roles",
                "infoEmpty": "Mostrando 0 a 0 de 0 Roles",
                "infoFiltered": "(Filtrado de _MAX_ total Roles)",
                "infoPostFix": "",
                "thousands": ",",
                "lengthMenu": "Mostrar _MENU_ Roles",
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
                buttons: [
                    {
                        text: 'Copiar',
                        extend: 'copy',
                    }, 
                    {
                        extend: 'pdf',
                        text: 'Exportar PDF',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        exportOptions: {
                            columns: ':visible'
                        },
                        customize: function (doc) {
                            // Márgenes pequeños
                            doc.pageMargins = [10, 10, 10, 10];
                            doc.defaultStyle.fontSize = 7;
                            // Centrar el título
                            if (doc.content[0].text) {
                                doc.content[0].alignment = 'center';
                            }
                            // Centrar cabecera
                            doc.styles.tableHeader.alignment = 'center';
                            doc.styles.tableHeader.fontSize = 8;
                            // Centrar contenido de todas las celdas
                            var body = doc.content[1].table.body;
                            for (var i = 1; i < body.length; i++) {
                                for (var j = 0; j < body[i].length; j++) {
                                    body[i][j].alignment = 'center';
                                }
                            }
                            // Columnas ancho automático
                            doc.content[1].table.widths = Array(body[0].length + 1).join('*').split('');
                        }
                    },
                    {
                        extend: 'csv'
                    },
                    {
                        extend: 'excel'
                    },
                    {
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