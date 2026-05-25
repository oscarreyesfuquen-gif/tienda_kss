<?php
session_start();
include("conexion.php");

// 1. SEGURIDAD: Solo administradores entran aquí
if (!isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 2) {
    header("Location: inicio.php");
    exit();
}

$mensaje = "";

// 2. PROCESAR EL FORMULARIO CUANDO SE DA CLIC EN GUARDAR
if (isset($_POST['guardar_producto'])) {
    $nombre = $_POST['nombre_producto'];
    $descripcion = $_POST['descripcion'];
    $precio = $_POST['precio'];
    
    // Configuración por defecto si no suben foto
    $nombre_imagen_final = "logo.png.png"; 

    // 3. LÓGICA DE SUBIDA DE IMÁGENES AL SERVIDOR
    if (isset($_FILES['foto_producto']) && $_FILES['foto_producto']['error'] == 0) {
        $nombre_original = $_FILES['foto_producto']['name'];
        $ruta_temporal = $_FILES['foto_producto']['tmp_name'];
        
        // Extraemos la extensión del archivo (jpg, png, webp, etc.)
        $extension = pathinfo($nombre_original, PATHINFO_EXTENSION);
        
        // Creamos un nombre único para evitar que fotos con el mismo nombre se borren entre sí
        $nombre_imagen_final = "prod_" . time() . "." . $extension;
        
        // Destino físico en tu carpeta de XAMPP
        $ruta_destino = $nombre_imagen_final; 

        // Movemos el archivo temporal a tu carpeta real del proyecto
        if (!move_uploaded_file($ruta_temporal, $ruta_destino)) {
            $nombre_imagen_final = "logo.png.png"; // Si falla la subida, se le asigna el logo por defecto
            $mensaje = "⚠️ Advertencia: No se pudo guardar la imagen en el servidor, usando logo predeterminado.";
        }
    }

    // 4. INSERCIÓN EN LA BASE DE DATOS USANDO PDO (BLINDADO)
    try {
        $sql = "INSERT INTO producto (nombre_producto, descripcion, precio, image) 
                VALUES (:nombre, :descripcion, :precio, :imagen)";
        
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':nombre', $nombre, PDO::PARAM_STR);
        $stmt->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
        $stmt->bindParam(':precio', $precio, PDO::PARAM_INT);
        $stmt->bindParam(':imagen', $nombre_imagen_final, PDO::PARAM_STR);
        
        if ($stmt->execute()) {
            echo "<script>
                    alert('¡Producto agregado con éxito al inventario!');
                    window.location.href='admin_productos.php';
                  </script>";
            exit();
        }
    } catch (PDOException $e) {
        error_log("Error al insertar producto: " . $e->getMessage());
        $mensaje = "❌ Error crítico: No se pudo guardar el producto en la base de datos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nuevo Producto - The Kings</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>

    <div class="main-content">
        <header class="header-tienda">
            <h1>Agregar Nuevo Producto ➕</h1>
            <a href="admin_productos.php" class="link-volver">⬅ Volver al Panel</a>
        </header>

        <div class="login-container" style="margin-top: 20px; max-width: 500px;">
            <h2>Detalles del Artículo</h2>
            
            <?php if(!empty($mensaje)): ?>
                <p style="color: #ffc107; font-weight: bold; text-align: center;"><?php echo $mensaje; ?></p>
            <?php endif; ?>

            <form method="POST" action="" enctype="multipart/form-data">
                
                <div class="input-group">
                    <label>Nombre del Producto:</label>
                    <input type="text" name="nombre_producto" placeholder="Ej: Gorra The Kings" required>
                </div>

                <div class="input-group">
                    <label>Descripción:</label>
                    <textarea name="descripcion" placeholder="Escribe las características del producto..." rows="4" style="width: 100%; background: #222; color: #fff; border: 1px solid #d4af37; border-radius: 5px; padding: 10px; box-sizing: border-radius;" required></textarea>
                </div>

                <div class="input-group">
                    <label>Precio (COP):</label>
                    <input type="number" name="precio" placeholder="Ej: 45000" min="0" required>
                </div>

                <div class="input-group">
                    <label>Imagen del Producto:</label>
                    <input type="file" name="foto_producto" accept="image/*" style="border: none; padding: 5px 0;">
                </div>

                <button type="submit" name="guardar_producto" class="btn-carrito" style="width: 100%; margin-top: 15px; padding: 12px;">
                    Guardar e Inmortalizar Producto
                </button>
            </form>
        </div>
    </div>

</body>
</html>