<?php
include '../app/config.php'; // $URL, $pdo, $fechaHora
include '../layout/sesion.php'; // Verifica sesión, establece $id_usuario_sesion, etc.
include '../layout/parte1.php'; // Cabecera HTML, CSS, jQuery, y menú lateral

// Para mostrar mensajes flash (SweetAlert)
// Este se incluye aquí para que los mensajes de otras acciones (como anulación) se muestren.
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
            <!-- Tarjetas de estadísticas rápidas -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3 id="contador-total-ventas">-</h3>
                            <p>Total Ventas</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3 id="contador-ventas-pagadas">-</h3>
                            <p>Ventas Pagadas</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3 id="contador-ventas-pendientes">-</h3>
                            <p>Ventas Pendientes</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3 id="contador-ventas-anuladas">-</h3>
                            <p>Ventas Anuladas</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-ban"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Ventas Registradas</h3>
                            <div class="card-tools">
                                <a href="<?php echo $URL; ?>/ventas/create.php" class="btn btn-success">
                                    <i class="fas fa-plus-circle"></i> Registrar Nueva Venta
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <!-- Filtros rápidos para estados -->
                            <div class="mb-3">
                                <div class="btn-group" role="group" aria-label="Filtros de estado">
                                    <button type="button" class="btn btn-outline-secondary filtro-estado active" data-estado="todos">Todas</button>
                                    <button type="button" class="btn btn-outline-warning filtro-estado" data-estado="pendiente">Pendientes</button>
                                    <button type="button" class="btn btn-outline-success filtro-estado" data-estado="pagada">Pagadas</button>
                                    <button type="button" class="btn btn-outline-info filtro-estado" data-estado="entregada">Entregadas</button>
                                    <button type="button" class="btn btn-outline-danger filtro-estado" data-estado="anulada">Anuladas</button>
                                </div>
                            </div>
                            
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

<!-- Modal para motivo de anulación -->
<div class="modal fade" id="modalAnularVenta" tabindex="-1" aria-labelledby="modalAnularVentaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="modalAnularVentaLabel"><i class="fas fa-ban"></i> Anular Venta</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formAnularVenta">
                    <input type="hidden" id="id_venta_anular" name="id_venta">
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> 
                        <strong>¡Atención!</strong> Está a punto de anular la venta <span id="codigo_venta_anular" class="font-weight-bold"></span>.<br>
                        Esta acción no se puede deshacer y afectará al inventario.
                    </div>
                    
                    <div class="form-group">
                        <label for="motivo_anulacion">Motivo de anulación <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="motivo_anulacion" name="motivo_anulacion" rows="3" required 
                                placeholder="Indique el motivo por el cual se anula esta venta..."></textarea>
                        <small class="form-text text-muted">Esta información quedará registrada en el historial de la venta.</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" id="btnConfirmarAnulacion" class="btn btn-danger"><i class="fas fa-ban"></i> Confirmar Anulación</button>
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

