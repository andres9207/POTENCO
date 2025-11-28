<?php
include '../../../data/db.config.php';
include "../../../data/conexion.php";
session_start();
error_reporting(0);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
ini_set('memory_limit','500M');
ini_set('max_execution_time', 600);
ini_set('upload_max_filesize', '300M');
ini_set('safe_mode', 0);
include '../../../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Helper\Sample;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$usuario   = $_COOKIE['usuarioPo'];
$idUsuario = $_COOKIE['idusuarioPo'];
$perfil    = $_SESSION["perfilPo"];
$IP = $_SERVER['REMOTE_ADDR'];
//include '../../data/validarpermisos.php';
setlocale(LC_TIME, 'es_CO.UTF-8'); 
date_default_timezone_set('America/Bogota');
$fecha  = date("Y/m/d H:i:s");
require_once "../../../controladores/general.controller.php";
include ("../../../data/validarpermisos.php");

$emp = $_GET['emp'];
$emp = implode(', ', (array)$emp); if($emp==""){$emp1="''";}else {$emp1=$emp;}
$des = $_GET['des'];
$has = $_GET['has'];
$tip = $_GET['tip'];

$titulo  = $tip==1?"INFORME HORAS CONTABILIDAD":"INFORME ALIMENTACION";

if($tip==1)
{

    // getPorcentaje(u.usu_clave_int,'HEDO','".$des."','".$has."') phedo, 
    // getPorcentaje(u.usu_clave_int,'HDF','".$des."','".$has."') phdf, 
    // getPorcentaje(u.usu_clave_int,'HEDF','".$des."','".$has."') phedf, 
    // getPorcentaje(u.usu_clave_int,'HENO','".$des."','".$has."') pheno, 
    // getPorcentaje(u.usu_clave_int,'HENF','".$des."','".$has."') phenf, 
    // getPorcentaje(u.usu_clave_int,'RN','".$des."','".$has."') prn, 
    // getPorcentaje(u.usu_clave_int,'HNF','".$des."','".$has."') phnf, 
    // getPorcentaje(u.usu_clave_int,'RD','".$des."','".$has."') prd, 
    // getPorcentaje(u.usu_clave_int,'ARE','".$des."','".$has."') pare,

    $sqldias = "SELECT u.usu_clave_int Clave, concat(u.usu_apellido,' ', u.usu_nombre) Nombre,u.usu_documento Cedula, 
    avg( l.liq_salario ) ValorHora, 
    avg( l.liq_hr_mes ) HorasMes,
    (avg( l.liq_salario ) * avg( l.liq_hr_mes ) ) Basico, 
    SUM(DISTINCT(IF(h.hor_nombre='HEDO',lih_porcentaje,0))) phedo, 
    SUM(DISTINCT(IF(h.hor_nombre='HDF',lih_porcentaje,0))) phdf, 
    SUM(DISTINCT(IF(h.hor_nombre='HEDF',lih_porcentaje,0))) phedf, 
    SUM(DISTINCT(IF(h.hor_nombre='HENO',lih_porcentaje,0))) pheno, 
    SUM(DISTINCT(IF(h.hor_nombre='HENF',lih_porcentaje,0))) phenf, 
    SUM(DISTINCT(IF(h.hor_nombre='RN',lih_porcentaje,0))) prn, 
    SUM(DISTINCT(IF(h.hor_nombre='HNF',lih_porcentaje,0))) phnf, 
    SUM(DISTINCT(IF(h.hor_nombre='RD',lih_porcentaje,0))) prd, 
    SUM(DISTINCT(IF(h.hor_nombre='ARE',lih_porcentaje,0))) pare,
    sum(IF(h.hor_nombre='HEDO',ld.lid_hedo,0)) hedo,
    sum(IF(h.hor_nombre='HDF',ld.lid_hdf,0)) hdf,
    sum(IF(h.hor_nombre='HEDF',ld.lid_hedf,0)) hedf,
    sum(IF(h.hor_nombre='HENO',ld.lid_heno,0)) heno,
    sum(IF(h.hor_nombre='HENF',ld.lid_henf,0)) henf,
    sum(IF(h.hor_nombre='RN',ld.lid_rn,0)) rn,
    sum(IF(h.hor_nombre='HNF',ld.lid_hnf,0)) hnf,
    sum(IF(h.hor_nombre='RD',ld.lid_rd,0)) rd,
    sum(IF(h.hor_nombre='ARE',ld.lid_permisos,0)) are
    FROM tbl_liquidar l JOIN tbl_liquidar_dias ld ON ld.liq_clave_int = l.liq_clave_int JOIN tbl_usuarios u ON u.usu_clave_int = l.usu_clave_int JOIN tbl_liquidar_horas lh on lh.liq_clave_int = l.liq_clave_int JOIN tbl_horas h ON lh.hor_clave_int = h.hor_clave_int
    
    WHERE (u.usu_clave_int  in(".$emp1.")  OR '".$emp."' IS NULL OR '".$emp."' = '') and u.est_clave_int in(1) and  ((ld.lid_fecha BETWEEN '".$des."' AND '".$has."') or ('".$des."' Is Null and '".$has."' Is Null) or ('".$des."' = '' and '".$has."' = '')) GROUP BY Clave, Nombre, Cedula ORDER BY Nombre";
}
else
{
    $sqldias = "SELECT CONCAT( u.usu_apellido, ' ',u.usu_nombre) nom, ld.lid_fecha fec, SUM(ld.lid_val_alimentacion) alimentacion FROM tbl_usuarios u JOIN tbl_liquidar l on l.usu_clave_int = u.usu_clave_int join tbl_liquidar_dias ld on ld.liq_clave_int = l.liq_clave_int where l.liq_inicio = '".$des."' and l.liq_fin = '".$has."' and ld.lid_alimentacion = 1 and (u.usu_clave_int  in(".$emp1.")  OR '".$emp."' IS NULL OR '".$emp."' = '') GROUP BY u.usu_nombre, u.usu_apellido,ld.lid_fecha ORDER BY nom , ld.lid_fecha";
}


