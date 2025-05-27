<?php
include '../app/config.php';
include '../layout/sesion.php';
include '../layout/parte1.php';

// Solo administradores
if (strtolower(trim($rol_sesion)) !== 'administrador') {
    header("Location: $URL/");
    exit();
}

require_once __DIR__ . '/../app/models/BackupUsuarioModel.php';
$backupModel = new BackupUsuarioModel($pdo, $URL);
$respaldos = $backupModel->listarRespaldos();

include '../layout/mensajes.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-archive"></i> Respaldos de Usuarios Eliminados</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?php echo $URL; ?>/">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo $URL; ?>/usuarios/">Usuarios</a></li>
                        <li class="breadcrumb-item active">Respaldos</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title">Historial de Respaldos</h3>
                    <div class="card-tools">
                        <a href="<?php echo $URL; ?>/usuarios/" class="btn btn-primary btn-sm">
                            <i class="fas fa-arrow-left"></i> Volver a Usuarios
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($respaldos)): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Usuario Eliminado</th>
                                        <th>Email</th>
                                        <th>Rol</th>
                                        <th>Fecha Eliminación</th>
                                        <th>Tamaño Respaldo</th>
                                        <th>Carpeta</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($respaldos as $respaldo): ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($respaldo['usuario_info']['nombres'] ?? 'N/A'); ?></strong>
                                                <br><small class="text-muted">ID: <?php echo $respaldo['usuario_info']['id_usuario'] ?? 'N/A'; ?></small>
                                            </td>
                                            <td><?php echo htmlspecialchars($respaldo['usuario_info']['email'] ?? 'N/A'); ?></td>
                                            <td>
                                                <span class="badge badge-secondary">
                                                    <?php echo htmlspecialchars($respaldo['usuario_info']['nombre_rol'] ?? 'N/A'); ?>
                                                </span>
                                            </td>
                                            <td><?php echo $respaldo['fecha_eliminacion'] ? date('d/m/Y H:i', strtotime($respaldo['fecha_eliminacion'])) : 'N/A'; ?></td>
                                            <td>
                                                <?php 
                                                $tamaño = $respaldo['tamaño'];
                                                if ($tamaño > 1024*1024) {
                                                    echo number_format($tamaño / (1024*1024), 2) . ' MB';
                                                } elseif ($tamaño > 1024) {
                                                    echo number_format($tamaño / 1024, 2) . ' KB';
                                                } else {
                                                    echo $tamaño . ' bytes';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <code><?php echo htmlspecialchars($respaldo['carpeta']); ?></code>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-info btn-ver-respaldo" 
                                                        data-carpeta="<?php echo htmlspecialchars($respaldo['carpeta']); ?>"
                                                        title="Ver detalles del respaldo">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-sm btn-warning btn-descargar-respaldo" 
                                                        data-carpeta="<?php echo htmlspecialchars($respaldo['carpeta']); ?>"
                                                        title="Descargar respaldo">
                                                    <i class="fas fa-download"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No hay respaldos de usuarios eliminados en el sistema.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../layout/parte2.php'; ?>

<script>
$(document).ready(function() {
    $('.btn-ver-respaldo').click(function() {
        const carpeta = $(this).data('carpeta');
        Swal.fire({
            title: 'Detalles del Respaldo',
            html: `
                <p><strong>Carpeta:</strong> ${carpeta}</p>
                <p>El respaldo incluye:</p>
                <ul class="text-left">
                    <li>Información completa del usuario</li>
                    <li>Todas las categorías creadas</li>
                    <li>Todos los productos del almacén</li>
                    <li>Historial completo de ventas</li>
                    <li>Base de datos de clientes</li>
                    <li>Archivos e imágenes asociadas</li>
                    <li>Estadísticas y reportes</li>
                </ul>
                <p><small>Para acceder al respaldo, consulte con el administrador del sistema.</small></p>
            `,
            icon: 'info',
            confirmButtonText: 'Entendido'
        });
    });
    
    $('.btn-descargar-respaldo').click(function() {
        const carpeta = $(this).data('carpeta');
        Swal.fire({
            title: 'Descargar Respaldo',
            text: 'Esta funcionalidad requiere permisos especiales del administrador del sistema.',
            icon: 'warning',
            confirmButtonText: 'Entendido'
        });
    });
});
</script>