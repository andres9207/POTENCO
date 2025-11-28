<?php
include '../../data/db.config.php';
include '../../data/conexion.php';
session_start();// activa la variable de sesion
error_reporting(0);
$usuario   = $_COOKIE['usuarioPo'];
$idUsuario = $_COOKIE['idusuarioPo'];
$perfil    = $_SESSION["perfilPo"];
include '../../data/validarpermisos.php';
$
// DB table to use
$table = 'tbl_liquidar_obras';
// Table's primary key
$primaryKey = 'l.lio_clave_int';
$emp = $_POST['emp'];
$emp = implode(', ', (array)$emp); if($emp==""){$emp1="''";}else {$emp1=$emp;}
$obr = $_POST['obr'];
$ini = $_POST['ini'];
$fin = $_POST['fin'];

$p55 = isset($permisosUsuario[55]) ?? 0;
// parameter names
$columns = array(
	array(
		'db' => 'l.lio_clave_int',
		'dt' => 'DT_RowId', 'field' => 'lio_clave_int',
		'formatter' => function( $d, $row ) {
			// Technically a DOM id cannot start with an integer, so we prefix
			// a string. This can also be useful if you have multiple tables
			// to ensure that the id is unique with a different prefix
			return 'rowo_'.$d;
		}
	),	  
    array( 'db' => "nameUser(l.usu_liquidador)", 'dt' => 'Liquidador', 'as'=>'Liquidador', 'field' => 'Liquidador'),
    array( 'db' => 'l.lio_fec_creacion', 'dt' => 'FechaCreacion', 'field' => 'lio_fec_creacion'),    
    array( 'db' => 'l.lio_observacion', 'dt' => 'Observacion', 'field' => 'lio_observacion'),
 	array( 'db' => 'l.lio_estado', 'dt' => 'Estado', 'field' => 'lio_estado','formatter'=>function($d,$row){
       
        if($d==1){ $est = "Por Aprobar"; }else if($d==2 ){ $est = "Por Facturar"; }else if($d==3){ $est =  "Facturada"; }
        else if($d==4){ $est =  "Rechazada"; }
        
        return $est;
    }),
 	array( 'db' => 'l.lio_clave_int', 'dt' => 'Clave', 'field' => 'lio_clave_int'),
    array( 'db' => 'l.lio_clave_int', 'dt' => 'Total', 'field' => 'lio_clave_int', 'formatter'=>function($d, $row){
        $ext = $row[18];
        $val = $row[16];
        $tot = $ext * $val;
        return $tot;
    } ),
    array( 'db' => 'l.lio_clave_int', 'dt' => 'Previa', 'field' => 'lio_clave_int','formatter'=>function($d,$row){
    
        global $p55,$idUsuario, $conectar;
        $d =  encrypt($d,'p4v4sAp');       
        return "<a class='btn  btn-info rounded-circle btn-sm' title='
            Previa Liquidación' onClick=CRUDLIQUIDAR('PREVIAPROFORMAOBRA','".$d."') data-toggle='modal' data-target='#mymodal'><i class='fas fa-eye text-white'></i></a>";
    }),
    array( 'db' => 'l.lio_inicio', 'dt' => 'Desde', 'field' => 'lio_inicio' ),
    array( 'db' => 'l.lio_fin', 'dt' => 'Hasta', 'field' => 'lio_fin' ),   
    array( 'db' => 'o.obr_nombre', 'dt' => 'Obra', 'field' => 'obr_nombre' ),
    array( 'db' => 'o.obr_cencos', 'dt' => 'Cenco', 'field' => 'obr_cencos' ),
    array( 'db' => 'l.lio_codigo', 'dt' => 'Codigo', 'field' => 'lio_codigo' ),
    array( 'db' => 'l.lio_horas_maquina', 'dt' => 'HorasMaquina', 'field' => 'lio_horas_maquina' ), 
    array( 'db' => 'l.lio_horas_contrato', 'dt' => 'HorasContrato', 'field' => 'lio_horas_contrato' ), 
    array( 'db' => 'l.lio_vr_hora_maquina', 'dt' => 'ValorHoraMaquina', 'field' => 'lio_vr_hora_maquina' ), 
    array( 'db' => 'l.lio_vr_hora', 'dt' => 'ValorHora', 'field' => 'lio_vr_hora' ),
    array( 'db' => 'l.lio_horas_extras', 'dt' => 'Extras', 'field' => 'lio_horas_extras' ),
    array( 'db' => 'l.lio_horas_extras', 'dt' => 'Extras', 'field' => 'lio_horas_extras' )
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
$groupBy = ' l.lio_clave_int';
$with = '';
$joinQuery = "";

$joinQuery = " FROM tbl_liquidar_obras l join tbl_usuarios u on u.usu_clave_int  = l.usu_liquidador join tbl_obras o on o.obr_clave_int = l.obr_clave_int join tbl_liquidar le on le.lio_clave_int = l.lio_clave_int ";
$wh = "";
// if($est=="" || $est=="Todos")
// {

// }
// if($est=="PorAprobar")
// {
//   $wh.= " and l.liq_estado = 1";
// }
// else
// if($est=="PorFacturar")
// {
//   $wh.= " and l.liq_estado = 2";
// }
// else
// if($est=="Facturadas")
// {
//   $wh.= " and l.liq_estado = 3";
// }
// else
// if($est=="Rechazadas")
// {
//   $wh.= " and l.liq_estado = 4";
// }

$extraWhere =" (le.usu_clave_int  in(".$emp1.")  OR '".$emp."' IS NULL OR '".$emp."' = '') and (o.obr_clave_int = '".$obr."'  OR '".$obr."' IS NULL OR '".$obr."' = '')  ".$wh;
if($ini and $fin)
{
    $extraWhere.=" and l.lio_inicio = '".$ini."' and l.lio_fin = '".$fin."'";
}

echo json_encode(
	SSP::simple($_POST, $sql_details, $table, $primaryKey, $columns, $joinQuery, $extraWhere, $groupBy,$with )
);