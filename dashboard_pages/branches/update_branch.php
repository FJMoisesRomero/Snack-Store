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
    $nombre = $_POST['nombre'];
    $responsable = $_POST['responsable'];
    $direccion = $_POST['direccion']; // Cambié 'capacidad' por 'direccion'

    // Preparar la consulta para actualizar la sucursal
    $sql = "UPDATE sucursales SET 
                nombre = :nombre, 
                responsable = :responsable,
                direccion = :direccion 
            WHERE id = :id";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':responsable', $responsable);
    $stmt->bindParam(':direccion', $direccion); // Cambié 'capacidad' por 'direccion'

    if ($stmt->execute()) {
        // Guardar en la sesión el mensaje de éxito
        session_start();
        $_SESSION['mensaje_exito'] = 'Sucursal actualizada correctamente';
        // Obtener la URL de la página anterior
        $previousPage = $_SERVER['HTTP_REFERER'];

        // Agregar el parámetro de mensaje a la URL
        $previousPageWithMessage = $previousPage . (strpos($previousPage, '?') !== false ? '&' : '?') . "show_message=1";

        // Redirigir a la página anterior con el parámetro de mensaje
        header("Location: $previousPageWithMessage");
        exit;
    } else {
        echo "<script>alert('Error al actualizar la sucursal.');</script>";
    }
}
?>
