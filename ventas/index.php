<?php
include '../app/config.php'; // $URL, $pdo, $fechaHora
include '../layout/sesion.php'; // Verifica sesión, establece $id_usuario_sesion, etc.
include '../layout/parte1.php'; // Cabecera HTML, CSS, jQuery, y menú lateral

// Para mostrar mensajes flash (SweetAlert)
// Este se incluye aquí para que los mensajes de otras acciones (como anulación futura) se muestren.
if (isset($_SESSION['mensaje']) && isset($_SESSION['icono'])) {
    $mensaje_script = "<script>
        Swal.fire({
            position: 'top-end',
            icon: '" . $_SESSION['icono'] . "',
            title: '" . addslashes($_SESSION['mensaje']) . "',
            showConfirmButton: false,
            timer: 4000
        });
    </script>";
    unset($_SESSION['mensaje']);
    unset($_SESSION['icono']);
} else {
    $mensaje_script = "";
}
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-file-invoice-dollar"></i> Listado de Ventas</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?php echo $URL; ?>/">Inicio</a></li>
                        <li class="breadcrumb-item active">Ventas</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Ventas Registradas</h3>
                            <div class="card-tools">
                                <a href="<?php echo $URL; ?>/ventas/create.php" class="btn btn-success">
                                    <i class="fas fa-plus-circle"></i> Registrar Nueva Venta
                                </a>
                                <!-- Podrías añadir botones para reportes aquí -->
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="tabla_listado_ventas" class="table table-bordered table-striped table-hover table-sm" style="width:100%">
                                    <thead class="thead-light">
                                        <tr>
                                            <th style="text-align: center;">#</th>
                                            <th>Nro. Venta</th>
                                            <th>Cliente</th>
                                            <th style="text-align: center;">Fecha Venta</th>
                                            <th style="text-align: right;">Total</th>
                                            <th style="text-align: center;">Estado</th>
                                            <th style="text-align: center;">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Los datos se cargarán vía AJAX por DataTables -->
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

<?php 
echo $mensaje_script; // Imprime el script de SweetAlert si hay mensajes
include '../layout/parte2.php'; 
?>

