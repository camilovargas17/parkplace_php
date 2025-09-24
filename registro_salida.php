<?php
session_start();
require 'db.php';

// Verificar rol
if ($_SESSION['rol'] !== 'operador' && $_SESSION['rol'] !== 'administrador') {
    header("Location:index.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $placa = $_POST['placa'];

    // Buscar vehículo
    $stmtVehiculo = $pdo->prepare("SELECT * FROM vehiculos WHERE placa = ? LIMIT 1");
    $stmtVehiculo->execute([$placa]);
    $vehiculo = $stmtVehiculo->fetch();

    if ($vehiculo) {
        // Cerrar registros viejos abiertos (excepto el más reciente)
        $pdo->query("
            UPDATE registros 
            SET hora_salida=NOW(), costo=0 
            WHERE vehiculo_id={$vehiculo['id']} 
              AND hora_salida IS NULL 
              AND id < (SELECT MAX(id) FROM registros WHERE vehiculo_id={$vehiculo['id']})
        ");

        // Buscar el registro activo más reciente
        $stmtRegistro = $pdo->prepare("
            SELECT * FROM registros 
            WHERE vehiculo_id = ? 
              AND hora_salida IS NULL 
            ORDER BY hora_entrada DESC 
            LIMIT 1
        ");
        $stmtRegistro->execute([$vehiculo['id']]);
        $registro = $stmtRegistro->fetch();

        if ($registro) {
            $entrada = new DateTime($registro['hora_entrada']);
            $salida = new DateTime();

            $diffSegundos = $salida->getTimestamp() - $entrada->getTimestamp();

            // Tiempo exacto
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

            $total = ($diffSegundos < 60) ? 0 : floor(($tarifa / 3600) * $diffSegundos);

            // Guardar salida
            $stmtUpdate = $pdo->prepare("UPDATE registros SET hora_salida=?, costo=? WHERE id=?");
            $stmtUpdate->execute([$salida->format("Y-m-d H:i:s"), $total, $registro['id']]);

            // 🔓 Liberar espacio
            if (!empty($registro['espacio_id'])) {
                $stmtEspacio = $pdo->prepare("UPDATE espacios SET ocupado=0, vehiculo_id=NULL WHERE id=?");
                $stmtEspacio->execute([$registro['espacio_id']]);
            }

            // Tiempo acumulado del vehículo
            $stmtAcum = $pdo->prepare("
                SELECT SUM(TIMESTAMPDIFF(SECOND, hora_entrada, hora_salida)) 
                FROM registros 
                WHERE vehiculo_id = ? AND hora_salida IS NOT NULL
            ");
            $stmtAcum->execute([$vehiculo['id']]);
            $acumuladoSegundos = $stmtAcum->fetchColumn();

            $acumuladoHoras = floor($acumuladoSegundos / 3600);
            $acumuladoMinutos = floor(($acumuladoSegundos % 3600) / 60);

            // Guardar comprobante en sesión
            $_SESSION['comprobante'] = [
                'placa' => $vehiculo['placa'],
                'tipo' => $vehiculo['tipo'],
                'entrada' => $registro['hora_entrada'],
                'salida' => $salida->format("Y-m-d H:i:s"),
                'tiempoExacto' => $tiempoExacto,
                'total' => $total,
                'acumulado' => $acumuladoHoras . " horas " . $acumuladoMinutos . " minutos"
            ];

            header("Location: comprobante.php");
            exit;

        } else {
            $msg = "⚠️ No se encontró registro de entrada.";
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
        /* Fondo futurista */
        body {
            margin: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #1f1c2c, #928dab);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Contenedor con glassmorphism */
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
            outline: none;
            transition: 0.3s;
            background: rgba(255,255,255,0.15);
            color: #fff;
        }

        input::placeholder {
            color: #ddd;
        }

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

        .error {
            background: rgba(255, 0, 72, 0.2);
            border: 1px solid #ff0048;
            color: #ff6a87;
            padding: 0.8rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            font-weight: 500;
            animation: slideDown 0.6s ease-out;
        }

        /* Animaciones */
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
    </div>
</body>
</html>
