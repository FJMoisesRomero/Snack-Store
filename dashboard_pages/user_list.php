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

// Verificar si se ha enviado una solicitud para "eliminar" un usuario
if (isset($_GET['delete'])) {
    $dni = $_GET['delete'];

    // Preparar la consulta para cambiar el estado_activo a false
    $stmt = $conn->prepare("UPDATE usuarios SET estado_activo = false WHERE dni = :dni");
    $stmt->bindParam(':dni', $dni);

    if ($stmt->execute()) {
        // Guardar en la sesión el mensaje de éxito
        session_start();
        $_SESSION['mensaje_exito'] = 'Usuario desactivado correctamente';

        // Obtener la URL de la página anterior
        $previousPage = $_SERVER['HTTP_REFERER'];

        // Agregar el parámetro de mensaje a la URL
        $previousPageWithMessage = $previousPage . (strpos($previousPage, '?') !== false ? '&' : '?') . "show_message=1";

        // Redirigir a la página anterior con el parámetro de mensaje
        header("Location: $previousPageWithMessage");
        exit;
    } else {
        echo "<script>alert('Error al desactivar el usuario.');</script>";
    }
}


// Consultar los usuarios
$stmt = $conn->prepare("SELECT dni, nombre, apellido, email, usuario FROM usuarios");
$stmt->execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch roles from the database
$stmt = $conn->prepare("SELECT id, rol FROM roles");
$stmt->execute();
$roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Consultar los usuarios con el rol y estado activo
$stmt = $conn->prepare("
    SELECT u.dni, u.nombre, u.apellido, u.email, u.usuario, r.rol
    FROM usuarios u
    LEFT JOIN roles r ON u.rol_id = r.id
    WHERE u.estado_activo = true
");
$stmt->execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.7.4/sweetalert2.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.7.4/sweetalert2.all.min.js"></script>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/user_list.css">
</head>
<body class="hold-transition sidebar-mini">

<div class="content-wrapper">
    <div class="container-fluid mt-3 align-items-center justify-content-center" id="mainContainer">
        <h2>Lista de Usuarios</h2>
        <!-- Button to trigger the registration modal -->
        <button class="btn btn-success" data-toggle="modal" data-target="#registerUserModal">
            <i class="fas fa-user-plus"></i> Agregar Usuario
        </button>
        <div id="table-wrapper">
            <table id="Table" class="table table-striped">
                <thead>
                    <tr>
                        <th>Rol</th>
                        <th>DNI</th>
                        <th>Nombre</th>
                        <th>Apellido</th>
                        <th>Email</th>
                        <th>Usuario</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="usersTableBody">
                    <?php foreach ($usuarios as $usuario): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($usuario['rol'] ?? 'Sin rol'); ?></td>
                        <td><?php echo htmlspecialchars($usuario['dni']); ?></td>
                        <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($usuario['apellido']); ?></td>
                        <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                        <td><?php echo htmlspecialchars($usuario['usuario']); ?></td>
                        <td>
                            <button class="btn btn-info btn-sm" data-toggle="tooltip" title="Ver detalles" onclick="viewUserDetails('<?php echo htmlspecialchars($usuario['dni']); ?>')">
                                <i class="fas fa-eye"></i>
                            </button>
                            <a href="#" class="btn btn-warning btn-sm" data-toggle="tooltip" title="Editar" onclick="editUser('<?php echo htmlspecialchars($usuario['dni']); ?>', '<?php echo htmlspecialchars($usuario['nombre']); ?>', '<?php echo htmlspecialchars($usuario['apellido']); ?>', '<?php echo htmlspecialchars($usuario['email']); ?>', '<?php echo htmlspecialchars($usuario['usuario']); ?>')">
                                <i class="fas fa-edit"></i>
                            </a>
                            <button class="btn btn-danger btn-sm" data-toggle="tooltip" title="Eliminar" onclick="confirmDelete('<?php echo htmlspecialchars($usuario['dni']); ?>', '<?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']); ?>')">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th>Rol</th>
                        <th>DNI</th>
                        <th>Nombre</th>
                        <th>Apellido</th>
                        <th>Email</th>
                        <th>Usuario</th>
                        <th>Acciones</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- Modal para ver detalles del usuario -->
<div class="modal fade" id="userDetailsModal" tabindex="-1" role="dialog" aria-labelledby="userDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userDetailsModalLabel">Detalles del Usuario</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Aquí se cargará el contenido del usuario -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para editar detalles del usuario -->
<div class="modal fade" id="editUserModal" tabindex="-1" role="dialog" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel">Editar Usuario</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editUserForm" action="../dashboard_pages/users/update_user.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="dni" id="editUserDni">
                    <div class="form-group">
                        <label for="editUserName">Nombre</label>
                        <input type="text" class="form-control" id="editUserName" name="nombre" required>
                    </div>
                    <div class="form-group">
                        <label for="editUserLastName">Apellido</label>
                        <input type="text" class="form-control" id="editUserLastName" name="apellido" required>
                    </div>
                    <div class="form-group">
                        <label for="editUserEmail">Email</label>
                        <input type="email" class="form-control" id="editUserEmail" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="editUserUsername">Usuario</label>
                        <input type="text" class="form-control" id="editUserUsername" name="usuario" required>
                    </div>
                    <div class="form-group">
                        <label for="editUserRole">Rol</label>
                        <select class="form-control" id="editUserRole" name="rol_id" required>
                            <!-- Roles will be populated here -->
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="editUserImage">Imagen</label>
                        <input type="file" class="form-control-file" id="editUserImage" name="imagen_usuario">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para registrar un nuevo usuario -->
<div class="modal fade" id="registerUserModal" tabindex="-1" role="dialog" aria-labelledby="registerUserModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="registerUserModalLabel">Registrar Nuevo Usuario</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="registerUserForm" action="../dashboard_pages/users/register_user.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="registerUserDni">DNI</label>
                        <input type="text" class="form-control" id="registerUserDni" name="dni" required>
                    </div>
                    <div class="form-group">
                        <label for="registerUserFirstName">Nombre</label>
                        <input type="text" class="form-control" id="registerUserFirstName" name="firstName" required>
                    </div>
                    <div class="form-group">
                        <label for="registerUserLastName">Apellido</label>
                        <input type="text" class="form-control" id="registerUserLastName" name="lastName" required>
                    </div>
                    <div class="form-group">
                        <label for="registerUserEmail">Email</label>
                        <input type="email" class="form-control" id="registerUserEmail" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="registerUserImage">Imagen</label>
                        <input type="file" class="form-control-file" id="registerUserImage" name="image">
                    </div>
                    <div class="form-group">
                        <label for="registerUserRole">Rol</label>
                        <select class="form-control" id="registerUserRole" name="role" required>
                            <?php foreach ($roles as $rol): ?>
                                <option value="<?php echo htmlspecialchars($rol['id']); ?>">
                                    <?php echo htmlspecialchars($rol['rol']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Registrar Usuario</button>
                </div>
            </form>
        </div>
    </div>
</div>



<script src="../scripts/user_list.js"></script>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

<script>
    function viewUserDetails(dni) {
        fetchUserDetails(dni);
    }

    function editUser(dni, nombre, apellido, email, usuario) {
        const roles = <?php echo json_encode($roles); ?>;
        const roleSelect = document.getElementById('editUserRole');
        roleSelect.innerHTML = ''; // Clear existing options

        roles.forEach(role => {
            const option = document.createElement('option');
            option.value = role.id;
            option.textContent = role.rol;
            roleSelect.appendChild(option);
        });

        document.getElementById('editUserDni').value = dni;
        document.getElementById('editUserName').value = nombre;
        document.getElementById('editUserLastName').value = apellido;
        document.getElementById('editUserEmail').value = email;
        document.getElementById('editUserUsername').value = usuario;

        $('#editUserModal').modal('show');
    }

    function confirmDelete(dni, nombreCompleto) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: `¿Quieres eliminar a ${nombreCompleto}?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `dashboard_pages/user_list.php?delete=${dni}`;
            }
        });
    }

    function viewUserDetails(dni) {
    fetch(`../dashboard_pages/users/view_user.php?id=${encodeURIComponent(dni)}`)
        .then(response => response.text())
        .then(html => {
            // Insertar el HTML recibido en el modal
            document.querySelector('#userDetailsModal .modal-body').innerHTML = html;
            // Show the modal
            $('#userDetailsModal').modal('show');
        })
        .catch(error => {
            Swal.fire({
                title: 'Error',
                text: 'No se pudo cargar los detalles del usuario.',
                icon: 'error'
            });
        });
}

// Mostrar mensaje de éxito si se ha establecido en la sesión
document.addEventListener('DOMContentLoaded', function() {
    <?php if (isset($_SESSION['mensaje_exito']) && isset($_GET['show_message']) && $_GET['show_message'] == '1'): ?>
        Swal.fire({
            title: 'Completado',
            text: '<?= htmlspecialchars($_SESSION['mensaje_exito']) ?>',
            icon: 'success',
            timer: 1000, // El mensaje se mostrará por 1 segundo
            timerProgressBar: true,
        });
        <?php unset($_SESSION['mensaje_exito']); // Elimina la variable después de mostrar el mensaje ?>
    <?php endif; ?>
});

// Mostrar mensaje de Error
document.addEventListener('DOMContentLoaded', function() {
    <?php if (isset($_SESSION['mensaje_error']) && isset($_GET['show_message_error']) && $_GET['show_message_error'] == '1'): ?>
        Swal.fire({
            title: 'Error',
            text: '<?= htmlspecialchars($_SESSION['mensaje_error']) ?>',
            icon: 'error',
            timer: 1000, // El mensaje se mostrará por 1 segundo
            timerProgressBar: true,
        });
        <?php unset($_SESSION['mensaje_error']); // Elimina la variable después de mostrar el mensaje ?>
    <?php endif; ?>
});
</script>
</body>
</html>
