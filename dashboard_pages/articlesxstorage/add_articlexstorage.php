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
// Iniciar la sesión para usar mensajes
session_start();

// Capturar los datos del formulario
$articulo_id = isset($_POST['articulo_id']) ? intval($_POST['articulo_id']) : 0;
$cantidad = isset($_POST['cantidad']) ? intval($_POST['cantidad']) : 0;
$deposito_id = isset($_POST['deposito_id']) ? intval($_POST['deposito_id']) : 0;

if ($articulo_id <= 0 || $cantidad <= 0 || $deposito_id <= 0) {
    $_SESSION['mensaje_error'] = 'Datos inválidos.';
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}

try {
    $conn->beginTransaction();

    // Consultar el stock actual del artículo
    $stmt = $conn->prepare("SELECT stock FROM articulos WHERE id = :articulo_id FOR UPDATE");
    $stmt->bindParam(':articulo_id', $articulo_id);
    $stmt->execute();
    $articulo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$articulo) {
        throw new Exception("Artículo no encontrado.");
    }

    $stock_actual = $articulo['stock'];

    // Verificar si hay suficiente stock
    if ($cantidad > $stock_actual) {
        throw new Exception("Cantidad superior al stock disponible.");
    }

    // Actualizar el stock del artículo
    $nuevo_stock = $stock_actual - $cantidad;
    $stmt = $conn->prepare("UPDATE articulos SET stock = :nuevo_stock WHERE id = :articulo_id");
    $stmt->bindParam(':nuevo_stock', $nuevo_stock);
    $stmt->bindParam(':articulo_id', $articulo_id);
    $stmt->execute();

    // Insertar el registro en articulosxdeposito
    $stmt = $conn->prepare("INSERT INTO articulosxdeposito (articulo_id, deposito_id, cantidad, estado_activo) VALUES (:articulo_id, :deposito_id, :cantidad, 1)");
    $stmt->bindParam(':articulo_id', $articulo_id);
    $stmt->bindParam(':deposito_id', $deposito_id);
    $stmt->bindParam(':cantidad', $cantidad);
    $stmt->execute();

    $conn->commit();
    $_SESSION['mensaje_exito'] = 'Artículo agregado al depósito con éxito.';
} catch (Exception $e) {
    $conn->rollBack();
    $_SESSION['mensaje_error'] = 'Error al agregar el artículo: ' . $e->getMessage();
}

// Obtener la URL de la página anterior y agregar el parámetro de mensaje
$previousPage = $_SERVER['HTTP_REFERER'];
$previousPageWithMessage = $previousPage . (strpos($previousPage, '?') !== false ? '&' : '?') . "show_message=1";

header("Location: $previousPageWithMessage");
exit();
?>
