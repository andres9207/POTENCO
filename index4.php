<?php
include('data/Conexion.php');
session_start();
error_reporting(0);
$IP = $_SERVER['REMOTE_ADDR'];
// variable login que almacena el login o nombre de usuario de la persona logueada
$login= isset($_SESSION['persona']);
// cookie que almacena el numero de identificacion de la persona logueada
$usuario= $_COOKIE['usuario'];
$idUsuario= $_SESSION["idusuario"];
$clave= $_COOKIE["clave"];
$identificacion = $_COOKIE["usIdentificacion"];
setlocale(LC_TIME, 'es_CO.UTF-8'); 
date_default_timezone_set('America/Bogota');
$fecha=date("Y/m/d H:i:s");
$conusu= pg_query($dbconn,"SELECT mer_clave_int,dir_clave_int,prf_clave_int,usu_imagen from tbl_usuario where usu_clave_int = '".$idUsuario."'");
$datusu = pg_fetch_array($conusu);
$idmercado = $datusu['mer_clave_int'];
$ultimadireccion = $datusu['dir_clave_int'];
$perfil = $datusu['prf_clave_int'];
$imgperfil = $datusu['usu_imagen'];
if($imgperfil=="" || $imgperfil==NULL)
{
  $imgperfil = "dist/img/default-user.png";
}

if($idUsuario<=0)
{
  $idmercado = 3;
}
$conped = pg_query($dbconn, "SELECT ped_clave_int FROM tbl_pedidos WHERE usu_clave_int = '".$idUsuario."' and ped_estado = 0");
$numped = pg_num_rows($conped);
$datped = pg_fetch_array($conped);
$idpedido = $datped['ped_clave_int'];

$conr = pg_query($dbconn, "SELECT reg_monto,to_char(reg_horaped,'HH24:MI:SS') hp FROM tbl_reglas LIMIT 1");
$datr = pg_fetch_array($conr);
$monto = $datr['reg_monto'];
$hormaxped = $datr['hp'];
$fecmaxped = date("Y/m/d ".$hormaxped);
$nunmax = 0;
if(strtotime($fecha)>strtotime($fecmaxped)){ $nunmax = 1; }//fecha y hora es mayor a la fecha y hora permitida para hacer pedidos desde la plataforma


function obtenerImagenes($ruta){

  if (is_dir($ruta)){
      echo "<ul id = 'video-gallery' style='list-style:none'> ";
      
      // Abre un gestor de directorios para la ruta indicada
      $gestor = opendir($ruta);

      // Recorre todos los archivos del directorio
      $i = 1;
      while (($archivo = readdir($gestor)) !== false)  {
           $ruta_completa = $ruta . "/" . $archivo;
          // Solo buscamos archivos sin entrar en subdirectorios
          if (is_file($ruta_completa)) {
              echo "<li class='col-xs-12 col-sm-4 col-md-4 video'  data-poster='video-poster".$i.".jpg' data-sub-html='video caption".$i."' data-html='#video".$i."'>
                    <div class='embed-responsive embed-responsive-16by9'> 
                      <video class='' controls preload='none' style='width:100%;'>
                          <source src='".$ruta_completa."' type='video/mp4'>
                      </video>
                      <div class= 'title-video' style='text-transform: uppercase'>".$archivo."</div>
                    </div>
              </li>";
              $i = $i + 1;
          }            
      }
      // Cierra el gestor de directorios
      closedir($gestor);
      echo "</ul>";
  } else {
      echo "No es una ruta de directorio valida<br/>";
  }
}
function obtenerVideo($ruta){

  if (is_dir($ruta)){
     
      
      // Abre un gestor de directorios para la ruta indicada
      $gestor = opendir($ruta);

      // Recorre todos los archivos del directorio
      $i = 1;
      while (($archivo = readdir($gestor)) !== false)  {
           $ruta_completa = $ruta . "/" . $archivo;
          // Solo buscamos archivos sin entrar en subdirectorios
          if (is_file($ruta_completa)) {
              echo "<div style='display:none;' id='video".$i."'>
                <video class='lg-video-object lg-html5 video-js vjs-default-skin' controls preload='none'>
                <source src='".$ruta_completa."' type='video/mp4'>
                 Your browser does not support HTML5 video.
                </video>
              </div>";
              $i = $i + 1;
          }            
      }
      // Cierra el gestor de directorios
      closedir($gestor);
      
  } else {
      echo "No es una ruta de directorio valida<br/>";
  }
}
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <link href="dist/img/favicon.ico?<?php echo time();?>" rel="shortcut icon"> 
  <title>DELASIEMBRA.COM | Inicio</title>
  <!-- Tell the browser to be responsive to screen width -->
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <script src="dist/cdn/angular.min.js"></script>
  <script src="dist/js/angular-ui-router.js"></script> 
  <script src="dist/js/ocLazyLoad.js"></script>  
  <script src="dist/js/rutas.js?<?php echo time(); ?>"></script>
 
  <!-- Font Awesome -->
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Tempusdominus Bbootstrap 4 -->
 <!-- <link rel="stylesheet" href="plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css">-->
  <!-- iCheck -->
  <link rel="stylesheet" href="plugins/icheck-bootstrap/icheck-bootstrap.min.css">
  <!-- JQVMap -->
  <!--<link rel="stylesheet" href="plugins/jqvmap/jqvmap.min.css">-->
  <!-- Theme style -->
  <link rel="stylesheet" href="dist/css/adminlte.css?<?php echo time();?>">
  <!-- overlayScrollbars -->
  <link rel="stylesheet" href="plugins/overlayScrollbars/css/OverlayScrollbars.min.css">
  <!-- Daterange picker -->
  <link rel="stylesheet" href="plugins/daterangepicker/daterangepicker.css">
  <!-- summernote -->
  <!--
  <link rel="stylesheet" href="plugins/summernote/summernote-bs4.css">-->
  <!-- Google Font: Source Sans Pro -->
  <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">


  <link rel="stylesheet" href="dist/css/fuente.css?<?php echo time();?>"/>
  <!--<link rel="stylesheet" href="bootstrap/css/bootstrap.css?<?php echo time();?>"/>-->
   <link rel="stylesheet" href="dist/css/bootstrapds.css?<?php echo time();?>"/>
  <!-- Font Awesome -->
  <link rel="stylesheet" href="dist/font-awesome-4.7.0/css/font-awesome.min.css"/>
  <!-- Ionicons -->
  <!--
  <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css"/>-->
  <!-- Theme style -->
  <!--
  <link rel="stylesheet" href="dist/css/AdminLTE.css?<?php echo time();?>"/>-->
  <!-- AdminLTE Skins. Choose a skin from the css/skins
       folder instead of downloading all of them to reduce the load. -->
  <!--<link rel="stylesheet" href="dist/css/skins/_all-skins.css?<?php echo time();?>"/>-->
  <!-- iCheck -->
  <!--<link rel="stylesheet" href="plugins/iCheck/all.css">
  <link rel="stylesheet" href="plugins/iCheck/flat/blue.css"/>
  <link rel="stylesheet" href="plugins/iCheck/square/blue.css?<?php echo time();?>"/>-->
  <!-- Morris chart -->
  <!--
  <link rel="stylesheet" href="plugins/morris/morris.css">-->
  <!-- jvectormap -->
  <!--<link rel="stylesheet" href="plugins/jvectormap/jquery-jvectormap-1.2.2.css"/>-->
  <!-- Date Picker -->
  <!--<link rel="stylesheet" href="plugins/datepicker/datepicker3.css?<?php echo time();?>"/>-->
  <!-- Daterange picker -->
  <!--<link rel="stylesheet" href="plugins/daterangepicker/daterangepicker-bs3.css?<?php echo time();?>"/>-->

   <link rel="stylesheet" href="plugins/timepicker/bootstrap-timepicker.css?<?php echo time();?>"/>
  <!-- bootstrap wysihtml5 - text editor -->
  <!--<link rel="stylesheet" href="plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css"/>-->

