<?php
date_default_timezone_set('America/Bogota');
session_start();
require 'db.php';

// Verificar rol
if ($_SESSION['rol'] !== 'operador' && $_SESSION['rol'] !== 'administrador') {
    header("Location:index.php");
    exit;
}

// Detectar dashboard correcto según rol
$dashboard = ($_SESSION['rol'] === 'administrador') ? 'admin_dashboard.php' : 'operador_dashboard.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $placa = $_POST['placa'];

    // Buscar vehículo
    $stmtVehiculo = $pdo->prepare("SELECT * FROM vehiculos WHERE placa = ? LIMIT 1");
    $stmtVehiculo->execute([$placa]);
    $vehiculo = $stmtVehiculo->fetch();

    if ($vehiculo) {
        // 👉 Buscar ÚNICAMENTE el registro activo (no se tocan registros viejos)
        $stmtRegistro = $pdo->prepare("
            SELECT * FROM registros 
            WHERE vehiculo_id = ? 
              AND hora_salida IS NULL 
            LIMIT 1
        ");
        $stmtRegistro->execute([$vehiculo['id']]);
        $registro = $stmtRegistro->fetch();

        if ($registro) {
            $entrada = new DateTime($registro['hora_entrada']);
            $salida = new DateTime();

            $diffSegundos = $salida->getTimestamp() - $entrada->getTimestamp();

            // Tiempo exacto de estadía
            if ($diffSegundos < 3600) {
                $minutos = floor($diffSegundos / 60);
                $segundos = $diffSegundos % 60;
                $tiempoExacto = "{$minutos} minutos {$segundos} segundos";
            } else {
                $horas = floor($diffSegundos / 3600);
                $minutos = floor(($diffSegundos % 3600) / 60);
                $segundos = $diffSegundos % 60;
                $tiempoExacto = "{$horas} horas {$minutos} minutos {$segundos} segundos";
            }

            // Tarifa por tipo de vehículo
            $stmtTarifa = $pdo->prepare("SELECT valor_hora FROM tarifas WHERE tipo_vehiculo = ? LIMIT 1");
            $stmtTarifa->execute([$vehiculo['tipo']]);
            $tarifa = $stmtTarifa->fetchColumn();

            // Calcular total proporcional por segundos
            $total = ($diffSegundos < 60) ? 0 : floor(($tarifa / 3600) * $diffSegundos);

            // ✅ Guardar salida en el registro activo
            $stmtUpdate = $pdo->prepare("UPDATE registros SET hora_salida=?, costo=? WHERE id=?");
            $stmtUpdate->execute([$salida->format("Y-m-d H:i:s"), $total, $registro['id']]);

            // 🔓 Liberar espacio ocupado
            if (!empty($registro['espacio_id'])) {
                $stmtEspacio = $pdo->prepare("UPDATE espacios SET ocupado=0, vehiculo_id=NULL WHERE id=?");
                $stmtEspacio->execute([$registro['espacio_id']]);
            }

            // Guardar comprobante en sesión
            $_SESSION['comprobante'] = [
                'placa' => $vehiculo['placa'],
                'tipo' => $vehiculo['tipo'],
                'entrada' => $registro['hora_entrada'],
                'salida' => $salida->format("Y-m-d H:i:s"),
                'tiempoExacto' => $tiempoExacto,
                'total' => $total
            ];

            header("Location: comprobante.php");
            exit;

        } else {
            $msg = "⚠️ No se encontró un registro de entrada activo para este vehículo.";
        }

    } else {
        $msg = "❌ Vehículo no existe.";
    }
}
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Registrar Salida</title>
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
            border-radius: 20px;
            padding: 2.5rem;
            max-width: 420px;
            width: 100%;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            text-align: center;
            color: #fff;
            animation: fadeIn 1s ease-out;
        }
        h1 {
            font-size: 1.8rem;
            margin-bottom: 1.5rem;
            background: linear-gradient(90deg, #ff6a00, #ee0979);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        form { 
            display: flex; 
            flex-direction: column; 
            gap: 1.2rem; 
        }
        input {
            padding: 0.9rem;
            border-radius: 10px;
            border: none;
            font-size: 1rem;
            background: rgba(255,255,255,0.15);
            color: #fff;
            outline: none;
            transition: 0.3s;
        }
        input::placeholder { color: #ddd; }
        input:focus {
            background: rgba(255,255,255,0.25);
            box-shadow: 0 0 8px #ff6a00;
        }
        button {
            padding: 1rem;
            border: none;
            border-radius: 12px;
            background: linear-gradient(135deg, #ff6a00, #ee0979);
            color: #fff;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        button:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(238, 9, 121, 0.6);
        }
        .volver {
            margin-top: 1rem;
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
        .error {
            background: rgba(255, 0, 72, 0.2);
            border: 1px solid #ff0048;
            color: #ff6a87;
            padding: 0.8rem;
            border-radius: 10px;
            margin-bottom: 1rem;
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
        <h1>🚪 Registrar Salida</h1>
        <?php if (!empty($msg)) echo "<p class='error'>$msg</p>"; ?>
        <form method="POST">
            <input type="text" name="placa" placeholder="Ingresa la placa" required>
            <button type="submit">Procesar Salida</button>
        </form>
        <a href="<?php echo $dashboard; ?>" class="volver">⬅️ Volver al Dashboard</a>
    </div>
</body>
</html>
