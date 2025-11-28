<?php
session_start();
include '../../../data/db.config.php';
include "../../../data/conexion.php";
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
ini_set('memory_limit','500M');
ini_set('max_execution_time', 600);
ini_set('upload_max_filesize', '300M');
ini_set('safe_mode', 0);

/*include '../../../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Helper\Sample;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;*/

$usuario   = $_COOKIE['usuarioPo'];
$idUsuario = $_COOKIE['idusuarioPo'];
$perfil    = $_SESSION["perfilPo"];
$IP = $_SERVER['REMOTE_ADDR'];
//include '../../data/validarpermisos.php';
setlocale(LC_TIME, 'es_CO.UTF-8'); 
date_default_timezone_set('America/Bogota');
$fecha  = date("Y/m/d H:i:s");
include ("../../../data/validarpermisos.php");

$p94 = isset($permisosUsuario[94]) ?? 0;
$p95 = isset($permisosUsuario[95]) ?? 0;
$p96 = isset($permisosUsuario[96]) ?? 0;
$p97 = isset($permisosUsuario[97]) ?? 0;
$p98 = isset($permisosUsuario[98]) ?? 0;
$p99 = isset($permisosUsuario[99]) ?? 0;
$p113 = isset($permisosUsuario[113]) ?? 0;
$p66 = isset($permisosUsuario[66]) ?? 0;

$tip = $_GET['tip'];
$agru = $_GET['agru'];

$conmin = "SELECT 
MIN(ped_fec_programada) AS minf, 
MAX(ped_fec_programada) AS maxf 
FROM tbl_pedidos";

$stmt = $conn->prepare($conmin);
$stmt->execute();

$datmin = $stmt->fetch(PDO::FETCH_ASSOC);

$minfec = $datmin['minf'];
$maxfec = $datmin['maxf'];


$cliente = $_GET['cliente']; $cliente = implode(', ', (array)$cliente); 
if($cliente==""){$cliente1="'0'";}else {$cliente1=$cliente;}

$vendedor = $_GET['vendedor']; $vendedor = implode(', ', (array)$vendedor); 
if($vendedor==""){$vendedor1="'0'";}else {$vendedor1=$vendedor;}

$estado = $_GET['estado']; $estado = implode(', ', (array)$estado); 
if($estado=="" || $estado==NULL){$estado1="'0'";}else {$estado1=$estado;}

$mercado = $_GET['mercado']; $mercado = implode(', ', (array)$mercado); 
if($mercado=="" || $mercado==NULL){$mercado1="'0'";}else {$mercado1=$mercado;}

$marca = $_GET['marca']; $marca = implode(', ', (array)$marca); 
if($marca=="" || $marca==NULL){$marca1="'0'";}else {$marca1=$marca;}

$distrito = $_GET['distrito']; $distrito = implode(', ', (array)$distrito); 
if($distrito==""){$distrito1="'0'";}else {$distrito1=$distrito;}

$ano = $_GET['ano']; $ano = "'".implode("','", (array)$ano)."'"; 
if($ano==""){$ano1="''";}else {$ano1=$ano; $ano = implode(",", (array)$_GET['ano']); }

$desde = $_GET['desde'];
$hasta = $_GET['hasta'];
$desdeentrega = $_GET['desdeentrega'];
$hastaentrega = $_GET['hastaentrega'];
$pedido  =$_GET['pedido'];
$pro = $_GET['producto']; $pro = implode(', ', (array)$pro); 
if($pro==""){$pro1="'0'";}else {$pro1=$pro;}
if($hasta=="0000-00-00"){ $hasta = "";}
if($desde=="0000-00-00"){ $desde = "";}
if($desde!="" and $hasta=="" ){$hasta=$desde;}else if($hasta!="" and $desde==""){$desde=$hasta;}
if($desde==""){ $desde=  $minfec;}
if($hasta==""){ $hasta=  $maxfec;}

if($desdeentrega!="" and $hastaentrega==""){$hastaentrega=$desdeentrega;}else if($hastaentrega!="" and $desdeentrega==""){$desdeentrega=$hastaentrega;}
//if($desdeentrega==""){ $desdeentrega =  $minfec;}
//if($hastaentrega==""){ $hastaentrega=  $maxfec;}
define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');
date_default_timezone_set("America/Bogota");
setlocale (LC_TIME,"spanish", "es_ES@euro", "es_ES", "es");

function convert_htmlentities($data)
{
    //$result = str_replace(
    //array("&aacute;","&eacute;","&iacute;","&oacute;","&uacute;","&ntilde;",
    //"&Aacute;","&Eacute;","&Iacute;","&Oacute;","&Uacute;","&Ntilde;"),array("á","é","í","ó","ú","ñ","Á","É","Í","Ó","Ú","Ñ") ,$data);
    $result = str_replace("á",htmlentities("á"),$data);
    $result = str_replace("é",htmlentities("é"),$result);
    $result = str_replace("í",htmlentities("í"),$result);
    $result = str_replace("ó",htmlentities("ó"),$result);
    $result = str_replace("ú",htmlentities("ú"),$result);
    $result = str_replace("Á",htmlentities("Á"),$result);
    $result = str_replace("É",htmlentities("É"),$result);
    $result = str_replace("Í",htmlentities("Í"),$result);
    $result = str_replace("Ó",htmlentities("Ó"),$result);
    $result = str_replace("Ú",htmlentities("Ú"),$result);
    $result = str_replace("ñ",htmlentities("ñ"),$result);
    $result = str_replace("Ñ",htmlentities("Ñ"),$result);
    $result = html_entity_decode($result, ENT_QUOTES, "ISO-8859-1");
    return $result;
}

/** Include PHPExcel */
require_once '../../../clases/PHPExcel.php';
date_default_timezone_set('UTC');
$cacheMethod = PHPExcel_CachedObjectStorageFactory::cache_to_sqlite;
if (PHPExcel_Settings::setCacheStorageMethod($cacheMethod)) {
   // echo date('H:i:s') , " Habilitar el almacenamiento en caché de la celda utilizando " , $cacheMethod , " metodo" , EOL;
} else {
    //echo date('H:i:s') , " No se puede establecer el almacenamiento en caché de la celda utilizando " , $cacheMethod , " método, volviendo a la memoria" , EOL;
}
/*$helper = new Sample();
if ($helper->isCli()) {
    $helper->log('This example should only be run from a Web Browser' . PHP_EOL);

    return;
}*/
/*
function cellColor($cells,$color){
    global $spreadsheet;
    $spreadsheet->getActiveSheet()->getStyle($cells)
    ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
    $spreadsheet->getActiveSheet()->getStyle($cells)
    ->getFill()->getStartColor()->setARGB($color);
}*/
PHPExcel_Cell::setValueBinder( new PHPExcel_Cell_AdvancedValueBinder() );			

