<?php
session_start(); // Iniciar la sesión

$servername = "sql106.infinityfree.com";
$username = "if0_37317346";
$password = "Laapetitosa44";
$dbname = "if0_37317346_apetitosa";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Consulta SQL para obtener productos con marcas y categorías
    $sql = "SELECT a.id, a.imagen, a.nombre AS producto_nombre, 
                   c.nombre AS categoria_nombre, 
                   m.nombre AS marca_nombre
            FROM articulos a
            JOIN categorias c ON a.categoria_id = c.id
            JOIN marcas m ON a.marca_id = m.id
            WHERE a.estado_activo = true
            ORDER BY a.id DESC
            LIMIT 6"; // Cambia el límite según tus necesidades

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Función para convertir BLOB a base64
    function blobToBase64($blob) {
        return 'data:image/jpeg;base64,' . base64_encode($blob);
    }
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit();
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>La Apetitosa - Tienda de Snacks</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/scrollreveal"></script>
    <script src="https://kit.fontawesome.com/54b6794846.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="index_style.css">
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAD0x45tSzsvzyX8hxzk6EiGQ8hCV9smD0&libraries=places" async defer></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        #map {
            height: 400px;
            width: 100%;
            border-radius: 0.5rem;
        }
        /* Import Google font - Poppins */
        @import url("https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700&display=swap");

        .button {
        font-family: "Poppins", sans-serif;
        position: relative;
        padding: 10px 22px;
        border-radius: 6px;
        border: none;
        color: #fff;
        cursor: pointer;
        background-color: #7d2ae8;
        transition: all 0.2s ease;
        margin-top: 5px;
        }
        .button:active {
        transform: scale(0.96);
        }
        .button:before,
        .button:after {
        position: absolute;
        content: "";
        width: 150%;
        left: 50%;
        height: 100%;
        transform: translateX(-50%);
        z-index: -1000;
        background-repeat: no-repeat;
        }
        .button.animate::before {
        top: -70%;
        background-image: radial-gradient(circle, #7d2ae8 20%, transparent 20%),
            radial-gradient(circle, transparent 20%, #7d2ae8 20%, transparent 30%),
            radial-gradient(circle, #7d2ae8 20%, transparent 20%),
            radial-gradient(circle, #7d2ae8 20%, transparent 20%),
            radial-gradient(circle, transparent 10%, #7d2ae8 15%, transparent 20%),
            radial-gradient(circle, #7d2ae8 20%, transparent 20%),
            radial-gradient(circle, #7d2ae8 20%, transparent 20%),
            radial-gradient(circle, #7d2ae8 20%, transparent 20%),
            radial-gradient(circle, #7d2ae8 20%, transparent 20%);
        background-size: 10% 10%, 20% 20%, 15% 15%, 20% 20%, 18% 18%, 10% 10%, 15% 15%,
            10% 10%, 18% 18%;
        animation: greentopBubbles ease-in-out 0.6s forwards infinite;
        }
        @keyframes greentopBubbles {
        0% {
            background-position: 5% 90%, 10% 90%, 10% 90%, 15% 90%, 25% 90%, 25% 90%,
            40% 90%, 55% 90%, 70% 90%;
        }
        50% {
            background-position: 0% 80%, 0% 20%, 10% 40%, 20% 0%, 30% 30%, 22% 50%,
            50% 50%, 65% 20%, 90% 30%;
        }
        100% {
            background-position: 0% 70%, 0% 10%, 10% 30%, 20% -10%, 30% 20%, 22% 40%,
            50% 40%, 65% 10%, 90% 20%;
            background-size: 0% 0%, 0% 0%, 0% 0%, 0% 0%, 0% 0%, 0% 0%;
        }
        }
        .button.animate::after {
        bottom: -70%;
        background-image: radial-gradient(circle, #7d2ae8 20%, transparent 20%),
            radial-gradient(circle, #7d2ae8 20%, transparent 20%),
            radial-gradient(circle, transparent 10%, #7d2ae8 15%, transparent 20%),
            radial-gradient(circle, #7d2ae8 20%, transparent 20%),
            radial-gradient(circle, #7d2ae8 20%, transparent 20%),
            radial-gradient(circle, #7d2ae8 20%, transparent 20%),
            radial-gradient(circle, #7d2ae8 20%, transparent 20%);
        background-size: 15% 15%, 20% 20%, 18% 18%, 20% 20%, 15% 15%, 20% 20%, 18% 18%;
        animation: greenbottomBubbles ease-in-out 0.6s forwards infinite;
        }
        @keyframes greenbottomBubbles {
        0% {
            background-position: 10% -10%, 30% 10%, 55% -10%, 70% -10%, 85% -10%,
            70% -10%, 70% 0%;
        }
        50% {
            background-position: 0% 80%, 20% 80%, 45% 60%, 60% 100%, 75% 70%, 95% 60%,
            105% 0%;
        }
        100% {
            background-position: 0% 90%, 20% 90%, 45% 70%, 60% 110%, 75% 80%, 95% 70%,
            110% 10%;
            background-size: 0% 0%, 0% 0%, 0% 0%, 0% 0%, 0% 0%, 0% 0%;
        }
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
                    <a href="#about" class="nav-item">Nosotros</a>
                    <a href="#products" class="nav-item">Productos</a>
                    <a href="#contact" class="nav-item">Contacto</a>
                    <?php if (isset($_SESSION['id'])): ?>
                        <a href="#" id="logout-link" class="nav-item" onclick="mostrarMensajeSalida(); return false;">Cerrar Sesión</a>
                    <?php else: ?>
                        <a href="./login.php" id="login-link" class="nav-item">Iniciar Sesión</a>
                    <?php endif; ?>
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
                        <a href="#about" class="text-gray-700 hover:text-blue-500">Nosotros</a>
                        <a href="#products" class="text-gray-700 hover:text-blue-500">Productos</a>
                        <a href="#contact" class="text-gray-700 hover:text-blue-500">Contacto</a>
                        <?php if (isset($_SESSION['id'])): ?>
                            <a href="#" class="text-gray-700 hover:text-blue-500" onclick="mostrarMensajeSalida(); return false;">Cerrar Sesión</a>
                        <?php else: ?>
                            <a href="./login.php" class="text-gray-700 hover:text-blue-500">Iniciar Sesión</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="hero" class="min-h-[400px] bg-cover bg-center flex items-center justify-center" style="background-image: url('https://i.ibb.co/BKCg6ZX/Picsart-24-09-15-23-27-37-396.jpg');">
        <div class="text-center">
            <h1 class="text-5xl font-bold" style="color:#6868AC">¡Bienvenidos a La Apetitosa!</h1>
            <p class="mt-4 text-xl text-white" style="color:#6868AC">Los mejores snacks en un solo lugar</p>
            <a href="#products" class="mt-8 inline-block bg-rose-500 text-white px-6 py-3 rounded-lg shadow-lg btn">Ver Productos</a>
        </div>
    </section>

    <!-- Products Section -->
    <section id="products" class="py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-4xl font-bold text-center mb-4">Últimos Productos</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php foreach ($productos as $producto): ?>
                    <div class="product-card bg-white rounded-lg p-6 shadow-lg transition-transform duration-300 hover:shadow-xl">
                        <img src="<?php echo blobToBase64($producto['imagen']); ?>" alt="<?php echo htmlspecialchars($producto['producto_nombre']); ?>" class="mx-auto w-48 h-48 object-cover rounded-t-lg">
                        <h3 class="text-xl font-semibold mt-4 text-center"><?php echo htmlspecialchars($producto['producto_nombre']); ?></h3>
                        <p class="mt-2 text-center"><?php echo htmlspecialchars($producto['categoria_nombre']); ?> | <?php echo htmlspecialchars($producto['marca_nombre']); ?></p>
                        <div class="flex justify-center">
                        <button class="button"><i class="fas fa-heart mr-2"></i>Me encanta</button>

                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 sm:grid-cols-2 gap-8">
            <div id="map"></div>
            <div>
                <h2 class="text-4xl font-bold text-center mb-4">Sobre Nosotros</h2>
                <p class="text-lg text-center mb-8">En La Apetitosa, nos dedicamos a ofrecer los mejores snacks para ti y tu familia. Fundada en 2020, hemos crecido rápidamente para convertirnos en una de las tiendas de snacks más queridas de la región. Nuestro compromiso con la calidad y el servicio al cliente es lo que nos distingue.</p>
            </div>
        </div>
    </section>


    <!-- Contact Section -->
    <section id="contact" class="py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 sm:grid-cols-2 gap-8">
            <div>
                <h2 class="text-4xl font-bold">Contáctanos</h2>
                <p class="mt-4">¡Nos encantaría saber de ti!</p>
            </div>
            <form class="mt-8 flex flex-col align-center justify-center" id="contactForm" action="send_message.php" method="POST">
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="name">Nombre</label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" type="text" id="name" name="name" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="email">Correo Electrónico</label>
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" type="email" id="email" name="email" required>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="message">Mensaje</label>
                    <textarea class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="message" name="message" required style="resize: none;"></textarea>
                </div>
                <a class="mt-8 inline-block bg-rose-500 text-white w-1/8 ml-auto mr-auto px-6 py-3 rounded-lg shadow-lg btn cursor-pointer" id="submitBtn">Enviar</a>


            </form>
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
        <!-- Mostrar SweetAlert2 si hay un mensaje en la URL -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Verificar si hay un mensaje en la sesión
            const urlParams = new URLSearchParams(window.location.search);
            const message = "<?php echo isset($_SESSION['register_success']) ? $_SESSION['register_success'] : ''; ?>";
            const nombre = "<?php echo isset($_SESSION['nombre']) ? $_SESSION['nombre'] : ''; ?>";
            const apellido = "<?php echo isset($_SESSION['apellido']) ? $_SESSION['apellido'] : ''; ?>";

            if (message) {
                Swal.fire({
                    title: `Bienvenido, ${nombre} ${apellido}!`,
                    text: message,
                    icon: 'success',
                    confirmButtonText: 'Ok'
                }).then(() => {
                    <?php
                    // Limpiar el mensaje de la sesión después de mostrarlo
                    unset($_SESSION['register_success']);
                    unset($_SESSION['nombre']);
                    unset($_SESSION['apellido']);
                    ?>
                });
            }
        });

        function mostrarMensajeSalida() {
            let timerInterval;
            Swal.fire({
                title: "Cerrando Sesión",
                html: "Redirigiendo en <b></b> segundos...",
                timer: 1000,
                timerProgressBar: true,
                didOpen: () => {
                    Swal.showLoading();
                    timerInterval = setInterval(() => {
                        const timer = Swal.getHtmlContainer().querySelector("b");
                        if (timer) {
                            timer.textContent = Math.ceil(Swal.getTimerLeft() / 1000);
                        }
                    }, 100);
                },
                willClose: () => {
                    clearInterval(timerInterval);
                }
            }).then((result) => {
                if (result.dismiss === Swal.DismissReason.timer) {
                    // Redirige al usuario a la página de cierre de sesión
                    window.location.href = "logout2.php";
                }
            });
        }

    </script>
    <script src="https://cdn.emailjs.com/dist/email.min.js"></script>
    <script>
        // Inicializa EmailJS
        emailjs.init('bP96iwMbcvz0ROjIk');

        document.getElementById('submitBtn').addEventListener('click', function(event) {
            event.preventDefault(); // Previene el envío tradicional del formulario

            // Obtén los valores del formulario
            var name = document.getElementById('name').value;
            var email = document.getElementById('email').value;
            var message = document.getElementById('message').value;

            // Valida que todos los campos están llenos
            if (name === '' || email === '' || message === '') {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Por favor, completa todos los campos.',
                confirmButtonText: 'Entendido'
            });
            return;
            }

            // Prepara los datos para enviar
            var templateParams = {
            from_name: name,
            from_mail: email,
            message: message
            };

            // Envía el correo electrónico utilizando EmailJS
            emailjs.send('service_w1yl7ar', 'template_9dndeyk', templateParams)
            .then(function(response) {
                Swal.fire({
                icon: 'success',
                title: '¡Mensaje enviado!',
                text: 'Tu mensaje ha sido enviado con éxito.',
                confirmButtonText: 'Genial'
                });
                document.getElementById('contactForm').reset(); // Limpia el formulario
            }, function(error) {
                Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Hubo un problema al enviar el mensaje. Por favor, inténtalo de nuevo.',
                confirmButtonText: 'Reintentar'
                });
                console.error('Error al enviar el mensaje:', error);
            });
        });
    </script>

    <script>
        const buttons = document.querySelectorAll(".button");
        buttons.forEach(button => {
            button.addEventListener("click", (e) => {
                e.preventDefault();
                button.classList.add("animate");
                setTimeout(() => {
                    button.classList.remove("animate");
                }, 600);
            });
        });
    </script>
</body>
</html>
