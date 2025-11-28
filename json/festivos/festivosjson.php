<?php
include '../../data/db.config.php';
include '../../data/conexion.php';
session_start();// activa la variable de sesion
error_reporting(0);
$usuario   = $_COOKIE['usuarioPo'];
$idUsuario = $_COOKIE['idusuarioPo'];
$perfil    = $_SESSION["perfilPo"];
include '../../data/validarpermisos.php';
$fec = $_POST['fec'];
$des = $_POST['des'];
$table = 'tbl_festivos';
$primaryKey = 'f.fes_clave_int';

$p5 =  isset($permisosUsuario[5]) ?? 0;
$p6 =  isset($permisosUsuario[6]) ?? 0;

$columns = array(
    array(
        'db' => 'f.fes_clave_int',
        'dt' => 'DT_RowId', 'field' => 'fes_clave_int',
        'formatter' => function( $d, $row ) {
            return 'rowf_'.$d;
        }
    ),       
    array( 'db' => 'f.fes_clave_int',  'dt' => 'Clave',    'field' => 'fes_clave_int' ),  
    array( 'db' => 'f.fes_fecha',     'dt' => 'Fecha',   'field' => 'fes_fecha' ),  
    array( 'db' => 'f.fes_descripcion',  'dt' => 'Descripcion', 'field' => 'fes_descripcion' ),     
    array( 'db' => 'f.fes_clave_int',  'dt' => 'Eliminar', 'field' => 'fes_clave_int','formatter'=>function($d,$row){		
        global $p6;
        $edicion = "";
        if($p6>0)
        {	
            $edicion.="<a class='btn btn-danger rounded-circle btn-sm' onclick=CRUDFESTIVOS('ELIMINAR','".$d."') title='Eliminar Festivo'><i class='fas fa-trash'></i></a>";
        }
        return $edicion;
    }),
    array( 'db' => 'f.fes_clave_int', 'dt' => 'Editar', 'field' => 'fes_clave_int','formatter'=>function($d,$row){		
        global $p5;
        $edicion = "";
        if($p5>0)
        {	
            $edicion.="<a class='btn btn-warning rounded-circle btn-sm' onclick=CRUDFESTIVOS('EDITAR','".$d."') title='Editar Festivo' data-toggle='modal' data-target='#myModal'><i class='fas fa-edit'></i></a>";
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

$extraWhere = " (f.fes_descripcion LIKE REPLACE('%".$des."%',' ','%') OR '".$des."' IS NULL OR '".$des."' = '')  ";
if($fec){
    $extraWhere .= " AND f.fes_fecha = '".$fec."' ";
}
$groupBy = '';
$with = '';
$joinQuery = "FROM tbl_festivos f";
echo json_encode(SSP::simple($_POST, $sql_details, $table, $primaryKey, $columns, $joinQuery, $extraWhere, $groupBy,$with));