function cellColor($cells,$color){
    global $objPHPExcel;

    $objPHPExcel->getActiveSheet()->getStyle($cells)->getFill()->applyFromArray(array(
        'type' => PHPExcel_Style_Fill::FILL_SOLID,
        'startcolor' => array(
             'rgb' => $color
        )
    ));
}

// Create new PHPExcel object
//echo date('H:i:s') , " Crear nuevo objeto PHPExcel" , EOL;
//$spreadsheet = new Spreadsheet();
$objPHPExcel = new PHPExcel();
// Set document properties
//echo date('H:i:s') , " Establecer propiedades" , EOL;
$objPHPExcel->getProperties()->setCreator("Pavas.co")
    ->setLastModifiedBy("Pavas.co")
    ->setTitle("Informe Ordenes")
    ->setSubject("Informe Ordenes")
    ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
    ->setKeywords("office 2007 openxml php")
    ->setCategory("Informes");

$objPHPExcel->setActiveSheetIndex(0);

$li = "B";
$lf = "O";
if($agru==1 || $agru=="" || $agru==NULL){ $tit = "FEC CREACION"; }
if($agru==2){ $tit = "FEC PROGRAMADA"; }
if($agru==3){ $tit = "FEC ENTREGA"; }
if($agru==4){ $tit = "MERCADO"; }
if($agru==5){ $tit = "CLIENTE"; }
if($agru==6){ $tit = "DISTRITO"; }
if($agru==7){ $tit = "VENDEDOR"; }
if($agru==8){ $tit = "MARCA"; }
if($agru==9){ $tit = "SEMANA"; }
if($agru==10){ $tit = "MES"; }
if($agru==11){ $tit = "ANO"; }
if($agru==12){ $tit = "PRODUCTO"; }

if($agru==1  || $agru=="" || $agru==NULL) {  $vagru = "p.ped_fecha"; } 
if($agru==2) {  $vagru = "p.ped_fec_programada"; } 
if($agru==3) {  $vagru = "p.ped_fec_entrega"; } 
if($agru==4) {  $vagru = "m.mer_nombre"; } 
if($agru==5) {  $vagru = "concat(u.usu_nombre,' ',u.usu_apellido)"; } 
if($agru==6) {  $vagru = "di.dis_nombre"; }  
if($agru==7) {  $vagru = "p.usu_vendedor"; } 
if($agru==8) {  $vagru = "ma.mar_nombre"; } 
if($agru==9) {  $vagru = "date_format(p.ped_fecha,'%y%u')"; } //semana
if($agru==10) {  $vagru = "date_format(p.ped_fecha,'%Y-%M')"; } //mes
if($agru==11) {  $vagru = "date_format(p.ped_fecha,'%Y')"; } //ano

$wh = "";
if($p99>0)
{
    if($estado=="PorAprobar" and $p94>0)
    {
        $wh.=" and p.ped_estado = '1'";
    }
    else if($estado=="PorDespachar" and $p95>0)
    {
        $wh.=" and p.ped_estado = '2'";
    }
    else if($estado=="PorFacturar" and $p113>0)
    {
        $wh.= " and p.ped_estado = '3' and (p.ped_num_factura IS NULL or p.ped_num_factura = '')";
    }
    else if($estado=="PorEntregar" and $p96>0)
    {
        $wh.=" and p.ped_estado = '3' and p.ped_num_factura !='' ";
    }
    else if($estado=="Entregadas" and $p97>0)
    {
        $wh.=" and p.ped_estado = '4'";
    }
    else if($estado=="Canceladas" and $p98>0)
    {
        $wh.=" and p.ped_estado = '5'";
    }
    else 
    {
        $wh.=" and p.ped_estado not in(0,5)";
    }
}
else if($estado=="PorAprobar" and $p94>0)
{
    $wh.=" and p.ped_estado = '1' and p.usu_vendedor = '".$idUsuario."'";
}
else if($estado=="PorDespachar" and $p95>0)
{
    $wh.=" and p.ped_estado = '2' and p.usu_vendedor = '".$idUsuario."'";
}
else if($estado=="PorFacturar" and $p113>0)
{
     $wh.= " and p.ped_estado = '3' and (p.ped_num_factura IS NULL or p.ped_num_factura = '')";
}
else if($estado=="PorEntregar" and $p113>0)
{
    $wh.="  and p.ped_estado = '3' and p.ped_num_factura !='' ";
}
else if($estado=="Entregadas" and $p113>0)
{
    $wh.="  and p.ped_estado = '4' and p.ped_num_factura !='' ";
}
else if($estado=="PorEntregar" and $p96>0)
{
    
    if($p66>0)
    {
        $wh.="  and p.ped_estado = '3'  and p.ped_num_factura !='' ";  
    }
    else
    {
        $wh.="  and p.ped_estado = '3' and (p.usu_vendedor = '".$idUsuario."' or p.usu_entrega = '".$idUsuario."') and p.ped_num_factura !='' ";
    }
}
else if($estado=="Entregadas" and $p97>0)
{
    $wh.="  and p.ped_estado = '4' and (p.usu_vendedor = '".$idUsuario."' or p.usu_entrega = '".$idUsuario."')";   
}
else if($estado=="Canceladas")// and $p98>0)
{
    $wh.="  and p.ped_estado = '5' and (p.usu_vendedor = '".$idUsuario."' or p.usu_entrega = '".$idUsuario."')";
}
else 
{
    $wh.=" and p.ped_estado not in(0,5) and (p.usu_vendedor = '".$idUsuario."' or p.usu_entrega = '".$idUsuario."')";
}
$join1="";

