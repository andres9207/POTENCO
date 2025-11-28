<?php
    session_start();// activa la variable de sesion
    include 'data/db.config.php';
    require_once 'data/conexion.php';

    error_reporting(E_ALL);
    $nombresession = $_COOKIE["nombrePo"];
    $imgsession = $_COOKIE['imgPo'];
    if($imgsession=="")
    {
      $imgsession  = "dist/img/default-user.png";
    }
    $nombre = ucwords(strtolower($nombresession));
    // $img = $_SESSION["imgPo"];
    $idUsuario = $_COOKIE['idusuarioPo'];
    //include 'data/validarpermisos.php';
    $per=$_SESSION["perfilPo"];
    if(!isset($idUsuario)){
       header("Location: index.php");
    }

    require_once "controladores/general.controller.php";
     include ("data/validarpermisos.php");

    $conperfil =  $conn->prepare("SELECT prf_clave_int FROM tbl_usuarios where usu_clave_int  = :idusuario");
    $conperfil->bindParam(':idusuario', $idUsuario);
    $conperfil->execute();
    $datperfil = $conperfil->fetch(PDO::FETCH_ASSOC);
    $idperfil  = $datperfil['prf_clave_int'];

    $reg = new General();
    $datr = $reg->fnReglas();
    $hirn = $datr['hirn'];
    $hfrn = $datr['hfrn'];
    $han  = $datr['han'];
    $hao  = $datr['hao'];
    $vhan = $datr['vhan'];
    $vhao = $datr['vhao']; 
    $hmes = $datr['hmes'];
    $vhanpm = $datr['vhanpm'];
    $hanpm = $datr['hanpm'];
    $limex = $datr['limex']; //EXTRAS LIMIT POR DIA
    $limexsemana = $datr['limexsemana']; // EXTRAS LIMITE SEMANA  
   ?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <link href="dist/img/favicon.png" rel="shortcut icon">
  <title>Sistema Control Horas - POTENCO | Principal</title>
  <script src="dist/cdn/angular.min.js"></script>
  <script src="dist/js/angular-ui-router.js"></script> 
  <script src="dist/js/ocLazyLoad.js"></script>  
  <script src="dist/js/rutas.js?v=1"></script>

  <!-- Font Awesome Icons -->
  <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
  <!-- overlayScrollbars -->
  <!-- <link rel="stylesheet" href="plugins/overlayScrollbars/css/OverlayScrollbars.min.css"> -->
  <!-- Theme style -->
  <link rel="stylesheet" href="dist/css/adminlte.css?v=1">
  <link rel="stylesheet" href="dist/css/main.css?v=2">
  

  <!-- <link rel="stylesheet" type="text/css" href="dist/intro.js-2.9.3/introjs.css?v=1"> -->
  <link href="dist/Parsley.js-2.8.1/src/parsley.css?v=1" rel="stylesheet" type="text/css">
  <link href="dist/dropify-master/dist/css/dropify.css?v=1"rel="stylesheet">
  <link rel="stylesheet" href="dist/jquery-fullsizable-master/css/jquery-fullsizable.css?v=1" />
  <!-- SweetAlert2 -->
  <link rel="stylesheet" href="plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css?v=1">
  <!-- Toastr -->
  <link rel="stylesheet" href="plugins/toastr/toastr.min.css">
  <link rel="stylesheet" href="dist/bootstrap-select-1.13.9/dist/css/bootstrap-select.css?v=1"/>
  <link rel="stylesheet" href="./dist/Ajax-Bootstrap-Select-master/css/ajax-bootstrap-select.css"/>
  <link rel="stylesheet" href="plugins/datatables-bs4/css/dataTables.bootstrap4.css">     
  <link rel="stylesheet" href="plugins/datatables-fixedheader/css/fixedHeader.bootstrap4.min.css?v=1">
  <link rel="stylesheet" href="plugins/datatables-rowreorder/css/rowReorder.bootstrap4.css?v=1"> 
  <link rel="stylesheet" href="plugins/datatables-responsive/css/responsive.bootstrap4.css?v=1">  
 

  <!-- <link rel="stylesheet" href="plugins/timepicker/bootstrap-timepicker.css"> -->

  <!-- Bootstrap Time Picker-->
  <link rel="stylesheet" href="./dist/bootstrap-timepicker/css/bootstrap-timepicker.css?v=1">
  <!-- iCheck for checkboxes and radio inputs -->
  <link rel="stylesheet" href="plugins/icheck-bootstrap/icheck-bootstrap.min.css">

  
  <link href="./dist/jquery-smartwizard-master/css/smart_wizard.css?v=1" rel="stylesheet"/>
  <link href="./dist/jquery-smartwizard-master/css/smart_wizard_all.css?v=1" rel="stylesheet"/>

  <!-- Tempusdominus Bbootstrap 4 -->
  <!-- <link rel="stylesheet" href="plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css"> -->

  <!-- <link rel="stylesheet" href="plugins/bootstrap4-duallistbox/bootstrap-duallistbox.min.css"> -->
  <!-- <link rel="stylesheet" type="text/css" href="./dist/steps/css/jquery.steps.css?v=1"> -->
  <!-- Google Font: Source Sans Pro -->
  <link href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700" rel="stylesheet">

  <link rel="stylesheet" href="./dist/css/searchbar.css?v=1"/>
  <!-- <link rel="stylesheet" href="./plugins/summernote/summernote-bs4.css?v=1"> -->

  <!-- <link rel="stylesheet" href="node_modules/signature_pad/docs/css/signature-pad.css?v=1" type="text/css"/>
  <link rel="stylesheet" href="node_modules/signature_pad/docs/css/ie9.css?v=1" type="text/css"/>
  -->
