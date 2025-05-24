<?php
// Incluir la biblioteca TCPDF y la configuración
require_once("../app/TCPDF-6.6.0/tcpdf.php");
include("../app/config.php");

// Verificar que se recibió un id_venta
if(!isset($_GET['id_venta'])) {
    die("Error: No se especificó el ID de la venta");
}

$id_venta = $_GET['id_venta'];

// Obtener datos de la venta
$sql_venta = "SELECT v.*, 
                     c.nombre_cliente, c.nit_ci_cliente, c.celular_cliente, c.email_cliente,
                     u.nombres as nombre_vendedor
              FROM tb_ventas v
              INNER JOIN tb_clientes c ON v.id_cliente = c.id_cliente
              INNER JOIN tb_usuarios u ON v.id_usuario = u.id_usuario
              WHERE v.id_venta = :id_venta";
$query_venta = $pdo->prepare($sql_venta);
$query_venta->bindParam(':id_venta', $id_venta, PDO::PARAM_INT);
$query_venta->execute();

if($query_venta->rowCount() == 0) {
    die("Error: Venta no encontrada");
}

$venta = $query_venta->fetch(PDO::FETCH_ASSOC);
$nro_venta = $venta['nro_venta'];
$fecha_venta = date('d/m/Y H:i:s', strtotime($venta['fyh_creacion']));
$total_venta = $venta['total_pagado'];

// Obtener productos de la venta
$sql_productos = "SELECT ca.*, 
                         p.nombre, p.codigo, p.descripcion, p.precio_venta
                  FROM tb_carrito ca
                  INNER JOIN tb_almacen p ON ca.id_producto = p.id_producto
                  WHERE ca.nro_venta = :nro_venta";
$query_productos = $pdo->prepare($sql_productos);
$query_productos->bindParam(':nro_venta', $nro_venta, PDO::PARAM_INT);
$query_productos->execute();
$productos = $query_productos->fetchAll(PDO::FETCH_ASSOC);

// Creación del documento PDF
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

// Configuración del documento
$pdf->SetCreator('Sistema de Ventas');
$pdf->SetAuthor('Simulador de Ventas');
$pdf->SetTitle('Factura Simulada');
$pdf->SetSubject('Factura Simulada - No Válida');
$pdf->SetKeywords('Factura, Simulación, Práctica');

// Eliminar cabecera y pie de página predeterminados
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Establecer márgenes
$pdf->SetMargins(15, 15, 15);
$pdf->SetAutoPageBreak(true, 15);

// Agregar página
$pdf->AddPage();

// Fuente principal
$pdf->SetFont('helvetica', '', 10);

// ADVERTENCIA DE SIMULACIÓN (en la parte superior)
$pdf->SetFont('helvetica', 'B', 14);
$pdf->SetTextColor(255, 0, 0);
$pdf->Cell(0, 10, 'FACTURA SIMULADA - NO VÁLIDA PARA DECLARACIONES TRIBUTARIAS', 0, 1, 'C');
$pdf->Cell(0, 10, 'DOCUMENTO DE PRUEBA - NO TIENE VALIDEZ LEGAL', 0, 1, 'C');
$pdf->SetTextColor(0, 0, 0);

// Información de la empresa (ficticia)
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(0, 8, 'TIENDA SIMULADA S.A.', 0, 1, 'C');
$pdf->SetFont('helvetica', '', 10);
$pdf->Cell(0, 6, 'RUC: 9999999999999', 0, 1, 'C');
$pdf->Cell(0, 6, 'Av. Simulación 789, Quito - Ecuador', 0, 1, 'C');
$pdf->Cell(0, 6, 'Tel: (02) 9999-999 / Email: simulacion@ejemplo.com', 0, 1, 'C');

// Línea separadora
$pdf->Line(15, $pdf->GetY() + 3, 195, $pdf->GetY() + 3);
$pdf->Ln(6);

// Información de la factura
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(90, 8, 'DATOS DE LA FACTURA', 0, 0);
$pdf->Cell(90, 8, 'DATOS DEL CLIENTE', 0, 1);

$pdf->SetFont('helvetica', '', 9);
$pdf->Cell(30, 6, 'Factura No.:', 0, 0);
$pdf->Cell(60, 6, $venta['nro_venta'], 0, 0);
$pdf->Cell(30, 6, 'Cliente:', 0, 0);
$pdf->Cell(60, 6, 'Consumidor Final SIMULADO', 0, 1);

$pdf->Cell(30, 6, 'Fecha emisión:', 0, 0);
$pdf->Cell(60, 6, $fecha_venta, 0, 0);
$pdf->Cell(30, 6, 'CI/RUC:', 0, 0);
$pdf->Cell(60, 6, '9999999999', 0, 1);

$pdf->Cell(30, 6, 'Vendedor:', 0, 0);
$pdf->Cell(60, 6, $venta['nombre_vendedor'], 0, 0);
$pdf->Cell(30, 6, 'Teléfono:', 0, 0);
$pdf->Cell(60, 6, '099-999-9999', 0, 1);

