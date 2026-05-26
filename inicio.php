<?php
session_start();
include("conexion.php");

// 1. LÓGICA DEL CARRITO CON CONTROL DE ACCESO
if (isset($_POST['agregar_carrito'])) {
    // Si el usuario NO ha iniciado sesión, lo frenamos de inmediato
    if (!isset($_SESSION['nombre_usuario'])) {
        echo "<script>
                alert('🛒 ¡Para añadir productos al carrito debes iniciar sesión primero!');
                window.location.href='login.php';
              </script>";
        exit();
    }
    
    // Si la sesión existe, procedemos a guardar en el carrito
    $id_p = $_POST['id_producto'];
    if (!isset($_SESSION['carrito'])) {
        $_SESSION['carrito'] = array();
    }
    $_SESSION['carrito'][] = $id_p;
    header("Location: inicio.php"); 
    exit();
}

// 2. CONSULTA DE PRODUCTOS CON PDO
try {
    $query = "SELECT * FROM producto";
    $stmt = $conexion->query($query); 
    $productos = $stmt->fetchAll();   
} catch (PDOException $e) {
    error_log("Error al consultar productos: " . $e->getMessage());
    $productos = array(); 
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Tienda KSS - Catálogo Real</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>

    <header class="header-tienda">
        <div class="logo-container">
            <img src="logo.png.png" alt="Logo KSS" class="header-logo">
        </div>

        <h1>Bienvenido, <span class="user-name"><?php echo isset($_SESSION['nombre_usuario']) ? htmlspecialchars($_SESSION['nombre_usuario']) : 'Invitado 👤'; ?></span></h1>

        <div class="carrito-wrapper">
            <?php if (isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 2): ?>
                <a href="admin_productos.php" class="btn-carrito" style="background: #d4af37; color: #000; text-decoration: none; font-weight: bold; padding: 8px 15px; margin-right: 15px; border-radius: 5px;">
                    👑 Panel Admin
                </a>
            <?php endif; ?>

            <a href="ver_carrito.php" class="carrito-contador" style="text-decoration: none;">
                🛒 Productos en carrito: 
                <strong>
                    <?php echo isset($_SESSION['carrito']) ? count($_SESSION['carrito']) : 0; ?>
                </strong>
            </a>
        </div>

      <?php if (isset($_SESSION['nombre_usuario'])): ?>
            <a href="logout.php" class="btn-salir" style="text-decoration: none;">Cerrar Sesión</a>
        <?php else: ?>
            <a href="login.php" class="carrito-contador" style="text-decoration: none; margin-left: 10px;">
                🔑 Iniciar Sesión
            </a>
        <?php endif; ?>

    <main class="productos-grid">
        <?php 
        if (!empty($productos)) {
            foreach ($productos as $row) { 
        ?>
            <article class="producto-card">
                <div class="info-superior">
                    <div class="producto-imagen-wrapper">
                        <?php 
                        $nombre_foto = !empty($row['image']) ? $row['image'] : 'logo.png.png'; 
                        ?>
                        <img src="<?php echo $nombre_foto; ?>" 
                             alt="<?php echo htmlspecialchars($row['nombre_producto']); ?>" 
                             class="producto-img-catalogo">
                    </div>
                    
                    <h3><?php echo htmlspecialchars($row['nombre_producto']); ?></h3>
                    <p><?php echo htmlspecialchars($row['descripcion']); ?></p>
                </div>
                
                <div class="info-inferior">
                    <p class="precio">$<?php echo number_format($row['precio'], 0, ',', '.'); ?></p>
                    <form method="POST" action="">
                        <input type="hidden" name="id_producto" value="<?php echo $row['id_producto']; ?>">
                        <button type="submit" name="agregar_carrito" class="btn-carrito">
                            Añadir al carrito
                        </button>
                    </form>
                </div>
            </article>
        <?php 
            } 
        } else {
            echo "<p class='error-mensaje'>No hay productos disponibles en este momento.</p>";
        }
        ?>
    </main>

</body>
</html>