<!-- DataTables CSS -->
<link rel="stylesheet" href="<?php echo $URL; ?>/public/templeates/AdminLTE-3.2.0/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="<?php echo $URL; ?>/public/templeates/AdminLTE-3.2.0/plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
<!-- DataTables JS -->
<script src="<?php echo $URL; ?>/public/templeates/AdminLTE-3.2.0/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="<?php echo $URL; ?>/public/templeates/AdminLTE-3.2.0/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
<script src="<?php echo $URL; ?>/public/templeates/AdminLTE-3.2.0/plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
<script src="<?php echo $URL; ?>/public/templeates/AdminLTE-3.2.0/plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
<!-- SweetAlert2 JS (ya debería estar en parte1.php o parte2.php, pero por si acaso) -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<script>
$(document).ready(function() {
    var tablaVentas = $('#tabla_listado_ventas').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "<?php echo $URL; ?>/app/controllers/ventas/controller_listado_ventas_dt.php",
            "type": "POST"
        },
        "columns": [
            { "data": null, "orderable": false, "searchable": false, "width": "5%", "className": "text-center",
                "render": function (data, type, row, meta) {
                    return meta.row + meta.settings._iDisplayStart + 1;
                }
            },
            { "data": "codigo_venta_referencia", "width": "15%" },
            { "data": "nombre_cliente" },
            { "data": "fecha_venta", "width": "12%", "className": "text-center",
                "render": function(data, type, row) {
                    if (type === 'display' || type === 'filter') {
                        if (data) {
                            var dateParts = data.split('-'); // YYYY-MM-DD
                            if (dateParts.length === 3) {
                                return dateParts[2] + '/' + dateParts[1] + '/' + dateParts[0];
                            }
                        }
                    }
                    return data;
                }
            },
            { "data": "total_general", "width": "12%", "className": "text-right",
                "render": function(data, type, row) {
                    return '$' + parseFloat(data).toFixed(2);
                } 
            },
            { "data": "estado_venta", "width": "10%", "className": "text-center",
                "render": function(data, type, row) {
                    let estado = data ? data.toLowerCase() : 'desconocido';
                    let badgeClass = 'secondary';
                    if (estado === 'pagada') badgeClass = 'success';
                    else if (estado === 'pendiente') badgeClass = 'warning';
                    else if (estado === 'anulada') badgeClass = 'danger';
                    else if (estado === 'entregada') badgeClass = 'info';
                    return '<span class="badge badge-' + badgeClass + '">' + (data ? data.toUpperCase() : 'N/A') + '</span>';
                }
            },
            { "data": "id_venta", "orderable": false, "searchable": false, "width": "15%", "className": "text-center",
                "render": function(data, type, row) {
                    var viewButton = '<a href="<?php echo $URL; ?>/ventas/show.php?id=' + data + '" class="btn btn-info btn-xs mx-1" title="Ver Detalle"><i class="fas fa-eye"></i></a>';
                    
                    // El botón de editar solo tendría sentido si el estado de la venta lo permite (ej. 'PENDIENTE')
                    // Por ahora, lo mostramos, pero la lógica de habilitación/deshabilitación sería más compleja.
                    var editButton = '<a href="<?php echo $URL; ?>/ventas/edit.php?id=' + data + '" class="btn btn-warning btn-xs mx-1" title="Editar Venta"><i class="fas fa-edit"></i></a>';
                    
                    var anularButton = '';
                    // Solo mostrar botón de anular si la venta no está ya anulada
                    if (row.estado_venta && row.estado_venta.toLowerCase() !== 'anulada') {
                        anularButton = '<button type="button" class="btn btn-danger btn-xs mx-1 btn-anular-venta" data-id="' + data + '" data-codigo="' + row.codigo_venta_referencia + '" title="Anular Venta"><i class="fas fa-ban"></i></button>';
                    } else if (row.estado_venta && row.estado_venta.toLowerCase() === 'anulada') {
                         anularButton = '<button type="button" class="btn btn-secondary btn-xs mx-1" disabled title="Venta ya Anulada"><i class="fas fa-ban"></i></button>';
                    }

                    return '<div class="btn-group">' + viewButton + editButton + anularButton + '</div>';
                }
            }
        ],
        "language": {
            "url": "<?php echo $URL; ?>/public/templeates/AdminLTE-3.2.0/plugins/datatables-plugins/i18n/es_es.json"
        },
        "responsive": true,
        "lengthChange": true,
        "autoWidth": false,
        "pageLength": 10,
        "order": [[ 3, "desc" ]] // Ordenar por fecha de venta (columna índice 3) descendente
    });

    // Manejador para el botón de anular venta
    $('#tabla_listado_ventas tbody').on('click', '.btn-anular-venta', function() {
        var ventaId = $(this).data('id');
        var ventaCodigo = $(this).data('codigo');

        Swal.fire({
            title: '¿Está seguro?',
            text: "Se anulará la venta Nro: " + ventaCodigo + ". ¡Esta acción podría ser irreversible si no se maneja con notas de crédito!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, ¡Anular!',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Aquí iría la llamada AJAX para anular la venta en el backend
                // Por ejemplo:
                // $.ajax({
                //     url: '<?php echo $URL; ?>/app/controllers/ventas/controller_anular_venta.php', // Necesitas crear este controlador
                //     type: 'POST',
                //     data: { 
                //         id_venta: ventaId,
                //         // Podrías necesitar un token CSRF aquí por seguridad
                //     },
                //     dataType: 'json',
                //     success: function(response) {
                //         if (response.status === 'success') {
                //             Swal.fire('¡Anulada!', response.message, 'success');
                //             tablaVentas.ajax.reload(null, false); // Recargar DataTables sin resetear paginación
                //         } else {
                //             Swal.fire('Error', response.message || 'No se pudo anular la venta.', 'error');
                //         }
                //     },
                //     error: function() {
                //         Swal.fire('Error de Conexión', 'No se pudo contactar al servidor.', 'error');
                //     }
                // });

                // --- INICIO: Simulación de éxito (temporal hasta implementar backend) ---
                Swal.fire(
                    '¡Acción Simulada!',
                    'La venta ' + ventaCodigo + ' se marcaría como anulada. (Backend no implementado)',
                    'info'
                );
                // Para ver el cambio visualmente (temporal, el backend debería hacerlo):
                // $(this).closest('tr').find('td').eq(5).html('<span class="badge badge-danger">ANULADA</span>'); // Columna de estado
                // $(this).prop('disabled', true).removeClass('btn-danger').addClass('btn-secondary');
                // tablaVentas.ajax.reload(null, false); // Descomentar cuando el backend funcione
                // --- FIN: Simulación de éxito ---
            }
        });
    });
});
</script>