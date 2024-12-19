<?php
session_start(); // Iniciar la sesión

$servername = "sql106.infinityfree.com";
$username = "if0_37317346";
$password = "Laapetitosa44";
$dbname = "if0_37317346_apetitosa";

// Inicializar una variable para los mensajes de error o éxito
$message = "";

// Verificar si el formulario ha sido enviado
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Crear conexión
    $conn = new mysqli($servername, $username, $password, $dbname);

    // Verificar conexión
    if ($conn->connect_error) {
        die("Conexión fallida: " . $conn->connect_error);
    }

    // Registro de nuevo usuario
    if (isset($_POST['signup-username'])) {
        // Obtener datos del formulario de registro
        $nombre = $_POST['signup-username'];
        $apellido = $_POST['signup-lastname'];
        $email = $_POST['signup-email'];
        $numero_telefono = $_POST['signup-phone'];
        $password = password_hash($_POST['signup-password'], PASSWORD_BCRYPT); // Encriptar la contraseña

        // Verificar si el email ya existe
        $sql = "SELECT id FROM clientes WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows == 0) {
            // Email no existe, proceder a insertar
            $sql = "INSERT INTO clientes (nombre, apellido, email, numero_telefono, password) VALUES (?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssss", $nombre, $apellido, $email, $numero_telefono, $password);
            
            if ($stmt->execute()) {
                // Establecer sesión de éxito
                $_SESSION['register_success'] = "Registro exitoso";
                $_SESSION['nombre'] = $nombre;
                $_SESSION['apellido'] = $apellido;
                header("Location: ./index.php");
                exit();
            } else {
                $message = "Error: " . $stmt->error;
            }
        } else {
            $message = "El correo electrónico ya está registrado";
        }

        $stmt->close();
    }

    // Inicio de sesión
    if (isset($_POST['login-email'])) {
        // Obtener datos del formulario de inicio de sesión
        $login_email = $_POST['login-email'];
        $login_password = $_POST['login-password'];

        // Verificar las credenciales del usuario
        $sql = "SELECT id, nombre, apellido, password FROM clientes WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $login_email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Obtener los datos del usuario
            $stmt->bind_result($id, $nombre, $apellido, $hashed_password);
            $stmt->fetch();

            // Verificar la contraseña
            if (password_verify($login_password, $hashed_password)) {
                // Establecer sesión
                $_SESSION['register_success'] = "Sesión iniciada";
                $_SESSION['id'] = $id;
                $_SESSION['nombre'] = $nombre;
                $_SESSION['apellido'] = $apellido;
                header("Location: ./index.php");
                exit();
            } else {
                $message = "Contraseña incorrecta";
            }
        } else {
            $message = "Correo electrónico no registrado";
        }

        $stmt->close();
    }

    $conn->close();

    // Redirigir a la misma página con el mensaje de error
    if ($message) {
        $_SESSION['message'] = $message;
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}
?>



<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>La Apetitosa - Iniciar Sesión</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/scrollreveal"></script>
    <script src="https://kit.fontawesome.com/54b6794846.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="index_style.css">
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAD0x45tSzsvzyX8hxzk6EiGQ8hCV9smD0&libraries=places" async defer></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <style>
        #map {
            height: 400px;
            width: 100%;
            border-radius: 0.5rem;
        }
        .container-main{
            width: 350px;
            height: 500px;
            background: red;
            overflow: hidden;
            background: url("https://doc-08-2c-docs.googleusercontent.com/docs/securesc/68c90smiglihng9534mvqmq1946dmis5/fo0picsp1nhiucmc0l25s29respgpr4j/1631524275000/03522360960922298374/03522360960922298374/1Sx0jhdpEpnNIydS4rnN4kHSJtU1EyWka?e=view&authuser=0&nonce=gcrocepgbb17m&user=03522360960922298374&hash=tfhgbs86ka6divo3llbvp93mg4csvb38") no-repeat center/ cover;
            border-radius: 10px;
            box-shadow: 2px 5px 20px #FF80BF;
            background: linear-gradient(to bottom, #FFD662, #FF80BF);
        }
        #toggle-auth{
            display: none;
        }
        .signup-section{
            position: relative;
            width:100%;
            height: 100%;
        }
        .container-main label{
            color: #fff;
            font-size: 2.3em;
            justify-content: center;
            display: flex;
            margin: 50px;
            font-weight: bold;
            cursor: pointer;
            transition: .5s ease-in-out;
        }

        .container-main input{
            width: 60%;
            height: 10px;
            background: #e0dede;
            justify-content: center;
            display: flex;
            margin: 15px auto;
            padding: 12px;
            border: none;
            outline: none;
            border-radius: 5px;
        }

        .container-main button{
            width: 60%;
            height: 40px;
            margin: 10px auto;
            justify-content: center;
            display: block;
            color: #fff;
            background: #573b8a;
            font-size: 1em;
            font-weight: bold;
            margin-top: 30px;
            outline: none;
            border: none;
            border-radius: 5px;
            transition: .2s ease-in;
            cursor: pointer;
        }

        .container-main button:hover{
            background: #6d44b8;
        }

        .login-section{
            height: 460px;
            background: #eee;
            border-radius: 60% / 10%;
            transform: translateY(-180px);
            transition: .8s ease-in-out;
        }
        .login-section label{
            color: #573b8a;
            transform: scale(.6);
        }

        #toggle-auth:checked ~ .login-section{
            transform: translateY(-500px);
        }
        #toggle-auth:checked ~ .login-section label{
            transform: scale(1);	
        }
        #toggle-auth:checked ~ .signup-section label{
            transform: scale(.6);
        }

        .iti{
            margin-left: 70px;
            width: 350px;
        }
        .password-wrapper {
            position: relative;
            width: 100%;
            margin: 15px auto;
        }

        .eye-icon {
            position: absolute;
            right: 80px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 1.2em; /* Adjust size as needed */
            color: #573b8a; /* Change color as needed */
        }


    </style>
