<?php
session_start();
require 'db.php';
if ($_SESSION['rol']!=='administrador') { header("Location:index.php"); exit; }

$totalHoy = $pdo->query("SELECT COUNT(*) as c, COALESCE(SUM(total_pagar),0) as suma FROM registros WHERE DATE(hora_salida)=CURDATE()")->fetch();
$hist = $pdo->query("SELECT DATE(hora_salida) as fecha, COUNT(*) as c, SUM(total_pagar) as suma FROM registros WHERE hora_salida IS NOT NULL GROUP BY DATE(hora_salida) ORDER BY fecha DESC LIMIT 7")->fetchAll();
?>
<!doctype html>
<html lang="es">
<head><meta charset="utf-8"><link rel="stylesheet" href="styles.css"></head>
<body>
<h1>Reportes</h1>
<h3>Hoy</h3>
<p>Vehículos: <?php echo $totalHoy['c']; ?> | Ingresos: $<?php echo $totalHoy['suma']; ?></p>
<h3>Últimos 7 días</h3>
<table>
<tr><th>Fecha</th><th>Vehículos</th><th>Ingresos</th></tr>
<?php foreach($hist as $h): ?>
<tr>
  <td><?php echo $h['fecha']; ?></td>
  <td><?php echo $h['c']; ?></td>
  <td>$<?php echo $h['suma']; ?></td>
</tr>
<?php endforeach; ?>
</table>
</body>
</html>
