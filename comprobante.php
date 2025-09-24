<?php
session_start();
if(empty($_SESSION['comprobante'])){ header("Location: operador_dashboard.php"); exit; }
$c=$_SESSION['comprobante'];
?>
<!doctype html>
<html lang="es">
<head><meta charset="utf-8"><link rel="stylesheet" href="styles.css"></head>
<body>
<div class="card">
  <h2>Comprobante de Pago</h2>
  <p><strong>Placa:</strong> <?php echo $c['placa']; ?></p>
  <p><strong>Tipo:</strong> <?php echo $c['tipo']; ?></p>
  <p><strong>Entrada:</strong> <?php echo $c['entrada']; ?></p>
  <p><strong>Salida:</strong> <?php echo $c['salida']; ?></p>
  <p><strong>Tiempo exacto:</strong> <?= $_SESSION['comprobante']['tiempoExacto'] ?></p>
  <p><strong>Total:</strong> $<?php echo $c['total']; ?></p>
  <button onclick="window.print()">Imprimir</button>
  <a href="pdf_generate.php" target="_blank">Descargar PDF</a>
</div>
</body>
</html>

