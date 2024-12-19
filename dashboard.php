<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit();
}

$user = $_SESSION['user'];

$servername = "sql106.infinityfree.com";
$username = "if0_37317346";
$password = "Laapetitosa44";
$dbname = "if0_37317346_apetitosa";
$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);

// Obtener la imagen del usuario
$id_usuario = $user['id']; 
$stmt = $conn->prepare("SELECT imagen_usuario FROM usuarios WHERE id = :id");
$stmt->bindParam(":id", $id_usuario);
$stmt->execute();
$datos_usuario = $stmt->fetch(PDO::FETCH_ASSOC);
$imagen_usuario = $datos_usuario['imagen_usuario']; 


// Verificar si mostrar el mensaje de bienvenida
$mostrar_bienvenida = isset($_SESSION['mostrar_bienvenida']) && $_SESSION['mostrar_bienvenida'];
// Función para incluir el archivo de página solicitado
function cargarPagina($pagina) {
  $ruta = "./dashboard_pages/" . $pagina . ".php";
  if (file_exists($ruta)) {
      include $ruta;
  } else {
      echo "<h1>Página no encontrada</h1>";
  }
}

// Determinar la página solicitada
$pagina = isset($_GET['page']) ? $_GET['page'] : 'index';


?>



<!DOCTYPE html>
<!--
This is a starter template page. Use this page to start your new project from
scratch. This page gets rid of all links and provides the needed markup only.
-->
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sistema de Inventarios</title>
    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="../ims/plugins/fontawesome-free/css/all.min.css">
    <!-- AdminLTE -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
    
    <link rel="stylesheet" href="../ims/css/dashboard.css">

    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.5/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.0/css/buttons.bootstrap4.min.css">

    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAD0x45tSzsvzyX8hxzk6EiGQ8hCV9smD0&libraries=places"></script>
    <script src="https://kit.fontawesome.com/54b6794846.js" crossorigin="anonymous"></script>
    
    <style>
        #Table td, #Table th {
            text-align: center; /* Centra el texto horizontalmente */
        }

        /* Si estás utilizando la clase DataTables para las tablas, también aplica estilos específicos */
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            text-align: center; /* Centra el texto de los botones de paginación si es necesario */
        }
        .dt-buttons .btn {
            border: none;
            color: #fff;
            font-size: 14px;
            padding: 6px 7px;
            margin: 2px;
            border-radius: 4px;
        }
        .btn-copy {
            background-color: #808080; /* Verde */
        }
        .btn-copy i {
            margin-right: 5px;
        }
        .btn-csv {
            background-color: #FF964F; /* Verde similar al de Excel */
        }
        .btn-csv i {
            margin-right: 5px;
        }
        .btn-excel {
            background-color: #4CAF50; /* Verde */
        }
        .btn-excel i {
            margin-right: 5px;
        }
        .btn-pdf {
            background-color: #f44336; /* Rojo */
        }
        .btn-pdf i {
            margin-right: 5px;
        }
        .btn-print {
            background-color: #2196F3; /* Azul */
        }
        .btn-print i {
            margin-right: 5px;
        }
        .btn-colvis {
            background-color: #607D8B; /* Gris */
        }
        .btn-colvis i {
            margin-right: 5px;
        }
    </style>
</head>
<body class="hold-transition sidebar-mini">
 <!-- Script para mostrar el mensaje de bienvenida -->
 <?php if ($mostrar_bienvenida): ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        Swal.fire({
            position: "top-end",
            icon: "success",
            title: "Bienvenido al Sistema<br><?= htmlspecialchars($user['nombre']) ?><br><?= htmlspecialchars($user['apellido']) ?>",
            showConfirmButton: false,
            timer: 1500
        });
    </script>
    <?php 
    unset($_SESSION['mostrar_bienvenida']); // Elimina la variable de sesión para que el mensaje de bienvenida no se muestre en recargas de la página
    endif; ?>
