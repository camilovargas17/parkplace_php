<?php
session_start();
require 'db.php';

// 🔒 Verificar acceso solo para operador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'operador') {
  header("Location: index.php");
  exit;
}

// 📊 Consulta rápida
$stmt = $pdo->query("
  SELECT e.ocupado, r.hora_entrada, r.hora_salida, v.placa
  FROM espacios e
  LEFT JOIN vehiculos v ON v.id = e.vehiculo_id
  LEFT JOIN registros r ON r.vehiculo_id = v.id
");
$espacios = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total = count($espacios);
$ocupados = count(array_filter($espacios, fn($e) => $e['ocupado']));
$libres = $total - $ocupados;

// 🚗 Entradas y salidas de hoy
$hoy = date("Y-m-d");
$entradasHoy = $pdo->query("
  SELECT COUNT(*) FROM registros WHERE DATE(hora_entrada) = '$hoy'
")->fetchColumn();

$salidasHoy = $pdo->query("
  SELECT COUNT(*) FROM registros WHERE DATE(hora_salida) = '$hoy'
")->fetchColumn();

// 📋 Últimos movimientos
$ultimos = $pdo->query("
  SELECT v.placa, 
         CASE WHEN r.hora_salida IS NULL THEN 'Entrada' ELSE 'Salida' END AS accion, 
         COALESCE(r.hora_salida, r.hora_entrada) AS hora
  FROM registros r
  JOIN vehiculos v ON v.id = r.vehiculo_id
  ORDER BY r.id DESC
  LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Panel Operador</title>
  <link rel="stylesheet" href="styles_admin.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    .cards { display: flex; gap: 20px; margin: 20px 0; flex-wrap: wrap; }
    .card {
      flex: 1;
      min-width: 180px;
      padding: 20px;
      border-radius: 12px;
      color: white;
      text-align: center;
      font-size: 18px;
      font-weight: bold;
    }
    .verde { background: #16a34a; }
    .rojo { background: #dc2626; }
    .azul { background: #2563eb; }
    .naranja { background: #ea580c; }
    .tabla { width: 100%; border-collapse: collapse; margin-top: 20px; }
    .tabla th, .tabla td { padding: 8px; border-bottom: 1px solid #ddd; text-align: center; }
    .quick-actions { margin: 25px 0; display: flex; gap: 15px; }
    .btn {
      padding: 12px 18px;
      border-radius: 8px;
      color: white;
      text-decoration: none;
      font-weight: bold;
    }
    .btn.verde { background: #16a34a; }
    .btn.rojo { background: #dc2626; }
    .btn.azul { background: #2563eb; }
    .chart-container { width: 350px; margin: 20px auto; }
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
        <li><a href="operador_dashboard.php" class="active">🏠 Panel Principal</a></li>
        <li><a href="espacios_operador.php">🅿️ Espacios</a></li>
        <li><a href="registro_entrada.php">⬅️ Registrar Entrada</a></li>
        <li><a href="registro_salida.php">➡️ Registrar Salida</a></li>
      </ul>
      <div class="sidebar-footer">
        <p><b>Rol actual:</b> Operador</p>
        <a href="index.php" class="logout">🚪 Cerrar sesión</a>
      </div>
    </aside>

    <!-- Contenido -->
    <main class="main-content">
      <header>
        <h1>📊 Panel de Control</h1>
        <p>Bienvenido <?= htmlspecialchars($_SESSION['nombre'] ?? 'Operador'); ?>, aquí tienes un resumen del parqueadero.</p>
      </header>

      <!-- Tarjetas -->
      <section class="cards">
        <div class="card rojo">🟥 Ocupados<br><?= $ocupados ?></div>
        <div class="card verde">🟩 Libres<br><?= $libres ?></div>
        <div class="card azul">📦 Total<br><?= $total ?></div>
        <div class="card naranja">🕒 Entradas Hoy<br><?= $entradasHoy ?></div>
      </section>

      <!-- Gráfico -->
      <div class="chart-container">
        <canvas id="ocupacionChart"></canvas>
      </div>

      <!-- Últimos movimientos -->
      <section>
        <h2>📋 Últimos Movimientos</h2>
        <table class="tabla">
          <tr><th>Placa</th><th>Acción</th><th>Hora</th></tr>
          <?php foreach($ultimos as $u): ?>
            <tr>
              <td><?= htmlspecialchars($u['placa']) ?></td>
              <td><?= htmlspecialchars($u['accion']) ?></td>
              <td><?= htmlspecialchars($u['hora']) ?></td>
            </tr>
          <?php endforeach; ?>
        </table>
      </section>

      <!-- Acciones rápidas -->
      <section class="quick-actions">
        <a href="registro_entrada.php" class="btn verde">➕ Registrar Entrada</a>
        <a href="registro_salida.php" class="btn rojo">➖ Registrar Salida</a>
        <a href="espacios_operador.php" class="btn azul">🅿️ Ver Espacios</a>
      </section>
    </main>
  </div>

  <script>
    const ctx = document.getElementById('ocupacionChart');
    new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: ['Ocupados', 'Libres'],
        datasets: [{
          data: [<?= $ocupados ?>, <?= $libres ?>],
          backgroundColor: ['#dc2626', '#16a34a']
        }]
      },
      options: { plugins: { legend: { position: 'bottom' } } }
    });
  </script>
</body>
</html>
