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

// Capturar el ID del depósito, tipo de movimiento, fecha y destino desde el formulario
$deposito_id = isset($_POST['deposito_id']) ? intval($_POST['deposito_id']) : 0;
$movimiento_tipo_id = isset($_POST['movimiento_tipo_id']) ? intval($_POST['movimiento_tipo_id']) : 0;
$fecha = isset($_POST['fecha']) ? $_POST['fecha'] : null;
$destino = isset($_POST['destino']) ? $_POST['destino'] : '-';
$observacion = isset($_POST['observacion']) ? $_POST['observacion'] : '-';
$articulos = isset($_POST['articulo_id']) ? $_POST['articulo_id'] : [];
$stocks = isset($_POST['stock']) ? $_POST['stock'] : [];

// Validar datos
if ($deposito_id <= 0 || $movimiento_tipo_id <= 0 || empty($fecha) || count($articulos) == 0 || count($articulos) != count($stocks)) {
    // Guardar en la sesión el mensaje de ERROR
    session_start();
    $_SESSION['mensaje_error'] = 'Datos de Artículos no válidos.';

    // Obtener la URL de la página anterior
    $previousPage = $_SERVER['HTTP_REFERER'];

    // Agregar el parámetro de mensaje a la URL
    $previousPageWithMessage = $previousPage . (strpos($previousPage, '?') !== false ? '&' : '?') . "show_message_error=1";

    // Redirigir a la página anterior con el parámetro de mensaje
    header("Location: $previousPageWithMessage");
    exit;
}



// Validar formato de fecha (opcional)
if (!DateTime::createFromFormat('Y-m-d', $fecha)) {
    echo "Fecha no válida.";
    exit();
}

// Generar código de comprobante
$comprobante_cod = strtoupper(uniqid('MOV'));

