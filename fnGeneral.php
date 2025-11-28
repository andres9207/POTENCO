<?php
session_start();
include('../data/conexion.php');
error_reporting(0);
$http_origin = $_SERVER['HTTP_ORIGIN'];
if ($http_origin == "https://pavas.com.co" || $http_origin == "http://localhost:4300")
{  
    header("Access-Control-Allow-Origin: $http_origin");
}
//header("Access-Control-Allow-Origin: https://pavas.com.co");
header("Access-Control-Allow-Headers: *");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require ('../PHPMailer-master/src/PHPMailer.php');
require ('../PHPMailer-master/src/Exception.php');
require ('../PHPMailer-master/src/SMTP.php');
require_once "../controladores/general.controller.php";
// cookie que almacena el numero de identificacion de la persona logueada
$usuario   = $_COOKIE['usuarioPo'];
$idUsuario = $_COOKIE['idusuarioPo'];
// $perfil    = $_SESSION["perfilPo"];

include '../data/validarpermisos.php';
setlocale(LC_TIME, 'es_CO.UTF-8'); 
date_default_timezone_set('America/Bogota');
$fecha  = date("Y/m/d H:i:s");
$semanaactual = date('W',  strtotime($fecha));

$opcion = $_POST['opcion'];

if($opcion=="CARGAROBRA")
{
    $obr = new General();
    $obr -> cargarObras('',2);
}
else if($opcion=="CARGARMES")
{
    $ano = $_POST['ano'];
    $sel = $_POST['val'];
    $mes = new General();
    $mes -> cargarMes($ano,1,date('m'),12,$sel,"DESC");
}
else if($opcion=="CARGARSEMANAS")
{
    // error_reporting(E_ALL);
    $ano = $_POST['ano'];
    if($ano=="" || $ano==NULL)
    {
        $ano = date('Y');
    }
    $emp = $_POST['emp'];
    //$mes = $_POST['mes'];
    if($ano==date('Y'))
    {
        // $sf = (int)$semanaactual;
        // if(date('MM')=="12" and $sf==1)
        // {
        //     $sf = 52;
        // }

        $sf = 52;
    }
    else
    {
        $sf = 52;
    }
    $sem = new General();
    $sem->cargarSemanas($ano,$mes,1,$sf,"","DESC",2,$emp);    	
}
else if($opcion=="CARGARHORAS")
{
    $hora  = $_POST['hora'];
    $sel   = $_POST['val'];
    $hf    = $_POST['hf'];
    $horas = new General();
    $horas -> cargarHoras($hora,$sel,$hf,2);
}
else if($opcion=="GUARDAR")
{
    $emp = $_POST['emp'];
    $sem = $_POST['sem'];
    $veri = mysqli_query($conectar, "SELECT jor_id from jornada where use_id = '".$emp."' and jor_semana = '".$sem."'");
    $numv = mysqli_num_rows($veri);
    if($numv>0)
    {
        $datv = mysqli_fetch_array($veri);
        $id = $datv['jor_id'];
    }
    else
    {
        $inse = mysqli_query($conectar,"");
        if($inse>0)
        
        {
            $id = mysqli_insert_id($conectar);
        }
    }
}