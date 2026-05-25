<?php
include("conexion.php"); // Ahora inyecta el motor PDO automáticamente
session_start(); 

if (isset($_SESSION['nombre_usuario'])) {
    header("Location: inicio.php");
    exit();
}

if (isset($_POST['ingresar'])) {
    $email = trim($_POST['email']); // Limpiamos espacios en blanco accidentales
    $password = $_POST['password'];

    try {
        // 1. Preparamos la consulta usando un marcador (:email) en lugar de la variable directa
        $consulta = "SELECT * FROM usuario WHERE email = :email";
        $stmt = $conexion->prepare($consulta);
        
        // 2. Vinculamos el dato de forma segura
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        
        // 3. Ejecutamos la consulta en el servidor
        $stmt->execute();
        
        // 4. Traemos el registro si existe
        $usuario = $stmt->fetch();

        if ($usuario) {
            // 5. Validamos la contraseña usando el hash de la base de datos
            if (password_verify($password, $usuario['password_hash'])) {
                $_SESSION['nombre_usuario'] = $usuario['nombre'];
                header("Location: inicio.php");
                exit();
            } else {
                // Contraseña incorrecta
                header("Location: error_login.php");
                exit();
            }
        } else {
            // Usuario no encontrado
            header("Location: error_login.php");
            exit();
        }

    } catch (PDOException $e) {
        // Si hay un fallo en la consulta, lo registramos oculto por seguridad
        error_log("Error en el login: " . $e->getMessage());
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

