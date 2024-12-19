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
    $id = intval($_GET['delete']); // Convertir a entero

    // Validar ID
    if ($id > 0) {
        try {
            // Preparar la consulta para cambiar el estado_activo a false
            $stmt = $conn->prepare("UPDATE sucursales SET estado_activo = false WHERE id = :id");
            $stmt->bindParam(':id', $id);

            if ($stmt->execute()) {
                session_start();
                $_SESSION['mensaje_exito'] = 'Sucursal desactivada correctamente';
                // Redirigir a la página anterior
                $previousPage = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php'; // Ruta de fallback
                $previousPageWithMessage = $previousPage . (strpos($previousPage, '?') !== false ? '&' : '?') . "show_message=1";
                header("Location: $previousPageWithMessage");
                exit;
            } else {
                echo "<script>alert('Error al desactivar la sucursal.');</script>";
            }
        } catch (PDOException $e) {
            echo "<script>alert('Error en la base de datos: " . $e->getMessage() . "');</script>";
        }
    } else {
        echo "<script>alert('ID de sucursal inválido.');</script>";
    }
}

// Consultar las sucursales activas
$stmt = $conn->prepare("
    SELECT id, nombre, direccion,responsable, created_at, updated_at
    FROM sucursales
    WHERE estado_activo = true
");
$stmt->execute();
$sucursales = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Sucursales</title>

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
    <div class="container-fluid mt-3 align-items-center justify-content-center"  id="mainContainer">
        <h2>Lista de Sucursales</h2>

        <!-- Botón para agregar sucursal -->
        <button class="btn btn-primary mb-3" onclick="showAddBranchModal()"><i class="fa-solid fa-plus" style="margin-right:10px"></i>Agregar Sucursal</button>

        <div id="table-wrapper">
            <table id="Table" class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Responsable</th>
                        <th>Dirección</th>
                        <th>Fecha de Creación</th>
                        <th>Fecha de Actualización</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="branchTableBody">
                    <?php foreach ($sucursales as $sucursal): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($sucursal['id']); ?></td>
                        <td><?php echo htmlspecialchars($sucursal['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($sucursal['responsable']); ?></td>
                        <td><?php echo htmlspecialchars($sucursal['direccion']); ?></td>
                        <td><?php echo htmlspecialchars($sucursal['created_at']); ?></td>
                        <td><?php echo htmlspecialchars($sucursal['updated_at']); ?></td>
                        <td>
                            <a data-toggle="tooltip" title="Ver Depósitos" href="?page=storagexbranch&sucursal=<?php echo htmlspecialchars($sucursal['id']); ?>" class="btn btn-info btn-sm">
                                <i class="fas fa-eye"></i>
                            </a>
                            <button class="btn btn-warning btn-sm" data-toggle="tooltip" title="Editar" onclick="editBranch(
                                '<?php echo htmlspecialchars($sucursal['id'], ENT_QUOTES, 'UTF-8'); ?>', 
                                '<?php echo htmlspecialchars($sucursal['nombre'], ENT_QUOTES, 'UTF-8'); ?>',
                                '<?php echo htmlspecialchars($sucursal['responsable'], ENT_QUOTES, 'UTF-8'); ?>', 
                                '<?php echo htmlspecialchars($sucursal['direccion'], ENT_QUOTES, 'UTF-8'); ?>'
                                
                            )">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-danger btn-sm" data-toggle="tooltip" title="Eliminar" onclick="confirmDelete('<?php echo htmlspecialchars($sucursal['id']); ?>', '<?php echo htmlspecialchars($sucursal['nombre']); ?>')">
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
                        <th>Responsable</th>
                        <th>Dirección</th>
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

<!-- Modal para agregar una nueva sucursal -->
<div class="modal fade" id="addBranchModal" tabindex="-1" role="dialog" aria-labelledby="addBranchModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addBranchModalLabel">Agregar Nueva Sucursal</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addBranchForm" action="../dashboard_pages/branches/add_branch.php" method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="newBranchName">Nombre</label>
                        <input type="text" class="form-control" id="newBranchName" name="nombre" required>
                    </div>
                    <div class="form-group">
                        <label for="newBranchResponsable">Responsable</label>
                        <input type="text" class="form-control" id="newBranchResponsable" name="responsable" required>
                    </div>
                    <div class="form-group">
                            <label for="newBranchAddress">Dirección</label>
                            <input type="text" class="form-control" id="newBranchAddress" name="direccion" required>
                            <div id="mapAdd" style="height: 400px; width: 100%;"></div>
                    </div>
                    
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Agregar Sucursal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para editar una sucursal -->
<div class="modal fade" id="editBranchModal" tabindex="-1" role="dialog" aria-labelledby="editBranchModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editBranchModalLabel">Editar Sucursal</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editBranchForm" action="../dashboard_pages/branches/update_branch.php" method="POST">
                <input type="hidden" id="editBranchId" name="id">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="editBranchName">Nombre</label>
                        <input type="text" class="form-control" id="editBranchName" name="nombre" required>
                    </div>
                    <div class="form-group">
                        <label for="editBranchResponsable">Responsable</label>
                        <input type="text" class="form-control" id="editBranchResponsable" name="responsable" required>
                    </div>
                    <div class="form-group">
                            <label for="editBranchAddress">Dirección</label>
                            <input type="text" class="form-control" id="editBranchAddress" name="direccion" required>
                            <div id="mapEdit" style="height: 400px; width: 100%;"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Actualizar Sucursal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>
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

    // Función para mostrar el modal de agregar sucursal
    function showAddBranchModal() {
        $('#addBranchModal').modal('show');
    }

    // Función para mostrar el modal de editar sucursal
    function editBranch(id, nombre, responsable, direccion) {
        document.getElementById('editBranchId').value = id;
        document.getElementById('editBranchName').value = nombre;
        document.getElementById('editBranchResponsable').value = responsable;
        document.getElementById('editBranchAddress').value = direccion;
        // Mostrar el modal
        $('#editBranchModal').modal('show');
    }

    // Función para confirmar la eliminación de una sucursal
    function confirmDelete(branchId, branchName) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: `Eliminarás la sucursal "${branchName}". Esta acción no se puede deshacer.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `dashboard_pages/branch_list.php?delete=${branchId}`;
            }
        });
    }
</script>

</body>
</html>
