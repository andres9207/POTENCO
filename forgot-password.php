<?php
include('data/conexion.php');
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <link href="dist/img/favicon.png" rel="shortcut icon">
  <title>Sistema Control de Horas | Password</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <!-- icheck bootstrap -->
  <link rel="stylesheet" href="plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="dist/css/adminlte.css?<?PHP echo time();?>">

  
  <!-- SweetAlert2 -->
  <link rel="stylesheet" href="plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css">
  <!-- Toastr -->
  <link rel="stylesheet" href="plugins/toastr/toastr.min.css">
   <link href="dist/Parsley.js-2.8.1/src/parsley.css?<?php echo time();?>" rel="stylesheet" type="text/css">
  
  <!-- Google Font: Source Sans Pro -->
  <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">
</head>
<body class="hold-transition login-page">
<div class="login-box">
  <div class="login-logo">
    <a href="index.php"><img src="dist/img/Potenco-logo-mini.png" class="img-logo"></a>
  </div>
  <!-- /.login-logo -->
  <div class="card">
    <div class="card-body login-card-body">
      <p class="login-box-msg font-weight-bold">Control de Horas</p>
      <p class="login-box-msg">¿Olvidaste tu contraseña? Aquí puedes recuperarla fácilmente.</p>

      <form  method="post" id="formrecuperar" name="formrecuperar">
        <div class="input-group mb-3">
          <input type="email" class="form-control" placeholder="Ingresa correo electrónico" id="txtrecuperar" required>
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-envelope"></span>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-6">
            <button type="submit" onclick="RECUPERARCONTRASENA()" class="btn btn-primary btn-block">Enviar</button>
          </div>
          <div class="col-6">
            <a href="index.php" class="btn btn-warning btn-block">Iniciar Sesión</a>
          </div>
          <!-- /.col -->
        </div>
      </form>

      
      
    </div>
    <!-- /.login-card-body -->
    <div class="card-footer text-center">
        <img src="<?php echo $logopavas;?>" style="height:25px"><br>
        Copyright © Todos los derechos reservados
    </div>
  </div>
</div>
<!-- /.login-box -->

<!-- jQuery -->
<script src="plugins/jquery/jquery.min.js"></script>
<!-- Bootstrap 4 -->
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>

<!-- SweetAlert2 -->
<script src="plugins/sweetalert2/sweetalert2.min.js?<?php echo time();?>"></script>
<!-- Toastr -->
<script src="plugins/toastr/toastr.min.js"></script>
<script src="dist/Parsley.js-2.8.1/dist/parsley.js?<?php echo time();?>"></script>
<!-- AdminLTE App -->
<script src="dist/js/adminlte.js?<?php echo time();?>"></script>
<script src="llamadaslogin.js?<?php echo time();?>"></script>

</body>
</html>
