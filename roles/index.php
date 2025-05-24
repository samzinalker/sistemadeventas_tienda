<?php
// 1. INICIAR SESIÓN (si no está activa)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. CONFIGURACIÓN PRINCIPAL (define $pdo, $URL, $fechaHora)
require_once __DIR__ . '/../app/config.php'; 

// 3. MANEJO DE SESIÓN DEL USUARIO (valida sesión, carga datos del usuario)
require_once __DIR__ . '/../layout/sesion.php'; 

// 4. MANEJO DE PERMISOS (valida si el usuario tiene acceso a esta página/módulo)
require_once __DIR__ . '/../layout/permisos.php'; 

// Lógica específica de la página (variables de título, carga de datos, etc.)
$titulo_pagina = 'Listado de Roles';
$modulo_abierto = 'roles';
$pagina_activa = 'roles_listado';

// Cargar los datos de los roles usando el controlador de listado
require_once __DIR__ . '/../app/controllers/roles/listado_de_roles.php'; 

// LAYOUT PARTE 1 (HTML head, navbar, sidebar, SweetAlert JS incluido)
require_once __DIR__ . '/../layout/parte1.php'; 
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><?php echo htmlspecialchars($titulo_pagina); ?></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?php echo $URL; ?>/">Inicio</a></li>
                        <li class="breadcrumb-item active">Roles</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-8"> {/* Ajusta el ancho si es necesario */}
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Roles Registrados</h3>
                            <div class="card-tools">
                                <a href="create.php" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus"></i> Nuevo Rol
                                </a>
                                <!-- BOTÓN PARA REPORTE PDF/VISTA PREVIA -->
                                <a href="reporte_roles_vista.php" target="_blank" class="btn btn-info btn-sm">
                                    <i class="fas fa-file-pdf"></i> Generar Reporte
                                </a>
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body">
                            <table id="tabla-roles" class="table table-bordered table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th style="width: 10%;"><center>Nro</center></th>
                                        <th style="width: 40%;">Nombre del Rol</th>
                                        <th style="width: 25%;"><center>Fecha Creación</center></th>
                                        <th style="width: 25%;"><center>Acciones</center></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $contador = 0;
                                    if (!empty($roles_datos)) {
                                        foreach ($roles_datos as $rol_dato) {
                                            $id_rol = $rol_dato['id_rol'];
                                    ?>
                                        <tr>
                                            <td><center><?php echo ++$contador; ?></center></td>
                                            <td><?php echo htmlspecialchars($rol_dato['rol']); ?></td>
                                            <td><center><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($rol_dato['fyh_creacion']))); ?></center></td>
                                            <td>
                                                <center>
                                                    <div class="btn-group">
                                                        <a href="update.php?id=<?php echo $id_rol; ?>" class="btn btn-success btn-sm" title="Editar Rol">
                                                            <i class="fa fa-pencil-alt"></i> Editar
                                                        </a>
                                                        <?php
                                                        $esRolAdminPrincipal = ($id_rol == 1); 
                                                        if (!$esRolAdminPrincipal): ?>
                                                            <form action="<?php echo htmlspecialchars($URL . '/app/controllers/roles/delete_controller.php'); ?>" method="POST" style="display:inline;" onsubmit="return confirm('¿Está realmente seguro de que desea eliminar este rol? Esta acción no se puede deshacer.');">
                                                                <input type="hidden" name="id_rol_a_eliminar" value="<?php echo htmlspecialchars($id_rol); ?>">
                                                                <button type="submit" class="btn btn-danger btn-sm" title="Eliminar Rol">
                                                                    <i class="fa fa-trash"></i> Borrar
                                                                </button>
                                                            </form>
                                                        <?php else: ?>
                                                            <button type="button" class="btn btn-danger btn-sm disabled" title="Este rol no se puede eliminar">
                                                                <i class="fa fa-trash"></i> Borrar
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </center>
                                            </td>
                                        </tr>
                                    <?php 
                                        } 
                                    } else { 
                                    ?>
                                        <tr>
                                            <td colspan="4"><center>No hay roles registrados.</center></td>
                                        </tr>
                                    <?php
                                    } 
                                    ?>
                                </tbody>
                            </table>
                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                </div>
            </div>
            <!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content -->
</div>
<!-- /.content-wrapper -->

<?php 
require_once __DIR__ . '/../layout/mensajes.php'; 
require_once __DIR__ . '/../layout/parte2.php'; 
?>
<script>
$(function () {
    $("#tabla-roles").DataTable({
        "responsive": true, "lengthChange": false, "autoWidth": false,
        "language": { 
            "url": "<?php echo $URL; ?>/public/plugins/datatables/i18n/Spanish.json"
        },
        "order": [[1, "asc"]] 
    });
});
</script>