<!-- DataTable -->
  <link rel="stylesheet" type="text/css" href="dist/DataTables-1.10.10/media/css/dataTables.bootstrap.css?<?php echo time(); ?>"/>
  <link rel="stylesheet" type="text/css" href="dist/DataTables-1.10.10/extensions/Select/css/select.dataTables.css?<?php echo time();?>"/>
  <link rel="stylesheet" type="text/css" href="dist/DataTables-1.10.10/extensions/Responsive/css/responsive.dataTables.css?<?php echo time();?>"/>
  <link rel="stylesheet" type="text/css" href="dist/DataTables-1.10.10/extensions/ColReorder/css/colReorder.dataTables.min.css?<?php echo time();?>"/>
  <link rel="stylesheet" type="text/css" href="dist/DataTables-1.10.10/extensions/FixedColumns/css/fixedColumns.dataTables.min.css?<?php echo time();?>"/>
  <link rel="stylesheet" type="text/css" href="dist/DataTables-1.10.10/extensions/FixedHeader/css/fixedHeader.dataTables.min.css?<?php echo time();?>"/>
  <!--
   <link rel="stylesheet" type="text/css" href="dist/DataTables-1.10.10/extensions/RowGroup/rowGroup.dataTables.min.css?<?php echo time();?>"/>-->
  <link href="dist/DataTables-1.10.10/extensions/rowGroup.dataTables.min.css?<?php echo time(); ?>"/>
  <link rel="stylesheet" type="text/css" href="plugins/bootstrapselect/dist/css/bootstrap-select.css?<?php echo time(); ?>"/>
  <link rel="stylesheet" href="dist/dropify-master/dist/css/dropify.css?<?php echo time();?>"/>
  <link rel="stylesheet" href="dist/sweetalert/sweetalert2.css?<?php echo time ();?>"/>
  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
  <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
  <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>

  <![endif]-->
  <!--<link rel="stylesheet" href="dist/cart/css/smart_cart.css?<?php echo time();?>"/>
  <link rel="stylesheet" href="dist/ihover/ihover.css?<?php echo time();?>"/>-->
<!--
  <link type="text/css" rel="stylesheet" href="dist/simplepagination/simplePagination.css?<?php echo time();?>"/>
-->
  <!--<link type="text/css" rel="stylesheet" href="dist/radios-to-slider-master/css/radios-to-slider.css?<?php echo time();?>"/>-->

   <!----- stilo de los videos------->
   <!--<link href="dist/css/video-js.css?<?php echo time();?>" rel="stylesheet">-->
  <!--
  <link type="text/css" rel="stylesheet" href="dist/css/flexslider.css?<?php echo time();?>">-->


  <link rel="stylesheet" href="dist/css/dropdown.css?<?php echo time();?>"/>
  <link type="text/css" rel="stylesheet" href="dist/multiple-emails.js-master/multiple-emails.css?<?php echo time();?>"/>

