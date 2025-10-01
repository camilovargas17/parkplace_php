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

// ================== VALIDACIÓN DE CONTRASEÑA ==================
function validarPassword($password) {
  // Al menos 6 caracteres, una letra y un número
  if (strlen($password) < 6) return false;
  if (!preg_match('/[A-Za-z]/', $password)) return false;
  if (!preg_match('/[0-9]/', $password)) return false;
  return true;
}

// ================== AGREGAR USUARIO ==================
if (isset($_POST['accion']) && $_POST['accion'] === 'agregar') {
  $nombre = $_POST['nombre'];
  $email = $_POST['email'];
  $password = $_POST['password'];
  $rol = $_POST['rol'];

  if (!validarPassword($password)) {
    $error = "La contraseña debe tener mínimo 6 caracteres, incluir letras y números.";
  } else {
    $password = md5($password);
    $stmt = $pdo->prepare("INSERT INTO usuarios (nombre,email,password,rol) VALUES (?,?,?,?)");
    $stmt->execute([$nombre, $email, $password, $rol]);
  }
}

// ================== EDITAR USUARIO ==================
if (isset($_POST['accion']) && $_POST['accion'] === 'editar') {
  $id = $_POST['id'];
  $nombre = $_POST['nombre'];
  $email = $_POST['email'];
  $rol = $_POST['rol'];
  $password = $_POST['password'];

  if (!empty($password)) {
    if (!validarPassword($password)) {
      $error = "La contraseña debe tener mínimo 6 caracteres, incluir letras y números.";
    } else {
      $password = md5($password);
      $stmt = $pdo->prepare("UPDATE usuarios SET nombre=?, email=?, password=?, rol=? WHERE id=?");
      $stmt->execute([$nombre, $email, $password, $rol, $id]);
    }
  } else {
    $stmt = $pdo->prepare("UPDATE usuarios SET nombre=?, email=?, rol=? WHERE id=?");
    $stmt->execute([$nombre, $email, $rol, $id]);
  }
}

// ================== ELIMINAR USUARIO ==================
if (isset($_GET['eliminar'])) {
  $id = $_GET['eliminar'];
  $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id=?");
  $stmt->execute([$id]);
  header("Location: usuarios.php");
  exit;
}

// ================== OBTENER TODOS LOS USUARIOS ==================
$usuarios = $pdo->query("SELECT * FROM usuarios")->fetchAll();
$editando = null;

if (isset($_GET['editar'])) {
  $id = $_GET['editar'];
  $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id=?");
  $stmt->execute([$id]);
  $editando = $stmt->fetch();
}
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
    h1 { margin-bottom: 1.5rem; }
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
    form input, form select {
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
    tr:hover { background: #f0f8ff; }
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
    .acciones a {
      margin-right: 10px;
      text-decoration: none;
      font-weight: bold;
      color: #4da3ff;
    }
    .error { color: red; margin-bottom: 1rem; }
  </style>
</head>

<body>
  <h1>Gestión de Usuarios</h1>

  <?php if (isset($error)): ?>
    <div class="error"><?php echo $error; ?></div>
  <?php endif; ?>

  <!-- Formulario agregar / editar -->
  <form method="POST">
    <input type="hidden" name="accion" value="<?php echo $editando ? 'editar' : 'agregar'; ?>">
    <?php if ($editando): ?>
      <input type="hidden" name="id" value="<?php echo $editando['id']; ?>">
    <?php endif; ?>
    
    <input type="text" name="nombre" placeholder="Nombre" 
           value="<?php echo $editando['nombre'] ?? ''; ?>" required>
    
    <input type="email" name="email" placeholder="Email" 
           value="<?php echo $editando['email'] ?? ''; ?>" required>
    
    <!-- 🔹 Contraseña obligatoria al agregar, opcional al editar -->
    <input type="password" name="password" 
           placeholder="<?php echo $editando ? 'Nueva contraseña (opcional)' : 'Contraseña'; ?>"
           <?php echo $editando ? '' : 'required'; ?>>
    
    <select name="rol">
      <option value="administrador" <?php echo ($editando && $editando['rol'] === 'administrador') ? 'selected' : ''; ?>>Administrador</option>
      <option value="operador" <?php echo ($editando && $editando['rol'] === 'operador') ? 'selected' : ''; ?>>Operador</option>
    </select>
    
    <button type="submit"><?php echo $editando ? 'Actualizar' : 'Agregar'; ?></button>
  </form>

  <!-- Tabla usuarios -->
  <table>
    <tr>
      <th>Nombre</th>
      <th>Email</th>
      <th>Rol</th>
      <th>Acciones</th>
    </tr>
    <?php foreach ($usuarios as $u): ?>
      <tr>
        <td><?php echo $u['nombre']; ?></td>
        <td><?php echo $u['email']; ?></td>
        <td><?php echo $u['rol']; ?></td>
        <td class="acciones">
          <a href="?editar=<?php echo $u['id']; ?>">✏️ Editar</a>
          <a href="?eliminar=<?php echo $u['id']; ?>" onclick="return confirm('¿Seguro que deseas eliminar este usuario?');">🗑️ Eliminar</a>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>

  <!-- Botón volver -->
  <a href="<?php echo $dashboard; ?>" class="volver">⬅️ Volver al Dashboard</a>
</body>
</html>