</head>
<body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed" ng-app="myApp">
<div class="wrapper">
  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand border-bottom-0 navbar-dark navbar-terracota">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
      </li>
      <li class="nav-item">
        <a ui-sref="Inicio" ui-sref-opts="{reload: true}"  class="nav-link">Home</a>
      </li>
      <li class="nav-item">
        <a href="data/logout.php" class="nav-link">Cerrar Sesión</a>
      </li>
    </ul>

    <!-- SEARCH FORM -->
    <form class="form-inline ml-3 hide">
      <div class="input-group input-group-sm search-input">
        <a href="" target="_blank" hidden></a>
        <input class="form-control form-control-navbar" type="search" placeholder="Busqueda Radicado" aria-label="Search" id="txtbusqueda">
        <div class="autocom-box">
          <!-- here list are inserted from javascript -->
        </div>
        <div class="input-group-append hide">
          <button class="btn btn-navbar icon" type="button">
            <i class="fas fa-search"></i>
          </button>
        </div>
      </div>
    </form>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
      <!-- Messages Dropdown Menu -->
      <li class="nav-item dropdown">
        <a onclick="CRUDUSUARIOS('NUEVO')" id="btnnuevo" class="nav-link" data-toggle="modal" data-target="#myModal">
          <i class="far fa-2x fa-plus-square"></i>
        </a>
      </li>
      <li class="nav-item dropdown hide">
       
        <a class="nav-link" data-toggle="dropdown" href="#">
          <i class="far fa-2x  fa-comments"></i>
          <span class="badge badge-danger navbar-badge">3</span>
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
          <a href="#" class="dropdown-item hide">
            <!-- Message Start -->
            <div class="media">
              <img src="<?php echo $imgsession;?>" alt="User Avatar" class="img-size-50 mr-3 img-circle">
              <div class="media-body">
                <h3 class="dropdown-item-title">
                  Brad Diesel
                  <span class="float-right text-sm text-danger"><i class="fas fa-star"></i></span>
                </h3>
                <p class="text-sm">Call me whenever you can...</p>
                <p class="text-sm text-muted"><i class="far fa-question mr-1"></i>Tiene  4 correspondencia pendientes</p>
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
          <i class="far fa-2x fa-bell"></i>
          <span class="badge badge-warning navbar-badge" id="spannotificaciones1"></span>
        </a>
        <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
          <span class="dropdown-item dropdown-header"><span id="spannotificaciones2"></span> Notificaciones</span>
          <div class="dropdown-divider"></div>
          <a ui-sref="AlertaLimite" class="dropdown-item">
            <i class="fas fa-clock mr-2"></i> <span id="spanlimite"></span> Semana exceden las horas <span class="font-weight-bold text-danger"><?php echo $limexsemana;?></span> 
            <span class="float-right text-muted text-sm hide">3 mins</span>
          </a>
          <div class="dropdown-divider"></div>
          
          <a onclick="CRUDCORRESPONDENCIA('VERCORRESPONDENCIA','','PorEntregar','Por Entregar')" class="dropdown-item hide">
            <i class="fas fa-clock mr-2"></i>  <span id="spanporentregar"></span> Correspondencia atrasada
            <span class="float-right text-muted text-sm hide">12 hours</span>
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item hide">
            <i class="fas fa-file mr-2"></i> 3 new reports
            <span class="float-right text-muted text-sm">2 days</span>
          </a>
          <div class="dropdown-divider"></div>
          <a href="#" class="dropdown-item dropdown-footer">Ver Todas las Notificaciones</a>
        </div>
      </li>
      <li class="nav-item hide">
        <a class="nav-link" data-widget="control-sidebar" data-slide="true" href="#"><i
            class="fas fa-th-large"></i></a>
      </li>
    </ul>
  </nav>
  <!-- /.navbar -->

  <!-- Main Sidebar Container -->
  <aside class="main-sidebar elevation-4 sidebar-light-terracota sidebar-no-expand">
    <!-- Brand Logo -->
    <a ui-sref="Inicio({ Idvisita: '',Idpedido:'' })" ui-sref-opts="{reload: true}" class="brand-link navbar-light">
      <img src="dist/img/favicon.png" alt="AdminLTE Logo" class="brand-image img-circle elevation-3"
           style="opacity: .8">
      <span class="brand-text font-weight-light text-sm text-center">Sistema Control Horas</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar user panel (optional) -->
      <div class="user-panel mt-3 pb-3 mb-3 d-flex">
        <div class="image">
         <a ui-sref="Inicio({ Idvisita: '',Idpedido:'' })" ui-sref-opts="{reload: true}"> <img src="<?php echo $imgsession;?>" class="img-circle elevation-2" alt="User Image"></a>
        </div>
        <div class="info">
          <a onclick="CRUDUSUARIOS('AJUSTECUENTA','<?PHP echo $idUsuario;?>')"  data-toggle='modal' data-target='#myModal' class="d-block"><?php echo $nombresession;?></a>
        </div>
      </div>

      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column nav-child-indent nav-legacy text-sm" data-widget="treeview" role="menu" data-accordion="false">
          <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
          <li class="nav-item  <?php if(permisosVentana($idperfil,9)<=0){ echo "hide"; } ?>">
              <a ui-sref="NewJornada" ui-sref-opts="{reload: true}" class="nav-link">
                
                <i class="far fa-calendar-times nav-icon"></i>
                <p>Registro Jornada</p>
              </a>
          </li>
          <li class="nav-item  <?php if(permisosVentana($idperfil,19)<=0){ echo "hide"; } ?>">
              <a ui-sref="NewSettle" ui-sref-opts="{reload: true}" class="nav-link">
                
                <i class="fas fa-check-double nav-icon"></i>
                <p>Liquidador</p>
              </a>
          </li>
          <li class="nav-item has-treeview menu-open hide <?php if(permisosVentana($idperfil,10)<=0 and permisosVentana($idperfil,2)<=0 and permisosVentana($idperfil,3)<=0 and permisosVentana($idperfil,13)<=0 and permisosVentana($idperfil,12)<=0 and permisosVentana($idperfil,5)<=0 and permisosVentana($idperfil,1)<=4 and permisosVentana($idperfil,6)<=0 and permisosVentana($idperfil,7)<=0){ echo "hide"; }?>">
            <a href="#" class="nav-link active">
              <i class="nav-icon fas fa-file"></i>
              <p>
                Informes
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">
            
            <li class="nav-item hide ">
                <a ui-sref="Informes({ Informe: 'INFORMEGRAFICO',Tituloinforme:'Informe Grafico' })" ui-sref-opts="{reload: true}" class="nav-link">
                  
                  <i class="fas fa-file  nav-icon"></i>
                  <p>Graficos</p>
                </a>
              </li>
              <li class="nav-item">
                <a ui-sref="Informes({ Informe: 'INFORMEGENERAL',Tituloinforme:'Informe General' })" ui-sref-opts="{reload: true}" class="nav-link">
                  <i class="far fa-file nav-icon"></i>
                  <p>General</p>
                </a>
              </li>
            </ul>
          </li>
          <li class="nav-item has-treeview menu-open <?php if(permisosVentana($idperfil,1)<=0 and permisosVentana($idperfil,2)<=0 and permisosVentana($idperfil,3)<=0 and permisosVentana($idperfil,13)<=0 and permisosVentana($idperfil,12)<=0 and permisosVentana($idperfil,5)<=0 and permisosVentana($idperfil,1)<=4 and permisosVentana($idperfil,6)<=0 and permisosVentana($idperfil,7)<=0){ echo "hide"; }?>">
            <a href="#" class="nav-link active">
              <i class="nav-icon fas fa-cog"></i>
              <p>
                Administracion
                <i class="right fas fa-angle-left"></i>
              </p>
            </a>
            <ul class="nav nav-treeview">            
              <li class="nav-item <?php if(permisosVentana($idperfil,1)<=0){ echo "hide"; } ?>">
                <a ui-sref="Perfiles" ui-sref-opts="{reload: true}" class="nav-link">                  
                  <i class="fas fa-portrait  nav-icon"></i>
                  <p>Perfiles</p>
                </a>
              </li>
              <li class="nav-item <?php if(permisosVentana($idperfil,2)<=0){ echo "hide"; } ?>">
                <a ui-sref="Usuarios" ui-sref-opts="{reload: true}" class="nav-link">
                  <i class="far fa-user nav-icon"></i>
                  <p>Usuarios</p>
                </a>
              </li>
              <li class="nav-item <?php if(permisosVentana($idperfil,16)<=0){ echo "hide"; } ?>">
                <a ui-sref="Horarios" ui-sref-opts="{reload: true}" class="nav-link">
                  <i class="fas fa-calendar nav-icon"></i>
                  <p>Horarios</p>
                </a>
              </li>
              <li class="nav-item <?php if(permisosVentana($idperfil,16)<=0){ echo "hide"; } ?>">
                <a ui-sref="Obras" ui-sref-opts="{reload: true}" class="nav-link">
                  <i class="fas fa-building nav-icon"></i>
                  <p>Obras</p>
                </a>
              </li>
              <li class="nav-item hide <?php if(permisosVentana($idperfil,3)<=0){ echo "hide"; } ?>">
                <a ui-sref="Empleados" ui-sref-opts="{reload: true}" class="nav-link">
                  <i class="fab fa-black-tie nav-icon"></i>
                  <p>Empleados</p>
                </a>
              </li>
              <li class="nav-item hide <?php if(permisosVentana($idperfil,13)<=0){ echo "hide"; } ?>">
                <a ui-sref="TipoDocumentos" ui-sref-opts="{reload: true}" class="nav-link">                
                  <i class="fas fa-file nav-icon"></i>
                  <p>Tipo de documentos</p>
                </a>
              </li>
              <li class="nav-item  hide <?php if(permisosVentana($idperfil,14)<=0 || isset($permisosUsuario[117])<=0){ echo "hide"; } ?>">
                <a ui-sref="Cencos" ui-sref-opts="{reload: true}" class="nav-link">
                  <i class="fas fa-building nav-icon"></i>
                  <p>Cencos</p>
                </a>
              </li>
              <li class="nav-item  <?php if(permisosVentana($idperfil,17)<=0 || isset($permisosUsuario[4])<=0){ echo "hide"; } ?>">
                <a ui-sref="Festivos" ui-sref-opts="{reload: true}" class="nav-link">
                  <i class="fas fa-gift nav-icon"></i>
                  <p>Festivos</p>
                </a>
              </li>
              <li class="nav-item  hide <?php if(permisosVentana($idperfil,13)<=0 ){ echo "hide"; } ?>">
                <a ui-sref="Almacenamiento" ui-sref-opts="{reload: true}" class="nav-link">
                
                  <i class="fas fa-save nav-icon"></i>
                  <p>Almacenamiento</p>
                </a>
              </li>
              <li class="nav-item hide <?php if(permisosVentana($idperfil,12)<=0){ echo "hide"; } ?>">
                <a ui-sref="Motivos" ui-sref-opts="{reload: true}" class="nav-link">
                  <i class="fas fa-ban nav-icon"></i>
                  <p>Motivos</p>
                </a>
              </li>
              
              
              <li class="nav-item <?php if(permisosVentana($idperfil,11)<=0){ echo "hide"; } ?>">
                <a onclick="CRUDUSUARIOS('REGLANEGOCIO')"  class="nav-link text-primary" data-toggle="modal" data-target="#myModal">
                <i class="fas fa-balance-scale-right nav-icon"></i>
                <p>Reglas de negocios</p>
                </a>
              </li>
              
            </ul>
          </li>
          <li class="nav-item">
            <a onclick="CRUDUSUARIOS('AJUSTECUENTA','<?PHP echo $idUsuario;?>')"  data-toggle='modal' data-target='#myModal' class="nav-link text-success">
            <i class="fas fa-edit nav-icon"></i>
            <p>Ajuste Cuenta</p></a>
          </li>
          <li class="nav-item ">
              <a href="data/logout.php" class="nav-link text-danger">
                <i class="fas fa-sign-out-alt nav-icon"></i>
                <p>Cerrar Sesión</p>
              </a>
          </li>
          <li class="nav-header">
            <strong>Copyright &copy; <?PHP echo date('Y');?> <a href="https://pavas.com.co">PAVAS S.A.S</a>.</strong>
           <br>Todos los derechos reservados.
          </li>
        
        
        </ul>
      </nav>
      <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
  </aside>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <div class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-12 text-center">
            <h1 class="m-0 text-dark" id="titlemodulo1">DASHBOARD</h1>
            <ol class="breadcrumb float-sm-right">
              <li class="breadcrumb-item"><a href="#" id="titlemodulo2">Home</a></li>
              <li class="breadcrumb-item active" id="titlemodulo3">Dashboard</li>
            </ol>
          </div><!-- /.col -->
          
        </div><!-- /.row -->
      </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
      <div class="container-fluid" ui-view="">
        
      </div><!--/. container-fluid -->
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

  <!-- Control Sidebar -->
  <aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
  </aside>
  <!-- /.control-sidebar -->

  <!-- Main Footer -->
  <footer class="main-footer text-sm" id="divfooter">
    <div class="float-left"></div>
    <!--<strong>Copyright &copy; 2014-2019 <a href="https://pavas.co">PAVAS S.A.S</a>.</strong>
    Todos los derechos reservados.-->
    
    <div class="float-right d-none d-sm-inline-block">
      <b>Version</b> 1.0
    </div>
  </footer>
