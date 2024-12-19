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

if (isset($_GET['delete']) && isset($_GET['deposito_id'])) {
    $articulo_id = intval($_GET['delete']);
    $deposito_id = intval($_GET['deposito_id']);

    if ($articulo_id <= 0 || $deposito_id <= 0) {
        $_SESSION['mensaje_error'] = 'ID no válido.';
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }

    try {
        // Iniciar una transacción
        $conn->beginTransaction();

        // Consultar la cantidad del artículo en el depósito
        $stmt = $conn->prepare("SELECT cantidad FROM articulosxdeposito WHERE articulo_id = :articulo_id AND deposito_id = :deposito_id");
        $stmt->bindParam(':articulo_id', $articulo_id);
        $stmt->bindParam(':deposito_id', $deposito_id);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($resultado) {
            $cantidad = $resultado['cantidad'];

            // Eliminar el artículo del depósito
            $stmt = $conn->prepare("DELETE FROM articulosxdeposito WHERE articulo_id = :articulo_id AND deposito_id = :deposito_id");
            $stmt->bindParam(':articulo_id', $articulo_id);
            $stmt->bindParam(':deposito_id', $deposito_id);
            $stmt->execute();

            // Sumar la cantidad al stock del artículo
            $stmt = $conn->prepare("UPDATE articulos SET stock = stock + :cantidad WHERE id = :articulo_id");
            $stmt->bindParam(':cantidad', $cantidad);
            $stmt->bindParam(':articulo_id', $articulo_id);
            $stmt->execute();
        }

        // Confirmar la transacción
        $conn->commit();
        $_SESSION['mensaje_exito'] = 'Artículo eliminado del depósito.';
    } catch (Exception $e) {
        // Revertir la transacción si ocurre un error
        $conn->rollBack();
        $_SESSION['mensaje_error'] = 'Error al eliminar el artículo: ' . $e->getMessage();
    }

    // Redirigir con mensaje
    $previousPage = $_SERVER['HTTP_REFERER'];
    $previousPageWithMessage = $previousPage . (strpos($previousPage, '?') !== false ? '&' : '?') . "show_message=1";

    header("Location: $previousPageWithMessage");
    exit();
}
?>
