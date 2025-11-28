<?php
include '../../data/db.config.php';
include "../../data/conexion.php";
session_start();// activa la variable de sesion
error_reporting(0);
$usuario   = $_COOKIE['usuarioPo'];
$idUsuario = $_COOKIE['idusuarioPo'];
$perfil    = $_SESSION["perfilPo"];
include '../../data/validarpermisos.php';
$id = $_POST['id'];
$table = 'tbl_labores';
$primaryKey = 'l.lab_clave_int';

$p122 = isset($permisosUsuario[122]);

$columns = array(
    array(
        'db' => 'l.lab_clave_int',
        'dt' => 'DT_RowId', 'field' => 'lab_clave_int',
        'formatter' => function( $d, $row ) {
            return 'rowla_'.$d;
        }
    ),       
    array( 'db' => 'l.lab_clave_int',  'dt' => 'Clave',   'field' => 'lab_clave_int' ),  
    array( 'db' => 'l.lab_nombre',     'dt' => 'Nombre',   'field' => 'lab_nombre' ),  
    array( 'db' => 'l.lab_descripcion', 'dt' => 'Descripcion',   'field' => 'lab_descripcion' ),  
    array( 'db' => 'l.lab_clave_int', 'dt' => 'Edicion', 'field' => 'lab_clave_int','formatter'=>function($d,$row){		
        global $p122;
        $edicion = "";
        if($p122>0)
        {	
            $edicion.="<a class='btn btn-danger btn-sm' onclick=CRUDCENCOS('ELIMINARLABOR','".$d."') title='Eliminar Labor'><i class='fas fa-trash'></i></a>";
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

$extraWhere = " l.cen_clave_int = '".$id."' and l.est_clave_int!=3";
$groupBy = '';
$with = '';
$joinQuery = "FROM tbl_labores l";
echo json_encode(SSP::simple($_POST, $sql_details, $table, $primaryKey, $columns, $joinQuery, $extraWhere, $groupBy,$with));

