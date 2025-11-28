<?php
include '../../data/db.config.php';
include '../../data/conexion.php';

session_start();// activa la variable de sesion
error_reporting(0);
$usuario   = $_COOKIE['usuarioPo'];
$idUsuario = $_COOKIE['idusuarioPo'];
$perfil    = $_SESSION["perfilPo"];
include '../../data/validarpermisos.php';
require_once "../../controladores/general.controller.php";

$emp = $_POST['emp'];
$emp = implode(', ', (array)$emp); if($emp==""){$emp1="''";}else {$emp1=$emp;}
$des = $_POST['des'];
$has = $_POST['has'];

$reg = new General();
$datr = $reg->fnReglas();
//reg_hor_mes hmes, reg_hor_semana hsemana, reg_lim_extras limex, reg_lim_extras_semana limexsemana
$hmes = $datr['hmes'];
$hsemana = $datr['hsemana'];
$limex = $datr['limex']; // LIMITE DIA
$limexsemana = $datr['limexsemana']; // LIMITE SEMANA 

$opc = $_POST['opc'];
$vagru = "";
if($opc==0) {  $vagru = "ld.lid_fecha"; } //semana
if($opc==1) {  $vagru = "date_format(ld.lid_fecha,'%Y%u')"; } //semana
if($opc==2) {  $vagru = "date_format(ld.lid_fecha,'%Y-%m')"; } //mes

