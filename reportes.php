<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
  header("Location:index.php");
  exit;
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Reportes - ParkPlace</title>
  <link rel="stylesheet" href="styles-reportes.css">


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
        <li><a href="admin_dashboard.php">🏠 Panel Principal</a></li>
        <li><a href="registros.php">📋 Registros</a></li>
        <li><a href="tarifas.php">💲 Tarifas</a></li>
        <li><a href="reportes.php" class="active">📊 Reportes</a></li>
      </ul>
      <div class="sidebar-footer">
        <p><b>Rol actual:</b> Administrador</p>
        <a href="logout.php" class="logout">🚪 Cerrar sesión</a>
      </div>
    </aside>

    <!-- Contenido -->
    <main class="main-content">
      <header>
        <h1>📊 Reportes del Sistema</h1>
        <p>Bienvenido <?php echo $_SESSION['nombre']; ?></p>
      </header>

      <!-- Sección de reportes -->
      <section class="intro">
        <!-- Tabla de resumen -->
        <div class="card resumen">
          <h2>Resumen General</h2>
          <table class="tabla-reportes">
            <thead>
              <tr>
                <th>Fecha</th>
                <th>Vehículos Atendidos</th>
                <th>Ingresos Totales</th>
                <th>Duración Promedio</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td>Hoy</td>
                <td>32</td>
                <td>$245,000</td>
                <td>2h 15m</td>
              </tr>
              <tr>
                <td>Ayer</td>
                <td>28</td>
                <td>$210,000</td>
                <td>2h 05m</td>
              </tr>
              <tr>
                <td>Promedio Semanal</td>
                <td>30</td>
                <td>$225,000</td>
                <td>2h 10m</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Gráfico -->
        <div class="card grafico">
          <h2>Ingresos de la Semana</h2>
          <canvas id="graficaReportes" width="600" height="250"></canvas>
        </div>
      </section>
    </main>
  </div>

  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    const ctx = document.getElementById('graficaReportes').getContext('2d');
    const grafica = new Chart(ctx, {
      type: 'line',
      data: {
        labels: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'],
        datasets: [{
          label: 'Ingresos ($)',
          data: [140000, 170000, 180000, 160000, 210000, 270000, 150000],
          borderColor: '#2563eb',
          backgroundColor: 'rgba(37, 99, 235, 0.2)',
          fill: true,
          tension: 0.3
        }]
      },
      options: {
        responsive: true,
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              callback: value => '$' + value.toLocaleString()
            }
          }
        }
      }
    });
  </script>
</body>
</html>
