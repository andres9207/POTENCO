<?php
  include 'data/db.config.php';
  include('data/conexion.php');
  $codigo = $_GET['codigo'];
  if($codigo=="")
  {

      echo '<script>alert("No hay codigo de verificación"); window.location.href="index.php";</script>';
  }
  else
  {
    // Verificar si el código ya fue usado
    $stmt = $conn->prepare("SELECT * FROM tbl_recuperar WHERE rec_codigo = :codigo AND rec_estado = 1");
    $stmt->execute([':codigo' => $codigo]);
    $num = $stmt->rowCount();

    if ($num > 0) {
        echo '<script>alert("Este código ya fue usado anteriormente"); window.location.href="index.php";</script>';
    } else {
    // Obtener los datos del código (si existe)
        $stmt2 = $conn->prepare("SELECT * FROM tbl_recuperar WHERE rec_codigo = :codigo LIMIT 1");
        $stmt2->execute([':codigo' => $codigo]);
        $num = $stmt2->rowCount();

        if ($num > 0) {
            $datos = $stmt2->fetch(PDO::FETCH_ASSOC);
            $idusuario = $datos['usu_clave_int'];

            // Consultar usuario
            $stmt3 = $conn->prepare("SELECT usu_usuario, usu_imagen FROM tbl_usuario WHERE usu_clave_int = :idusuario");
            $stmt3->execute([':idusuario' => $idusuario]);
            $datusu = $stmt3->fetch(PDO::FETCH_ASSOC);

            $usuario = $datusu['usu_usuario'];
            $imagen = $datusu['usu_imagen'];

            if (empty($imagen)) {
                $imagen = "dist/img/default-user.png";
            }

            // Puedes hacer echo o continuar tu lógica aquí
        }
    }

  } 
  ?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <link href="dist/img/favicon.png" rel="shortcut icon">
  <title>Sistema Control de Horas | Restablece Contraseña</title>
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
      <p class="login-box-msg">Estás a solo un paso de tu nueva contraseña, recupera tu contraseña ahora.</p>

      <form action="login.html" method="post">
        <div class="input-group mb-3">
          <input type="password" class="form-control" placeholder="Contraseña Nueva" id="contrasena1">
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-lock"></span>
            </div>
          </div>
        </div>
        <div class="input-group mb-3">
          <input type="password" class="form-control" placeholder="Confirmar contraseña" id="contrasena2">
          <div class="input-group-append">
            <div class="input-group-text">
              <span class="fas fa-lock"></span>
            </div>
          </div>
        </div>
        <div class="row">
          <div class="col-6">
            <button type="submit" onclick="RESTABLECER('<?php echo $codigo;?>')" class="btn btn-primary btn-block">Restablecer</button>
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
<script src="dist/js/adminlte.js"></script>
<script src="llamadaslogin.js?<?php echo time();?>"></script>

</body>
</html>