// Completar el campo destino automáticamente si el tipo de movimiento no es EGRESO
if ($movimiento_tipo_id != 2) { // Asumiendo que el ID 2 corresponde a EGRESO
    try {
        $stmt = $conn->prepare("
            SELECT d.id AS deposito_id, d.nombre AS deposito_nombre, s.nombre AS sucursal_nombre
            FROM depositos d
            LEFT JOIN depositosxsucursal ds ON d.id = ds.deposito_id
            LEFT JOIN sucursales s ON ds.sucursal_id = s.id
            WHERE d.id = :deposito_id
        ");
        $stmt->bindParam(':deposito_id', $deposito_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            $destino = htmlspecialchars($result['deposito_nombre']) . ' - ' . htmlspecialchars($result['sucursal_nombre']);
        } else {
            $destino = 'Destino desconocido'; // Valor por defecto si no se encuentra el depósito
        }
    } catch (PDOException $e) {
        echo "Error al obtener el destino: " . $e->getMessage();
        exit();
    }
}

try {
    $conn->beginTransaction();

    // Insertar movimiento
    $stmt = $conn->prepare("INSERT INTO movimientosxdeposito (deposito_id, movimiento_tipo_id, comprobante_cod, fecha, destino, observacion, created_at, updated_at) VALUES (:deposito_id, :movimiento_tipo_id, :comprobante_cod, :fecha, :destino, :observacion, NOW(), NOW())");
    $stmt->bindParam(':deposito_id', $deposito_id);
    $stmt->bindParam(':movimiento_tipo_id', $movimiento_tipo_id);
    $stmt->bindParam(':comprobante_cod', $comprobante_cod);
    $stmt->bindParam(':fecha', $fecha);
    $stmt->bindParam(':destino', $destino, PDO::PARAM_STR);
    $stmt->bindParam(':observacion', $observacion);
    $stmt->execute();
    
    $movimiento_id = $conn->lastInsertId();

    // Insertar detalles del movimiento
    $stmt = $conn->prepare("INSERT INTO movimiento_detalles (articulo_id, articulo_descripcion, stock, movimiento_id, created_at, updated_at) VALUES (:articulo_id, :articulo_descripcion, :stock, :movimiento_id, NOW(), NOW())");
    
    foreach ($articulos as $index => $articulo_id) {
        $stock = $stocks[$index];
        
        // Consultar el artículo completo
        $stmtArticulo = $conn->prepare("SELECT a.nombre, m.nombre AS marca_nombre, c.nombre AS categoria_nombre
            FROM articulos a
            LEFT JOIN marcas m ON a.marca_id = m.id
            LEFT JOIN categorias c ON a.categoria_id = c.id
            WHERE a.id = :articulo_id");
        $stmtArticulo->bindParam(':articulo_id', $articulo_id);
        $stmtArticulo->execute();
        $articulo = $stmtArticulo->fetch(PDO::FETCH_ASSOC);
        
        if ($articulo) {
            $articulo_descripcion = htmlspecialchars($articulo['nombre']) . ' - ' . 
                htmlspecialchars($articulo['marca_nombre']) . ' - ' . 
                htmlspecialchars($articulo['categoria_nombre']);

            $stmt->bindParam(':articulo_id', $articulo_id);
            $stmt->bindParam(':articulo_descripcion', $articulo_descripcion);
            $stmt->bindParam(':stock', $stock);
            $stmt->bindParam(':movimiento_id', $movimiento_id);
            $stmt->execute();

            // Actualizar o insertar en articulosxdeposito
            $stmtCheck = $conn->prepare("SELECT id, stock FROM articulosxdeposito WHERE articulo_id = :articulo_id AND deposito_id = :deposito_id");
            $stmtCheck->bindParam(':articulo_id', $articulo_id);
            $stmtCheck->bindParam(':deposito_id', $deposito_id);
            $stmtCheck->execute();
            $exists = $stmtCheck->fetch(PDO::FETCH_ASSOC);

            if ($exists) {
                // Verificar el tipo de movimiento para determinar si restar o sumar el stock
                if ($movimiento_tipo_id == 2) { // Asumiendo que el ID 2 corresponde a EGRESO
                    // Restar stock
                    $stmtUpdate = $conn->prepare("UPDATE articulosxdeposito SET stock = stock - :stock, updated_at = NOW() WHERE id = :id");
                } else {
                    // Sumar stock (por defecto se suma si no es EGRESO)
                    $stmtUpdate = $conn->prepare("UPDATE articulosxdeposito SET stock = stock + :stock, updated_at = NOW() WHERE id = :id");
                }
                $stmtUpdate->bindParam(':stock', $stock);
                $stmtUpdate->bindParam(':id', $exists['id']);
                $stmtUpdate->execute();
            } else {
                // Insertar nuevo registro
                $stmtInsert = $conn->prepare("INSERT INTO articulosxdeposito (articulo_id, deposito_id, stock, created_at, updated_at) VALUES (:articulo_id, :deposito_id, :stock, NOW(), NOW())");
                $stmtInsert->bindParam(':articulo_id', $articulo_id);
                $stmtInsert->bindParam(':deposito_id', $deposito_id);
                $stmtInsert->bindParam(':stock', $stock);
                $stmtInsert->execute();
            }
        }
    }

    $conn->commit();

    // Guardar en la sesión el mensaje de éxito
    session_start();
    $_SESSION['mensaje_exito'] = 'Movimiento agregado exitosamente.';

    // Obtener la URL de la página anterior
    $previousPage = $_SERVER['HTTP_REFERER'];

    // Agregar el parámetro de mensaje a la URL
    $previousPageWithMessage = $previousPage . (strpos($previousPage, '?') !== false ? '&' : '?') . "show_message=1";

    // Redirigir a la página anterior con el parámetro de mensaje
    header("Location: $previousPageWithMessage");
    exit;
} catch (PDOException $e) {
    $conn->rollBack();
    echo "Error: " . $e->getMessage();
}
?>
