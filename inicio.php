<?php
session_start();
include("conexion.php");

// 1. SEGURIDAD: Si no hay sesión, al login
if (!isset($_SESSION['nombre_usuario'])) {
    header("Location: login.php");
    exit();
}

// 2. LÓGICA DEL CARRITO
if (isset($_POST['agregar_carrito'])) {
    $id_p = $_POST['id_producto'];
    if (!isset($_SESSION['carrito'])) {
        $_SESSION['carrito'] = array();
    }
    $_SESSION['carrito'][] = $id_p;
    header("Location: inicio.php"); 
    exit();
}

// 3. CONSULTA DE PRODUCTOS CON PDO
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

        <h1>Bienvenido, <span class="user-name"><?php echo htmlspecialchars($_SESSION['nombre_usuario']); ?></span> 👋</h1>

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

        <a href="logout.php" class="btn-salir">Cerrar Sesión</a>
    </header>

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