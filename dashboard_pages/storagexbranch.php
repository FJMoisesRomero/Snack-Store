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

// Depuración: Mostrar el parámetro de la sucursal recibido
if (isset($_GET['sucursal'])) {
    $sucursal = intval($_GET['sucursal']);
    echo "Sucursal recibido: " . $sucursal . "<br>";
} else {
    echo "No se recibió el parámetro sucursal.<br>";
    $sucursal = 0;
}

if ($sucursal <= 0) {
    echo "Sucursal no válida.";
    exit();
}


// Consultar la sucursal
$stmt = $conn->prepare("SELECT id, nombre FROM sucursales WHERE id = :sucursal_id");
$stmt->bindParam(':sucursal_id', $sucursal);
$stmt->execute();
$sucursalData = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$sucursalData) {
    echo "Sucursal no encontrada.";
    exit();
}

$sucursal_id = $sucursalData['id'];
$sucursal_nombre = htmlspecialchars($sucursalData['nombre']);

// Consultar los depósitos asociados a la sucursal con capacidad usada
$query = "
    SELECT d.id, d.nombre, 
           IFNULL(SUM(CASE WHEN a.deposito_id = d.id THEN a.stock END), 0) AS capacidad_usada, 
           d.created_at, d.updated_at
    FROM depositos d
    JOIN depositosxsucursal ds ON d.id = ds.deposito_id
    LEFT JOIN articulosxdeposito a ON d.id = a.deposito_id
    WHERE ds.sucursal_id = :sucursal_id
    AND ds.estado_activo = 1
    GROUP BY d.id, d.nombre, d.created_at, d.updated_at
";
$stmt = $conn->prepare($query);
$stmt->bindParam(':sucursal_id', $sucursal_id);
$stmt->execute();
$depositos = $stmt->fetchAll(PDO::FETCH_ASSOC);



?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Depósitos en la Sucursal</title>

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
        <a href="?page=branch_list" class="nav-link" style="font-size: 40px; position: relative; top: 20px; margin-bottom:-40px">
            <i class="fa-solid fa-arrow-left-long"></i>
        </a>
        <h2>Depósitos en  <?php echo $sucursal_nombre; ?></h2>

        <!-- Botón para agregar depósito -->
        <button class="btn btn-primary mb-3" onclick="showAddStorageModal()"><i class="fa-solid fa-plus" style="margin-right:10px"></i>Agregar Depósito</button>

        <div id="table-wrapper">
            <table id="Table" class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Total de Existencias</th>
                        <th>Fecha de Creación</th>
                        <th>Fecha de Actualización</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="depositosTableBody">
                    <?php foreach ($depositos as $deposito): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($deposito['id']); ?></td>
                        <td><?php echo htmlspecialchars($deposito['nombre']); ?></td>
                        <td>
                            <?php
                            $capacidad_usada = htmlspecialchars($deposito['capacidad_usada']);
                            ?> 
                            <span style="color: red;"><?php echo $capacidad_usada; ?></span>
                        </td>
                        <td><?php echo htmlspecialchars($deposito['created_at']); ?></td>
                        <td><?php echo htmlspecialchars($deposito['updated_at']); ?></td>
                        <td>
                            <a data-toggle="tooltip" title="Ver Articulos" href="?page=articlexstorage&deposito_id=<?php echo htmlspecialchars($deposito['id']); ?>" class="btn btn-info btn-sm <?php echo $pagina === 'articlexstorage' ? 'active' : ''; ?>">
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

<!-- Modal para agregar un nuevo depósito -->
<div class="modal fade" id="addStorageModal" tabindex="-1" role="dialog" aria-labelledby="addStorageModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addStorageModalLabel">Agregar Nuevo Depósito</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addStorageForm" action="../dashboard_pages/storages/add_storage.php" method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="newStorageName">Nombre</label>
                        <input type="text" class="form-control" id="newStorageName" name="nombre" required>
                    </div>
                    <input type="hidden" name="sucursal_id" value="<?php echo htmlspecialchars($sucursal_id); ?>">
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Agregar Depósito</button>
                </div>
            </form>
        </div>
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

    // Función para mostrar el modal de agregar depósito
    function showAddDepositModal() {
        $('#addDepositModal').modal('show');
    }

    // Función para mostrar el modal de editar depósito
    function editStorage(id, nombre) {
        document.getElementById('editStorageId').value = id;
        document.getElementById('editStorageName').value = nombre;
        // Mostrar el modal
        $('#editStorageModal').modal('show');
    }

    // Función para confirmar la eliminación de un depósito
    function confirmDelete(depositoId, depositoNombre) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: `Eliminarás el depósito "${depositoNombre}". Esta acción no se puede deshacer.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `../dashboard_pages/storagesxbranch/delete_storagexbranch.php?id=${depositoId}&sucursal_id=<?php echo htmlspecialchars($sucursal); ?>`;
                }
            });
        }

    
    // Función para mostrar el modal de agregar depósito
    function showAddStorageModal() {
        $('#addStorageModal').modal('show');
    }

</script>

</body>
</html>
