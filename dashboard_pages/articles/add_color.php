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

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['nombre_color']) && isset($_POST['codigo_color'])) {
    $nombre = $_POST['nombre_color'];
    $codigo_color = $_POST['codigo_color'];

    // Insertar el nuevo color en la base de datos
    $stmt = $conn->prepare("INSERT INTO colores (nombre, codigo_color) VALUES (:nombre, :codigo_color)");
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':codigo_color', $codigo_color);
    
    if ($stmt->execute()) {
        session_start();
        $_SESSION['mensaje_exito'] = 'Color agregado correctamente';
        $previousPage = $_SERVER['HTTP_REFERER'];
        $previousPageWithMessage = $previousPage . (strpos($previousPage, '?') !== false ? '&' : '?') . "show_message=1";
        header("Location: $previousPageWithMessage");
        exit;
    } else {
        echo "<script>alert('Error al agregar el color.');</script>";
    }
}
?>
