<?php
// Incluir archivos necesarios
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../models/VentasModel.php';
require_once __DIR__ . '/../../models/ClienteModel.php';
require_once __DIR__ . '/../../../layout/sesion.php';

// Incluir TCPDF
require_once __DIR__ . '/../../TCPDF-6.6.0/tcpdf.php';

// Verificar autenticación
if (!isset($_SESSION['id_usuario'])) {
    header('Location: ' . $URL . '/login/');
    exit;
}

// Verificar si se proporcionó un ID de venta
if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    $_SESSION['mensaje'] = "Error: ID de venta no válido.";
    $_SESSION['icono'] = "error";
    header('Location: ' . $URL . '/ventas/');
    exit;
}

$id_venta = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);
$id_usuario_sesion = $_SESSION['id_usuario'];

try {
    // Obtener datos de la venta
    $ventasModel = new VentasModel($pdo, $id_usuario_sesion);
    $clienteModel = new ClienteModel($pdo, $URL);
    
    $venta_info = $ventasModel->getVentaById($id_venta);
    
    if (!$venta_info) {
        $_SESSION['mensaje'] = "Venta no encontrada o no tiene permiso para verla.";
        $_SESSION['icono'] = "warning";
        header('Location: ' . $URL . '/ventas/');
        exit;
    }
    
    $detalles_venta = $ventasModel->getDetallesVentaById($id_venta);
    
    // Obtener información completa del cliente
    $cliente_info = $clienteModel->getClienteByIdAndUsuarioId($venta_info['id_cliente'], $id_usuario_sesion);
    
    // Obtener información del usuario/empresa
    $sql_usuario = "SELECT nombres, email FROM tb_usuarios WHERE id_usuario = :id_usuario";
    $stmt_usuario = $pdo->prepare($sql_usuario);
    $stmt_usuario->bindParam(':id_usuario', $id_usuario_sesion, PDO::PARAM_INT);
    $stmt_usuario->execute();
    $usuario_info = $stmt_usuario->fetch(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    $_SESSION['mensaje'] = "Error al cargar datos de la venta: " . $e->getMessage();
    $_SESSION['icono'] = "error";
    header('Location: ' . $URL . '/ventas/');
    exit;
}

// Crear nueva instancia de TCPDF
class MYPDF extends TCPDF {
    // Cabecera de página
    public function Header() {
        // Logo (si tienes uno, descomenta la siguiente línea)
        // $this->Image('logo.png', 15, 10, 30, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false);
        
        // Título principal
        $this->SetFont('helvetica', 'B', 20);
        $this->SetTextColor(33, 37, 41); // Color bootstrap dark
        $this->Cell(0, 15, 'COMPROBANTE DE VENTA', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(20);
        
        // Línea decorativa
        $this->SetDrawColor(0, 123, 255); // Color bootstrap primary
        $this->SetLineWidth(0.8);
        $this->Line(15, $this->GetY(), 195, $this->GetY());
        $this->Ln(5);
    }
    
    // Pie de página
    public function Footer() {
        $this->SetY(-25);
        
        // Línea decorativa
        $this->SetDrawColor(0, 123, 255);
        $this->SetLineWidth(0.5);
        $this->Line(15, $this->GetY(), 195, $this->GetY());
        $this->Ln(3);
        
        // Información del pie
        $this->SetFont('helvetica', 'I', 8);
        $this->SetTextColor(128, 128, 128);
        $this->Cell(0, 5, 'Documento generado el ' . date('d/m/Y H:i:s'), 0, false, 'L', 0, '', 0, false, 'T', 'M');
        $this->Cell(0, 5, 'Página ' . $this->getAliasNumPage() . ' de ' . $this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
    }
}

// Crear documento PDF
$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Configurar documento
$pdf->SetCreator('Sistema de Ventas');
$pdf->SetAuthor($usuario_info['nombres'] ?? 'Usuario');
$pdf->SetTitle('Factura de Venta #' . $venta_info['codigo_venta_referencia']);
$pdf->SetSubject('Comprobante de Venta');
$pdf->SetKeywords('Venta, Factura, PDF');

// Configurar márgenes
$pdf->SetMargins(15, 35, 15);
$pdf->SetHeaderMargin(5);
$pdf->SetFooterMargin(15);
$pdf->SetAutoPageBreak(TRUE, 25);

// Establecer modo de visualización
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// Agregar página
$pdf->AddPage();

// === INFORMACIÓN DE LA EMPRESA/USUARIO ===
$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetTextColor(33, 37, 41);
$pdf->Cell(0, 8, 'DATOS DEL VENDEDOR', 0, 1, 'L');

$pdf->SetFont('helvetica', '', 10);
$pdf->SetTextColor(73, 80, 87);
$pdf->Cell(40, 6, 'Vendedor:', 0, 0, 'L');
$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetTextColor(33, 37, 41);
$pdf->Cell(0, 6, $venta_info['nombre_vendedor'], 0, 1, 'L');

if (!empty($usuario_info['email'])) {
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetTextColor(73, 80, 87);
    $pdf->Cell(40, 6, 'Email:', 0, 0, 'L');
    $pdf->SetFont('helvetica', '', 10);
    $pdf->SetTextColor(33, 37, 41);
    $pdf->Cell(0, 6, $usuario_info['email'], 0, 1, 'L');
}

$pdf->Ln(5);

// === INFORMACIÓN DE LA VENTA Y CLIENTE ===
// Crear tabla de información general
$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetTextColor(33, 37, 41);
$pdf->Cell(0, 8, 'INFORMACIÓN DE LA VENTA', 0, 1, 'L');

// Fondo para la información principal
$pdf->SetFillColor(248, 249, 250); // Color de fondo claro
$pdf->SetDrawColor(222, 226, 230); // Color del borde
$pdf->SetLineWidth(0.2);

// Información de la venta (lado izquierdo)
$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetTextColor(33, 37, 41);
$pdf->Cell(95, 8, 'DATOS DE LA VENTA', 1, 0, 'C', true);

// Información del cliente (lado derecho)
$pdf->Cell(95, 8, 'DATOS DEL CLIENTE', 1, 1, 'C', true);

// Contenido de la venta
$pdf->SetFont('helvetica', '', 9);
$pdf->SetTextColor(73, 80, 87);

// Lado izquierdo - Datos de venta
$x_left = $pdf->GetX();
$y_start = $pdf->GetY();

$pdf->Cell(95, 6, 'Número de Venta: ' . $venta_info['codigo_venta_referencia'], 'LR', 1, 'L');
$pdf->Cell(95, 6, 'Fecha: ' . date("d/m/Y", strtotime($venta_info['fecha_venta'])), 'LR', 1, 'L');

if (!empty($venta_info['tipo_comprobante'])) {
    $pdf->Cell(95, 6, 'Tipo Comprobante: ' . strtoupper($venta_info['tipo_comprobante']), 'LR', 1, 'L');
}

if (!empty($venta_info['nro_comprobante_fisico'])) {
    $pdf->Cell(95, 6, 'Nro. Comprobante: ' . $venta_info['nro_comprobante_fisico'], 'LR', 1, 'L');
}

// Estado con color
$pdf->SetFont('helvetica', 'B', 9);
switch (strtolower($venta_info['estado_venta'])) {
    case 'pagada':
        $pdf->SetTextColor(40, 167, 69); // Verde
        break;
    case 'pendiente':
        $pdf->SetTextColor(255, 193, 7); // Amarillo
        break;
    case 'anulada':
        $pdf->SetTextColor(220, 53, 69); // Rojo
        break;
    case 'entregada':
        $pdf->SetTextColor(23, 162, 184); // Azul
        break;
    default:
        $pdf->SetTextColor(108, 117, 125); // Gris
}
$pdf->Cell(95, 6, 'Estado: ' . strtoupper($venta_info['estado_venta']), 'LRB', 1, 'L');

// Lado derecho - Datos del cliente
$y_end = $pdf->GetY();
$pdf->SetXY($x_left + 95, $y_start);

$pdf->SetFont('helvetica', '', 9);
$pdf->SetTextColor(73, 80, 87);

$pdf->Cell(95, 6, 'Cliente: ' . $venta_info['nombre_cliente'], 'LR', 1, 'L');

if ($cliente_info && !empty($cliente_info['nit_ci_cliente'])) {
    $tipo_doc = strtoupper($cliente_info['tipo_documento'] ?? 'DOCUMENTO');
    $pdf->Cell(95, 6, $tipo_doc . ': ' . $cliente_info['nit_ci_cliente'], 'LR', 1, 'L');
}

if ($cliente_info && !empty($cliente_info['celular_cliente'])) {
    $pdf->Cell(95, 6, 'Celular: ' . $cliente_info['celular_cliente'], 'LR', 1, 'L');
}

if ($cliente_info && !empty($cliente_info['email_cliente'])) {
    $pdf->Cell(95, 6, 'Email: ' . $cliente_info['email_cliente'], 'LR', 1, 'L');
}

if ($cliente_info && !empty($cliente_info['direccion'])) {
    $pdf->Cell(95, 6, 'Dirección: ' . substr($cliente_info['direccion'], 0, 35) . (strlen($cliente_info['direccion']) > 35 ? '...' : ''), 'LRB', 1, 'L');
} else {
    $pdf->Cell(95, 6, '', 'LRB', 1, 'L');
}

// Ajustar posición para continuar
$pdf->SetY(max($y_end, $pdf->GetY()));

$pdf->Ln(8);

// === OBSERVACIONES ===
if (!empty($venta_info['observaciones'])) {
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetTextColor(33, 37, 41);
    $pdf->Cell(0, 6, 'OBSERVACIONES:', 0, 1, 'L');
    
    $pdf->SetFont('helvetica', '', 9);
    $pdf->SetTextColor(73, 80, 87);
    $pdf->MultiCell(0, 5, $venta_info['observaciones'], 1, 'L', false, 1, '', '', true, 0, false, true, 20, 'T');
    $pdf->Ln(5);
}

// === DETALLE DE PRODUCTOS ===
$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetTextColor(33, 37, 41);
$pdf->Cell(0, 8, 'DETALLE DE PRODUCTOS', 0, 1, 'L');

// Cabecera de la tabla de productos
$pdf->SetFillColor(0, 123, 255); // Azul bootstrap primary
$pdf->SetTextColor(255, 255, 255); // Texto blanco
$pdf->SetDrawColor(0, 123, 255);
$pdf->SetLineWidth(0.3);
$pdf->SetFont('helvetica', 'B', 8);

// Anchos de columnas
$w = array(20, 65, 20, 25, 15, 25, 25);

$pdf->Cell($w[0], 8, 'Código', 1, 0, 'C', true);
$pdf->Cell($w[1], 8, 'Producto', 1, 0, 'C', true);
$pdf->Cell($w[2], 8, 'Cantidad', 1, 0, 'C', true);
$pdf->Cell($w[3], 8, 'P. Unit.', 1, 0, 'C', true);
$pdf->Cell($w[4], 8, '% IVA', 1, 0, 'C', true);
$pdf->Cell($w[5], 8, 'Subtotal', 1, 0, 'C', true);
$pdf->Cell($w[6], 8, 'Total', 1, 1, 'C', true);

// Contenido de la tabla
$pdf->SetFillColor(248, 249, 250);
$pdf->SetTextColor(33, 37, 41);
$pdf->SetFont('helvetica', '', 8);

$fill = false;
foreach ($detalles_venta as $detalle) {
    $pdf->Cell($w[0], 6, $detalle['codigo_producto'], 1, 0, 'C', $fill);
    
    // Truncar nombre del producto si es muy largo
    $nombre_producto = strlen($detalle['nombre_producto']) > 30 ? 
                      substr($detalle['nombre_producto'], 0, 30) . '...' : 
                      $detalle['nombre_producto'];
    $pdf->Cell($w[1], 6, $nombre_producto, 1, 0, 'L', $fill);
    
    $pdf->Cell($w[2], 6, number_format(floatval($detalle['cantidad']), 2), 1, 0, 'C', $fill);
    $pdf->Cell($w[3], 6, '$' . number_format(floatval($detalle['precio_venta_unitario']), 2), 1, 0, 'R', $fill);
    $pdf->Cell($w[4], 6, number_format(floatval($detalle['porcentaje_iva_item']), 1) . '%', 1, 0, 'C', $fill);
    $pdf->Cell($w[5], 6, '$' . number_format(floatval($detalle['subtotal_item']), 2), 1, 0, 'R', $fill);
    $pdf->Cell($w[6], 6, '$' . number_format(floatval($detalle['total_item']), 2), 1, 1, 'R', $fill);
    
    $fill = !$fill;
}

$pdf->Ln(5);

// === RESUMEN FINANCIERO ===
$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetTextColor(33, 37, 41);
$pdf->Cell(0, 8, 'RESUMEN FINANCIERO', 0, 1, 'L');

// Tabla de totales
$pdf->SetXY(120, $pdf->GetY());
$pdf->SetFont('helvetica', 'B', 10);
$pdf->SetFillColor(248, 249, 250);
$pdf->SetDrawColor(222, 226, 230);

$pdf->Cell(45, 6, 'Subtotal General:', 1, 0, 'L', true);
$pdf->Cell(25, 6, '$' . number_format(floatval($venta_info['subtotal_general']), 2), 1, 1, 'R', true);

$pdf->SetXY(120, $pdf->GetY());
$pdf->Cell(45, 6, 'IVA Total:', 1, 0, 'L', true);
$pdf->Cell(25, 6, '$' . number_format(floatval($venta_info['monto_iva_general']), 2), 1, 1, 'R', true);

if (floatval($venta_info['descuento_general']) > 0) {
    $pdf->SetXY(120, $pdf->GetY());
    $pdf->SetTextColor(220, 53, 69); // Rojo para descuentos
    $pdf->Cell(45, 6, 'Descuento General:', 1, 0, 'L', true);
    $pdf->Cell(25, 6, '-$' . number_format(floatval($venta_info['descuento_general']), 2), 1, 1, 'R', true);
    $pdf->SetTextColor(33, 37, 41); // Volver al color normal
}

$pdf->SetXY(120, $pdf->GetY());
$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetFillColor(40, 167, 69); // Verde para total
$pdf->SetTextColor(255, 255, 255); // Texto blanco
$pdf->Cell(45, 8, 'TOTAL GENERAL:', 1, 0, 'L', true);
$pdf->Cell(25, 8, '$' . number_format(floatval($venta_info['total_general']), 2), 1, 1, 'R', true);

$pdf->Ln(10);

// === INFORMACIÓN ADICIONAL ===
$pdf->SetFont('helvetica', 'I', 8);
$pdf->SetTextColor(108, 117, 125);
$pdf->Cell(0, 5, 'Gracias por su compra. Este documento es una representación impresa de su comprobante de venta.', 0, 1, 'C');

// Generar el PDF
$filename = 'Venta_' . $venta_info['codigo_venta_referencia'] . '_' . date('Y-m-d') . '.pdf';

// Limpiar cualquier salida anterior
ob_end_clean();

// Enviar el PDF al navegador
$pdf->Output($filename, 'I');

// Terminar el script
exit();
?>