$having = "HAVING SUM(ld.lid_hedo+ld.lid_hdf+ld.lid_hedf+ld.lid_heno+ld.lid_henf+ld.lid_rn+ld.lid_hnf+ld.lid_rd) > 0";
$table = 'tbl_usuarios';
$primaryKey = 'u.usu_clave_int';
$columns = array(
    array(
        'db' => 'u.usu_clave_int',
        'dt' => 'DT_RowId', 'field' => 'usu_clave_int',
        'formatter' => function( $d, $row ) {
            return 'rowc_'.$d;
        }
    ),  
    array( 'db' => "u.usu_clave_int", 'dt' => 'Clave', 'field' => 'usu_clave_int' ),      
    array( 'db' => "concat(u.usu_apellido,' ',u.usu_nombre )",'dt' => 'Nombre', 'as' => 'Nombre',      'field' => 'Nombre' ),   
    array( 'db' => "u.usu_documento", 'dt' => 'Cedula', 'as' => 'Cedula', 'field' => 'Cedula', 'formatter'=>function($d, $row){
        $doc =  str_replace(",","",$d);
        $doc =  str_replace(".","",$doc);
        return $doc;
    } ), 
    array( 'db' => "avg( l.liq_salario )",   'dt' => 'ValorHora',   'as' => 'ValorHora',   'field' => 'ValorHora' ),   
    array( 'db' => "(avg( l.liq_salario ) * avg( l.liq_hr_mes ) )", 'dt' => 'Basico', 'as' => 'Basico', 'field' => 'Basico' ),  
   
    array( 'db' => "sum(ld.lid_hedo)", 'dt' => 'hedo', 'as' => 'hedo', 'field' => 'hedo', 'formatter'=>function($d,$row){
        $are = $row[14];
        $tot  = ($are>0)? $d-$are: $d;
        return $tot;
    } ),
    array( 'db' => "sum(ld.lid_hdf)",      'dt' => 'hdf',  'as' => 'hdf', 'field' => 'hdf' ),
    array( 'db' => "sum(ld.lid_hedf)",     'dt' => 'hedf', 'as' => 'hedf', 'field' => 'hedf' ),
    array( 'db' => "sum(ld.lid_heno)",     'dt' => 'heno', 'as' => 'heno', 'field' => 'heno' ),
    array( 'db' => "sum(ld.lid_henf)",     'dt' => 'henf', 'as' => 'henf', 'field' => 'henf' ),
    array( 'db' => "sum(ld.lid_rn)",       'dt' => 'rn',   'as' => 'rn',   'field' => 'rn' ),
    array( 'db' => "sum(ld.lid_hnf)",      'dt' => 'hnf',  'as' => 'hnf',  'field' => 'hnf' ),
    array( 'db' => "sum(ld.lid_rd)",       'dt' => 'rd',   'as' => 'rd',   'field' => 'rd' ),
    array( 'db' => "sum(ld.lid_permisos)", 'dt' => 'are',  'as' => 'are',  'field' => 'are' ),//revisar calculo ar 
    array( 'db' => "sum(ld.lid_permisos)", 'dt' => 'totalhoras', 'as' => 'totalhoras', 'field' => 'totalhoras','formatter'=>function($d,$row){
        $are = $row[14];
        $hedo  = ($are>0)? $row[6]-$are: $row[6];
        $total = $hedo;
        $total+= $row[7];        
        $total+= $row[8];               
        $total+= $row[9];               
        $total+= $row[10];               
        $total+= $row[11];               
        $total+= $row[12];                       
        $total+= $row[13];                       
        // $total+= $row[14];
        return $total;
    } ),//revisar calculo are
    array( 'db' => "sum(ld.lid_permisos)", 'dt' => 'Color', 'as' => 'Color', 'field' => 'Color','formatter'=>function($d,$row){
        global $opc, $limex, $limexsemana, $hmes,$hsemana;
        $totalhoras = $row[15];
        $totallaboradas = $row[18];
        $are = $row[14];
        $hedo  = ($are>0)? $row[6]-$are: $row[6];
        $total = $hedo;
        $total+= $row[7];        
        $total+= $row[8];               
        $total+= $row[9];               
        $total+= $row[10];               
        $total+= $row[11];               
        $total+= $row[12];                       
        $total+= $row[13];                          
        // $total+= $row[24];
        $color = '';
        if($opc==0 and floatval($total)>floatval($limex))
        {
            $color = 'bg-danger bg-opacity-25';
        }else if($opc==1 and floatval($total)>floatval($limexsemana)){
            $color = 'bg-danger bg-opacity-50';
        }
        if($opc==2 and floatval($totallaboradas)>floatval($hmes)){
            $color = 'bg-danger';
        }
        else if($opc==1 and floatval($totalhoras)>floatval($hsemana))
        {
            $color  = 'bg-danger bg-opacity-75';
        }
        return $color;
    } ),
    array( 'db' => "".$vagru."", 'dt' => 'Agrupacion', 'as' => 'Agrupacion', 'field' => 'Agrupacion'),
    array( 'db' => "sum(ld.lid_horas)", 'dt' => 'HorasLaboradas', 'as' => 'HorasLaboradas', 'field' => 'HorasLaboradas' ),//revisar calculo ar   
    array( 'db' => "".$vagru."", 'dt' => 'Rango', 'as' => 'Rango', 'field' => 'Rango','formatter'=>function($d,$row){
        global $conn, $opc;

        if ($opc == 1) {
            $sql = "SELECT 
                        STR_TO_DATE(:d1, '%x%v %W') AS LUN, 
                        STR_TO_DATE(:d2, '%x%v %W') AS DOM";
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':d1', $d . ' Monday');
            $stmt->bindValue(':d2', $d . ' Sunday');
        } else {
            $sql = "SELECT  
                        DATE_ADD(DATE_ADD(LAST_DAY(:fecha), INTERVAL 1 DAY), INTERVAL -1 MONTH) AS LUN, 
                        LAST_DAY(:fecha) AS DOM";
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':fecha', $d . '-01');
        }
        
        $stmt->execute();
        $datld = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $sLunes   = $datld['LUN'];
        $sDomingo = $datld['DOM'];
        
        return $sLunes . " a " . $sDomingo;
        
    } ),//revisar calculo ar   
);
$sql_details = array(
	'user' => 'usrpavashtg',
	'pass' => '9A12)WHFy$2p4v4s',
	'db'   => 'bdpotenco',
	'host' => 'localhost'
);

require( '../../data/ssp.class.php' );

$extraWhere=" (u.usu_clave_int  in(".$emp1.")  OR '".$emp."' IS NULL OR '".$emp."' = '') and u.est_clave_int in(1) ";
if($des and $has)
{
    $extraWhere .= " AND (ld.lid_fecha BETWEEN '".$des."' AND '".$has."')";
}	
$groupBy = ' Nombre, Cedula, Agrupacion';
$with = '';
$joinQuery = "FROM tbl_liquidar l JOIN tbl_liquidar_dias ld ON ld.liq_clave_int = l.liq_clave_int JOIN tbl_usuarios u ON u.usu_clave_int = l.usu_clave_int ";
echo json_encode(SSP::simple($_POST, $sql_details, $table, $primaryKey, $columns, $joinQuery, $extraWhere, $groupBy,$with, $having));