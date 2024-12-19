<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion de Usuarios</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="../User_Registration/user_registration.css">
    <link rel="stylesheet" href="../css/adminlte.min.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <script src="https://kit.fontawesome.com/54b6794846.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/@emailjs/browser@3/dist/email.min.js"></script>
        <script type="text/javascript">
        (function(){
            emailjs.init("bP96iwMbcvz0ROjIk");
        })();
        </script>

    <script src="https://kit.fontawesome.com/54b6794846.js" crossorigin="anonymous"></script>
</head>
<body class="hold-transition sidebar-mini">

<div class="content-wrapper">
    <div class="body">
        <div id="success-message" class="success-container" style="display: none;">
            <p>Usuario registrado con éxito.</p>
            <span class="close-icon" onclick="closeSuccessMessage()">
                <i class="fa-solid fa-xmark"></i>
            </span>
        </div>
        <div id="info-container" class="info-container">
            <i class="fa-solid fa-circle-info"></i>
            <p>Recuerde que para agregar un usuario al sistema todos los campos son obligatorios y deben ser completados, asegúrese de corroborar la información así como también respetar los caracteres permitidos en los diferentes campos.</p>
            <p>Asegúrese de que el DNI ingresado sea válido y único. El nombre y apellido deben coincidir con los documentos oficiales. El email debe ser uno válido y activo para poder recibir su <span>Usuario</span> y <span>Contraseña</span> de Acceso.</p>
            <img src="../User_Registration/user_registration_img.png" alt="User Registration Info">
        </div>
        <div id="main-container" class="container">
            <div>
                <h1 class="ur_h1">Agregar Usuario al Sistema</h1>
            </div>
            <div class="main-container">
                <div class="form-container">
                    <form action="#" method="post" id="registration-form">
                        <label for="dni">DNI</label>
                        <input type="text" id="dni" name="dni" placeholder="Ingrese el DNI" required>
                        
                        <label for="first-name">Nombre</label>
                        <input type="text" id="first-name" name="first-name" placeholder="Ingrese el Nombre" required>
                        
                        <label for="last-name">Apellido</label>
                        <input type="text" id="last-name" name="last-name" placeholder="Ingrese el Apellido" required>
                        
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" placeholder="Ingrese el Email" required>
                        <!-- Selector de Rol -->
                        <label for="role">Rol</label><br>
                        <select id="role" name="role" required>
    <?php
    $servername = "sql106.infinityfree.com";
    $username = "if0_37317346";
    $password = "Laapetitosa44";
    $dbname = "if0_37317346_apetitosa";
    
    try {
        // Crear una nueva conexión PDO
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        // Establecer el modo de error de PDO a excepción
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Consulta para obtener roles
        $sql = "SELECT id, rol FROM roles";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        
        // Obtener los resultados
        $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($roles) > 0) {
            foreach ($roles as $row) {
                echo "<option value='" . htmlspecialchars($row['id']) . "'>" . htmlspecialchars($row['rol']) . "</option>";
            }
        } else {
            echo "<option value=''>No hay roles disponibles</option>";
        }
        
    } catch (PDOException $e) {
        // Mostrar mensaje de error en caso de falla
        echo "Error: " . $e->getMessage();
    }
    
    // Cerrar la conexión
    $conn = null;
    ?>
</select>

                    </form>
                </div>
                <div class="profile-container">
                    <h2>Imagen de Perfil</h2>
                    <div class="profile-placeholder">
                        <img id="profile-img" src="https://placehold.jp/150x150.png" alt="Profile Image">
                    </div>
                    <input type="file" id="upload-img" accept="image/*" style="display: none;">
                    <button id="upload-btn"><i class="fa-solid fa-pencil"></i></button>
                    
                    <button class="register-btn" type="submit">Registrar Usuario</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="../User_Registration/user_registration.js"></script>
</body>
</html>
