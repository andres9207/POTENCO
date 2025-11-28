<?php
include '../../data/db.config.php';
include '../../data/conexion.php';
session_start();
error_reporting(0);
//$login     = isset($_SESSION['persona']);
// cookie que almacena el numero de identificacion de la persona logueada
$usuario   = $_COOKIE['usuarioPo'];
$idUsuario = $_COOKIE['idusuarioPo'];
$perfil    = $_SESSION["perfilPo"];
include '../../data/validarpermisos.php';
require_once "../../controladores/general.controller.php";
$gen = new General();

$conperfil =  $conn->prepare("SELECT prf_clave_int FROM tbl_usuarios where usu_clave_int  = :idusuario");
$conperfil->bindParam(':idusuario', $idUsuario);
$conperfil->execute();
$datperfil = $conperfil->fetch(PDO::FETCH_ASSOC);
$idperfil  = $datperfil['prf_clave_int'];

$p48 = isset($permisosUsuario[48]) ?? 0;
$p51 =  isset($permisosUsuario[51]) ?? 0;
$p52 =  isset($permisosUsuario[52]) ?? 0;
$p93 =  isset($permisosUsuario[93]) ?? 0;
$p94 =  isset($permisosUsuario[94]) ?? 0;
$p95 =  isset($permisosUsuario[95]) ?? 0;
$p96 =  isset($permisosUsuario[96]) ?? 0;
$p97 =  isset($permisosUsuario[97]) ?? 0;
$p98 =  isset($permisosUsuario[98]) ?? 0;
$p116 = isset($permisosUsuario[116]) ?? 0;
$p122 = isset($permisosUsuario[122]) ?? 0;
$p123 = isset($permisosUsuario[123]) ?? 0;
$p38 =  isset($permisosUsuario[38]) ?? 0;
?>  
 <!-- Info boxes -->
