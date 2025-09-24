<?php
session_start();
require 'db.php';

// Verificar rol
if ($_SESSION['rol'] !== 'administrador') {
    header("Location:index.php");
    exit;
}

// Verificar si viene el ID
if (!isset($_GET['id'])) {
    header("Location: vehiculos.php");
    exit;
}

$id = (int) $_GET['id'];
$msg = "";

// Buscar el vehículo
$stmt = $pdo->prepare("SELECT * FROM vehiculos WHERE id = ?");
$stmt->execute([$id]);
$vehiculo = $stmt->fetch();

if (!$vehiculo) {
    header("Location: vehiculos.php");
    exit;
}

// Si envían el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $placa = trim($_POST['placa']);
    $tipo = $_POST['tipo'];

    $stmt = $pdo->prepare("UPDATE vehiculos SET placa=?, tipo=? WHERE id=?");
    $stmt->execute([$placa, $tipo, $id]);

    $_SESSION['flash'] = "✅ Vehículo actualizado correctamente.";
    header("Location: vehiculos.php");
    exit;
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Editar Vehículo</title>
    <style>
        body {
            margin: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #1f1c2c, #928dab);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

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
            background: linear-gradient(90deg, #36d1dc, #5b86e5);
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
            box-shadow: 0 0 8px #36d1dc;
        }

        option {
            color: #000;
        }

        button {
            padding: 1rem;
            border: none;
            border-radius: 12px;
            background: linear-gradient(135deg, #36d1dc, #5b86e5);
            color: #fff;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        button:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(91, 134, 229, 0.6);
        }

        .flash {
            background: rgba(0, 255, 72, 0.2);
            border: 1px solid #00ff48;
            color: #8aff8a;
            padding: 0.8rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            font-weight: 500;
            animation: slideDown 0.6s ease-out;
        }

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
        <h1>✏️ Editar Vehículo</h1>
        <?php if (!empty($msg)) echo "<p class='flash'>$msg</p>"; ?>
        <form method="POST">
            <input type="text" name="placa" value="<?php echo htmlspecialchars($vehiculo['placa']); ?>" required>
            <select name="tipo" required>
                <option value="carro" <?php if ($vehiculo['tipo']=="carro") echo "selected"; ?>>Carro</option>
                <option value="moto" <?php if ($vehiculo['tipo']=="moto") echo "selected"; ?>>Moto</option>
            </select>
            <button type="submit">Actualizar</button>
        </form>
    </div>
</body>
</html>
