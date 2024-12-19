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
    $sucursal_id = intval($_POST['sucursal_id']); // Obtener el sucursal_id del formulario

    // Preparar la consulta para insertar el depósito
    $sql = "INSERT INTO depositos (nombre, created_at, updated_at, estado_activo) 
            VALUES (:nombre, NOW(), NOW(), 1)";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':nombre', $nombre);

    if ($stmt->execute()) {
        // Obtener el último ID insertado
        $deposito_id = $conn->lastInsertId();

        // Insertar en la tabla depositosxsucursal
        $sql = "INSERT INTO depositosxsucursal (sucursal_id, deposito_id, estado_activo) 
                VALUES (:sucursal_id, :deposito_id, 1)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':sucursal_id', $sucursal_id);
        $stmt->bindParam(':deposito_id', $deposito_id);

        if ($stmt->execute()) {
            // Guardar en la sesión el mensaje de éxito
            session_start();
            $_SESSION['mensaje_exito'] = 'Depósito agregado correctamente';
            // Obtener la URL de la página anterior
            $previousPage = $_SERVER['HTTP_REFERER'];
            // Agregar el parámetro de mensaje a la URL
            $previousPageWithMessage = $previousPage . (strpos($previousPage, '?') !== false ? '&' : '?') . "show_message=1";
            // Redirigir a la página anterior con el parámetro de mensaje
            header("Location: $previousPageWithMessage");
            exit;
        } else {
            echo "<script>alert('Error al asociar el depósito a la sucursal.');</script>";
        }
    } else {
        echo "<script>alert('Error al agregar el depósito.');</script>";
    }
}

?>