<!--<script type="text/javascript" src="http://maps.google.com/maps/api/js?key=AIzaSyCLQTtkGTPtDiq1fAEa7A6HEKj4B_sTekM"></script>-->
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCLQTtkGTPtDiq1fAEa7A6HEKj4B_sTekM&libraries=places"></script>

  <link rel="stylesheet" href="dist/openstreet/css/leaflet.css?<?php echo time();?>"/>
  <link rel="stylesheet" href="dist/openstreet/css/style.css?<?php echo time();?>"/>
  <!--<script src="dist/openstreet/js/leaflet.js?<?php echo time();?>"></script>
  <script src="dist/openstreet/js/esri-leaflet.js?<?php echo time();?>"></script>-->
  <script src="dist/openstreet/js/geosearch.js?<?php echo time();?>"></script>
  <script src="ubicacion/leaflet.js?<?php echo time();?>"></script>
  <script src="ubicacion/esri-leaflet.js?<?php echo time();?>"></script>
  <link href="dist/openstreet/css/esri-leaflet-geocoder.css?<?php echo time();?>" rel="stylesheet">
  <script src="ubicacion/esri-leaflet-geocoder.js?<?php echo time();?>"></script>
  <script src="ubicacion/L.GeoSearch-master/src/js/l.control.geosearch.js?<?php echo time();?>"></script>
  <link rel="stylesheet" href="ubicacion/l.geosearch.css?<?php echo time();?>" />
  <script src="ubicacion/L.GeoSearch-master/src/js/l.geosearch.provider.openstreetmap.js?<?php echo time();?>"></script>
  <script src="ubicacion/L.GeoSearch-master/src/js/l.geosearch.provider.google.js?<?php echo time();?>"></script>
  <script src="dist/openstreet/js/leaflet-geoip.js?<?php echo time();?>"></script>
  <script src="dist/openstreet/js/Leaflet.AccuratePosition.js?<?php echo time();?>"></script>
</head>
<body class="hold-transition sidebar-mini layout-fixed" ng-app="myApp">
<div class="wrapper">

  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-dark navbar-success">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="index3.html" class="nav-link">Home</a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="#" class="nav-link">Contact</a>
      </li>
    </ul>

    <!-- SEARCH FORM -->
    <form class="form-inline ml-3">
      <div class="input-group input-group-sm">
        <input class="form-control form-control-navbar typehead"  aria-label="Search" type="search" name="bustxtproducto" id="bustxtproducto" placeholder="Busqueda" data-provide="typeahead" autocomplete="off" >
        <div class="input-group-append">
          <button class="btn btn-navbar" type="submit">
            <i class="fas fa-search"></i>
          </button>
        </div>
      </div>
    </form>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
      <!-- Messages Dropdown Menu -->
      <li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#">
          <i class="far fa-comments"></i>
          <span class="badge badge-danger navbar-badge">3</span>
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
          <a href="#" class="dropdown-item">
            <!-- Message Start -->
            <div class="media">
              <img src="dist/img/user1-128x128.jpg" alt="User Avatar" class="img-size-50 mr-3 img-circle">
              <div class="media-body">
                <h3 class="dropdown-item-title">
                  Brad Diesel
                  <span class="float-right text-sm text-danger"><i class="fas fa-star"></i></span>
                </h3>
                <p class="text-sm">Call me whenever you can...</p>
                <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
              </div>
            </div>
            <!-- Message End -->
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item">
            <!-- Message Start -->
            <div class="media">
              <img src="dist/img/user8-128x128.jpg" alt="User Avatar" class="img-size-50 img-circle mr-3">
              <div class="media-body">
                <h3 class="dropdown-item-title">
                  John Pierce
                  <span class="float-right text-sm text-muted"><i class="fas fa-star"></i></span>
                </h3>
                <p class="text-sm">I got your message bro</p>
                <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
              </div>
            </div>
            <!-- Message End -->
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item">
            <!-- Message Start -->
            <div class="media">
              <img src="dist/img/user3-128x128.jpg" alt="User Avatar" class="img-size-50 img-circle mr-3">
              <div class="media-body">
                <h3 class="dropdown-item-title">
                  Nora Silvester
                  <span class="float-right text-sm text-warning"><i class="fas fa-star"></i></span>
                </h3>
                <p class="text-sm">The subject goes here</p>
                <p class="text-sm text-muted"><i class="far fa-clock mr-1"></i> 4 Hours Ago</p>
              </div>
            </div>
            <!-- Message End -->
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item dropdown-footer">See All Messages</a>
        </div>
      </li>
      <!-- Notifications Dropdown Menu -->
      <li class="nav-item dropdown">
        <a class="nav-link" data-toggle="dropdown" href="#">
          <i class="far fa-bell"></i>
          <span class="badge badge-warning navbar-badge">15</span>
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
          <span class="dropdown-item dropdown-header">15 Notifications</span>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item">
            <i class="fas fa-envelope mr-2"></i> 4 new messages
            <span class="float-right text-muted text-sm">3 mins</span>
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item">
            <i class="fas fa-users mr-2"></i> 8 friend requests
            <span class="float-right text-muted text-sm">12 hours</span>
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item">
            <i class="fas fa-file mr-2"></i> 3 new reports
            <span class="float-right text-muted text-sm">2 days</span>
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item dropdown-footer">See All Notifications</a>
        </div>
      </li>
      <li class="nav-item">
        <a class="nav-link" data-widget="control-sidebar" data-slide="true" href="#">
          <i class="fas fa-th-large"></i>
        </a>
      </li>
    </ul>
  </nav>
  <!-- /.navbar -->

  <!-- Main Sidebar Container -->
  <aside class="main-sidebar elevation-4 sidebar-light-success">
    <!-- Brand Logo -->
    <a href="index3.html" class="brand-link navbar-white">
      <img src="dist/img/LOGOMINI.png" alt="Delasiembra.com Logo" class="brand-image"
           style="opacity: .8">
      <span class="brand-text font-weight-light">Delasiembra.com</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar user panel (optional) -->
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
          <img src="<?php echo $imgperfil;?>" class="img-circle elevation-2" alt="User Image">
        </div>
        <div class="info">
          <a href="#" class="d-block"><?php echo $usuario;?></a>
        </div>
      </div>

      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column nav-legacy" data-widget="treeview" role="menu" data-accordion="false">

          
        <li class="nav-header">MENU PRINCIPAL</li>
        <?php  //$idUsuario = 1; $perfil = 1;
        
