<?php
include '../../data/db.config.php';
include '../../data/conexion.php';
session_start();// activa la variable de sesion
error_reporting(0);
$usuario   = $_COOKIE['usuarioPo'];
$idUsuario = $_COOKIE['idusuarioPo'];
$perfil    = $_SESSION["perfilPo"];
include '../../data/validarpermisos.php';
$nom = $_POST['nom'];
$doc = $_POST['doc'];
$table = 'tbl_cencos';
$primaryKey = 'c.cen_clave_int';

$p118 =  isset($permisosUsuario[118]) ?? 0;
$p119 =  isset($permisosUsuario[119]) ?? 0;

$columns = array(
    array(
        'db' => 'c.cen_clave_int',
        'dt' => 'DT_RowId', 'field' => 'cen_clave_int',
        'formatter' => function( $d, $row ) {
            return 'rows_'.$d;
        }
    ),       
    array( 'db' => 'c.cen_clave_int',  'dt' => 'Clave',    'field' => 'cen_clave_int' ),  
    array( 'db' => 'c.cen_nombre',     'dt' => 'Nombre',   'field' => 'cen_nombre' ),  
    array( 'db' => 'c.cen_codigo',  'dt' => 'Codigo', 'field' => 'cen_codigo' ),  
    array( 'db' => 'c.cen_membrete',  'dt' => 'Membrete', 'field' => 'cen_membrete','formatter'=>function($d,$row){		
        global $p119;
        $edicion = "";
        $edicion.="<a id='view".$row[1]."' class='btn btn-info btn-sm fullsizable' href='".$d."' target='_blank' title='Clic para ver membrete'><i class='fas fa-eye'></i></a>";
        
        return $edicion;
    }),
    array( 'db' => 'c.cen_clave_int',  'dt' => 'Eliminar', 'field' => 'cen_clave_int','formatter'=>function($d,$row){		
        global $p119;
        $edicion = "";
        if($p119>0)
        {	
            $edicion.="<a class='btn btn-danger rounded-circle btn-sm' onclick=CRUDCENCOS('ELIMINAR','".$d."') title='Eliminar Centro de Costos'><i class='fas fa-trash'></i></a>";
        }
        return $edicion;
    }),
    array( 'db' => 'c.cen_clave_int', 'dt' => 'Editar', 'field' => 'cen_clave_int','formatter'=>function($d,$row){		
        global $p118;
        $edicion = "";
        if($p118>0)
        {	
            $edicion.="<a class='btn btn-warning rounded-circle btn-sm' onclick=CRUDCENCOS('EDITAR','".$d."') title='Editar Centro de Costos' data-toggle='modal' data-target='#myModal'><i class='fas fa-edit'></i></a>";
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

$extraWhere = " (c.cen_nombre LIKE REPLACE('%".$nom."%',' ','%') OR '".$nom."' IS NULL OR '".$nom."' = '') and (c.cen_codigo LIKE REPLACE('%".$cod."%',' ','%') OR '".$cod."' IS NULL OR '".$cod."' = '') and c.est_clave_int != 3 ";
$groupBy = '';
$with = '';
$joinQuery = "FROM tbl_cencos c";
echo json_encode(SSP::simple($_POST, $sql_details, $table, $primaryKey, $columns, $joinQuery, $extraWhere, $groupBy,$with));
