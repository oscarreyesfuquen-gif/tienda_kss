<?php
session_start();
include("conexion.php");

$carrito_vacio = true;
$total = 0;

if (isset($_SESSION['carrito']) && count($_SESSION['carrito']) > 0) {
    $carrito_vacio = false;
    $ids = implode(',', $_SESSION['carrito']);
    $query = "SELECT * FROM producto WHERE id_producto IN ($ids)";
    $resultado = mysqli_query($conexion, $query);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Carrito - KSS</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
    <div class="main-content">
        <header class="header-tienda">
            <h1>Tu Carrito de Compras 🛒</h1>
            <a href="inicio.php" class="link-volver">⬅ Volver a la tienda</a>
        </header>

        <div class="login-container carrito-ancho">
            <?php if ($carrito_vacio): ?>
                <p>Tu carrito está vacío.</p>
            <?php else: ?>
                <table class="tabla-carrito">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Precio</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($resultado)): 
                            $total += $row['precio'];
                        ?>
                            <tr>
                                <td><?php echo $row['nombre_producto']; ?></td>
                                <td>$<?php echo number_format($row['precio'], 0, ',', '.'); ?></td>
                                <td>
                                    <form method="POST" action="eliminar_item.php">
                                        <input type="hidden" name="id_eliminar" value="<?php echo $row['id_producto']; ?>">
                                        <button type="submit" class="btn-eliminar">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                
                <div class="carrito-resumen">
                    <h3>Total a pagar: <span>$<?php echo number_format($total, 0, ',', '.'); ?></span></h3>
                    
                    <form method="POST" action="finalizar_compra.php">
                        <button type="submit" class="btn-finalizar">Finalizar Compra</button>
                    </form>

                    <a href="vaciar_carrito.php" class="link-vaciar">Vaciar carrito</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