</div>

  <div class="modal fade" id="myModal">
    <div class="modal-dialog">
      <div class="modal-content">
        <!--<div id="overlaymodal" class="overlay d-flex justify-content-center align-items-center">
            <i class="fas fa-2x fa-sync fa-spin"></i>
        </div>-->
        <div class="modal-header">
          <h4 class="modal-title" id="titlemodal"></h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body" id="contentmodal">
          <p></p>
        </div>
        <div class="modal-footer justify-content-end">
          <button type="button" class="btn btn-default hide" data-dismiss="modal">Cerrar</button>
          <button type="button" class="btn btn-success" id="btnexportar">Exportar Excell <i class="fas fa-file-excel"></i></button>         
          <button type="button" class="btn btn-primary" id="btnguardar"></button>
        </div>
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
  </div>
  <!-- ./wrapper -->

  <!-- REQUIRED SCRIPTS -->
  <!-- jQuery -->
  <script src="plugins/jquery/jquery.min.js"></script>

  <script src="plugins/moment/moment.min.js"></script>
  <!-- Bootstrap -->
  <script src="plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
  <!-- overlayScrollbars -->
 <!--<script src="plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js"></script>-->
  <!-- AdminLTE App -->
  <script src="dist/js/adminlte.js"></script>

  <script src="plugins/bootstrap/js/bootstrap3-typeahead.min.js?v=1"></script><!--
  <script src="dist/js/typeahead.jquery.js?v=1"></script>-->

  <!-- OPTIONAL SCRIPTS -->
  <script src="dist/js/demo.js"></script>
  <!-- PAGE PLUGINS -->
  <!-- jQuery Mapael -->
  <script src="plugins/jquery-mousewheel/jquery.mousewheel.js"></script>
  <script src="plugins/raphael/raphael.min.js"></script>
  <script src="plugins/jquery-mapael/jquery.mapael.min.js"></script>
  <script src="plugins/jquery-mapael/maps/usa_states.min.js"></script>
  <!-- ChartJS -->
  <script src="plugins/chart.js/Chart.min.js"></script>
  <script src="dist/dropify-master/dist/js/dropify.js?<?php echo time() ?>"></script>
  <!-- <script src="dist/intro.js-2.9.3/intro.js?<?php echo time() ?>"></script> -->
  <script src="dist/Parsley.js-2.8.1/dist/parsley.js?v=1"></script>
  <script src="dist/Parsley.js-2.8.1/dist/i18n/es.js?v=1"></script>
  <script src="dist/bootstrap-select-1.13.9/dist/js/bootstrap-select.js?v=1"></script>
  <script src="dist/bootstrap-select-1.13.9/dist/js/i18n/defaults-es_CL.js?v=1"></script>
   
  <script type="text/javascript" src="./dist/Ajax-Bootstrap-Select-master/js/ajax-bootstrap-select.js"></script>
  <script type="text/javascript" src="./dist/Ajax-Bootstrap-Select-master/js/locale/ajax-bootstrap-select.es-ES.js"></script>

  
  <!-- SweetAlert2 -->
  <script src="dist/sweetalert/sweetalert2.min.js"></script>
  <!-- Toastr -->
  <script src="plugins/toastr/toastr.min.js"></script>
  <!-- PAGE SCRIPTS
  <script src="dist/js/pages/dashboard2.js"></script> -->
  <script src="plugins/datatables/jquery.dataTables.js"></script>
  <script src="plugins/datatables-bs4/js/dataTables.bootstrap4.js"></script>
  <script src="plugins/datatables-rowreorder/js/dataTables.rowReorder.js"></script>
  <script src="plugins/datatables-fixedheader/js/dataTables.fixedHeader.min.js"></script>
  <script src="./plugins/datatables-keytable/js/dataTables.keyTable.min.js"></script>
  <!-- <script src="plugins/data" -->
  <script src="plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
  <script src="plugins/datatables-responsive/js/responsive.bootstrap4.js"></script>

  <script src="dist/jquery.formatCurrency-1.4.0/jquery.formatCurrency-1.4.0.min.js?v=1"></script>
  <script src="dist/jquery.formatCurrency-1.4.0/i18n/jquery.formatCurrency.all.js?v=1"></script>
  <script type="text/javascript" src="dist/jquery-fullsizable-master/js/jquery-fullsizable.js?v=1"></script>
  <!-- <script src="plugins/bootstrap4-duallistbox/jquery.bootstrap-duallistbox.min.js"></script> -->
  <!-- Time Picker-->
  <script type="text/javascript" src="./dist/bootstrap-timepicker/js/bootstrap-timepicker.js"></script>
  <!-- <script src="plugins/timepicker/bootstrap-timepicker.js?v=1"></script> -->
  <!--<script src="plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.js?v=1"></script>-->
  <!-- <link href="dist/tooltipster-master/css/tooltipster.css?v=1" rel="stylesheet" type="text/css"/>
  <link rel="stylesheet" type="text/css" href="dist/tooltipster-master/css/themes/tooltipster-light.css?v=1" />
  <link rel="stylesheet" type="text/css" href="dist/tooltipster-master/css/themes/tooltipster-noir.css?v=1" />
  <link rel="stylesheet" type="text/css" href="dist/tooltipster-master/css/themes/tooltipster-punk.css?v=1" />
  <link rel="stylesheet" type="text/css" href="dist/tooltipster-master/css/themes/tooltipster-shadow.css?v=1" />
  <script src="dist/tooltipster-master/js/jquery.tooltipster.js?v=1"></script> -->
  <script src="dist/js/autosize.js"></script>
  <!-- GRAFICOS HIGHCHART -->

  <script src="./dist/Highcharts-8.0.0/code/highcharts.js"></script>
  <script src="./dist/Highcharts-8.0.0/code/highcharts-3d.js"></script>
  <script src="./dist/Highcharts-8.0.0/code/modules/cylinder.js"></script>
  <script src="./dist/Highcharts-8.0.0/code/modules/funnel3d.js"></script>
  <script src="./dist/Highcharts-8.0.0/code/modules/exporting.js"></script>
  <script src="./dist/jquery-smartwizard-master/js/jquery.smartWizard.js?v=1"></script>

  <script src="llamadas.js?v=4"></script>
  <!-- <script src="./plugins/summernote/summernote-bs4.min.js?v=1"></script> -->
  <!-- <script src="./dist/jquery-loading-overlay-master/js/loadingoverlay.js?v=1"></script> -->
</body>
</html>