<div class="wrapper">

  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light fixed-top">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
      <a href="?page=index" class="nav-link <?php echo $pagina === 'index'; ?>">Inicio</a>
      </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto" >
      <li class="nav-item">
        <a class="nav-link" data-widget="fullscreen" href="#" role="button">
          <i class="fas fa-expand-arrows-alt"></i>
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" data-widget="control-sidebar" data-slide="true" href="#" role="button">
          <i class="fas fa-th-large"></i>
        </a>
      </li>
    </ul>
  </nav>
  <!-- /.navbar -->

  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4" style="position:fixed">
    <!-- Brand Logo -->
    <a href="?page=index" class="brand-link <?php echo $pagina === 'index'; ?>">
      <img src="https://i.ibb.co/bbcDcNJ/logo.png" alt="SMI Logo" class="brand-image img-circle elevation-3" style="opacity: .8">
      <span class="brand-text font-weight-light" style="font-size: 15px">Sistema de Inventarios</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar user panel (optional) -->
      <div class="user-panel mt-3 pb-3 mb-3 d-flex" style="justify-content:center">
        <div >
        <?php if (!empty($imagen_usuario)) { ?>
                    <img style="width:60px; height:60px"src="data:image/jpeg;base64,<?= base64_encode($imagen_usuario) ?>" alt="Imagen de Usuario"/>
                <?php } else { ?>
                    <img src="images/userImage1.png" alt="Imagen de Usuario por defecto"/>
                <?php } ?>
        </div>
        <div class="info">
        <a><?= htmlspecialchars($user['nombre']) ?><br> <?= htmlspecialchars($user['apellido']) ?></a>
        </div>
      </div>


      <!-- SidebarSearch Form -->
      <div class="form-inline">
        <div class="input-group" data-widget="sidebar-search">
          <input class="form-control form-control-sidebar" type="search" placeholder="Buscar" aria-label="Search">
          <div class="input-group-append">
            <button class="btn btn-sidebar" style="margin-top:-.5px">
              <i class="fas fa-search fa-fw"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
          <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
               <li class="nav-item has-treeview <?= ($pagina === 'index')  ? 'menu-open' : '' ?>">
               <a href="#" class="nav-link <?= ($pagina === 'index') ? 'active' : '' ?>">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>
                Dashboard
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="?page=index" class="nav-link <?php echo $pagina === 'index' ? 'active' : ''; ?>">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Pagina Inicial</p>
                </a>
              </li>
            </ul>
          </li>
          <li class="nav-item has-treeview <?= ($pagina === 'rol_list') || ($pagina === 'user_list') ? 'menu-open' : '' ?>">
            <a href="#" class="nav-link <?= ($pagina === 'rol_list') || ($pagina === 'user_list') ? 'active' : '' ?>">
            <i class="fa-solid fa-users-gear" style="margin:5px"></i>
              <p>
                Gestión de Usuarios
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="?page=rol_list" class="nav-link <?php echo $pagina === 'rol_list' ? 'active' : ''; ?>">
                  <i class="fa-solid fa-user-tie" style="margin:5px 5px"></i>
                  <p>Lista de Roles</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="?page=user_list" class="nav-link <?php echo $pagina === 'user_list' ? 'active' : ''; ?>">
                <i class="fa-solid fa-address-book" style="margin:5px"></i>
                <p>Lista de Usuarios</p>
                </a>
              </li>
            </ul>
          </li>
          <li class="nav-item">
            <a href="?page=branch_list" class="nav-link <?php echo $pagina === 'branch_list' ? 'active' : ''; ?>">
            <i class="fa-solid fa-building" style="margin:6px"></i>
              <p>Gestión de Sucursales</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="?page=storage_list" class="nav-link <?php echo $pagina === 'storage_list'||$pagina === 'storagexbranch'||$pagina === 'articlexstorage'||$pagina === 'movementsxstorage' ? 'active' : ''; ?>">
            <i class="fa-solid fa-warehouse" style="margin:3px"></i>
              <p>Gestión de Depósitos</p>
            </a>
          </li>
          <li class="nav-item has-treeview <?= ($pagina === 'article_list') || ($pagina === 'brand_list') || ($pagina === 'category_list') ? 'menu-open' : '' ?>">
            <a href="#" class="nav-link <?= ($pagina === 'article_list') || ($pagina === 'brand_list') || ($pagina === 'category_list') ? 'active' : '' ?>">
            <i class="fa-solid fa-boxes-stacked" style="margin:5px"></i>
              <p>
                Gestión de Artículos
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="?page=article_list" class="nav-link <?php echo $pagina === 'article_list' ? 'active' : ''; ?>">
                <i class="fa-solid fa-box" style="margin:5px"></i>
                    <p>Lista de Artículos</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="?page=brand_list" class="nav-link <?php echo $pagina === 'brand_list' ? 'active' : ''; ?>">
                <i class="fa-brands fa-bandcamp" style="margin:5px"></i>
                <p>Lista de Marcas</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="?page=category_list" class="nav-link <?php echo $pagina === 'category_list' ? 'active' : ''; ?>">
                <i class="fa-solid fa-cookie-bite" style="margin:5px"></i></i>
                <p>Lista de Categorías</p>
                </a>
              </li>
            </ul>
          </li>
          <li class="nav-item" style="position: fixed; bottom: 5px; background-color: #C63637; border-radius: 5px">
            <a class="nav-link" onclick="mostrarMensajeSalida()" href="#">
              <i class="fa fa-power-off" style="margin:5px"></i>
              <p >Salir</p>
            </a>
          </li>
        </ul>
      </nav>
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>

 <!-- Content Wrapper. Contains page content -->
    <?php cargarPagina($pagina); ?>
  <!-- /.content-wrapper -->


  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
    <div class="p-3">
      <h5>Herramientas</h5>
      <p>Aqui se mostraran herramientas de navegacion</p>
    </div>
  </aside>
  <!-- /.control-sidebar -->

 
