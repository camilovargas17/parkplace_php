<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $pass = md5($_POST['password']);

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email=? AND password=? LIMIT 1");
    $stmt->execute([$email, $pass]);
    $u = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($u) {
        $_SESSION['id'] = $u['id'];
        $_SESSION['nombre'] = $u['nombre'];
        $_SESSION['rol'] = $u['rol'];

        if ($u['rol'] == 'administrador') {
            header("Location: admin_dashboard.php");
        } else {
            header("Location: operador_dashboard.php");
        }
        exit;
    } else {
        $error = "❌ Credenciales incorrectas";
    }
}
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<title>ParkPlace - Login</title>
<link rel="stylesheet" href="styles.css">
<script src="https://kit.fontawesome.com/yourcode.js" crossorigin="anonymous"></script>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="logo">
                <i class="fas fa-car"></i>
            </div>
            <h2>Bienvenido a <span>ParkPlace</span></h2>
            <p class="subtitle">Sistema integral de gestión de parqueadero</p>

            <?php if(!empty($error)) echo "<p class='error'>$error</p>"; ?>

            <form method="POST">
                <div class="input-group">
                    <label>Usuario</label>
                    <div class="input-icon">
                        <i class="fas fa-user"></i>
                        <input type="email" name="email" placeholder="Ingrese su usuario" required>
                    </div>
                </div>

                <div class="input-group">
                    <label>Contraseña</label>
                    <div class="input-icon">
                        <i class="fas fa-lock"></i>
                        <input type="password" id="password" name="password" placeholder="Ingrese su contraseña" required>
                        <i class="fas fa-eye toggle-password" onclick="togglePassword()"></i>
                    </div>
                </div>

                <button type="submit" class="btn-submit">Iniciar Sesión</button>
            </form>
        </div>
    </div>

<script>
function togglePassword() {
    const input = document.getElementById("password");
    const icon = document.querySelector(".toggle-password");
    if (input.type === "password") {
        input.type = "text";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
    } else {
        input.type = "password";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
    }
}
</script>
</body>
</html>