?>
        
        <li class="nav-item active itemmenu dos">
         <a class="nav-link"  ui-sref="<?php if($perfil==1){ echo 'Home';}else if($perfil==2){ echo "Pendientes({ Idestado: '1' })"; }else { echo "Pedidos({ Idcategoria: '', Nomcategoria:'Todos los productos' })";}?>" ui-sref-opts="{reload: true}">
           <i class="fas fa-home "></i> <p>Inicio</p> 
         </a>
       </li>
        <?php if($perfil==3 || $idUsuario<=0) { 
         $concate = pg_query($dbconn,"select cat_clave_int,cat_nombre,cat_imagen from tbl_categorias where cat_activo = 1");
         ?>
       <li class="nav-item has-treeview menu-open">
          <a class="nav-link active" ui-sref="Pedidos({ Idcategoria: '', Nomcategoria:'Todos los productos' })" ui-sref-opts="{reload: true}"><i class="fa  fa-cart-arrow-down "></i>
            <p>Productos
              <i class="right fas fa-angle-left"></i>
            </p>  
          </a>
          <ul class="nav nav-treeview">
             <?php
             while($datc = pg_fetch_array($concate))
             {
             ?>
             <li class="nav-item">
               <a class="nav-link" ui-sref="Pedidos({ Idcategoria: <?php echo $datc['cat_clave_int'];?>,Nomcategoria:'<?php echo $datc['cat_nombre'];?>' })" ui-sref-opts="{reload: true}"> <img src="<?php echo $datc['cat_imagen'];?>" class="img-cart-icon-left">
               <p><?php echo $datc['cat_nombre'];?></p></a></li>
             <?php
             }
             ?>
             
           </ul>
       </li>
       <?php 
      } 
       ?>
       <?php
      
       if($idUsuario>0)
       {
       ?>
        <li class="nav-item has-treeview">
         <a class="nav-link" href="#">
          <i class="fa fa-user-circle-o"></i>         
          <p>Mi cuenta
              <i class="right fas fa-angle-left"></i>
            </p>  
         </a>
         <ul class="nav nav-treeview">
           
           <li class="nav-item itemmenu dos">
             <a class="nav-link" ui-sref="Ajustes({ Op: 'Cue' })" ui-sref-opts="{reload: true}"><i class="fas fa-cog"></i>Ajustes</a>
          </li>
           <li class="nav-item itemmenu dos hide">
             <a class="nav-link" data-toggle="modal" data-target="#modalright" onclick="CRUDPEDIDOS('NUEVADIRECCION','')"><i class="fas  fa-street-view "></i>Direcciones</a>
           </li>
           <li class="hide"><a class="nav-link" href="#"><i class="fas fa-globe "></i>Idioma</a></li>
           <li class="hide"><a class="nav-link" href="#"><i class="fas fa-bell-o"></i>Centro de notificaciones</a></li>
           <li><a  class="nav-link" ui-sref="Pqr" ui-sref-opts="{reload: true}"><i class="fas fa-info-circle "></i>(PQR)</a></li>
         </ul>
       </li>

       <li class="nav-item has-treeview  <?php if($perfil=="1"){ echo "hide";}?>">
         <a class="nav-link"  href="#">
           <i class="fas fa-info-circle  "></i>
           <p>Historial de pedidos
              <i class="right fas fa-angle-left"></i>
            </p>  
         </a>
         <ul class="nav nav-treeview">
               <?php if($perfil=="1"){}?>
               <li class="nav-item itemmenu dos"><a class="nav-link"  ui-sref="Pendientes({ Idestado: '1', Lista: '0' })" ui-sref-opts="{reload: true}"><i class="fas fa-bell  "></i> Pedidos pendientes</a></li>
               <li class="nav-item itemmenu dos"><a class="nav-link"  ui-sref="Pendientes({ Idestado: '2', Lista: '0' })" ui-sref-opts="{reload: true}"><i class="fas fa-motorcycle  "></i>Proceso de entrega</a></li>
               <li class="nav-item itemmenu dos"><a class="nav-link"  ui-sref="Pendientes({ Idestado: '3', Lista: '0' })" ui-sref-opts="{reload: true}"><i class="fas fa-check  "></i> Pedidos completados</a></li>
               <li class="nav-item itemmenu dos"><a class="nav-link"  ui-sref="Pendientes({ Idestado: '4', Lista: '0' })" ui-sref-opts="{reload: true}"><i class="fas fa-ban  "></i> Pedidos Cancelados</a></li>
                <li class="nav-item itemmenu dos <?php if($perfil!=3){ echo 'hide';}?>"><a class="nav-link"  ui-sref="Pendientes({ Idestado: '', Lista: '1' })" ui-sref-opts="{reload: true}"><i class="fas fa-list-ul  "></i> Lista deseos</a></li>
             </ul>
       </li>
       
      
       <?php if($perfil=="1"){ ?>

       <li class="nav-item active has-treeview">
         <a class="nav-link"  href="#">
            <i class="fa fa-shield  "></i>
            <p>Seguridad
              <i class="right fas fa-angle-left"></i>
            </p>  
         </a>
          <ul class="nav nav-treeview">           
           <li class="nav-item itemmenu dos"><a class="nav-link"  ui-sref="Perfiles" ui-sref-opts="{reload: true}"><i class="fa fa-user  "></i>Perfiles</a></li>
           <li class="nav-item itemmenu dos"><a class="nav-link"  ui-sref="Usuarios({ Conf: '0' })" ui-sref-opts="{reload: true}"><i class="fa fa-users  "></i>Usuarios</a></li>
           <li class="nav-item itemmenu dos hide"><a class="nav-link"  href="#"><i class="fa fa fa-expeditedssl  "></i>Permisos</a></li>
         </ul>
       </li>
      <?php } ?>
       <?php if($perfil=="1"){ ?>
       <li class="nav-item active has-treeview">
         <a class="nav-link"  href="#">
            <i class="fa fa-wrench "></i><p>Configuración<i class="right fas fa-angle-left"></i></p> 
         </a>
          <ul class="nav nav-treeview">
           <!--<li><a ui-sref="Unidades" ui-sref-opts="{reload: true}"><i class="fa fa-cog"></i>Unidades</a></li>-->
            <li class="nav-item itemmenu dos"><a  class="nav-link" ui-sref="Mercados" ui-sref-opts="{reload: true}"><i class="fa fa-cog  "></i>Mercados</a></li>
           <li class="nav-item  itemmenu dos"><a class="nav-link"  ui-sref="Categorias" ui-sref-opts="{reload: true}"><i class="fa fa-leanpu  "></i>Categorias</a></li>
           <li class="nav-item itemmenu dos"><a class="nav-link"  ui-sref="Productos" ui-sref-opts="{reload: true}"><i class="fa fa-cog  "></i>Productos</a></li>
           <li class="nav-item itemmenu dos hide"><a class="nav-link"  ui-sref="ListaPrecios" ui-sref-opts="{reload: true}"><i class="fa fa-cog  "></i>Lista de precios</a></li>
           <li class="nav-item itemmenu dos"><a class="nav-link"  ui-sref="Proveedores" ui-sref-opts="{reload: true}"><i class="fa fa-building  "></i>Proveedores</a></li>
           <li class="nav-item itemmenu dos hide"><a class="nav-link" ui-sref="Pedidos({ Idcategoria: 1 })" ui-sref-opts="{reload: true}"><i class="fa  fa-cart-arrow-down  "></i>Pedidos</a></li>
           <li class="nav-item itemmenu dos"><a class="nav-link"  ui-sref="Ofertas" ui-sref-opts="{reload: true}"><i class="fa  fa-ticket  "></i>Ofertas</a></li>
           <li class="hide"><a class="nav-link"  href="#"><i class="fa fa-globe  "></i>Clientes</a></li>
           <li class="hide"><a class="nav-link"  href="#"><i class="fa fa-bell-o  "></i>Domiciliarios</a></li>
           <li class="nav-item itemmenu dos"><a class="nav-link"  ui-sref="Sectores" ui-sref-opts="{reload: true}"><i class="fa fa-cog  "></i>Sectores</a></li>
           <li class="nav-item itemmenu dos">
             <a  
           data-toggle="modal" data-target="#modallogin" class="nav-link text-success" onclick="CRUDREGLAS('REGLASNEGOCIO')">Reglas de negocio</a>
           </li>
         </ul>
       </li>
       <li class="nav-item has-treeview">
         <a class="nav-link"  href="#">
            <i class="fa fa-file"></i><p>Informes <i class="right fas fa-angle-left "></i></p>
         </a>
          <ul class="nav nav-treeview">
          
             <li class="nav-item itemmenu dos"><a  class="nav-link"  ui-sref="Informes({ Informe: 'INFORMEDESPACHOS',Tituloinforme:'Informe Despacho Dia' })" ui-sref-opts="{reload: true}"><i class="fa"></i>Despachos Dia</a></li>

             <li class="nav-item itemmenu dos"><a class="nav-link" ui-sref="Informes({ Informe: 'INFORMECOMPRADOR',Tituloinforme:'Informe Compradores' })" ui-sref-opts="{reload: true}"><i class="fa"></i>Informe de compradores</a></li>
          
           
         </ul>
       </li>
       <?php } 
         }
       else
       {
       ?>
       <li class="nav-item itemmenu dos">
         <a class="nav-link" data-toggle="modal" data-target="#modallogin"  onclick="CRUDUSUARIOS('LOGIN','','')">
           <i class="far fa-user  "></i> <p>Iniciar Sesión</p> 
         </a>
       </li>
        <li  class="nav-item treeview itemmenu dos">
         <a class="nav-link" style="color:#8a6d3b !important" data-toggle="modal" data-target="#modallogin"  onclick="CRUDUSUARIOS('REGISTRONUEVO','','')">
           <i class="fas fa-user-plus"></i> <p>Registrate</p> 
         </a>
       </li>
       <li class="nav-item treeview itemmenu dos hide">
         <a class="nav-link" ui-sref="Domiciliario" ui-sref-opts="{reload: true}">
           <i class="far fa-send  "></i> <p>Reparte para nosotros</p> 
         </a>
       </li>
       
       <?php
       }
       ?>
     
          <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
          <li class="nav-item has-treeview menu-open hide">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-tachometer-alt"></i>
              <p>
                Dashboard
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="./index.html" class="nav-link active">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Dashboard v1</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="./index2.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Dashboard v2</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="./index3.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Dashboard v3</p>
                </a>
              </li>
            </ul>
          </li>
          <li class="nav-item hide">
            <a href="pages/widgets.html" class="nav-link">
              <i class="nav-icon fas fa-th"></i>
              <p>
                Widgets
                <span class="right badge badge-danger">New</span>
              </p>
            </a>
          </li>
          <li class="nav-item has-treeview hide">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-copy"></i>
              <p>
                Layout Options
                <i class="fas fa-angle-left right"></i>
                <span class="badge badge-info right">6</span>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="pages/layout/top-nav.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Top Navigation</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/layout/boxed.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Boxed</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/layout/fixed-sidebar.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Fixed Sidebar</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/layout/fixed-topnav.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Fixed Navbar</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/layout/fixed-footer.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Fixed Footer</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/layout/collapsed-sidebar.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Collapsed Sidebar</p>
                </a>
              </li>
            </ul>
          </li>
          <li class="nav-item has-treeview hide">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-chart-pie"></i>
              <p>
                Charts
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="pages/charts/chartjs.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>ChartJS</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/charts/flot.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Flot</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/charts/inline.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Inline</p>
                </a>
              </li>
            </ul>
          </li>
          <li class="nav-item has-treeview hide">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-tree"></i>
              <p>
                UI Elements
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="pages/UI/general.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>General</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/UI/icons.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Icons</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/UI/buttons.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Buttons</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/UI/sliders.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Sliders</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/UI/modals.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Modals & Alerts</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/UI/navbar.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Navbar & Tabs</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/UI/timeline.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Timeline</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/UI/ribbons.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Ribbons</p>
                </a>
              </li>
            </ul>
          </li>
          <li class="nav-item has-treeview hide">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-edit"></i>
              <p>
                Forms
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="pages/forms/general.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>General Elements</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/forms/advanced.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Advanced Elements</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/forms/editors.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Editors</p>
                </a>
              </li>
            </ul>
          </li>
          <li class="nav-item has-treeview hide">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-table"></i>
              <p>
                Tables
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="pages/tables/simple.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Simple Tables</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/tables/data.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>DataTables</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/tables/jsgrid.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>jsGrid</p>
                </a>
              </li>
            </ul>
          </li>
          <li class="nav-header hide">EXAMPLES</li>
          <li class="nav-item hide">
            <a href="pages/calendar.html" class="nav-link">
              <i class="nav-icon far fa-calendar-alt"></i>
              <p>
                Calendar
                <span class="badge badge-info right">2</span>
              </p>
            </a>
          </li>
          <li class="nav-item hide">
            <a href="pages/gallery.html" class="nav-link">
              <i class="nav-icon far fa-image"></i>
              <p>
                Gallery
              </p>
            </a>
          </li>
          <li class="nav-item has-treeview hide">
            <a href="#" class="nav-link">
              <i class="nav-icon far fa-envelope"></i>
              <p>
                Mailbox
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="pages/mailbox/mailbox.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Inbox</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/mailbox/compose.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Compose</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/mailbox/read-mail.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Read</p>
                </a>
              </li>
            </ul>
          </li>
          <li class="nav-item has-treeview hide">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-book"></i>
              <p>
                Pages
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="pages/examples/invoice.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Invoice</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/examples/profile.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Profile</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/examples/e_commerce.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>E-commerce</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/examples/projects.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Projects</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/examples/project_add.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Project Add</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/examples/project_edit.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Project Edit</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/examples/project_detail.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Project Detail</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/examples/contacts.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Contacts</p>
                </a>
              </li>
            </ul>
          </li>
          <li class="nav-item has-treeview hide">
            <a href="#" class="nav-link">
              <i class="nav-icon far fa-plus-square"></i>
              <p>
                Extras
                <i class="fas fa-angle-left right"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="pages/examples/login.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Login</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/examples/register.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Register</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/examples/forgot-password.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Forgot Password</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/examples/recover-password.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Recover Password</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/examples/lockscreen.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Lockscreen</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/examples/legacy-user-menu.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Legacy User Menu</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/examples/language-menu.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Language Menu</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/examples/404.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Error 404</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/examples/500.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Error 500</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/examples/pace.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Pace</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="pages/examples/blank.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Blank Page</p>
                </a>
              </li>
              <li class="nav-item">
                <a href="starter.html" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Starter Page</p>
                </a>
              </li>
            </ul>
          </li>
          <li class="nav-header hide">MISCELLANEOUS</li>
          <li class="nav-item hide">
            <a href="https://adminlte.io/docs/3.0" class="nav-link">
              <i class="nav-icon fas fa-file"></i>
              <p>Documentation</p>
            </a>
          </li>
          <li class="nav-header hide">MULTI LEVEL EXAMPLE</li>
          <li class="nav-item hide">
            <a href="#" class="nav-link">
              <i class="fas fa-circle nav-icon"></i>
              <p>Level 1</p>
            </a>
          </li>
          <li class="nav-item has-treeview hide">
            <a href="#" class="nav-link">
              <i class="nav-icon fas fa-circle"></i>
              <p>
                Level 1
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Level 2</p>
                </a>
              </li>
              <li class="nav-item has-treeview">
                <a href="#" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>
                    Level 2
                    <i class="right fas fa-angle-left"></i>
                  </p>
                </a>
                <ul class="nav nav-treeview">
                  <li class="nav-item">
                    <a href="#" class="nav-link">
                      <i class="far fa-dot-circle nav-icon"></i>
                      <p>Level 3</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="#" class="nav-link">
                      <i class="far fa-dot-circle nav-icon"></i>
                      <p>Level 3</p>
                    </a>
                  </li>
                  <li class="nav-item">
                    <a href="#" class="nav-link">
                      <i class="far fa-dot-circle nav-icon"></i>
                      <p>Level 3</p>
                    </a>
                  </li>
                </ul>
              </li>
              <li class="nav-item">
                <a href="#" class="nav-link">
                  <i class="far fa-circle nav-icon"></i>
                  <p>Level 2</p>
                </a>
              </li>
            </ul>
          </li>
          <li class="nav-item hide">
            <a href="#" class="nav-link">
              <i class="fas fa-circle nav-icon"></i>
              <p>Level 1</p>
            </a>
          </li>
          <li class="nav-header hide">LABELS</li>
          <li class="nav-item hide">
            <a href="#" class="nav-link">
              <i class="nav-icon far fa-circle text-danger"></i>
              <p class="text">Important</p>
            </a>
          </li>
          <li class="nav-item hide">
            <a href="#" class="nav-link">
              <i class="nav-icon far fa-circle text-warning"></i>
              <p>Warning</p>
            </a>
          </li>
          <li class="nav-item hide">
            <a href="#" class="nav-link">
              <i class="nav-icon far fa-circle text-info"></i>
              <p>Informational</p>
            </a>
          </li>
        </ul>
      </nav>
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper ">
    <!-- Content Header (Page header) -->
    <div class="content-header hide">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1 class="m-0 text-dark">Dashboard</h1>
          </div><!-- /.col -->
          <div class="col-sm-6">
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#">Home</a></li>
              <li class="breadcrumb-item active">Dashboard v1</li>
            </ol>
          </div><!-- /.col -->
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content" ui-view="">
      
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->
  <footer class="main-footer">
    <strong>Copyright &copy; <?PHP echo date('Y');?> <a href="http://adminlte.io">PAVAS S.A.S</a>.</strong>
    All rights reserved.
    <div class="float-right d-none d-sm-inline-block">
      <b>Version</b> 2.0.1
    </div>
  </footer>

  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
  </aside>
  <!-- /.control-sidebar -->
