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

// Verificar si se ha enviado el formulario de edición
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $rol = $_POST['rol'];

    // Normalizar el rol del formulario (solo para comparar)
    $normalized_rol = strtolower(trim($rol));
    $normalized_rol = preg_replace('/[áàäâ]/u', 'a', $normalized_rol);
    $normalized_rol = preg_replace('/[éèëê]/u', 'e', $normalized_rol);
    $normalized_rol = preg_replace('/[íìïî]/u', 'i', $normalized_rol);
    $normalized_rol = preg_replace('/[óòöô]/u', 'o', $normalized_rol);
    $normalized_rol = preg_replace('/[úùüû]/u', 'u', $normalized_rol);
    $normalized_rol = str_replace(' ', '', $normalized_rol); // Eliminar todos los espacios

    // Verificar si el rol ya existe usando la normalización temporal, excluyendo el rol actual
    $stmt = $conn->prepare("
        SELECT COUNT(*) 
        FROM roles 
        WHERE REPLACE(LOWER(rol), ' ', '') = :rol AND id != :id
    ");
    $stmt->bindParam(':rol', $normalized_rol);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $exists = $stmt->fetchColumn();

    if ($exists > 0) {
        session_start();
        $_SESSION['mensaje_error'] = 'El rol ya existe.';
        
        // Obtener la URL de la página anterior
        $previousPage = $_SERVER['HTTP_REFERER'];
        
        // Agregar el parámetro de mensaje a la URL
        $previousPageWithMessage = $previousPage . (strpos($previousPage, '?') !== false ? '&' : '?') . "show_message_error=1";
        
        // Redirigir a la página anterior con el parámetro de mensaje
        header("Location: $previousPageWithMessage");
        exit;
    }

    // Preparar la consulta para actualizar el rol
    $sql = "UPDATE roles SET rol = :rol WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':rol', $rol); // Aquí se usa el rol original sin normalizar

    if ($stmt->execute()) {
        // Guardar en la sesión el mensaje de éxito
        session_start();
        $_SESSION['mensaje_exito'] = 'Rol actualizado correctamente';
        
        // Obtener la URL de la página anterior
        $previousPage = $_SERVER['HTTP_REFERER'];
        
        // Agregar el parámetro de mensaje a la URL
        $previousPageWithMessage = $previousPage . (strpos($previousPage, '?') !== false ? '&' : '?') . "show_message=1";

        // Redirigir a la página anterior con el parámetro de mensaje
        header("Location: $previousPageWithMessage");
        exit;
    } else {
        echo "<script>alert('Error al actualizar el rol.');</script>";
    }
}
?>
