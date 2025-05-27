<?php
include '../app/config.php';
include '../layout/sesion.php';
include '../layout/parte1.php';

// Solo administradores
if (strtolower(trim($rol_sesion)) !== 'administrador') {
    header("Location: $URL/");
    exit();
}

require_once __DIR__ . '/../app/models/UsuarioModel.php';
$usuarioModel = new UsuarioModel($pdo, $URL);
$usuarios_eliminados = $usuarioModel->getAllUsuarios(true);
$usuarios_eliminados = array_filter($usuarios_eliminados, function($user) {
    return $user['estado'] === 'eliminado';
});

include '../layout/mensajes.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><i class="fas fa-user-slash"></i> Usuarios Eliminados</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?php echo $URL; ?>/">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo $URL; ?>/usuarios/">Usuarios</a></li>
                        <li class="breadcrumb-item active">Eliminados</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="content">
        <div class="container-fluid">
            <div class="card card-outline card-danger">
                <div class="card-header">
                    <h3 class="card-title">Historial de Usuarios Eliminados</h3>
                    <div class="card-tools">
                        <a href="<?php echo $URL; ?>/usuarios/" class="btn btn-primary btn-sm">
                            <i class="fas fa-arrow-left"></i> Volver a Usuarios Activos
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (!empty($usuarios_eliminados)): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Nombre</th>
                                        <th>Email</th>
                                        <th>Rol</th>
                                        <th>Fecha Creación</th>
                                        <th>Fecha Eliminación</th>
                                        <th>Eliminado Por</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($usuarios_eliminados as $usuario): ?>
                                        <tr>
                                            <td><?php echo $usuario['id_usuario']; ?></td>
                                            <td><?php echo htmlspecialchars($usuario['nombres']); ?></td>
                                            <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                                            <td><?php echo htmlspecialchars($usuario['nombre_rol']); ?></td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($usuario['fyh_creacion'])); ?></td>
                                            <td><?php echo $usuario['fecha_eliminacion'] ? date('d/m/Y H:i', strtotime($usuario['fecha_eliminacion'])) : 'N/A'; ?></td>
                                            <td><?php echo htmlspecialchars($usuario['eliminado_por_nombre'] ?? 'N/A'); ?></td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-success btn-restaurar-usuario" 
                                                        data-id="<?php echo $usuario['id_usuario']; ?>"
                                                        data-nombre="<?php echo htmlspecialchars($usuario['nombres']); ?>">
                                                    <i class="fas fa-undo"></i> Restaurar
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> No hay usuarios eliminados en el sistema.
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
    $('.btn-restaurar-usuario').click(function() {
        const idUsuario = $(this).data('id');
        const nombreUsuario = $(this).data('nombre');
        
        Swal.fire({
            title: '¿Restaurar Usuario?',
            html: `¿Estás seguro de que quieres restaurar al usuario:<br><strong>${nombreUsuario}</strong>?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, Restaurar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Crear formulario para enviar la restauración
                const form = $('<form>', {
                    'method': 'POST',
                    'action': '<?php echo $URL; ?>/app/controllers/usuarios/restore_controller.php'
                });
                
                form.append($('<input>', {
                    'type': 'hidden',
                    'name': 'id_usuario_a_restaurar',
                    'value': idUsuario
                }));
                
                $('body').append(form);
                form.submit();
            }
        });
    });
});
</script>