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

$id = $_GET['id'];
$id =  decrypt($id,'p4v4sAp');
//CONSULTA DATOS LIQUIDACIONES
$sqlLiquidacion = "SELECT u.usu_nombre nom, u.usu_apellido ape, u.usu_correo cor, l.liq_fecha fec, l.liq_inicio ini, l.liq_fin fin, l.liq_tipo tip, l.liq_hr_mes hrmes, liq_salario vrhor, o.obr_nombre nomobr, o.obr_cencos cenco, liq_consecutivo cons, o.obr_clave_int obr, liq_vr_maquina vhma, liq_codigo codigo FROM tbl_liquidar l join tbl_usuarios u on u.usu_clave_int = l.usu_clave_int left outer join tbl_obras o on o.obr_clave_int = l.obr_clave_int WHERE l.liq_clave_int = '".$id."'";

$stmtLiquidacion = $conn->prepare($sqlLiquidacion);
$stmtLiquidacion->execute();
$datl = $stmtLiquidacion->fetch(PDO::FETCH_ASSOC);

$nom    = $datl['nom'];
$ape    = $datl['ape'];
$cor    = $datl['cor'];
$fec    = $datl['fec'];
$ini    = $datl['ini'];
$fin    = $datl['fin'];
$hrmes  = $datl['hrmes'];
$vrhor  = $datl['vrhor'];
$obr    = $datl['obr'];
$nomobr = $datl['nomobr'];
$cenco  = $datl['cenco'];
$tip    = $datl['tip'];
$cons   = $datl['cons'];
$vhma   = $datl['vhma'];

$hirn   = $datl['hirn'];
$hfrn   = $datl['hfrn'];
//$hirn = str_replace(".",",",$hirn);
//$hfrn = str_replace(".",",",$hfrn);


$salariobase = $hrmes*$vrhor;

if($tip==1)
{
    $archivo = 'Proforma Horas Extras x Empleado.pdf';//Cambiar por nuevo campo en BD para fecha cierre de la intervencion
    $titulo  = "Proforma Horas Extras x Empleado";
    $sqldias = "SELECT lid_fecha fec, lid_hi_man hiam, lid_hf_man hfam , lid_hi_tar hipm,  lid_hf_tar hfpm,lid_horas, lid_hedo, lid_hdf, lid_hedf, lid_heno, lid_henf, lid_rn, lid_hnf, lid_rd, lid_horario,lid_alimentacion,lid_val_alimentacion, lid_compensado, lid_remunerado, lid_observacion, diaHabil(lid_fecha) fes, WEEKDAY(lid_fecha) dia from tbl_liquidar_dias where liq_clave_int = '".$id."'";

    $sqlhoras = "select sum(lid_hedo) tothedo,sum(lid_hdf) tothdf,sum(lid_hedf) tothedf,sum(lid_heno) totheno,sum(lid_henf) tothenf,sum(lid_henf) tothenf,sum(lid_rn) totrn, sum(lid_hnf) tothnf, sum(lid_rd) totrd, sum(lid_horas) totalhoras from tbl_liquidar_dias where liq_clave_int = '".$id."'";
}
else
{
    $archivo = 'Proforma Horas Extras x Obra.pdf';
    $titulo  = "Proforma Horas Extras x Obra";
    $sqldias = "SELECT lid_fecha fec, lid_hi_man hiam, lid_hf_man hfam , lid_hi_tar hipm,  lid_hf_tar hfpm,lid_horas, lid_hedo, lid_hdf, lid_hedf, lid_heno, lid_henf, lid_rn, lid_hnf, lid_rd, lid_horario,lid_alimentacion,lid_val_alimentacion, lid_compensado, lid_remunerado,lid_observacion, diaHabil(lid_fecha) fes, WEEKDAY(lid_fecha) dia from tbl_liquidar_dias_obra where liq_clave_int = '".$id."'";

    $sqlhoras = "select sum(lid_hedo) tothedo,sum(lid_hdf) tothdf,sum(lid_hedf) tothedf,sum(lid_heno) totheno,sum(lid_henf) tothenf,sum(lid_rn) totrn, sum(lid_hnf) tothnf, sum(lid_rd) totrd, sum(lid_horas) totalhoras from tbl_liquidar_dias_obra where liq_clave_int = '".$id."'";
}

$stmtHoras = $conn->prepare($sqlhoras);
$stmtHoras->execute();
$dath = $stmtHoras->fetch(PDO::FETCH_ASSOC);
$totalhedo  = $dath['tothedo'];
$totalhdf   = $dath['tothdf'];
$totalhedf  = $dath['tothedf'];
$totalheno  = $dath['totheno'];
$totalhenf  = $dath['tothenf'];
$totalrn    = $dath['totrn'];
$totalhnf   = $dath['tothnf'];
$totalrd    = $dath['totrd'];

