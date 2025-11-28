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
// $sem = $_POST['sem'];

$table = 'tbl_liquidar_dias';
if($tip==2 || $tip==4)
{
    $table = "tbl_liquidar_dias_obra";
}
$primaryKey = 'ld.lid_clave_int';

$columns = array(
    array(
        'db' => "date_format(ld.lid_fecha, '%Y%m')",
        'dt' => 'DT_RowId', 'as'=>'DT_RowId', 'field' => 'DT_RowId',
        'formatter' => function( $d, $row ) {
            return 'rowd_'.$d;
        }
    ),       
    array( 'db' => "date_format(ld.lid_fecha, '%Y%m')",'dt' => 'Mes',  'as' => 'Mes','field' => 'Mes' ), 
    array( 'db' => "SUM(IF(diaHabil(ld.lid_fecha) = 1 and ld.lid_horas>0,1,0))",   'dt' => 'Generados',   'as' => 'Generados',   'field' => 'Generados' ),   
    array( 'db' => "SUM(IF(diaHabil(ld.lid_fecha) = 1 and ld.lid_horas>0  and t.tin_suma = 4,1,0))", 'dt' => 'Compensados', 'as' => 'Compensados', 'field' => 'Compensados' ),   
    array( 'db' => "SUM(IF(diaHabil(ld.lid_fecha) = 1 and ld.lid_horas>0  and t.tin_suma = 5,1,0))", 'dt' => 'Remunerados', 'as' => 'Remunerados', 'field' => 'Remunerados' ),   
    array( 'db' => "SUM(IF(t.tin_suma = 7,1,0))",     'dt' => 'Tomados',     'as' => 'Tomados',     'field' => 'Tomados' )   
);
$sql_details = array(
	'user' => 'usrpavashtg',
	'pass' => '9A12)WHFy$2p4v4s',
	'db'   => 'bdpotenco',
	'host' => 'localhost'
);

require( '../../data/ssp.class.php' );

$extraWhere=" (ld.usu_clave_int = '".$emp."' )";

$joinQuery = "FROM tbl_liquidar_dias ld";

if($tip==2 || $tip==4)
{
    $joinQuery = "FROM tbl_liquidar_dias_obra ld";
}
$joinQuery.=" LEFT OUTER JOIN tbl_tipo_novedad t on t.tin_clave_int = ld.tin_clave_int";
$groupBy = ' Mes';

$having =  'HAVING Generados>=3 or Compensados>0 or Tomados>0 or Remunerados>0';
$with = '';

echo json_encode(SSP::simple($_POST, $sql_details, $table, $primaryKey, $columns, $joinQuery, $extraWhere, $groupBy,$with, $having));

