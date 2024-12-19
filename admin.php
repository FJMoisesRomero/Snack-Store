<?php
session_start();
$error_message = "";

// Verificar si el usuario ya ha iniciado sesión
if (isset($_SESSION['user'])) {
    header("Location: ./dashboard.php");
    exit();
}

if ($_POST) {
    include('./db_connection.php');

    $usuario = $_POST['usuario'];
    $password = $_POST['password'];

    // Prepared statement to prevent SQL injection
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE usuario = :usuario");
    $stmt->bindParam(':usuario', $usuario);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verificación de la contraseña como strings
    if ($user && $password === $user['password']) {
        $_SESSION['user'] = $user;
        $_SESSION['mostrar_bienvenida'] = true;
        header("Location: ./dashboard.php");
        exit();
    } else {
        $error_message = "Usuario o contraseña incorrectos";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>SMI Login - Sistema de Manejo de Inventarios</title>
    <script src="https://kit.fontawesome.com/54b6794846.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <script src="https://unpkg.com/scrollreveal"></script>
    <style>
        body {
            background: url('https://i.ibb.co/sw6LRTK/login-Background.jpg') no-repeat center center fixed;
            background-size: cover;
        }

        #section {
            margin: 100px auto;
        }

        body.swal2-shown:not(.swal2-no-backdrop):not(.swal2-toast-shown) {
        height: 100vh !important;
        }
    </style>
</head>

<body class="flex justify-center items-center h-screen m-0">
    <div id="section" class="flex w-4/5 max-w-3xl bg-white bg-opacity-90 rounded-lg shadow-lg overflow-hidden flex-col-reverse md:flex-row">
        <div class="w-full md:w-1/2 bg-gradient-to-br from-blue-500 to-blue-300 text-white p-10 flex flex-col items-center">
            <h1 class="text-3xl font-bold">SMI</h1>
            <p class="mt-4">Sistema de Manejo de Inventarios</p>
            <p class="mt-2">Gestiona tu inventario de manera eficiente y eficaz con nuestra solución integral.</p>
            <a href="https://github.com/FJMoisesRomero" target="_blank">
                <button class="mt-4 px-6 py-2 rounded-full bg-white text-blue-500 hover:bg-gray-200">Saber Más</button>
            </a>
            <img src="https://i.ibb.co/C9N4dFy/login-Image1.png" alt="Information Image" class="mt-6 max-w-full rounded-lg hidden md:block">
        </div>
        <div class="w-full md:w-1/2 p-10 flex flex-col justify-center">
            <h2 class="text-2xl text-gray-800 text-center">Acceder</h2>
            <p class="text-gray-600 text-center">Inicia sesión para acceder a tu cuenta.</p>
            <form action="./admin.php" method="POST" class="flex flex-col items-center">
                <div class="w-full mb-4">
                    <label for="usuario" class="block mb-1 font-bold text-gray-700">Usuario</label>
                    <input type="text" name="usuario" id="usuario" required class="w-full p-2 border border-gray-300 rounded-md">
                </div>
                <div class="w-full mb-4 relative">
                    <label for="password" class="block mb-1 font-bold text-gray-700">Contraseña</label>
                    <input type="password" name="password" id="password" required class="w-full p-2 border border-gray-300 rounded-md">
                    <i class="fas fa-eye-slash absolute right-3 top-10 cursor-pointer" id="togglePassword" onclick="togglePasswordVisibility()"></i>
                </div>
                <button type="submit" class="w-full px-4 py-2 rounded-md bg-gradient-to-br from-blue-500 to-blue-300 text-white hover:from-blue-600 hover:to-blue-400">Loguearse</button>
                <p class="mt-4 text-gray-600 text-center">¿Olvidaste tu Contraseña? <a href="https://github.com/FJMoisesRomero" target="_blank" class="text-blue-500 hover:underline">Ponte en contacto con un Administrador</a></p>
            </form>
        </div>
    </div>

<script>
    function togglePasswordVisibility() {
        const passwordInput = document.getElementById('password');
        const passwordIcon = document.getElementById('togglePassword');
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            passwordIcon.classList.remove('fa-eye-slash');
            passwordIcon.classList.add('fa-eye');
        } else {
            passwordInput.type = 'password';
            passwordIcon.classList.remove('fa-eye');
            passwordIcon.classList.add('fa-eye-slash');
        }
    }
</script>
<script>
    <?php if ($error_message): ?>
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: '<?php echo $error_message; ?>',
            confirmButtonText: 'Aceptar'
        });
    <?php endif; ?>

    ScrollReveal().reveal('#section', { 
    duration: 1000, 
    origin: 'bottom', 
    distance: '50px',
    });

</script>
</body>

</html>