$totalhoraslaboradas = $dath['totalhoras']; //TOTAL HORAS LABORADAS
$totalhem = $totalhoraslaboradas - $hrmes; // TOTAL EXTRAS MAQUINA
$totalhem = ($totalhem<0)?0:$totalhem;
$totalmaquina = $vhma * $totalhem; // VALOR TOTAL HORAS EXTRAS MAQUINA

if($tip==1){ $totalhem = 0; } // SI TIPO ES NOMINA ESTE VALOR ES CERO

$toh = new General();

//CALCULO TOTALES INICI
$hedo = $toh->getPorcentaje('HEDO');
$hdf  = $toh->getPorcentaje('HDF');
$hedf = $toh->getPorcentaje('HEDF');
$heno = $toh->getPorcentaje('HENO');
$henf = $toh->getPorcentaje('HENF');
$rn   = $toh->getPorcentaje('RN');
$hnf  = $toh->getPorcentaje('HNF');
$rd   = $toh->getPorcentaje('RD');

$tithedo = $hedo['hor_descripcion'];  
$codhedo = $hedo['hor_codigo'];  
$porhedo = $hedo['hor_porcentaje'];        
$vhhedo  = $porhedo * $vrhor;
$tothedo = $vhhedo*$totalhedo;

$tithdf = $hdf['hor_descripcion'];  
$codhdf = $hdf['hor_codigo'];  
$porhdf = $hdf['hor_porcentaje'];        
$vhhdf  = $porhdf * $vrhor;
$tothdf = $vhhdf*$totalhdf;

$tithedf = $hedf['hor_descripcion'];  
$codhedf = $hedf['hor_codigo'];  
$porhedf = $hedf['hor_porcentaje'];        
$vhhedf  = $porhedf * $vrhor;
$tothedf = $vhhedf*$totalhedf;

$titheno = $heno['hor_descripcion'];  
$codheno = $heno['hor_codigo'];  
$porheno = $heno['hor_porcentaje'];        
$vhheno  = $porheno * $vrhor;
$totheno = $vhheno*$totalheno;

$tithenf = $henf['hor_descripcion']; 
$codhenf = $henf['hor_codigo'];   
$porhenf = $henf['hor_porcentaje'];        
$vhhenf  = $porhenf * $vrhor;
$tothenf = $vhhenf*$totalhenf;

$titrn = $rn['hor_descripcion'];  
$codrn = $rn['hor_codigo'];  
$porrn = $rn['hor_porcentaje'];        
$vhrn  = $porrn * $vrhor;
$totrn = $vhrn*$totalrn;

$tithnf = $hnf['hor_descripcion']; 
$codhnf = $hnf['hor_codigo'];   
$porhnf = $hnf['hor_porcentaje'];        
$vhhnf  = $porhnf * $vrhor;
$tothnf = $vhhnf*$totalhnf;

$titrd  = $rd['hor_descripcion'];  
$codrd = $rd['hor_codigo'];  
$porrd  = $rd['hor_porcentaje'];        
$vhrd   = $porrd * $vrhor;
$totrd  = $vhrd*$totalrd;

$totalhoras = $totalhedo + $totalhdf + $totalhedf + $totalheno + $totalhenf + $totalrn + $totalhnf + $totalrd + $totalhem;
$total =  $tothedo + $tothdf + $tothedf + $totheno + $tothenf + $totrn + $tothnf + $totrd + $totalmaquina;

$dscto = 0;
$iva = 0;
$rtefuente = 0;
$rteiva = 0;
$neto = $total + $iva  - $dscto - $rtefuente - $rteiva;


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
//require_once '../../../clases/PHPExcel.php';
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
// Create new PHPExcel object
//echo date('H:i:s') , " Crear nuevo objeto PHPExcel" , EOL;
//$spreadsheet = new Spreadsheet();
$spreadsheet = new Spreadsheet();
// Set document properties
//echo date('H:i:s') , " Establecer propiedades" , EOL;
$spreadsheet->getProperties()->setCreator("PAVAS SAS")
    ->setLastModifiedBy("PAVAS SAS")
    ->setTitle($titulo)
    ->setSubject("Informe Liquidador")
    ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
    ->setKeywords("office 2021 openxml php")
    ->setCategory("Informes");

$spreadsheet->setActiveSheetIndex(0);

$nomarc = "LIQUIDADORNOMINA";
$spreadsheet->getActiveSheet()
->setCellValue('H1', "LIQUIDADOR DE NOMINA")
->setCellValue('H2', "FECHA")
->setCellValue('I2', "MAÑANA")
//->setCellValue('J2', "")
//->setCellValue('K2', "")
->setCellValue('L2', "TOTAL HORAS")
->setCellValue('M2', "TARDE")
//->setCellValue('N2', "")
//->setCellValue('O2', ")
->setCellValue('P2', "TOTAL HORAS")
->setCellValue('Q2', "HORAS DIA")
->setCellValue('R2', "HORARIO")
->setCellValue('S2', "COMPENSADO")
->setCellValue('T2', "PAGADO Y COMPENSADO")
->setCellValue('U2', "PERMISO REMUNERADO")
->setCellValue('V2', "OBSERVACION")
->setCellValue('W2', "HEDO")
->setCellValue('X2', "HDF")
->setCellValue('Y2', "HEDF")
->setCellValue('Z2', "HENO")
->setCellValue('AA2', "HENF")
->setCellValue('AB2', "RN")
->setCellValue('AC2', "HNF")
->setCellValue('AD2', "RD")

