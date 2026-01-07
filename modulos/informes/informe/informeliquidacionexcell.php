<?php
session_start();
include '../../../data/db.config.php';
include "../../../data/conexion.php";
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
ini_set('memory_limit','500M');
ini_set('max_execution_time', 600);

require_once '../../../clases/PHPExcel.php';

date_default_timezone_set('America/Bogota');

// Parámetros (acepta GET o POST)
$ano  = isset($_REQUEST['ano']) ? $_REQUEST['ano'] : '';
$mes  = isset($_REQUEST['mes']) ? str_pad($_REQUEST['mes'],2,'0',STR_PAD_LEFT) : '';
$emp  = isset($_REQUEST['emp']) ? intval($_REQUEST['emp']) : 0;
$obr  = isset($_REQUEST['obr']) ? intval($_REQUEST['obr']) : 0;
$ini  = isset($_REQUEST['ini']) ? $_REQUEST['ini'] : '';
$fin  = isset($_REQUEST['fin']) ? $_REQUEST['fin'] : '';
$tip  = isset($_REQUEST['tip']) ? intval($_REQUEST['tip']) : 1;

if($ini=="" || $fin=="" || $emp<=0){
    echo "<div class='alert alert-info'>Faltan parámetros (envíe: emp, ini, fin).</div>";
    exit;
}

// Obtener nombre empleado
$nombreEmp = '';
$stmtE = $conn->prepare("SELECT concat(usu_nombre,' ',usu_apellido) nom, usu_clave_int FROM tbl_usuarios WHERE usu_clave_int = :emp LIMIT 1");
$stmtE->bindParam(':emp',$emp,PDO::PARAM_INT);
$stmtE->execute();
$dataE = $stmtE->fetch(PDO::FETCH_ASSOC);
if($dataE){ $nombreEmp = $dataE['nom']; }

// Consulta dias liquidacion
$sql = "SELECT lid_fecha AS fecha, lid_hi_man AS hi_man, lid_hf_man AS hf_man, lid_hi_tar AS hi_tar, lid_hf_tar AS hf_tar, lid_horas AS horas, lid_horario AS horario, lid_hedo AS hedo, lid_hdf AS hdf, lid_hedf AS hedf, lid_heno AS heno, lid_henf AS henf, lid_rn AS rn, lid_hnf AS hnf, lid_rd AS rd, lid_alimentacion AS alimentacion, lid_val_alimentacion AS valalimentacion, lid_bonificacion AS bonificacion, lid_val_bonificacion AS valbonificacion, lid_auxilio AS auxilio, lid_permisos AS permisos, lid_observacion AS observaciones
        FROM tbl_liquidar_dias
        WHERE lid_fecha BETWEEN :ini AND :fin AND usu_clave_int = :emp
        ORDER BY lid_fecha ASC";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':ini',$ini);
$stmt->bindParam(':fin',$fin);
$stmt->bindParam(':emp',$emp,PDO::PARAM_INT);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Crear PHPExcel
PHPExcel_Cell::setValueBinder( new PHPExcel_Cell_AdvancedValueBinder() );
$objPHPExcel = new PHPExcel();
$objPHPExcel->getProperties()->setCreator("Pavas.co")
    ->setLastModifiedBy("Pavas.co")
    ->setTitle("Liquidacion $nombreEmp")
    ->setSubject("Liquidacion")
    ->setDescription("Liquidacion de horas y conceptos")
    ->setKeywords("liquidacion excel")
    ->setCategory("Informes");

$sheet = $objPHPExcel->setActiveSheetIndex(0);

// Cabecera
$sheet->setCellValue('A1', 'LIQUIDACIÓN');
$sheet->setCellValue('A2', 'Empleado: ' . $nombreEmp);
$sheet->setCellValue('A3', 'Periodo: ' . $ini . ' - ' . $fin);

$headers = ['Fecha','Horario','Hi Mañana','Hf Mañana','Hi Tarde','Hf Tarde','Horas Totales','Alimentación','Val. Ali','Bonificación','Val. Bon','Auxilio','HEDO','HDF','HEDF','HENO','HENF','RN','HNF','RD','Permisos','Observaciones'];
$col = 'A';
$row = 5;
foreach($headers as $h){
    $sheet->setCellValue($col.$row, $h);
    $sheet->getStyle($col.$row)->getFont()->setBold(true);
    $sheet->getColumnDimension($col)->setAutoSize(true);
    $col++;
}

