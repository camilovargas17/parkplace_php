<?php
session_start();
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
  header("Location:index.php");
  exit;
}

// Conexión centralizada con PDO
require_once "db.php";

// Vehículos activos (entraron y aún no tienen salida)
$stmt = $pdo->query("SELECT COUNT(*) AS activos FROM registros WHERE hora_salida IS NULL");
$vehiculosActivos = $stmt->fetch(PDO::FETCH_ASSOC)['activos'] ?? 0;

// Ingresos del día
$stmt = $pdo->query("SELECT SUM(costo) AS ingresos FROM registros WHERE DATE(created_at) = CURDATE()");
$ingresosDia = $stmt->fetch(PDO::FETCH_ASSOC)['ingresos'] ?? 0;

// Total registros del día
$stmt = $pdo->query("SELECT COUNT(*) AS total FROM registros WHERE DATE(created_at) = CURDATE()");
$totalRegistros = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Ingresos semanales (Lun-Dom)
$ingresosSemana = [];
for ($i = 0; $i < 7; $i++) {
    $dia = date('Y-m-d', strtotime("monday this week +$i day"));
    $stmt = $pdo->query("SELECT SUM(costo) AS ingresos FROM registros WHERE DATE(created_at) = '$dia'");
    $ingresosSemana[] = $stmt->fetch(PDO::FETCH_ASSOC)['ingresos'] ?? 0;
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
        <li><a href="registro_entrada.php">⬅️Registrar Entrada</a></li>
        <li><a href="registro_salida.php">➡️Registrar Salida</a></li>
        <li><a href="vehiculos.php">🚗 Vehículos</a></li>
        <li><a href="tarifas.php">💲 Tarifas</a></li>
        <li><a href="reportes.php">📊 Reportes</a></li>
        <li><a href="espacios.php">🅿️ Espacios</a></li>
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
          <p class="number"><?= $vehiculosActivos ?></p>
          <small>Actualmente en el parqueadero</small>
        </div>
        <div class="card">
          <h3>Ingresos del Día</h3>
          <p class="number green">$<?= number_format($ingresosDia, 0, ',', '.') ?></p>
          <small>Acumulado hoy</small>
        </div>
        <div class="card">
          <h3>Total de Registros</h3>
          <p class="number"><?= $totalRegistros ?></p>
          <small>Registros de hoy</small>
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
          data: <?= json_encode($ingresosSemana) ?>,
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
