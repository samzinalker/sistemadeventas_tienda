<?php
// 1nclude the main TCPOF library (search for installation path).

require_once("../app/TCPDF-6.6.0/tcpdf.php");
include("../app/config.php");

//$1d_venta_get = $ GET['id_venta"];
//$nro_venta_get = $_GET[*nro_venta"];

// create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, array(215,279), true, 'UTF-8', false);

// set document information
$pdf->setCreator(PDF_CREATOR);
$pdf->setAuthor('Sistema de parqueo');
$pdf->setTitle('sistema de parqueo');
$pdf->setSubject ('Sistema de parqueo');
$pdf->setKeywords('TCPDF, PDF, example, test, guide');

// remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

//set default monospaced font
$pdf->setDefaultMonospacedFont(PDF_FONT_MONOSPACED) ;

// set margins
$pdf->setMargins(5, 5, 5);

// set auto page breaks
$pdf->setAutoPageBreak(true, 5);

// set image scale factor
$pdf->setImagescale(PDF_IMAGE_SCALE_RATIO);

// set some language-dependent strings (optional)
if (@file_exists(dirname(__FILE__).'/lang/eng.php')) { 
	require_once(dirname(__FILE__).'/lang/eng-php');
	$pdf->setLanguageArray($l);
	}



// set font
$pdf->setFont('Helvetica', '', 7);


$pdf->AddPage();

$html = '
<table border="l" style="font-size :10px">
            <tr>
			 <td style="text-align: center;width: 200px">
			 <b>SiSTEMA DE VENTAS MAMANI</b><br>
			 fffffff<br>
			 ssssss
			 ffffff
			 
			 </td>
			 <td style="width: 200px"></td>
             <td style="font-size: 16px;width: 290px">
			 <b>CEDULA: </b> 1221 3124<br>
             <b>CEDULA: </b> 1221 3124<br>
             <b>CEDULA: </b> 1221 3124<br>22233
			 </td>
			</tr>
			<table>
			 <p style="text-align: center:font-size	:25px"<b>FACTURA</b></p>
			 <div>
			 <table>
			 <tr>
			      <td><b>Fecha:</b> 24	</td>
				  </tr>
				  </table>
				  </div>
			
		
		
		
		
		'
        
 



;
// output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');

$style = array( 

'border'=> 0,

'vpadding' => '3',

'hpadding' => '3',

'fgcolor' => array(0, 0, 0),

'bgcolor' => false, //array(255,255,255)

'module_width' => 1, // width of a single module in points

'module_height'=> 1 // height of a single module in points
);

$QR = 'Factura realizada por el sistema de paqueo de Mamani, al cliente Marcelo Mamani con nit: 42141
con el vehiculo con numero de placa 3983FREDD y esta factura se genero en 21 de octubre de 2022 a hr: 18:00';
$pdf->write2DBarcode($QR, 'QRCODE,L', 22,105,35,35, $style);

//Close and output PDF document
$pdf->Output('example_002.pdf', 'I');

//---
// END OF FILE
//---


