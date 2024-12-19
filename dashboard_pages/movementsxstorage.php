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

// Consultar los movimientos asociados al depósito
$query = "
    SELECT md.id, md.deposito_id, md.movimiento_tipo_id, mt.nombre AS tipo_nombre, md.comprobante_cod, md.destino, md.created_at, md.updated_at, md.fecha
    FROM movimientosxdeposito md
    JOIN movimiento_tipos mt ON md.movimiento_tipo_id = mt.id
    WHERE md.deposito_id = :deposito_id
";
$stmt = $conn->prepare($query);
$stmt->bindParam(':deposito_id', $deposito_id);
$stmt->execute();
$movimientos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Consultar los tipos de movimiento
$stmt = $conn->prepare("SELECT id, nombre FROM movimiento_tipos");
$stmt->execute();
$tiposMovimiento = $stmt->fetchAll(PDO::FETCH_ASSOC);
// Consultar articulos disponibles 
$stmt = $conn->prepare("
    SELECT 
        a.id, 
        a.nombre, 
        m.nombre AS marca_nombre, 
        c.nombre AS categoria_nombre,
        a.categoria_id AS categoria_id
    FROM articulos a 
    LEFT JOIN marcas m ON a.marca_id = m.id
    LEFT JOIN categorias c ON a.categoria_id = c.id
    WHERE a.estado_activo = true
");
$stmt->execute();
$articulosDisponibles = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Consultar articulos por deposito (EGRESO)
$stmt = $conn->prepare("
    SELECT 
        a.id, 
        a.nombre, 
        m.nombre AS marca_nombre, 
        c.nombre AS categoria_nombre,
        ad.stock,
        ad.stock_minimo,
        a.categoria_id AS categoria_id
    FROM articulos a         
    JOIN articulosxdeposito ad ON ad.articulo_id = a.id
    LEFT JOIN marcas m ON a.marca_id = m.id
    LEFT JOIN categorias c ON a.categoria_id = c.id
    WHERE ad.deposito_id = :deposito_id
");
$stmt->bindParam(':deposito_id', $deposito_id);
$stmt->execute();
$articulosPorDeposito = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Codificar ambos arrays en JSON
$articulosDisponiblesJson = json_encode($articulosDisponibles);
$articulosPorDepositoJson = json_encode($articulosPorDeposito);

// Consultar los destinos disponibles (depositos y sucursales)
$stmt = $conn->prepare("
    SELECT d.id AS deposito_id, d.nombre AS deposito_nombre, s.nombre AS sucursal_nombre
    FROM depositos d
    LEFT JOIN depositosxsucursal ds ON d.id = ds.deposito_id
    LEFT JOIN sucursales s ON ds.sucursal_id = s.id
");
$stmt->execute();
$destinos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Codificar el array de destinos en JSON para usar en el frontend
$destinosJson = json_encode($destinos);


?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movimientos en Depósito</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.7.4/sweetalert2.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.7.4/sweetalert2.all.min.js"></script>
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/movementsxstorage.css">
    <style>
        .autocomplete-list {
            border: 1px solid #ccc;
            border-top: none;
            max-height: 150px;
            overflow-y: auto;
            position: absolute;
            z-index: 1000;
            background-color: #fff;
            width: 100%;
        }
        .autocomplete-item {
            padding: 8px;
            cursor: pointer;
        }
        .autocomplete-item:hover {
            background-color: #ddd;
        }
    </style>
</head>
<body class="hold-transition sidebar-mini">

<div class="content-wrapper">
    <div class="container-fluid mt-3 align-items-center justify-content-center" id="mainContainer">
        <a href="?page=articlexstorage&deposito_id=<?php echo htmlspecialchars($deposito_id); ?>" class="nav-link" style="font-size: 40px; position: relative; top: 20px; margin-bottom: -40px;">
        <i class="fa-solid fa-arrow-left-long"></i>
        </a>
        <h2>Movimientos en <?php echo $deposito_nombre; ?></h2>

        <!-- Botón para nuevo movimiento -->
        <button type="button" class="btn btn-primary" style="margin-bottom: 20px" data-toggle="modal" data-target="#addMovementModal">
            <i class="fa-solid fa-plus"> </i> Nuevo Movimiento
        </button>

        <div id="table-wrapper">
        <table id="Table" class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Tipo de Movimiento</th>
                    <th>Código de Comprobante</th>
                    <th>Destino</th>
                    <th>Detalle del Movimiento</th>
                    <th>Fecha del Movimiento</th>
                </tr>
            </thead>
            <tbody id="movimientosTableBody">
                <?php foreach ($movimientos as $movimiento): ?>
                <tr>
                    <td><?php echo htmlspecialchars($movimiento['id']); ?></td>
                    <td><?php echo htmlspecialchars($movimiento['tipo_nombre']); ?></td>
                    <td><?php echo htmlspecialchars($movimiento['comprobante_cod']); ?></td>
                    <td><?php echo htmlspecialchars($movimiento['destino']); ?></td>
                    <td>
                        <button type="button" class="btn btn-info btn-sm" data-toggle="modal" data-target="#viewDetailsModal" data-movimiento-id="<?php echo htmlspecialchars($movimiento['id']); ?>">
                            Ver Detalle
                        </button>
                    </td>
                    <td><?php echo htmlspecialchars($movimiento['fecha']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <th>ID</th>
                    <th>Tipo de Movimiento</th>
                    <th>Código de Comprobante</th>
                    <th>Destino</th>
                    <th>Detalle del Movimiento</th>
                    <th>Fecha del Movimiento</th>
                </tr>
            </tfoot>
        </table>

        </div>

        <!-- Paginación -->
        <div class="pagination" id="pagination"></div>
    </div>
</div>

<!-- Modal para ver detalles del movimiento -->
<div class="modal fade" id="viewDetailsModal" tabindex="-1" role="dialog" aria-labelledby="viewDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewDetailsModalLabel">Detalles del Movimiento</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="row" style="display: flex; justify-content: space-between">
                            <div class="col-md-6">
                                <p><strong>Código:</strong> <span id="codigo"></span></p>
                                <p><strong>Destino:</strong> <span id="destino"></span></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Fecha:</strong> <span id="fecha"></span></p>
                                <p><strong>Observación:</strong> <span id="observacion"></span></p>
                            </div>
                        </div>
                    </div>
                    <table class="table table-bordered" id="detailsTable">
                        <thead>
                            <tr>
                                <th class="custom-column">Artículo</th>
                                <th>Marca</th>
                                <th>Categoría</th>
                                <th>Stock</th>
                            </tr>
                        </thead>
                        <tbody id="detailsTableBody">
                            <!-- Los detalles se cargarán aquí con JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>


<!-- Modal para agregar un nuevo movimiento -->
<div class="modal fade" id="addMovementModal" tabindex="-1" role="dialog" aria-labelledby="addMovementModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl-custom" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addMovementModalLabel">Nuevo Movimiento</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="addMovementForm" action="../dashboard_pages/movementsxstorage/add_movementxstorage.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="deposito_id" id="modalDepositoId" value="<?php echo htmlspecialchars($deposito_id); ?>">


                        <div class="row" style="justify-content: space-around">
                            <!-- Columna 1: Tipo de Movimiento y Destino -->
                            <div class="col-md-4">
                                <!-- Tipo de Movimiento -->
                                <div class="form-group">
                                    <label for="movementType">Tipo de Movimiento</label>
                                    <select class="form-control" id="movementType" name="movimiento_tipo_id" required>
                                        <?php foreach ($tiposMovimiento as $tipo): ?>
                                            <option value="<?php echo htmlspecialchars($tipo['id']); ?>">
                                                <?php echo htmlspecialchars($tipo['nombre']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Destino -->
                                <div class="form-group" id="destinationGroup" style="display: none;">
                                    <label for="destination">Destino</label>
                                    <div style="position: relative; width: 100%;">
                                        <input style="width: 100%;" type="text" id="autocomplete-input" placeholder="Escribe o selecciona un destino...">
                                        <input type="hidden" name="destino" id="destinationHiddenInput"> <!-- Campo oculto para enviar el destino -->
                                        <div id="autocomplete-list" class="autocomplete-list" style="display: none;"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Columna 2: Fecha del Movimiento -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="movementDate">Fecha del Movimiento</label>
                                    <?php
                                        // Generar la fecha actual en el formato YYYY-MM-DD
                                        $today = date('Y-m-d');
                                    ?>
                                    <input type="date" class="form-control" id="movementDate" name="fecha" max="<?php echo $today; ?>" required>
                                </div>

                                <!-- Campo de Observación -->
                                <div class="form-group">
                                    <label for="observation">Observación</label>
                                    <textarea class="form-control" id="observation" name="observacion" rows="4" style="resize: none; overflow-y: auto; height: 70px;" placeholder="Escribe una observación (opcional)"></textarea>
                                </div>
                            </div>
                        </div>



                    <div class="table-responsive">
                        <table class="table table-bordered" id="movementsTable">
                            <thead>
                                <tr>
                                    <th>Artículo</th>
                                    <th>Marca</th>
                                    <th>Categoría</th>
                                    <th>Stock</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- No hay filas iniciales -->
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="5" class="text-center">
                                        <button type="button" class="btn btn-success btn-sm" id="addRowButton">
                                            <i class="fa fa-plus"></i> Agregar Artículo
                                        </button>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" id="cancelButton" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Completar Movimiento</button>
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
    document.addEventListener('DOMContentLoaded', function() {
        const articulosDisponibles = <?php echo json_encode($articulosDisponibles); ?>;
        const articulosPorDeposito = <?php echo $articulosPorDepositoJson; ?>;
        let currentArticulos = articulosDisponibles;
        // Data from PHP to JavaScript
        const destinos = <?php echo $destinosJson; ?>;
        
        // Extract destination names and ids
        const destinationItems = destinos.map(destino => ({
            value: `${destino.deposito_nombre}-${destino.sucursal_nombre}`,
            text: `${destino.deposito_nombre} - ${destino.sucursal_nombre}`
        }));

        const input = document.getElementById('autocomplete-input');
        const list = document.getElementById('autocomplete-list');

        function updateList(value) {
            list.innerHTML = '';
            const filteredItems = value === '' 
                ? destinationItems 
                : destinationItems.filter(item => item.text.toLowerCase().includes(value.toLowerCase()));
            
            if (filteredItems.length > 0) {
                list.style.display = 'block';
                filteredItems.forEach(item => {
                    const div = document.createElement('div');
                    div.classList.add('autocomplete-item');
                    div.textContent = item.text;
                    div.addEventListener('click', function() {
                        input.value = item.text;
                        list.style.display = 'none';
                    });

                    list.appendChild(div);
                });
            } else {
                list.style.display = 'none';
            }
        }

        input.addEventListener('input', function() {
            updateList(input.value);
        });

        input.addEventListener('focus', function() {
            updateList(input.value);
        });

        document.addEventListener('click', function(event) {
            if (!input.contains(event.target) && !list.contains(event.target)) {
                list.style.display = 'none';
            }
        });

        // Show the destination group based on movement type selection
        document.getElementById('movementType').addEventListener('change', function() {
            const selectedType = this.value;
            const destinoGroup = document.getElementById('destinationGroup');

            if (selectedType == '2') { // '2' is the ID for "egreso"
                destinoGroup.style.display = 'block';
            } else {
                destinoGroup.style.display = 'none';
            }
        });
        //Copiar el valor del input al input oculto para mandar al backend
        document.getElementById('addMovementForm').addEventListener('submit', function(event) {
            const autocompleteInputValue = document.getElementById('autocomplete-input').value;
            document.getElementById('destinationHiddenInput').value = autocompleteInputValue;
        });

        // Mostrar mensaje de éxito si se ha establecido en la sesión
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

        // Función para actualizar las opciones disponibles en el dropdown
        function updateOptions() {
            const selectedIds = Array.from(document.querySelectorAll('input[name="articulo_id[]"]')).map(input => input.value);

            document.querySelectorAll('.custom-select-wrapper').forEach(selectWrapper => {
                const dropdown = selectWrapper.querySelector('.custom-select-dropdown');
                const options = dropdown.querySelectorAll('.option');
                const hiddenInput = selectWrapper.querySelector('input[name="articulo_id[]"]').value;

                options.forEach(option => {
                    const value = option.dataset.value;
                    if (selectedIds.includes(value) && hiddenInput !== value) {
                        option.style.display = 'none';
                    } else {
                        option.style.display = '';
                    }
                });
            });
        }

        // Función para limpiar la tabla y campos del modal al hacer clic en el botón cancelar
        $('#addMovementModal').on('hidden.bs.modal', function () {
            const form = document.getElementById('addMovementForm');
            const tableBody = document.getElementById('movementsTable').getElementsByTagName('tbody')[0];
            const destinoGroup = document.getElementById('destinationGroup');
            destinoGroup.style.display = 'none';
            // Limpiar el formulario
            form.reset();

            // Limpiar las filas de la tabla
            tableBody.innerHTML = '';
        });

        // Función para actualizar la información cuando se selecciona un artículo
        function updateRowInfo(row) {
        const articuloSelect = row.querySelector('.custom-select-search');
        const categoriaField = row.querySelector('input[name="categoria[]"]');
        const marcaField = row.querySelector('input[name="marca[]"]');
        const stockField = row.querySelector('input[name="stock[]"]');

        const selectedArtId = row.querySelector('input[name="articulo_id[]"]').value;
        const articulo = currentArticulos.find(a => a.id == selectedArtId);

        if (articulo) {
            categoriaField.value = articulo.categoria_nombre || '';
            marcaField.value = articulo.marca_nombre || '';
            stockField.disabled = false; // Enable stock input
            if (currentArticulos === articulosPorDeposito) {
                const stockDisponible = articulo.stock - articulo.stock_minimo;
                stockField.setAttribute('max', stockDisponible);
            }
        } else {
            categoriaField.value = '';
            marcaField.value = '';
            stockField.disabled = true; // Disable stock input if no article is selected
            if (currentArticulos === articulosPorDeposito) {
                stockField.removeAttribute('max');
            }
        }
    }


        // Función para agregar una nueva fila a la tabla
        document.getElementById('addRowButton').addEventListener('click', function() {
            const table = document.getElementById('movementsTable').getElementsByTagName('tbody')[0];
            const newRow = table.insertRow();

            newRow.innerHTML = 
                `<td class="custom-column">
                    <div class="custom-select-wrapper">
                        <input type="hidden" name="articulo_id[]">
                        <input type="text" class="custom-select-search form-control" placeholder="Buscar Artículo">
                        <div class="custom-select-dropdown">
                            ${currentArticulos.map(articulo => `
                                <div class="option" 
                                    data-value="${articulo.id}" 
                                    data-marca="${articulo.marca_nombre}"
                                    data-categoria="${articulo.categoria_nombre}"
                                    data-stock="${articulo.stock}"
                                    data-stock-minimo="${articulo.stock_minimo}">
                                    ${articulo.nombre} - ${articulo.marca_nombre} - ${articulo.categoria_nombre}
                                </div>
                            `).join('')}
                        </div>
                    </div>
                </td>
                <td>
                    <input type="text" class="form-control" name="marca[]" readonly>
                </td>
                <td>
                    <input type="text" class="form-control" name="categoria[]" readonly>
                </td>
                <td>
                    <input type="number" class="form-control" name="stock[]" required min="1" disabled>
                </td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm remove-row">
                        <i class="fa fa-trash-alt"></i>
                    </button>
                </td>`;

            // Set up the custom select in the new row
            const newSelectWrapper = newRow.querySelector('.custom-select-wrapper');
            setupCustomSelect(newSelectWrapper);

            // Add event listener to remove row
            newRow.querySelector('.remove-row').addEventListener('click', function() {
                newRow.remove();
                updateOptions(); // Update available options after removing a row
            });

            // Enable stock input when an article is selected
            const stockInput = newRow.querySelector('input[name="stock[]"]');
            newSelectWrapper.addEventListener('change', function() {
                stockInput.disabled = !hiddenInput.value; // Enable if an article is selected
            });

            // Update available options
            updateOptions();
        });


        // Inicializar el campo de fecha con la fecha actual
        function setDateMax() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('movementDate').setAttribute('max', today);
        }

        // Configurar el campo de fecha cuando el modal se muestra
        $('#addMovementModal').on('show.bs.modal', function () {
            setDateMax();
        });

        // Función para configurar el select personalizado
        function setupCustomSelect(selectWrapper) {
            const searchInput = selectWrapper.querySelector('.custom-select-search');
            const dropdown = selectWrapper.querySelector('.custom-select-dropdown');
            const hiddenInput = selectWrapper.querySelector('input[name="articulo_id[]"]');
            const options = Array.from(selectWrapper.querySelectorAll('.option'));

            function filterOptions() {
                const filter = searchInput.value.toLowerCase();
                options.forEach(option => {
                    if (option.textContent.toLowerCase().includes(filter)) {
                        option.style.display = '';
                    } else {
                        option.style.display = 'none';
                    }
                });
            }

            function selectOption(option) {
                hiddenInput.value = option.dataset.value;
                searchInput.value = option.textContent.trim(); // Use trim() to remove extra spaces
                dropdown.style.display = 'none';
                const row = selectWrapper.closest('tr');
                updateRowInfo(row); // Actualizar la información de la fila seleccionada
                updateOptions(); // Filtrar las opciones disponibles después de seleccionar
            }

            searchInput.addEventListener('input', filterOptions);

            options.forEach(option => {
                option.addEventListener('click', () => selectOption(option));
            });

            searchInput.addEventListener('focus', () => dropdown.style.display = 'block');

            document.addEventListener('click', function(event) {
                if (!selectWrapper.contains(event.target)) {
                    dropdown.style.display = 'none';
                }
            });
        }

        // Inicializar select personalizados existentes
        document.querySelectorAll('.custom-select-wrapper').forEach(setupCustomSelect);

        // Re-inicializar select personalizados en filas nuevas
        document.getElementById('addRowButton').addEventListener('click', function() {
            setTimeout(() => {
                document.querySelectorAll('.custom-select-wrapper').forEach(setupCustomSelect);
            }, 0);
        });

        // Actualizar los artículos mostrados según el tipo de movimiento seleccionado
        document.getElementById('movementType').addEventListener('change', function() {
            const selectedType = this.value;
            
            if (selectedType == 2) {
                currentArticulos = articulosPorDeposito;
            } else {
                currentArticulos = articulosDisponibles;
            }

            // Actualizar todas las filas en la tabla con los nuevos artículos
            document.querySelectorAll('.custom-select-wrapper').forEach(selectWrapper => {
                const dropdown = selectWrapper.querySelector('.custom-select-dropdown');
                const options = dropdown.querySelectorAll('.option');
                
                options.forEach(option => option.remove());

                currentArticulos.forEach(articulo => {
                    const option = document.createElement('div');
                    option.className = 'option';
                    option.dataset.value = articulo.id;
                    option.dataset.marca = articulo.marca_nombre;
                    option.dataset.categoria = articulo.categoria_nombre;
                    option.dataset.stock = articulo.stock;
                    option.dataset.stockMinimo = articulo.stock_minimo;
                    option.textContent = `${articulo.nombre} - ${articulo.marca_nombre} - ${articulo.categoria_nombre}`;
                    
                    dropdown.appendChild(option);
                });

                setupCustomSelect(selectWrapper);
            });

            // Limpiar las filas de la tabla si el tipo de movimiento cambia
            document.getElementById('movementsTable').getElementsByTagName('tbody')[0].innerHTML = '';
        });
    });
</script>


<script>
    $('#viewDetailsModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget); // Botón que abrió el modal
        var movimientoId = button.data('movimiento-id'); // Extraer el ID del movimiento

        var modal = $(this);
        var detailsTableBody = modal.find('#detailsTableBody');

        // Limpiar la tabla antes de cargar nuevos datos
        detailsTableBody.empty();
        
        // Solicitar la información de la cabecera del movimiento al servidor
        $.ajax({
            url: '../dashboard_pages/movementsxstorage/get_movement_header.php',
            type: 'GET',
            data: { movimiento_id: movimientoId },
            success: function(response) {
                try {
                    var data = JSON.parse(response);

                    if (data.error) {
                        console.error(data.error);
                        modal.find('#codigo').text('No disponible');
                        modal.find('#destino').text('No disponible');
                        modal.find('#fecha').text('No disponible');
                        modal.find('#observacion').text('No disponible');
                    } else {
                        modal.find('#codigo').text(data.comprobante_cod || 'No disponible');
                        modal.find('#destino').text(data.destino || 'No disponible');
                        modal.find('#fecha').text(data.fecha || 'No disponible');
                        modal.find('#observacion').text(data.observacion || 'No disponible');
                    }
                } catch (e) {
                    console.error('Error al procesar los datos de la cabecera:', e);
                    modal.find('#codigo').text('Error al cargar');
                    modal.find('#destino').text('Error al cargar');
                    modal.find('#fecha').text('Error al cargar');
                    modal.find('#observacion').text('Error al cargar');
                }
            },
            error: function() {
                modal.find('#codigo').text('Error en la solicitud');
                modal.find('#destino').text('Error en la solicitud');
                modal.find('#fecha').text('Error en la solicitud');
                modal.find('#observacion').text('Error en la solicitud');
            }
        });
        // Solicitar detalles del movimiento al servidor
        $.ajax({
            url: '../dashboard_pages/movementsxstorage/get_movement_details.php',
            type: 'GET', // Cambiado a GET para que coincida con el script PHP
            data: { movimiento_id: movimientoId },
            success: function(response) {
                try {
                    // Supongamos que la respuesta es un JSON
                    var data = JSON.parse(response);

                    // Verificar si se recibieron datos válidos
                    if (Array.isArray(data) && data.length) {
                        // Rellenar la tabla con los detalles
                        data.forEach(function(item) {
                            // Desglosar la descripción en partes
                            var descripcionParts = item.articulo_descripcion.split(' - ');
                            
                            // Verificar que la descripción tenga las partes esperadas
                            if (descripcionParts.length === 3) {
                                var nombre = descripcionParts[0];
                                var marca = descripcionParts[1];
                                var categoria = descripcionParts[2];
                                
                                // Agregar una fila con los detalles desglosados
                                detailsTableBody.append(
                                    `<tr>
                                        <td>${nombre}</td>
                                        <td>${marca}</td>
                                        <td>${categoria}</td>
                                        <td>${item.stock}</td>
                                    </tr>`
                                );
                            } else {
                                // En caso de que la descripción no tenga las partes esperadas
                                detailsTableBody.append(
                                    `<tr>
                                        <td colspan="5">Descripción del artículo no válida.</td>
                                    </tr>`
                                );
                            }
                        });
                    } else {
                        detailsTableBody.append(
                            `<tr>
                                <td colspan="5">No hay detalles disponibles.</td>
                            </tr>`
                        );
                    }
                } catch (e) {
                    console.error('Error al procesar los datos:', e);
                    detailsTableBody.append(
                        `<tr>
                            <td colspan="5">Error al cargar detalles.</td>
                        </tr>`
                    );
                }
            },
            error: function() {
                detailsTableBody.append(
                    `<tr>
                        <td colspan="5">Error en la solicitud.</td>
                    </tr>`
                );
            }
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        // Función para configurar el campo de fecha del modal
        function setDateMax() {
            const today = new Date().toISOString().split('T')[0];
            const dateInput = document.getElementById('movementDate');
            if (dateInput) {
                dateInput.setAttribute('max', today);
            }
        }

        // Configurar el campo de fecha cuando el modal se muestra
        $('#addMovementModal').on('show.bs.modal', function () {
            setDateMax();
        });
    });
</script>



</body>
</html>

