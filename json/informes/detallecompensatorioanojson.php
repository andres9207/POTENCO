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
$primaryKey = 'ta.Anio';

$columns = array(
    array(
        'db' => "ta.Anio",
        'dt' => 'DT_RowId', 'as'=>'DT_RowId', 'field' => 'DT_RowId',
        'formatter' => function( $d, $row ) {
            return 'rowd_'.$d;
        }
    ),       
    array( 'db' => "ta.Anio",'dt' => 'Ano',  'as' => 'Ano','field' => 'Ano' ), 
    array( 'db' => "SUM(IF(ta.generados>=3,ta.generados,0))",   'dt' => 'Generados',   'as' => 'Generados',   'field' => 'Generados' ),   
    array( 'db' => "SUM(ta.compensados)", 'dt' => 'Compensados', 'as' => 'Compensados', 'field' => 'Compensados' ),   
    array( 'db' => "SUM(ta.remunerados)", 'dt' => 'Remunerados', 'as' => 'Remunerados', 'field' => 'Remunerados' ),   
    array( 'db' => "SUM(ta.tomados)",     'dt' => 'Tomados',     'as' => 'Tomados',     'field' => 'Tomados' )
    
);
$sql_details = array(
	'user' => 'usrpavashtg',
	'pass' => '9A12)WHFy$2p4v4s',
	'db'   => 'bdpotenco',
	'host' => 'localhost'
);

require( '../../data/ssp.class.php' );

$extraWhere=""; // (ld.usu_clave_int = '".$emp."' )";
$tb = $tip==2 || $tip==4 ? 'tbl_liquidar_dias_obra':'tbl_liquidar_dias';

$joinQuery = "FROM (SELECT
    DATE_FORMAT(ld.lid_fecha, '%Y') AS Anio,
    DATE_FORMAT(ld.lid_fecha, '%Y%m') AS Mes,
    SUM(IF(diaHabil(ld.lid_fecha) = 1 AND ld.lid_horas > 0, 1, 0)) AS generados,
    SUM(IF(diaHabil(ld.lid_fecha) = 1 AND ld.lid_horas > 0 AND t.tin_suma = 4, 1, 0)) AS compensados,
    SUM(IF(diaHabil(ld.lid_fecha) = 1 AND ld.lid_horas > 0 AND t.tin_suma = 5, 1, 0)) AS remunerados,
    SUM(IF(t.tin_suma = 7, 1, 0)) AS tomados
    FROM
    ".$tb." ld
    LEFT OUTER JOIN
    tbl_tipo_novedad t ON t.tin_clave_int = ld.tin_clave_int
    WHERE
    ld.usu_clave_int = '".$emp."'
    GROUP BY
    Anio, Mes
    HAVING
    Generados >=3 OR Remunerados>0 OR Tomados>0 OR Compensados>0) as ta";

// if($tip==2 || $tip==4)
// {
//     $joinQuery = "FROM tbl_liquidar_dias_obra ld";
// }
// $joinQuery.=" LEFT OUTER JOIN tbl_tipo_novedad t on t.tin_clave_int = ld.tin_clave_int";
$groupBy = ' Ano';
$with = '';

echo json_encode(SSP::simple($_POST, $sql_details, $table, $primaryKey, $columns, $joinQuery, $extraWhere, $groupBy,$with));

