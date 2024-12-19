<?php
$servername = "sql106.infinityfree.com";
$username = "if0_37317346";
$password = "Laapetitosa44";
$dbname = "if0_37317346_apetitosa";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Consultas para obtener los datos
    $stmt_products = $conn->query("SELECT COUNT(*) AS count FROM articulos WHERE estado_activo = true");
    $stmt_clients = $conn->query("SELECT COUNT(*) AS count FROM clientes");
    $stmt_employees = $conn->query("SELECT COUNT(*) AS count FROM usuarios");

    $products_count = $stmt_products->fetch(PDO::FETCH_ASSOC)['count'];
    $clients_count = $stmt_clients->fetch(PDO::FETCH_ASSOC)['count'];
    $employees_count = $stmt_employees->fetch(PDO::FETCH_ASSOC)['count'];

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sistema de Inventarios - Dashboard</title>

    <!-- Google Font: Source Sans Pro -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback">
    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="../plugins/fontawesome-free/css/all.min.css">
    <!-- Theme style -->
    <link rel="stylesheet" href="../css/adminlte.min.css">
    <link rel="stylesheet" href="../css/dashboard.css">

    <script src="https://kit.fontawesome.com/54b6794846.js" crossorigin="anonymous"></script>
    <style>
      .info-box {
        display: flex;
        align-items: center;
        padding: 20px; /* Aumenta el padding */
        border-radius: 5px;
        background: #fff;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin-bottom: 20px; /* Aumenta el margen inferior si es necesario */
      }

      .info-box-icon {
        font-size: 2rem; /* Aumenta el tamaño del icono */
        padding: 20px; /* Aumenta el padding del icono */
        border-radius: 50%;
        color: #fff;
        text-align: center;
      }

      .info-box-content {
        margin-left: 15px;
      }

      .info-box-text {
        font-size: 1.2rem; /* Aumenta el tamaño del texto */
        font-weight: bold;
      }

      .info-box-number {
        font-size: 2rem; /* Aumenta el tamaño del número */
        font-weight: bold;
      }

    </style>
</head>
<body class="hold-transition sidebar-mini">
 
<div class="content-wrapper" style="height:100vh;">
    <!-- Content Header (Page header) -->
    <div class="content-header mt-4">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="mt-4">Pagina Inicial</h1>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <section class="content">
      <div class="container-fluid">
        <!-- Info boxes -->
        <div class="row">
          <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box">
              <span class="info-box-icon bg-info elevation-1"><i class="fas fa-cog"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">CPU Traffic</span>
                <span class="info-box-number">
                  10
                  <small>%</small>
                </span>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
          </div>
          <!-- /.col -->
          <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box mb-3">
              <span class="info-box-icon bg-danger elevation-1"><i class="fas fa-shopping-cart"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">Productos</span>
                <span class="info-box-number"><?php echo number_format($products_count); ?></span>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
          </div>
          <!-- /.col -->

          <!-- fix for small devices only -->
          <div class="clearfix hidden-md-up"></div>

          <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box mb-3">
              <span class="info-box-icon bg-success elevation-1"><i class="fas fa-user"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">Clientes</span>
                <span class="info-box-number"><?php echo number_format($clients_count); ?></span>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
          </div>
          <!-- /.col -->
          <div class="col-12 col-sm-6 col-md-3">
            <div class="info-box mb-3">
              <span class="info-box-icon bg-warning elevation-1"><i class="fas fa-users"></i></span>

              <div class="info-box-content">
                <span class="info-box-text">Empleados</span>
                <span class="info-box-number"><?php echo number_format($employees_count); ?></span>
              </div>
              <!-- /.info-box-content -->
            </div>
            <!-- /.info-box -->
          </div>
          <!-- /.col -->
        </div>
        <!-- /.row -->
      </div><!--/. container-fluid -->
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

<!-- REQUIRED SCRIPTS -->

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  // Aquí puedes agregar tus scripts personalizados
</script>
</body>
</html>