if($tip==1 and $agru!=12)
{
    $li = "B";
    $lf = "M";
    $nomarc = "INFORMEVENTAS";
    $objPHPExcel->getActiveSheet()
    ->setCellValue('B1', "INFORME DE ORDENES")
    ->setCellValue('B2', "N° ORDEN")
    ->setCellValue('C2', "FEC.PEDIDO")
    ->setCellValue('D2', "FEC.PROGRAMADA")
    ->setCellValue('E2', "HOR.PROGRAMADA")
    ->setCellValue('F2', "MERCADO")
    ->setCellValue('G2', "CLIENTE")
    ->setCellValue('H2', "VENDEDOR")
    ->setCellValue('I2', "DISTRITO")
    ->setCellValue('J2', "DIRECCION")
    ->setCellValue('K2', "TELEFONO")
    ->setCellValue('L2', "TOTAL CAJAS")
    ->setCellValue('M2', "ESTADO")
    //->setCellValue('L2', "SUBTOTAL")
    //->setCellValue('M2', "DESCUENTO")
    //->setCellValue('N2', "TOTAL")
    //->setCellValue('O2', "ESTADO")
    ->mergeCells('B1:M1');   

    $sql = "SELECT p.ped_clave_int Orden, p.ped_codigo Codigo, p.ped_fecha Fecha, p.ped_total TotalPedido, p.ped_iva IvaPedido, p.ped_estado Estado, concat(u.usu_nombre,' ',u.usu_apellido) AS Cliente, p.usu_vendedor vendedor, p.ped_direccion Direccion, p.ped_fec_programada FechaProgramado, p.ped_hor_programada HoraProgramada, p.ped_retefuente Retefuente, p.ped_num_factura Factura, u.usu_celular Telefono, p.ped_descuento Descuento, sum((pde_cantidad*pde_valor) + ((pde_cantidad*pde_valor)*(pde_iva/100)) - ((pde_cantidad*pde_valor)*(pde_descuento/100))) AS Total, m.mer_nombre Mercado, di.dis_nombre Distrito, p.ped_fec_entrega FechaEntrega,sum(pde_cantidad) CantidadCajas"; 

    $groupBy = 'p.ped_clave_int,u.usu_nombre,u.usu_apellido,m.mer_nombre,di.dis_nombre';

    $joinQuery = " FROM tbl_pedidos AS p JOIN tbl_usuarios u ON u.usu_clave_int = p.usu_clave_int  join tbl_pedidos_detalle d on d.ped_clave_int = p.ped_clave_int join tbl_productos pr on pr.pro_clave_int = d.pro_clave_int join tbl_mercados m on m.mer_clave_int = d.mer_clave_int JOIN tbl_distritos di on di.dis_clave_int = p.dis_clave_int  JOIN tbl_marcas ma ON ma.mar_clave_int = pr.mar_clave_int";

    $extraWhere =" (  u.usu_clave_int IN(".$cliente1.") OR '".$cliente."' IS NULL OR '".$cliente."' = '') and (p.ped_codigo LIKE REPLACE('%".$pedido."%',' ','%') OR '".$pedido."' IS NULL OR '".$pedido."' = '' ) and  ((p.ped_fec_programada BETWEEN '".$desde."' AND '".$hasta."') or ('".$desde."' Is Null and '".$hasta."' Is Null) or ('".$desde."' = '' and '".$hasta."' = ''))  and  (  pr.pro_clave_int IN(".$pro1.") OR '".$pro."' IS NULL OR '".$pro."' = '' ) and ( m.mer_clave_int IN(".$mercado1.") OR '".$mercado."' IS NULL OR '".$mercado."' = '') and ( di.dis_clave_int IN(".$distrito1.") OR '".$distrito."' IS NULL OR '".$distrito."' = '') and (date_format(ped_fecha,'%Y') IN (".$ano1.")  OR '".$ano."' IS NULL OR '".$ano."' = '' )  and ( p.usu_vendedor IN(".$vendedor1.") OR '".$vendedor."' IS NULL OR '".$vendedor."' = '') and  ((p.ped_fecha BETWEEN '".$desdeentrega." 00:00:00' AND '".$hastaentrega." 23:59:59') or ('".$desdeentrega."' Is Null and '".$hastaentrega."' Is Null) or ('".$desdeentrega."' = '' and '".$hastaentrega."' = '')) and di.dis_clave_int in (select dis_clave_int from tbl_usuarios_distritos where usu_clave_int = '".$idUsuario."') ".$wh;
    $orderBy = $vagru;

}
else if($tip==1 and $agru==12)
{
    $li = "B";
    $lf = "E";

    $nomarc = "INFORMEVENTASPRODUCTO";
    $objPHPExcel->getActiveSheet()
    ->setCellValue('B1', "INFORME DE VENTAS PRODUCTO")
    ->setCellValue('B2', "MARCA")
    ->setCellValue('C2', "CODIGO")
    ->setCellValue('D2', "PRODUCTO")
    ->setCellValue('E2', "TOTAL CAJAS")
    //->setCellValue('F2', "UNIDAD")
    //->setCellValue('G2', "TOTAL")
    ->mergeCells('B1:E1'); 
    $sql = "SELECT pr.pro_clave_int, ma.mar_nombre marca, pr.pro_clave_int idproducto, pr.pro_nombre producto, pr.pro_codigo codigo, pr.pro_unidad unidad, SUM(d.pde_cantidad) AS CantidadCajas, sum((pde_cantidad*pde_valor) + ((pde_cantidad*pde_valor)*(pde_iva/100)) - ((pde_cantidad*pde_valor)*(pde_descuento/100))) AS total, SUM(d.pde_cantidad) AS cantidad2 ";   
    $joinQuery = " FROM tbl_pedidos AS p JOIN tbl_usuarios u ON u.usu_clave_int = p.usu_clave_int  join tbl_pedidos_detalle d on d.ped_clave_int = p.ped_clave_int join tbl_productos pr on pr.pro_clave_int = d.pro_clave_int join tbl_mercados m on m.mer_clave_int = d.mer_clave_int JOIN tbl_distritos di on di.dis_clave_int = p.dis_clave_int JOIN tbl_marcas ma ON ma.mar_clave_int = pr.mar_clave_int ";

    $extraWhere =" (  u.usu_clave_int IN(".$cliente1.") OR '".$cliente."' IS NULL OR '".$cliente."' = '') and (p.ped_codigo LIKE REPLACE('%".$pedido."%',' ','%') OR '".$pedido."' IS NULL OR '".$pedido."' = '' ) and  ((p.ped_fec_programada BETWEEN '".$desde."' AND '".$hasta."') or ('".$desde."' Is Null and '".$hasta."' Is Null) or ('".$desde."' = '' and '".$hasta."' = ''))  and  (  pr.pro_clave_int IN(".$pro1.") OR '".$pro."' IS NULL OR '".$pro."' = '' ) and ( m.mer_clave_int IN(".$mercado1.") OR '".$mercado."' IS NULL OR '".$mercado."' = '') and ( ma.mar_clave_int IN(".$marca1.") OR '".$marca."' IS NULL OR '".$marca."' = '') and ( di.dis_clave_int IN(".$distrito1.") OR '".$distrito."' IS NULL OR '".$distrito."' = '') and (date_format(ped_fecha,'%Y') IN (".$ano1.")  OR '".$ano."' IS NULL OR '".$ano."' = '' )  and ( p.usu_vendedor IN(".$vendedor1.") OR '".$vendedor."' IS NULL OR '".$vendedor."' = '') and  ((p.ped_fecha BETWEEN '".$desdeentrega." 00:00:00' AND '".$hastaentrega." 23:59:59') or ('".$desdeentrega."' Is Null and '".$hastaentrega."' Is Null) or ('".$desdeentrega."' = '' and '".$hastaentrega."' = '')) and di.dis_clave_int in (select dis_clave_int from tbl_usuarios_distritos where usu_clave_int = '".$idUsuario."') ".$wh;

    $groupBy = 'ma.mar_nombre,pr.pro_clave_int,pr.pro_nombre,pr.pro_codigo,pr.pro_unidad';
    $orderBy = 'marca,producto';  
}
else if($tip==2 and $agru!=12)
{
    $li = "B";
    $lf = "C";
    //$vagru = "p.ped_fecha";
    $nomarc = "CONSOLIDADOVENTAS".$tit;

    $objPHPExcel->getActiveSheet()
    ->setCellValue('B1', "INFORME CONSOLIDADO VENTAS POR ".$tit)
    ->setCellValue('B2', $tit)
    ->setCellValue('C2', "TOTAL CAJAS")
    //->setCellValue('D2', "TOTAL")
    //->setCellValue('E2', "PROMEDIO")
    ->mergeCells('B1:C1'); 

    $sql="SELECT  ".$vagru." AS agrupacion, sum((pde_cantidad*pde_valor) + ((pde_cantidad*pde_valor)*(pde_iva/100)) - ((pde_cantidad*pde_valor)*(pde_descuento/100))) AS total, COUNT(p.ped_clave_int) AS cantidad, AVG((pde_cantidad*pde_valor) + ((pde_cantidad*pde_valor)*(pde_iva/100)) - ((pde_cantidad*pde_valor)*(pde_descuento/100))) AS promedio,sum(d.pde_cantidad) CantidadCajas ";
    $joinQuery = "FROM tbl_pedidos AS p JOIN tbl_usuarios u ON u.usu_clave_int = p.usu_clave_int  join tbl_pedidos_detalle d on d.ped_clave_int = p.ped_clave_int join tbl_productos pr on pr.pro_clave_int = d.pro_clave_int join tbl_mercados m on m.mer_clave_int = d.mer_clave_int JOIN tbl_distritos di on di.dis_clave_int = p.dis_clave_int JOIN tbl_marcas ma ON ma.mar_clave_int = pr.mar_clave_int ";

    $extraWhere =" ( u.usu_clave_int IN(".$cliente1.") OR '".$cliente."' IS NULL OR '".$cliente."' = '') and (p.ped_codigo LIKE REPLACE('%".$pedido."%',' ','%') OR '".$pedido."' IS NULL OR '".$pedido."' = '' ) and  ((p.ped_fec_programada BETWEEN '".$desde."' AND '".$hasta."') or ('".$desde."' Is Null and '".$hasta."' Is Null) or ('".$desde."' = '' and '".$hasta."' = ''))  and  (  pr.pro_clave_int IN(".$pro1.") OR '".$pro."' IS NULL OR '".$pro."' = '' ) and ( m.mer_clave_int IN(".$mercado1.") OR '".$mercado."' IS NULL OR '".$mercado."' = '') and ( ma.mar_clave_int IN(".$marca1.") OR '".$marca."' IS NULL OR '".$marca."' = '') and ( di.dis_clave_int IN(".$distrito1.") OR '".$distrito."' IS NULL OR '".$distrito."' = '') and (date_format(ped_fecha,'%Y') IN (".$ano1.")  OR '".$ano."' IS NULL OR '".$ano."' = '' )  and ( p.usu_vendedor IN(".$vendedor1.") OR '".$vendedor."' IS NULL OR '".$vendedor."' = '') and  ((p.ped_fecha BETWEEN '".$desdeentrega." 00:00:00' AND '".$hastaentrega." 23:59:59') or ('".$desdeentrega."' Is Null and '".$hastaentrega."' Is Null) or ('".$desdeentrega."' = '' and '".$hastaentrega."' = '')) and  di.dis_clave_int in (select dis_clave_int from tbl_usuarios_distritos where usu_clave_int = '".$idUsuario."') ".$wh;
    $groupBy = 'agrupacion';
    $orderBy = "agrupacion";
}
else if($tip=="3")
{
    if($agru=="" || $agru==NULL){ $tit = "PRODUCTO"; }
    $li = "B";
    $lf = "N";
    $vagru = "p.ped_fecha";

    $qcant = "COUNT(p.ped_clave_int)";
    //$qtotal = "sum((pde_cantidad*pde_valor) + ((pde_cantidad*pde_valor)*(pde_iva/100)) - ((pde_cantidad*pde_valor)*(pde_descuento/100)))";
    $qtotal = "SUM(pde_cantidad)";
    $qavg = "''";
    $nomarc = "VENTAS".$tit."-SEMANA";
    //if(($agru==1  || $agru=="" || $agru==NULL)) {  $vagru = "p.ped_fecha"; } 
    if($agru==2) {  $vagru = "p.ped_fec_programada"; } 
    if($agru==3) {  $vagru = "p.ped_fec_entrega"; } 
    if($agru==4) {  $vagru = "m.mer_nombre"; } 
    if($agru==5) {  $vagru = "concat(u.usu_nombre,' ',u.usu_apellido)"; } 
    if($agru==6) {  $vagru = "di.dis_nombre"; }  
    if($agru==7) {  $vagru = "p.usu_vendedor"; } 
    if($agru==8) {  $vagru = "ma.mar_nombre"; } 
    if($agru==9) {  $vagru = "date_format(p.ped_fecha,'%y%u')"; } //semana
    if($agru==10){  $vagru = "date_format(p.ped_fecha,'%Y-%M')"; } //mes
    if($agru==11){  $vagru = "date_format(p.ped_fecha,'%Y')"; } //ano
    if($agru==12  || $agru=="" || $agru==NULL){  
        $vagru = 'pr.pro_nombre';
        $qcant = "COUNT(p.ped_clave_int)";
        $qtotal = "SUM(pde_cantidad)";
        //$qtotal = "sum((pde_cantidad*pde_valor) + ((pde_cantidad*pde_valor)*(pde_iva/100)) - ((pde_cantidad*pde_valor)*(pde_descuento/100)))";
        $qavg = "''"; 
    }
  
    $objPHPExcel->getActiveSheet()
    ->setCellValue('B1', "INFORME DE VENTAS SEMANA")
    ->setCellValue('B2', "AÑO")
    ->setCellValue('C2', "SEMANA")
    ->setCellValue('D2', "FEC.INICIO")
    ->setCellValue('E2', "FEC.FINAL")
    ->setCellValue('F2', $tit)
    ->setCellValue('G2', "LUNES")
    ->setCellValue('H2', "MARTES")
    ->setCellValue('I2', "MIERCOLES")
    ->setCellValue('J2', "JUEVES")
    ->setCellValue('K2', "VIERNES")
    ->setCellValue('L2', "SABADO")
    ->setCellValue('M2', "DOMINGO")
    ->setCellValue('N2', "TOTAL CAJAS")
    ->mergeCells('B1:N1');    

    $sql = "SELECT  date_format(p.ped_fecha,'%Y') AS ano, date_format(p.ped_fecha,'%y%u') AS semana, ".$vagru." AS agrupacion, '' AS lunes, '' AS martes, '' AS miercoles, '' AS jueves, '' AS viernes, '' AS sabado, '' AS domingo, ".$qtotal." AS CantidadCajas, ".$qcant." AS cantidad, ".$qavg." AS promedio, MIN(p.ped_fecha) AS fechamin, MAX(p.ped_fecha) AS fechamax ";
    $joinQuery = "FROM tbl_pedidos AS p JOIN tbl_usuarios u ON u.usu_clave_int = p.usu_clave_int  join tbl_mercados m on m.mer_clave_int = u.mer_clave_int JOIN tbl_distritos di on di.dis_clave_int = p.dis_clave_int JOIN tbl_pedidos_detalle d on d.ped_clave_int = p.ped_clave_int JOIN tbl_productos pr on pr.pro_clave_int = d.pro_clave_int JOIN tbl_marcas ma ON ma.mar_clave_int = pr.mar_clave_int ";

    $extraWhere =" (u.usu_clave_int IN(".$cliente1.") OR '".$cliente."' IS NULL OR '".$cliente."' = '') and (p.ped_codigo LIKE REPLACE('%".$pedido."%',' ','%') OR '".$pedido."' IS NULL OR '".$pedido."' = '' ) and  ((p.ped_fec_programada BETWEEN '".$desde."' AND '".$hasta."') or ('".$desde."' Is Null and '".$hasta."' Is Null) or ('".$desde."' = '' and '".$hasta."' = ''))  and  (  pr.pro_clave_int IN(".$pro1.") OR '".$pro."' IS NULL OR '".$pro."' = '' ) and ( m.mer_clave_int IN(".$mercado1.") OR '".$mercado."' IS NULL OR '".$mercado."' = '') and ( ma.mar_clave_int IN(".$marca1.") OR '".$marca."' IS NULL OR '".$marca."' = '') and ( di.dis_clave_int IN(".$distrito1.") OR '".$distrito."' IS NULL OR '".$distrito."' = '') and ( p.usu_vendedor IN(".$vendedor1.") OR '".$vendedor."' IS NULL OR '".$vendedor."' = '') and  ((p.ped_fecha BETWEEN '".$desdeentrega." 00:00:00' AND '".$hastaentrega." 23:59:59') or ('".$desdeentrega."' Is Null and '".$hastaentrega."' Is Null) or ('".$desdeentrega."' = '' and '".$hastaentrega."' = ''))  and di.dis_clave_int in (select dis_clave_int from tbl_usuarios_distritos where usu_clave_int = '".$idUsuario."') ".$wh;
   
    $groupBy = 'ano,semana,agrupacion';
    $orderBy = "ano,semana,agrupacion";
}
else if($tip=="5")
{
    if($agru=="" || $agru==NULL){ $tit = "PRODUCTO"; }
    $li = "B";
    $lf = "R";
    $vagru = "p.ped_fecha";

    $qcant = "COUNT(p.ped_clave_int)";
    $qtotal = "SUM(pde_cantidad)";
    //$qtotal = "sum((pde_cantidad*pde_valor) + ((pde_cantidad*pde_valor)*(pde_iva/100)) - ((pde_cantidad*pde_valor)*(pde_descuento/100)))";
    $qavg = "''";
    $nomarc = "VENTAS".$tit."-MES";
    //if($agru==1  || $agru=="" || $agru==NULL) {  $vagru = "p.ped_fecha"; } 
    if($agru==2) {  $vagru = "p.ped_fec_programada"; } 
    if($agru==3) {  $vagru = "p.ped_fec_entrega"; } 
    if($agru==4) {  $vagru = "m.mer_nombre"; } 
    if($agru==5) {  $vagru = "concat(u.usu_nombre,' ',u.usu_apellido)"; } 
    if($agru==6) {  $vagru = "di.dis_nombre"; }  
    if($agru==7) {  $vagru = "p.usu_vendedor"; } 
    if($agru==8) {  $vagru = "ma.mar_nombre"; } 
    if($agru==9) {  $vagru = "date_format(p.ped_fecha,'%y%u')"; } //semana
    if($agru==10){  $vagru = "date_format(p.ped_fecha,'%Y-%M')"; } //mes
    if($agru==11){  $vagru = "date_format(p.ped_fecha,'%Y')"; } //ano
    if($agru==12  || $agru=="" || $agru==NULL){  
        $vagru = 'pr.pro_nombre';
        $qcant = "COUNT(p.ped_clave_int)";
        $qtotal = "SUM(pde_cantidad)";
        //$qtotal = "sum((pde_cantidad*pde_valor) + ((pde_cantidad*pde_valor)*(pde_iva/100)) - ((pde_cantidad*pde_valor)*(pde_descuento/100)))";
        $qavg = "''"; 
    }

    $objPHPExcel->getActiveSheet()
    ->setCellValue('B1', "INFORME DE VENTAS MES")
    ->setCellValue('B2', "AÑO")
    ->setCellValue('C2', "FEC.INICIO")
    ->setCellValue('D2', "FEC.FINAL")
    ->setCellValue('E2', $tit)
    ->setCellValue('F2', "ENERO")
    ->setCellValue('G2', "FEBRERO")
    ->setCellValue('H2', "MARZO")
    ->setCellValue('I2', "ABRIL")
    ->setCellValue('J2', "MAYO")
    ->setCellValue('K2', "JUNIO")
    ->setCellValue('L2', "JULIO")
    ->setCellValue('M2', "AGOSTO")
    ->setCellValue('N2', "SEPTIEMBRE")
    ->setCellValue('O2', "OCTUBRE")
    ->setCellValue('P2', "NOVIEMBRE")
    ->setCellValue('Q2', "DICIEMBRE")
    ->setCellValue('R2', "TOTAL CAJAS")
    ->mergeCells('B1:R1');   
    
    $sql = "SELECT date_format(p.ped_fecha,'%Y') AS ano, ".$vagru." AS agrupacion, '' AS ene, '' AS feb, '' AS mar, '' AS abr, '' AS may, '' AS jun, '' AS jul, '' AS ago, '' AS sep, '' AS oct, '' AS nov, '' AS dic, ".$qtotal." AS CantidadCajas, ".$qcant." AS cantidad, ".$qavg." AS promedio, MIN(p.ped_fecha) AS fechamin, MAX(p.ped_fecha) AS fechamax ";
    $joinQuery = "FROM tbl_pedidos AS p JOIN tbl_usuarios u ON u.usu_clave_int = p.usu_clave_int  join tbl_pedidos_detalle d on d.ped_clave_int = p.ped_clave_int join tbl_productos pr on pr.pro_clave_int = d.pro_clave_int join tbl_mercados m on m.mer_clave_int = d.mer_clave_int JOIN tbl_distritos di on di.dis_clave_int = p.dis_clave_int JOIN tbl_marcas ma ON ma.mar_clave_int = pr.mar_clave_int";
 
    $extraWhere =" ( u.usu_clave_int IN(".$cliente1.") OR '".$cliente."' IS NULL OR '".$cliente."' = '') and (p.ped_codigo LIKE REPLACE('%".$pedido."%',' ','%') OR '".$pedido."' IS NULL OR '".$pedido."' = '' ) and  ((p.ped_fec_programada BETWEEN '".$desde."' AND '".$hasta."') or ('".$desde."' Is Null and '".$hasta."' Is Null) or ('".$desde."' = '' and '".$hasta."' = ''))  and  (  pr.pro_clave_int IN(".$pro1.") OR '".$pro."' IS NULL OR '".$pro."' = '' ) and ( m.mer_clave_int IN(".$mercado1.") OR '".$mercado."' IS NULL OR '".$mercado."' = '') and ( ma.mar_clave_int IN(".$marca1.") OR '".$marca."' IS NULL OR '".$marca."' = '') and ( di.dis_clave_int IN(".$distrito1.") OR '".$distrito."' IS NULL OR '".$distrito."' = '') and (date_format(ped_fecha,'%Y') IN (".$ano1.")  OR '".$ano."' IS NULL OR '".$ano."' = '' )  and ( p.usu_vendedor IN(".$vendedor1.") OR '".$vendedor."' IS NULL OR '".$vendedor."' = '') and  ((p.ped_fecha BETWEEN '".$desdeentrega." 00:00:00' AND '".$hastaentrega." 23:59:59') or ('".$desdeentrega."' Is Null and '".$hastaentrega."' Is Null) or ('".$desdeentrega."' = '' and '".$hastaentrega."' = '')) and di.dis_clave_int in (select dis_clave_int from tbl_usuarios_distritos where usu_clave_int = '".$idUsuario."')  ".$wh;

    $groupBy = "ano,agrupacion";
    $orderBy = "ano,agrupacion";
}

