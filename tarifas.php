<?php
session_start();
require 'db.php';

// 🔒 Verificar acceso
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') {
    header("Location:index.php");
    exit;
}

// 💾 Actualizar tarifa
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo = $_POST['tipo'];
    $valor = $_POST['valor'];

    if (is_numeric($valor) && $valor >= 0) {
        $stmt = $pdo->prepare("UPDATE tarifas SET valor_hora=?, updated_at=NOW() WHERE tipo_vehiculo=?");
        $stmt->execute([$valor, $tipo]);
    }
}

// 📊 Obtener tarifas
$tarifas = $pdo->query("SELECT * FROM tarifas ORDER BY tipo_vehiculo")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Tarifas - ParkPlace</title>
  <link rel="stylesheet" href="styles-tarifas.css">
  <style>
    table { border-collapse: collapse; width: 60%; margin: 20px auto; }
    th, td { border: 1px solid #ddd; padding: 10px; text-align: center; }
    th { background: #1e3a8a; color: white; }
    input[type=number] { width: 100px; padding: 5px; text-align: right; }
    button { padding: 6px 12px; background: #16a34a; border: none; color: white; cursor: pointer; border-radius: 5px; }
    button:hover { background: #15803d; }
    h1 { text-align: center; color: #1e3a8a; }
  </style>
</head>
<body>
  <h1>💲 Tarifas</h1>
  <table>
    <tr>
      <th>Tipo Vehículo</th>
      <th>Valor por Hora</th>
      <th>Actualizar</th>
    </tr>
    <?php foreach ($tarifas as $t): ?>
      <tr>
        <form method="POST">
          <td>
            <?= htmlspecialchars($t['tipo_vehiculo']); ?>
            <input type="hidden" name="tipo" value="<?= htmlspecialchars($t['tipo_vehiculo']); ?>">
          </td>
          <td>
            <input type="number" step="100" name="valor" value="<?= htmlspecialchars($t['valor_hora']); ?>">
          </td>
          <td>
            <button type="submit">Guardar</button>
          </td>
        </form>
      </tr>
    <?php endforeach; ?>
  </table>
</body>
</html>
