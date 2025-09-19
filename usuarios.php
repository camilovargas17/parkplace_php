<?php
session_start();
require 'db.php';
if ($_SESSION['rol'] !== 'administrador') {
  header("Location:index.php");
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nombre = $_POST['nombre'];
  $email = $_POST['email'];
  $password = md5($_POST['password']);
  $rol = $_POST['rol'];
  $stmt = $pdo->prepare("INSERT INTO usuarios (nombre,email,password,rol) VALUES (?,?,?,?)");
  $stmt->execute([$nombre, $email, $password, $rol]);
}

$usuarios = $pdo->query("SELECT * FROM usuarios")->fetchAll();
?>
<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <link rel="stylesheet" href="styles-usuario.css">
</head>

<body>
  <h1>Usuarios</h1>
  <form method="POST">
    <input type="text" name="nombre" placeholder="Nombre" required>
    <input type="email" name="email" placeholder="Email" required>
    <input type="password" name="password" placeholder="Contraseña" required>
    <select name="rol">
      <option value="administrador">Administrador</option>
      <option value="operador">Operador</option>
    </select>
    <button type="submit">Agregar</button>
  </form>
  <table>
    <tr>
      <th>Nombre</th>
      <th>Email</th>
      <th>Rol</th>
    </tr>
    <?php foreach ($usuarios as $u): ?>
      <tr>
        <td><?php echo $u['nombre']; ?></td>
        <td><?php echo $u['email']; ?></td>
        <td><?php echo $u['rol']; ?></td>
      </tr>
    <?php endforeach; ?>
  </table>
</body>

</html>