</div>
<!-- ./wrapper -->
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap 4 -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- AdminLTE App -->
<script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- DataTables & Plugins -->
<script src="https://cdn.datatables.net/1.13.5/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.5/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.0/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.0/js/buttons.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.0/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.0/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.0/js/buttons.colVis.min.js"></script>
<!-- Script general -->
<script src="../ims/scripts/user_list.js"></script> <!-- Aún necesitarás este archivo local -->
<!-- jQuery Mapael -->
<script src="https://cdn.jsdelivr.net/npm/jquery-mousewheel@3.1.13/jquery.mousewheel.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/raphael@2.3.0/raphael.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-mapael@2.2.0/js/jquery.mapael.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-mapael@2.2.0/maps/usa_states.min.js"></script>
<!-- ChartJS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js"></script>
<!-- CSS de Select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />
<!-- JS de Select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>

<script>
    var ctx = document.getElementById('salesChart').getContext('2d');
    var salesChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['January', 'February', 'March', 'April', 'May', 'June', 'July'],
            datasets: [{
                label: 'Sales',
                data: [65, 59, 80, 81, 56, 55, 40],
                backgroundColor: 'rgba(60,141,188,0.9)',
                borderColor: 'rgba(60,141,188,0.8)',
                pointRadius: false,
                pointColor: '#3b8bba',
                pointStrokeColor: 'rgba(60,141,188,1)',
                pointHighlightFill: '#fff',
                pointHighlightStroke: 'rgba(60,141,188,1)',
            }]
        },
        options: {
            maintainAspectRatio: false,
            responsive: true,
        }
    });
    
