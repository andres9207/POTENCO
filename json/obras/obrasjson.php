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
$nompro = $_POST['nompro'];
$ubi = $_POST['ubi'];
$cen = $_POST['cen'];
$dir = $_POST['dir'];
$dir = implode(', ', (array)$dir); if($dir==""){$dir1="''";}else {$dir1=$dir;}

$est = $_POST['est'];
$est = implode(', ', (array)$est); if($est==""){$est1="''";}else {$est1=$est;}

$table = 'tbl_cencos';
$primaryKey = 'o.obr_clave_int';


$p2 =  isset($permisosUsuario[2]) ?? 0;
$p3 =  isset($permisosUsuario[3]) ?? 0;

$columns = array(
    array(
        'db' => 'o.obr_clave_int',
        'dt' => 'DT_RowId', 'field' => 'obr_clave_int',
        'formatter' => function( $d, $row ) {
            return 'rowo_'.$d;
        }
    ),       
    array( 'db' => 'o.obr_clave_int',    'dt' => 'Clave',    'field' => 'obr_clave_int' ),  
    array( 'db' => 'o.obr_nombre',       'dt' => 'Nombre',   'field' => 'obr_nombre' ),  
    array( 'db' => 'o.obr_nom_proyecto', 'dt' => 'NombreProyecto',   'field' => 'obr_nom_proyecto' ), 
    array( 'db' => 'o.obr_ubicacion',    'dt' => 'Ubicacion',   'field' => 'obr_ubicacion' ),   
    array( 'db' => 'o.obr_fec_inicio',   'dt' => 'FechaInicio',   'field' => 'obr_fec_inicio' ),   
    array( 'db' => 'o.obr_cencos',       'dt' => 'Cencos',   'field' => 'obr_cencos' ),  
    array( 'db' => "concat(u.usu_nombre,' ',usu_apellido)",  'dt' => 'Director', 'as'=>'Director', 'field' => 'Director'), 
    array( 'db' => 'o.obr_vr_operador','dt' => 'ValorOperario', 'field' => 'obr_vr_operador' ),  
    array( 'db' => 'o.obr_vr_senalero','dt' => 'ValorSenalero', 'field' => 'obr_vr_senalero' ), 
    array( 'db' => 'o.obr_vr_elevador','dt' => 'ValorElevador', 'field' => 'obr_vr_elevador' ), 
    array( 'db' => 'o.obr_vr_maquina', 'dt' => 'ValorMaquina', 'field' => 'obr_vr_maquina' ),  
    array( 'db' => 'o.obr_hr_mes',     'dt' => 'Horas', 'field' => 'obr_hr_mes' ),  
    array( 'db' => 'o.obr_hr_semana',  'dt' => 'HorasSemana', 'field' => 'obr_hr_semana' ),  
    array( 'db' => 'o.obr_lunes',      'dt' => 'Lun', 'field' => 'obr_lunes' ),  
    array( 'db' => 'o.obr_martes',     'dt' => 'Mar', 'field' => 'obr_martes' ),  
    array( 'db' => 'o.obr_miercoles',  'dt' => 'Mie', 'field' => 'obr_miercoles' ),  
    array( 'db' => 'o.obr_jueves',     'dt' => 'Jue', 'field' => 'obr_jueves' ),  
    array( 'db' => 'o.obr_viernes',    'dt' => 'Vie', 'field' => 'obr_viernes' ),  
    array( 'db' => 'o.obr_sabado',     'dt' => 'Sab', 'field' => 'obr_sabado' ),  
    array( 'db' => 'o.obr_domingo',    'dt' => 'Dom', 'field' => 'obr_domingo' ),   
   
    array( 'db' => 'o.obr_clave_int',  'dt' => 'Eliminar', 'field' => 'obr_clave_int','formatter'=>function($d,$row){		
        global $p3;
        $edicion = "";
        if($p3>0)
        {	
            $edicion.="<a class='btn btn-danger rounded-circle btn-sm' onclick=CRUDOBRAS('ELIMINAR','".$d."') title='Eliminar Obra'><i class='fas fa-trash'></i></a>";
        }
        return $edicion;
    }),
    array( 'db' => 'o.obr_clave_int', 'dt' => 'Editar', 'field' => 'obr_clave_int','formatter'=>function($d,$row){		
        global $p2;
        $edicion = "";
        if($p2>0)
        {	
            $edicion.="<a class='btn btn-warning rounded-circle btn-sm' onclick=CRUDOBRAS('EDITAR','".$d."') title='Editar Obra' data-toggle='modal' data-target='#myModal'><i class='fas fa-edit'></i></a>";
        }
        return $edicion;
    }) ,
    array( 'db' => 'e.est_nombre',    'dt' => 'Estado', 'field' => 'est_nombre' ),   
    array( 'db' => 'o.obr_contrato',  'dt' => 'Contrato', 'field' => 'obr_contrato' ),   
);
$sql_details = array(
	'user' => 'usrpavashtg',
	'pass' => '9A12)WHFy$2p4v4s',
	'db'   => 'bdpotenco',
	'host' => 'localhost'
);

require( '../../data/ssp.class.php' );

$extraWhere = " (o.obr_nombre LIKE REPLACE('%".$nom."%',' ','%') OR '".$nom."' IS NULL OR '".$nom."' = '') and (o.obr_nom_proyecto LIKE REPLACE('%".$nompro."%',' ','%') OR '".$nompro."' IS NULL OR '".$nompro."' = '') and (o.obr_ubicacion LIKE REPLACE('%".$ubi."%',' ','%') OR '".$ubi."' IS NULL OR '".$ubi."' = '') and (o.obr_cencos LIKE REPLACE('%".$cen."%',' ','%') OR '".$cen."' IS NULL OR '".$cen."' = '') and o.est_clave_int != 3 and ( o.obr_encargado  IN (".$dir1.") OR '".$dir."' IS NULL OR '".$dir."' = '') and ( o.est_clave_int  IN (".$est1.") OR '".$est."' IS NULL OR '".$est."' = '')";
$groupBy = '';
$with = '';
$joinQuery = "FROM tbl_obras o left outer join tbl_usuarios u on o.obr_encargado = u.usu_clave_int join tbl_estados e on e.est_clave_int = o.est_clave_int";
echo json_encode(SSP::simple($_POST, $sql_details, $table, $primaryKey, $columns, $joinQuery, $extraWhere, $groupBy,$with));
