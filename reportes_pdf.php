<?php
require 'vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$options = new Options();
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);

ob_start();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <style>
    body { font-family: Arial, sans-serif; font-size: 12px; }
    h2, h3 { color: #2563eb; }
    table {
      border-collapse: collapse;
      width: 100%;
      margin-bottom: 20px;
    }
    th {
      background-color: #2563eb;
      color: white;
      padding: 8px;
      text-align: center;
    }
    td {
      border: 1px solid #ddd;
      padding: 8px;
      text-align: center;
    }
    tr:nth-child(even) { background-color: #f9f9f9; }
    .header {
      text-align: center;
      border-bottom: 2px solid #333;
      padding-bottom: 10px;
      margin-bottom: 20px;
    }
    .footer {
      text-align: center;
      font-size: 10px;
      position: fixed;
      bottom: -20px;
      left: 0;
      right: 0;
      color: #555;
    }
  </style>
</head>
<body>

<div class="header">
  <img src="logo.png" width="80" style="margin-bottom:5px;">
  <h2>ParkPlace</h2>
  <p>Sistema de Gestión de Parqueaderos</p>
  <p>📅 Fecha de emisión: <?= date("d/m/Y H:i"); ?></p>
</div>

<h3>🚗 Clientes Frecuentes</h3>
<table>
  <thead>
    <tr>
      <th>Placa</th><th>Visitas</th><th>Última Visita</th><th>Total Gastado</th>
    </tr>
  </thead>
  <tbody>
    <tr><td>ABC-123</td><td>15</td><td>Hoy</td><td>$45,000</td></tr>
    <tr><td>DEF-456</td><td>12</td><td>Ayer</td><td>$36,000</td></tr>
    <tr><td>GHI-789</td><td>10</td><td>Hoy</td><td>$30,000</td></tr>
    <tr><td>JKL-012</td><td>8</td><td>2 días</td><td>$24,000</td></tr>
    <tr><td>MNO-345</td><td>7</td><td>Hoy</td><td>$21,000</td></tr>
  </tbody>
</table>

<h3>📊 Resumen Semanal Detallado</h3>
<table>
  <thead>
    <tr>
      <th>Día</th><th>Vehículos</th><th>Ingresos</th><th>Promedio por Vehículo</th><th>% del Total Semanal</th>
    </tr>
  </thead>
  <tbody>
    <tr><td>Lun</td><td>89</td><td>$267,000</td><td>$3,000</td><td>13.2%</td></tr>
    <tr><td>Mar</td><td>95</td><td>$285,000</td><td>$3,000</td><td>14.1%</td></tr>
    <tr><td>Mié</td><td>102</td><td>$306,000</td><td>$3,000</td><td>15.1%</td></tr>
    <tr><td>Jue</td><td>87</td><td>$261,000</td><td>$3,000</td><td>12.9%</td></tr>
    <tr><td>Vie</td><td>110</td><td>$330,000</td><td>$3,000</td><td>16.3%</td></tr>
    <tr><td>Sáb</td><td>125</td><td>$375,000</td><td>$3,000</td><td>18.5%</td></tr>
    <tr><td>Dom</td><td>68</td><td>$204,000</td><td>$3,000</td><td>10.1%</td></tr>
  </tbody>
</table>

<h3>📌 Totales Generales</h3>
<table>
  <tr><th>Total Vehículos</th><th>Total Ingresos</th><th>Promedio Diario</th></tr>
  <tr><td>676</td><td>$2,028,000</td><td>$289,714</td></tr>
</table>

<div class="footer">
  Reporte generado por ParkPlace © <?= date("Y"); ?>
</div>

</body>
</html>

<?php
$html = ob_get_clean();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// ✅ Numeración de páginas
$canvas = $dompdf->getCanvas();
$canvas->page_script(function ($pageNumber, $pageCount, $canvas, $fontMetrics) {
    $text = "Página $pageNumber de $pageCount";
    $font = $fontMetrics->get_font("Arial", "normal");
    $size = 10;
    $canvas->text(520, 820, $text, $font, $size);
});

$dompdf->stream("reportes.pdf", ["Attachment" => true]);
