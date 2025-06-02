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
        $this->Cell(0, 10, 'COMPROBANTE DE VENTA', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        $this->Ln(15); // Reducido de 20 a 15
        
        // Línea decorativa
        $this->SetDrawColor(0, 123, 255); // Color bootstrap primary
        $this->SetLineWidth(0.8);
        $this->Line(15, $this->GetY(), 195, $this->GetY());
        $this->Ln(3); // Reducido de 5 a 3
    }
    
    // Pie de página
    public function Footer() {
        $this->SetY(-15); // Reducido de -25 a -15
        
        // Línea decorativa
        $this->SetDrawColor(0, 123, 255);
        $this->SetLineWidth(0.5);
        $this->Line(15, $this->GetY(), 195, $this->GetY());
        $this->Ln(2); // Reducido de 3 a 2
        
        // Información del pie
        $this->SetFont('helvetica', 'I', 7); // Reducido de 8 a 7
        $this->SetTextColor(128, 128, 128);
        $this->Cell(0, 4, 'Documento generado el ' . date('d/m/Y H:i:s'), 0, false, 'L', 0, '', 0, false, 'T', 'M');
        $this->Cell(0, 4, 'Página ' . $this->getAliasNumPage() . ' de ' . $this->getAliasNbPages(), 0, false, 'R', 0, '', 0, false, 'T', 'M');
        
        // Agregar mensaje de agradecimiento en el pie de página
        $this->Ln(3);
        $this->Cell(0, 4, 'Gracias por su compra. Este documento es una representación impresa de su comprobante de venta.', 0, false, 'C');
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

// Configurar márgenes - Reducidos para ahorrar espacio
$pdf->SetMargins(15, 30, 15); // Reducido el margen superior de 35 a 30
$pdf->SetHeaderMargin(5);
$pdf->SetFooterMargin(10); // Reducido de 15 a 10
$pdf->SetAutoPageBreak(TRUE, 15); // Reducido de 25 a 15

// Establecer modo de visualización
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

// Agregar página
$pdf->AddPage();

// === INFORMACIÓN DE LA EMPRESA/USUARIO ===
$pdf->SetFont('helvetica', 'B', 11); // Reducido de 12 a 11
$pdf->SetTextColor(33, 37, 41);
$pdf->Cell(0, 6, 'DATOS DEL VENDEDOR', 0, 1, 'L'); // Reducido de 8 a 6

// Vendedor (usando HTML)
$vendedor_html = '<table cellspacing="0" cellpadding="1" border="0">
    <tr>
        <td width="20%" style="font-weight: normal; color: #495057;">Vendedor:</td>
        <td width="80%" style="font-weight: bold; color: #212529;">'.$venta_info['nombre_vendedor'].'</td>
    </tr>';

if (!empty($usuario_info['email'])) {
    $vendedor_html .= '<tr>
        <td width="20%" style="font-weight: normal; color: #495057;">Email:</td>
        <td width="80%" style="font-weight: normal; color: #212529;">'.$usuario_info['email'].'</td>
    </tr>';
}

$vendedor_html .= '</table>';

$pdf->writeHTML($vendedor_html, true, false, true, false, '');
$pdf->Ln(2); // Reducido de 5 a 2

// === INFORMACIÓN DE LA VENTA Y CLIENTE ===
$pdf->SetFont('helvetica', 'B', 11); // Reducido de 12 a 11
$pdf->SetTextColor(33, 37, 41);
$pdf->Cell(0, 6, 'INFORMACIÓN DE LA VENTA', 0, 1, 'L'); // Reducido de 8 a 6

// Preparar los datos para usar en el HTML
$fecha_formateada = date("d/m/Y", strtotime($venta_info['fecha_venta']));
$tipo_comprobante = !empty($venta_info['tipo_comprobante']) ? strtoupper($venta_info['tipo_comprobante']) : '';
$nro_comprobante = !empty($venta_info['nro_comprobante_fisico']) ? $venta_info['nro_comprobante_fisico'] : '';

// Determinar el color del estado
$color_estado = '';
switch (strtolower($venta_info['estado_venta'])) {
    case 'pagada':
        $color_estado = 'color: #28a745;'; // Verde
        break;
    case 'pendiente':
        $color_estado = 'color: #ffc107;'; // Amarillo
        break;
    case 'anulada':
        $color_estado = 'color: #dc3545;'; // Rojo
        break;
    case 'entregada':
        $color_estado = 'color: #17a2b8;'; // Azul
        break;
    default:
        $color_estado = 'color: #6c757d;'; // Gris
}

// Preparar los datos del cliente
$tipo_doc = isset($cliente_info['tipo_documento']) ? strtoupper($cliente_info['tipo_documento']) : 'DOCUMENTO';
$nit_ci = isset($cliente_info['nit_ci_cliente']) ? $cliente_info['nit_ci_cliente'] : '';
$celular = isset($cliente_info['celular_cliente']) ? $cliente_info['celular_cliente'] : '';
$email = isset($cliente_info['email_cliente']) ? $cliente_info['email_cliente'] : '';
$direccion = isset($cliente_info['direccion']) ? $cliente_info['direccion'] : '';
if (strlen($direccion) > 35) {
    $direccion = substr($direccion, 0, 35) . '...';
}

// Generar HTML para la tabla de información - Padding reducido
$info_html = '
<table cellspacing="0" cellpadding="3" border="1" style="border-color: #dee2e6;">
    <tr bgcolor="#f8f9fa" style="font-weight: bold; color: #212529;">
        <td align="center" width="50%">DATOS DE LA VENTA</td>
        <td align="center" width="50%">DATOS DEL CLIENTE</td>
    </tr>
    <tr>
        <td>
            <table cellspacing="0" cellpadding="1" border="0">
                <tr>
                    <td width="40%" style="font-weight: bold; color: #495057;">Número de Venta:</td>
                    <td width="60%" style="color: #212529;">'.$venta_info['codigo_venta_referencia'].'</td>
                </tr>
                <tr>
                    <td width="40%" style="font-weight: bold; color: #495057;">Fecha:</td>
                    <td width="60%" style="color: #212529;">'.$fecha_formateada.'</td>
                </tr>';
                
if (!empty($tipo_comprobante)) {
    $info_html .= '
                <tr>
                    <td width="40%" style="font-weight: bold; color: #495057;">Tipo Comprobante:</td>
                    <td width="60%" style="color: #212529;">'.$tipo_comprobante.'</td>
                </tr>';
}

if (!empty($nro_comprobante)) {
    $info_html .= '
                <tr>
                    <td width="40%" style="font-weight: bold; color: #495057;">Nro. Comprobante:</td>
                    <td width="60%" style="color: #212529;">'.$nro_comprobante.'</td>
                </tr>';
}

$info_html .= '
                <tr>
                    <td width="40%" style="font-weight: bold; color: #495057;">Estado:</td>
                    <td width="60%" style="font-weight: bold; '.$color_estado.'">'.strtoupper($venta_info['estado_venta']).'</td>
                </tr>
            </table>
        </td>
        <td>
            <table cellspacing="0" cellpadding="1" border="0">
                <tr>
                    <td width="30%" style="font-weight: bold; color: #495057;">Cliente:</td>
                    <td width="70%" style="color: #212529;">'.$venta_info['nombre_cliente'].'</td>
                </tr>';

if (!empty($nit_ci)) {
    $info_html .= '
                <tr>
                    <td width="30%" style="font-weight: bold; color: #495057;">'.$tipo_doc.':</td>
                    <td width="70%" style="color: #212529;">'.$nit_ci.'</td>
                </tr>';
}

if (!empty($celular)) {
    $info_html .= '
                <tr>
                    <td width="30%" style="font-weight: bold; color: #495057;">Celular:</td>
                    <td width="70%" style="color: #212529;">'.$celular.'</td>
                </tr>';
}

if (!empty($email)) {
    $info_html .= '
                <tr>
                    <td width="30%" style="font-weight: bold; color: #495057;">Email:</td>
                    <td width="70%" style="color: #212529;">'.$email.'</td>
                </tr>';
}

if (!empty($direccion)) {
    $info_html .= '
                <tr>
                    <td width="30%" style="font-weight: bold; color: #495057;">Dirección:</td>
                    <td width="70%" style="color: #212529;">'.$direccion.'</td>
                </tr>';
}

$info_html .= '
            </table>
        </td>
    </tr>
</table>';

$pdf->writeHTML($info_html, true, false, true, false, '');
$pdf->Ln(3); // Reducido de 8 a 3

// === OBSERVACIONES ===
if (!empty($venta_info['observaciones'])) {
    $pdf->SetFont('helvetica', 'B', 10);
    $pdf->SetTextColor(33, 37, 41);
    $pdf->Cell(0, 5, 'OBSERVACIONES:', 0, 1, 'L'); // Reducido de 6 a 5
    
    $obs_html = '<table cellspacing="0" cellpadding="3" border="1" style="border-color: #dee2e6;">
        <tr>
            <td style="color: #495057;">'.$venta_info['observaciones'].'</td>
        </tr>
    </table>';
    
    $pdf->writeHTML($obs_html, true, false, true, false, '');
    $pdf->Ln(2); // Reducido de 5 a 2
}

// === DETALLE DE PRODUCTOS ===
$pdf->SetFont('helvetica', 'B', 11); // Reducido de 12 a 11
$pdf->SetTextColor(33, 37, 41);
$pdf->Cell(0, 6, 'DETALLE DE PRODUCTOS', 0, 1, 'L'); // Reducido de 8 a 6

// Tabla de productos en HTML - Padding reducido
$productos_html = '
<table cellspacing="0" cellpadding="3" border="1" style="border-color: #007bff;">
    <tr bgcolor="#007bff" style="color: #ffffff; font-weight: bold;">
        <td width="10%" align="center">Código</td>
        <td width="35%" align="center">Producto</td>
        <td width="10%" align="center">Cantidad</td>
        <td width="12%" align="center">P. Unit.</td>
        <td width="8%" align="center">% IVA</td>
        <td width="12%" align="center">Subtotal</td>
        <td width="13%" align="center">Total</td>
    </tr>';

$fill = false;
foreach ($detalles_venta as $detalle) {
    // Truncar nombre del producto si es muy largo
    $nombre_producto = strlen($detalle['nombre_producto']) > 30 ? 
                  substr($detalle['nombre_producto'], 0, 30) . '...' : 
                  $detalle['nombre_producto'];
    
    $bg_color = $fill ? ' bgcolor="#f8f9fa"' : '';
    
    $productos_html .= '
    <tr'.$bg_color.'>
        <td align="center">'.$detalle['codigo_producto'].'</td>
        <td>'.$nombre_producto.'</td>
        <td align="center">'.number_format(floatval($detalle['cantidad']), 2).'</td>
        <td align="right">$'.number_format(floatval($detalle['precio_venta_unitario']), 2).'</td>
        <td align="center">'.number_format(floatval($detalle['porcentaje_iva_item']), 1).'%</td>
        <td align="right">$'.number_format(floatval($detalle['subtotal_item']), 2).'</td>
        <td align="right">$'.number_format(floatval($detalle['total_item']), 2).'</td>
    </tr>';
    
    $fill = !$fill;
}

$productos_html .= '</table>';

$pdf->writeHTML($productos_html, true, false, true, false, '');
$pdf->Ln(3); // Reducido de 5 a 3

// === RESUMEN FINANCIERO ===
$pdf->SetFont('helvetica', 'B', 11); // Reducido de 12 a 11
$pdf->SetTextColor(33, 37, 41);
$pdf->Cell(0, 6, 'RESUMEN FINANCIERO', 0, 1, 'L'); // Reducido de 8 a 6

// Calcular los valores
$subtotal = number_format(floatval($venta_info['subtotal_general']), 2);
$iva = number_format(floatval($venta_info['monto_iva_general']), 2);
$descuento = number_format(floatval($venta_info['descuento_general']), 2);
$total = number_format(floatval($venta_info['total_general']), 2);

// Tabla de resumen financiero en HTML - Padding reducido
$resumen_html = '
<table cellspacing="0" cellpadding="3" border="0" align="right">
    <tr>
        <td width="120" bgcolor="#f8f9fa" style="border: 1px solid #dee2e6; font-weight: bold; color: #212529;">Subtotal General:</td>
        <td width="80" align="right" bgcolor="#f8f9fa" style="border: 1px solid #dee2e6;">$'.$subtotal.'</td>
    </tr>
    <tr>
        <td width="120" bgcolor="#f8f9fa" style="border: 1px solid #dee2e6; font-weight: bold; color: #212529;">IVA Total:</td>
        <td width="80" align="right" bgcolor="#f8f9fa" style="border: 1px solid #dee2e6;">$'.$iva.'</td>
    </tr>';

if (floatval($venta_info['descuento_general']) > 0) {
    $resumen_html .= '
    <tr>
        <td width="120" bgcolor="#f8f9fa" style="border: 1px solid #dee2e6; font-weight: bold; color: #dc3545;">Descuento General:</td>
        <td width="80" align="right" bgcolor="#f8f9fa" style="border: 1px solid #dee2e6; color: #dc3545;">-$'.$descuento.'</td>
    </tr>';
}

$resumen_html .= '
    <tr>
        <td width="120" bgcolor="#28a745" style="border: 1px solid #28a745; font-weight: bold; color: #ffffff; font-size: 11pt;">TOTAL GENERAL:</td>
        <td width="80" align="right" bgcolor="#28a745" style="border: 1px solid #28a745; color: #ffffff; font-size: 11pt;">$'.$total.'</td>
    </tr>
</table>';

$pdf->writeHTML($resumen_html, true, false, true, false, '');

// Generar el PDF
$filename = 'Venta_' . $venta_info['codigo_venta_referencia'] . '_' . date('Y-m-d') . '.pdf';

// Limpiar cualquier salida anterior
ob_end_clean();

// Enviar el PDF al navegador
$pdf->Output($filename, 'I');

// Terminar el script
exit();
?>