<?php
session_start();
require 'db.php';

// 🔒 Verificar acceso
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
  header("Location:index.php");
  exit;
}

// 📊 Consulta de los espacios
$stmt = $pdo->query("
  SELECT e.numero, e.ocupado, v.placa, v.tipo, r.hora_entrada
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
  <title>Espacios Ocupados - ParkPlace</title>
  <link rel="stylesheet" href="styles_admin.css">
  <style>
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
    }
    .libre { background: #16a34a; }   /* Verde */
    .ocupado { background: #dc2626; } /* Rojo */
    .espacio small { display:block; font-weight: normal; margin-top: 5px; }
    .contador { margin-top: 10px; font-size: 18px; font-weight: bold; }
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
        <li><a href="registro_entrada.php">⬅️ Registrar Entrada</a></li>
        <li><a href="registro_salida.php">➡️ Registrar Salida</a></li>
        <li><a href="vehiculos.php">🚗 Vehículos</a></li>
        <li><a href="tarifas.php">💲 Tarifas</a></li>
        <li><a href="reportes.php">📊 Reportes</a></li>
        <li><a href="espacios.php" class="active">🅿️ Espacios</a></li>
      </ul>
      <div class="sidebar-footer">
        <p><b>Rol actual:</b> Administrador</p>
        <a href="logout.php" class="logout">🚪 Cerrar sesión</a>
      </div>
    </aside>

    <!-- Contenido -->
    <main class="main-content">
      <header>
        <h1>🅿️ Espacios del Parqueadero</h1>
        <p>Visualiza los cupos ocupados y libres</p>
        <div class="contador">
          🟥 Ocupados: <?= $ocupados ?> &nbsp;&nbsp; 🟩 Libres: <?= $libres ?> &nbsp;&nbsp; 📦 Total: <?= $total ?>
        </div>
      </header>

      <div class="grid">
        <?php foreach($espacios as $e): ?>
          <div class="espacio <?= $e['ocupado'] ? 'ocupado' : 'libre' ?>">
            Espacio <?= $e['numero'] ?>
            <?php if($e['ocupado']): ?>
              <small>🚗 <?= htmlspecialchars($e['placa']) ?> (<?= $e['tipo'] ?>)</small>
              <small>📅 Entrada: <?= $e['hora_entrada'] ?></small>
            <?php else: ?>
              <small>✅ Disponible</small>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    </main>
  </div>
</body>
</html>