</div>
<!-- ./wrapper -->

<div id="modallogin" class="modal login fade" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header bg-success">
        
        <h4 class="modal-title" id="titlelogin"></h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body" id="divlogin">
           
      </div>    
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->

<div id="modalregistro" class="modal fade" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-success">
        
        <h4 class="modal-title" id="titlemodal">Modal title</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body" id="contenidomodal">
        <p>One fine body&hellip;</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
        <button type="button" id="btnguardar" class="btn btn-primary">Guardar Cambios</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->


<div id="modalinfo" class="modal fade" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-success">
       
        <h4 class="modal-title" id="titleinfo">Modal title</h4> 
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      </div>
      <div class="modal-body" id="contenidomodalinfo">
        
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
         <button data-loading-text="Enviando..." type="button" id="btnenviar" class="btn btn-primary">Hacer Pedido</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->


  <div class="modal left fade" id="modalleft" tabindex="-1" role="dialog" aria-labelledby="titleleft">
    <div class="modal-dialog" role="document">
      <div class="modal-content">

        <div class="modal-header bg-success">
          
          <h4 class="modal-title" id="titleleft"></h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>

        <div class="modal-body" id="contenidomodalleft">
         
        </div>

      </div><!-- modal-content -->
    </div><!-- modal-dialog -->
  </div><!-- modal -->
  
  <!-- Modal -->
  <div class="modal right fade" id="modalright" tabindex="-1" role="dialog" aria-labelledby="titleright">
    <div class="modal-dialog" role="document">
      <div class="modal-content">

        <div class="modal-header bg-success">
          
          <h4 class="modal-title" id="titleright"></h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>

        <div class="modal-body" id="contenidomodalright">
          
        </div>

      </div><!-- modal-content -->
    </div><!-- modal-dialog -->
  </div><!-- modal -->

  <div class="modal pedido fade" id="modalpedido" tabindex="-1" role="dialog" aria-labelledby="titlepedido">
    <div class="modal-dialog rotateOutDownLeft animate" role="document">
      <div class="modal-content">

        <div class="modal-header bg-success">
         
          <h4 class="modal-title" id="titlepedido"></h4> 
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>

        <div class="modal-body" id="contenidopedido">
           <input type="hidden" name="" id="inismart" value="0">
            <form action="modulos/pedidos/guardarpedido.php" id="formpedido" name="formpedido" method="POST"> 
              <!-- SmartCart element -->
              <div id="smartcart" ></div>
              <div class="panel panel-default sc-cart sc-theme-default" id="divdetallepedido">
                
              </div>

          </form>
        </div>

      </div><!-- modal-content -->
    </div><!-- modal-dialog -->
  </div><!-- modal -->