// Rellenar datos
$r = $row + 1;
$totales = [ 'HEDO'=>0,'HDF'=>0,'HEDF'=>0,'HENO'=>0,'HENF'=>0,'RN'=>0,'HNF'=>0,'RD'=>0,'Horas'=>0,'Alimentacion'=>0,'Bonificacion'=>0,'Auxilio'=>0,'Permisos'=>0 ];
foreach($rows as $d){
    $horasTot = floatval(str_replace(',','.',$d['horas']));
    $hedo = floatval(str_replace(',','.',$d['hedo']));
    $hdf  = floatval(str_replace(',','.',$d['hdf']));
    $hedf = floatval(str_replace(',','.',$d['hedf']));
    $heno = floatval(str_replace(',','.',$d['heno']));
    $henf = floatval(str_replace(',','.',$d['henf']));
    $rn   = floatval(str_replace(',','.',$d['rn']));
    $hnf  = floatval(str_replace(',','.',$d['hnf']));
    $rd   = floatval(str_replace(',','.',$d['rd']));
    $ali  = intval($d['alimentacion']);
    $valali = floatval(str_replace(',','.',$d['valalimentacion']));
    $bon  = intval($d['bonificacion']);
    $valbon = floatval(str_replace(',','.',$d['valbonificacion']));
    $aux  = floatval(str_replace(',','.',$d['auxilio']));
    $perm = floatval(str_replace(',','.',$d['permisos']));

    $c = 'A';
    $sheet->setCellValue($c.$r, $d['fecha']); $c++;
    $sheet->setCellValue($c.$r, $d['horario']); $c++;
    $sheet->setCellValue($c.$r, $d['hi_man']); $c++;
    $sheet->setCellValue($c.$r, $d['hf_man']); $c++;
    $sheet->setCellValue($c.$r, $d['hi_tar']); $c++;
    $sheet->setCellValue($c.$r, $d['hf_tar']); $c++;
    $sheet->setCellValue($c.$r, $horasTot); $c++;
    $sheet->setCellValue($c.$r, $ali ? 'Sí' : ''); $c++;
    $sheet->setCellValue($c.$r, $valali); $c++;
    $sheet->setCellValue($c.$r, $bon ? 'Sí' : ''); $c++;
    $sheet->setCellValue($c.$r, $valbon); $c++;
    $sheet->setCellValue($c.$r, $aux); $c++;
    $sheet->setCellValue($c.$r, $hedo); $c++;
    $sheet->setCellValue($c.$r, $hdf); $c++;
    $sheet->setCellValue($c.$r, $hedf); $c++;
    $sheet->setCellValue($c.$r, $heno); $c++;
    $sheet->setCellValue($c.$r, $henf); $c++;
    $sheet->setCellValue($c.$r, $rn); $c++;
    $sheet->setCellValue($c.$r, $hnf); $c++;
    $sheet->setCellValue($c.$r, $rd); $c++;
    $sheet->setCellValue($c.$r, $perm); $c++;
    $sheet->setCellValue($c.$r, $d['observaciones']);

    // totales
    $totales['HEDO'] += $hedo;
    $totales['HDF'] += $hdf;
    $totales['HEDF'] += $hedf;
    $totales['HENO'] += $heno;
    $totales['HENF'] += $henf;
    $totales['RN'] += $rn;
    $totales['HNF'] += $hnf;
    $totales['RD'] += $rd;
    $totales['Horas'] += $horasTot;
    $totales['Alimentacion'] += $valali;
    $totales['Bonificacion'] += $valbon;
    $totales['Auxilio'] += $aux;
    $totales['Permisos'] += $perm;

    $r++;
}

// Totales al final
$r += 1;
$sheet->setCellValue('A'.$r, 'TOTALES');
$sheet->setCellValue('G'.$r, $totales['Horas']);
$sheet->setCellValue('I'.$r, $totales['Alimentacion']);
$sheet->setCellValue('K'.$r, $totales['Bonificacion']);
$sheet->setCellValue('L'.$r, $totales['Auxilio']);
$sheet->setCellValue('M'.$r, $totales['HEDO']);
$sheet->setCellValue('N'.$r, $totales['HDF']);
$sheet->setCellValue('O'.$r, $totales['HEDF']);
$sheet->setCellValue('P'.$r, $totales['HENO']);
$sheet->setCellValue('Q'.$r, $totales['HENF']);
$sheet->setCellValue('R'.$r, $totales['RN']);
$sheet->setCellValue('S'.$r, $totales['HNF']);
$sheet->setCellValue('T'.$r, $totales['RD']);
$sheet->setCellValue('U'.$r, $totales['Permisos']);

// Formato simple
$sheet->getStyle('A5:U5')->getFont()->setBold(true);
$sheet->getActiveSheet()->getSheetView()->setZoomScale(90);
$sheet->setTitle('Liquidacion');

// Guardar en la misma carpeta y forzar descarga
$archivo = 'LIQUIDACION_'.($emp)."_".date('Ymd_His').'.xlsx';
$destDir = __DIR__ . '/';
$destPath = $destDir . $archivo;

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
// Guardar archivo en servidor
try{
    $objWriter->save($destPath);
}catch(Exception $e){
    // no bloquear si no puede guardar
}

// Forzar descarga
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="'.$archivo.'"');
header('Cache-Control: max-age=0');
$objWriter2 = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
$objWriter2->save('php://output');
exit;
