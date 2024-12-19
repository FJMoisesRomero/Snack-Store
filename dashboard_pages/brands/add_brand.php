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

// Verificar si se ha enviado el formulario de adición
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre = $_POST['nombre'];

    // Normalizar el nombre de la marca (solo para comparar)
    $normalized_nombre = strtolower(trim($nombre));
    $normalized_nombre = preg_replace('/[áàäâ]/u', 'a', $normalized_nombre);
    $normalized_nombre = preg_replace('/[éèëê]/u', 'e', $normalized_nombre);
    $normalized_nombre = preg_replace('/[íìïî]/u', 'i', $normalized_nombre);
    $normalized_nombre = preg_replace('/[óòöô]/u', 'o', $normalized_nombre);
    $normalized_nombre = preg_replace('/[úùüû]/u', 'u', $normalized_nombre);
    $normalized_nombre = str_replace(' ', '', $normalized_nombre); // Eliminar todos los espacios

    // Verificar si la marca ya existe usando la normalización temporal
    $stmt = $conn->prepare("SELECT COUNT(*) FROM marcas WHERE REPLACE(LOWER(nombre), ' ', '') = :nombre");
    $stmt->bindParam(':nombre', $normalized_nombre);
    $stmt->execute();
    $exists = $stmt->fetchColumn();

    if ($exists > 0) {
        session_start();
        $_SESSION['mensaje_error'] = 'La marca ya existe.';
        
        // Obtener la URL de la página anterior
        $previousPage = $_SERVER['HTTP_REFERER'];
        
        // Agregar el parámetro de mensaje a la URL
        $previousPageWithMessage = $previousPage . (strpos($previousPage, '?') !== false ? '&' : '?') . "show_message_error=1";
        
        // Redirigir a la página anterior con el parámetro de mensaje
        header("Location: $previousPageWithMessage");
        exit;
    }

    // Preparar la consulta para insertar la marca
    $sql = "INSERT INTO marcas (nombre, created_at, updated_at, estado_activo) 
            VALUES (:nombre, NOW(), NOW(), 1)";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':nombre', $nombre); // Aquí se usa el nombre original sin normalizar

    if ($stmt->execute()) {
        // Guardar en la sesión el mensaje de éxito
        session_start();
        $_SESSION['mensaje_exito'] = 'Marca agregada correctamente';
        
        // Obtener la URL de la página anterior
        $previousPage = $_SERVER['HTTP_REFERER'];
        
        // Agregar el parámetro de mensaje a la URL
        $previousPageWithMessage = $previousPage . (strpos($previousPage, '?') !== false ? '&' : '?') . "show_message=1";

        // Redirigir a la página anterior con el parámetro de mensaje
        header("Location: $previousPageWithMessage");
        exit;
    } else {
        echo "<script>alert('Error al agregar la marca.');</script>";
    }
}
?>
