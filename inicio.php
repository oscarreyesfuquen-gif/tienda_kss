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

// 3. CONSULTA DE PRODUCTOS
$query = "SELECT * FROM producto";
$resultado = mysqli_query($conexion, $query);
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

        <h1>Bienvenido, <span class="user-name"><?php echo $_SESSION['nombre_usuario']; ?></span> 👋</h1>

        <div class="carrito-wrapper">
            <a href="ver_carrito.php" class="carrito-contador">
                🛒 Productos en carrito: 
                <strong>
                    <?php echo isset($_SESSION['carrito']) ? count($_SESSION['carrito']) : 0; ?>
                </strong>
            </a>
        </div>

        <a href="logout.php" class="btn-salir">Cerrar Sesión</a>
    </header>

    <main class="productos-grid">
        <?php while ($row = mysqli_fetch_assoc($resultado)) { ?>
            <article class="producto-card">
                <div class="info-superior">
                    <div class="producto-imagen-wrapper">
                        <?php 
                        // IMPORTANTE: Usamos 'image' porque así aparece en tu phpMyAdmin
                        $nombre_foto = !empty($row['image']) ? $row['image'] : 'logo.png.png'; 
                        ?>
                        <img src="<?php echo $nombre_foto; ?>" 
                             alt="<?php echo $row['nombre_producto']; ?>" 
                             class="producto-img-catalogo">
                    </div>
                    
                    <h3><?php echo $row['nombre_producto']; ?></h3>
                    <p><?php echo $row['descripcion']; ?></p>
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
        <?php } ?>
    </main>

</body>
</html>

<script>
    document.querySelectorAll('.btn-carrito').forEach(boton => {
        boton.addEventListener('click', () => {
            alert('¡Excelente elección! El producto se ha añadido a tu carrito de The Kings.');
        });
    });
</script>