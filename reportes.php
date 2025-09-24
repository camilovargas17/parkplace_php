<?php
session_start();
require 'db.php';

// 🔒 Verificar acceso
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
  header("Location:index.php");
  exit;
}

// ==================== 📊 CONSULTAS ====================

// Ingresos de hoy
$stmt = $pdo->prepare("SELECT COALESCE(SUM(costo),0) FROM registros WHERE DATE(hora_salida) = CURDATE()");
$stmt->execute();
$ingresosHoy = $stmt->fetchColumn();

// Vehículos de hoy
$stmt = $pdo->prepare("SELECT COUNT(*) FROM registros WHERE DATE(hora_entrada) = CURDATE()");
$stmt->execute();
$vehiculosHoy = $stmt->fetchColumn();

// Promedio semanal (ingresos/día)
$stmt = $pdo->prepare("SELECT COALESCE(SUM(costo),0)/7 FROM registros WHERE YEARWEEK(hora_entrada,1) = YEARWEEK(CURDATE(),1)");
$stmt->execute();
$promedioSemanal = $stmt->fetchColumn();

// Ocupación actual
$stmt = $pdo->prepare("SELECT COUNT(*) FROM registros WHERE hora_salida IS NULL");
$stmt->execute();
$ocupacionActual = $stmt->fetchColumn();
$capacidad = 50; // ⚡ Ajusta según tu parqueadero

// Actividad por hora (hoy)
$stmt = $pdo->prepare("SELECT HOUR(hora_entrada) as hora, COUNT(*) as total FROM registros WHERE DATE(hora_entrada) = CURDATE() GROUP BY HOUR(hora_entrada)");
$stmt->execute();
$actividadHoras = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Ingresos semanales
$stmt = $pdo->prepare("SELECT DAYNAME(hora_salida) as dia, SUM(costo) as total FROM registros WHERE YEARWEEK(hora_salida,1) = YEARWEEK(CURDATE(),1) GROUP BY DAYOFWEEK(hora_salida) ORDER BY DAYOFWEEK(hora_salida)");
$stmt->execute();
$ingresosSemanales = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Distribución por tipo de vehículo
$stmt = $pdo->prepare("SELECT v.tipo, COUNT(*) as total FROM registros r INNER JOIN vehiculos v ON v.id = r.vehiculo_id WHERE DATE(r.hora_entrada) = CURDATE() GROUP BY v.tipo");
$stmt->execute();
$tiposVehiculos = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

// Clientes frecuentes
$stmt = $pdo->prepare("SELECT v.placa, COUNT(r.id) as visitas, MAX(r.hora_entrada) as ultima, SUM(r.costo) as gastado FROM registros r INNER JOIN vehiculos v ON v.id = r.vehiculo_id GROUP BY v.placa ORDER BY visitas DESC LIMIT 5");
$stmt->execute();
$clientesFrecuentes = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    .btn-exportar:hover { background: #b91c1c; }
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
        <li><a href="registro_entrada.php">Registrar Entrada</a></li>
        <li><a href="registro_salida.php">Registrar Salida</a></li>
        <li><a href="vehiculos.php">🚗 Vehículos</a></li>
        <li><a href="tarifas.php">💲 Tarifas</a></li>
        <li><a href="reportes.php" class="active">📊 Reportes</a></li>
      </ul>
      <div class="sidebar-footer">
        <p><b>Rol actual:</b> Administrador</p>
        <a href="index.php" class="logout">🚪 Cerrar sesión</a>
      </div>
    </aside>

    <!-- Contenido principal -->
    <main class="main-content">
      <header style="display:flex;align-items:center;justify-content:space-between;">
        <div>
          <h1>📊 Reportes</h1>
          <p>Análisis detallado de operaciones</p>
        </div>
        <a href="reportes_pdf.php" class="btn-exportar">📄 Exportar PDF</a>
      </header>

      <!-- Tarjetas resumen -->
      <section class="cards">
        <div class="card">
          <h3>Ingresos Hoy</h3>
          <p class="number green">$<?= number_format($ingresosHoy,0,',','.') ?></p>
        </div>
        <div class="card">
          <h3>Vehículos Hoy</h3>
          <p class="number"><?= $vehiculosHoy ?></p>
        </div>
        <div class="card">
          <h3>Promedio Semanal</h3>
          <p class="number">$<?= number_format($promedioSemanal,0,',','.') ?></p>
        </div>
        <div class="card">
          <h3>Ocupación Actual</h3>
          <p class="number"><?= $ocupacionActual ?>/<?= $capacidad ?></p>
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
              <tr><th>Placa</th><th>Visitas</th><th>Última Visita</th><th>Total Gastado</th></tr>
            </thead>
            <tbody>
              <?php foreach($clientesFrecuentes as $c): ?>
              <tr>
                <td><?= htmlspecialchars($c['placa']) ?></td>
                <td><?= $c['visitas'] ?></td>
                <td><?= $c['ultima'] ?></td>
                <td>$<?= number_format($c['gastado'],0,',','.') ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </section>
    </main>
  </div>

  <!-- JS de gráficas -->
  <script>
    const actividadHoras = <?= json_encode($actividadHoras) ?>;
    const ingresosSemanales = <?= json_encode($ingresosSemanales) ?>;
    const tiposVehiculos = <?= json_encode($tiposVehiculos) ?>;

    new Chart(document.getElementById('chartHoras'), {
      type: 'bar',
      data: {
        labels: Object.keys(actividadHoras).map(h => h+":00"),
        datasets: [{ data: Object.values(actividadHoras), backgroundColor:'#2563eb' }]
      },
      options: { responsive:true, plugins:{ legend:{display:false} } }
    });

    new Chart(document.getElementById('chartIngresos'), {
      type: 'line',
      data: {
        labels: Object.keys(ingresosSemanales),
        datasets: [{ data: Object.values(ingresosSemanales), borderColor:'#16a34a', tension:0.3 }]
      },
      options: { responsive:true, plugins:{ legend:{display:false} } }
    });

    new Chart(document.getElementById('chartTipos'), {
      type: 'pie',
      data: {
        labels: Object.keys(tiposVehiculos),
        datasets: [{ data: Object.values(tiposVehiculos), backgroundColor:['#2563eb','#16a34a','#dc2626','#9333ea'] }]
      }
    });
  </script>
</body>
</html>
