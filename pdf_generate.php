<?php
session_start();
require 'vendor/autoload.php';

use Dompdf\Dompdf;

if(empty($_SESSION['comprobante'])){ die("Sin comprobante"); }
$c=$_SESSION['comprobante'];

$html="
<h2>Comprobante ParkPlace</h2>
<p><strong>Placa:</strong> {$c['placa']}</p>
<p><strong>Tipo:</strong> {$c['tipo']}</p>
<p><strong>Entrada:</strong> {$c['entrada']}</p>
<p><strong>Salida:</strong> {$c['salida']}</p>
<p><strong>Horas:</strong> {$c['horas']}</p>
<p><strong>Total:</strong> \${$c['total']}</p>
";

$dompdf=new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4','portrait');
$dompdf->render();
$dompdf->stream("comprobante.pdf",["Attachment"=>true]);
