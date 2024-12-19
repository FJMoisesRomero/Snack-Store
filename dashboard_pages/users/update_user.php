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
    $dni = $_POST['dni'];
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $email = $_POST['email'];
    $usuario = $_POST['usuario'];
    $rol_id = $_POST['rol_id'];
    $imagen_usuario = null;

    if (isset($_FILES['imagen_usuario']) && $_FILES['imagen_usuario']['error'] == UPLOAD_ERR_OK) {
        $imagen_usuario = file_get_contents($_FILES['imagen_usuario']['tmp_name']);
    }

    // Preparar la consulta para actualizar el usuario
    $sql = "UPDATE usuarios SET nombre = :nombre, apellido = :apellido, email = :email, usuario = :usuario, rol_id = :rol_id" .
           ($imagen_usuario ? ", imagen_usuario = :imagen_usuario" : "") .
           " WHERE dni = :dni";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':dni', $dni);
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':apellido', $apellido);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':usuario', $usuario);
    $stmt->bindParam(':rol_id', $rol_id);

    if ($imagen_usuario) {
        $stmt->bindParam(':imagen_usuario', $imagen_usuario, PDO::PARAM_LOB);
    }

    if ($stmt->execute()) {
        // Guardar en la sesión el mensaje de éxito
        session_start();
        $_SESSION['mensaje_exito'] = 'Usuario actualizado correctamente';
        // Obtener la URL de la página anterior
        $previousPage = $_SERVER['HTTP_REFERER'];

        // Agregar el parámetro de mensaje a la URL
        $previousPageWithMessage = $previousPage . (strpos($previousPage, '?') !== false ? '&' : '?') . "show_message=1";

        // Redirigir a la página anterior con el parámetro de mensaje
        header("Location: $previousPageWithMessage");
        exit;
    } else {
        echo "<script>alert('Error al actualizar el usuario.');</script>";
    }
}
?>
