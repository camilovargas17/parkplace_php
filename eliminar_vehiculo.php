<?php
session_start();
require 'db.php';

// Verificar rol
if ($_SESSION['rol'] !== 'administrador') {
    header("Location:index.php");
    exit;
}

// Verificar si se recibió el ID
if (!isset($_GET['id'])) {
    header("Location: vehiculos.php");
    exit;
}

$id = (int) $_GET['id'];

// Solo ejecutar si se confirma con POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("DELETE FROM vehiculos WHERE id = ?");
    $stmt->execute([$id]);

    $_SESSION['flash'] = "✅ Vehículo eliminado correctamente.";
    header("Location: vehiculos.php");
    exit;
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Eliminar Vehículo</title>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<script>
Swal.fire({
    title: '¿Estás seguro?',
    text: "Esta acción no se puede deshacer",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Sí, eliminar',
    cancelButtonText: 'Cancelar'
}).then((result) => {
    if (result.isConfirmed) {
        // Crear un form oculto y enviarlo
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '';
        document.body.appendChild(form);
        form.submit();
    } else {
        // Si cancela, volver a la lista
        window.location.href = "vehiculos.php";
    }
});
</script>
</body>
</html>