define('EOL',(PHP_SAPI == 'cli') ? PHP_EOL : '<br />');
date_default_timezone_set("America/Bogota");
setlocale (LC_TIME,"spanish", "es_ES@euro", "es_ES", "es");

function convert_htmlentities($data)
{
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

date_default_timezone_set('UTC');
$helper = new Sample();
if ($helper->isCli()) {
    $helper->log('This example should only be run from a Web Browser' . PHP_EOL);
    return;
}

function cellColor($cells,$color){
    global $spreadsheet;
    $spreadsheet->getActiveSheet()->getStyle($cells)
    ->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID);
    $spreadsheet->getActiveSheet()->getStyle($cells)
    ->getFill()->getStartColor()->setARGB($color);
}

$spreadsheet = new Spreadsheet();

$spreadsheet->getProperties()->setCreator("PAVAS SAS")
    ->setLastModifiedBy("PAVAS SAS")
    ->setTitle($titulo)
    ->setSubject("Informe Contabilidad")
    ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
    ->setKeywords("office 2021 openxml php")
    ->setCategory("Informes");

if($tip==1)
{
    $spreadsheet->setActiveSheetIndex(0);
    $nomarc = "HORAS CONTABILIDAD";
    $spreadsheet->getActiveSheet()
    ->setCellValue('A1', "HORAS CONTABILIDAD")
    ->setCellValue('A2', "EMPLEADO")
    ->setCellValue('B2', "CEDULA")
    ->setCellValue('C2', "001")
    ->setCellValue('D2', "002")
    ->setCellValue('E2', "004")
    ->setCellValue('F2', "014")
    ->setCellValue('G2', "003")
    ->setCellValue('H2', "005")
    ->setCellValue('I2', "006")
    ->setCellValue('J2', "013")
    ->setCellValue('K2', "008")
    ->setCellValue('L2', "010")

    ->setCellValue('M2', "")
    ->setCellValue('N2', "")
    ->setCellValue('O2', "")
    ->setCellValue('P2', "")
    ->setCellValue('Q2', "014")
    ->setCellValue('R2', "002")

    ->setCellValue('S2', "002")
    ->setCellValue('T2', "010")
    ->setCellValue('U2', "006")
    ->setCellValue('V2', "008")
    ->setCellValue('W2', "003")
    ->setCellValue('X2', "005")
    ->setCellValue('Y2', "004")
    ->setCellValue('Z2', "013")
    ->setCellValue('AA2', "014")

    ->setCellValue('AB2', "002")
    ->setCellValue('AC2', "006")
    ->setCellValue('AD2', "008")
    ->setCellValue('AE2', "004")
    ->setCellValue('AF2', "014")
    ->setCellValue('AG2', "003")
    ->setCellValue('AH2', "013")
    ->setCellValue('AI2', "005")
    ->setCellValue('AJ2', "010")
    ->setCellValue('AK2', "VALOR TOTAL SIN DEDUCCIONES")
    ->setCellValue('AL2', "002")
    ->setCellValue('AM2', "014")
    ->setCellValue('AN2', "TOTAL BONIFICACIÓN")
    ->setCellValue('AO2', "TOTAL")


    ->setCellValue('C3', "BÁSICO")

    ->setCellValue('D3', "VALOR EXTRA ORDINARIA")
    ->setCellValue('E3', "VALOR EXTRA DIURNA FESTIVA")
    ->setCellValue('F3', "VALOR DIURNA FESTIVA")
    ->setCellValue('G3', "VALOR EXTRA NOCTURNA ORDINARIA")
    ->setCellValue('H3', "VALOR EXTRA NOCTURNA FESTIVA")
    ->setCellValue('I3', "VALOR RECARGO NOCTURNO")
    ->setCellValue('J3', "VALOR RECARGO DOMINICAL")
    ->setCellValue('K3', "VALOR RECARGO NOCTURNO DOMINICAL")
    ->setCellValue('L3', "VALOR RECARGO EXTRAS ORDINARIA")

    ->setCellValue('M3', "TORAL HORAS")
    ->setCellValue('N3', "TORAL HORAS")
    ->setCellValue('O3', "HORAS A AJUSTAR")
    ->setCellValue('P3', "")
    ->setCellValue('Q3', "HORAS FESTIVO COMO BONIFICACION")
    ->setCellValue('R3', "HORAS ORDINARIAS COMO BONIFICACION")

    ->setCellValue('S3', "TOTAL EXTRAS ORDINARIAS LABORADAS") //002
    ->setCellValue('T3', "TOTAL RECARGO EXTRAS ORDINARIAS") //010
    ->setCellValue('U3', "TOTAL RECARGO NOCTURNO") // 006
    ->setCellValue('V3', "TOTAL RECARGO NOCTURNAS DOMINICAL") //008
    ->setCellValue('W3', "TOTAL EXTRAS NOCTURNAS") // 003
    ->setCellValue('X3', "TOTAL EXTRAS NOCTURNAS FESTIVA") //005
    ->setCellValue('Y3', "TOTAL EXTRAS DIURNAS FESTIVAS") //004
    ->setCellValue('Z3', "TOTAL RECARGO DOMINICAL") //013
    ->setCellValue('AA3', "TOTAL EXTRAS FESTIVAS") //014

    ->setCellValue('AB3', "VALOR  EXTRAS ORDINARIAS")
    ->setCellValue('AC3', "VALOR RECARGO NOCTURNO")
    ->setCellValue('AD3', "VALOR RECARGO NOCTURNO DOMINICAL")
    ->setCellValue('AE3', "VALOR EXTRAS FESTIVAS")
    ->setCellValue('AF3',  "VALOR DIURNA FESTIVAS")
    ->setCellValue('AG3', "VALOR EXTRAS NOCTURNO")
    ->setCellValue('AH3', "VALOR RECARGO DOMINICAL")
    ->setCellValue('AI3', "VALOR EXTRAS DOMINICAL NOCTURNO")
    ->setCellValue('AJ3', "VALOR RECARGO EXTRAS ORDINARIAS")//PERMISOS QUE SE PAHAN AL 25%

    ->setCellValue('AL3', "VALOR HORA EXTRA COMO BONIFICACIÓN")
    ->setCellValue('AM3', "VALOR EXTRA DIURNA FESTIVA COMO BONIFICACIÓN")

    //MERGE COLUMNAS
    ->mergeCells('A2:A3')
    ->mergeCells('B2:B3')
    ->mergeCells('AK2:AK3')
    ->mergeCells('AN2:AN3')
    ->mergeCells('AO2:AO3')


    //MERGE FILAS
    ->mergeCells('A1:AO1');
    // ->mergeCells('S2:S3')
    // ->mergeCells('T2:T3')
    // ->mergeCells('U2:U3')
    // ->mergeCells('V2:V3')
    // ->mergeCells('H1:AD1');   
    cellColor('A2:L3','002060');

    cellColor('M2:P3','CCC0DA');// MORADO
    cellColor('Q2:R3','9BBB59');// VERDE

    cellColor('S2:AA3','E86E0A');
    cellColor('AB2:AJ3','002060');
    cellColor('AK2:AK3','FFFFFF');
    cellColor('AL2:AN3','9BBB59');// VERDER TOTALES BONIDIFCACION

    cellColor('AO2:AO3','FFFFFF');

    $spreadsheet->getActiveSheet()->getStyle('A2:AJ3')
        ->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
        
    $spreadsheet->getActiveSheet()->getStyle('AK2:AO3')
    ->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_ORANGE);

    $spreadsheet->getActiveSheet()->getStyle('A2:AO3')->getAlignment()->setWrapText(true);
    // $spreadsheet->getActiveSheet()->getStyle('P2')->getAlignment()->setWrapText(true);
    // $spreadsheet->getActiveSheet()->getStyle('T2')->getAlignment()->setWrapText(true);
    // $spreadsheet->getActiveSheet()->getStyle('U2')->getAlignment()->setWrapText(true);

    // $spreadsheet->getActiveSheet()->getCell('W3')->setValueExplicit($codhedo,\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    // $spreadsheet->getActiveSheet()->getCell('X3')->setValueExplicit($codhdf,\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    // $spreadsheet->getActiveSheet()->getCell('Y3')->setValueExplicit($codhedf,\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    // $spreadsheet->getActiveSheet()->getCell('Z3')->setValueExplicit($codheno,\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    // $spreadsheet->getActiveSheet()->getCell('AA3')->setValueExplicit($codhenf,\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    // $spreadsheet->getActiveSheet()->getCell('AB3')->setValueExplicit($codrn,\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    // $spreadsheet->getActiveSheet()->getCell('AC3')->setValueExplicit($codhnf,\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    // $spreadsheet->getActiveSheet()->getCell('AD3')->setValueExplicit($codrd,\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

    //cellColor('B2:'.$lf.'2', '17a2b8 ');

    $spreadsheet->getActiveSheet()->getStyle('A1:AO3')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
    $spreadsheet->getActiveSheet()->getStyle('A1:AO3')->getFont()->setBold(true);
    $spreadsheet->getActiveSheet()->getStyle('A1:AO3')->getAlignment()->applyFromArray(array('horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER));
    $spreadsheet->getActiveSheet()->getStyle('A1:AO3')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

    //echo date('H:i:s') , " Set column height" , EOL;
    $spreadsheet->getActiveSheet()->getRowDimension('1')->setRowHeight(60);
    $spreadsheet->getActiveSheet()->getRowDimension('2')->setRowHeight(12.75);
    $spreadsheet->getActiveSheet()->getRowDimension('3')->setRowHeight(101);

    $stmtDias = $conn->prepare($sqldias);
    $stmtDias->execute();
    $numdias = $stmtDias->rowCount();
    //echo $numpro;
    $hastacont = $numdias +4;
    $acum = $hastacont;
    $filc = 4;
    $filcs = 5;
    while ($data = $stmtDias->fetch(PDO::FETCH_ASSOC)) {
        $nombre = $data['Nombre'];
        $cedula = $data['Cedula'];
        $basico = $data['Basico'];
        $vrhor  = $data['ValorHora'];
        $hormes = $data['HorasMes'];

        $HEDO = 0; // HORAS EXTRAS DIURNAS ORDINA
        $HDF  = 0;
        $HEDF = 0;
        $HENO = 0;
        $HENF = 0;
        $RN   = 0;
        $HDF  = 0;
        $RD   = 0;

        $PHEDO = $data['phedo']; $PHEDO = ($PHEDO=="" || $PHEDO==NULL)?0:$PHEDO*100;
        $PHENF = $data['phenf']; $PHENF = ($PHENF=="" || $PHENF==NULL)?0:$PHENF*100;
        $PHENO = $data['pheno']; $PHENO = ($PHENO=="" || $PHENO==NULL)?0:$PHENO*100;
        $PHEDF = $data['phedf']; $PHEDF = ($PHEDF=="" || $PHEDF==NULL)?0:$PHEDF*100;
        $PHNF  = $data['phnf'];  $PHNF = ($PHNF=="" || $PHNF==NULL)?0:$PHNF*100;
        $PRD   = $data['prd'];   $PRD   = ($PRD=="" || $PRD==NULL)?0:$PRD*100;
        $PRN   = $data['prn'];   $PRN   = ($PRN=="" || $PRN==NULL)?0:$PRN*100;
        $PHDF  = $data['phdf'];  $PHDF  = ($PHDF=="" || $PHDF==NULL)?0:$PHDF*100;
        $PARE  = $data['pare'];  $PARE  = ($PARE=="" || $PARE==NULL)?0:$PARE*100;
        
        
        $HENF  = $data['henf'];
        $HENO  = $data['heno'];
        $HEDF  = $data['hedf'];
        $HNF   = $data['hnf'];
        $RD    = $data['rd'];
        $RN    = $data['rn'];
        $HDF   = $data['hdf'];
        $ARE   = $data['are'];   
        $HEDO  = $data['hedo'];    
        $HEDO  = ($ARE>0)? $HEDO-$ARE: $HEDO;


        $EXTGEN = $HEDO + $HENO + $HENF + $HEDF + $HDF;

        $formulaQ = $EXTGEN>24 ? '=IF(AND('.$HEDO.'>'.$HENO.','.$HEDO.'>'.$HENF.','.$HEDO.'>'.$HEDF.','.$HEDO.'>'.$HDF.'),IF(O'.$filc.'>'.$HEDO.', O'.$filc.'-'.$HEDO.',0),IF(O'.$filc.'>'.$HDF.','.$HDF.',O'.$filc.'))': 0;
        
        $formulaR = $EXTGEN>24 ? '=IF(AND('.$HEDO.'>'.$HENO.','.$HEDO.'>'.$HENF.','.$HEDO.'>'.$HEDF.','.$HEDO.'>'.$HDF.'),IF(O'.$filc.'>'.$HEDO.', '.$HEDO.',O'.$filc.'),IF(O'.$filc.'>'.$HDF.','.$HEDO.'-(O'.$filc.'-'.$HDF.'),0))': 0;

        $formulaS = $EXTGEN>24 ?'=IF(AND('.$HEDO.'>'.$HENO.','.$HEDO.'>'.$HENF.','.$HEDO.'>'.$HEDF.','.$HEDO.'>'.$HDF.'),IF(O'.$filc.'>'.$HEDO.', 0,'.$HEDO.'-O'.$filc.'),'.$HEDO.')' : $HEDO;

        $formulaAA = $EXTGEN>24 ?'=IF(AND('.$HEDO.'>'.$HENO.','.$HEDO.'>'.$HENF.','.$HEDO.'>'.$HEDF.','.$HEDO.'>'.$HDF.'),IF(O'.$filc.'>'.$HEDO.', '.$HDF.'-(O'.$filc.'-'.$HEDO.'),'.$HDF.'),IF(O'.$filc.'>'.$HDF.',0, '.$HDF.'-O'.$filc.'))': $HDF;

        $formulaAO = '=AK'.$filc.'+AN'.$filc;
        
        $spreadsheet->getActiveSheet()
        ->setCellValue('A'. $filc, $nombre)
        ->setCellValue('B'. $filc, $cedula)
        ->setCellValue('C'. $filc, $basico)

        ->setCellValue('D'. $filc, "=+C".$filc."/".$hormes."*".$PHEDO."%")
        ->setCellValue('E'. $filc, "=+C".$filc."/".$hormes."*".$PHEDF."%")
        ->setCellValue('F'. $filc, "=+C".$filc."/".$hormes."*".$PHDF."%")
        ->setCellValue('G'. $filc, "=+C".$filc."/".$hormes."*".$PHENO."%")
        ->setCellValue('H'. $filc, "=+C".$filc."/".$hormes."*".$PHENF."%")
        ->setCellValue('I'. $filc, "=+C".$filc."/".$hormes."*".$PRN."%")
        ->setCellValue('J'. $filc, "=+C".$filc."/".$hormes."*".$PRD."%")
        ->setCellValue('K'. $filc, "=+C".$filc."/".$hormes."*".$PHNF."%")
        ->setCellValue('L'. $filc, "=+C".$filc."/".$hormes."*".$PARE."%")

        ->setCellValue('M'. $filc,"=+S".$filc."+W".$filc."+X".$filc."+Y".$filc."+AA".$filc)
        ->setCellValue('N'. $filc, $EXTGEN)
        ->setCellValue('O'. $filc, "=+IF(".$EXTGEN.">24,N".$filc."-24,0)")
        // ->setCellValue('P'. $filc, $HNF)
        ->setCellValue('Q' . $filc, $formulaQ) // CALCULO BONIFICACION FESTIVA
        ->setCellValue('R' . $filc, $formulaR) // CALCULO BONIFICACION ORDINARIA

        // ->setCellValue('M'. $filc, $HEDO)
        // ->setCellValue('N'. $filc, $ARE)
        // ->setCellValue('O'. $filc, $RN)
        // ->setCellValue('P'. $filc, $HNF)
        // ->setCellValue('Q'. $filc, $HENO)
        // ->setCellValue('R'. $filc, $HENF)
        // ->setCellValue('S'. $filc, $HEDF)
        // ->setCellValue('T'. $filc, $RD)
        // ->setCellValue('U'. $filc, $HDF)

         ->setCellValue('S' . $filc, $formulaS)
        // ->setCellValue('S'. $filc, $HEDO)
        ->setCellValue('T'. $filc, $ARE)
        ->setCellValue('U'. $filc, $RN)
        ->setCellValue('V'. $filc, $HNF)
        ->setCellValue('W'. $filc, $HENO)
        ->setCellValue('X'. $filc, $HENF)
        ->setCellValue('Y'. $filc, $HEDF)
        ->setCellValue('Z'. $filc, $RD)
        // ->setCellValue('AA'. $filc, $HDF)
        ->setCellValue('AA' . $filc, $formulaAA)

        ->setCellValue('AB'. $filc, "=+D".$filc."*S".$filc)
        ->setCellValue('AC'. $filc, "=+I".$filc."*U".$filc)
        ->setCellValue('AD'. $filc, "=+K".$filc."*V".$filc)
        ->setCellValue('AE'. $filc, "=+E".$filc."*Y".$filc)
        ->setCellValue('AF'. $filc, "=+F".$filc."*AA".$filc)
        ->setCellValue('AG'. $filc, "=+G".$filc."*W".$filc)
        ->setCellValue('AH'. $filc, "=+J".$filc."*Z".$filc)
        ->setCellValue('AI'. $filc, "=+H".$filc."*X".$filc)
        ->setCellValue('AJ'. $filc, "=+L".$filc."*T".$filc)
        ->setCellValue('AK'. $filc, "=SUM(AB".$filc.":AJ".$filc.")")
        ->setCellValue('AL'. $filc, "=+R".$filc."*D".$filc)
        ->setCellValue('AM'. $filc, "=+Q".$filc."*F".$filc)
        ->setCellValue('AN'. $filc, "=+AL".$filc."+AM".$filc)
        ->setCellValue('AO'. $filc, $formulaAO);
        
        // ->setCellValue('V'. $filc,  "=+D".$filc."*M".$filc)
        // ->setCellValue('W'. $filc,  "=+I".$filc."*O".$filc)
        // ->setCellValue('X'. $filc,  "=+K".$filc."*P".$filc)
        // ->setCellValue('Y'. $filc,  "=+E".$filc."*S".$filc)
        // ->setCellValue('Z'. $filc,  "=+F".$filc."*U".$filc)
        // ->setCellValue('AA'. $filc, "=+G".$filc."*Q".$filc)
        // ->setCellValue('AB'. $filc, "=+J".$filc."*T".$filc)
        // ->setCellValue('AC'. $filc, "=+H".$filc."*R".$filc)
        // ->setCellValue('AD'. $filc, "=+L".$filc."*N".$filc)
        // ->setCellValue('AE'. $filc, "=SUM(V".$filc.":AD".$filc.")");

        $spreadsheet->getActiveSheet()->getStyle("C".$filc.":L".$filc)->getNumberFormat()->setFormatCode('[Black][>=0]"$"* #,##0.00;[Red][<0]("$"* #,##0.00);"$"* #,##0.00');
        $spreadsheet->getActiveSheet()->getStyle("M".$filc.":AA".$filc)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_00);
        $spreadsheet->getActiveSheet()->getStyle("V".$filc.":AK".$filc)->getNumberFormat()->setFormatCode('[Black][>=0]"$"* #,##0.00;[Red][<0]("$"* #,##0.00);"$"* #,##0.00');
        //$spreadsheet->getActiveSheet()->getCell('D'. $filc.":L".$filc)->getCalculatedValue();
        //$spreadsheet->getActiveSheet()->getCell('V'. $filc.":AE".$filc)->getCalculatedValue();
        // $spreadsheet->getActiveSheet()->getCell('X'. $filc)->getCalculatedValue();

        // $spreadsheet->getActiveSheet()->getCell('Y'. $filc)->getCalculatedValue();
        // $spreadsheet->getActiveSheet()->getCell('Z'. $filc)->getCalculatedValue();
        // $spreadsheet->getActiveSheet()->getCell('AA'. $filc)->getCalculatedValue();
        // $spreadsheet->getActiveSheet()->getCell('AB'. $filc)->getCalculatedValue();
        // $spreadsheet->getActiveSheet()->getCell('AC'. $filc)->getCalculatedValue();
        // $spreadsheet->getActiveSheet()->getCell('AD'. $filc)->getCalculatedValue();

        $spreadsheet->getActiveSheet()->getStyle("A".$filc.":AK".$filc)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        //BORDES
        $spreadsheet->getActiveSheet()->getStyle("A".$filc.":AK".$filc)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DASHED);
        
        //$spreadsheet->getActiveSheet()->getCell('W'. $filc)->getStyle()->setQuotePrefix(true);
        // $spreadsheet->getActiveSheet()->getCell('X'. $filc)->getStyle()->setQuotePrefix(true);
        // $spreadsheet->getActiveSheet()->getCell('Y'. $filc)->getStyle()->setQuotePrefix(true);
        // $spreadsheet->getActiveSheet()->getCell('Z'. $filc)->getStyle()->setQuotePrefix(true);
        // $spreadsheet->getActiveSheet()->getCell('AA'. $filc)->getStyle()->setQuotePrefix(true);
        // $spreadsheet->getActiveSheet()->getCell('AB'. $filc)->getStyle()->setQuotePrefix(true);
        // $spreadsheet->getActiveSheet()->getCell('AC'. $filc)->getStyle()->setQuotePrefix(true);
        // $spreadsheet->getActiveSheet()->getCell('AD'. $filc)->getStyle()->setQuotePrefix(true);   

        $filc  = $filc + 1;
        $filcs = $filcs + 1;
    }
    $filc1 = $filc+1;
    $filcu = $filc-1;

    $spreadsheet->getActiveSheet()->getRowDimension($filc)->setRowHeight(7);

    $spreadsheet->getActiveSheet()
    ->setCellValue('A'. $filc1,  "TOTALES")


    ->setCellValue('M'. $filc1,  "=SUM(M4:M".$filcu.")")
    ->setCellValue('N'. $filc1,  "=SUM(N4:N".$filcu.")")
    ->setCellValue('O'. $filc1,  "=SUM(O4:O".$filcu.")")
    ->setCellValue('P'. $filc1,  "=SUM(P4:P".$filcu.")")
    ->setCellValue('Q'. $filc1,  "=SUM(Q4:Q".$filcu.")")
    ->setCellValue('R'. $filc1,  "=SUM(R4:R".$filcu.")")
    ->setCellValue('S'. $filc1,  "=SUM(S4:S".$filcu.")")
    ->setCellValue('T'. $filc1,  "=SUM(T4:T".$filcu.")")
    ->setCellValue('U'. $filc1,  "=SUM(U4:U".$filcu.")")
    ->setCellValue('V'. $filc1,  "=SUM(V4:V".$filcu.")")
    ->setCellValue('W'. $filc1,  "=SUM(W4:W".$filcu.")")
    ->setCellValue('X'. $filc1,  "=SUM(X4:X".$filcu.")")
    ->setCellValue('Y'. $filc1,  "=SUM(Y4:Y".$filcu.")" )
    ->setCellValue('Z'. $filc1,  "=SUM(Z4:Z".$filcu.")")
    ->setCellValue('AA'. $filc1, "=SUM(AA4:AA".$filcu.")")
    ->setCellValue('AB'. $filc1, "=SUM(AB4:AB".$filcu.")")
    ->setCellValue('AC'. $filc1, "=SUM(AC4:AC".$filcu.")")
    ->setCellValue('AD'. $filc1, "=SUM(AD4:AD".$filcu.")")
    ->setCellValue('AE'. $filc1, "=SUM(AE4:AE".$filcu.")")
    ->setCellValue('AF'. $filc1, "=SUM(AF4:AF".$filcu.")")
    ->setCellValue('AG'. $filc1, "=SUM(AG4:AG".$filcu.")")
    ->setCellValue('AH'. $filc1, "=SUM(AH4:AH".$filcu.")")
    ->setCellValue('AI'. $filc1, "=SUM(AI4:AI".$filcu.")")
    ->setCellValue('AJ'. $filc1, "=SUM(AJ4:AJ".$filcu.")")
    ->setCellValue('AK'. $filc1, "=SUM(AK4:AK".$filcu.")")
    ->setCellValue('AL'. $filc1, "=SUM(AL4:AL".$filcu.")")
    ->setCellValue('AM'. $filc1, "=SUM(AM4:AM".$filcu.")")
    ->setCellValue('AN'. $filc1, "=SUM(AN4:AN".$filcu.")")
    ->setCellValue('AO'. $filc1, "=SUM(AO4:AO".$filcu.")")
    ->mergeCells('A'.$filc.':AO'.$filc)
    ->mergeCells('A'.$filc1.':L'.$filc1);


    cellColor('A'.$filc1.':Ak'.$filc1,'EEECE1');
    $spreadsheet->getActiveSheet()->getStyle("D4:L".$filc)->getNumberFormat()->setFormatCode('[Black][>=0]"$"* #,##0.00;[Red][<0]("$"* #,##0.00);"$"* #,##0.00');
    $spreadsheet->getActiveSheet()->getStyle("M4:AA".$filc)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_00);
    $spreadsheet->getActiveSheet()->getStyle("AB4:AO".$filc)->getNumberFormat()->setFormatCode('[Black][>=0]"$"* #,##0.00;[Red][<0]("$"* #,##0.00);"$"* #,##0.00');
    // $spreadsheet->getActiveSheet()->getCell("D4:L".$filc)->getCalculatedValue();
    // $spreadsheet->getActiveSheet()->getCell("V4:AE".$filc)->getCalculatedValue();
    $spreadsheet->getActiveSheet()->getStyle("A".$filc1.":AO".$filc1)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
    $spreadsheet->getActiveSheet()->getStyle("A".$filc1.":AO".$filc1)->getFont()->setBold(true);

    $spreadsheet->getActiveSheet()->getStyle("D".$filc1.":L".$filc1)->getNumberFormat()->setFormatCode('[Black][>=0]"$"* #,##0.00;[Red][<0]("$"* #,##0.00);"$"* #,##0.00');
    $spreadsheet->getActiveSheet()->getStyle("M".$filc1.":AA".$filc1)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_00);
    $spreadsheet->getActiveSheet()->getStyle("AB".$filc1.":AO".$filc1)->getNumberFormat()->setFormatCode('[Black][>=0]"$"* #,##0.00;[Red][<0]("$"* #,##0.00);"$"* #,##0.00');
}
else
{
    $spreadsheet->setActiveSheetIndex(0);
    $nomarc = "ALIMENTACION CONTABILIDAD";
    $spreadsheet->getActiveSheet()
    ->setCellValue('A1', "ALIMENTACION CONTABILIDAD")
    ->setCellValue('A2', "EMPLEADO")
    ->setCellValue('B2', "FECHA")
    ->setCellValue('C2', "VALOR")       
    //MERGE FILAS
    ->mergeCells('A1:C1'); 
    cellColor('A2:C2','002060');
    

    $spreadsheet->getActiveSheet()->getStyle('A2:C2')
        ->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
   

    $spreadsheet->getActiveSheet()->getStyle('A2')->getAlignment()->setWrapText(true);

    $spreadsheet->getActiveSheet()->getStyle('A1:C2')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
    $spreadsheet->getActiveSheet()->getStyle('A1:C2')->getFont()->setBold(true);
    $spreadsheet->getActiveSheet()->getStyle('A1:C2')->getAlignment()->applyFromArray(array('horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER));
    $spreadsheet->getActiveSheet()->getStyle('A1:C2')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

    //echo date('H:i:s') , " Set column height" , EOL;
    $spreadsheet->getActiveSheet()->getRowDimension('1')->setRowHeight(60);
    $spreadsheet->getActiveSheet()->getRowDimension('2')->setRowHeight(12.75);

    $stmtDias = $conn->prepare($sqldias);
    $stmtDias->execute();
    $numdias = $stmtDias->rowCount();
    //echo $numpro;
    $hastacont = $numdias +3;
    $acum = $hastacont;
    $filc = 3;
    $filcs = 4;

    while ($data = $stmtDias->fetch(PDO::FETCH_ASSOC)) {
        $nombre = $data['nom'];
        $fec = $data['fec'];
        $ali = $data['alimentacion'];        
        
        $spreadsheet->getActiveSheet()
        ->setCellValue('A'. $filc, $nombre)
        ->setCellValue('B'. $filc, $fec)
        ->setCellValue('C'. $filc, $ali);

        $spreadsheet->getActiveSheet()->getStyle("C".$filc)->getNumberFormat()->setFormatCode('[Black][>=0]"$"* #,##0.00;[Red][<0]("$"* #,##0.00);"$"* #,##0.00');        

        $spreadsheet->getActiveSheet()->getStyle("A".$filc.":C".$filc)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        //BORDES
        $spreadsheet->getActiveSheet()->getStyle("A".$filc.":C".$filc)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DASHED); 
        $filc  = $filc + 1;
        $filcs = $filcs + 1;
    }
    $filc1 = $filc+1;
    $filcu = $filc-1;

    $spreadsheet->getActiveSheet()->getRowDimension($filc)->setRowHeight(7);

    $spreadsheet->getActiveSheet()
    ->setCellValue('A'. $filc1,  "TOTALES")
    ->setCellValue('B'. $filc1,  "=SUM(C3:C".$filcu.")")
    ->mergeCells('B'.$filc1.':C'.$filc1);
    cellColor('A'.$filc1.':C'.$filc1,'EEECE1');
    $spreadsheet->getActiveSheet()->getStyle("B".$filc1)->getNumberFormat()->setFormatCode('[Black][>=0]"$"* #,##0.00;[Red][<0]("$"* #,##0.00);"$"* #,##0.00');
   
    // $spreadsheet->getActiveSheet()->getCell("D4:L".$filc)->getCalculatedValue();
    // $spreadsheet->getActiveSheet()->getCell("V4:AE".$filc)->getCalculatedValue();
    $spreadsheet->getActiveSheet()->getStyle("A".$filc1.":C".$filc1)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
    $spreadsheet->getActiveSheet()->getStyle("C".$filc1)->getFont()->setBold(true);
}

$spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(45);
$spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(15);
$spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(15);

if($tip==1)
{
    $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(15);
    $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(15);
    $spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(15);
    $spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth(15);
    $spreadsheet->getActiveSheet()->getColumnDimension('H')->setWidth(15);
    $spreadsheet->getActiveSheet()->getColumnDimension('I')->setWidth(15);
    $spreadsheet->getActiveSheet()->getColumnDimension('J')->setWidth(15);
    $spreadsheet->getActiveSheet()->getColumnDimension('K')->setWidth(15);
    $spreadsheet->getActiveSheet()->getColumnDimension('L')->setWidth(15);
    $spreadsheet->getActiveSheet()->getColumnDimension('M')->setWidth(15);
    $spreadsheet->getActiveSheet()->getColumnDimension('N')->setWidth(15);
    $spreadsheet->getActiveSheet()->getColumnDimension('O')->setWidth(15);
    $spreadsheet->getActiveSheet()->getColumnDimension('P')->setWidth(15);
    $spreadsheet->getActiveSheet()->getColumnDimension('Q')->setWidth(15);
    $spreadsheet->getActiveSheet()->getColumnDimension('R')->setWidth(15);
    $spreadsheet->getActiveSheet()->getColumnDimension('S')->setWidth(15);
    $spreadsheet->getActiveSheet()->getColumnDimension('T')->setWidth(15);
    $spreadsheet->getActiveSheet()->getColumnDimension('U')->setWidth(15);
    $spreadsheet->getActiveSheet()->getColumnDimension('V')->setWidth(15);
    $spreadsheet->getActiveSheet()->getColumnDimension('W')->setWidth(15);
    $spreadsheet->getActiveSheet()->getColumnDimension('X')->setWidth(15);
    $spreadsheet->getActiveSheet()->getColumnDimension('Y')->setWidth(15);
    $spreadsheet->getActiveSheet()->getColumnDimension('Z')->setWidth(15);
    $spreadsheet->getActiveSheet()->getColumnDimension('AA')->setWidth(15);
    $spreadsheet->getActiveSheet()->getColumnDimension('AB')->setWidth(15);
    $spreadsheet->getActiveSheet()->getColumnDimension('AC')->setWidth(15);
    $spreadsheet->getActiveSheet()->getColumnDimension('AD')->setWidth(15);
    $spreadsheet->getActiveSheet()->getColumnDimension('AE')->setWidth(15);
    $spreadsheet->getActiveSheet()->getColumnDimension('AF')->setWidth(15);
    $spreadsheet->getActiveSheet()->getColumnDimension('AG')->setWidth(15);
    $spreadsheet->getActiveSheet()->getColumnDimension('AH')->setWidth(15);
    $spreadsheet->getActiveSheet()->getColumnDimension('AI')->setWidth(15);
    $spreadsheet->getActiveSheet()->getColumnDimension('AJ')->setWidth(15);
    $spreadsheet->getActiveSheet()->getColumnDimension('AK')->setWidth(15);
    $spreadsheet->getActiveSheet()->getColumnDimension('AL')->setWidth(15);
    $spreadsheet->getActiveSheet()->getColumnDimension('AN')->setWidth(15);
    $spreadsheet->getActiveSheet()->getColumnDimension('AN')->setWidth(15);
    $spreadsheet->getActiveSheet()->getColumnDimension('AO')->setWidth(15);
}
//titulos de la hoja
$spreadsheet->getActiveSheet()->setTitle($nomarc);
//zoom de la pagina


$spreadsheet->getActiveSheet()->getSheetView()->setZoomScale(90);
$spreadsheet->setActiveSheetIndex(0);
//$spreadsheet->getActiveSheet()->setSheetState(PHPExcel_Worksheet::SHEETSTATE_HIDDEN);

$objDrawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
$objDrawing->setName('Potenco logo');
$objDrawing->setDescription('Potenco logo');
$objDrawing->setPath('../../../dist/img/Potenco-logo-mini.png');
$objDrawing->setHeight(60);
$objDrawing->setCoordinates('A1');
$objDrawing->setOffsetX(10);
$objDrawing->setOffsetY(10);
$objDrawing->setWorksheet($spreadsheet->getActiveSheet());

$callStartTime = microtime(true);
$archivo = $titulo.'.xlsx';
$archivo =  date('Ymd').' '.$archivo;

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

$writer = new Xlsx($spreadsheet);

// $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
// $writer->save($archivo);
$writer->save('php://output');