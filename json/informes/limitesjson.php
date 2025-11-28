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
// $emp = implode(', ', (array)$emp); if($emp==""){$emp1="''";}else {$emp1=$emp;}
$ano = $_POST['ano'];
$sem = $_POST['sem'];
$tip = $_POST['tip'];

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
    array( 'db' => "concat(u.usu_apellido,' ', u.usu_nombre)", 'dt' => 'Nombre',      'as' => 'Nombre',      'field' => 'Nombre' ),   
    array( 'db' => "date_format(ld.lid_fecha,'%Y')",   'dt' => 'Ano',   'as' => 'Ano',   'field' => 'Ano' ),   
    array( 'db' => "concat(DATE_FORMAT(ld.lid_fecha,'%y'),week(ld.lid_fecha))", 'dt' => 'Semana', 'as' => 'Semana', 'field' => 'Semana' ),   
    array( 'db' => "min(ld.lid_fecha)",   'dt' => 'Desde',   'as' => 'Desde',   'field' => 'Desde' ),
    array( 'db' => "max(ld.lid_fecha)",   'dt' => 'Hasta',   'as' => 'Hasta',   'field' => 'Hasta' ),  
    array( 'db' => "sum(ld.lid_hedo+ld.lid_hedf+ld.lid_heno+ld.lid_henf)",     'dt' => 'Extras',  'as' => 'Extras',     'field' => 'Extras' )
);
$sql_details = array(
	'user' => 'usrpavashtg',
	'pass' => '9A12)WHFy$2p4v4s',
	'db'   => 'bdpotenco',
	'host' => 'localhost'
);

require( '../../data/ssp.class.php' );

$extraWhere=" (u.usu_clave_int  =  '".$emp."'  OR '".$emp."' IS NULL OR '".$emp."' = '') and u.est_clave_int in(1)";
$groupBy = ' Ano, Semana, u.usu_clave_int';
$with = '';
$joinQuery = "FROM tbl_liquidar_dias ld join tbl_usuarios u on u.usu_clave_int = ld.usu_clave_int";
$having = "HAVING Extras>0 and Extras>= (SELECT reg_lim_extras_semana from tbl_reglas LIMIT 1) ";
echo json_encode(SSP::simple($_POST, $sql_details, $table, $primaryKey, $columns, $joinQuery, $extraWhere, $groupBy,$with, $having));