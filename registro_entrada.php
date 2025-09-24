<?php
session_start();
require 'db.php';

if ($_SESSION['rol'] !== 'operador' && $_SESSION['rol'] !== 'administrador') {
  header("Location:index.php");
  exit;
}

$msg = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $placa = trim($_POST['placa']);
  $tipo  = $_POST['tipo'];

  // Insertar o actualizar vehículo
  $stmt = $pdo->prepare("INSERT INTO vehiculos (placa,tipo) VALUES (?,?) 
                         ON DUPLICATE KEY UPDATE tipo=VALUES(tipo)");
  $stmt->execute([$placa, $tipo]);

  $vehiculo_id = $pdo->lastInsertId();
  if ($vehiculo_id == 0) {
    $vehiculo_id = $pdo->query("SELECT id FROM vehiculos WHERE placa = " . $pdo->quote($placa))->fetchColumn();
  }

  // 🚨 VALIDAR SI EL VEHÍCULO YA ESTÁ DENTRO
  $stmt = $pdo->prepare("SELECT COUNT(*) FROM registros 
                         WHERE vehiculo_id = ? AND hora_salida IS NULL");
  $stmt->execute([$vehiculo_id]);
  $yaDentro = $stmt->fetchColumn();

  if ($yaDentro > 0) {
    $msg = "⚠️ El vehículo con placa $placa ya se encuentra dentro del estacionamiento.";
  } else {
    // Buscar un espacio libre
    $stmt = $pdo->query("SELECT id FROM espacios WHERE ocupado = 0 LIMIT 1");
    $espacio_id = $stmt->fetchColumn();

    if ($espacio_id) {
      // Asignar el espacio al vehículo
      $stmt = $pdo->prepare("UPDATE espacios SET ocupado=1, vehiculo_id=? WHERE id=?");
      $stmt->execute([$vehiculo_id, $espacio_id]);

      // Registrar entrada
      $stmt = $pdo->prepare("INSERT INTO registros (vehiculo_id, usuario_id, hora_entrada, espacio_id) 
                             VALUES (?, ?, NOW(), ?)");
      $stmt->execute([$vehiculo_id, $_SESSION['id'], $espacio_id]);

      $msg = "✅ Vehículo registrado en el espacio #$espacio_id.";
    } else {
      $msg = "⚠️ No hay espacios disponibles.";
    }
  }
}

?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Registrar Entrada</title>
  <style>
    /* Fondo futurista */
    body {
      margin: 0;
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      background: linear-gradient(135deg, #1f1c2c, #928dab);
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    /* Contenedor con glassmorphism */
    .card {
      background: rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
      border-radius: 20px;
      padding: 2.5rem;
      width: 100%;
      max-width: 420px;
      box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
      text-align: center;
      color: #fff;
      animation: fadeIn 1s ease-out;
    }

    h1 {
      font-size: 1.8rem;
      margin-bottom: 1.5rem;
      background: linear-gradient(90deg, #4facfe, #00f2fe);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
    }

    form {
      display: flex;
      flex-direction: column;
      gap: 1.2rem;
    }

    input, select {
      padding: 0.9rem;
      border-radius: 10px;
      border: none;
      font-size: 1rem;
      outline: none;
      transition: 0.3s;
      background: rgba(255,255,255,0.15);
      color: #fff;
    }

    input::placeholder {
      color: #ddd;
    }

    input:focus, select:focus {
      background: rgba(255,255,255,0.25);
      box-shadow: 0 0 8px #00f2fe;
    }

    button {
      padding: 1rem;
      border: none;
      border-radius: 12px;
      background: linear-gradient(135deg, #4facfe, #00f2fe);
      color: #fff;
      font-size: 1.1rem;
      font-weight: bold;
      cursor: pointer;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    button:hover {
      transform: translateY(-3px);
      box-shadow: 0 6px 20px rgba(0, 242, 254, 0.6);
    }

    /* Botón secundario */
    .btn-back {
      margin-top: 1rem;
      display: inline-block;
      padding: 0.9rem;
      border-radius: 12px;
      text-decoration: none;
      background: rgba(255,255,255,0.2);
      color: #fff;
      font-weight: 500;
      transition: 0.3s;
    }

    .btn-back:hover {
      background: rgba(255,255,255,0.35);
      box-shadow: 0 6px 15px rgba(255,255,255,0.3);
    }

    .flash {
      background: rgba(0, 255, 163, 0.2);
      border: 1px solid #00f2fe;
      color: #00f2fe;
      padding: 0.8rem;
      border-radius: 10px;
      margin-bottom: 1rem;
      font-weight: 500;
      animation: slideDown 0.6s ease-out;
    }

    /* Animaciones */
    @keyframes fadeIn {
      from { opacity: 0; transform: translateY(20px); }
      to { opacity: 1; transform: translateY(0); }
    }

    @keyframes slideDown {
      from { opacity: 0; transform: translateY(-10px); }
      to { opacity: 1; transform: translateY(0); }
    }
  </style>
</head>
<body>
  <div class="card">
    <h1>🚗 Registrar Entrada</h1>
    <?php if (!empty($msg)) echo "<p class='flash'>$msg</p>"; ?>
    <form method="POST">
      <input type="text" name="placa" placeholder="Ingresa la placa" required>
      <select name="tipo" required>
        <option value="carro">Carro</option>
        <option value="moto">Moto</option>
      </select>
      <button type="submit">Registrar</button>
    </form>

    <!-- Botón Volver -->
    <a href="<?php echo ($_SESSION['rol'] == 'administrador') ? 'admin_dashboard.php' : 'operador_dashboard.php'; ?>" class="btn-back">
      ⬅️ Volver al Dashboard
    </a>
  </div>
</body>
</html>
