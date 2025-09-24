<?php
session_start();
require 'db.php';
if ($_SESSION['rol'] !== 'administrador') {
  header("Location:index.php");
  exit;
}

$vehiculos = $pdo->query("SELECT * FROM vehiculos ORDER BY created_at DESC")->fetchAll();
?>
<!doctype html>
<html lang="es">

<head>
  <meta charset="utf-8">
  <title>Vehículos registrados</title>
  <link rel="stylesheet" href="styles-vehiculos.css">
  <!-- FontAwesome para iconos -->
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

    /* Columna de acciones */
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
  </style>
</head>

<body>
  <h1>Vehículos registrados</h1>
  <table>
    <tr>
      <th>Placa</th>
      <th>Tipo</th>
      <th>Fecha Registro</th>
      <th>Acciones</th>
    </tr>
    <?php foreach ($vehiculos as $v): ?>
      <tr>
        <td><?php echo $v['placa']; ?></td>
        <td><?php echo $v['tipo']; ?></td>
        <td><?php echo $v['created_at']; ?></td>
        <td class="acciones">
          <!-- Editar -->
          <a href="editar_vehiculo.php?id=<?php echo $v['id']; ?>" class="edit" title="Editar">
            <i class="fas fa-pen"></i>
          </a>
          <!-- Eliminar -->
          <a href="eliminar_vehiculo.php?id=<?php echo $v['id']; ?>" class="delete" title="Eliminar"
             onclick="return confirm('¿Seguro que deseas eliminar este vehículo?');">
            <i class="fas fa-trash"></i>
          </a>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
</body>

</html>
