<?php
$host = "localhost";
$user = "root";
$pass = ""; 
$db   = "kss";
$port = 3307; 

$conexion = mysqli_connect($host, $user, $pass, $db, $port);

if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}
?>