<div class="modal custom" id="modalcustom" tabindex="-1" role="dialog" aria-labelledby="titlecustom" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header bg-success">
           
               <h4 class="modal-title" id="titlecustom"></h4> 
               <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>

            </div>

            <div class="modal-body" id="contenidomodalcustom">
            <p></p>
            </div>

            <div class="modal-footer">
                <div class="btnmodal" data-dismiss="modal">Cerrar &#10006;</div>

            </div>
        </div>
    </div>
</div>
<a class="btn" href="#" id="back-to-top" title="Back to top">
  <i class="fa fa-chevron-circle-up "></i>
</a>


<!-- jQuery -->
<script src="plugins/jquery/jquery.min.js"></script>
<!-- jQuery UI 1.11.4 -->
<script src="plugins/jquery-ui/jquery-ui.min.js"></script>
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<script>
  $.widget.bridge('uibutton', $.ui.button)
</script>
<!-- Bootstrap 4 -->
<script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<!-- ChartJS -->
<!--<script src="plugins/chart.js/Chart.min.js"></script>-->
<!-- Sparkline -->
<!--<script src="plugins/sparklines/sparkline.js"></script>-->
<!-- JQVMap -->
<!--<script src="plugins/jqvmap/jquery.vmap.min.js"></script>
<script src="plugins/jqvmap/maps/jquery.vmap.usa.js"></script>-->
<!-- jQuery Knob Chart -->
<script src="plugins/jquery-knob/jquery.knob.min.js"></script>
<!-- daterangepicker -->
<script src="plugins/moment/moment.min.js"></script>
<script src="plugins/daterangepicker/daterangepicker.js"></script>
<!-- Tempusdominus Bootstrap 4 -->
<!--<script src="plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js"></script>-->
<!-- Summernote -->
<!--<script src="plugins/summernote/summernote-bs4.min.js"></script>-->
<!-- overlayScrollbars -->
<script src="plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>
<!-- AdminLTE App -->