<div class="row align-items-center" id="divhome">
  <?php  
  //echo $p98;
    if($p48>0)
    { 
      ?>
      <div class="col-6 col-sm-6 col-md-3" onclick="javascript:window.location.href='#/Jornada/New'" style="cursor:pointer">
      
        <div class="small-box bg-success">
          <div class="inner">
            <h3>&nbsp;</h3>
            <p class="font-weight-bold">Registro Jornada</p>
          </div>
          <div class="icon">
            <i class="fas fa-calendar-check"></i>
          </div>
          <a href="#" class="small-box-footer hide "><br></a>
        </div>
        
        <!-- /.info-box -->
      </div>
      <?php 
    }
    if(permisosVentana($idperfil,19)>0){
    ?>
    <div class="col-6 col-sm-6 col-md-3" onclick="javascript:window.location.href='#/Liquidar/NewSettle'" style="cursor:pointer">
    
      <div class="small-box bg-success">
        <div class="inner">
          <h3>&nbsp;</h3>
          <p class="font-weight-bold">Nueva Liquidación</p>
        </div>
        <div class="icon">
          <i class="fas fa-clock"></i>
        </div>
        <a href="#" class="small-box-footer hide "><br></a>
      </div>    
    <!-- /.info-box -->
    </div>
    <?php 
    } 
    ?>
    
   
      <div class="col-6 col-sm-6 col-md-3 hide <?php if($p93<=0){ echo "hide";}?>"  onclick="javascript:window.location.href='#/Jornada/Planillas?TIP=PorAprobar&TIT=Planillas por aprobar'">
      
      <div class="small-box bg-info">
        <div class="inner">
          <h3 id="divporaprobar">0</h3>
          <p class="font-weight-bold">Planillas por aprobar</p>
        </div>
        <div class="icon">
          <i class="fas fa-question"></i>
        </div>
        <a href="#" class="small-box-footer hide"><br></a>
      </div>    
    </div>
    <div class="col-6 col-sm-6 col-md-3 <?php if($p94<=0){ echo "hide";}?>" onclick="javascript:window.location.href='#/Jornada/Planillas?TIP=Cerradas&TIT=Planillas Cerradas'">
      <div class="small-box bg-orange">
        <div class="inner">
          <h3 id="divaprobadas">0</h3>
          <p class="font-weight-bold">Planillas Cerradas</p>
        </div>
        <div class="icon">
          <i class="fas fa-check"></i>
        </div>
        <a href="#" class="small-box-footer hide"><br></a>
      </div>  
    </div>
    <!-- /.col -->
    <div class="col-6 col-sm-6 col-md-3 <?php if($p95<=0){ echo "hide";}?>" onclick="javascript:window.location.href='#/Liquidar/List?TIP=PorAprobar&TIT=Liquidaciones por Aprobar'">
      <div class="small-box bg-warning">
        <div class="inner">
          <h3 id="divproformasaprobar">0</h3>
          <p class="font-weight-bold">Liquidaciones Por Aprobar</p>
        </div>
        <div class="icon">
          <i class="fas fa-question"></i>
        </div>
        <a href="#" class="small-box-footer hide"><br></a>
      </div>  
   
    </div>
    <!-- /.col -->
   
    <div class="col-6 col-sm-6 col-md-3 <?php if($p97<=0){ echo "hide";}?>"  onclick="javascript:window.location.href='#/Liquidar/List?TIP=PorFacturar&TIT=Liquidaciones por Factura'">
      <div class="small-box bg-purple">
        <div class="inner">
          <h3 id="divproformasporfacturar">0</h3>
          <p class="font-weight-bold">Liquidaciones por facturar</p>
        </div>
        <div class="icon">
          <i class="fas fa-clock"></i>
        </div>
        <a href="#" class="small-box-footer hide"><br></a>
      </div>  
      
    </div>
    <!-- /.col -->

    <!-- fix for small devices only -->
    <div class="clearfix hidden-md-up"></div>

    <!-- /.col -->
    
    <div class="col-6 col-sm-6 col-md-3 <?php if($p98<=0){ echo "hide";}?>"   onclick="javascript:window.location.href='#/Liquidar/List?TIP=Facturadas&TIT=Facturas'">
      <div class="small-box bg-success">
        <div class="inner">
          <h3 id="divfacturas">0</h3>
          <p class="font-weight-bold">Liquidaciones Facturadas</p>
        </div>
        <div class="icon">
          <i class="fas fa-money-check"></i>
        </div>
        <a href="#" class="small-box-footer hide"><br></a>
      </div>  
    </div>
    <div class="col-6 col-sm-6 col-md-3 <?php if($p96<=0){ echo "hide";}?>" onclick="javascript:window.location.href='#/Liquidar/List?TIP=Rechazadas&TIT=Liquidaciones Rechazadas'">
      <div class="small-box bg-danger">
        <div class="inner">
          <h3 id="divproformasrechazadas">0</h3>
          <p class="font-weight-bold">Liquidaciones Rechazadas</p>
        </div>
        <div class="icon">
          <i class="fas fa-ban"></i>
        </div>
        <a href="#" class="small-box-footer hide"><br></a>
      </div>  
    
    </div>
    <div class="col-6 col-sm-6 col-md-3 <?php if($p116<=0){ echo "hide";}?>"   onclick="javascript:window.location.href='#/Liquidar/Compensatorios'">
      <div class="small-box bg-info">
        <div class="inner">
          <h3 id="divcompensatorios">&nbsp;</h3>
          <p class="font-weight-bold">Compensatorios</p>
        </div>
        <div class="icon">
          <i class="fas fa-calendar"></i>
        </div>
        <a href="#" class="small-box-footer hide"><br></a>
      </div>  
    </div>
    <div class="col-6 col-sm-6 col-md-3 <?php if($p122<=0){ echo "hide";}?>"   onclick="javascript:window.location.href='#/Liquidar/LiquidarObras?TIP=Pendientes&TIT=Liquidaciones Obra Pendiente'">
      <div class="small-box bg-gray-light">
        <div class="inner">
          <h3 id="divpendienteobra">&nbsp;</h3>
          <p class="font-weight-bold">Obras por Liquidar</p>
        </div>
        <div class="icon">
          <i class="fas fa-calendar"></i>
        </div>
        <a href="#" class="small-box-footer hide"><br></a>
      </div>  
    </div>
    <div class="col-6 col-sm-6 col-md-3 <?php if($p122<=0){ echo "hide";}?>"   onclick="javascript:window.location.href='#/Liquidar/LiquidarObras?TIP=Generadas&TIT=Liquidaciones de Obra Generadas'">
      <div class="small-box bg-gradient-blue">
        <div class="inner">
          <h3 id="divporaprobarobra">&nbsp;</h3>
          <p class="font-weight-bold">Liquidaciones de Obra Generadas</p>
        </div>
        <div class="icon">
          <i class="fas fa-calendar"></i>
        </div>
        <a href="#" class="small-box-footer hide"><br></a>
      </div>  
    </div>
    <div class="col-6 col-sm-6 col-md-3 <?php if($p38<=0){ echo "hide";}?>"   onclick="javascript:window.location.href='#/Informes/Contabilidad'">
      <div class="small-box bg-gradient-indigo">
        <div class="inner">
          <h3 id="divinformecontabilidad">&nbsp;</h3>
          <p class="font-weight-bold">Horas Contabilidad</p>
        </div>
        <div class="icon">
          <i class="fas fa-file-archive"></i>
        </div>
        <a href="#" class="small-box-footer hide"><br></a>
      </div>  
    </div>
    <div class="col-6 col-sm-6 col-md-3 <?php if($p123<=0){ echo "hide";}?>"   onclick="javascript:window.location.href='#/Informes/Horas'">
      <div class="small-box bg-gradient-lime">
        <div class="inner">
          <h3 id="divinformehoras">&nbsp;</h3>
          <p class="font-weight-bold">Informes de Horas</p>
        </div>
        <div class="icon">
          <i class="fas fa-file"></i>
        </div>
        <a href="#" class="small-box-footer hide"><br></a>
      </div>  
    </div>
   
    <!-- /.col -->

</div>
<div class="row hide" id="divnuevacorrespondencia">
    <div class="col-12 col-sm-12 col-md-12" id="divinforden">
    </div>    
    <div class="col-12 col-sm-6 col-md-6" id="divresumen"></div>
</div>
<div class="row">
  <div class="col-lg-1 col-md-2">
      <select id="busano" name="busano" class="form-control form-control-sm selectpicker" title="Año" onchange="CRUDINFORMES('GRAFICO')">
        <option  value=''>Todos</option>
        <?php 
          $gen -> cargarAnos(2020,date('Y'),$ano,"DESC",1);
        ?>
      </select>
  </div>
</div>
<div class="row" id="divinforme"></div>
