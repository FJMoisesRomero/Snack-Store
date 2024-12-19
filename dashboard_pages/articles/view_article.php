<?php
$servername = "sql106.infinityfree.com";
$username = "if0_37317346";
$password = "Laapetitosa44";
$dbname = "if0_37317346_apetitosa";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit();
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int) $_GET['id'];  // Asegurarse de que el ID sea un entero
    
    $stmt = $conn->prepare("
        SELECT a.id, a.nombre, a.descripcion, a.imagen, m.nombre AS marca_nombre, c.nombre AS categoria_nombre, a.created_at, a.updated_at
        FROM articulos a
        LEFT JOIN marcas m ON a.marca_id = m.id
        LEFT JOIN categorias c ON a.categoria_id = c.id
        WHERE a.id = :id
    ");
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    
    $articulo = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($articulo) {
        // Mostrar imagen
        if ($articulo['imagen']) {
            $imagen_articulo = base64_encode($articulo['imagen']);
            echo "<div style='text-align: center;'><img src='data:image/jpeg;base64,{$imagen_articulo}' alt='Imagen del Artículo' style='width: 100px; height: auto;'></div>";
        } else {
            echo "<div style='text-align: center;'><img src='images/default-image.png' alt='Imagen por defecto' style='width: 100px; height: auto;'></div>";
        }
        
        // Mostrar detalles del artículo
        echo "<p><strong>ID:</strong> " . htmlspecialchars($articulo['id']) . "</p>";
        echo "<p><strong>Nombre:</strong> " . htmlspecialchars($articulo['nombre']) . "</p>";
        echo "<p><strong>Descripción:</strong> " . htmlspecialchars($articulo['descripcion']) . "</p>";
        echo "<p><strong>Marca:</strong> " . htmlspecialchars($articulo['marca_nombre']) . "</p>";
        echo "<p><strong>Categoría:</strong> " . htmlspecialchars($articulo['categoria_nombre']) . "</p>";
        
        // Mostrar fechas
        echo "<p><strong>Fecha de creación:</strong> " . date('d-m-Y H:i:s', strtotime($articulo['created_at'])) . "</p>";
        echo "<p><strong>Última actualización:</strong> " . date('d-m-Y H:i:s', strtotime($articulo['updated_at'])) . "</p>";

    } else {
        echo "<p>No se encontraron detalles para el artículo solicitado.</p>";
    }
} else {
    echo "<p>ID del artículo no especificado o inválido.</p>";
}

?>
