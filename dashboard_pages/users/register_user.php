<?php
$servername = "sql106.infinityfree.com";
$username = "if0_37317346";
$password = "Laapetitosa44";
$dbname = "if0_37317346_apetitosa";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Recibir los datos enviados por POST
    $dni = $_POST['dni'];
    $firstName = $_POST['firstName'];
    $lastName = $_POST['lastName'];
    $email = $_POST['email'];
    $role = $_POST['role'];

    // Verificar si el DNI o el correo ya están registrados
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE dni = :dni OR email = :email");
    $stmt->bindParam(':dni', $dni);
    $stmt->bindParam(':email', $email);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        // Redirigir con un mensaje de error
        session_start();
        $_SESSION['mensaje_error'] = 'El DNI o el correo ya están registrados.';
        
        // Obtener la URL de la página anterior
        $previousPage = $_SERVER['HTTP_REFERER'];
        
        // Agregar el parámetro de error a la URL
        $previousPageWithError = $previousPage . (strpos($previousPage, '?') !== false ? '&' : '?') . "show_message_error=1";
        
        header("Location: $previousPageWithError");
        exit();
    }

    // Funciones para generar usuario y contraseña
    function obtenerSimboloAleatorio() {
        return rand(0, 1) === 0 ? '$' : '#';
    }

    function quitarAcentos($cadena) {
        return iconv('UTF-8', 'ASCII//TRANSLIT', $cadena);
    }

    function capitalizarPrimerNombre($nombre) {
        $nombreSinAcentos = quitarAcentos($nombre);
        $primerNombre = explode(' ', $nombreSinAcentos)[0];
        return ucfirst(strtolower($primerNombre));
    }

    $username = strtolower(substr($firstName, 0, 1)) . strtolower(substr($lastName, 0, 1)) . rand(0, 1000);
    $capitalizedFirstName = capitalizarPrimerNombre($firstName);
    $password = $capitalizedFirstName . substr($dni, 0, 1) . substr($dni, -1) . obtenerSimboloAleatorio();


    // Subir imagen si está disponible
    $imagen = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
        $imagen = file_get_contents($_FILES['image']['tmp_name']);
    }

    // Insertar usuario en la base de datos
    $stmt = $conn->prepare("INSERT INTO usuarios (usuario, dni, nombre, apellido, password, email, imagen_usuario, created_at, updated_at, rol_id, estado_activo) 
                            VALUES (:usuario, :dni, :nombre, :apellido, :password, :email, :imagen_usuario, NOW(), NOW(), :rol_id, 1)");
    $stmt->bindParam(':usuario', $username);
    $stmt->bindParam(':dni', $dni);
    $stmt->bindParam(':nombre', $firstName);
    $stmt->bindParam(':apellido', $lastName);
    $stmt->bindParam(':password', $password);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':rol_id', $role);
    if ($imagen !== null) {
        $stmt->bindParam(':imagen_usuario', $imagen, PDO::PARAM_LOB);
    } else {
        $stmt->bindValue(':imagen_usuario', null, PDO::PARAM_LOB);
    }

    if ($stmt->execute()) {
        // Enviar correo con EmailJS
        $emailjsServiceID = 'service_w1yl7ar'; // Cambia por tu ID de servicio
        $emailjsTemplateID = 'template_8scsfp6'; // Cambia por tu ID de plantilla
        $emailjsPublicKey = 'bP96iwMbcvz0ROjIk'; // Cambia por tu clave pública

        $emailjsEndpoint = 'https://api.emailjs.com/api/v1.0/email/send';
        $emailjsData = [
            'service_id' => $emailjsServiceID,
            'template_id' => $emailjsTemplateID,
            'user_id' => $emailjsPublicKey,
            'template_params' => [
                'sendername' => 'Sistema de Manejo de Inventarios',
                'to' => $email,
                'subject' => 'Usuario y Contraseña de Acceso',
                'replyto' => 'francomoises11@gmail.com',
                'message' => "Estos son sus datos de acceso para el Sistema de Manejo de inventarios. No comparta estos datos con nadie.\nUsuario: $username\nContraseña: $password",
            ],
        ];

        $ch = curl_init($emailjsEndpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($emailjsData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Origin: http://http://laapetitosa.html-5.me/dashboard.php', // Cambia por tu dominio
        ]);

        $result = curl_exec($ch);
        curl_close($ch);

        if ($result === false) {
            session_start();
            $_SESSION['mensaje_error'] = 'Error al enviar el correo.';
        } else {
            session_start();
            $_SESSION['mensaje_exito'] = 'Usuario registrado correctamente.';
        }

        // Redirigir con éxito o error
        $previousPage = $_SERVER['HTTP_REFERER'];
        $showMessageParam = isset($_SESSION['mensaje_error']) ? 'show_message_error=1' : 'show_message=1';
        $previousPageWithMessage = $previousPage . (strpos($previousPage, '?') !== false ? '&' : '?') . $showMessageParam;
        header("Location: $previousPageWithMessage");
        exit();
    } else {
        session_start();
        $_SESSION['mensaje_error'] = 'Error, usuario o correo ya existen';
        $previousPage = $_SERVER['HTTP_REFERER'];
        $previousPageWithMessage = $previousPage . (strpos($previousPage, '?') !== false ? '&' : '?') . "show_message_error=1";
        header("Location: $previousPageWithMessage");
    }
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit();
}
?>
