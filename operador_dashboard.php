<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'operador') {
  header("Location:index.php");
  exit;
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Panel Operador - ParkPlace</title>
  <link rel="stylesheet" href="styles_operator.css">
</head>
<body>
  <div class="dashboard">
    <!-- Sidebar -->
    <aside class="sidebar">
      <div class="sidebar-header">
        <h2>ParkPlace</h2>
        <p>Gestión de parqueadero</p>
      </div>
      <ul class="menu">
        <li><a href="operador_dashboard.php" class="active">🏠 Panel Principal</a></li>
        <li><a href="espacios.php">🅿 Espacios</a></li>
        <li><a href="registro_entrada.php">⬅ Registrar Entrada</a></li>
        <li><a href="registro_salida.php">➡ Registrar Salida</a></li>
      </ul>
      <div class="sidebar-footer">
        <p><b>Rol actual:</b> Operador</p>
        <a href="logout.php" class="logout">🚪 Cerrar sesión</a>
      </div>
    </aside>

    <!-- Contenido -->
    <main class="main-content">
      <header>
        <h1>Panel Operador</h1>
        <p>Bienvenido <?= htmlspecialchars($_SESSION['nombre']); ?></p>
      </header>

      <section class="info">
        <p>
          Desde este panel puedes gestionar los <b>espacios</b>, registrar 
          <b>entradas</b> y <b>salidas</b>.  
          Al registrar una salida, podrás generar el <b>comprobante en PDF</b>.
        </p>
      </section>
    </main>
  </div>
</body>
</html>
