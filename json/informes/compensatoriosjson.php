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
$emp = implode(', ', (array)$emp); if($emp==""){$emp1="''";}else {$emp1=$emp;}

$table = 'tbl_usuarios';

$primaryKey = 'u.usu_clave_int';

$columns = array(
    array(
        'db' => 'u.usu_clave_int',
        'dt' => 'DT_RowId', 'field' => 'usu_clave_int',
        'formatter' => function( $d, $row ) {
            return 'row_'.$d;
        }
    ),  
    array( 'db' => "u.usu_clave_int", 'dt' => 'Clave', 'field' => 'usu_clave_int' ),      
    array( 'db' => "concat(u.usu_apellido,' ', u.usu_nombre)",        'dt' => 'Nombre',      'as' => 'Nombre',      'field' => 'Nombre' ),   
    array( 'db' => "SUM(IF(uc.generados>=3,uc.generados,0))",   'dt' => 'Generados',   'as' => 'Generados',   'field' => 'Generados' ),   
    array( 'db' => "SUM(uc.compensados)", 'dt' => 'Compensados', 'as' => 'Compensados', 'field' => 'Compensados' ),   
    array( 'db' => "SUM(uc.remunerados)", 'dt' => 'Remunerados', 'as' => 'Remunerados', 'field' => 'Remunerados' ),   
    array( 'db' => "SUM(uc.tomados)",     'dt' => 'Tomados',     'as' => 'Tomados',     'field' => 'Tomados' )
);
$sql_details = array(
	'user' => 'usrpavashtg',
	'pass' => '9A12)WHFy$2p4v4s',
	'db'   => 'bdpotenco',
	'host' => 'localhost'
);

require( '../../data/ssp.class.php' );

$extraWhere=" (u.usu_clave_int  in(".$emp1.")  OR '".$emp."' IS NULL OR '".$emp."' = '') and u.est_clave_int in(1)";
$extraWhereFrom=" AND (ld.usu_clave_int  in(".$emp1.")  OR '".$emp."' IS NULL OR '".$emp."' = '')";
$groupBy = ' u.usu_clave_int';
$with = '';
$joinQuery = "FROM tbl_usuarios u JOIN (
        SELECT ld.usu_clave_int usu, date_format(ld.lid_fecha, '%Y%m') mes, 
        SUM(IF(diaHabil(ld.lid_fecha) = 1 and ld.lid_horas>0,1,0)) generados,
        SUM(IF(diaHabil(ld.lid_fecha) = 1 and ld.lid_horas>0  and t.tin_suma = 4,1,0)) compensados,
        SUM(IF(diaHabil(ld.lid_fecha) = 1 and ld.lid_horas>0  and t.tin_suma = 5,1,0)) remunerados,
        SUM(IF(t.tin_suma = 7,1,0)) tomados
        FROM tbl_liquidar_dias ld join tbl_tipo_novedad t on t.tin_clave_int = ld.tin_clave_int 
        WHERE diaHabil(lid_fecha) in (1,0)   ".$extraWhereFrom."
        GROUP BY mes, usu
        HAVING generados >=3  OR remunerados>0 OR tomados>0 OR compensados>0
    ) as uc ON uc.usu = u.usu_clave_int ";
$having = "HAVING Generados > 0 OR Remunerados>0 OR Tomados>0 OR Compensados>0";
echo json_encode(SSP::simple($_POST, $sql_details, $table, $primaryKey, $columns, $joinQuery, $extraWhere, $groupBy,$with, $having));