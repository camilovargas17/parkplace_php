<?php
session_start();
require 'db.php';

// Solo administrador puede entrar
if ($_SESSION['rol'] !== 'administrador') {
  header("Location:index.php");
  exit;
}

// Dashboard destino según rol
$dashboard = ($_SESSION['rol'] === 'administrador') ? 'admin_dashboard.php' : 'operador_dashboard.php';

// Agregar usuario nuevo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nombre = $_POST['nombre'];
  $email = $_POST['email'];
  $password = md5($_POST['password']); // ⚠️ Simple, luego podemos cambiar a password_hash()
  $rol = $_POST['rol'];

  $stmt = $pdo->prepare("INSERT INTO usuarios (nombre,email,password,rol) VALUES (?,?,?,?)");
  $stmt->execute([$nombre, $email, $password, $rol]);
}

// Obtener todos los usuarios
$usuarios = $pdo->query("SELECT * FROM usuarios")->fetchAll();
?>
<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <title>Gestión de Usuarios</title>
  <link rel="stylesheet" href="styles-usuario.css">
  <style>
    body {
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #f5f7fa;
      margin: 0;
      padding: 2rem;
      display: flex;
      flex-direction: column;
      align-items: center;
    }

    h1 {
      margin-bottom: 1.5rem;
    }

    form {
      background: white;
      padding: 1.5rem;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
      margin-bottom: 2rem;
      display: flex;
      flex-wrap: wrap;
      gap: 1rem;
      justify-content: center;
    }

    form input,
    form select {
      padding: 0.6rem;
      border: 1px solid #ccc;
      border-radius: 8px;
      outline: none;
      flex: 1 1 200px;
    }

    form button {
      padding: 0.7rem 1.2rem;
      border: none;
      border-radius: 8px;
      background: linear-gradient(135deg, #36d1dc, #5b86e5);
      color: white;
      font-weight: bold;
      cursor: pointer;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    form button:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(91, 134, 229, 0.6);
    }

    table {
      border-collapse: collapse;
      width: 80%;
      max-width: 900px;
      background: white;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    }

    th {
      background: #4da3ff;
      color: white;
      padding: 12px;
      text-align: left;
    }

    td {
      padding: 12px;
      border-bottom: 1px solid #eee;
    }

    tr:hover {
      background: #f0f8ff;
    }

    /* Botón volver */
    .volver {
      margin-top: 1.5rem;
      display: inline-block;
      padding: 0.8rem 1.2rem;
      border-radius: 10px;
      background: linear-gradient(135deg, #36d1dc, #5b86e5);
      color: #fff;
      text-decoration: none;
      font-weight: bold;
      transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .volver:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(91, 134, 229, 0.6);
    }
  </style>
</head>

<body>
  <h1>Gestión de Usuarios</h1>

  <!-- Formulario agregar -->
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

  <!-- Tabla usuarios -->
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

  <!-- Botón volver -->
  <a href="<?php echo $dashboard; ?>" class="volver">⬅️ Volver al Dashboard</a>
</body>
</html>
