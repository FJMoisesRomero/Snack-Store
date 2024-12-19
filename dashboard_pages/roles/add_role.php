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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $rol = $_POST['rol'];

    // Normalizar el rol del formulario (solo para comparar)
    $normalized_rol = strtolower(trim($rol));
    $normalized_rol = preg_replace('/[áàäâ]/u', 'a', $normalized_rol);
    $normalized_rol = preg_replace('/[éèëê]/u', 'e', $normalized_rol);
    $normalized_rol = preg_replace('/[íìïî]/u', 'i', $normalized_rol);
    $normalized_rol = preg_replace('/[óòöô]/u', 'o', $normalized_rol);
    $normalized_rol = preg_replace('/[úùüû]/u', 'u', $normalized_rol);
    $normalized_rol = str_replace(' ', '', $normalized_rol); // Eliminar todos los espacios

    // Verificar si el rol ya existe usando la normalización temporal
    $stmt = $conn->prepare("SELECT COUNT(*) FROM roles WHERE REPLACE(LOWER(rol), ' ', '') = :rol");
    $stmt->bindParam(':rol', $normalized_rol);
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

    // Obtener el próximo ID disponible
    $stmt = $conn->prepare("SELECT MIN(id) AS next_id FROM roles WHERE id NOT IN (SELECT id FROM roles)");
    $stmt->execute();
    $next_id = $stmt->fetch(PDO::FETCH_ASSOC)['next_id'];

    // Si no hay IDs disponibles, usar el máximo ID actual + 1
    if ($next_id === null) {
        $stmt = $conn->prepare("SELECT MAX(id) AS max_id FROM roles");
        $stmt->execute();
        $max_id = $stmt->fetch(PDO::FETCH_ASSOC)['max_id'];
        $next_id = $max_id + 1;
    }

    // Asegurarse de que el próximo ID no sea 0
    if ($next_id <= 0) {
        $next_id = 1;
    }

    // Preparar la consulta para insertar el nuevo rol
    $stmt = $conn->prepare("INSERT INTO roles (id, rol, created_at, updated_at) VALUES (:id, :rol, NOW(), NOW())");
    $stmt->bindParam(':id', $next_id);
    $stmt->bindParam(':rol', $rol); // Aquí se usa el rol original sin normalizar

    try {
        $stmt->execute();
        // Guardar en la sesión el mensaje de éxito
        session_start();
        $_SESSION['mensaje_exito'] = 'Rol agregado correctamente';
        // Obtener la URL de la página anterior
        $previousPage = $_SERVER['HTTP_REFERER'];
        // Agregar el parámetro de mensaje a la URL
        $previousPageWithMessage = $previousPage . (strpos($previousPage, '?') !== false ? '&' : '?') . "show_message=1";
        // Redirigir a la página anterior con el parámetro de mensaje
        header("Location: $previousPageWithMessage");
        exit;
    } catch (PDOException $e) {
        echo "<script>alert('Error al agregar el rol: " . $e->getMessage() . "');</script>";
    }
}
?>