</head>
<body class="bg-white text-gray-800 transition duration-500 ease-in-out">

    <!-- Navbar -->
    <nav id="navbar" class="sticky top-0 shadow-lg z-50 transition duration-500 ease-in-out">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <div class="flex items-center">
                    <a id="logo" href="#hero" class="text-3xl font-bold" style="margin-top: -15px">La Apetitosa</a>
                </div>
                <!-- Button for small screens -->
                <div class="block lg:hidden">
                    <button id="menuButton" class="text-gray-700 focus:outline-none">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                        </svg>
                    </button>
                </div>
                <!-- Menu items -->
                <div class="hidden lg:flex space-x-4">
                    <a href="./index.php" class="nav-item">Página Principal</a>
                </div>
                <!-- Mobile menu -->
                <div id="mobileMenu" class="lg:hidden fixed inset-0 bg-white z-40 transform -translate-x-full transition-transform duration-300">
                    <div class="flex justify-end p-4">
                        <button id="closeMenuButton" class="text-gray-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="flex flex-col items-center space-y-4 mt-10">
                        <a href="./index.php" class="text-gray-700 hover:text-blue-500">Página Principal</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="hero" class="min-h-[400px] bg-cover bg-center flex flex-col-reverse md:flex-row items-center justify-center">
        <div class="h-full w-full md:w-1/2 flex flex-col justify-center bg-white rounded-lg p-12 my-8 mx-4 md:my-0 md:mx-0" style="height: 400px; box-shadow: 5px 20px 50px #000;">
            <div style="color: #573b8a">
                <h2 class="text-4xl font-bold">Inicia Sesión o Regístrate</h2>
                <p class="mt-4">En La Apetitosa nos enfocamos en brindarte los mejores precios y descuentos en snacks. ¡Inicia sesión o regístrate para recibir información exclusiva sobre nuestras promociones!</p>
            </div>
        </div>

        <div class="container-main">  
            <input type="checkbox" id="toggle-auth" aria-hidden="true">
            <div class="signup-section">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                    <label for="toggle-auth" aria-hidden="true">Registrarse</label>
                    <input type="text" name="signup-username" placeholder="Nombre" required>
                    <input type="text" name="signup-lastname" placeholder="Apellido" required>
                    <input type="email" name="signup-email" placeholder="Email" required>
                    <input id="signup-phone" type="tel" name="signup-phone" placeholder="Número de Teléfono" required>
                    <div class="password-wrapper">
                        <input type="password" name="signup-password" placeholder="Contraseña" required>
                        <span class="eye-icon" onclick="togglePassword('signup-password', this)">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                    <button type="submit">Registrarse</button>
                </form>
            </div>

            <div class="login-section" style="z-index: 9; position: relative;">
                <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="POST">
                    <label for="toggle-auth" aria-hidden="true">Iniciar Sesión</label>
                    <input type="email" name="login-email" placeholder="Email" required>
                    <div class="password-wrapper">
                        <input type="password" name="login-password" placeholder="Contraseña" required>
                        <span class="eye-icon" onclick="togglePassword('login-password', this)">
                            <i class="fas fa-eye"></i>
                        </span>
                    </div>
                    <button type="submit">Iniciar Sesión</button>
                </form>
            </div>
        </div>
    </section>


    <!-- Footer -->
    <footer class="bg-[#6868AC] text-[#FFD662] py-4">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center">
            <p>&copy; 2024 La Apetitosa</p>
            <a href="#hero" class="text-[#FFD662] hover:underline"><i class="fa-solid fa-arrow-up text-4xl"></i></a>
        </div>
    </footer>

    <script>
        // Smooth scroll for main navigation
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                document.querySelectorAll('.nav-item').forEach(i => i.classList.remove('active'));
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });
            });
        });

        // Smooth scroll for mobile menu
        document.querySelectorAll('#mobileMenu a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                document.querySelectorAll('.nav-item').forEach(i => i.classList.remove('active'));
                e.preventDefault();
                document.querySelector(this.getAttribute('href')).scrollIntoView({
                    behavior: 'smooth'
                });

                // Close the mobile menu
                document.getElementById('mobileMenu').style.transform = 'translateX(-100%)';
            });
        });

        document.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', function() {
                document.querySelectorAll('.nav-item').forEach(i => i.classList.remove('active'));
                this.classList.add('active');
            });
        });

        document.addEventListener('DOMContentLoaded', () => {
            const menuButton = document.getElementById('menuButton');
            const closeMenuButton = document.getElementById('closeMenuButton');
            const mobileMenu = document.getElementById('mobileMenu');

            menuButton.addEventListener('click', () => {
                mobileMenu.style.transform = 'translateX(0)';
            });

            closeMenuButton.addEventListener('click', () => {
                mobileMenu.style.transform = 'translateX(-100%)';
            });
        });
        document.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', function() {
                document.querySelectorAll('.nav-item').forEach(i => i.classList.remove('active'));
                this.classList.add('active');
            });
        });

        document.addEventListener('DOMContentLoaded', () => {
            const menuButton = document.getElementById('menuButton');
            const closeMenuButton = document.getElementById('closeMenuButton');
            const mobileMenu = document.getElementById('mobileMenu');

            menuButton.addEventListener('click', () => {
                mobileMenu.style.transform = 'translateX(0)';
            });

            closeMenuButton.addEventListener('click', () => {
                mobileMenu.style.transform = 'translateX(-100%)';
            });
        });

        // ScrollReveal Animations
        ScrollReveal().reveal('#hero', { duration: 1000, origin: 'bottom', distance: '50px' });
        ScrollReveal().reveal('#about', { duration: 1000, origin: 'left', distance: '50px' });
        ScrollReveal().reveal('#products', { duration: 1000, origin: 'right', distance: '50px' });
        ScrollReveal().reveal('#contact', { duration: 1000, origin: 'top', distance: '50px' });

    </script>
    <script>
        function initMap() {
            const location = { lat: -24.7820, lng: -65.4232 }; // Replace with your location coordinates
            const map = new google.maps.Map(document.getElementById("map"), {
                zoom: 12,
                center: location,
            });
            new google.maps.Marker({
                position: location,
                map: map,
                title: "Nuestra Ubicación",
            });
        }

        // Initialize the map when the Google Maps API is loaded
        window.onload = initMap;
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var input = document.querySelector('#signup-phone');
            window.intlTelInput(input, {
                // Opcional: puedes agregar opciones de configuración aquí
                initialCountry: "auto",
                geoIpLookup: function(callback) {
                    fetch('https://ipinfo.io/json?token=YOUR_ACCESS_TOKEN') // Obtén un token de ipinfo.io
                        .then(response => response.json())
                        .then(ipjson => callback(ipjson.country))
                        .catch(() => callback('US')); // En caso de error, usa 'US' como país por defecto
                },
                utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.min.js" // Utilidades opcionales
            });
        });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Check if there's a message set in session
            <?php if (isset($_SESSION['message'])): ?>
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: '<?php echo $_SESSION['message']; ?>',
                    confirmButtonText: 'Aceptar'
                }).then(function() {
                    // Clear the message after showing
                    <?php unset($_SESSION['message']); ?>
                });
            <?php endif; ?>
        });

        function togglePassword(inputId, icon) {
            const input = document.querySelector(`input[name="${inputId}"]`);
            const eyeIcon = icon.querySelector('i');
            
            if (input.type === "password") {
                input.type = "text";
                eyeIcon.classList.remove('fa-eye');
                eyeIcon.classList.add('fa-eye-slash');
            } else {
                input.type = "password";
                eyeIcon.classList.remove('fa-eye-slash');
                eyeIcon.classList.add('fa-eye');
            }
        }


    </script>

</body>
</html>
