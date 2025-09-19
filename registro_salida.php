<?php
session_start();
require 'db.php';
if ($_SESSION['rol']!=='operador') { header("Location:index.php"); exit; }

if ($_SERVER['REQUEST_METHOD']==='POST') {
  $placa=$_POST['placa'];
  $vehiculo=$pdo->query("SELECT * FROM vehiculos WHERE placa='$placa'")->fetch();
  if($vehiculo){
    $registro=$pdo->query("SELECT * FROM registros WHERE vehiculo_id={$vehiculo['id']} AND hora_salida IS NULL ORDER BY hora_entrada DESC LIMIT 1")->fetch();
    if($registro){
      $entrada=new DateTime($registro['hora_entrada']);
      $salida=new DateTime();
      $diff=$entrada->diff($salida);
      $horas=max(1,$diff->h + ($diff->days*24));

      $tarifa=$pdo->query("SELECT valor_hora FROM tarifas WHERE tipo_vehiculo='{$vehiculo['tipo']}' LIMIT 1")->fetchColumn();
      $total=$tarifa*$horas;

      $stmt=$pdo->prepare("UPDATE registros SET hora_salida=NOW(), total_pagar=? WHERE id=?");
      $stmt->execute([$total,$registro['id']]);

      $_SESSION['comprobante']=[
        'placa'=>$vehiculo['placa'],
        'tipo'=>$vehiculo['tipo'],
        'entrada'=>$registro['hora_entrada'],
        'salida'=>$salida->format("Y-m-d H:i:s"),
        'horas'=>$horas,
        'total'=>$total
      ];
      header("Location: comprobante.php"); exit;
    } else { $msg="No se encontró registro de entrada."; }
  } else { $msg="Vehículo no existe."; }
}
?>
<!doctype html>
<html lang="es">
<head><meta charset="utf-8"><link rel="stylesheet" href="styles.css"></head>
<body>
<h1>Registrar Salida</h1>
<?php if(!empty($msg)) echo "<p class='error'>$msg</p>"; ?>
<form method="POST">
  <input type="text" name="placa" placeholder="Placa" required>
  <button type="submit">Procesar Salida</button>
</form>
</body>
</html>
