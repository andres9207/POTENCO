<?php 
require_once 'data/conexion.php';
include 'data/db.config.php';
     
//$pass = encrypt("ap2020*",'p4v4sAp');
//echo $pass;
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <link href="dist/img/favicon.png" rel="shortcut icon">
  <title>Sistema Control de Horas | Log in</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <!-- icheck bootstrap -->
  <link rel="stylesheet" href="plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="dist/css/adminlte.css?<?php echo time(); ?>">

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
    <a href="index.php">
      <img src="dist/img/Potenco-logo-mini.png" class="img-logo">
    </a>
  </div>
  <!-- /.login-logo -->
  <div class="card">
    <div class="card-body login-card-body">
      <p class="login-box-msg font-weight-bold">Control de Horas</p>

      <form id="frmlogin" method="post">
        <div class="input-group mb-2">
          <input type="text" class="form-control" placeholder="Correo electrónico o usuario" id="loginmail" required data-parsley-errors-container="#msn-error1" data-parsley-error-message="Debe ingresar correo electrónico o usuario" >
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-envelope"></span>
            </div>
          </div>
        </div>
        <span id="msn-error1"></span>
        
        <div class="input-group mb-2">
          <input type="password" class="form-control" placeholder="Contraseña" id="loginpass" required data-parsley-errors-container="#msn-error2" data-parsley-error-message="Debe ingresar una contraseña">
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-lock"></span>
            </div>
          </div>
        </div>
        <span id="msn-error2"></span>
        <div class="row justify-content-center text-center">
        
          <!-- /.col -->
          <div class="col-12">
            <button type="submit" class="btn btn-primary btn-block" onclick="INICIARSESION()">Iniciar Sesión</button>
          </div>
          <div class="col-12 mt-2">
            <a href="forgot-password.php">Olvidaste tu contraseña?</a>
          </div>
          <!-- /.col -->
        </div>
      </form>

      <div class="social-auth-links text-center d-none ">
        <p>- OR -</p>
        <a href="#" class="btn btn-block btn-primary">
          <i class="fab fa-facebook mr-2"></i> Sign in using Facebook
        </a>
        <a href="#" class="btn btn-block btn-danger">
          <i class="fab fa-google-plus mr-2"></i> Sign in using Google+
        </a>
      </div>
      <!-- /.social-auth-links -->

    
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
<script src="plugins/jquery/jquery.min.js?<?php echo time();?>"></script>
<!-- Bootstrap 4 -->
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js?<?php echo time();?>"></script>
<!-- SweetAlert2 -->
<script src="plugins/sweetalert2/sweetalert2.min.js?<?php echo time();?>"></script>
<!-- Toastr -->
<script src="plugins/toastr/toastr.min.js"></script>
<script src="dist/Parsley.js-2.8.1/dist/parsley.js?<?php echo time();?>"></script>
<!-- AdminLTE App -->
<script src="dist/js/adminlte.js"></script>
<script src="llamadaslogin.js?<?php echo time();?>"></script>

</body>
</html>
