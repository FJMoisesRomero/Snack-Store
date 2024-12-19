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
// Capturar el ID del depósito desde la URL
$deposito_id = isset($_GET['deposito_id']) ? intval($_GET['deposito_id']) : 0;

if ($deposito_id <= 0) {
    echo "ID del depósito no válido.";
    exit();
}

// Consultar el nombre del depósito
$stmt = $conn->prepare("SELECT nombre FROM depositos WHERE id = :deposito_id");
$stmt->bindParam(':deposito_id', $deposito_id);
$stmt->execute();
$deposito = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$deposito) {
    echo "Depósito no encontrado.";
    exit();
}

$deposito_nombre = htmlspecialchars($deposito['nombre']);

// Consultar los artículos asociados al depósito con información completa del artículo
$query = "
    SELECT ad.articulo_id, a.nombre AS nombre, ad.stock, ad.stock_minimo, a.imagen, 
           m.nombre AS marca_nombre, c.nombre AS categoria_nombre
    FROM articulosxdeposito ad
    JOIN articulos a ON ad.articulo_id = a.id
    LEFT JOIN marcas m ON a.marca_id = m.id
    LEFT JOIN categorias c ON a.categoria_id = c.id
    WHERE ad.deposito_id = :deposito_id
";
$stmt = $conn->prepare($query);
$stmt->bindParam(':deposito_id', $deposito_id);
$stmt->execute();
$articulos = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Artículos en Depósito</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.7.4/sweetalert2.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.7.4/sweetalert2.all.min.js"></script>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/user_list.css">
<style>
/* Estilo para filas con stock igual al stock mínimo */
.alert-stock {
    background-color: #f8d7da; /* Color de fondo rojo claro */
    color: #721c24; /* Color del texto rojo oscuro */
}

/* Estilo para el texto adicional de "Reponer Stock" */
.alert-text {
    color: red;
    font-weight: bold;
    display: block; /* Asegura que el texto aparezca en una nueva línea */
    margin-top: 5px; /* Espacio entre el stock y el mensaje */
}



</style>
</head>
<body class="hold-transition sidebar-mini">

<div class="content-wrapper">
    <div class="container-fluid mt-3 align-items-center justify-content-center" id="mainContainer">
        <a href="?page=storage_list" class="nav-link" style="font-size: 40px; position: relative; top: 20px; margin-bottom: -40px;">
        <i class="fa-solid fa-arrow-left-long"></i>
        </a>
        <h2>Artículos en  <?php echo $deposito_nombre; ?></h2>

        <!-- Botón para agregar artículo -->
        <a style="margin-bottom: 20px" href="?page=movementsxstorage&deposito_id=<?php echo htmlspecialchars($deposito_id); ?>"  class="btn btn-primary">
            <i class="fa-solid fa-eye"> </i> Ver Movimientos
        </a>
        <div id="table-wrapper">
            <table id="Table" class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Imagen</th>
                        <th>Nombre</th>
                        <th>Marca</th>
                        <th>Categoría</th>
                        <th>Stock en Depósito</th>
                        <th>Stock mínimo</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="articulosTableBody">
                <?php foreach ($articulos as $articulo): ?>
                <tr class="<?php echo ($articulo['stock'] == $articulo['stock_minimo']) ? 'alert-stock' : ''; ?>">
                    <td><?php echo htmlspecialchars($articulo['articulo_id']); ?></td>
                    <td>
                        <?php if ($articulo['imagen']): ?>
                            <?php $imagen_articulo = base64_encode($articulo['imagen']); ?>
                            <img src="data:image/jpeg;base64,<?php echo $imagen_articulo; ?>" alt="Imagen del Artículo" style="width: 100px; height: auto;">
                        <?php else: ?>
                            <img src="images/default-image.png" alt="Imagen por defecto" style="width: 100px; height: auto;">
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($articulo['nombre']); ?></td>
                    <td><?php echo htmlspecialchars($articulo['marca_nombre']); ?></td>
                    <td><?php echo htmlspecialchars($articulo['categoria_nombre']); ?></td>
                    <td>
                        <?php echo htmlspecialchars($articulo['stock']); ?>
                        <?php if ($articulo['stock'] == $articulo['stock_minimo']): ?>
                            <br><span class="alert-text"><i class="fa-solid fa-circle-exclamation"></i> Reponer Stock</span>
                        <?php endif; ?>
                    </td>
                    <td><?php echo htmlspecialchars($articulo['stock_minimo']); ?></td>
                    <td>
                        <button class="btn btn-warning btn-sm" data-toggle="tooltip" title="Editar Stock Mínimo" onclick="editArticle('<?php echo htmlspecialchars($articulo['articulo_id']); ?>', '<?php echo htmlspecialchars($articulo['stock_minimo']); ?>', '<?php echo htmlspecialchars($articulo['stock']); ?>')">
                            <i class="fas fa-edit"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>


                </tbody>
                <tfoot>
                    <tr>
                        <th>ID</th>
                        <th>Imagen</th>
                        <th>Nombre</th>
                        <th>Marca</th>
                        <th>Categoría</th>
                        <th>Stock en Depósito</th>
                        <th>Stock mínimo</th>
                        <th>Acciones</th>
                    </tr>
                </tfoot>
            </table>
        </div>


        <!-- Paginación -->
        <div class="pagination" id="pagination"></div>
    </div>
</div>


<!-- Modal para editar el stock mínimo -->
<div class="modal fade" id="editArticleModal" tabindex="-1" role="dialog" aria-labelledby="editArticleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editArticleModalLabel">Editar Stock Mínimo en Depósito</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editArticleForm" action="../dashboard_pages/articlesxstorage/update_articlexstorage.php" method="POST">
                <input type="hidden" id="editArticleId" name="articulo_id" value="">
                <input type="hidden" id="editDepositoId" name="deposito_id" value="<?php echo htmlspecialchars($deposito_id); ?>">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="editArticleQuantity">Nuevo Stock Mínimo en Depósito</label>
                        <input type="number" class="form-control" id="editArticleQuantity" name="stock_minimo" required min="1">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Actualizar Stock Mínimo</button>
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

    // Función para mostrar el modal de agregar artículo
    function showAddArticleModal() {
        $('#addArticleModal').modal('show');
    }

    function editArticle(articulo_id, stock_minimo, stock) {
        // Configurar los campos del modal con los valores proporcionados
        document.getElementById('editArticleId').value = articulo_id;
        document.getElementById('editArticleQuantity').value = stock_minimo;
        document.getElementById('editArticleQuantity').setAttribute('max', stock - 1);
        
        // Mostrar el modal
        $('#editArticleModal').modal('show');
    }


</script>

</body>
</html>
