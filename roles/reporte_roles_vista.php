<?php
// 1. Incluir configuración y iniciar sesión si es necesario para obtener $pdo
if (session_status() === PHP_SESSION_NONE) {
    session_start(); 
}
require_once __DIR__ . '/../app/config.php'; // Para $pdo, $URL

// 2. Verificar permisos (opcional pero recomendado si el reporte es sensible)
// require_once __DIR__ . '/../layout/permisos.php';

// 3. Incluir el modelo de Rol para obtener los datos
require_once __DIR__ . '/../app/models/RolModel.php';

if (!isset($pdo)) {
    die("Error crítico: La conexión a la base de datos no está disponible.");
}

$rolModel = new RolModel($pdo);
$roles_datos = $rolModel->getAllRoles();

// Fecha actual para el reporte
$fecha_reporte = date('d/m/Y H:i:s');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Roles - Sistema de Ventas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .report-container {
            width: 100%;
            max-width: 800px; 
            margin: auto;
        }
        h1 {
            text-align: center;
            color: #333;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .report-info {
            margin-bottom: 20px;
            font-size: 0.9em;
            color: #555;
        }
        .report-info p {
            margin: 5px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            color: #333;
        }
        td.center {
            text-align: center;
        }
        .action-buttons-container { /* Renombrado para más generalidad */
            text-align: center;
            margin-top: 20px;
            margin-bottom: 20px;
        }
        .action-button { /* Clase genérica para botones de acción */
            padding: 10px 20px;
            font-size: 16px;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none; /* Para el enlace del botón de regresar */
            margin-left: 5px; /* Espacio entre botones */
            margin-right: 5px; /* Espacio entre botones */
        }
        .print-button { /* Específico para el botón de imprimir */
            background-color: #007bff;
        }
        .print-button:hover {
            background-color: #0056b3;
        }
        .back-button { /* Específico para el botón de regresar */
            background-color: #6c757d; /* Color gris, puedes cambiarlo */
        }
        .back-button:hover {
            background-color: #5a6268;
        }

        /* Estilos para impresión */
        @media print {
            body {
                margin: 0; 
            }
            .action-buttons-container { /* Ocultar contenedor de botones */
                display: none; 
            }
            h1 {
                 font-size: 1.5em; 
            }
            table, th, td {
                font-size: 0.9em; 
            }
        }
    </style>
</head>
<body>
    <div class="report-container">
        <h1>Reporte de Roles</h1>
        <div class="report-info">
            <p><strong>Fecha del Reporte:</strong> <?php echo $fecha_reporte; ?></p>
            <p><strong>Generado por:</strong> Sistema de Ventas</p>
            <?php if (isset($_SESSION['nombres'])): ?>
                <p><strong>Usuario:</strong> <?php echo htmlspecialchars($_SESSION['nombres']); ?></p>
            <?php endif; ?>
        </div>

        <div class="action-buttons-container">
            <a href="../roles/" class="action-button back-button">Regresar al Listado</a>
            <button class="action-button print-button" onclick="window.print();">Imprimir Reporte</button>
        </div>

        <table>
            <thead>
                <tr>
                    <th style="width:10%; text-align:center;">Nro</th>
                    <th style="width:60%;">Nombre del Rol</th>
                    <th style="width:30%; text-align:center;">Fecha de Creación</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (!empty($roles_datos)) {
                    $contador = 0;
                    foreach ($roles_datos as $rol_dato) {
                        $contador++;
                        ?>
                        <tr>
                            <td class="center"><?php echo $contador; ?></td>
                            <td><?php echo htmlspecialchars($rol_dato['rol']); ?></td>
                            <td class="center"><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($rol_dato['fyh_creacion']))); ?></td>
                        </tr>
                        <?php
                    }
                } else {
                    ?>
                    <tr>
                        <td colspan="3" class="center">No hay roles registrados.</td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>