<?php
include '../../data/db.config.php';
include '../../data/conexion.php';
session_start();// activa la variable de sesion
error_reporting(0);
$usuario   = $_COOKIE['usuarioPo'];
$idUsuario = $_COOKIE['idusuarioPo'];
$perfil    = $_SESSION["perfilPo"];
include '../../data/validarpermisos.php';

$emp = $_POST['emp'];
$tip = $_POST['tip'];
$opc = $_POST['opc'];
$sem = $_POST['sem'];

$table = 'tbl_liquidar_dias';
if($tip==2 || $tip==4)
{
    $table = "tbl_liquidar_dias_obra";
}
$primaryKey = 'ld.lid_clave_int';

$columns = array(
    array(
        'db' => 'ld.lid_clave_int',
        'dt' => 'DT_RowId', 'field' => 'lid_clave_int',
        'formatter' => function( $d, $row ) {
            return 'rowd_'.$d;
        }
    ),       
    array( 'db' => "ld.lid_fecha",'dt' => 'Fecha','field' => 'lid_fecha' ), 
    array( 'db' => "ld.lid_hi_man",'dt' => 'InicioAm','field' => 'lid_hi_man', 'formatter'=> function($d, $row){
        $hor  = ($d!="" and $row[4]>0)? date("g:i a", strtotime($d)): "";
        return $hor;
    } ), 
    array( 'db' => "ld.lid_hf_man",'dt' => 'FinAm','field' => 'lid_hf_man', 'formatter'=> function($d, $row){
        $hor  = ($d!="" and $row[4]>0)? date("g:i a", strtotime($d)): "";
        return $hor;
    }  ), 
    array( 'db' => "calculoHoras3(ld.lid_fecha,ld.lid_hi_man,ld.lid_hf_man)",'dt' => 'TotalAm', 'as'=>'TotalAm', 'field' => 'TotalAm' ), 
    array( 'db' => "ld.lid_hi_tar",'dt' => 'InicioPm','field' => 'lid_hi_tar' , 'formatter'=> function($d, $row){
        $hor  = ($d!="" and $row[7]>0)? date("g:i a", strtotime($d)): "";
        return $hor;
    } ), 
    array( 'db' => "ld.lid_hf_tar",'dt' => 'FinPm','field' => 'lid_hf_tar' , 'formatter'=> function($d, $row){
        $hor  = ($d!="" and $row[7]>0)? date("g:i a", strtotime($d)): "";
        return $hor;
    } ), 
    array( 'db' => "calculoHoras3(ld.lid_fecha,ld.lid_hi_tar,ld.lid_hf_tar)",'dt' => 'TotalPm', 'as'=>'TotalPm', 'field' => 'TotalPm' ),
    array( 'db' => "ld.lid_horas",'dt' => 'HorasDia','field' => 'lid_horas' ),   
    array( 'db' => "ld.lid_horario",'dt' => 'Horario','field' => 'lid_horario' ) , 
    array( 'db' => "ld.lid_observacion",'dt' => 'Observacion','field' => 'lid_observacion' )     
    
);
$sql_details = array(
	'user' => 'usrpavashtg',
	'pass' => '9A12)WHFy$2p4v4s',
	'db'   => 'bdpotenco',
	'host' => 'localhost'
);

require( '../../data/ssp.class.php' );

$extraWhere=" (ld.usu_clave_int = '".$emp."' )";
if($tip==1 || $tip==2)
{
    if($opc=="Generados")
    {
        $extraWhere.=" and diaHabil(ld.lid_fecha) = 1 and ld.lid_horas>0";   
    } 
    if($opc=="Remunerados")
    {
        $extraWhere.=" and t.tin_suma = 5";   
    } 
    if($opc=="Compensados")
    {
        $extraWhere.=" and diaHabil(ld.lid_fecha) = 1 and ld.lid_horas>0 and t.tin_suma = 4";   
    } 
    if($opc=="Tomados")
    {
        $extraWhere.="  and t.tin_suma = 7 "; 
    }
}
else
{   
    $extraWhere.=" and concat(DATE_FORMAT(ld.lid_fecha,'%y'),week(ld.lid_fecha)) = '".$sem."'";
}
$joinQuery = "FROM tbl_liquidar_dias ld";
if($tip==2 || $tip==4)
{
    $joinQuery = "FROM tbl_liquidar_dias_obra ld";
}
$joinQuery.=" LEFT OUTER JOIN tbl_tipo_novedad t on t.tin_clave_int = ld.tin_clave_int";
$groupBy = ' ld.lid_clave_int';
$with = '';

echo json_encode(SSP::simple($_POST, $sql_details, $table, $primaryKey, $columns, $joinQuery, $extraWhere, $groupBy,$with));

