<?php
include ('../app/config.php');
include ('../layout/sesion.php');

include ('../layout/parte1.php');


include ('../app/controllers/categorias/listado_de_categoria.php');


?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="m-0">Listado de Categorías
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modal-create">
                           <i class="fa fa-plus"></i> Agregar Nuevo
                        </button>
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
                <div class="col-md-9">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Categorías registrados</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
                                </button>
                            </div>

                        </div>

                        <div class="card-body" style="display: block;">
                        <table id="example1" class="table table-bordered table-striped">
    <thead>
    <tr>
        <th><center>Nro</center></th>
        <th><center>Nombre de la categoría</center></th>
        <th><center>Acciones</center></th>
    </tr>
    </thead>
    <tbody>
    <?php
    $contador = 0;
    foreach ($categorias_datos as $categorias_dato){
        $id_categoria = $categorias_dato['id_categoria'];
        $nombre_categoria = $categorias_dato['nombre_categoria']; ?>
        <tr>
            <td><center><?php echo $contador = $contador + 1;?></center></td>
            <td><?php echo $nombre_categoria;?></td>
            <td>
                <center>
                    <div class="btn-group">
                        <button type="button" class="btn btn-success" data-toggle="modal"
                                data-target="#modal-update<?php echo $id_categoria;?>">
                            <i class="fa fa-pencil-alt"></i> Editar
                        </button>
                        
                        <!-- Agregamos el botón de eliminar -->
                        <button type="button" class="btn btn-danger" data-toggle="modal"
                                data-target="#modal-delete<?php echo $id_categoria;?>">
                            <i class="fa fa-trash"></i> Eliminar
                        </button>
                        
                        <!-- Modal para eliminar categoría -->
                        <div class="modal fade" id="modal-delete<?php echo $id_categoria;?>">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header bg-danger">
                                        <h4 class="modal-title">¿Está seguro de eliminar esta categoría?</h4>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <p>Esta acción no se puede deshacer.</p>
                                        <p>Categoría: <strong><?php echo $nombre_categoria; ?></strong></p>
                                    </div>
                                    <div class="modal-footer justify-content-between">
                                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancelar</button>
                                        <button type="button" class="btn btn-danger" id="btn_delete<?php echo $id_categoria; ?>">Eliminar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <script>
                            $('#btn_delete<?php echo $id_categoria;?>').click(function() {
                                var id_categoria = '<?php echo $id_categoria;?>';
                                var url = "../app/controllers/categorias/delete_de_categorias.php";
                                
                                $.get(url, {id_categoria: id_categoria}, function(datos) {
                                    $('#respuesta_delete<?php echo $id_categoria;?>').html(datos);
                                });
                            });
                        </script>
                        <div id="respuesta_delete<?php echo $id_categoria;?>"></div>
                        
                        <!-- Resto del código para el modal de actualización -->
                    </div>
                </center>
            </td>
        </tr>
        <?php
    }
    ?>
    </tbody>
    <tfoot>
    <tr>
        <th><center>Nro</center></th>
        <th><center>Nombre de la categoría</center></th>
        <th><center>Acciones</center></th>
    </tr>
    </tfoot>
</table>
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
                "info": "Mostrando _START_ a _END_ de _TOTAL_ Categorías",
                "infoEmpty": "Mostrando 0 a 0 de 0 Categorías",
                "infoFiltered": "(Filtrado de _MAX_ total Categorías)",
                "infoPostFix": "",
                "thousands": ",",
                "lengthMenu": "Mostrar _MENU_ Categorías",
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
                        exportOptions: {
                            columns: [0, 1]
                        }
                    },
                    {
                        extend: 'pdf',
                        text: 'Exportar PDF',
                        orientation: 'portrait', // <--- VERTICAL
                        pageSize: 'A4',
                        exportOptions: {
                            columns: [0, 1]
                        },
                        customize: function (doc) {
                            doc.pageMargins = [20, 20, 20, 20];
                            doc.defaultStyle.fontSize = 11;
                            if (doc.content[0].text) {
                                doc.content[0].alignment = 'center';
                            }
                            doc.styles.tableHeader.alignment = 'center';
                            doc.styles.tableHeader.fontSize = 12;
                            var body = doc.content[1].table.body;
                            for (var i = 1; i < body.length; i++) {
                                for (var j = 0; j < body[i].length; j++) {
                                    body[i][j].alignment = 'center';
                                }
                            }
                            doc.content[1].table.widths = ['15%', '85%'];
                        }
                    },
                    {
                        extend: 'csv',
                        exportOptions: {
                            columns: [0, 1]
                        }
                    },
                    {
                        extend: 'excel',
                        exportOptions: {
                            columns: [0, 1]
                        }
                    },
                    {
                        text: 'Imprimir',
                        extend: 'print',
                        exportOptions: {
                            columns: [0, 1]
                        }
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