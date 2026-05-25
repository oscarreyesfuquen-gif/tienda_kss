<?php
session_start();
include("conexion.php");

// 1. SEGURIDAD: Solo el administrador (rol 2) puede ejecutar este borrado
if (!isset($_SESSION['id_rol']) || $_SESSION['id_rol'] != 2) {
    header("Location: inicio.php");
    exit();
}

// 2. VALIDACIÓN: Verificamos que llegue un ID válido por la URL (método GET)
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id_producto = $_GET['id'];

    try {
        // 3. SENTENCIA PREPARADA: Borrado seguro inmune a inyección SQL
        $sql = "DELETE FROM producto WHERE id_producto = :id";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':id', $id_producto, PDO::PARAM_INT);

        if ($stmt->execute()) {
            // Si se borra con éxito, mandamos una alerta y refrescamos el panel
            echo "<script>
                    alert('¡Producto eliminado correctamente del inventario!');
                    window.location.href='admin_productos.php';
                  </script>";
            exit();
        }
    } catch (PDOException $e) {
        // Guardamos el error real en el log interno del servidor
        error_log("Error al eliminar producto: " . $e->getMessage());
        
        // Alerta elegante para el usuario si el producto está amarrado a un pedido existente
        echo "<script>
                alert('❌ No se puede eliminar el producto porque está asociado a un historial de compras existente.');
                window.location.href='admin_productos.php';
              </script>";
        exit();
    }
} else {
    // Si intentan entrar al archivo directo sin un ID, los regresamos al panel
    header("Location: admin_productos.php");
    exit();
}
?>