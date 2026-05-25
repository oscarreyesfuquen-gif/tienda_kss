<?php
session_start();
unset($_SESSION['carrito']); // Solo borramos la bolsa de compras, no la sesión del usuario
header("Location: inicio.php");
?>