<!--<script src="plugins/jQuery/jQuery-2.1.4.min.js"></script>-->
<!-- jQuery UI 1.11.4 -->
<!--<script src="https://code.jquery.com/ui/1.11.4/jquery-ui.min.js"></script>-->
<!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
<!-- Bootstrap 3.3.5 -->
<!--<script src="bootstrap/js/bootstrap.min.js?<?php echo time();?>"></script>-->
<script src="plugins/bootstrap/js/bootstrapds.js?<?php echo time();?>"></script>
<script src="plugins/bootstrap/js/bootstrap3-typeahead.min.js?<?php echo time();?>"></script>
<script src="plugins/bootstrap/js/validator.js?<?php echo time();?>"></script>
<script src="plugins/bootstrapselect/dist/js/bootstrap-select.js?<?php echo time();?>"></script>
<!-- Morris.js charts -->
<!--<script src="dist/cdn/raphael-min.js?<?php echo time();?>"></script>-->
<!--<script src="plugins/morris/morris.min.js?<?php echo time();?>"></script>-->
<!-- Sparkline -->
<!--<script src="plugins/sparkline/jquery.sparkline.min.js?<?php echo time();?>"></script>-->
<!-- jvectormap -->
<!--<script src="plugins/jvectormap/jquery-jvectormap-1.2.2.min.js?<?php echo time();?>"></script>-->
<!--<script src="plugins/jvectormap/jquery-jvectormap-world-mill-en.js?<?php echo time();?>"></script>-->
<!-- jQuery Knob Chart -->
<!--<script src="plugins/knob/jquery.knob.js?<?php echo time();?>"></script>-->
<!-- datepicker -->

