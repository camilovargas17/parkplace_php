<?php
$host = "127.0.0.1";   
$dbname = "parkplace"; 
$user = "root";        
$pass = "";            
$port = 3307;          

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("❌ Error de conexión a la BD: " . $e->getMessage());
}
?>
