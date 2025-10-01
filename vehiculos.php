<?php
session_start();
require 'db.php';

// Verificar rol
if ($_SESSION['rol'] !== 'administrador') {
  header("Location:index.php");
  exit;
}

// Dashboard destino según rol
$dashboard = ($_SESSION['rol'] === 'administrador') ? 'admin_dashboard.php' : 'operador_dashboard.php';

// Filtro por placa (si existe)
$placaFiltro = isset($_GET['placa']) ? trim($_GET['placa']) : '';

if ($placaFiltro !== '') {
  $stmt = $pdo->prepare("SELECT * FROM vehiculos WHERE placa LIKE :placa ORDER BY created_at DESC");
  $stmt->execute(['placa' => "%$placaFiltro%"]);
  $vehiculos = $stmt->fetchAll();
} else {
  $vehiculos = $pdo->query("SELECT * FROM vehiculos ORDER BY created_at DESC")->fetchAll();
}
?>
<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <title>Vehículos registrados</title>
  <link rel="stylesheet" href="styles-vehiculos.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
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
      margin-bottom: 20px;
    }

    input[type="text"] {
      padding: 8px;
      width: 250px;
      border: 1px solid #ccc;
      border-radius: 4px;
    }

    button[type="submit"] {
      padding: 8px 12px;
      background-color: #4da3ff;
      color: white;
      border: none;
      border-radius: 4px;
      cursor: pointer;
      margin-left: 5px;
    }

    button[type="submit"]:hover {
      background-color: #3c91e6;
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

    .acciones {
      text-align: center;
      white-space: nowrap;
    }

    .acciones a {
      margin: 0 6px;
      text-decoration: none;
      font-size: 1.2rem;
      transition: 0.3s;
    }

    .acciones .edit {
      color: #ffa502;
    }

    .acciones .edit:hover {
      color: #e58e26;
    }

    .acciones .delete {
      color: #ff4757;
    }

    .acciones .delete:hover {
      color: #d63031;
    }

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
  <h1>Vehículos registrados</h1>

  <!-- Formulario de búsqueda -->
  <form method="GET">
    <input type="text" name="placa" placeholder="Buscar por placa" value="<?php echo htmlspecialchars($placaFiltro); ?>">
    <button type="submit">Buscar</button>
  </form>

  <table>
    <tr>
      <th>Placa</th>
      <th>Tipo</th>
      <th>Fecha Registro</th>
      <th>Acciones</th>
    </tr>

    <?php if (empty($vehiculos)): ?>
      <tr>
        <td colspan="4" style="text-align: center;">No se encontraron vehículos con esa placa.</td>
      </tr>
    <?php else: ?>
      <?php foreach ($vehiculos as $v): ?>
        <tr>
          <td><?php echo htmlspecialchars($v['placa']); ?></td>
          <td><?php echo htmlspecialchars($v['tipo']); ?></td>
          <td><?php echo htmlspecialchars($v['created_at']); ?></td>
          <td class="acciones">
            <a href="editar_vehiculo.php?id=<?php echo $v['id']; ?>" class="edit" title="Editar">
              <i class="fas fa-pen"></i>
            </a>
            <a href="eliminar_vehiculo.php?id=<?php echo $v['id']; ?>" class="delete" title="Eliminar"
               onclick="return confirm('¿Seguro que deseas eliminar este vehículo?');">
              <i class="fas fa-trash"></i>
            </a>
          </td>
        </tr>
      <?php endforeach; ?>
    <?php endif; ?>
  </table>

  <a href="<?php echo $dashboard; ?>" class="volver">⬅️ Volver al Dashboard</a>
</body>
</html>
