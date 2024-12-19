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
$sucursal_id = isset($_POST['sucursal_id']) ? intval($_POST['sucursal_id']) : 0;
$deposito_id = isset($_POST['deposito_id']) ? intval($_POST['deposito_id']) : 0;

if ($sucursal_id <= 0 || $deposito_id <= 0) {
    $_SESSION['mensaje_error'] = 'Datos inválidos.';
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit();
}

try {
    $conn->beginTransaction();

    // Verificar si el depósito ya está asociado con la sucursal
    $stmt = $conn->prepare("SELECT * FROM depositosxsucursal WHERE sucursal_id = :sucursal_id AND deposito_id = :deposito_id");
    $stmt->bindParam(':sucursal_id', $sucursal_id);
    $stmt->bindParam(':deposito_id', $deposito_id);
    $stmt->execute();

    if ($stmt->fetch(PDO::FETCH_ASSOC)) {
        throw new Exception("El depósito ya está asociado con la sucursal.");
    }

    // Insertar el registro en depositosxsucursal
    $stmt = $conn->prepare("INSERT INTO depositosxsucursal (sucursal_id, deposito_id, estado_activo) VALUES (:sucursal_id, :deposito_id, 1)");
    $stmt->bindParam(':sucursal_id', $sucursal_id);
    $stmt->bindParam(':deposito_id', $deposito_id);
    $stmt->execute();

    $conn->commit();
    $_SESSION['mensaje_exito'] = 'Depósito agregado a la sucursal con éxito.';
} catch (Exception $e) {
    $conn->rollBack();
    $_SESSION['mensaje_error'] = 'Error al agregar el depósito: ' . $e->getMessage();
}

// Obtener la URL de la página anterior y agregar el parámetro de mensaje
$previousPage = $_SERVER['HTTP_REFERER'];
$previousPageWithMessage = $previousPage . (strpos($previousPage, '?') !== false ? '&' : '?') . "show_message=1";

header("Location: $previousPageWithMessage");
exit();
?>