->setCellValue('H3', "Día/Mes/Año")
->setCellValue('I3', "Desde")
//->setCellValue('J3', "")
->setCellValue('K3', "Hasta")
->setCellValue('L3', "#")
->setCellValue('M3', "Desde")
//->setCellValue('N3', "")
->setCellValue('O3', "Hasta")
->setCellValue('P3', "#")
->setCellValue('Q3', "#")
//->setCellValue('R3', "")
//->setCellValue('S3', "")
//->setCellValue('T3', "")
//->setCellValue('U3', "")
//->setCellValue('V3', "")
//->setCellValue('W3', $codhedo)
//->setCellValue('X3', $codhdf)
//->setCellValue('Y3', $codhedf)
//->setCellValue('Z3', $codheno)
//->setCellValue('AA3', $codhenf)
//->setCellValue('AB3', $codrn)
//->setCellValue('AC3', $codhnf)
//->setCellValue('AD3', $codrd)

//MERGE COLUMNAS
->mergeCells('H2:K2')
->mergeCells('M2:O2')

//MERGE FILAS
->mergeCells('R2:R3')
->mergeCells('S2:S3')
->mergeCells('T2:T3')
->mergeCells('U2:U3')
->mergeCells('V2:V3')
->mergeCells('H1:AD1');   
cellColor('H2:AD3','002060');

$spreadsheet->getActiveSheet()->getStyle('H2:AD3')
    ->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);

$spreadsheet->getActiveSheet()->getStyle('L2')->getAlignment()->setWrapText(true);
$spreadsheet->getActiveSheet()->getStyle('P2')->getAlignment()->setWrapText(true);
$spreadsheet->getActiveSheet()->getStyle('T2')->getAlignment()->setWrapText(true);
$spreadsheet->getActiveSheet()->getStyle('U2')->getAlignment()->setWrapText(true);


