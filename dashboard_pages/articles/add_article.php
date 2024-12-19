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
    $descripcion = $_POST['descripcion'];
    $marca_id = $_POST['marca_id'];
    $categoria_id = $_POST['categoria_id'];

    // Normalizar el nombre del artículo (solo para comparar)
    $normalized_nombre = strtolower(trim($nombre));
    $normalized_nombre = preg_replace('/[áàäâ]/u', 'a', $normalized_nombre);
    $normalized_nombre = preg_replace('/[éèëê]/u', 'e', $normalized_nombre);
    $normalized_nombre = preg_replace('/[íìïî]/u', 'i', $normalized_nombre);
    $normalized_nombre = preg_replace('/[óòöô]/u', 'o', $normalized_nombre);
    $normalized_nombre = preg_replace('/[úùüû]/u', 'u', $normalized_nombre);
    $normalized_nombre = str_replace(' ', '', $normalized_nombre); // Eliminar todos los espacios

    // Verificar si la combinación ya existe
    $stmt = $conn->prepare("SELECT COUNT(*) FROM articulos WHERE 
                             REPLACE(LOWER(nombre), ' ', '') = :nombre AND 
                             marca_id = :marca_id AND 
                             categoria_id = :categoria_id");
    $stmt->bindParam(':nombre', $normalized_nombre);
    $stmt->bindParam(':marca_id', $marca_id);
    $stmt->bindParam(':categoria_id', $categoria_id);
    $stmt->execute();
    $exists = $stmt->fetchColumn();

    if ($exists > 0) {
        session_start();
        $_SESSION['mensaje_error'] = 'El artículo ya existe con la misma combinación de nombre, marca y categoría.';
        
        // Obtener la URL de la página anterior
        $previousPage = $_SERVER['HTTP_REFERER'];
        
        // Agregar el parámetro de mensaje a la URL
        $previousPageWithMessage = $previousPage . (strpos($previousPage, '?') !== false ? '&' : '?') . "show_message_error=1";
        
        // Redirigir a la página anterior con el parámetro de mensaje
        header("Location: $previousPageWithMessage");
        exit;
    }

    // Leer el archivo de imagen
    $imagen = null;
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == UPLOAD_ERR_OK) {
        $imagen = file_get_contents($_FILES['imagen']['tmp_name']);
    }

    // Preparar la consulta para insertar el artículo
    $sql = "INSERT INTO articulos (nombre, descripcion, marca_id, categoria_id, imagen, estado_activo) 
            VALUES (:nombre, :descripcion, :marca_id, :categoria_id, :imagen, 1)";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':descripcion', $descripcion);
    $stmt->bindParam(':marca_id', $marca_id);
    $stmt->bindParam(':categoria_id', $categoria_id);

    // Si hay una imagen, agregarla al parámetro de la consulta
    if ($imagen !== null) {
        $stmt->bindParam(':imagen', $imagen, PDO::PARAM_LOB);
    } else {
        // En caso de que no se suba una imagen, asignar un valor NULL
        $stmt->bindValue(':imagen', null, PDO::PARAM_LOB);
    }

    if ($stmt->execute()) {
        // Guardar en la sesión el mensaje de éxito
        session_start();
        $_SESSION['mensaje_exito'] = 'Artículo agregado correctamente';
        // Obtener la URL de la página anterior
        $previousPage = $_SERVER['HTTP_REFERER'];

        // Agregar el parámetro de mensaje a la URL
        $previousPageWithMessage = $previousPage . (strpos($previousPage, '?') !== false ? '&' : '?') . "show_message=1";

        // Redirigir a la página anterior con el parámetro de mensaje
        header("Location: $previousPageWithMessage");
        exit;
    } else {
        echo "<script>alert('Error al agregar el artículo.');</script>";
    }
}
?>
