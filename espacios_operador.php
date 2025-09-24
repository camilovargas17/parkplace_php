<?php
session_start();
require 'db.php';

// 🔒 Verificar acceso solo para operador
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'operador') {
  header("Location: operador_dashboard.php");
  exit;
}

// 📊 Consulta de los espacios
$stmt = $pdo->query("
  SELECT e.id, e.numero, e.ocupado, v.placa, v.tipo, r.hora_entrada
  FROM espacios e
  LEFT JOIN vehiculos v ON v.id = e.vehiculo_id
  LEFT JOIN registros r ON r.vehiculo_id = v.id AND r.hora_salida IS NULL
  ORDER BY e.numero
");
$espacios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ✅ Calcular ocupados y libres
$total = count($espacios);
$ocupados = count(array_filter($espacios, fn($e) => $e['ocupado']));
$libres = $total - $ocupados;
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Espacios - Operador</title>
  <link rel="stylesheet" href="styles_admin.css">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    .cards {
      display: flex;
      gap: 20px;
      margin: 20px 0;
    }
    .card {
      flex: 1;
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

    .grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
      gap: 15px;
      margin-top: 20px;
    }
    .espacio {
      border-radius: 10px;
      padding: 15px;
      text-align: center;
      font-weight: bold;
      color: white;
      cursor: pointer;
      transition: transform .2s;
    }
    .espacio:hover { transform: scale(1.05); }
    .libre { background: #16a34a; }
    .ocupado { background: #dc2626; }
    .espacio small { display:block; font-weight: normal; margin-top: 5px; }
    a { text-decoration: none; color: inherit; }

    .chart-container {
      width: 300px;
      margin: 20px auto;
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
        <li><a href="operador_dashboard.php">🏠 Panel Principal</a></li>
        <li><a href="espacios_operador.php" class="active">🅿️ Espacios</a></li>
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
        <h1>🅿️ Espacios del Parqueadero</h1>
        <p>Haz clic en un espacio para registrar entrada o salida</p>
      </header>

      <!-- Tarjetas resumen -->
      <section class="cards">
        <div class="card rojo">🟥 Ocupados<br><?= $ocupados ?></div>
        <div class="card verde">🟩 Libres<br><?= $libres ?></div>
        <div class="card azul">📦 Total<br><?= $total ?></div>
      </section>

      <!-- Gráfico -->
      <div class="chart-container">
        <canvas id="ocupacionChart"></canvas>
      </div>

      <!-- Grilla de espacios -->
      <div class="grid">
        <?php foreach($espacios as $e): ?>
          <?php if($e['ocupado']): ?>
            <!-- Ocupado ➡ salida -->
            <a href="registro_salida.php?espacio_id=<?= urlencode($e['id']) ?>">
              <div class="espacio ocupado">
                Espacio <?= htmlspecialchars($e['numero']) ?>
                <small>🚗 <?= htmlspecialchars($e['placa']) ?> (<?= htmlspecialchars($e['tipo']) ?>)</small>
                <small>📅 Entrada: <?= htmlspecialchars($e['hora_entrada']) ?></small>
              </div>
            </a>
          <?php else: ?>
            <!-- Libre ➡ entrada -->
            <a href="registro_entrada.php?espacio_id=<?= urlencode($e['id']) ?>">
              <div class="espacio libre">
                Espacio <?= htmlspecialchars($e['numero']) ?>
                <small>✅ Disponible</small>
              </div>
            </a>
          <?php endif; ?>
        <?php endforeach; ?>
      </div>
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
      options: {
        plugins: {
          legend: { position: 'bottom' }
        }
      }
    });
  </script>
</body>
</html>