$spreadsheet->getActiveSheet()->getCell('W3')->setValueExplicit($codhedo,\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
$spreadsheet->getActiveSheet()->getCell('X3')->setValueExplicit($codhdf,\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
$spreadsheet->getActiveSheet()->getCell('Y3')->setValueExplicit($codhedf,\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
$spreadsheet->getActiveSheet()->getCell('Z3')->setValueExplicit($codheno,\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
$spreadsheet->getActiveSheet()->getCell('AA3')->setValueExplicit($codhenf,\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
$spreadsheet->getActiveSheet()->getCell('AB3')->setValueExplicit($codrn,\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
$spreadsheet->getActiveSheet()->getCell('AC3')->setValueExplicit($codhnf,\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
$spreadsheet->getActiveSheet()->getCell('AD3')->setValueExplicit($codrd,\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

//cellColor('B2:'.$lf.'2', '17a2b8 ');

$spreadsheet->getActiveSheet()->getStyle('H1:AD3')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
$spreadsheet->getActiveSheet()->getStyle('H1:AD3')->getFont()->setBold(true);
$spreadsheet->getActiveSheet()->getStyle('H1:AD3')->getAlignment()->applyFromArray(array('horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER));
$spreadsheet->getActiveSheet()->getStyle('H1:AD3')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

//echo date('H:i:s') , " Set column height" , EOL;
$spreadsheet->getActiveSheet()->getRowDimension('1')->setRowHeight(60);
$spreadsheet->getActiveSheet()->getRowDimension('2')->setRowHeight(12.75);
$spreadsheet->getActiveSheet()->getRowDimension('3')->setRowHeight(12.75);


//CONDICIONALES
$conditional1 = new \PhpOffice\PhpSpreadsheet\Style\Conditional();
$conditional1->setConditionType(\PhpOffice\PhpSpreadsheet\Style\Conditional::CONDITION_CELLIS);
$conditional1->setOperatorType(\PhpOffice\PhpSpreadsheet\Style\Conditional::OPERATOR_EQUAL);
$conditional1->addCondition('SI');
$conditional1->getStyle()->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED);
$conditional1->getStyle()->getFont()->setBold(true);
$conditionalStyles[] = $conditional1;

$stmtDias = $conn->prepare($sqldias);
$stmtDias->execute();
$numdias = $stmtDias->rowCount();
//echo $numpro;
$hastacont = $numdias +4;
$acum = $hastacont;
$filc = 4;
$filcs = 5;
$lineaWG = "=";  $lineaW  = "=";
$lineaXG = "=";  $lineaX  = "=";
$lineaYG = "=";  $lineaY  = "=";
$lineaZG = "=";  $lineaZ  = "=";
$lineaAAG = "="; $lineaAA  = "=";
$lineaABG = "="; $lineaAB  = "=";
$lineaACG = "="; $lineaAC  = "=";
$lineaADG = "="; $lineaAD  = "=";

while ($data = $stmtDias->fetch(PDO::FETCH_ASSOC)) {
    $idfec = $data['id'];
    $dia  = $data['dia'];
    $fec  = $data['fec'];
    $tothoras = $data['lid_horas'];
    $nmes = date("F", strtotime($fec));
    $ndia = date("l", strtotime($fec));
    $numd = date("d", strtotime($fec));
    $anod = date("Y", strtotime($fec));
    $fechatext = $ndia.", ". $numd." de ".$nmes." de ".$anod;
    $fes = $data['fes']; $bgfes = ($fes==1)?"bg-secondary":""; //dia festivo
    $titfes = ($fes==1)?"Dia Festivo":"";

        //DIA INICIAL Y FINAL
    $diainicial = $numd;
    $diafinal = date("t",strtotime($fec));
    $horario = $data['lid_horario'];                   
    $aini = $data['hiam'];
    $afin = $data['hfam'];
    $pini = $data['hipm'];
    $pfin = $data['hfpm'];

    $tsaini = $toh->time_to_sec($aini);
    $tsafin = $toh->time_to_sec($afin);
    $tspini = $toh->time_to_sec($pini);
    $tspfin = $toh->time_to_sec($pfin);

    $totalam = $toh->fnHoras($fec,$aini,$afin);
    $totalpm = $toh->fnHoras($fec,$pini,$pfin);

    $aminit  = ($tothoras<=0)?'':strtotime($aini); // ($aini!="")? date("g:i:s", strtotime($aini)): $aini;
    $amfint  = ($tothoras<=0)?'':strtotime($afin); //($afin!="")? date("g:i:s", strtotime($afin)): $afin;
    $pminit  = ($tothoras<=0)?'':strtotime($pini); //($pini!="")? date("g:i:s", strtotime($pini)): $pini;
    $pmfint  = ($tothoras<=0)?'':strtotime($pfin); //($pfin!="")? date("g:i:s", strtotime($pfin)): $pfin;

    $ali = $data['lid_alimentacion'];
    $valorali = $data['lid_val_alimentacion'];
    $icali = "";
    if($ali==1)
    {
        //buscar imagen de alimentacion
        $icali = "";
    }

    $HEDO = 0; // HORAS EXTRAS DIURNAS ORDINA
    $HDF  = 0;
    $HEDF = 0;
    $HENO = 0;
    $HENF = 0;
    $RN   = 0;
    $HDF  = 0;
    $RD   = 0;
    
    
    // pagado y compensado = T5 => "SI" o vacio
    $T5 = "";//
    // compensado = S5 = >Si o Ambos o vacion
    $S5  = $data['lid_compensado'];
    // PERMISO REMUNERADO = U5=> SI
    $U5  = $data['lid_remunerado'];

    $obs = $data['lid_observacion'];
    
    $HENF = $data['lid_henf'];
    $HENO = $data['lid_heno'];
    $HEDF = $data['lid_hedf'];
    $HNF  = $data['lid_hnf'];
    $RD   = $data['lid_rd'];
    $RN   = $data['lid_rn'];
    $HDF  = $data['lid_hdf'];
    $HEDO = $data['lid_hedo'];       
    
    if($fes==1)
    {
        cellColor('H'.$filc.':AD'.$filc,'A5A5A5');
    }

    $W = '=IF(R'.$filc.'>Q'.$filc.',IF(U'.$filc.'="SI",0,Q'.$filc.'-R'.$filc.'),Q'.$filc.'-R'.$filc.')-X'.$filc.'-Z'.$filc.'-AA'.$filc.'-Y'.$filc.'-AC'.$filc.'-AD'.$filc;

    //$W = `=IF(R$filc>Q$filc,IF(U$filc="SI",0,Q$filc-R$filc),Q$filc-R$filc)-X$filc-Z$filc-AA$filc-Y$filc-AC$filc-AD$filc`;

    $spreadsheet->getActiveSheet()
    // ->setCellValue('B' . $filc, $codigo)
    ->setCellValue('F' . $filc, $fes)
    ->setCellValue('G' . $filc, '=WEEKDAY(H'.$filc.', 1)')
    //->setCellValue('H' . $filc, strtotime($fec))
    //->setCellValue('I' . $filc, $aminit)
    ->setCellValue('J' . $filc, "a")
    //->setCellValue('K' . $filc, $amfint)
    ->setCellValue('L' . $filc, "=(K".$filc."-I".$filc."+(I".$filc.">K".$filc."))*24")
    //->setCellValue('M' . $filc, $pminit)
    ->setCellValue('N' . $filc, "a")
    //->setCellValue('O' . $filc, $pmfint)
    ->setCellValue('P' . $filc, "=(O".$filc."-M".$filc."+(M".$filc.">O".$filc."))*24")
    ->setCellValue('Q' . $filc, "=L".$filc."+P".$filc)
    ->setCellValue('R' . $filc, $horario)
    ->setCellValue('S' . $filc, $S5)
    ->setCellValue('T' . $filc, $T5)
    ->setCellValue('U' . $filc, $U5)
    ->setCellValue('V' . $filc, $obs)    
    ->setCellValue('W' . $filc, $W)
    ->setCellValue('X' . $filc, '=IF(O(G'.$filc.'=1,F'.$filc.'=1),Q'.$filc.',0)-Y'.$filc.'-AA'.$filc.'+IF(Y(O(G'.$filcs.'=1,F'.$filcs.'=1),AA'.$filc.'>0,Y(G'.$filc.'<>1,F'.$filc.'<>1)),O'.$filc.'*24,0)-IF(Y(O(G'.$filc.'=1,F'.$filc.'=1),S'.$filc.'="SI",T'.$filc.'=""),IF(Q'.$filc.'<=8,Q'.$filc.',"8"),0)-IF(Y(F'.$filcs.'<>1,G'.$filcs.'<>1,O(F'.$filc.'=1,G'.$filc.'=1),O'.$filc.'<(6/24)),O'.$filc.'*24,0)-AC'.$filc)

    ->setCellValue('Y' . $filc, '=IF(Y(O(G'.$filc.'=1,F'.$filc.'=1),Q'.$filc.'>8),Q'.$filc.'-8,0)-AA'.$filc.'+IF(Y(O(G'.$filcs.'=1,F'.$filcs.'=1),Y(G'.$filc.'<>1,F'.$filc.'<>1),AA'.$filc.'>0),O'.$filc.'*24,0)-IF(Y(F'.$filcs.'<>1,G'.$filcs.'<>1,O(F'.$filc.'=1,G'.$filc.'=1),O'.$filc.'<(6/24)),O'.$filc.'*24,0)')

    ->setCellValue('Z' . $filc, '=IF(O(G'.$filc.'=1,F'.$filc.'=1),0,((IF(Y(Q'.$filc.'>R'.$filc.',O'.$filc.'>'.$hirn.'),ABS(M'.$filc.'+(R'.$filc.'/24)-(O'.$filc.')),0)+IF(Y(Q'.$filc.'>R'.$filc.',O'.$filc.'<'.$hfrn.',L'.$filc.'=0),ABS(M'.$filc.'-(1+O'.$filc.')+(R'.$filc.'/24)),0))*24)+((IF(Y(Q'.$filc.'>R'.$filc.',O'.$filc.'<'.$hfrn.',O'.$filc.'<>0,L'.$filc.'<>0),O'.$filc.'+0.04166666666666666666666666666667,0))*24)-AA'.$filc.'+IF(Y(I'.$filc.'<'.$hfrn.',I'.$filc.'<>0,Q'.$filc.'>R'.$filc.'),IF((('.$hfrn.'-I'.$filc.')*24)>(Q'.$filc.'-R'.$filc.'),Q'.$filc.'-R'.$filc.',('.$hfrn.'-I'.$filc.')*24)))+IF(Y(F'.$filcs.'<>1,G'.$filcs.'<>1,O(F'.$filc.'=1,G'.$filc.'=1),O'.$filc.'<(6/24)),O'.$filc.'*24,0)')

     ->setCellValue('AA' . $filc, '=IF(O(G'.$filc.'=1,F'.$filc.'=1),((IF(Y(Q'.$filc.'>8,O'.$filc.'>'.$hirn.'),O'.$filc.'-'.$hirn.',0)+IF(Y(Q'.$filc.'>8,O'.$filc.'<'.$hfrn.',L'.$filc.'=0),ABS(M'.$filc.'-(1+O'.$filc.')+(8/24)),0))*24)+((IF(Y(Q'.$filc.'>8,O'.$filc.'<'.$hfrn.',O'.$filc.'<>0,L'.$filc.'<>0),O'.$filc.'+0.04166666666666666666666666666667,0))*24),0)+IF(Y(O(G'.$filcs.'=1,F'.$filcs.'=1),Y(F'.$filc.'<>1,G'.$filc.'<>1),O'.$filc.'<(6/24)),O'.$filc.'*24,0)-IF(Y(F'.$filcs.'<>1,G'.$filcs.'<>1,O(F'.$filc.'=1,G'.$filc.'=1),O'.$filc.'<(6/24)),O'.$filc.'*24,0)')

    ->setCellValue('AB' . $filc,'=IF(O(G'.$filc.'=1,F'.$filc.'=1),0,((IF(Y(L'.$filc.'=0,M'.$filc.'>=0.54166666666666666666666666666667,O'.$filc.'>'.$hirn.',Q'.$filc.'<=R'.$filc.'),M'.$filc.'+(R'.$filc.'/24)-(('.$hirn.'*24)/24),0)))*24+((IF(Y(L'.$filc.'=0,M'.$filc.'>0.54166666666666666666666666666667,O'.$filc.'<'.$hfrn.'),M'.$filc.'+((P'.$filc.'/24)-1)+(0.04166666666666666666666666666667),0))*24)-IF(Y(P'.$filc.'>8,L'.$filc.'=0,M'.$filc.'>0.54166666666666666666666666666667,O'.$filc.'<'.$hfrn.'),(P'.$filc.'-R'.$filc.'),0)+IF(Y(I'.$filc.'<'.$hfrn.',I'.$filc.'<>0,Q'.$filc.'<=R'.$filc.'),('.$hfrn.'-I'.$filc.'),0)*24)+IF(Y(I'.$filc.'<(6/24),Q'.$filc.'>R'.$filc.',('.$hfrn.'-I'.$filc.')>(Q'.$filc.'/24-R'.$filc.'/24),I'.$filc.'<>0),('.$hfrn.'-I'.$filc.')-(Q'.$filc.'/24-R'.$filc.'/24),0)*24' )

    ->setCellValue('AC' . $filc, '=((IF(Y(O(F'.$filc.'=1,G'.$filc.'=1),L'.$filc.'=0,M'.$filc.'>0.54166666666666666666666666666667,O'.$filc.'>'.$hirn.'),M'.$filc.'+(8/24)-(('.$hirn.'*24)/24),0)*24))+((IF(Y(O(F'.$filc.'=1,G'.$filc.'=1),L'.$filc.'=0,M'.$filc.'>0.54166666666666666666666666666667,O'.$filc.'<'.$hfrn.'),M'.$filc.'+((P'.$filc.'/24)-1)+(0.04166666666666666666666666666667),0))*24)-IF(Y(O(F'.$filc.'=1,G'.$filc.'=1),P'.$filc.'>8,L'.$filc.'=0,M'.$filc.'>0.54166666666666666666666666666667,O'.$filc.'<'.$hfrn.'),(P'.$filc.'-8),0)+IF(Y(O(F'.$filc.'=1,G'.$filc.'=1),I'.$filc.'<'.$hfrn.',I'.$filc.'<>0),('.$hfrn.'-I'.$filc.')*24,0)')

    ->setCellValue('AD' . $filc, '=IF(T'.$filc.'="SI",0,IF(Y(O(G'.$filc.'=1,F'.$filc.'=1),O(S'.$filc.'="SI",S'.$filc.'="Ambos")),IF(Q'.$filc.'<=8,Q'.$filc.',8),0))')
    //->setCellValue('N' . $filc, "=L".$filc."-M".$filc
    ;

    //echo '=IF(O(G'.$filc.'=1,F'.$filc.'=1),0,((IF(Y(Q'.$filc.'>R'.$filc.',O'.$filc.'>'.$hirn.'),ABS(M'.$filc.'+(R'.$filc.'/24)-(O'.$filc.')),0)+IF(Y(Q'.$filc.'>R'.$filc.',O'.$filc.'<'.$hfrn.',L'.$filc.'=0),ABS(M'.$filc.'-(1+O'.$filc.')+(R'.$filc.'/24)),0))*24)+((IF(Y(Q'.$filc.'>R'.$filc.',O'.$filc.'<'.$hfrn.',O'.$filc.'<>0,L'.$filc.'<>0),O'.$filc.'+0.04166666666666666666666666666667,0))*24)-AA'.$filc.'+IF(Y(I'.$filc.'<'.$hfrn.',I'.$filc.'<>0,Q'.$filc.'>R'.$filc.'),IF((('.$hfrn.'-I'.$filc.')*24)>(Q'.$filc.'-R'.$filc.'),Q'.$filc.'-R'.$filc.',('.$hfrn.'-I'.$filc.')*24)))+IF(Y(F'.$filcs.'<>1,G'.$filcs.'<>1,O(F'.$filc.'=1,G'.$filc.'=1),O'.$filc.'<(6/24)),O'.$filc.'*24,0)';

    $spreadsheet->getActiveSheet()->getCell('W'. $filc)->getCalculatedValue();
    $spreadsheet->getActiveSheet()->getCell('X'. $filc)->getCalculatedValue();
    $spreadsheet->getActiveSheet()->getCell('Y'. $filc)->getCalculatedValue();
    $spreadsheet->getActiveSheet()->getCell('Z'. $filc)->getCalculatedValue();
    $spreadsheet->getActiveSheet()->getCell('AA'. $filc)->getCalculatedValue();
    $spreadsheet->getActiveSheet()->getCell('AB'. $filc)->getCalculatedValue();
    $spreadsheet->getActiveSheet()->getCell('AC'. $filc)->getCalculatedValue();
    $spreadsheet->getActiveSheet()->getCell('AD'. $filc)->getCalculatedValue();

    
    //$spreadsheet->getActiveSheet()->getCell('W'. $filc)->getStyle()->setQuotePrefix(true);
    // $spreadsheet->getActiveSheet()->getCell('X'. $filc)->getStyle()->setQuotePrefix(true);
    // $spreadsheet->getActiveSheet()->getCell('Y'. $filc)->getStyle()->setQuotePrefix(true);
    // $spreadsheet->getActiveSheet()->getCell('Z'. $filc)->getStyle()->setQuotePrefix(true);
    // $spreadsheet->getActiveSheet()->getCell('AA'. $filc)->getStyle()->setQuotePrefix(true);
    // $spreadsheet->getActiveSheet()->getCell('AB'. $filc)->getStyle()->setQuotePrefix(true);
    // $spreadsheet->getActiveSheet()->getCell('AC'. $filc)->getStyle()->setQuotePrefix(true);
    // $spreadsheet->getActiveSheet()->getCell('AD'. $filc)->getStyle()->setQuotePrefix(true);
    
    $spreadsheet->getActiveSheet()->getCell('G' . $filc)->getCalculatedValue();
   

    //CONDICIONALES COLUMNA COMPENSADO Y PERMISOS REMUNERADO
    $conditionalStyles = $spreadsheet->getActiveSheet()->getStyle('S' . $filc)->getConditionalStyles();
    $spreadsheet->getActiveSheet()->getStyle('S' . $filc)->setConditionalStyles($conditionalStyles);

    $conditionalStyles = $spreadsheet->getActiveSheet()->getStyle('U' . $filc)->getConditionalStyles();
    $spreadsheet->getActiveSheet()->getStyle('U' . $filc)->setConditionalStyles($conditionalStyles);

    \PhpOffice\PhpSpreadsheet\Cell\Cell::setValueBinder( new \PhpOffice\PhpSpreadsheet\Cell\AdvancedValueBinder() );

    $spreadsheet->getActiveSheet()
    ->setCellValue('H' . $filc, $fec);
    
    $spreadsheet->getActiveSheet()->getStyle('H' . $filc)
    ->getNumberFormat()
    ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_YYYYMMDDSLASH);

    $spreadsheet->getActiveSheet()
    ->setCellValue('I' . $filc, ($aminit=="")?"":\PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($aminit));
    $spreadsheet->getActiveSheet()
    ->setCellValue('K' . $filc, ($amfint=="")?"":\PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($amfint));
    $spreadsheet->getActiveSheet()
    ->setCellValue('M' . $filc, ($pminit=="")?"":\PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($pminit));
    $spreadsheet->getActiveSheet()
    ->setCellValue('O' . $filc, ($pmfint=="")?"":\PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($pmfint));

    $spreadsheet->getActiveSheet()->getStyle('I' . $filc)
    ->getNumberFormat()
    ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_TIME1);

    $spreadsheet->getActiveSheet()->getStyle('K' . $filc)
    ->getNumberFormat()
    ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_TIME1);

    $spreadsheet->getActiveSheet()->getStyle('M' . $filc)
    ->getNumberFormat()
    ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_TIME1);

    $spreadsheet->getActiveSheet()->getStyle('O' . $filc)
    ->getNumberFormat()
    ->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_DATE_TIME1);

    $spreadsheet->getActiveSheet()->getStyle('L' . $filc)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_00);

    $spreadsheet->getActiveSheet()->getStyle('P' . $filc)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_00);

    $spreadsheet->getActiveSheet()->getStyle('Q' . $filc)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_00);

    $spreadsheet->getActiveSheet()->getStyle('R' . $filc)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_00);

    $spreadsheet->getActiveSheet()->getStyle('W' . $filc,':AD'.$filc)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_00);

    $lineaWG.= "+W".$filc; $lineaW.= "+W".$filc;
    $lineaXG.= "+X".$filc; $lineaX.= "+X".$filc;
    $lineaYG.= "+Y".$filc; $lineaY.= "+Y".$filc;
    $lineaZG.= "+Z".$filc; $lineaZ.= "+Z".$filc;
    $lineaAAG.= "+AA".$filc; $lineaAA.= "+AA".$filc;
    $lineaABG.= "+AB".$filc; $lineaAB.= "+AB".$filc;
    $lineaACG.= "+AC".$filc; $lineaAC.= "+AC".$filc;
    $lineaADG.= "+AD".$filc; $lineaAD.= "+AD".$filc;

    
 

    //TIPO DE DATOSPASS
    //$spreadsheet->getActiveSheet()->getCell('I' . $fil)->setValueExplicit($codrd,\PhpOffice\PhpSpreadsheet\Cell\DataType::);

    $spreadsheet->getActiveSheet()->getStyle('H'.$filc.':AD'.$filc)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
    if(($numd==15 || $numd==$diafinal) and $i!=0)
    {
        $filc = $filc + 1;
        $filcs = $filcs + 1;
        $spreadsheet->getActiveSheet()
        // ->setCellValue('B' . $filc, $codigo)
        ->setCellValue('H' .  $filc, "Quincena: ".$nmes." ".$numd)
        ->setCellValue('W' .  $filc, $lineaW)
        ->setCellValue('X' .  $filc, $lineaX)
        ->setCellValue('Y' .  $filc, $lineaY)
        ->setCellValue('Z' .  $filc, $lineaZ)
        ->setCellValue('AA' . $filc, $lineaAA)
        ->setCellValue('AB' . $filc, $lineaAB)
        ->setCellValue('AC' . $filc, $lineaAC)
        ->setCellValue('AD' . $filc, $lineaAD)
        ->mergeCells('H'.$filc.':V'.$filc); 
        $spreadsheet->getActiveSheet()->getStyle('H'.$filc.':AD'.$filc)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);  
        cellColor('H'.$filc.':AD'.$filc, '002060'); 

        
        //$spreadsheet->getActiveSheet()->getStyle('H2:AD3')
        //->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
        
        //COLOR TEXTO
        $spreadsheet->getActiveSheet()->getStyle('H'.$filc.':AD'.$filc)
        ->getFont()->getColor()->setARGB('FFFFFF');

        $lineaW  = "=";
        $lineaX  = "=";
        $lineaY  = "=";
        $lineaZ  = "=";
        $lineaAA  = "=";
        $lineaAB  = "=";
        $lineaAC  = "=";
        $lineaAD  = "=";
    }    
    //$spreadsheet->getActiveSheet()->getCell('B' . $filc)->setValueExplicit($codigo,\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    //$spreadsheet->getActiveSheet()->getStyle('L'.$filc.':N'.$filc)->getNumberFormat()->setFormatCode('[Black][>=0]"$"* #,##0.00;[Red][<0]("$"* #,##0.00);"$"* #,##0.00');    

    $filc = $filc + 1;
    $filcs = $filcs + 1;
}
$filc1 = $filc;
$filcu = $filc-1;

$spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(4);
$spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(4);
$spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(4);
$spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(4);
$spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(4);
$spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(4);
$spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth(4);
$spreadsheet->getActiveSheet()->getColumnDimension('H')->setAutoSize(true);
$spreadsheet->getActiveSheet()->getColumnDimension('I')->setAutoSize(true);
$spreadsheet->getActiveSheet()->getColumnDimension('J')->setAutoSize(true);
$spreadsheet->getActiveSheet()->getColumnDimension('K')->setAutoSize(true);
$spreadsheet->getActiveSheet()->getColumnDimension('L')->setWidth(13.4);
$spreadsheet->getActiveSheet()->getColumnDimension('M')->setAutoSize(true);
$spreadsheet->getActiveSheet()->getColumnDimension('N')->setAutoSize(true);
$spreadsheet->getActiveSheet()->getColumnDimension('O')->setAutoSize(true);
$spreadsheet->getActiveSheet()->getColumnDimension('P')->setWidth(13.4);
$spreadsheet->getActiveSheet()->getColumnDimension('Q')->setAutoSize(true);
$spreadsheet->getActiveSheet()->getColumnDimension('R')->setAutoSize(true);
$spreadsheet->getActiveSheet()->getColumnDimension('S')->setAutoSize(true);
$spreadsheet->getActiveSheet()->getColumnDimension('T')->setWidth(17);
$spreadsheet->getActiveSheet()->getColumnDimension('U')->setWidth(17);
$spreadsheet->getActiveSheet()->getColumnDimension('V')->setAutoSize(true);
$spreadsheet->getActiveSheet()->getColumnDimension('W')->setAutoSize(true);
$spreadsheet->getActiveSheet()->getColumnDimension('X')->setAutoSize(true);
$spreadsheet->getActiveSheet()->getColumnDimension('Y')->setAutoSize(true);
$spreadsheet->getActiveSheet()->getColumnDimension('Z')->setAutoSize(true);
$spreadsheet->getActiveSheet()->getColumnDimension('AA')->setAutoSize(true);
$spreadsheet->getActiveSheet()->getColumnDimension('AB')->setAutoSize(true);
$spreadsheet->getActiveSheet()->getColumnDimension('AC')->setAutoSize(true);
$spreadsheet->getActiveSheet()->getColumnDimension('AD')->setAutoSize(true);
//zoom de la pagina
$spreadsheet->getActiveSheet()->getSheetView()->setZoomScale(90);
//titulos de la hoja
$spreadsheet->getActiveSheet()->setTitle("LIQUIDADOR");
$spreadsheet->setActiveSheetIndex(0);
//$spreadsheet->getActiveSheet()->setSheetState(PHPExcel_Worksheet::SHEETSTATE_HIDDEN);

$objDrawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
$objDrawing->setName('Potenco logo');
$objDrawing->setDescription('Potenco logo');
$objDrawing->setPath('../../../dist/img/Potenco-logo-mini.png');
$objDrawing->setHeight(60);
$objDrawing->setCoordinates('H1');
$objDrawing->setOffsetX(10);
$objDrawing->setOffsetY(10);
$objDrawing->setWorksheet($spreadsheet->getActiveSheet());

$callStartTime = microtime(true);
$archivo = $titulo.'.xlsx';
$archivo =  date('Ymd').''.$archivo;

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

$writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
$writer->save('php://output');