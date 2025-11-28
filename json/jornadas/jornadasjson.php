<?php
include '../../data/db.config.php';
include '../../data/conexion.php';
session_start();// activa la variable de sesion
error_reporting(0);
$usuario   = $_COOKIE['usuarioPo'];
$idUsuario = $_COOKIE['idusuarioPo'];
$perfil    = $_SESSION["perfilPo"];
include '../../data/validarpermisos.php';

$table = 'tbl_jornada';
// Table's primary key
$primaryKey = 'j.jor_clave_int';

$ano = $_POST['ano'];
$sem = $_POST['sem'];
$emp = $_POST['emp'];
$emp = implode(', ', (array)$emp); if($emp==""){$emp1="''";}else {$emp1=$emp;}
$obr = $_POST['obr'];
$obr = implode(', ', (array)$obr); if($obr==""){$obr1="''";}else {$obr1=$obr;}
$est = $_POST['est'];

$p52 = isset($permisosUsuario[52]);
$p51 = isset($permisosUsuario[51]);
$p99 = isset($permisosUsuario[99]);
// parameter names
$columns = array(
	array(
		'db' => 'j.jor_clave_int',
		'dt' => 'DT_RowId', 'field' => 'jor_clave_int',
		'formatter' => function( $d, $row ) {
			// Technically a DOM id cannot start with an integer, so we prefix
			// a string. This can also be useful if you have multiple tables
			// to ensure that the id is unique with a different prefix
			return 'rowj_'.$d;
		}
	),	
        
    array( 'db' => 'j.jor_clave_int', 'dt' => 'Editar', 'field' => 'jor_clave_int','formatter'=>function($d,$row){
        $est = $row[9];
        $cre = $row[12];
        global $idUsuario, $p99;
        $d =  encrypt($d,'p4v4sAp');
        if((/*($est==0 || $est==3) and*/ $idUsuario==$cre ) /*|| ($p99>0 and $est==1)*/){
            return "<a class='btn btn-circle btn-warning btn-xs' href='#/Jornada/Edit?ID=".$d."' title='Editar Jornada' style='heigth:22px; width:22px'><i class='fas fa-edit'></i></a>";    
        }else{
            return "";
        }       
    
    }),
    array( 'db' => 'j.jor_clave_int', 'dt' => 'Eliminar', 'field' => 'jor_clave_int','formatter'=>function($d,$row){
        $est = $row[9];
        global $idUsuario, $p99;
        $cre = $row[12];
        if(($est==0 and $idUsuario==$cre) ){
            return "<a class='btn btn-circle  btn-danger btn-xs' onClick=CRUDJORNADA('ELIMINAR','".$d."') title='Eliminar Jornada' ><i class='fa fa-trash'></i></a>";
        }
    }),			
	array( 'db' => 'j.jor_ano', 'dt' => 'Ano', 'field' => 'jor_ano'),
	array( 'db' => 'j.jor_semana', 'dt' => 'Semana', 'field' => 'jor_semana' ),
    array( 'db' => 'u.usu_documento', 'dt' => 'Nit', 'field' => 'usu_documento' ),
    array( 'db' => "CONCAT(u.usu_nombre,' ',u.usu_apellido)", 'dt' => 'Empleado', 'as'=>'Empleado', 'field' => 'Empleado'),
    array( 'db' => 'j.jor_fec_creacion', 'dt' => 'FechaRegistro', 'field' => 'jor_fec_creacion'),    
    array( 'db' => 'j.jor_nota', 'dt' => 'Nota', 'field' => 'jor_nota'),
 	array( 'db' => 'j.jor_estado', 'dt' => 'Estado', 'field' => 'jor_estado','formatter'=>function($d,$row){
       
        if($d==0){ $est = "Por Aprobar"; }else if($d==1 ){ $est = "Cerrada"; }
        
         return $est;
    }),
 	array( 'db' => 'j.jor_clave_int', 'dt' => 'Clave', 'field' => 'jor_clave_int'),
    array( 'db' => 'j.jor_clave_int', 'dt' => 'Aprobar', 'field' => 'jor_clave_int','formatter'=>function($d,$row){

        $est = $row[9];
        global $p52,$p51, $idUsuario;
        $cre = $row[12];
        $d =  encrypt($d,'p4v4sAp');
        if( $p52>0 || $p51>0 and ($est==0))
        {
             return "<a class='btn btn-circle btn-success btn-xs' href='#/Jornada/Approved?ID=".$d."'  title='
            Aprobar Planilla'  data-toggle='modal' data-target='#mymodal'><i class='fas fa-check-double text-white'></i></a>";
        }
        else if($est==1 and $idUsuario==$cre)
        {
            return "";
        }
        else 
        {
            return "";
        } 
    }),
    array( 'db' => 'j.jor_creacion', 'dt' => 'Creacion', 'field' => 'jor_creacion'),   
    array( 'db' => "CONCAT(u2.usu_nombre,' ',u2.usu_apellido)", 'dt' => 'CreadaPor', 'as'=>'CreadaPor', 'field'=>'CreadaPor'),
    array( 'db' => 'j.jor_total', 'dt' => 'Total', 'field' => 'jor_total' ),
    array( 'db' => "MIN(jd.jod_fecha)", 'dt' => 'FechaMin', 'as'=>'FechaMin', 'field' => 'FechaMin'),
    array( 'db' => "MAX(jd.jod_fecha)", 'dt' => 'FechaMax', 'as'=>'FechaMax', 'field' => 'FechaMax'),
   
);

$sql_details = array(
    'user' => 'usrpavashtg',
    'pass' => '9A12)WHFy$2p4v4s',
    'db'   => 'bdpotenco',
    'host' => '127.0.0.1'
);

/* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * If you just want to use the basic configuration for DataTables with PHP
 * server-side, there is no need to edit below this line.
 */

require( '../../data/ssp.class.php' );
$whereAll = "";
$groupBy = ' j.jor_clave_int';
$with = '';
$joinQuery = "";

$joinQuery = " FROM tbl_jornada j 
join tbl_jornada_dias jd on jd.jor_clave_int = j.jor_clave_int 
JOIN  tbl_jornada_horas jh on jh.jod_clave_int = jd.jod_clave_int 
JOIN tbl_usuarios u on u.usu_clave_int = j.usu_clave_int 
LEFT JOIN tbl_usuarios u2 on u2.usu_clave_int = j.jor_creacion 
LEFT OUTER JOIN tbl_obras o on o.obr_clave_int = jh.obr_clave_int ";
 
$wh = "";
if($est=="" || $est=="Todos")
{

}
if($est=="PorAprobar")
{
  $wh.= " and j.jor_estado = 0";
}
else
if($est=="Cerradas")
{
  $wh.= " and j.jor_estado = 1";
}
else
if($est=="Liquidadas")
{
  $wh.= " and j.jor_estado = 2";
}
$extraWhere =" (j.jor_ano  = '".$ano."' OR '".$ano."' IS NULL OR '".$ano."' = '') and (j.jor_semana  = '".$sem."' OR '".$sem."' IS NULL OR '".$sem."' = '') and (j.usu_clave_int  in(".$emp1.")  OR '".$emp."' IS NULL OR '".$emp."' = '') and (o.obr_clave_int in(".$obr1.")  OR '".$obr."' IS NULL OR '".$obr."' = '')".$wh;

echo json_encode(
	SSP::simple($_POST, $sql_details, $table, $primaryKey, $columns, $joinQuery, $extraWhere, $groupBy,$with )
);