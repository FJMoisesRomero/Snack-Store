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
    $stmt = $conn->prepare("UPDATE categorias SET estado_activo = false WHERE id = :id");
    $stmt->bindParam(':id', $id);

    if ($stmt->execute()) {
        session_start();
        $_SESSION['mensaje_exito'] = 'Categoría desactivada correctamente';
        $previousPage = $_SERVER['HTTP_REFERER'];
        $previousPageWithMessage = $previousPage . (strpos($previousPage, '?') !== false ? '&' : '?') . "show_message=1";
        header("Location: $previousPageWithMessage");
        exit;
    } else {
        echo "<script>alert('Error al desactivar la categoría.');</script>";
    }
}

// Consultar las categorías activas
$stmt = $conn->prepare("
    SELECT id, nombre, created_at, updated_at
    FROM categorias
    WHERE estado_activo = true
");
$stmt->execute();
$categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Categorías</title>

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
        <h2>Lista de Categorías</h2>

        <!-- Botón para agregar categoría -->
        <button class="btn btn-primary mb-3" onclick="showAddCategoryModal()"><i class="fa-solid fa-plus" style="margin-right:10px"></i>Agregar Categoría</button>

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
                <tbody id="categoriesTableBody">
                    <?php foreach ($categorias as $categoria): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($categoria['id']); ?></td>
                        <td><?php echo htmlspecialchars($categoria['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($categoria['created_at']); ?></td>
                        <td><?php echo htmlspecialchars($categoria['updated_at']); ?></td>
                        <td>
                            <button class="btn btn-warning btn-sm" data-toggle="tooltip" title="Editar" onclick="editCategory(
                                '<?php echo htmlspecialchars($categoria['id'], ENT_QUOTES, 'UTF-8'); ?>', 
                                '<?php echo htmlspecialchars($categoria['nombre'], ENT_QUOTES, 'UTF-8'); ?>'
                            )">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-danger btn-sm" data-toggle="tooltip" title="Eliminar" onclick="confirmDelete('<?php echo htmlspecialchars($categoria['id']); ?>', '<?php echo htmlspecialchars($categoria['nombre']); ?>')">
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

<!-- Modal para agregar una nueva categoría -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" role="dialog" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addCategoryModalLabel">Agregar Nueva Categoría</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addCategoryForm" action="../dashboard_pages/categories/add_category.php" method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="newCategoryName">Nombre</label>
                        <input type="text" class="form-control" id="newCategoryName" name="nombre" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Agregar Categoría</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para editar una categoría -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" role="dialog" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editCategoryModalLabel">Editar Categoría</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editCategoryForm" action="../dashboard_pages/categories/update_category.php" method="POST">
                <input type="hidden" id="editCategoryId" name="id">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="editCategoryName">Nombre</label>
                        <input type="text" class="form-control" id="editCategoryName" name="nombre" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Actualizar Categoría</button>
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
            <?php unset($_SESSION['mensaje_exito']); ?>
        <?php endif; ?>
    });

    // Función para mostrar el modal de agregar categoría
    function showAddCategoryModal() {
        $('#addCategoryModal').modal('show');
    }

    // Función para mostrar el modal de editar categoría
    function editCategory(id, nombre) {
        document.getElementById('editCategoryId').value = id;
        document.getElementById('editCategoryName').value = nombre;
        $('#editCategoryModal').modal('show');
    }

    // Función para confirmar la eliminación de una categoría
    function confirmDelete(id, nombre) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: `Estás a punto de eliminar la categoría "${nombre}"`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'dashboard_pages/category_list.php?delete=' + id;
            }
        });
    }
</script>
</body>
</html>
