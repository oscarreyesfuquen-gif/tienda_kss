<?php
session_start();
include("conexion.php");

// 1. SEGURIDAD: Solo administradores
if (!isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 2) {
    header("Location: inicio.php");
    exit();
}

$mensaje = "";
$producto = null;

// 2. CARGAR LOS DATOS ACTUALES DEL PRODUCTO
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id_producto = $_GET['id'];
    
    try {
        // Traemos el producto específico
        $stmt = $conexion->prepare("SELECT * FROM producto WHERE id_producto = :id LIMIT 1");
        $stmt->bindParam(':id', $id_producto, PDO::PARAM_INT);
        $stmt->execute();
        $producto = $stmt->fetch();
        
        if (!$producto) {
            header("Location: admin_productos.php");
            exit();
        }
        
        // Traemos las categorías para el menú desplegable
        $stmt_cat = $conexion->query("SELECT id_categoria, nombre_categoria FROM categoria");
        $categorias = $stmt_cat->fetchAll();
        
    } catch (PDOException $e) {
        error_log("Error al cargar producto para edición: " . $e->getMessage());
        header("Location: admin_productos.php");
        exit();
    }
} else {
    header("Location: admin_productos.php");
    exit();
}

// 3. PROCESAR LA ACTUALIZACIÓN (CUANDO SE DA CLIC EN ACTUALIZAR)
if (isset($_POST['actualizar_producto'])) {
    $nombre = $_POST['nombre_producto'];
    $descripcion = $_POST['descripcion'];
    $precio = $_POST['precio'];
    $id_categoria = $_POST['id_categoria'];
    
    // Mantenemos la imagen actual por defecto si no suben una nueva
    $nombre_imagen_final = $producto['image']; 

    // Si el administrador decidió subir una nueva foto
    if (isset($_FILES['foto_producto']) && $_FILES['foto_producto']['error'] == 0) {
        $nombre_original = $_FILES['foto_producto']['name'];
        $ruta_temporal = $_FILES['foto_producto']['tmp_name'];
        $extension = pathinfo($nombre_original, PATHINFO_EXTENSION);
        
        // Nombre único para la nueva foto
        $nombre_imagen_final = "prod_" . time() . "." . $extension;
        $ruta_destino = $nombre_imagen_final; 

        if (!move_uploaded_file($ruta_temporal, $ruta_destino)) {
            $nombre_imagen_final = $producto['image']; // Si falla, conserva la vieja
            $mensaje = "⚠️ No se pudo cargar la nueva imagen, se mantuvo la anterior.";
        }
    }

    // 4. EJECUTAR EL UPDATE CON PDO
    try {
        $sql = "UPDATE producto 
                SET nombre_producto = :nombre, descripcion = :descripcion, precio = :precio, image = :imagen, id_categoria = :id_cat 
                WHERE id_producto = :id";
        
        $stmt_update = $conexion->prepare($sql);
        $stmt_update->bindParam(':nombre', $nombre, PDO::PARAM_STR);
        $stmt_update->bindParam(':descripcion', $descripcion, PDO::PARAM_STR);
        $stmt_update->bindParam(':precio', $precio, PDO::PARAM_INT);
        $stmt_update->bindParam(':imagen', $nombre_imagen_final, PDO::PARAM_STR);
        $stmt_update->bindParam(':id_cat', $id_categoria, PDO::PARAM_INT);
        $stmt_update->bindParam(':id', $id_producto, PDO::PARAM_INT);
        
        if ($stmt_update->execute()) {
            echo "<script>
                    alert('¡Producto actualizado con éxito en el inventario!');
                    window.location.href='admin_productos.php';
                  </script>";
            exit();
        }
    } catch (PDOException $e) {
        error_log("Error al actualizar producto: " . $e->getMessage());
        $mensaje = "❌ Error al guardar los cambios en la base de datos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Producto - The Kings</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>

    <div class="main-content">
        <header class="header-tienda">
            <h1>Editar Producto ✏️</h1>
            <a href="admin_productos.php" class="link-volver">⬅ Volver al Panel</a>
        </header>

        <div class="login-container" style="margin-top: 20px; max-width: 500px;">
            <h2>Modificar Atributos</h2>
            
            <?php if(!empty($mensaje)): ?>
                <p style="color: #ffc107; font-weight: bold; text-align: center;"><?php echo $mensaje; ?></p>
            <?php endif; ?>

            <form method="POST" action="" enctype="multipart/form-data">
                
                <div class="input-group">
                    <label>Nombre del Producto:</label>
                    <input type="text" name="nombre_producto" value="<?php echo htmlspecialchars($producto['nombre_producto']); ?>" required>
                </div>

                <div class="input-group">
                    <label>Categoría:</label>
                    <select name="id_categoria" required style="width: 100%; background: #222; color: #fff; border: 1px solid #d4af37; border-radius: 5px; padding: 10px;">
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?php echo $cat['id_categoria']; ?>" <?php echo ($cat['id_categoria'] == $producto['id_categoria']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['nombre_categoria']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="input-group">
                    <label>Descripción:</label>
                    <textarea name="descripcion" rows="4" style="width: 100%; background: #222; color: #fff; border: 1px solid #d4af37; border-radius: 5px; padding: 10px; box-sizing: border-box;" required><?php echo htmlspecialchars($producto['descripcion']); ?></textarea>
                </div>

                <div class="input-group">
                    <label>Precio (COP):</label>
                    <input type="number" name="precio" value="<?php echo $producto['precio']; ?>" min="0" required>
                </div>

                <div class="input-group">
                    <label>Imagen Actual:</label>
                    <div style="margin-bottom: 10px;">
                        <img src="<?php echo $producto['image']; ?>" style="width: 70px; height: 70px; object-fit: cover; border-radius: 5px; border: 1px solid #d4af37;">
                    </div>
                    <label style="font-size: 12px; color: #aaa;">Subir nueva imagen (opcional):</label>
                    <input type="file" name="foto_producto" accept="image/*" style="border: none; padding: 5px 0;">
                </div>

                <button type="submit" name="actualizar_producto" class="btn-carrito" style="width: 100%; margin-top: 15px; padding: 12px;">
                    Guardar Cambios
                </button>
            </form>
        </div>
    </div>

</body>
</html>