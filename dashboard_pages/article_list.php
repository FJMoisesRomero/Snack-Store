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

// Manejar la desactivación de artículos
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    // Preparar la consulta para cambiar el estado_activo a false
    $stmt = $conn->prepare("UPDATE articulos SET estado_activo = false WHERE id = :id");
    $stmt->bindParam(':id', $id);

    if ($stmt->execute()) {
        session_start();
        $_SESSION['mensaje_exito'] = 'Artículo desactivado correctamente';
        $previousPage = $_SERVER['HTTP_REFERER'];
        $previousPageWithMessage = $previousPage . (strpos($previousPage, '?') !== false ? '&' : '?') . "show_message=1";
        header("Location: $previousPageWithMessage");
        exit;
    } else {
        echo "<script>alert('Error al desactivar el artículo.');</script>";
    }
}

// Consultar las marcas para el <select> en el modal
$stmt = $conn->prepare("SELECT id, nombre FROM marcas");
$stmt->execute();
$marcas = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Consultar las categorías para el <select> en el modal de agregar artículo
$stmt = $conn->prepare("SELECT id, nombre FROM categorias");
$stmt->execute();
$categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Consultar los artículos activos
$stmt = $conn->prepare("
    SELECT 
        a.id, 
        a.nombre,
        a.descripcion, 
        a.imagen, 
        a.marca_id,
        a.categoria_id,
        m.nombre AS marca_nombre, 
        c.nombre AS categoria_nombre, 
        a.created_at, 
        a.updated_at 
    FROM 
        articulos a
    LEFT JOIN 
        marcas m ON a.marca_id = m.id
    LEFT JOIN 
        categorias c ON a.categoria_id = c.id
    WHERE 
        a.estado_activo = true
");


$stmt->execute();
$articulos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Artículos</title>

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
<!-- Contenedor Principal -->
<div class="content-wrapper" style="height:auto;">
    <div class="container-fluid mt-3 align-items-center justify-content-center" id="mainContainer">
        <h2>Lista de Artículos</h2>

        <!-- Botón para agregar artículo -->
        <button class="btn btn-primary mb-3" onclick="showAddArticleModal()"><i class="fa-solid fa-plus" style="margin-right:10px"></i>Agregar Artículo</button>

        <div id="table-wrapper">
            <table id="Table" class="table table-striped ">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Imagen</th>
                        <th>Nombre</th>
                        <th>Descripcion</th>
                        <th>Categoría</th>
                        <th>Marca</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="articulosTableBody">
                    <?php foreach ($articulos as $articulo): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($articulo['id']); ?></td>
                        <td>
                            <?php if ($articulo['imagen']): ?>
                                <?php $imagen_articulo = base64_encode($articulo['imagen']); ?>
                                <img src="data:image/jpeg;base64,<?php echo $imagen_articulo; ?>" alt="Imagen del Artículo" style="width: 100px; height: auto;">
                            <?php else: ?>
                                <img src="images/default-image.png" alt="Imagen por defecto" style="width: 100px; height: auto;">
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($articulo['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($articulo['descripcion']); ?></td>
                        <td><?php echo htmlspecialchars($articulo['categoria_nombre']); ?></td>
                        <td><?php echo htmlspecialchars($articulo['marca_nombre']); ?></td>
                        <td>
                            <button class="btn btn-info btn-sm" data-toggle="tooltip" title="Ver Detalles" onclick="viewArticleDetails('<?php echo htmlspecialchars($articulo['id']); ?>')">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="btn btn-warning btn-sm" data-toggle="tooltip" title="Editar" onclick="editArticle(
                                '<?php echo htmlspecialchars($articulo['id'], ENT_QUOTES, 'UTF-8'); ?>',
                                '<?php echo htmlspecialchars($articulo['nombre'], ENT_QUOTES, 'UTF-8'); ?>',
                                '<?php echo htmlspecialchars($articulo['descripcion'], ENT_QUOTES, 'UTF-8'); ?>',
                                '<?php echo htmlspecialchars($articulo['marca_id'], ENT_QUOTES, 'UTF-8'); ?>',
                                '<?php echo htmlspecialchars($articulo['categoria_id'], ENT_QUOTES, 'UTF-8'); ?>',
                                )">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-danger btn-sm" data-toggle="tooltip" title="Eliminar" onclick="confirmDelete('<?php echo htmlspecialchars($articulo['id']); ?>', '<?php echo htmlspecialchars($articulo['nombre']); ?>')">
                                <i class="fas fa-trash-alt"></i>
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
                        <th>Descripcion</th>
                        <th>Categoría</th>
                        <th>Marca</th>
                        <th>Acciones</th>
                    </tr>
                </tfoot>
            </table>
        </div>


        <!-- Paginación -->
        <div class="pagination" id="pagination"></div>

    </div>
</div>

<!-- Modal para agregar un nuevo artículo -->
<div class="modal fade" id="addArticleModal" tabindex="-1" role="dialog" aria-labelledby="addArticleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addArticleModalLabel">Agregar Nuevo Artículo</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addArticleForm" action="../dashboard_pages/articles/add_article.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="newArticleName">Nombre</label>
                        <input type="text" class="form-control" id="newArticleName" name="nombre" required>
                    </div>
                    <div class="form-group">
                        <label for="newArticleDescription">Descripción</label>
                        <input type="text" class="form-control" id="newArticleDescription" name="descripcion" required>
                    </div>
                    <div class="form-group">
                        <label for="newArticleMarcaId">Marca</label>
                        <input type="text" class="form-control" id="autocomplete-marca" placeholder="Selecciona una marca..." readonly>
                        <input type="text" id="search-marca" placeholder="Escribe para buscar..." style="width: 100%; margin-top: 5px;">
                        <div id="autocomplete-list-marca" class="autocomplete-list" style="display: none;"></div>
                        <input type="hidden" name="marca_id" id="newArticleMarcaId" required>
                    </div>
                    <div class="form-group">
                        <label for="newArticleCategoriaId">Categoría</label>
                        <input type="text" class="form-control" id="autocomplete-categoria" placeholder="Selecciona una categoría..." readonly>
                        <input type="text" id="search-categoria" placeholder="Escribe para buscar..." style="width: 100%; margin-top: 5px;">
                        <div id="autocomplete-list-categoria" class="autocomplete-list" style="display: none;"></div>
                        <input type="hidden" name="categoria_id" id="newArticleCategoriaId" required>
                    </div>
                    <div class="form-group">
                        <label for="newArticleImage">Imagen</label>
                        <input type="file" class="form-control" id="newArticleImage" name="imagen" accept="image/*">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Agregar Artículo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const marcas = <?php echo json_encode($marcas); ?>;
    const categorias = <?php echo json_encode($categorias); ?>;

    function setupAutocomplete(input, searchInput, list, items, hiddenInput) {
        function updateList(value) {
            list.innerHTML = '';
            const filteredItems = value === '' ? items : items.filter(item => item.nombre.toLowerCase().includes(value.toLowerCase()));
            if (filteredItems.length > 0) {
                list.style.display = 'block';
                filteredItems.forEach(item => {
                    const div = document.createElement('div');
                    div.classList.add('autocomplete-item');
                    div.textContent = item.nombre;
                    div.addEventListener('click', function() {
                        input.value = item.nombre;
                        hiddenInput.value = item.id; // Setea el ID oculto
                        list.style.display = 'none';
                        searchInput.value = ''; // Limpiar el input de búsqueda
                    });
                    list.appendChild(div);
                });
            } else {
                list.style.display = 'none';
            }
        }

        searchInput.addEventListener('input', function() {
            updateList(searchInput.value);
        });

        searchInput.addEventListener('focus', function() {
            updateList(searchInput.value);
        });

        document.addEventListener('click', function(event) {
            if (!input.contains(event.target) && !list.contains(event.target) && !searchInput.contains(event.target)) {
                list.style.display = 'none';
            }
        });
    }

    // Configurar autocompletar para marcas y categorías
    setupAutocomplete(
        document.getElementById('autocomplete-marca'),
        document.getElementById('search-marca'),
        document.getElementById('autocomplete-list-marca'),
        marcas,
        document.getElementById('newArticleMarcaId')
    );

    setupAutocomplete(
        document.getElementById('autocomplete-categoria'),
        document.getElementById('search-categoria'),
        document.getElementById('autocomplete-list-categoria'),
        categorias,
        document.getElementById('newArticleCategoriaId')
    );
</script>




<!-- Modal para editar artículo -->
<div class="modal fade" id="editArticleModal" tabindex="-1" role="dialog" aria-labelledby="editArticleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editArticleModalLabel">Editar Artículo</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editArticleForm" action="../dashboard_pages/articles/update_article.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" id="editArticleId" name="id">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="editArticleName">Nombre</label>
                        <input type="text" class="form-control" id="editArticleName" name="nombre" required>
                    </div>
                    <div class="form-group">
                        <label for="editArticleDescription">Descripcion</label>
                        <input type="text" class="form-control" id="editArticleDescription" name="descripcion" required>
                    </div>
                    <div class="form-group">
                        <label for="editArticleMarcaId">Marca</label>
                        <select class="form-control" id="editArticleMarcaId" name="marca_id" required>
                            <?php foreach ($marcas as $marca): ?>
                                <option value="<?php echo htmlspecialchars($marca['id']); ?>"><?php echo htmlspecialchars($marca['nombre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="editArticleCategoriaId">Categoría</label>
                        <select class="form-control" id="editArticleCategoriaId" name="categoria_id" required>
                            <?php foreach ($categorias as $categoria): ?>
                                <option value="<?php echo htmlspecialchars($categoria['id']); ?>"><?php echo htmlspecialchars($categoria['nombre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="editArticleImage">Imagen</label>
                        <input type="file" class="form-control-file" id="editArticleImage" name="imagen">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Actualizar Artículo</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para ver detalles del artículo -->
<div class="modal fade" id="articleDetailsModal" tabindex="-1" role="dialog" aria-labelledby="articleDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="articleDetailsModalLabel">Detalles del Artículo</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Aquí se cargará el contenido del artículo -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
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
                timer: 2000, // El mensaje se mostrará por 1 segundo
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
    // Función para mostrar el modal de agregar artículo
    function showAddArticleModal() {
        $('#addArticleModal').modal('show');
    }

    function editArticle(id, nombre,descripcion, marca_id, categoria_id) {
    // Establecer los valores en el modal
    $('#editArticleId').val(id);
    $('#editArticleName').val(nombre);
    $('#editArticleDescription').val(descripcion);
    $('#editArticleMarcaId').val(marca_id);
    $('#editArticleCategoriaId').val(categoria_id);

    // Mostrar el modal
    $('#editArticleModal').modal('show');
    }



    // Función para confirmar la eliminación de un artículo
    function confirmDelete(articleId, articleName) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: `Eliminarás el artículo "${articleName}". Esta acción no se puede deshacer.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `dashboard_pages/article_list.php?delete=${articleId}`;
            }
        });
    }
    function viewArticleDetails(articleId) {
        fetch(`../dashboard_pages/articles/view_article.php?id=${encodeURIComponent(articleId)}`)
            .then(response => response.text())
            .then(html => {
                // Insertar el HTML recibido en el modal
                document.querySelector('#articleDetailsModal .modal-body').innerHTML = html;
                // Mostrar el modal
                $('#articleDetailsModal').modal('show');
            })
            .catch(error => {
                Swal.fire({
                    title: 'Error',
                    text: 'No se pudieron cargar los detalles del artículo.',
                    icon: 'error'
                });
            });
    }

</script>

</body>
</html>
