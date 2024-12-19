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

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $rol = $_POST['rol'];

    // Obtener el siguiente ID disponible
    $stmt = $conn->prepare("SELECT MIN(id) AS next_id FROM roles WHERE id NOT IN (SELECT id FROM roles)");
    $stmt->execute();
    $next_id = $stmt->fetch(PDO::FETCH_ASSOC)['next_id'];

    // Si no hay IDs disponibles, usar el máximo ID actual + 1
    if ($next_id === null) {
        $stmt = $conn->prepare("SELECT MAX(id) AS max_id FROM roles");
        $stmt->execute();
        $max_id = $stmt->fetch(PDO::FETCH_ASSOC)['max_id'];
        $next_id = $max_id + 1;
    }

    // Preparar la consulta para insertar el nuevo rol
    $stmt = $conn->prepare("INSERT INTO roles (id, rol, created_at, updated_at) VALUES (:id, :rol, NOW(), NOW())");
    $stmt->bindParam(':id', $next_id);
    $stmt->bindParam(':rol', $rol);

    if ($stmt->execute()) {
        // Guardar en la sesión el mensaje de éxito
        session_start();
        $_SESSION['mensaje_exito'] = 'Rol agregado correctamente';
        // Obtener la URL de la página anterior
        $previousPage = $_SERVER['HTTP_REFERER'];
        // Agregar el parámetro de mensaje a la URL
        $previousPageWithMessage = $previousPage . (strpos($previousPage, '?') !== false ? '&' : '?') . "show_message=1";
        // Redirigir a la página anterior con el parámetro de mensaje
        header("Location: $previousPageWithMessage");
        exit;
    } else {
        echo "<script>alert('Error al agregar el rol.');</script>";
    }
}

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    // Preparar la consulta para eliminar el rol
    $stmt = $conn->prepare("DELETE FROM roles WHERE id = :id");
    $stmt->bindParam(':id', $id);

    if ($stmt->execute()) {
        // Guardar en la sesión el mensaje de éxito
        session_start();
        $_SESSION['mensaje_exito'] = 'Rol eliminado correctamente';
        // Obtener la URL de la página anterior
        $previousPage = $_SERVER['HTTP_REFERER'];
        // Agregar el parámetro de mensaje a la URL
        $previousPageWithMessage = $previousPage . (strpos($previousPage, '?') !== false ? '&' : '?') . "show_message=1";
        // Redirigir a la página anterior con el parámetro de mensaje
        header("Location: $previousPageWithMessage");
        exit;
    } else {
        echo "<script>alert('Error al eliminar el rol.');</script>";
    }
}

// Consultar los roles
$stmt = $conn->prepare("SELECT id, rol, created_at, updated_at FROM roles");
$stmt->execute();
$roles = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Roles</title>

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

<div class="content-wrapper" style="height: auto">
    <div class="container-fluid mt-3 align-items-center justify-content-center" id="mainContainer">
        <h2>Lista de Roles</h2>

        <!-- Botón para agregar rol -->
        <button class="btn btn-primary mb-3" onclick="showAddRoleModal()"><i class="fa-solid fa-plus" style="margin-right:10px"></i>Agregar Rol</button>
        <div id="table-wrapper">
            <table id="Table" class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Rol</th>
                        <th>Fecha de Creación</th>
                        <th>Fecha de Actualización</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="rolesTableBody">
                    <?php foreach ($roles as $role): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($role['id']); ?></td>
                        <td><?php echo htmlspecialchars($role['rol']); ?></td>
                        <td><?php echo htmlspecialchars($role['created_at']); ?></td>
                        <td><?php echo htmlspecialchars($role['updated_at']); ?></td>
                        <td>                            
                            <button class="btn btn-warning btn-sm" data-toggle="tooltip" title="Editar" onclick="editRole('<?php echo htmlspecialchars($role['id']); ?>', '<?php echo htmlspecialchars($role['rol']); ?>')">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-danger btn-sm" data-toggle="tooltip" title="Eliminar" onclick="confirmDelete('<?php echo htmlspecialchars($role['id']); ?>', '<?php echo htmlspecialchars($role['rol']); ?>')">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th>ID</th>
                        <th>Rol</th>
                        <th>Fecha de Creación</th>
                        <th>Fecha de Actualización</th>
                        <th>Acciones</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<!-- Modal para agregar un nuevo rol -->
<div class="modal fade" id="addRoleModal" tabindex="-1" role="dialog" aria-labelledby="addRoleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addRoleModalLabel">Agregar Nuevo Rol</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addRoleForm" action="../dashboard_pages/roles/add_role.php" method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="newRoleName">Rol</label>
                        <input type="text" class="form-control" id="newRoleName" name="rol" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Agregar Rol</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para editar detalles del rol -->
<div class="modal fade" id="editRoleModal" tabindex="-1" role="dialog" aria-labelledby="editRoleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editRoleModalLabel">Editar Rol</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editRoleForm" action="../dashboard_pages/roles/update_role.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id" id="editRoleId">
                    <div class="form-group">
                        <label for="editRoleName">Rol</label>
                        <input type="text" class="form-control" id="editRoleName" name="rol" required>
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

<script src="../scripts/user_list.js"></script>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
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
<script>
function confirmDelete(id, rol) {
    Swal.fire({
        title: '¿Desea eliminar el rol?',
        text: rol,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'No, cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Redirigir a la URL de eliminación con el ID del rol
            window.location.href = '../dashboard_pages/rol_list.php?delete=' + encodeURIComponent(id);
        }
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

function editRole(id, rol) {
    // Rellenar el formulario del modal con los detalles del rol
    document.getElementById('editRoleId').value = id;
    document.getElementById('editRoleName').value = rol;

    // Mostrar el modal
    $('#editRoleModal').modal('show');
}

function showAddRoleModal() {
    // Mostrar el modal de agregar rol
    $('#addRoleModal').modal('show');
}
</script>

</body>
</html>
