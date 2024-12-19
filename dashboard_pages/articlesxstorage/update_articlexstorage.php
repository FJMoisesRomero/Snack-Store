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
// Validar y capturar los datos del formulario
$articulo_id = isset($_POST['articulo_id']) ? intval($_POST['articulo_id']) : 0;
$deposito_id = isset($_POST['deposito_id']) ? intval($_POST['deposito_id']) : 0;
$stock_minimo = isset($_POST['stock_minimo']) ? intval($_POST['stock_minimo']) : 0;

if ($articulo_id <= 0 || $deposito_id <= 0) {
    echo "Datos no válidos.";
    exit();
}

// Actualizar el stock mínimo en la tabla articulosxdeposito
$sql = "UPDATE articulosxdeposito SET stock_minimo = :stock_minimo WHERE articulo_id = :articulo_id AND deposito_id = :deposito_id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':stock_minimo', $stock_minimo);
$stmt->bindParam(':articulo_id', $articulo_id);
$stmt->bindParam(':deposito_id', $deposito_id);

if ($stmt->execute()) {
    // Guardar en la sesión el mensaje de éxito
    session_start();
    $_SESSION['mensaje_exito'] = 'Stock mínimo actualizado correctamente.';
    
    // Obtener la URL de la página anterior
    $previousPage = $_SERVER['HTTP_REFERER'];

    // Agregar el parámetro de mensaje a la URL
    $previousPageWithMessage = $previousPage . (strpos($previousPage, '?') !== false ? '&' : '?') . "show_message=1";

    // Redirigir a la página anterior con el parámetro de mensaje
    header("Location: $previousPageWithMessage");
    exit;
} else {
    // Mostrar un mensaje de error
    echo "<script>alert('Error al actualizar el stock mínimo.');</script>";
}
?>