<script>
$(document).ready(function() {
    // Variable global para estado actual del filtro
    let estadoFiltroActual = 'todos';
    
    // Inicializar DataTable
    var tablaVentas = $('#tabla_listado_ventas').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": "<?php echo $URL; ?>/app/controllers/ventas/controller_listado_ventas_dt.php",
            "type": "POST",
            "data": function(d) {
                d.filtro_estado = estadoFiltroActual;
                return d;
            }
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
                    let badgeIcon = '';
                    
                    if (estado === 'pagada') {
                        badgeClass = 'success';
                        badgeIcon = '<i class="fas fa-check-circle mr-1"></i>';
                    }
                    else if (estado === 'pendiente') {
                        badgeClass = 'warning';
                        badgeIcon = '<i class="fas fa-clock mr-1"></i>';
                    }
                    else if (estado === 'anulada') {
                        badgeClass = 'danger';
                        badgeIcon = '<i class="fas fa-ban mr-1"></i>';
                    }
                    else if (estado === 'entregada') {
                        badgeClass = 'info';
                        badgeIcon = '<i class="fas fa-truck mr-1"></i>';
                    }
                    
                    return '<span class="badge badge-' + badgeClass + ' badge-pill">' + 
                           badgeIcon + (data ? data.toUpperCase() : 'N/A') + '</span>';
                }
            },
            { "data": "id_venta", "orderable": false, "searchable": false, "width": "15%", "className": "text-center",
                "render": function(data, type, row) {
                    let estado = row.estado_venta ? row.estado_venta.toLowerCase() : '';
                    
                    // Botón Ver Detalle (siempre visible)
                    let viewButton = '<a href="<?php echo $URL; ?>/ventas/show.php?id=' + data + '" class="btn btn-info btn-xs mx-1" title="Ver Detalle"><i class="fas fa-eye"></i></a>';
                    
                    // Botón Editar (solo para ventas PENDIENTES)
                    let editButton = '';
                    if (estado === 'pendiente') {
                        editButton = '<a href="<?php echo $URL; ?>/ventas/edit.php?id=' + data + '" class="btn btn-warning btn-xs mx-1" title="Editar Venta"><i class="fas fa-edit"></i></a>';
                    } else {
                        editButton = '<button class="btn btn-secondary btn-xs mx-1" disabled title="Solo se pueden editar ventas PENDIENTES"><i class="fas fa-edit"></i></button>';
                    }
                    
                    // Botón Anular (para ventas no ANULADAS)
                    let anularButton = '';
                    if (estado !== 'anulada') {
                        anularButton = '<button type="button" class="btn btn-danger btn-xs mx-1 btn-anular-venta" data-id="' + data + '" data-codigo="' + row.codigo_venta_referencia + '" title="Anular Venta"><i class="fas fa-ban"></i></button>';
                    } else {
                        anularButton = '<button type="button" class="btn btn-secondary btn-xs mx-1" disabled title="Venta ya Anulada"><i class="fas fa-ban"></i></button>';
                    }

                    // Botón Validar (solo para ventas PENDIENTES)
                    let validarButton = '';
                    if (estado === 'pendiente') {
                        validarButton = '<button type="button" class="btn btn-success btn-xs mx-1 btn-validar-venta" data-id="' + data + '" data-codigo="' + row.codigo_venta_referencia + '" title="Marcar como PAGADA"><i class="fas fa-check-circle"></i></button>';
                    }

                    return '<div class="btn-group">' + viewButton + editButton + anularButton + validarButton + '</div>';
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
        "order": [[ 3, "desc" ]], // Ordenar por fecha de venta (columna índice 3) descendente
        "drawCallback": function(settings) {
            // Actualizar contadores cada vez que se redibuja la tabla
            actualizarContadoresEstado();
        }
    });

    // Función para actualizar los contadores de estado
    function actualizarContadoresEstado() {
        $.ajax({
            url: '<?php echo $URL; ?>/app/controllers/ventas/controller_estadisticas_ventas.php',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                console.log("Respuesta de estadísticas:", response); // Para depuración
                if (response.status === 'success') {
                    $('#contador-total-ventas').text(response.data.total || 0);
                    $('#contador-ventas-pagadas').text(response.data.pagadas || 0);
                    $('#contador-ventas-pendientes').text(response.data.pendientes || 0);
                    $('#contador-ventas-anuladas').text(response.data.anuladas || 0);
                } else {
                    console.error("Error al obtener estadísticas:", response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error("Error AJAX al obtener estadísticas:", status, error);
                console.error("Respuesta:", xhr.responseText);
            }
        });
    }

    // Filtros de estado
    $('.filtro-estado').click(function() {
        $('.filtro-estado').removeClass('active');
        $(this).addClass('active');
        
        estadoFiltroActual = $(this).data('estado');
        tablaVentas.ajax.reload();
    });

    // Manejador para el botón de anular venta
    $('#tabla_listado_ventas tbody').on('click', '.btn-anular-venta', function() {
        var ventaId = $(this).data('id');
        var ventaCodigo = $(this).data('codigo');
        
        // Llenar datos en el modal
        $('#id_venta_anular').val(ventaId);
        $('#codigo_venta_anular').text(ventaCodigo);
        $('#motivo_anulacion').val('');
        
        // Mostrar el modal
        $('#modalAnularVenta').modal('show');
    });
    
    // Manejador para confirmar anulación desde el modal
    $('#btnConfirmarAnulacion').click(function() {
        const idVenta = $('#id_venta_anular').val();
        const motivo = $('#motivo_anulacion').val().trim();
        
        if (!motivo) {
            Swal.fire('Error', 'Debe ingresar un motivo para anular la venta.', 'error');
            return;
        }
        
        // Deshabilitar botón y mostrar cargando
        $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Procesando...');
        
        $.ajax({
            url: '<?php echo $URL; ?>/app/controllers/ventas/controller_anular_venta.php',
            type: 'POST',
            data: { 
                id_venta: idVenta,
                motivo_anulacion: motivo
            },
            dataType: 'json',
            success: function(response) {
                $('#modalAnularVenta').modal('hide');
                
                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Venta Anulada!',
                        text: response.message,
                        confirmButtonText: 'Aceptar'
                    });
                    tablaVentas.ajax.reload();
                    actualizarContadoresEstado();
                } else {
                    Swal.fire('Error', response.message || 'No se pudo anular la venta.', 'error');
                }
            },
            error: function(xhr) {
                $('#modalAnularVenta').modal('hide');
                Swal.fire('Error de Conexión', 'No se pudo contactar al servidor. Intente nuevamente.', 'error');
                console.error("Error AJAX:", xhr.responseText);
            },
            complete: function() {
                $('#btnConfirmarAnulacion').prop('disabled', false).html('<i class="fas fa-ban"></i> Confirmar Anulación');
            }
        });
    });
    
    // Manejador para el botón de validar venta (cambiar a PAGADA)
    $('#tabla_listado_ventas tbody').on('click', '.btn-validar-venta', function() {
        var ventaId = $(this).data('id');
        var ventaCodigo = $(this).data('codigo');

        Swal.fire({
            title: 'Validar Venta',
            text: `¿Confirma que desea marcar la venta ${ventaCodigo} como PAGADA?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, Confirmar Pago',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?php echo $URL; ?>/app/controllers/ventas/actualizar_estado_venta.php',
                    type: 'POST',
                    data: { 
                        id_venta: ventaId,
                        estado_venta: 'PAGADA'
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Venta Validada!',
                                text: 'La venta ha sido marcada como PAGADA correctamente.',
                                confirmButtonText: 'Aceptar'
                            });
                            tablaVentas.ajax.reload();
                            actualizarContadoresEstado();
                        } else {
                            Swal.fire('Error', response.message || 'No se pudo validar la venta.', 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error de Conexión', 'No se pudo contactar al servidor.', 'error');
                    }
                });
            }
        });
    });

    // Cargar contadores iniciales
    actualizarContadoresEstado();
    
    // Opcional: Añadir manejador para actualizar cuando se cambia de página o tamaño
    $('#tabla_listado_ventas').on('page.dt length.dt', function () {
        setTimeout(function() {
            actualizarContadoresEstado();
        }, 500); // Pequeño retraso para asegurar que la tabla se haya redibujado
    });
    
    // Tooltip para los botones
    $(function () {
        $('[data-toggle="tooltip"]').tooltip();
    });
});
</script>