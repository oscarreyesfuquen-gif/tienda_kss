<?php
include("conexion.php");

if (isset($_POST['registrar'])) {
    // 1. SANITIZACIÓN: Limpiamos los datos antes de tocarlos
    // trim() quita espacios vacíos accidentales al inicio y al final
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Eliminamos cualquier etiqueta HTML o scripts sospechosos del nombre
    $nombre = filter_var($nombre, FILTER_SANITIZE_SPECIAL_CHARS);
    
    // Validamos y limpiamos que el correo sea estructuralmente un email válido
    $email = filter_var($email, FILTER_VALIDATE_EMAIL);

    if (!$email) {
        echo "<script>
                alert('Error: El correo electrónico ingresado no tiene un formato válido.');
                window.history.back();
              </script>";
        exit();
    }

    try {
        // 2. VERIFICAR EMAIL EXISTENTE CON PDO (Consulta Preparada)
        $stmt_buscar = $conexion->prepare("SELECT id_usuario FROM usuario WHERE email = :email LIMIT 1");
        $stmt_buscar->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt_buscar->execute();
        
        // fetch() nos dice si encontró una fila
        if ($stmt_buscar->fetch()) {
            echo "<script>
                    alert('Error: Este correo electrónico ya está registrado. Intenta con otro o inicia sesión.');
                    window.history.back();
                  </script>";
            exit();
        } else {
            // 3. REGISTRO SEGURO CON PDO
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $documento_aleatorio = "ID-" . rand(100, 999);

            // Recuerda que añadimos 'id_rol' en el pilar anterior, por defecto será 1 (Cliente)
            $sql = "INSERT INTO usuario (tipo_doc, documento, nombre, apellidos, email, password_hash, id_rol) 
                    VALUES ('CC', :documento, :nombre, '', :email, :password_hash, 1)";

            $stmt_insertar = $conexion->prepare($sql);
            $stmt_insertar->bindParam(':documento', $documento_aleatorio, PDO::PARAM_STR);
            $stmt_insertar->bindParam(':nombre', $nombre, PDO::PARAM_STR);
            $stmt_insertar->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt_insertar->bindParam(':password_hash', $password_hash, PDO::PARAM_STR);

            if ($stmt_insertar->execute()) {
                echo "<script>
                        alert('¡Usuario registrado con éxito!');
                        window.location.href='login.php';
                      </script>";
                exit();
            }
        }
    } catch (PDOException $e) {
        error_log("Error crítico en el registro: " . $e->getMessage());
        echo "Lo sentimos, ocurrió un error interno al procesar tu registro.";
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
</html>