</script>
<script>
  $(function () {
            $("#Table").DataTable({
                "responsive": true,
                "lengthChange": false,
                "autoWidth": false,
                "buttons": [
                    {
                        extend: 'copy',
                        text: '<i class="fas fa-copy"></i> Copiar',
                        className: 'btn btn-copy',
                        titleAttr: 'Copiar'
                    },
                    {
                        extend: 'csv',
                        text: '<i class="fas fa-file-csv"></i> CSV',
                        className: 'btn btn-csv',
                        titleAttr: 'Exportar CSV'
                    },
                    {
                        extend: 'excel',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        className: 'btn btn-excel',
                        titleAttr: 'Exportar Excel'
                    },
                    {
                        extend: 'pdf',
                        text: '<i class="fas fa-file-pdf"></i> PDF',
                        className: 'btn btn-pdf',
                        titleAttr: 'Exportar PDF'
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print"></i> Imprimir',
                        className: 'btn btn-print',
                        titleAttr: 'Imprimir'
                    },
                    {
                        extend: 'colvis',
                        text: '<i class="fas fa-columns"></i> Visibilidad de columnas',
                        className: 'btn btn-colvis',
                        titleAttr: 'Visibilidad de columnas'
                    }
                ],
                "language": {
                    "search": "Buscar:",
                    "lengthMenu": "Mostrar _MENU_ entradas",
                    "info": "Mostrando _START_ a _END_ de _TOTAL_ entradas",
                    "infoEmpty": "Mostrando 0 a 0 de 0 entradas",
                    "infoFiltered": "(filtrado de _MAX_ entradas totales)",
                    "infoPostFix": "",
                    "loadingRecords": "Cargando...",
                    "zeroRecords": "No se encontraron resultados",
                    "emptyTable": "No hay datos disponibles en la tabla",
                    "paginate": {
                        "first": "Primero",
                        "last": "Último",
                        "next": "Siguiente",
                        "previous": "Anterior"
                    },
                }
            }).buttons().container().appendTo('#table-wrapper .col-md-6:eq(0)');
        });
</script>
<script>
  function mostrarMensajeSalida() {
        let timerInterval;
        Swal.fire({
            title: "Cerrando Sesión",
            html: "",
            timer: 1000,
            timerProgressBar: true,
            didOpen: () => {
                Swal.showLoading();
                timerInterval = setInterval(() => {
                    const timer = Swal.getHtmlContainer().querySelector("b");
                    if (timer) {
                        timer.textContent = Swal.getTimerLeft();
                    }
                }, 100);
            },
            willClose: () => {
                clearInterval(timerInterval);
            }
        }).then((result) => {
            if (result.dismiss === Swal.DismissReason.timer) {
                window.location.href = "logout.php"; // Redirige al usuario a la página de cierre de sesión
            }
        });
    }
