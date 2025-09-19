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
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    .btn-exportar {
      display: inline-block;
      padding: 8px 15px;
      background: #dc2626;
      color: white;
      border-radius: 6px;
      text-decoration: none;
      font-weight: bold;
      margin-left: 15px;
      transition: background 0.3s;
    }
    .btn-exportar:hover {
      background: #b91c1c;
    }
  </style>
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
        <li><a href="usuarios.php">👤 Usuarios</a></li>
        <li><a href="registros.php">📋 Registros</a></li>
        <li><a href="tarifas.php">💲 Tarifas</a></li>
        <li><a href="reportes.php" class="active">📊 Reportes</a></li>
      </ul>
      <div class="sidebar-footer">
        <p><b>Rol actual:</b> Administrador</p>
        <a href="logout.php" class="logout">🚪 Cerrar sesión</a>
      </div>
    </aside>

    <!-- Contenido principal -->
    <main class="main-content">
      <header style="display:flex;align-items:center;justify-content:space-between;">
        <div>
          <h1>📊 Reportes</h1>
          <p>Análisis detallado de operaciones</p>
        </div>
        <!-- Botón exportar PDF -->
        <a href="reportes_pdf.php" class="btn-exportar">📄 Exportar PDF</a>
      </header>

      <!-- Tarjetas resumen -->
      <section class="cards">
        <div class="card">
          <h3>Ingresos Hoy</h3>
          <p class="number green">$366,000</p>
          <small>+15% vs ayer</small>
        </div>
        <div class="card">
          <h3>Vehículos Hoy</h3>
          <p class="number">122</p>
          <small>+8% vs ayer</small>
        </div>
        <div class="card">
          <h3>Promedio Semanal</h3>
          <p class="number">$289,714</p>
          <small>Por día</small>
        </div>
        <div class="card">
          <h3>Ocupación Actual</h3>
          <p class="number">24/50</p>
          <small>48% ocupado</small>
        </div>
      </section>

      <!-- Gráficas -->
      <section class="charts">
        <div class="card">
          <h3>Actividad por Hora - Hoy</h3>
          <canvas id="chartHoras"></canvas>
        </div>
        <div class="card">
          <h3>Ingresos Semanales</h3>
          <canvas id="chartIngresos"></canvas>
        </div>
      </section>

      <!-- Distribución y Clientes -->
      <section class="charts">
        <div class="card">
          <h3>Distribución por Tipo de Vehículo</h3>
          <canvas id="chartTipos"></canvas>
        </div>
        <div class="card">
          <h3>Clientes Frecuentes</h3>
          <table class="tabla-reportes">
            <thead>
              <tr>
                <th>Placa</th>
                <th>Visitas</th>
                <th>Última Visita</th>
                <th>Total Gastado</th>
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
        </div>
      </section>

      <!-- Resumen semanal detallado -->
      <section class="card">
        <h3>Resumen Semanal Detallado</h3>
        <table class="tabla-reportes">
          <thead>
            <tr>
              <th>Día</th>
              <th>Vehículos</th>
              <th>Ingresos</th>
              <th>Promedio por Vehículo</th>
              <th>% del Total Semanal</th>
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
      </section>
    </main>
  </div>

  <!-- JS de gráficas -->
  <script>
    // Actividad por hora
    new Chart(document.getElementById('chartHoras'), {
      type: 'bar',
      data: {
        labels: ['06:00','08:00','10:00','12:00','14:00','16:00','18:00','20:00'],
        datasets: [{ data: [2,14,10,18,22,20,15,7], backgroundColor:'#2563eb' }]
      },
      options: { responsive:true, plugins:{ legend:{display:false} } }
    });

    // Ingresos semanales
    new Chart(document.getElementById('chartIngresos'), {
      type: 'line',
      data: {
        labels: ['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'],
        datasets: [{ data: [267000,285000,306000,261000,330000,375000,204000], borderColor:'#16a34a', tension:0.3 }]
      },
      options: { responsive:true, plugins:{ legend:{display:false} } }
    });

    // Distribución de tipos
    new Chart(document.getElementById('chartTipos'), {
      type: 'pie',
      data: {
        labels: ['Automóviles','Motocicletas','Camiones','Bicicletas'],
        datasets: [{ data: [65,24,8,3], backgroundColor:['#2563eb','#16a34a','#dc2626','#9333ea'] }]
      }
    });
  </script>
</body>
</html>
