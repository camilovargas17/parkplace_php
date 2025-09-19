<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol']!=='operador') {
  header("Location:index.php"); exit;
}
?>
<!doctype html>
<html lang="es">
<head><meta charset="utf-8"><link rel="stylesheet" href="styles.css"></head>
<body>
<nav>
  <a href="registro_entrada.php">Registrar Entrada</a>
  <a href="registro_salida.php">Registrar Salida</a>
  <a href="logout.php">Salir</a>
</nav>
<div class="content">
  <h1>Panel Operador</h1>
  <p>Bienvenido <?php echo $_SESSION['nombre']; ?></p>
</div>
</body>
</html>