<!-- Daterange picker -->
<!--<script src="dist/cdn/moment.min.js?<?php echo time();?>"></script>
<script src="plugins/daterangepicker/daterangepicker.js?<?php echo time();?>"></script>-->
<!-- datepicker -->
<<!--script src="plugins/datepicker/bootstrap-datepicker.js?<?php echo time();?>"></script>
<script src="plugins/datepicker/locales/bootstrap-datepicker.es.js?<?php echo time();?>"></script>-->

<!-- bootstrap time picker -->
<script src="plugins/timepicker/bootstrap-timepicker.js?<?php echo time();?>"></script>
<!-- Bootstrap WYSIHTML5 -->
<!--<script src="plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js?<?php echo time();?>"></script>-->
<!-- Slimscroll -->
<!--<script src="plugins/slimScroll/jquery.slimscroll.min.js?<?php echo time();?>"></script>-->
<!-- FastClick -->
<!--<script src="plugins/fastclick/fastclick.js?<?php echo time();?>"></script>-->
<!-- AdminLTE App -->
<!--<script src="dist/js/app.js?<?php echo time();?>"></script>-->

<script src="upload.js?<?php echo time();?>"></script>

<!-- AdminLTE dashboard demo (This is only for demo purposes) -->
<!--<script src="dist/js/pages/dashboard.js?<?php echo time();?>"></script>-->
<!-- AdminLTE for demo purposes -->
<!--<script src="dist/js/demo.js?<?php echo time();?>"></script>-->

<!-- Dropify -->
<script src="dist/dropify-master/dist/js/dropify.js?<?php echo time();?>"></script>

<!--Sweetalert2 -->
<script  type="text/javascript" src="dist/sweetalert/sweetalert2.js?<?php echo time();?>"></script>
<script src="https://cdn.jsdelivr.net/npm/promise-polyfill@7.1.0/dist/promise.min.js"></script>
<!-- DataTable -->
<script type="text/javascript" src="dist/DataTables-1.10.10/media/js/jquery.dataTables.js?<?php echo time();?>"></script>
<script type="text/javascript" src="dist/DataTables-1.10.10/media/js/dataTables.bootstrap.min.js?<?php echo time();?>"></script>
<script type="text/javascript" src="dist/DataTables-1.10.10/media/js/ColReorderWithResize.js?<?php echo time();?>"></script>
<script type="text/javascript" src="dist/DataTables-1.10.10/extensions/Responsive/js/dataTables.responsive.js?<?php echo time();?>"></script>
<script type="text/javascript" src="dist/DataTables-1.10.10/extensions/FixedColumns/js/dataTables.fixedColumns.min.js?<?php echo time();?>"></script>
<script type="text/javascript" src="dist/DataTables-1.10.10/extensions/FixedHeader/js/dataTables.fixedHeader.min.js?<?php echo time();?>"></script>
<script src="dist/DataTables-1.10.10/extensions/dataTables.rowGroup.min.js?<?php echo time(); ?>"></script>

<!-- Autosize -->
<script src="dist/jquery.formatCurrency-1.4.0/jquery.formatCurrency-1.4.0.min.js?<?php echo time();?>"></script>
<script src="dist/jquery.formatCurrency-1.4.0/i18n/jquery.formatCurrency.all.js?<?php echo time();?>"></script>
<!--
<script src="dist/radios-to-slider-master/js/jquery.radios-to-slider.js?<?php echo time();?>" type="text/javascript" ></script>-->
<!-- Autosize-->
<script type="text/javascript" src="dist/js/autosize.js?<?php echo time();?>"></script>
<!-- Tooltipster -->
<link href="dist/tooltipster-master/css/tooltipster.css?<?php echo time(); ?>" rel="stylesheet" type="text/css"/>
<link rel="stylesheet" type="text/css" href="dist/tooltipster-master/css/themes/tooltipster-light.css?<?php echo time(); ?>" />
<link rel="stylesheet" type="text/css" href="dist/tooltipster-master/css/themes/tooltipster-noir.css?<?php echo time(); ?>" />
<link rel="stylesheet" type="text/css" href="dist/tooltipster-master/css/themes/tooltipster-punk.css?<?php echo time(); ?>" />
<link rel="stylesheet" type="text/css" href="dist/tooltipster-master/css/themes/tooltipster-shadow.css?<?php echo time(); ?>" />
<script src="dist/tooltipster-master/js/jquery.tooltipster.js?<?php echo time();?>"></script>
<!-- input mask-->
<script src="plugins/inputmask/inputmask/inputmask.js?<?php echo time();?>"></script>
<!-- Clipboard -->
<script type="text/javascript" src="dist/clipboard.js-master/dist/clipboard.js?<?php echo time();?>"></script>

<script type="text/javascript" src="dist/multiple-emails.js-master/multiple-emails.js?<?php echo time();?>"></script>
<script src="dist/js/adminlte.js"></script>
<!-- AdminLTE dashboard demo (This is only for demo purposes) -->
<script src="dist/js/pages/dashboard.js"></script>
<!-- AdminLTE for demo purposes -->
<script src="dist/js/demo.js"></script>
<script src="llamadas.js?<?php echo time();?>" type="text/javascript"></script>

</body>
</html>
