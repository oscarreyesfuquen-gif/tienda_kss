<?php
include("conexion.php");
session_start(); 

if (isset($_SESSION['nombre_usuario'])) {
    header("Location: inicio.php");
    exit();
}

if (isset($_POST['ingresar'])) {
    $email = mysqli_real_escape_string($conexion, $_POST['email']);
    $password = $_POST['password'];

    $consulta = "SELECT * FROM usuario WHERE email = '$email'";
    $resultado = mysqli_query($conexion, $consulta);

    if (mysqli_num_rows($resultado) > 0) {
        $usuario = mysqli_fetch_assoc($resultado);
        
        if (password_verify($password, $usuario['password_hash'])) {
            $_SESSION['nombre_usuario'] = $usuario['nombre'];
            header("Location: inicio.php");
            exit();
        } else {
            header("Location: error_login.php");
            exit();
        }
    } else {
        header("Location: error_login.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - KSS</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <div class="flex-center">
        <div class="login-container">
            
           <div class="logo-wrapper">
   <img src="logo.png.png" alt="Logo KSS" class="login-logo">
</div>

            <h2>Bienvenido</h2>
            <p class="subtitulo">Ingresa tus credenciales para continuar</p>

            <form method="POST" action="">
                <input type="email" name="email" placeholder="Correo electrónico" required>
                <input type="password" name="password" placeholder="Tu contraseña" required>
                <button type="submit" name="ingresar">Entrar a la tienda</button>
            </form>
            
            <p class="footer-text">
                ¿Aún no tienes cuenta? <a href="registro.php">Regístrate gratis</a>
            </p>
        </div>
    </div>
</body>
