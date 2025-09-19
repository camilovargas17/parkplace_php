<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol']!=='administrador') {
  header("Location:index.php"); exit;
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Panel Administrador - ParkPlace</title>
  <link rel="stylesheet" href="styles_admin.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        <li><a href="admin_dashboard.php" class="active">🏠 Panel Principal</a></li>
        <li><a href="usuarios.php">👤 Usuarios</a></li>
        <li> <a href="registro_entrada.php">Registrar Entrada</a></li>
        <li><a href="registro_salida.php">Registrar Salida</a></li>
        <li><a href="vehiculos.php">🚗 Vehículos</a></li>
        <li><a href="tarifas.php">💲 Tarifas</a></li>
        <li><a href="reportes.php">📊 Reportes</a></li>
      </ul>
      <div class="sidebar-footer">
        <p><b>Rol actual:</b> Administrador</p>
        <a href="index.php" class="logout">🚪 Cerrar sesión</a>
      </div>
    </aside>

    <!-- Contenido -->
    <main class="main-content">
      <header>
        <h1>Panel Principal</h1>
        <p>Resumen general del parqueadero</p>
      </header>

      <!-- Tarjetas -->
      <section class="cards">
        <div class="card">
          <h3>Vehículos Activos</h3>
          <p class="number">24</p>
          <small>+3 desde ayer</small>
        </div>
        <div class="card">
          <h3>Ingresos del Día</h3>
          <p class="number green">$185,000</p>
          <small>+12% vs ayer</small>
        </div>
        <div class="card">
          <h3>Total de Registros</h3>
          <p class="number">89</p>
          <small>Registros hoy</small>
        </div>
      </section>

      <!-- Gráfica -->
      <section class="chart-section">
        <h3>Ingresos Semanales</h3>
        <canvas id="ingresosChart"></canvas>
      </section>
    </main>
  </div>

  <script>
    const ctx = document.getElementById('ingresosChart').getContext('2d');
    const ingresosChart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
        datasets: [{
          label: 'Ingresos',
          data: [140000, 160000, 180000, 150000, 210000, 280000, 130000],
          backgroundColor: '#1565c0'
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { display: false }
        }
      }
    });
  </script>
</body>
</html>
