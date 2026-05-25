<?php
session_start();
include("conexion.php");

// CONTROL DE ACCESO CRÍTICO: Si no es administrador (rol 2), lo sacamos a patadas al inicio
if (!isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 2) {
    header("Location: inicio.php");
    exit();
}

// Consultamos todos los productos de la base de datos con PDO
try {
    $query = "SELECT * FROM producto";
    $stmt = $conexion->query($query);
    $productos = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error en el panel de administración: " . $e->getMessage());
    $productos = array();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Control - The Kings</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>

    <header class="header-tienda">
        <div class="logo-container">
            <img src="logo.png.png" alt="Logo KSS" class="header-logo">
        </div>
        <h1>Panel de Administración 👑</h1>
        <div class="botones-nav">
            <a href="inicio.php" class="link-volver">Ver Tienda</a>
            <a href="logout.php" class="btn-salir">Cerrar Sesión</a>
        </div>
    </header>

    <main class="login-container carrito-ancho" style="margin-top: 30px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h2>Gestión de Inventario</h2>
            <a href="agregar_producto.php" class="btn-carrito" style="text-decoration: none; padding: 10px 20px;">+ Agregar Producto</a>
        </div>

        <table class="tabla-carrito">
            <thead>
                <tr>
                    <th>Imagen</th>
                    <th>Producto</th>
                    <th>Descripción</th>
                    <th>Precio</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($productos)): ?>
                    <?php foreach ($productos as $prod): ?>
                        <tr>
                            <td>
                                <img src="<?php echo !empty($prod['image']) ? $prod['image'] : 'logo.png.png'; ?>" 
                                     alt="Miniatura" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                            </td>
                            <td style="font-weight: bold;"><?php echo htmlspecialchars($prod['nombre_producto']); ?></td>
                            <td><?php echo htmlspecialchars($prod['descripcion']); ?></td>
                            <td style="color: #d4af37; font-weight: bold;">$<?php echo number_format($prod['precio'], 0, ',', '.'); ?></td>
                            <td>
                                <div style="display: flex; gap: 10px;">
                                    <a href="editar_producto.php?id=<?php echo $prod['id_producto']; ?>" class="btn-carrito" style="background: #28a745; text-decoration: none; padding: 5px 10px; font-size: 12px;">Editar</a>
                                    <a href="eliminar_producto.php?id=<?php echo $prod['id_producto']; ?>" class="btn-eliminar" style="text-decoration: none; padding: 5px 10px; font-size: 12px;" onclick="return confirm('¿Seguro que deseas eliminar este producto?');">Eliminar</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5">No hay productos registrados en el sistema.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>

</body>
</html>
