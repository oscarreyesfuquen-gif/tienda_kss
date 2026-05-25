<?php
session_start();

if (isset($_POST['id_eliminar'])) {
    $id = $_POST['id_eliminar'];
    
    // Buscamos la posición del producto en la lista
    $posicion = array_search($id, $_SESSION['carrito']);
    
    // Si lo encuentra, lo borra
    if ($posicion !== false) {
        unset($_SESSION['carrito'][$posicion]);
        // Re-indexamos el array para que no queden huecos
        $_SESSION['carrito'] = array_values($_SESSION['carrito']);
    }
}

header("Location: ver_carrito.php");
exit();
?>
