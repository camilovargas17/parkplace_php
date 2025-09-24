<?php
require 'vendor/autoload.php';
require 'db.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$options = new Options();
$options->set('isRemoteEnabled', true);

$dompdf = new Dompdf($options);

// 📌 CONSULTAS DINÁMICAS (ajustadas a "costo")

// Ingresos de hoy
$ingresosHoy = $pdo->query("SELECT IFNULL(SUM(costo),0) FROM registros WHERE DATE(hora_salida)=CURDATE()")->fetchColumn();

// Vehículos hoy
$vehiculosHoy = $pdo->query("SELECT COUNT(*) FROM registros WHERE DATE(hora_entrada)=CURDATE()")->fetchColumn();

// Promedio semanal (últimos 7 días)
$promedioSemanal = $pdo->query("SELECT IFNULL(AVG(costo),0) FROM registros WHERE YEARWEEK(hora_entrada,1)=YEARWEEK(CURDATE(),1)")->fetchColumn();

// Ocupación actual
$capacidadMax = 50; // 🔹 cámbialo según tu parqueadero
$ocupados = $pdo->query("SELECT COUNT(*) FROM registros WHERE hora_salida IS NULL")->fetchColumn();

// Clientes frecuentes
$clientes = $pdo->query("
    SELECT v.placa, COUNT(r.id) as visitas, MAX(r.hora_entrada) as ultima_visita, SUM(r.costo) as gastado
    FROM registros r
    JOIN vehiculos v ON r.vehiculo_id=v.id
    GROUP BY v.placa
    ORDER BY visitas DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Resumen semanal detallado
$resumen = $pdo->query("
    SELECT DAYNAME(hora_entrada) as dia, COUNT(*) as vehiculos, SUM(costo) as ingresos
    FROM registros
    WHERE YEARWEEK(hora_entrada,1)=YEARWEEK(CURDATE(),1)
    GROUP BY DAYOFWEEK(hora_entrada)
    ORDER BY DAYOFWEEK(hora_entrada)
")->fetchAll(PDO::FETCH_ASSOC);

// Totales
$totales = $pdo->query("SELECT COUNT(*) as vehiculos, SUM(costo) as ingresos FROM registros")->fetch(PDO::FETCH_ASSOC);

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

<h3>📌 Resumen General</h3>
<table>
  <tr><th>Ingresos Hoy</th><th>Vehículos Hoy</th><th>Promedio Semanal</th><th>Ocupación Actual</th></tr>
  <tr>
    <td>$<?= number_format($ingresosHoy,0,",","."); ?></td>
    <td><?= $vehiculosHoy; ?></td>
    <td>$<?= number_format($promedioSemanal,0,",","."); ?></td>
    <td><?= $ocupados ?>/<?= $capacidadMax; ?></td>
  </tr>
</table>

<h3>🚗 Clientes Frecuentes</h3>
<table>
  <thead>
    <tr>
      <th>Placa</th><th>Visitas</th><th>Última Visita</th><th>Total Gastado</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($clientes as $c): ?>
    <tr>
      <td><?= $c['placa']; ?></td>
      <td><?= $c['visitas']; ?></td>
      <td><?= $c['ultima_visita']; ?></td>
      <td>$<?= number_format($c['gastado'],0,",","."); ?></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<h3>📊 Resumen Semanal Detallado</h3>
<table>
  <thead>
    <tr>
      <th>Día</th><th>Vehículos</th><th>Ingresos</th><th>Promedio por Vehículo</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($resumen as $r): ?>
    <tr>
      <td><?= $r['dia']; ?></td>
      <td><?= $r['vehiculos']; ?></td>
      <td>$<?= number_format($r['ingresos'],0,",","."); ?></td>
      <td>$<?= number_format(($r['ingresos']/$r['vehiculos']),0,",","."); ?></td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<h3>📌 Totales Generales</h3>
<table>
  <tr><th>Total Vehículos</th><th>Total Ingresos</th></tr>
  <tr>
    <td><?= $totales['vehiculos']; ?></td>
    <td>$<?= number_format($totales['ingresos'],0,",","."); ?></td>
  </tr>
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

// Numeración de páginas
$canvas = $dompdf->getCanvas();
$canvas->page_script(function ($pageNumber, $pageCount, $canvas, $fontMetrics) {
    $text = "Página $pageNumber de $pageCount";
    $font = $fontMetrics->get_font("Arial", "normal");
    $size = 10;
    $canvas->text(520, 820, $text, $font, $size);
});

$dompdf->stream("reportes.pdf", ["Attachment" => true]);
