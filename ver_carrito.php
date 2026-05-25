<?php
session_start();
include("conexion.php");

$carrito_vacio = true;
$total = 0;
$productos_carrito = array();

if (isset($_SESSION['carrito']) && count($_SESSION['carrito']) > 0) {
    $carrito_vacio = false;
    
    // 1. Limpiamos duplicados o valores vacíos por seguridad
    $lista_ids = array_filter($_SESSION['carrito']); 
    
    if (!empty($lista_ids)) {
        try {
            // 2. Creamos una cadena con signos de interrogación según la cantidad de IDs. Ej: (?, ?, ?)
            $interrogaciones = str_repeat('?,', count($lista_ids) - 1) . '?';
            
            // 3. Preparamos la consulta SQL dinámica
            $query = "SELECT * FROM producto WHERE id_producto IN ($interrogaciones)";
            $stmt = $conexion->prepare($query);
            
            // 4. Ejecutamos pasando el arreglo directo (PDO mapea cada ID a un signo '?')
            $stmt->execute(array_values($lista_ids));
            
            // 5. Capturamos todos los productos
            $productos_carrito = $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error al cargar el carrito: " . $e->getMessage());
            $carrito_vacio = true; // Si falla la BD, protegemos el flujo asumiendo vacío
        }
    }
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
            <?php if ($carrito_vacio || empty($productos_carrito)): ?>
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
                        <?php 
                        // Usamos un ciclo foreach moderno para iterar sobre los productos traídos por PDO
                        foreach ($productos_carrito as $row): 
                            $total += $row['precio'];
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['nombre_producto']); ?></td>
                                <td>$<?php echo number_format($row['precio'], 0, ',', '.'); ?></td>
                                <td>
                                    <form method="POST" action="eliminar_item.php">
                                        <input type="hidden" name="id_eliminar" value="<?php echo $row['id_producto']; ?>">
                                        <button type="submit" class="btn-eliminar">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
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