cellColor('B2:'.$lf.'2', '17a2b8 ');

$objPHPExcel->getActiveSheet()->getStyle('B1:'.$lf.'2')->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
$objPHPExcel->getActiveSheet()->getStyle('B1:'.$lf.'2')->getFont()->setBold(true);
$objPHPExcel->getActiveSheet()->getStyle('B1:'.$lf.'2')->getAlignment()->applyFromArray(array('horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER));
$objPHPExcel->getActiveSheet()->getStyle('B1:'.$lf.'2')->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);

//echo date('H:i:s') , " Set column height" , EOL;
$objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(14);
$objPHPExcel->getActiveSheet()->getRowDimension('2')->setRowHeight(12.75);


$queryFinal = $sql . " " . $joinQuery . " WHERE " . $extraWhere . " GROUP BY " . $groupBy . " ORDER BY " . $orderBy;
$stmt = $conn->prepare($queryFinal);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$numpro = count($rows);
$hastacont = $numpro + 3;
$acum = $hastacont;
$filc = 3;
for ($i = 0; $i < $numpro; $i++) {
    $data = $rows[$i];

    if($tip==1 and $agru!=12)
    {
        $orden  = $data['Orden'];
        $codigo    = $data['Codigo'];
        $cliente   = $data['Cliente'];
        $mercado = $data['Mercado'];
        $vendedor = $data['vendedor'];
        $fechac    = $data['Fecha'];
        $fechap    = $data['FechaProgramado'];
        $horap     = $data['HoraProgramada'];
        $telefono  = $data['Telefono'];
        $direccion = $data['Direccion']; 
        //$subtotal  = $data['Total'];
        $mercado = $data['Mercado'];
        $distrito = $data['Distrito'];
        $descuento = $data['Descuento'];
        $estado    = $data['Estado'];
        $factura  = $data['Factura'];
        $cantidad = $data['CantidadCajas'];
        $nomvendedor = "";

        $sql = "SELECT CONCAT(usu_nombre, ' ', usu_apellido) AS nom 
        FROM tbl_usuarios 
        WHERE usu_clave_int = :vendedor 
        LIMIT 1";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':vendedor', $vendedor, PDO::PARAM_INT);
        $stmt->execute();

        $dat1 = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($dat1) {
            $nomvendedor = $dat1['nom'];
        }


        if($estado==1) { $estado  = "Por Aprobar"; }
        else if($estado==2){ $estado  = "En proceso de entrega"; }
        else if($estado==3 and $factura==""){ $estado = "Por Facturar"; }
        else if($estado==3 and $factura!=""){ $estado = "Por Entregar"; }
        else if($estado==4){ $estado = "Entregada"; } 
        else if($estado==5){ $estado = "Cancelada"; } 
      
        $objPHPExcel->getActiveSheet()
        // ->setCellValue('B' . $filc, $codigo)
         ->setCellValue('C' . $filc, $fechac)
         ->setCellValue('D' . $filc, $fechap)
         ->setCellValue('E' . $filc, $horap)
         ->setCellValue('F' . $filc, $mercado)
         ->setCellValue('G' . $filc, $cliente)
         ->setCellValue('H' . $filc, $nomvendedor)
         ->setCellValue('I' . $filc, $distrito)
         ->setCellValue('J' . $filc, $direccion)
         ->setCellValue('K' . $filc, $telefono)
         ->setCellValue('L' . $filc, $cantidad)
         ->setCellValue('M' . $filc, $estado)
         //->setCellValue('M' . $filc, $descuento)
         //->setCellValue('N' . $filc, "=L".$filc."-M".$filc)
         //->setCellValue('O' . $filc, $estado)
         ;
 
         $objPHPExcel->getActiveSheet()->getCell('B' . $filc)->setValueExplicit($codigo,PHPExcel_Cell_DataType::TYPE_STRING);
         //$objPHPExcel->getActiveSheet()->getStyle('L'.$filc.':N'.$filc)->getNumberFormat()->setFormatCode('[Black][>=0]"$"* #,##0.00;[Red][<0]("$"* #,##0.00);"$"* #,##0.00'); 		
        
    }
    else if($tip==1 and $agru==12)
    {
        $marca     = $data['marca'];
        $codigo    = $data['codigo'];
        $producto  = $data['producto'];
        $cantidad  = $data['CantidadCajas'];
        $unidad    = $data['unidad'];
        $total     = $data['total'];

        if($unidad==1){ $unidad ="Cajas";}else
        if($unidad==2){ $unidad ="Kilos"; }
        //echo "".$categoria."-".$producto."-".$clasificacion."-".$cantidadv."-".$unidad."-".$total."<br>";
        $objPHPExcel->getActiveSheet()
        ->setCellValue('B' . $filc, $marca)
        //->setCellValue('C' . $filc, $codigo)
        ->setCellValue('D' . $filc, $producto)
        ->setCellValue('E' . $filc, $cantidad);
        //->setCellValue('F' . $filc, $unidad)
        //->setCellValue('G' . $filc, $total)
        $objPHPExcel->getActiveSheet()->getCell('C' . $filc)->setValueExplicit($codigo,PHPExcel_Cell_DataType::TYPE_STRING);
        //$objPHPExcel->getActiveSheet()->getStyle('G'.$filc)->getNumberFormat()->setFormatCode('[Black][>=0]"$"* #,##0.00;[Red][<0]("$"* #,##0.00);"$"* #,##0.00'); 	
    }    
    else if($tip==2 and $agru!=12)
    {
        $agrupacion = $data['agrupacion'];
        $total      = $data['total'];
        $cantidad   = $data['CantidadCajas'];
        $promedio   = $data['promedio']; 

        $agrupaciont = agrupacion;
        if($agru==7)
		{
           
            $sql = "SELECT CONCAT(usu_nombre, ' ', usu_apellido) AS nom 
                    FROM tbl_usuarios 
                    WHERE usu_clave_int = :agrupacion 
                    LIMIT 1";

            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':agrupacion', $agrupacion, PDO::PARAM_INT);
            $stmt->execute();

            $dat1 = $stmt->fetch(PDO::FETCH_ASSOC);

            $nom = $dat1['nom'] ?? '';
            $agrupaciont = $nom;
		}
		
       

        $objPHPExcel->getActiveSheet()
        ->setCellValue('B' . $filc, $agrupaciont)
        ->setCellValue('C' . $filc, $cantidad);
        //->setCellValue('D' . $filc, $total)
       // ->setCellValue('E' . $filc, $promedio);
       // $objPHPExcel->getActiveSheet()->getStyle('D'.$filc.':E'.$filc)->getNumberFormat()->setFormatCode('[Black][>=0]"$"* #,##0.00;[Red][<0]("$"* #,##0.00);"$"* #,##0.00'); 
        
    }
    else if($tip==3)//informe semana
    {
        $ano        = $data['ano'];
        $semana     = $data['semana'];
        $fechamin   = $data['fechamin'];
        $fechamax   = $data['fechamax'];
        $agrupacion = $data['agrupacion']; 
        $agrupaciont = $agrupacion;
        if($agru==7)
		{
            $sql = "SELECT CONCAT(usu_nombre, ' ', usu_apellido) AS nom 
                    FROM tbl_usuarios 
                    WHERE usu_clave_int = :agrupacion 
                    LIMIT 1";

            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':agrupacion', $agrupacion, PDO::PARAM_INT);
            $stmt->execute();

            $dat1 = $stmt->fetch(PDO::FETCH_ASSOC);

            $nom = $dat1['nom'] ?? '';
            $agrupaciont = $nom;
		}       
        
        $di = array();
        $nd = 1;
        for($d=0;$d<7;$d++)
        {
            $joinq = "FROM tbl_pedidos AS p JOIN tbl_usuarios u ON u.usu_clave_int = p.usu_clave_int  join tbl_mercados m on m.mer_clave_int = u.mer_clave_int JOIN tbl_distritos di on di.dis_clave_int = p.dis_clave_int JOIN tbl_pedidos_detalle d on d.ped_clave_int = p.ped_clave_int JOIN tbl_productos pr on pr.pro_clave_int = d.pro_clave_int JOIN tbl_marcas ma ON ma.mar_clave_int = pr.mar_clave_int  ";
            $eWhere ="  WHERE ( u.usu_clave_int IN(".$cliente1.") OR '".$cliente."' IS NULL OR '".$cliente."' = '') and (p.ped_codigo LIKE REPLACE('%".$pedido."%',' ','%') OR '".$pedido."' IS NULL OR '".$pedido."' = '' ) and  ((p.ped_fec_programada BETWEEN '".$desde."' AND '".$hasta."') or ('".$desde."' Is Null and '".$hasta."' Is Null) or ('".$desde."' = '' and '".$hasta."' = ''))  and  (  pr.pro_clave_int IN(".$pro1.") OR '".$pro."' IS NULL OR '".$pro."' = '' ) and ( m.mer_clave_int IN(".$mercado1.") OR '".$mercado."' IS NULL OR '".$mercado."' = '') and ( ma.mar_clave_int IN(".$marca1.") OR '".$marca."' IS NULL OR '".$marca."' = '') and ( di.dis_clave_int IN(".$distrito1.") OR '".$distrito."' IS NULL OR '".$distrito."' = '') and ( p.usu_vendedor IN(".$vendedor1.") OR '".$vendedor."' IS NULL OR '".$vendedor."' = '') and  ((p.ped_fecha BETWEEN '".$desdeentrega." 00:00:00' AND '".$hastaentrega." 23:59:59') or ('".$desdeentrega."' Is Null and '".$hastaentrega."' Is Null) or ('".$desdeentrega."' = '' and '".$hastaentrega."' = ''))  and di.dis_clave_int in (select dis_clave_int from tbl_usuarios_distritos where usu_clave_int = '".$idUsuario."')  and (date_format(p.ped_fecha,'%Y') = '".$ano."') and (date_format(p.ped_fecha,'%y%u') = '".$semana."') and (date_format(p.ped_fecha,'%w') = '".$nd."') ".$wh;
            $eWhere.= "AND ".$vagru."='".$agrupacion."'";          


            $query = "SELECT " . $qtotal . " AS tot " . $joinq . $eWhere;
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $dat = $stmt->fetch(PDO::FETCH_ASSOC);
            $tot = $dat['tot'] ?? 0;
            $tot = ($tot < 0 || $tot === "" || is_null($tot)) ? 0 : $tot;

            $di[$d] = $tot;
            $nd++;
        }
        $objPHPExcel->getActiveSheet()
        ->setCellValue('B' . $filc, $ano)
        ->setCellValue('C' . $filc, $semana)
        ->setCellValue('D' . $filc, $fechamin)
        ->setCellValue('E' . $filc, $fechamax)
        ->setCellValue('F' . $filc, $agrupaciont)
        ->setCellValue('G' . $filc, $di[0])
        ->setCellValue('H' . $filc, $di[1])
        ->setCellValue('I' . $filc, $di[2])
        ->setCellValue('J' . $filc, $di[3])
        ->setCellValue('K' . $filc, $di[4])
        ->setCellValue('L' . $filc, $di[5])
        ->setCellValue('M' . $filc, $di[6])
        ->setCellValue('N' . $filc, "=SUM(G".$filc.":M".$filc.")");  
        //$objPHPExcel->getActiveSheet()->getStyle('G'.$filc.':N'.$filc)->getNumberFormat()->setFormatCode('[Black][>=0]"$"* #,##0.00;[Red][<0]("$"* #,##0.00);"$"* #,##0.00');     	
    }
    else if($tip==5)
    {
        $ano        = $data['ano'];       
        $fechamin   = $data['fechamin'];
        $fechamax   = $data['fechamax'];
        $agrupacion = $data['agrupacion']; 
        $agrupaciont = $agrupacion;
        if($agru==7)
		{
            $sql = "SELECT CONCAT(usu_nombre, ' ', usu_apellido) AS nom 
                    FROM tbl_usuarios 
                    WHERE usu_clave_int = :agrupacion 
                    LIMIT 1";

            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':agrupacion', $agrupacion, PDO::PARAM_INT);
            $stmt->execute();

            $dat1 = $stmt->fetch(PDO::FETCH_ASSOC);

            $nom = $dat1['nom'] ?? '';
            $agrupaciont = $nom;
		}
		
     
        $mi = array();
        $nd = 1;
        for($d=0;$d<12;$d++)
        {
            $mes = ($nd<10)? "0".$nd:$nd;
            $joinq = "FROM tbl_pedidos AS p JOIN tbl_usuarios u ON u.usu_clave_int = p.usu_clave_int  join tbl_mercados m on m.mer_clave_int = u.mer_clave_int JOIN tbl_distritos di on di.dis_clave_int = p.dis_clave_int JOIN tbl_pedidos_detalle d on d.ped_clave_int = p.ped_clave_int JOIN tbl_productos pr on pr.pro_clave_int = d.pro_clave_int JOIN tbl_marcas ma ON ma.mar_clave_int = pr.mar_clave_int";
           
            $eWhere ="  WHERE ( u.usu_clave_int IN(".$cliente1.") OR '".$cliente."' IS NULL OR '".$cliente."' = '') and (p.ped_codigo LIKE REPLACE('%".$pedido."%',' ','%') OR '".$pedido."' IS NULL OR '".$pedido."' = '' ) and  ((p.ped_fec_programada BETWEEN '".$desde."' AND '".$hasta."') or ('".$desde."' Is Null and '".$hasta."' Is Null) or ('".$desde."' = '' and '".$hasta."' = ''))  and  (  pr.pro_clave_int IN(".$pro1.") OR '".$pro."' IS NULL OR '".$pro."' = '' ) and ( m.mer_clave_int IN(".$mercado1.") OR '".$mercado."' IS NULL OR '".$mercado."' = '') and ( ma.mar_clave_int IN(".$marca1.") OR '".$marca."' IS NULL OR '".$marca."' = '') and ( di.dis_clave_int IN(".$distrito1.") OR '".$distrito."' IS NULL OR '".$distrito."' = '') and ( p.usu_vendedor IN(".$vendedor1.") OR '".$vendedor."' IS NULL OR '".$vendedor."' = '') and  ((p.ped_fecha BETWEEN '".$desdeentrega." 00:00:00' AND '".$hastaentrega." 23:59:59') or ('".$desdeentrega."' Is Null and '".$hastaentrega."' Is Null) or ('".$desdeentrega."' = '' and '".$hastaentrega."' = ''))   and di.dis_clave_int in (select dis_clave_int from tbl_usuarios_distritos where usu_clave_int = '".$idUsuario."') and (date_format(p.ped_fecha,'%Y') = '".$ano."') and (date_format(p.ped_fecha,'%m') = '".$mes."') ".$wh;

            $eWhere.= "AND ".$vagru."='".$agrupacion."'";
            $query = "SELECT " . $qtotal . " AS tot " . $joinq . $eWhere;
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $dat = $stmt->fetch(PDO::FETCH_ASSOC);
            $tot = $dat['tot'] ?? 0;
            $tot = ($tot < 0 || $tot === "" || is_null($tot)) ? 0 : $tot;

            $mi[$d] = $tot;
            $nd++;
        }
        $objPHPExcel->getActiveSheet()
        ->setCellValue('B' . $filc, $ano)
        ->setCellValue('C' . $filc, $fechamin)
        ->setCellValue('D' . $filc, $fechamax)
        ->setCellValue('E' . $filc, $agrupaciont)
        ->setCellValue('F' . $filc, $mi[0])
        ->setCellValue('G' . $filc, $mi[1])
        ->setCellValue('H' . $filc, $mi[2])
        ->setCellValue('I' . $filc, $mi[3])
        ->setCellValue('J' . $filc, $mi[4])
        ->setCellValue('K' . $filc, $mi[5])
        ->setCellValue('L' . $filc, $mi[6])
        ->setCellValue('M' . $filc, $mi[7])
        ->setCellValue('N' . $filc, $mi[8])
        ->setCellValue('O' . $filc, $mi[9])
        ->setCellValue('P' . $filc, $mi[10])
        ->setCellValue('Q' . $filc, $mi[11])
        ->setCellValue('R' . $filc, "=SUM(F".$filc.":Q".$filc.")");  
       // $objPHPExcel->getActiveSheet()->getStyle('F'.$filc.':R'.$filc)->getNumberFormat()->setFormatCode('[Black][>=0]"$"* #,##0.00;[Red][<0]("$"* #,##0.00);"$"* #,##0.00');    
    }

    $filc = $filc + 1;
}
$filc1 = $filc;
$filcu = $filc-1;

$objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(18);
$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('C')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('D')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('E')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('F')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('G')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('J')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('K')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('L')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('M')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('N')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('O')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('P')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('Q')->setAutoSize(true);
$objPHPExcel->getActiveSheet()->getColumnDimension('R')->setAutoSize(true);
//zoom de la pagina
$objPHPExcel->getActiveSheet()->getSheetView()->setZoomScale(90);
//titulos de la hoja
$objPHPExcel->getActiveSheet()->setTitle($nomarc);
//$objPHPExcel->getActiveSheet()->setSheetState(PHPExcel_Worksheet::SHEETSTATE_HIDDEN);
$callStartTime = microtime(true);
$archivo = $nomarc.'.xlsx';
$objPHPExcel->setActiveSheetIndex(0);
$archivo =  date('Ymd').''.$archivo;
// Redirect output to a client’s web browser (Xls)
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment;filename="'.$archivo.'"');
header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
header('Cache-Control: max-age=1');

// If you're serving to IE over SSL, then the following may be needed
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
header('Pragma: public'); // HTTP/1.0

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter->save('php://output');
  
//$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
//$writer->save('php://output');