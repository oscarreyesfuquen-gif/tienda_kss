<?php
include("conexion.php");

if (isset($_POST['registrar'])) {
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $email = mysqli_real_escape_string($conexion, $_POST['email']);
    $password = $_POST['password'];

    // 1. PRIMERO VERIFICAMOS SI EL EMAIL YA EXISTE
    $buscarEmail = "SELECT * FROM usuario WHERE email = '$email'";
    $resultadoBusqueda = mysqli_query($conexion, $buscarEmail);

    if (mysqli_num_rows($resultadoBusqueda) > 0) {
        // Si el correo ya está en la DB, avisamos y no insertamos
        echo "<script>
                alert('Error: Este correo electrónico ya está registrado. Intenta con otro o inicia sesión.');
                window.history.back();
              </script>";
    } else {
        // 2. SI NO EXISTE, PROCEDEMOS AL REGISTRO NORMAL
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO usuario (tipo_doc, documento, nombre, apellidos, email, password_hash) 
                VALUES ('CC', 'ID-".rand(100,999)."', '$nombre', '', '$email', '$password_hash')";

        if (mysqli_query($conexion, $sql)) {
            echo "<script>
                    alert('¡Usuario registrado con éxito!');
                    window.location.href='login.php';
                  </script>";
        } else {
            echo "Error al registrar: " . mysqli_error($conexion);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - KSS</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <div class="flex-center">
        <div class="login-container">
            
          <div class="logo-wrapper">
<img src="logo.png.png" alt="Logo KSS" class="login-logo">
</div>

            <h2>Crear Cuenta</h2>
            <p class="subtitulo">Únete a la comunidad KSS</p>

            <form method="POST" action="">
                <input type="text" name="nombre" placeholder="Nombre completo" required>
                <input type="email" name="email" placeholder="Correo electrónico" required>
                <input type="password" name="password" placeholder="Crea una contraseña" required>
                <button type="submit" name="registrar">Registrarse ahora</button>
            </form>
            
            <p class="footer-text">
                ¿Ya tienes cuenta? <a href="login.php">Inicia sesión aquí</a>
            </p>
        </div>
    </div>
</body>
