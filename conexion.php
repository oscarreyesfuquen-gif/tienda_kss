<?php
$host = "localhost";
$user = "root";
$pass = ""; 
$db   = "kss";
$port = 3307; 

try {
    // 1. Configuramos el DNS (Origen de los datos) especificando host, puerto y base de datos
    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
    
    // 2. Creamos la conexión PDO
    $conexion = new PDO($dsn, $user, $pass);
    
    // 3. Configuramos PDO para que maneje los errores lanzando "Excepciones"
    // Esto es crucial para la seguridad y el bloque try-catch
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // 4. Configuración opcional: para que los resultados de las consultas vengan como arreglos asociativos
    $conexion->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // 5. Si algo falla, el código salta aquí. 
    // En lugar de romper la página con datos sensibles, guardamos el error en un log silencioso
    error_log("Error de conexión a la BD: " . $e->getMessage());
    
    // Y al usuario le mostramos un mensaje amigable y limpio
    die("Lo sentimos, en este momento experimentamos problemas técnicos. Por favor, intenta más tarde.");
}
?>