$pdf->Cell(30, 6, 'Forma de pago:', 0, 0);
$pdf->Cell(60, 6, 'Efectivo', 0, 0);
$pdf->Cell(30, 6, 'Email:', 0, 0);
$pdf->Cell(60, 6, 'cliente@ejemplo.com', 0, 1);

// Línea separadora
$pdf->Line(15, $pdf->GetY() + 3, 195, $pdf->GetY() + 3);
$pdf->Ln(6);

// Tabla de productos
$pdf->SetFont('helvetica', 'B', 9);
$pdf->SetFillColor(240, 240, 240);
$pdf->Cell(15, 8, 'CANT', 1, 0, 'C', true);
$pdf->Cell(30, 8, 'CÓDIGO', 1, 0, 'C', true);
$pdf->Cell(85, 8, 'DESCRIPCIÓN', 1, 0, 'C', true);
$pdf->Cell(25, 8, 'P. UNIT', 1, 0, 'C', true);
$pdf->Cell(25, 8, 'SUBTOTAL', 1, 1, 'C', true);

$pdf->SetFont('helvetica', '', 9);
$subtotal = 0;

// Añadir productos
foreach($productos as $producto) {
    // Calcular el precio total por producto
    $total_producto = $producto['cantidad'] * $producto['precio_venta'];
    $subtotal += $total_producto;
    
    // Simplificar nombre del producto según requisitos
    $nombre_simplificado = preg_replace('/ (Tipo |Marca |Super |Extra |Premium ).*$/i', '', $producto['nombre']);
    
    // Fila de producto
    $pdf->Cell(15, 7, $producto['cantidad'], 1, 0, 'C');
    $pdf->Cell(30, 7, $producto['codigo'], 1, 0, 'C');
    $pdf->Cell(85, 7, $nombre_simplificado, 1, 0, 'L');
    $pdf->Cell(25, 7, '$' . number_format($producto['precio_venta'], 2), 1, 0, 'R');
    $pdf->Cell(25, 7, '$' . number_format($total_producto, 2), 1, 1, 'R');
}

// Totales
$pdf->SetFont('helvetica', 'B', 9);

// Celdas vacías para alinear los totales a la derecha
$pdf->Cell(130, 7, '', 0, 0);
$pdf->Cell(25, 7, 'SUBTOTAL: ', 1, 0, 'R');
$pdf->Cell(25, 7, '$' . number_format($subtotal, 2), 1, 1, 'R');

// IVA (valor simulado)
$iva = 0; // No ponemos IVA para evitar problemas con SRI
$pdf->Cell(130, 7, '', 0, 0);
$pdf->Cell(25, 7, 'IVA (0%): ', 1, 0, 'R');
$pdf->Cell(25, 7, '$' . number_format($iva, 2), 1, 1, 'R');

// Total final
$pdf->Cell(130, 7, '', 0, 0);
$pdf->Cell(25, 7, 'TOTAL: ', 1, 0, 'R');
$pdf->Cell(25, 7, '$' . number_format($subtotal + $iva, 2), 1, 1, 'R');

// Espacio antes de las notas
$pdf->Ln(10);

// Notas importantes 
$pdf->SetFont('helvetica', 'B', 10);
$pdf->Cell(0, 8, 'NOTAS IMPORTANTES:', 0, 1);
$pdf->SetFont('helvetica', '', 9);
$pdf->MultiCell(0, 5, 'Este documento es solo para fines educativos y de prueba. No constituye una factura legalmente válida ni puede ser utilizada para declaraciones fiscales o contables de ningún tipo.', 0, 'L');

// Pie con advertencia de simulación
$pdf->SetY(-40);
$pdf->Line(15, $pdf->GetY(), 195, $pdf->GetY());
$pdf->SetFont('helvetica', 'B', 12);
$pdf->SetTextColor(255, 0, 0);
$pdf->Cell(0, 10, 'DOCUMENTO DE PRUEBA - NO TIENE VALIDEZ LEGAL', 0, 1, 'C');
$pdf->Cell(0, 10, 'FACTURA SIMULADA - NO VÁLIDA PARA DECLARACIONES TRIBUTARIAS', 0, 1, 'C');

// Generar código QR con mensaje de simulación
$style = array(
    'border' => 0,
    'vpadding' => '3',
    'hpadding' => '3',
    'fgcolor' => array(0, 0, 0),
    'bgcolor' => false,
    'module_width' => 1,
    'module_height' => 1
);

$qr_text = "DOCUMENTO DE SIMULACIÓN\nFactura #: " . $venta['nro_venta'] . "\nFecha: " . $fecha_venta . "\nTotal: $" . number_format($subtotal + $iva, 2) . "\nEste documento no tiene validez legal ni fiscal";
$pdf->write2DBarcode($qr_text, 'QRCODE,L', 155, -40, 40, 40, $style);

// Salida del documento
$pdf->Output('factura_simulada_' . $venta['nro_venta'] . '.pdf', 'I');