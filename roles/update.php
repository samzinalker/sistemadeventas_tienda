<?php
// 1. Incluir configuración, sesión y permisos
require_once __DIR__ . '/../app/config.php';
// INCLUIR FUNCIONES GLOBALES AQUÍ
require_once __DIR__ . '/../app/utils/funciones_globales.php'; // <--- AÑADIDO AQUÍ
require_once __DIR__ . '/../layout/sesion.php'; // sesion.php puede usar setMensaje si hay error de BD
require_once __DIR__ . '/../layout/permisos.php'; // permisos.php puede usar setMensaje

// 2. Verificar si se proporcionó un ID de rol válido
if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    // Si no hay ID o no es un entero, redirigir o mostrar error
    setMensaje("ID de rol no válido o no proporcionado.", "error");
    redirigir('/roles/');
}
$id_rol_editar = (int)$_GET['id'];

// 3. Obtener datos del rol a editar
require_once __DIR__ . '/../app/models/RolModel.php';
$rolModel = new RolModel($pdo);
$rol_a_editar = $rolModel->getRolById($id_rol_editar);

// Verificar si el rol existe
if (!$rol_a_editar) {
    setMensaje("El rol que intenta editar no existe.", "error");
    redirigir('/roles/');
}

// No permitir editar el rol de administrador principal (ID 1 usualmente)
// Esta validación es importante aquí para evitar cargar el formulario innecesariamente.
if ($id_rol_editar == 1) { // Asumiendo que ID 1 es el rol de administrador principal
    setMensaje("El rol 'administrador' principal no puede ser editado desde aquí.", "warning");
    redirigir('/roles/');
}


// 4. Definir título y variables para el layout
$titulo_pagina = 'Actualizar Rol: ' . htmlspecialchars($rol_a_editar['rol']);
$modulo_abierto = 'roles';
$pagina_activa = 'roles_update'; // Para resaltar en el menú (puedes crear uno si es necesario)

// Repoblar formulario si hay datos en sesión por un error previo en el controlador
// Usamos el id_rol_editar como clave para los datos del formulario en sesión, 
// para evitar conflictos si se abren múltiples pestañas de edición.
$form_data_key = 'form_data_rol_update_' . $id_rol_editar;
$form_data = $_SESSION[$form_data_key] ?? ['rol' => $rol_a_editar['rol']]; // Aseguramos que 'rol' exista

if (isset($_SESSION[$form_data_key])) {
    unset($_SESSION[$form_data_key]); // Limpiar después de usar
}


// 5. Incluir la parte superior del layout
require_once __DIR__ . '/../layout/parte1.php';
?>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0"><?php echo $titulo_pagina; ?></h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?php echo $URL; ?>/">Inicio</a></li>
                        <li class="breadcrumb-item"><a href="<?php echo $URL; ?>/roles/">Roles</a></li>
                        <li class="breadcrumb-item active">Actualizar Rol</li>
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
                <div class="col-md-6"> {/* Ajusta el ancho si es necesario */}
                    <div class="card card-success"> {/* Cambiado a card-success para diferenciar */}
                        <div class="card-header">
                            <h3 class="card-title">Modifique los datos del rol</h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                            </div>
                        </div>
                        <!-- /.card-header -->
                        <div class="card-body">
                            <form action="<?php echo htmlspecialchars($URL . '/app/controllers/roles/update_controller.php'); ?>" method="post">
                                <input type="hidden" name="id_rol" value="<?php echo htmlspecialchars($id_rol_editar); ?>">
                                
                                <div class="form-group">
                                    <label for="nombre_rol">Nombre del Rol</label>
                                    <input type="text" name="nombre_rol" id="nombre_rol" class="form-control" 
                                           value="<?php echo htmlspecialchars($form_data['rol'] ?? ''); ?>" required>
                                    <small class="form-text text-muted">Ej: Vendedor, Editor, Contador, etc.</small>
                                </div>
                                
                                <hr>
                                <div class="form-group text-right">
                                    <a href="<?php echo $URL; ?>/roles/" class="btn btn-secondary">Cancelar</a>
                                    <button type="submit" class="btn btn-success">Actualizar Rol</button>
                                </div>
                            </form>
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
// 6. Incluir mensajes y la parte final del layout
require_once __DIR__ . '/../layout/mensajes.php'; // mensajes.php necesita que la sesión ya esté iniciada
require_once __DIR__ . '/../layout/parte2.php';
?>