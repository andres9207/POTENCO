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
    array( 'db' => "diasCompensado(u.usu_clave_int,'generados',2)",   'dt' => 'Generados',   'as' => 'Generados',   'field' => 'Generados' ),   
    array( 'db' => "diasCompensado(u.usu_clave_int,'compensados',2)", 'dt' => 'Compensados', 'as' => 'Compensados', 'field' => 'Compensados' ),   
    array( 'db' => "diasCompensado(u.usu_clave_int,'remunerados',2)", 'dt' => 'Remunerados', 'as' => 'Remunerados', 'field' => 'Remunerados' ),   
    array( 'db' => "diasCompensado(u.usu_clave_int,'tomados',2)",     'dt' => 'Tomados',     'as' => 'Tomados',     'field' => 'Tomados' )
);
$sql_details = array(
	'user' => 'usrpavashtg',
	'pass' => '9A12)WHFy$2p4v4s',
	'db'   => 'bdpotenco',
	'host' => 'localhost'
);

require( '../../data/ssp.class.php' );

$extraWhere=" (u.usu_clave_int  in(".$emp1.")  OR '".$emp."' IS NULL OR '".$emp."' = '') and u.est_clave_int in(1)";
$groupBy = ' u.usu_clave_int';
$with = '';
$joinQuery = "FROM tbl_usuarios u";
$having = "HAVING Generados > 0  or Remunerados>0 or Tomados>0 or Compensados>0";
echo json_encode(SSP::simple($_POST, $sql_details, $table, $primaryKey, $columns, $joinQuery, $extraWhere, $groupBy,$with, $having));