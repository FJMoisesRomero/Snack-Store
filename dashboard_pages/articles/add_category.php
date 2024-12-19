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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['nombre_categoria'])) {
    $nombre_categoria = $_POST['nombre_categoria'];

    // Insertar la nueva categoría en la base de datos
    $stmt = $conn->prepare("INSERT INTO categorias (nombre) VALUES (:nombre_categoria)");
    $stmt->bindParam(':nombre_categoria', $nombre_categoria);
    
    if ($stmt->execute()) {
        session_start();
        $_SESSION['mensaje_exito'] = 'Categoría agregada correctamente';
        $previousPage = $_SERVER['HTTP_REFERER'];
        $previousPageWithMessage = $previousPage . (strpos($previousPage, '?') !== false ? '&' : '?') . "show_message=1";
        header("Location: $previousPageWithMessage");
        exit;
    } else {
        echo "<script>alert('Error al agregar la categoría.');</script>";
    }
}
?>
