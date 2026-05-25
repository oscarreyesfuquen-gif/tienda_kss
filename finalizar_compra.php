<?php
session_start();
include("conexion.php");

if (!isset($_SESSION['nombre_usuario']) || empty($_SESSION['carrito'])) {
    header("Location: inicio.php");
    exit();
}

$nombre_u = $_SESSION['nombre_usuario'];
$res_u = mysqli_query($conexion, "SELECT id_usuario FROM usuario WHERE nombre = '$nombre_u' LIMIT 1");
$user = mysqli_fetch_assoc($res_u);
$id_usuario = $user['id_usuario'];

$ids = implode(',', $_SESSION['carrito']);
$res_p = mysqli_query($conexion, "SELECT SUM(precio) as total FROM producto WHERE id_producto IN ($ids)");
$data_p = mysqli_fetch_assoc($res_p);
$total_final = $data_p['total'];

$sql_pedido = "INSERT INTO pedido (id_usuario, total, id_estado) VALUES ($id_usuario, $total_final, 1)";

if (mysqli_query($conexion, $sql_pedido)) {
    $id_nuevo_pedido = mysqli_insert_id($conexion);

    foreach ($_SESSION['carrito'] as $id_prod) {
        $res_det = mysqli_query($conexion, "SELECT precio FROM producto WHERE id_producto = $id_prod");
        $prod = mysqli_fetch_assoc($res_det);
        $precio_u = $prod['precio'];

        $sql_detalle = "INSERT INTO detalle_pedido (id_pedido, id_producto, cantidad, precio_unitario) 
                        VALUES ($id_nuevo_pedido, $id_prod, 1, $precio_u)";
        mysqli_query($conexion, $sql_detalle);
    }

    unset($_SESSION['carrito']);
    echo "<script>
            alert('¡Compra realizada con éxito! Tu pedido es el #$id_nuevo_pedido');
            window.location.href='inicio.php';
          </script>";
} else {
    echo "Error al procesar: " . mysqli_error($conexion);
}
?>
