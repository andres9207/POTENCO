<?php
include '../../data/db.config.php';
include '../../data/conexion.php';

session_start();// activa la variable de sesion
error_reporting(E_ALL);
$usuario   = $_COOKIE['usuarioPo'];
$idUsuario = $_COOKIE['idusuarioPo'];
$perfil    = $_SESSION["perfilPo"];
include '../../data/validarpermisos.php';


$nom = $_POST['nom'];
$table = 'tbl_perfil';

$primaryKey = 'p.prf_clave_int';

$columns = array(
	array(
		'db' => 'p.prf_clave_int',
		'dt' => 'DT_RowId', 'field' => 'prf_clave_int',
		'formatter' => function( $d, $row ) {
			return 'row_'.$d;
		}
	),	
	array( 'db' => 'p.prf_nombre',    'dt' => 'Nombre',   'field' => 'prf_nombre' ),      
	array( 'db' => 'p.est_clave_int', 'dt' => 'Estado',   'field' => 'est_clave_int','formatter' => function( $d, $row ) {
		//return $d;   text-transform: lowercase;
		if($d==1)
		{
			return '<input type="checkbox" disabled value="" checked>';
		}
		else
		{
			//return $row[4];
			return '<input type="checkbox" disabled value="">';
		}
	}),		
	array( 'db' => 'p.prf_clave_int', 'dt' => 'Editar', 'field' => 'prf_clave_int','formatter'=>function($d,$row){

		global $permisosUsuario;
		$p105 = isset($permisosUsuario[105]);
		$edicion  = "";
		if($p105>0)
		{
			$edicion.="<a class='btn btn-warning btn-sm rounded-circle' onClick=CRUDPERFIL('EDITAR','".$d."') title='Editar Perfil'  data-toggle='modal' data-target='#myModal'><i class='fas fa-edit'></i></a>";
		}
		return $edicion;
	}),
	array( 'db' => 'p.prf_clave_int', 'dt' => 'Eliminar', 'field' => 'prf_clave_int','formatter'=>function($d,$row){

		global $permisosUsuario;
		$p106 = isset($permisosUsuario[106]);
		$edicion  = "";
		if($p106>0)
		{
			$edicion.="<a class='btn btn-danger btn-sm rounded-circle' onclick=CRUDPERFIL('ELIMINAR','".$d."') title='Eliminar Perfil'><i class='fas fa-trash'></i></a>";
		}
		return $edicion;
   	}),
   	array( 'db' => 'p.prf_clave_int', 'dt' => 'Permisos', 'field' => 'prf_clave_int','formatter'=>function($d,$row){

		global $permisosUsuario;
		$p108 = isset($permisosUsuario[108]);
		$edicion  = "";
		if($p108)
		{	
			$edicion.="<a class='btn btn-info btn-sm rounded-circle' onClick=CRUDPERFIL('ASIGNARPERMISOS','".$d."') title='Asignar permisos' data-toggle='modal' data-target='#myModal'><i class='fas fa-lock'></i></a>";
		}
		return $edicion;
	})

);
$sql_details = array(
	'user' => 'usrpavashtg',
	'pass' => '9A12)WHFy$2p4v4s',
	'db'   => 'bdpotenco',
	'host' => 'localhost'
);

require( '../../data/ssp.class.php' );

$extraWhere="(`p`.`prf_nombre` LIKE REPLACE('".$nom."%',' ','%') OR '".$nom."' IS NULL OR '".$nom."' = '')  and p.est_clave_int not in(3)";

$groupBy = ' p.prf_clave_int';
$with = '';
$joinQuery = "FROM tbl_perfil p";
echo json_encode(SSP::simple($_POST, $sql_details, $table, $primaryKey, $columns, $joinQuery, $extraWhere, $groupBy,$with));