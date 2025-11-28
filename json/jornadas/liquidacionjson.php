<?php
include '../../data/db.config.php';
include '../../data/conexion.php';
session_start();// activa la variable de sesion
error_reporting(0);
$usuario   = $_COOKIE['usuarioPo'];
$idUsuario = $_COOKIE['idusuarioPo'];
$perfil    = $_SESSION["perfilPo"];
include '../../data/validarpermisos.php';
$table = 'tbl_liquidar';
// Table's primary key
$primaryKey = 'l.liq_clave_int';
$ano = $_POST['ano'];
$emp = $_POST['emp'];
$emp = implode(', ', (array)$emp); if($emp==""){$emp1="''";}else {$emp1=$emp;}
$obr = $_POST['obr'];
$obr = implode(', ', (array)$obr); if($obr==""){$obr1="''";}else {$obr1=$obr;}
$est = $_POST['est'];

$p55 = isset($permisosUsuario[55]) ?? 0;
$p121 = isset($permisosUsuario[121]) ?? 0;
$p122 = isset($permisosUsuario[122]) ?? 0;
// parameter names
$columns = array(
	array(
		'db' => 'l.liq_clave_int',
		'dt' => 'DT_RowId', 'field' => 'liq_clave_int',
		'formatter' => function( $d, $row ) {
			// Technically a DOM id cannot start with an integer, so we prefix
			// a string. This can also be useful if you have multiple tables
			// to ensure that the id is unique with a different prefix
			return 'rowl_'.$d;
		}
	),	
        
    array( 'db' => 'l.liq_clave_int', 'dt' => 'Editar', 'field' => 'liq_clave_int','formatter'=>function($d,$row){
        $est = $row[9];
        $cre = $row[12];
        global $idUsuario;
        $d =  encrypt($d,'p4v4sAp');
        if(($est==1 || $est==3) and $idUsuario==$cre){
            return "<a class='btn  btn-warning btn-sm rounded-circle' href='#/Liquidar/EditSettle?ID=".$d."' title='Editar Liquidación'><i class='fas fa-edit'></i></a>";    
        }else{
            return "";
        }    
    }),
    array( 'db' => 'l.liq_clave_int', 'dt' => 'Eliminar', 'field' => 'liq_clave_int','formatter'=>function($d,$row){
        $est = $row[9];
        global $idUsuario;
        $cre = $row[12];
        if(($est==0 and $idUsuario==$cre)){
            return "<a class='btn   btn-danger btn-sm rounded-circle' onClick=CRUDLIQUIDAR('ELIMINAR','".$d."') title='Eliminar Liquidación' style='heigth:22px; width:22px' ><i class='fa fa-trash'></i></a>";
        }
    }),			
	array( 'db' => 'l.liq_ano', 'dt' => 'Ano', 'field' => 'liq_ano'),
	array( 'db' => 'l.liq_semana', 'dt' => 'Semana', 'field' => 'liq_semana' ),
    array( 'db' => 'u.usu_documento', 'dt' => 'Nit', 'field' => 'usu_documento' ),
    array( 'db' => "CONCAT(u.usu_nombre,' ',u.usu_apellido)", 'dt' => 'Empleado', 'as'=>'Empleado', 'field' => 'Empleado'),
    array( 'db' => 'l.liq_fecha', 'dt' => 'FechaRegistro', 'field' => 'liq_fecha'),    
    array( 'db' => 'l.liq_notas', 'dt' => 'Nota', 'field' => 'liq_notas'),
 	array( 'db' => 'l.liq_estado', 'dt' => 'Estado', 'field' => 'liq_estado','formatter'=>function($d,$row){
       
        if($d==1){ $est = "Por Aprobar"; }else if($d==2 ){ $est = "Por Facturar"; }else if($d==3){ $est =  "Facturada"; }
        else if($d==4){ $est =  "Rechazada"; }
        
         return $est;
    }),
 	array( 'db' => 'l.liq_clave_int', 'dt' => 'Clave', 'field' => 'liq_clave_int'),
    array( 'db' => 'l.liq_clave_int', 'dt' => 'Aprobar', 'field' => 'liq_clave_int','formatter'=>function($d,$row){

        $est = $row[9];
        global $p55,$idUsuario;
        $cre = $row[12];
        $d =  encrypt($d,'p4v4sAp');
        if( $p55>0 and ($est==1))
        {
             return "<a class='btn  btn-success rounded-circle btn-sm' href='#/Liquidar/ApprovedSettle?ID=".$d."'  title='Aprobar Liquidación'><i class='fas fa-check-double text-white'></i></a>";
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
    array( 'db' => 'l.liq_creacion', 'dt' => 'Creacion', 'field' => 'liq_creacion'),   
    array( 'db' => "CONCAT(u2.usu_nombre,' ',u2.usu_apellido)", 'dt' => 'CreadaPor', 'as' => 'CreadaPor', 'field' => 'CreadaPor'),
    array( 'db' => 'l.liq_total', 'dt' => 'Total', 'field' => 'liq_total' ),
    array( 'db' => 'l.liq_clave_int', 'dt' => 'Previa', 'field' => 'liq_clave_int','formatter'=>function($d,$row){

        $est = $row[9];
        global $p55,$idUsuario;
        $cre = $row[12];
        $d =  encrypt($d,'p4v4sAp');
       
        return "<a class='btn  btn-info rounded-circle btn-sm' title='
            Previa Liquidación' onClick=CRUDLIQUIDAR('PREVIAPROFORMA','".$d."') data-toggle='modal' data-target='#mymodal'><i class='fas fa-eye text-white'></i></a>";
    }),
    array( 'db' => 'l.liq_inicio', 'dt' => 'Desde', 'field' => 'liq_inicio' ),
    array( 'db' => 'l.liq_fin', 'dt' => 'Hasta', 'field' => 'liq_fin' ),
    array( 'db' => 'l.liq_tipo', 'dt' => 'Tipo', 'field' => 'liq_tipo', 'formatter'=>function($d, $row){
        $tip  = $d==1? "Nomina": "Obra";
        return  $tip;
    } ),
    array( 'db' => 'o.obr_nombre', 'dt' => 'Obra', 'field' => 'obr_nombre' ),
    array( 'db' => 'o.obr_cencos', 'dt' => 'Cenco', 'field' => 'obr_cencos' ),
    array( 'db' => 'l.liq_codigo', 'dt' => 'Codigo', 'field' => 'liq_codigo' ),
      
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
$groupBy = ' l.liq_clave_int';
$with = '';
$joinQuery = "";

$joinQuery = " FROM tbl_liquidar l 
join tbl_usuarios u on u.usu_clave_int  = l.usu_clave_int 
join tbl_usuarios u2 on u2.usu_clave_int  = l.liq_creacion 
left outer join tbl_obras o on o.obr_clave_int = l.obr_clave_int ";
 
$wh = "";
if($est=="" || $est=="Todos")
{

}
if($est=="PorAprobar")
{
  $wh.= " and l.liq_estado = 1";
}
else
if($est=="PorFacturar")
{
  $wh.= " and l.liq_estado = 2";
}
else
if($est=="Facturadas")
{
  $wh.= " and l.liq_estado = 3";
}
else
if($est=="Rechazadas")
{
  $wh.= " and l.liq_estado = 4";
}
$extraWhere =" (date_format(l.liq_fecha,'%Y')  = '".$ano."' OR '".$ano."' IS NULL OR '".$ano."' = '') and (l.usu_clave_int  in(".$emp1.")  OR '".$emp."' IS NULL OR '".$emp."' = '') and (o.obr_clave_int in(".$obr1.")  OR '".$obr."' IS NULL OR '".$obr."' = '') ".$wh;
if($p121>0 || $p122>0)
{
    $extraWhere.=" and (";    
    $extraWhere.=($p121>0)?"l.liq_tipo = 1 ":" ";
    $extraWhere.=($p121>0 and $p122>0)?" OR":" ";
    $extraWhere.=($p122>0)?" l.liq_tipo = 2)":")";    
}
else
{
    $extraWhere.= "and l.liq_tipo  not in(1,2)"; 
}

echo json_encode(
	SSP::simple($_POST, $sql_details, $table, $primaryKey, $columns, $joinQuery, $extraWhere, $groupBy,$with )
);