</script>
<script>
    $(document).ready(function(){
        // Inicializar todos los tooltips
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>
<script>
        function initMap() {
            // Opciones del mapa
            var mapOptions = {
                center: { lat: -24.7820, lng: -65.4232 },
                zoom: 12
            };

            // Crear el mapa para agregar sucursal
            var mapAdd = new google.maps.Map(document.getElementById('mapAdd'), mapOptions);
            var autocompleteAdd = new google.maps.places.Autocomplete(document.getElementById('newBranchAddress'));
            autocompleteAdd.bindTo('bounds', mapAdd);
            var markerAdd = new google.maps.Marker({
                map: mapAdd,
                anchorPoint: new google.maps.Point(0, -29)
            });

            // Crear el mapa para editar sucursal
            var mapEdit = new google.maps.Map(document.getElementById('mapEdit'), mapOptions);
            var autocompleteEdit = new google.maps.places.Autocomplete(document.getElementById('editBranchAddress'));
            autocompleteEdit.bindTo('bounds', mapEdit);
            var markerEdit = new google.maps.Marker({
                map: mapEdit,
                anchorPoint: new google.maps.Point(0, -29)
            });

            // Crear el servicio de Geocoding
            var geocoder = new google.maps.Geocoder();

            // Manejar el autocompletado para agregar sucursal
            autocompleteAdd.addListener('place_changed', function() {
                var place = autocompleteAdd.getPlace();

                if (!place.geometry) {
                    document.getElementById('newBranchAddress').placeholder = 'Introduce una dirección';
                } else {
                    if (place.geometry.viewport) {
                        mapAdd.fitBounds(place.geometry.viewport);
                    } else {
                        mapAdd.setCenter(place.geometry.location);
                        mapAdd.setZoom(17);
                    }

                    markerAdd.setPosition(place.geometry.location);
                    markerAdd.setVisible(true);
                    document.getElementById('newBranchAddress').value = place.formatted_address;
                }
            });

            // Manejar el autocompletado para editar sucursal
            autocompleteEdit.addListener('place_changed', function() {
                var place = autocompleteEdit.getPlace();

                if (!place.geometry) {
                    document.getElementById('editBranchAddress').placeholder = 'Introduce una dirección';
                } else {
                    if (place.geometry.viewport) {
                        mapEdit.fitBounds(place.geometry.viewport);
                    } else {
                        mapEdit.setCenter(place.geometry.location);
                        mapEdit.setZoom(17);
                    }

                    markerEdit.setPosition(place.geometry.location);
                    markerEdit.setVisible(true);
                    document.getElementById('editBranchAddress').value = place.formatted_address;
                }
            });

            // Manejar clic en el mapa para agregar sucursal
            mapAdd.addListener('click', function(event) {
                var latLng = event.latLng;

                geocoder.geocode({ location: latLng }, function(results, status) {
                    if (status === 'OK') {
                        if (results[0]) {
                            markerAdd.setPosition(latLng);
                            markerAdd.setVisible(true);
                            document.getElementById('newBranchAddress').value = results[0].formatted_address;
                        } else {
                            window.alert('No se encontraron resultados');
                        }
                    } else {
                        window.alert('Geocoder falló debido a: ' + status);
                    }
                });
            });

            // Manejar clic en el mapa para editar sucursal
            mapEdit.addListener('click', function(event) {
                var latLng = event.latLng;

                geocoder.geocode({ location: latLng }, function(results, status) {
                    if (status === 'OK') {
                        if (results[0]) {
                            markerEdit.setPosition(latLng);
                            markerEdit.setVisible(true);
                            document.getElementById('editBranchAddress').value = results[0].formatted_address;
                        } else {
                            window.alert('No se encontraron resultados');
                        }
                    } else {
                        window.alert('Geocoder falló debido a: ' + status);
                    }
                });
            });

            // Inicializar las direcciones si existen
            initializeAddress(document.getElementById('newBranchAddress'), mapAdd, markerAdd);
            initializeAddress(document.getElementById('editBranchAddress'), mapEdit, markerEdit);
        }

        function initializeAddress(addressField, map, marker) {
            var address = addressField.value;
            if (address) {
                var geocoder = new google.maps.Geocoder();
                geocoder.geocode({ address: address }, function(results, status) {
                    if (status === 'OK') {
                        map.setCenter(results[0].geometry.location);
                        marker.setPosition(results[0].geometry.location);
                        marker.setVisible(true);
                    }
                });
            }
        }

        // Inicializar el mapa cuando la página se carga
        window.onload = initMap;
</script>
<!--<script>
    function initMap() {
        var mapOptions = {
            center: { lat: -24.7820, lng: -65.4232 },  // Coordenadas iniciales
            zoom: 12
        };

        // Crear el mapa para agregar proveedor
        var mapAddSupplier = new google.maps.Map(document.getElementById('mapAdd'), mapOptions);
        var autocompleteAddSupplier = new google.maps.places.Autocomplete(document.getElementById('newSupplierAddress'));
        autocompleteAddSupplier.bindTo('bounds', mapAddSupplier);
        var markerAddSupplier = new google.maps.Marker({
            map: mapAddSupplier,
            anchorPoint: new google.maps.Point(0, -29)
        });

        // Crear el mapa para editar proveedor
        var mapEditSupplier = new google.maps.Map(document.getElementById('mapEdit'), mapOptions);
        var autocompleteEditSupplier = new google.maps.places.Autocomplete(document.getElementById('editSupplierAddress'));
        autocompleteEditSupplier.bindTo('bounds', mapEditSupplier);
        var markerEditSupplier = new google.maps.Marker({
            map: mapEditSupplier,
            anchorPoint: new google.maps.Point(0, -29)
        });

        // Crear el servicio de Geocoding
        var geocoder = new google.maps.Geocoder();

        // Autocompletado para agregar proveedor
        autocompleteAddSupplier.addListener('place_changed', function() {
            var place = autocompleteAddSupplier.getPlace();

            if (!place.geometry) {
                document.getElementById('newSupplierAddress').placeholder = 'Introduce una dirección';
            } else {
                if (place.geometry.viewport) {
                    mapAddSupplier.fitBounds(place.geometry.viewport);
                } else {
                    mapAddSupplier.setCenter(place.geometry.location);
                    mapAddSupplier.setZoom(17);
                }

                markerAddSupplier.setPosition(place.geometry.location);
                markerAddSupplier.setVisible(true);
                document.getElementById('newSupplierAddress').value = place.formatted_address;
            }
        });

        // Autocompletado para editar proveedor
        autocompleteEditSupplier.addListener('place_changed', function() {
            var place = autocompleteEditSupplier.getPlace();

            if (!place.geometry) {
                document.getElementById('editSupplierAddress').placeholder = 'Introduce una dirección';
            } else {
                if (place.geometry.viewport) {
                    mapEditSupplier.fitBounds(place.geometry.viewport);
                } else {
                    mapEditSupplier.setCenter(place.geometry.location);
                    mapEditSupplier.setZoom(17);
                }

                markerEditSupplier.setPosition(place.geometry.location);
                markerEditSupplier.setVisible(true);
                document.getElementById('editSupplierAddress').value = place.formatted_address;
            }
        });

        // Manejar clic en el mapa para agregar proveedor
        mapAddSupplier.addListener('click', function(event) {
            var latLng = event.latLng;

            geocoder.geocode({ location: latLng }, function(results, status) {
                if (status === 'OK') {
                    if (results[0]) {
                        markerAddSupplier.setPosition(latLng);
                        markerAddSupplier.setVisible(true);
                        document.getElementById('newSupplierAddress').value = results[0].formatted_address;
                    } else {
                        window.alert('No se encontraron resultados');
                    }
                } else {
                    window.alert('Geocoder falló debido a: ' + status);
                }
            });
        });

        // Manejar clic en el mapa para editar proveedor
        mapEditSupplier.addListener('click', function(event) {
            var latLng = event.latLng;

            geocoder.geocode({ location: latLng }, function(results, status) {
                if (status === 'OK') {
                    if (results[0]) {
                        markerEditSupplier.setPosition(latLng);
                        markerEditSupplier.setVisible(true);
                        document.getElementById('editSupplierAddress').value = results[0].formatted_address;
                    } else {
                        window.alert('No se encontraron resultados');
                    }
                } else {
                    window.alert('Geocoder falló debido a: ' + status);
                }
            });
        });

        // Inicializar direcciones si ya existen (para editar)
        initializeAddress(document.getElementById('newSupplierAddress'), mapAddSupplier, markerAddSupplier);
        initializeAddress(document.getElementById('editSupplierAddress'), mapEditSupplier, markerEditSupplier);
    }

    function initializeAddress(addressField, map, marker) {
        var address = addressField.value;
        if (address) {
            var geocoder = new google.maps.Geocoder();
            geocoder.geocode({ address: address }, function(results, status) {
                if (status === 'OK') {
                    map.setCenter(results[0].geometry.location);
                    marker.setPosition(results[0].geometry.location);
                    marker.setVisible(true);
                }
            });
        }
    }

    // Inicializar el mapa cuando la página se carga
    window.onload = initMap;
</script>
-->
</body>
</html>
