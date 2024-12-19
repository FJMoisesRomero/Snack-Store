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

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    // Preparar la consulta para cambiar el estado_activo a false
    $stmt = $conn->prepare("UPDATE marcas SET estado_activo = false WHERE id = :id");
    $stmt->bindParam(':id', $id);

    if ($stmt->execute()) {
        session_start();
        $_SESSION['mensaje_exito'] = 'Marca desactivada correctamente';
        $previousPage = $_SERVER['HTTP_REFERER'];
        $previousPageWithMessage = $previousPage . (strpos($previousPage, '?') !== false ? '&' : '?') . "show_message=1";
        header("Location: $previousPageWithMessage");
        exit;
    } else {
        echo "<script>alert('Error al desactivar la marca.');</script>";
    }
}

// Consultar las marcas activas
$stmt = $conn->prepare("
    SELECT id, nombre, created_at, updated_at
    FROM marcas
    WHERE estado_activo = true
");
$stmt->execute();
$marcas = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Marcas</title>

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
        <h2>Lista de Marcas</h2>

        <!-- Botón para agregar marca -->
        <button class="btn btn-primary mb-3" onclick="showAddBrandModal()"><i class="fa-solid fa-plus" style="margin-right:10px"></i>Agregar Marca</button>

        <div id="table-wrapper">
            <table id="Table" class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Fecha de Creación</th>
                        <th>Fecha de Actualización</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="brandsTableBody">
                    <?php foreach ($marcas as $marca): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($marca['id']); ?></td>
                        <td><?php echo htmlspecialchars($marca['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($marca['created_at']); ?></td>
                        <td><?php echo htmlspecialchars($marca['updated_at']); ?></td>
                        <td>
                            <button class="btn btn-warning btn-sm" data-toggle="tooltip" title="Editar" onclick="editBrand(
                                '<?php echo htmlspecialchars($marca['id'], ENT_QUOTES, 'UTF-8'); ?>', 
                                '<?php echo htmlspecialchars($marca['nombre'], ENT_QUOTES, 'UTF-8'); ?>'
                            )">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-danger btn-sm" data-toggle="tooltip" title="Eliminar" onclick="confirmDelete('<?php echo htmlspecialchars($marca['id']); ?>', '<?php echo htmlspecialchars($marca['nombre']); ?>')">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Fecha de Creación</th>
                        <th>Fecha de Actualización</th>
                        <th>Acciones</th>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Paginación -->
        <div class="pagination" id="pagination"></div>

    </div>
</div>

<!-- Modal para agregar una nueva marca -->
<div class="modal fade" id="addBrandModal" tabindex="-1" role="dialog" aria-labelledby="addBrandModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addBrandModalLabel">Agregar Nueva Marca</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addBrandForm" action="../dashboard_pages/brands/add_brand.php" method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="newBrandName">Nombre</label>
                        <input type="text" class="form-control" id="newBrandName" name="nombre" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Agregar Marca</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para editar una marca -->
<div class="modal fade" id="editBrandModal" tabindex="-1" role="dialog" aria-labelledby="editBrandModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editBrandModalLabel">Editar Marca</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editBrandForm" action="../dashboard_pages/brands/update_brand.php" method="POST">
                <input type="hidden" id="editBrandId" name="id">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="editBrandName">Nombre</label>
                        <input type="text" class="form-control" id="editBrandName" name="nombre" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Actualizar Marca</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
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

    // Función para mostrar el modal de agregar marca
    function showAddBrandModal() {
        $('#addBrandModal').modal('show');
    }

    // Función para mostrar el modal de editar marca
    function editBrand(id, nombre) {
        document.getElementById('editBrandId').value = id;
        document.getElementById('editBrandName').value = nombre;
        // Mostrar el modal
        $('#editBrandModal').modal('show');
    }

// Función para confirmar la eliminación de una marca
function confirmDelete(brandId, brandName) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: `Eliminarás la marca "${brandName}". Esta acción no se puede deshacer.`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = `dashboard_pages/brand_list.php?delete=${brandId}`;
        }
    });
}
</script>

</body>
</html>
