<?php
session_start();
require 'db.php';
if ($_SESSION['rol'] !== 'administrador') {
  header("Location:index.php");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $tipo = $_POST['tipo'];
  $valor = $_POST['valor'];
  $stmt = $pdo->prepare("UPDATE tarifas SET valor_hora=? WHERE tipo_vehiculo=?");
  $stmt->execute([$valor, $tipo]);
}

$tarifas = $pdo->query("SELECT * FROM tarifas")->fetchAll();
?>
<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <link rel="stylesheet" href="styles-tarifas.css">
</head>

<body>
  <h1>Tarifas</h1>
  <table>
    <tr>
      <th>Tipo Vehículo</th>
      <th>Valor por Hora</th>
      <th>Actualizar</th>
    </tr>
    <?php foreach ($tarifas as $t): ?>
      <tr>
        <form method="POST">
          <td><?php echo $t['tipo_vehiculo']; ?><input type="hidden" name="tipo" value="<?php echo $t['tipo_vehiculo']; ?>"></td>
          <td><input type="number" step="0.01" name="valor" value="<?php echo $t['valor_hora']; ?>"></td>
          <td><button type="submit">Guardar</button></td>
        </form>
      </tr>
    <?php endforeach; ?>
  </table>
</body>

</html>