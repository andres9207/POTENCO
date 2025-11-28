<?php 
 session_start();
 include('../../data/conexion.php');
 error_reporting(0);
 $usuario   = $_COOKIE['usuarioPo'];
 $idUsuario = $_COOKIE['idusuarioPo'];
 $perfil    = $_SESSION["perfilPo"];
 $IP = $_SERVER['REMOTE_ADDR'];
 //include '../../data/validarpermisos.php';
 setlocale(LC_TIME, 'es_CO.UTF-8'); 
date_default_timezone_set('America/Bogota');
 $fecha  = date("Y/m/d H:i:s");
?>
<input id="tipoinforme" value="" type="hidden">
<div class="row" id="divfiltros"></div>
<div class="row mt-2">
    <div class="col-md-12 table-responsive" id="tabladatos"></div>
</div>
