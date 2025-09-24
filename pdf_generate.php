<?php
session_start();
require __DIR__ . '/vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

if (empty($_SESSION['comprobante'])) {
    header("Location: registrar_salida.php");
    exit;
}

$c = $_SESSION['comprobante'];

$html = "
<style>
    body {
        font-family: DejaVu Sans, sans-serif;
        background: #f5f7fa;
        margin: 0;
        padding: 20px;
        color: #333;
    }
    .ticket {
        max-width: 600px;
        margin: auto;
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 6px 20px rgba(0,0,0,0.15);
        padding: 30px;
    }
    .header {
        text-align: center;
        border-bottom: 2px solid #ff6a00;
        padding-bottom: 15px;
        margin-bottom: 20px;
    }
    .header h1 {
        margin: 0;
        font-size: 24px;
        color: #ff6a00;
        letter-spacing: 1px;
    }
    .header p {
        margin: 5px 0 0;
        font-size: 14px;
        color: #666;
    }
    .info {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
    }
    .info th, .info td {
        text-align: left;
        padding: 10px;
        font-size: 14px;
    }
    .info th {
        background: #ff6a00;
        color: #fff;
        border-radius: 8px 8px 0 0;
    }
    .info tr:nth-child(even) {
        background: #f9f9f9;
    }
    .total {
        text-align: right;
        font-size: 18px;
        font-weight: bold;
        color: #ee0979;
        margin-top: 10px;
    }
    .footer {
        text-align: center;
        font-size: 12px;
        color: #777;
        border-top: 1px dashed #aaa;
        padding-top: 10px;
        margin-top: 20px;
    }
</style>

<div class='ticket'>
    <div class='header'>
        <h1>ParkPlace</h1>
        <p>Comprobante de Estacionamiento</p>
    </div>

    <table class='info'>
        <tr>
            <th>Placa</th>
            <td>{$c['placa']}</td>
        </tr>
        <tr>
            <th>Tipo</th>
            <td>{$c['tipo']}</td>
        </tr>
        <tr>
            <th>Entrada</th>
            <td>{$c['entrada']}</td>
        </tr>
        <tr>
            <th>Salida</th>
            <td>{$c['salida']}</td>
        </tr>
        <tr>
            <th>Tiempo</th>
            <td>{$c['tiempoExacto']}</td>
        </tr>
    </table>

    <p class='total'>Total a Pagar: \$ {$c['total']}</p>

    <div class='footer'>
        Gracias por elegir ParkPlace 🚗<br>
        ¡Vuelva pronto!
    </div>
</div>
";

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

// Asegurar codificación correcta
$dompdf->loadHtml(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Evitar salida previa
ob_end_clean();
$dompdf->stream("comprobante.pdf", ["Attachment" => true]);
