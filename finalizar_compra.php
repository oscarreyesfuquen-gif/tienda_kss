<?php
session_start();
include("conexion.php");

if (!isset($_SESSION['nombre_usuario']) || empty($_SESSION['carrito'])) {
    header("Location: inicio.php");
    exit();
}

$nombre_u = $_SESSION['nombre_usuario'];

try {
    // 1. INICIAMOS LA TRANSACCIÓN (Bloque de seguridad absoluta)
    $conexion->beginTransaction();

    // 2. OBTENER EL ID DEL USUARIO
    $stmt_u = $conexion->prepare("SELECT id_usuario FROM usuario WHERE nombre = :nombre LIMIT 1");
    $stmt_u->bindParam(':nombre', $nombre_u, PDO::PARAM_STR);
    $stmt_u->execute();
    $user = $stmt_u->fetch();
    
    if (!$user) {
        throw new Exception("Usuario no encontrado en la sesión.");
    }
    $id_usuario = $user['id_usuario'];

    // 3. CALCULAR EL TOTAL FINAL
    $lista_ids = array_filter($_SESSION['carrito']);
    $interrogaciones = str_repeat('?,', count($lista_ids) - 1) . '?';
    
    $stmt_p = $conexion->prepare("SELECT SUM(precio) as total FROM producto WHERE id_producto IN ($interrogaciones)");
    $stmt_p->execute(array_values($lista_ids));
    $data_p = $stmt_p->fetch();
    $total_final = $data_p['total'] ?? 0;

    // 4. INSERTAR LA CABECERA DEL PEDIDO
    $sql_pedido = "INSERT INTO pedido (id_usuario, total, id_estado) VALUES (:id_usuario, :total, 1)";
    $stmt_pedido = $conexion->prepare($sql_pedido);
    $stmt_pedido->bindParam(':id_usuario', $id_usuario, PDO::PARAM_INT);
    $stmt_pedido->bindParam(':total', $total_final, PDO::PARAM_STR);
    $stmt_pedido->execute();
    
    // Capturamos el ID del pedido recién creado usando el método nativo de PDO
    $id_nuevo_pedido = $conexion->lastInsertId();

    // 5. PREPARAMOS LAS CONSULTAS DEL CICLO UNA SOLA VEZ (Optimiza el rendimiento)
    $stmt_det_prod = $conexion->prepare("SELECT precio FROM producto WHERE id_producto = :id_prod");
    $sql_detalle = "INSERT INTO detalle_pedido (id_pedido, id_producto, cantidad, precio_unitario) 
                    VALUES (:id_pedido, :id_producto, 1, :precio_u)";
    $stmt_detalle = $conexion->prepare($sql_detalle);

    // 6. RECORREMOS EL CARRITO E INSERTAMOS LOS DETALLES
    foreach ($_SESSION['carrito'] as $id_prod) {
        // Consultamos el precio de forma segura
        $stmt_det_prod->bindParam(':id_prod', $id_prod, PDO::PARAM_INT);
        $stmt_det_prod->execute();
        $prod = $stmt_det_prod->fetch();
        $precio_u = $prod['precio'] ?? 0;

        // Insertamos el detalle de forma segura
        $stmt_detalle->bindParam(':id_pedido', $id_nuevo_pedido, PDO::PARAM_INT);
        $stmt_detalle->bindParam(':id_producto', $id_prod, PDO::PARAM_INT);
        $stmt_detalle->bindParam(':precio_u', $precio_u, PDO::PARAM_STR);
        $stmt_detalle->execute();
    }

    // 7. SI TODO SALIÓ BIEN, CONFIRMAMOS Y GUARDAMOS EN LA BD
    $conexion->commit();

    // Vaciamos la bolsa de compras de la sesión
    unset($_SESSION['carrito']);

    echo "<script>
            alert('¡Compra realizada con éxito! Tu pedido es el #$id_nuevo_pedido');
            window.location.href='inicio.php';
          </script>";

} catch (Exception $e) {
    // 8. SI ALGO FALLA, DESHACEMOS TODO LO QUE SE HAYA CAMBIADO
    if ($conexion->inTransaction()) {
        $conexion->rollBack();
    }

    error_log("Error crítico al procesar la compra: " . $e->getMessage());
    echo "<script>
            alert('Lo sentimos, ocurrió un error interno al procesar tu pago. Tu dinero y carrito están seguros.');
            window.location.href='ver_carrito.php';
          </script>";
}
?>