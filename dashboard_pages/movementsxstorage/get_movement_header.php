<?php
$servername = "sql106.infinityfree.com";
$username = "if0_37317346";
$password = "Laapetitosa44";
$dbname = "if0_37317346_apetitosa";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['error' => "Connection failed: " . $e->getMessage()]);
    exit();
}

// Capturar el ID del movimiento desde la solicitud
$movimiento_id = isset($_GET['movimiento_id']) ? intval($_GET['movimiento_id']) : 0;

if ($movimiento_id <= 0) {
    echo json_encode(['error' => 'ID de movimiento no válido.']);
    exit();
}

try {
    // Consultar la información de la cabecera del movimiento
    $stmt = $conn->prepare("
        SELECT 
            m.comprobante_cod,
            m.fecha,
            m.destino,
            m.observacion
        FROM movimientosxdeposito m
        WHERE m.id = :movimiento_id
    ");
    $stmt->bindParam(':movimiento_id', $movimiento_id);
    $stmt->execute();
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // Devolver los resultados en formato JSON
    echo json_encode($result);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
