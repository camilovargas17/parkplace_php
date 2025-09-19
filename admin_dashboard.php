<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol']!=='administrador') {
  header("Location:index.php"); exit;
}
?>
<!doctype html>
<html lang="es">
<head><meta charset="utf-8"><link rel="stylesheet" href="styles.css"></head>
<body>
<nav>
  <a href="vehiculos.php">Vehículos</a>
  <a href="tarifas.php">Tarifas</a>
  <a href="reportes.php">Reportes</a>
  <a href="usuarios.php">Usuarios</a>
  <a href="logout.php">Salir</a>
</nav>
<div class="content">
  <h1>Panel Administrador</h1>
  <p>Bienvenido <?php echo $_SESSION['nombre']; ?></p>
</div>
</body>
</html>
