<?php
session_start();
require 'db.php';
if ($_SESSION['rol'] !== 'operador' && $_SESSION['rol'] !== 'administrador') {
  header("Location:index.php");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $placa = $_POST['placa'];
  $tipo = $_POST['tipo'];

  $stmt = $pdo->prepare("INSERT INTO vehiculos (placa,tipo) VALUES (?,?) ON DUPLICATE KEY UPDATE tipo=VALUES(tipo)");
  $stmt->execute([$placa, $tipo]);

  $vehiculo_id = $pdo->lastInsertId();
  if ($vehiculo_id == 0) {
    $vehiculo_id = $pdo->query("SELECT id FROM vehiculos WHERE placa='$placa'")->fetchColumn();
  }

  $stmt = $pdo->prepare("INSERT INTO registros (vehiculo_id,usuario_id,hora_entrada) VALUES (?,?,NOW())");
  $stmt->execute([$vehiculo_id, $_SESSION['id']]);
  $msg = "Vehículo registrado con éxito.";
}
?>
<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <link rel="stylesheet" href="styles-registro.css">
</head>

<body>
  <h1>Registrar Entrada</h1>
  <?php if (!empty($msg)) echo "<p class='flash'>$msg</p>"; ?>
  <form method="POST">
    <input type="text" name="placa" placeholder="Placa" required>
    <select name="tipo" required>
      <option value="carro">Carro</option>
      <option value="moto">Moto</option>
    </select>
    <button type="submit">Registrar</button>
  </form>
</body>

</html>