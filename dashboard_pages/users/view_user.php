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


// Obtener el DNI del usuario
if (isset($_GET['id'])) {
    $dni = $_GET['id'];
    $stmt = $conn->prepare("
        SELECT usuarios.*, roles.rol 
        FROM usuarios 
        LEFT JOIN roles ON usuarios.rol_id = roles.id 
        WHERE usuarios.dni = :dni
    ");
    $stmt->bindParam(':dni', $dni);
    $stmt->execute();
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        // Mostrar HTML con los detalles del usuario
        echo '<div class="user-details">';
        
        // Mostrar la imagen del usuario
        if ($usuario['imagen_usuario']) {
            $imagen_usuario = base64_encode($usuario['imagen_usuario']);
            echo '<div class="image-container">';
            echo '<img class="user-image" src="data:image/jpeg;base64,' . $imagen_usuario . '" alt="Imagen de Usuario"/>';
            echo '</div>';
        } else {
            echo '<div class="image-container">';
            echo '<img class="user-image" src="images/userImage1.png" alt="Imagen de Usuario por defecto"/>';
            echo '</div>';
        }
        echo '<p><strong>Rol:</strong> ' . htmlspecialchars($usuario['rol']) . '</p>';
        echo '<p><strong>DNI:</strong> ' . htmlspecialchars($usuario['dni']) . '</p>';
        echo '<p><strong>Nombre:</strong> ' . htmlspecialchars($usuario['nombre']) . '</p>';
        echo '<p><strong>Apellido:</strong> ' . htmlspecialchars($usuario['apellido']) . '</p>';
        echo '<p><strong>Email:</strong> ' . htmlspecialchars($usuario['email']) . '</p>';
        echo '<p><strong>Usuario:</strong> ' . htmlspecialchars($usuario['usuario']) . '</p>';
        echo '<p><strong>Creado el:</strong> ' . htmlspecialchars($usuario['created_at']) . '</p>';
        echo '<p><strong>Actualizado el:</strong> ' . htmlspecialchars($usuario['updated_at']) . '</p>';
        echo '</div>';
    } else {
        echo 'Usuario no encontrado.';
    }
} else {
    echo 'No se ha proporcionado un ID.';
}
?>
