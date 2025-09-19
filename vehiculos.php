<?php
session_start();
require 'db.php';
if ($_SESSION['rol']!=='administrador') { header("Location:index.php"); exit; }

$vehiculos=$pdo->query("SELECT * FROM vehiculos ORDER BY created_at DESC")->fetchAll();
?>
<!doctype html>
<html lang="es">
<head><meta charset="utf-8"><link rel="stylesheet" href="styles.css"></head>
<body>
<h1>Vehículos registrados</h1>
<table>
<tr><th>Placa</th><th>Tipo</th><th>Fecha Registro</th></tr>
<?php foreach($vehiculos as $v): ?>
<tr>
  <td><?php echo $v['placa']; ?></td>
  <td><?php echo $v['tipo']; ?></td>
  <td><?php echo $v['created_at']; ?></td>
</tr>
<?php endforeach; ?>
</table>
</body>
</html>
