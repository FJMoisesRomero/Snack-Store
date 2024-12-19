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

session_start();

if (isset($_GET['id']) && isset($_GET['sucursal_id'])) {
    $deposito_id = intval($_GET['id']);
    $sucursal_id = intval($_GET['sucursal_id']);

    if ($deposito_id <= 0 || $sucursal_id <= 0) {
        $_SESSION['mensaje_error'] = 'ID no válido.';
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }

    try {
        // Iniciar una transacción
        $conn->beginTransaction();

        // Eliminar la asociación del depósito con la sucursal
        $stmt = $conn->prepare("DELETE FROM depositosxsucursal WHERE deposito_id = :deposito_id AND sucursal_id = :sucursal_id");
        $stmt->bindParam(':deposito_id', $deposito_id);
        $stmt->bindParam(':sucursal_id', $sucursal_id);
        $stmt->execute();

        // Confirmar la transacción
        $conn->commit();
        $_SESSION['mensaje_exito'] = 'Depósito eliminado de la sucursal.';
    } catch (Exception $e) {
        // Revertir la transacción si ocurre un error
        $conn->rollBack();
        $_SESSION['mensaje_error'] = 'Error al eliminar el depósito: ' . $e->getMessage();
    }

    // Redirigir con mensaje
    $previousPage = $_SERVER['HTTP_REFERER'];
    $previousPageWithMessage = $previousPage . (strpos($previousPage, '?') !== false ? '&' : '?') . "show_message=1";

    header("Location: $previousPageWithMessage");
    exit();
}
?>
