<?php
include ('../app/config.php');
include ('../layout/sesion.php');
include ('../layout/parte1.php');
include ('../app/controllers/compras/listado_de_compras.php');

// Incluir verificación explícita de usuario
$id_usuario_actual = $_SESSION['id_usuario'];

// Si no existe verificacion_usuario.php, está incluido en listado_de_compras.php
// Esto garantiza que solo se muestren las compras del usuario actual
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="m-0">
                        Mis Compras
                        <a href="create.php" class="btn btn-primary">
                            <i class="fa fa-plus"></i> Registrar nueva compra
                        </a>
                    </h1>
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
                            <h3 class="card-title">Compras registradas</h3>
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
                                        <th><center>Nro de compra</center></th>
                                        <th><center>Producto</center></th>
                                        <th><center>Fecha</center></th>
                                        <th><center>Proveedor</center></th>
                                        <th><center>Comprobante</center></th>
                                        <th><center>Precio</center></th>
                                        <th><center>Cantidad</center></th>
                                        <th><center>Total</center></th>
                                        <th><center>Acciones</center></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $contador = 0;
                                    foreach ($compras_datos as $compras_dato){
                                        $id_compra = $compras_dato['id_compra']; 
                                        $precio_compra = $compras_dato['precio_compra'];
                                        $cantidad = $compras_dato['cantidad'];
                                        $total = floatval($precio_compra) * intval($cantidad);
                                        $fecha_formateada = date('d/m/Y', strtotime($compras_dato['fecha_compra']));
                                        ?>
                                        <tr>
                                            <td><center><?php echo $contador = $contador + 1;?></center></td>
                                            <td><center><?php echo $compras_dato['nro_compra'];?></center></td>
                                            <td>
                                                <button type="button" class="btn btn-warning btn-sm" data-toggle="modal"
                                                        data-target="#modal-producto<?php echo $id_compra;?>">
                                                    <?php echo $compras_dato['nombre_producto'];?>
                                                </button>
                                                <!-- modal para visualizar datos de los productos -->
                                                <div class="modal fade" id="modal-producto<?php echo $id_compra;?>">
                                                    <div class="modal-dialog modal-lg">
                                                        <div class="modal-content">
                                                            <div class="modal-header" style="background-color: #07b0d6;color: white">
                                                                <h4 class="modal-title">Datos del producto</h4>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="row">
                                                                    <div class="col-md-9">
                                                                        <div class="row">
                                                                            <div class="col-md-2">
                                                                                <div class="form-group">
                                                                                    <label for="">Código</label>
                                                                                    <input type="text" value="<?php echo $compras_dato['codigo'];?>" class="form-control" disabled>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-4">
                                                                                <div class="form-group">
                                                                                    <label for="">Nombre del producto</label>
                                                                                    <input type="text" value="<?php echo $compras_dato['nombre_producto'];?>" class="form-control" disabled>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-6">
                                                                                <div class="form-group">
                                                                                    <label for="">Descripción</label>
                                                                                    <input type="text" value="<?php echo $compras_dato['descripcion'];?>" class="form-control" disabled>
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        <div class="row">
                                                                            <div class="col-md-3">
                                                                                <div class="form-group">
                                                                                    <label for="">Stock</label>
                                                                                    <input type="text" value="<?php echo $compras_dato['stock'];?>" class="form-control" disabled>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-3">
                                                                                <div class="form-group">
                                                                                    <label for="">Stock mínimo</label>
                                                                                    <input type="text" value="<?php echo $compras_dato['stock_minimo'];?>" class="form-control" disabled>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-3">
                                                                                <div class="form-group">
                                                                                    <label for="">Stock máximo</label>
                                                                                    <input type="text" value="<?php echo $compras_dato['stock_maximo'];?>" class="form-control" disabled>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-3">
                                                                                <div class="form-group">
                                                                                    <label for="">Fecha de Ingreso</label>
                                                                                    <input type="text" value="<?php echo date('d/m/Y', strtotime($compras_dato['fecha_ingreso']));?>" class="form-control" disabled>
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        <div class="row">
                                                                            <div class="col-md-4">
                                                                                <div class="form-group">
                                                                                    <label for="">Precio Compra</label>
                                                                                    <input type="text" value="$<?php echo number_format($compras_dato['precio_compra_producto'],2);?>" class="form-control" disabled>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-4">
                                                                                <div class="form-group">
                                                                                    <label for="">Precio Venta</label>
                                                                                    <input type="text" value="$<?php echo number_format($compras_dato['precio_venta_producto'],2);?>" class="form-control" disabled>
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-md-4">
                                                                                <div class="form-group">
                                                                                    <label for="">Categoría</label>
                                                                                    <input type="text" value="<?php echo $compras_dato['nombre_categoria'];?>" class="form-control" disabled>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <div class="form-group">
                                                                            <label for="">Imagen del producto</label>
                                                                            <img src="<?php echo $URL."/almacen/img_productos/".$compras_dato['imagen'];?>" width="100%" alt="Imagen del producto">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                                            </div>
                                                        </div>
                                                        <!-- /.modal-content -->
                                                    </div>
                                                    <!-- /.modal-dialog -->
                                                </div>
                                                <!-- /.modal -->
                                            </td>
                                            <td><center><?php echo $fecha_formateada;?></center></td>
                                            <td>
                                                <button type="button" class="btn btn-info btn-sm" data-toggle="modal"
                                                        data-target="#modal-proveedor<?php echo $id_compra;?>">
                                                    <?php echo $compras_dato['nombre_proveedor'];?>
                                                </button>

                                                <!-- modal para visualizar datos de los proveedores -->
                                                <div class="modal fade" id="modal-proveedor<?php echo $id_compra;?>">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header" style="background-color: #07b0d6;color: white">
                                                                <h4 class="modal-title">Datos del proveedor</h4>
                                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                    <span aria-hidden="true">&times;</span>
                                                                </button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="row">
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label for="">Nombre</label>
                                                                            <input type="text" value="<?php echo $compras_dato['nombre_proveedor'];?>" class="form-control" disabled>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label for="">Celular</label>
                                                                            <div class="input-group">
                                                                                <input type="text" value="<?php echo $compras_dato['celular_proveedor'];?>" class="form-control" disabled>
                                                                                <div class="input-group-append">
                                                                                    <a href="https://wa.me/591<?php echo $compras_dato['celular_proveedor'];?>" target="_blank" class="btn btn-success">
                                                                                        <i class="fab fa-whatsapp"></i>
                                                                                    </a>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="row">
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label for="">Teléfono</label>
                                                                            <input type="text" value="<?php echo $compras_dato['telefono_proveedor'];?>" class="form-control" disabled>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label for="">Empresa</label>
                                                                            <input type="text" value="<?php echo $compras_dato['empresa'];?>" class="form-control" disabled>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="row">
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label for="">Email</label>
                                                                            <div class="input-group">
                                                                                <input type="text" value="<?php echo $compras_dato['email_proveedor'];?>" class="form-control" disabled>
                                                                                <div class="input-group-append">
                                                                                    <a href="mailto:<?php echo $compras_dato['email_proveedor'];?>" class="btn btn-primary">
                                                                                        <i class="far fa-envelope"></i>
                                                                                    </a>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label for="">Dirección</label>
                                                                            <input type="text" value="<?php echo $compras_dato['direccion_proveedor'];?>" class="form-control" disabled>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                                            </div>
                                                        </div>
                                                        <!-- /.modal-content -->
                                                    </div>
                                                    <!-- /.modal-dialog -->
                                                </div>
                                                <!-- /.modal -->
                                            </td>
                                            <td><center><?php echo $compras_dato['comprobante'];?></center></td>
                                            <td><center>$<?php echo number_format(floatval($precio_compra), 2);?></center></td>
                                            <td><center><?php echo $cantidad;?></center></td>
                                            <td><center><strong>$<?php echo number_format($total, 2);?></strong></center></td>
                                            <td>
                                                <center>
                                                    <div class="btn-group">
                                                        <a href="show.php?id=<?php echo $id_compra; ?>" class="btn btn-info btn-sm">
                                                            <i class="fa fa-eye"></i>
                                                        </a>
                                                        <a href="update.php?id=<?php echo $id_compra; ?>" class="btn btn-success btn-sm">
                                                            <i class="fa fa-pencil-alt"></i>
                                                        </a>
                                                        <a href="delete.php?id=<?php echo $id_compra; ?>" class="btn btn-danger btn-sm" 
                                                           onclick="return confirm('¿Está seguro que desea eliminar esta compra?')">
                                                            <i class="fa fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </center>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                    ?>
                                    </tbody>
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
            "pageLength": 10,
            "language": {
                "emptyTable": "No hay compras registradas",
                "info": "Mostrando _START_ a _END_ de _TOTAL_ Compras",
                "infoEmpty": "Mostrando 0 a 0 de 0 Compras",
                "infoFiltered": "(Filtrado de _MAX_ total Compras)",
                "lengthMenu": "Mostrar _MENU_ Compras",
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
                buttons: [
                    {
                        text: 'Copiar',
                        extend: 'copy',
                        exportOptions: {
                            columns: ':not(:last-child)'
                        }
                    },
                    {
                        extend: 'pdf',
                        text: 'Exportar PDF',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        exportOptions: {
                            columns: ':not(:last-child)'
                        },
                        customize: function (doc) {
                            doc.pageMargins = [10, 10, 10, 10];
                            doc.defaultStyle.fontSize = 7;
                            if (doc.content[0].text) {
                                doc.content[0].alignment = 'center';
                            }
                            doc.styles.tableHeader.alignment = 'center';
                            doc.styles.tableHeader.fontSize = 8;
                            var body = doc.content[1].table.body;
                            for (var i = 1; i < body.length; i++) {
                                for (var j = 0; j < body[i].length; j++) {
                                    body[i][j].alignment = 'center';
                                }
                            }
                            doc.content[1].table.widths = Array(body[0].length + 1).join('*').split('');
                        }
                    },
                    {
                        extend: 'csv',
                        exportOptions: {
                            columns: ':not(:last-child)'
                        }
                    },
                    {
                        extend: 'excel',
                        exportOptions: {
                            columns: ':not(:last-child)'
                        }
                    },
                    {
                        text: 'Imprimir',
                        extend: 'print',
                        exportOptions: {
                            columns: ':not(:last-child)'
                        }
                    }
                ]
            },
            {
                extend: 'colvis',
                text: 'Visor de columnas',
                collectionLayout: 'fixed three-column'
            }],
        }).buttons().container().appendTo('#example1_wrapper .col-md-6:eq(0)');
    });
</script>