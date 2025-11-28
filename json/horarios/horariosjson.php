<?php
include '../../data/db.config.php';
include "../../data/conexion.php";
session_start();// activa la variable de sesion
error_reporting(0);
$usuario   = $_COOKIE['usuarioPo'];
$idUsuario = $_COOKIE['idusuarioPo'];
$perfil    = $_SESSION["perfilPo"];
include '../../data/validarpermisos.php';
$nom = $_POST['nom'];

$table = 'tbl_horarios';
$primaryKey = 'h.hor_clave_int';

$p8 =  isset($permisosUsuario[8]) ?? 0;
$p9 =  isset($permisosUsuario[9]) ?? 0;

$columns = array(
    array(
        'db' => 'h.hor_clave_int',
        'dt' => 'DT_RowId', 'field' => 'hor_clave_int',
        'formatter' => function( $d, $row ) {
            return 'rowh_'.$d;
        }
    ),       
    array( 'db' => 'h.hor_clave_int',  'dt' => 'Clave',    'field' => 'hor_clave_int' ),  
    array( 'db' => 'h.hor_nombre',     'dt' => 'Nombre',   'field' => 'hor_nombre' ), 
    array( 'db' => 'h.hor_1',          'dt' => 'Lun', 'field' => 'hor_1' ),  
    array( 'db' => 'h.hor_2',          'dt' => 'Mar', 'field' => 'hor_2' ),  
    array( 'db' => 'h.hor_3',          'dt' => 'Mie', 'field' => 'hor_3' ),  
    array( 'db' => 'h.hor_4',          'dt' => 'Jue', 'field' => 'hor_4' ),  
    array( 'db' => 'h.hor_5',          'dt' => 'Vie', 'field' => 'hor_5' ),  
    array( 'db' => 'h.hor_6',          'dt' => 'Sab', 'field' => 'hor_6' ),  
    array( 'db' => 'h.hor_7',          'dt' => 'Dom', 'field' => 'hor_7' ),   
   
    array( 'db' => 'h.hor_clave_int',  'dt' => 'Eliminar', 'field' => 'hor_clave_int','formatter'=>function($d,$row){		
        global $p9;		
        $edicion = "";
        if($p9>0)
        {	
            $edicion.="<a class='btn btn-danger rounded-circle btn-sm' onclick=CRUDHORARIOS('ELIMINAR','".$d."') title='Eliminar Horario'><i class='fas fa-trash'></i></a>";
        }
        return $edicion;
    }),
    array( 'db' => 'h.hor_clave_int', 'dt' => 'Editar', 'field' => 'hor_clave_int','formatter'=>function($d,$row){		
        global $p8;
        $edicion = "";
        if($p8>0)
        {	
            $edicion.="<a class='btn btn-warning rounded-circle btn-sm' onclick=CRUDHORARIOS('EDITAR','".$d."') title='Editar Horario' data-toggle='modal' data-target='#myModal'><i class='fas fa-edit'></i></a>";
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

$extraWhere = " (h.hor_nombre LIKE REPLACE('%".$nom."%',' ','%') OR '".$nom."' IS NULL OR '".$nom."' = '') and h.est_clave_int != 3 ";
$groupBy = '';
$with = '';
$joinQuery = "FROM tbl_horarios h";
echo json_encode(SSP::simple($_POST, $sql_details, $table, $primaryKey, $columns, $joinQuery, $extraWhere, $groupBy,$with));