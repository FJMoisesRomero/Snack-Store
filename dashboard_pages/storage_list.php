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
    $stmt = $conn->prepare("UPDATE depositos SET estado_activo = false WHERE id = :id");
    $stmt->bindParam(':id', $id);

    if ($stmt->execute()) {
        session_start();
        $_SESSION['mensaje_exito'] = 'Depósito desactivado correctamente';
        $previousPage = $_SERVER['HTTP_REFERER'];
        $previousPageWithMessage = $previousPage . (strpos($previousPage, '?') !== false ? '&' : '?') . "show_message=1";
        header("Location: $previousPageWithMessage");
        exit;
    } else {
        echo "<script>alert('Error al desactivar el depósito.');</script>";
    }
}

$stmt = $conn->prepare("
    SELECT d.id, d.nombre AS deposito_nombre, 
           s.nombre AS sucursal_nombre, 
           IFNULL(SUM(CASE WHEN a.deposito_id = d.id THEN a.stock END), 0) AS capacidad_usada,
           d.created_at,
           d.updated_at
    FROM depositos d
    LEFT JOIN articulosxdeposito a ON d.id = a.deposito_id
    LEFT JOIN depositosxsucursal ds ON d.id = ds.deposito_id
    LEFT JOIN sucursales s ON ds.sucursal_id = s.id
    WHERE d.estado_activo = true
    GROUP BY d.id, s.nombre, d.created_at, d.updated_at
");

$stmt->execute();
$depositos = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Depósitos</title>

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
        <h2>Lista General de Depósitos</h2>



        <div id="table-wrapper">
            <table id="Table" class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Sucursal</th> <!-- Nueva columna para la sucursal -->
                    <th>Total de Existencias</th>
                    <th>Fecha de Creación</th>
                    <th>Fecha de Actualización</th>
                    <th>Acciones</th>
                </tr>
            </thead>
                <tbody id="storageTableBody">
                    <?php foreach ($depositos as $deposito): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($deposito['id']); ?></td>
                        <td><?php echo htmlspecialchars($deposito['deposito_nombre']); ?></td>
                        <td><?php echo htmlspecialchars($deposito['sucursal_nombre']); ?></td> <!-- Mostrar sucursal -->
                        <td>
                            <?php
                            $capacidad_usada = htmlspecialchars($deposito['capacidad_usada']);
                            ?> 
                            <span style="color: red;"><?php echo $capacidad_usada; ?></span>
                        </td>
                        <td><?php echo htmlspecialchars($deposito['created_at']); ?></td>
                        <td><?php echo htmlspecialchars($deposito['updated_at']); ?></td>
                        <td>
                            <a data-toggle="tooltip" title="Ver Articulos" href="?page=articlexstorage&deposito_id=<?php echo htmlspecialchars($deposito['id']); ?>" class="btn btn-info btn-sm">
                                <i class="fas fa-eye"></i>
                            </a>
                            <button class="btn btn-warning btn-sm" data-toggle="tooltip" title="Editar" onclick="editStorage('<?php echo htmlspecialchars($deposito['id'], ENT_QUOTES, 'UTF-8'); ?>', '<?php echo htmlspecialchars($deposito['nombre'], ENT_QUOTES, 'UTF-8'); ?>')">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-danger btn-sm" data-toggle="tooltip" title="Eliminar" onclick="confirmDelete('<?php echo htmlspecialchars($deposito['id']); ?>', '<?php echo htmlspecialchars($deposito['nombre']); ?>')">
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
                        <th>Sucursal</th>
                        <th>Total de Existencias</th>
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



<!-- Modal para editar un depósito -->
<div class="modal fade" id="editStorageModal" tabindex="-1" role="dialog" aria-labelledby="editStorageModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editStorageModalLabel">Editar Depósito</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editStorageForm" action="../dashboard_pages/storages/update_storage.php" method="POST">
                <input type="hidden" id="editStorageId" name="id">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="editStorageName">Nombre</label>
                        <input type="text" class="form-control" id="editStorageName" name="nombre" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Actualizar Depósito</button>
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


    // Función para mostrar el modal de editar depósito
    function editStorage(id, nombre) {
        document.getElementById('editStorageId').value = id;
        document.getElementById('editStorageName').value = nombre;
        // Mostrar el modal
        $('#editStorageModal').modal('show');
    }

    // Función para confirmar la eliminación de un depósito
    function confirmDelete(storageId, storageName) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: `Eliminarás el depósito "${storageName}". Esta acción no se puede deshacer.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `../dashboard_pages/storage_list.php?delete=${storageId}`;
            }
        });
    }
</script>

</body>
</html>
