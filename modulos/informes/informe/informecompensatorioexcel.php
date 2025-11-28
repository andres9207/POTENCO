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
    $emp1 = "";
    if($emp==""){$emp1="''";}else {$emp1=$emp;}
    $tip = $_GET['tip'];
    $opc = $_GET['opc'];
    $titulo  = $opc==1?"INFORME COMPENSATORIO AÑO":"INFORME COMPENSATORIO MES";
    $titulo2  = $opc==1?"AÑO":"MES";
    $campo = $opc==1?"ta.Anio":"date_format(ld.lid_fecha, '%Y%m')";

    $sqlcompensatorio = $opc==1 ? "SELECT CONCAT( u.usu_apellido, ' ',u.usu_nombre) Nombre,u.usu_documento Cedula, ".$campo." dato, 
    SUM(IF(ta.generados>=3,ta.generados,0)) Generados, SUM(ta.compensados) Compensados, SUM(ta.remunerados) Remunerados, SUM(ta.tomados) Tomados FROM tbl_usuarios u JOIN (SELECT
    ld.usu_clave_int idu,
    DATE_FORMAT(ld.lid_fecha, '%Y') AS Anio,
    DATE_FORMAT(ld.lid_fecha, '%Y%m') AS Mes,
    SUM(IF(diaHabil(ld.lid_fecha) = 1 AND ld.lid_horas > 0, 1, 0)) AS generados,
    SUM(IF(diaHabil(ld.lid_fecha) = 1 AND ld.lid_horas > 0 AND t.tin_suma = 4, 1, 0)) AS compensados,
    SUM(IF(diaHabil(ld.lid_fecha) = 1 AND ld.lid_horas > 0 AND t.tin_suma = 5, 1, 0)) AS remunerados,
    SUM(IF(t.tin_suma = 7, 1, 0)) AS tomados
    FROM
    tbl_liquidar_dias ld
    LEFT OUTER JOIN
    tbl_tipo_novedad t ON t.tin_clave_int = ld.tin_clave_int
    WHERE (ld.usu_clave_int in(".$emp1.")  OR '".$emp."' IS NULL OR '".$emp."' = '') 
    GROUP BY
    idu,Anio, Mes
    HAVING
    generados >=3 OR remunerados>0 OR tomados>0 OR compensados>0) as ta on ta.idu = u.usu_clave_int WHERE u.est_clave_int in(1)
    GROUP BY dato, Nombre, cedula ORDER BY Nombre, dato  " :"SELECT CONCAT( u.usu_apellido, ' ',u.usu_nombre) Nombre,u.usu_documento Cedula, ".$campo." dato, 
    SUM(IF(diaHabil(ld.lid_fecha) = 1 and ld.lid_horas>0,1,0)) Generados, SUM(IF(diaHabil(ld.lid_fecha) = 1 and ld.lid_horas>0  and t.tin_suma = 4,1,0)) Compensados, SUM(IF(diaHabil(ld.lid_fecha) = 1 and ld.lid_horas>0  and t.tin_suma = 5,1,0)) Remunerados, SUM(IF(t.tin_suma = 7,1,0)) Tomados FROM tbl_usuarios u JOIN tbl_liquidar_dias ld  on  ld.usu_clave_int = u.usu_clave_int LEFT OUTER JOIN tbl_tipo_novedad t on t.tin_clave_int = ld.tin_clave_int 
    WHERE (ld.usu_clave_int in(".$emp1.")  OR '".$emp."' IS NULL OR '".$emp."' = '') and u.est_clave_int in(1)
    GROUP BY dato, Nombre, cedula HAVING Generados>=3 or Compensados>0 or Tomados>0 or Remunerados>0 ORDER BY Nombre, dato 
    ";

    // echo $sqlcompensatorio;
    
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
    ->setSubject("Informe Compensatorios")
    ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
    ->setKeywords("office 2021 openxml php")
    ->setCategory("Informes");
    $spreadsheet->setActiveSheetIndex(0);
    $nomarc = $titulo;
    $spreadsheet->getActiveSheet()
    ->setCellValue('A1', $titulo)
    ->setCellValue('A2', "EMPLEADO")
    ->setCellValue('B2', "CEDULA")
    ->setCellValue('C2', $titulo2)
    ->setCellValue('D2', "GENERADOS")
    ->setCellValue('E2', "COMPENSADOS")
    ->setCellValue('F2', "TOMADOS")
    ->setCellValue('G2', "PENDIENTES")
    ->mergeCells('A1:G1');

    $spreadsheet->getActiveSheet()->getStyle('A2:G2')
        ->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE);
        
    $spreadsheet->getActiveSheet()->getStyle('A2:G2')
    ->getFont()->getColor()->setARGB(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_ORANGE);

    $spreadsheet->getActiveSheet()->getStyle('A2:G2')->getAlignment()->setWrapText(true);

    $spreadsheet->getActiveSheet()->getStyle('A1:G2')->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
    $spreadsheet->getActiveSheet()->getStyle('A1:G2')->getFont()->setBold(true);
    $spreadsheet->getActiveSheet()->getStyle('A1:G2')->getAlignment()->applyFromArray(array('horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER));
    $spreadsheet->getActiveSheet()->getStyle('A1:G2')->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);

    //echo date('H:i:s') , " Set column height" , EOL;
    $spreadsheet->getActiveSheet()->getRowDimension('1')->setRowHeight(60);
    $spreadsheet->getActiveSheet()->getRowDimension('2')->setRowHeight(12.75);

    $stmtCompensatorio = $conn->prepare($sqlcompensatorio);
    $stmtCompensatorio->execute();
    $numcompensatorio = $stmtCompensatorio->rowCount();
    //echo $numpro;
    $hastacont = $numcompensatorio +3;
    $acum = $hastacont;
    $filc = 3;

    while ($data = $stmtCompensatorio->fetch(PDO::FETCH_ASSOC)) {
        $nombre = $data['Nombre'];
        $cedula = $data['Cedula'];
        $dato = $data['dato'];
        $generados = $data['Generados'];
        $compensados = $data['Compensados'];
        $tomados = $data['Tomados'];
        
        $spreadsheet->getActiveSheet()
        ->setCellValue('A'. $filc, $nombre)
        ->setCellValue('B'. $filc, $cedula)
        ->setCellValue('C'. $filc, $dato)
        ->setCellValue('D'. $filc, $generados>=3?$generados:0)
        ->setCellValue('E'. $filc, $compensados)
        ->setCellValue('F'. $filc, $tomados)
        ->setCellValue('G'. $filc,"=+D".$filc."+E".$filc."-F".$filc);
     
        $spreadsheet->getActiveSheet()->getStyle("D".$filc.":G".$filc)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_00);      

        $spreadsheet->getActiveSheet()->getStyle("D".$filc.":G".$filc)->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
        //BORDES
        $spreadsheet->getActiveSheet()->getStyle("A".$filc.":G".$filc)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_DASHED);      

        $filc  = $filc + 1;
    }
    $filc1 = $filc+1;
    $filcu = $filc-1;

    $spreadsheet->getActiveSheet()->getRowDimension($filc)->setRowHeight(7);

    $spreadsheet->getActiveSheet()
    ->setCellValue('A'. $filc1,  "TOTALES")
    ->setCellValue('D'. $filc1,  "=SUM(D3:D".$filcu.")")
    ->setCellValue('E'. $filc1,  "=SUM(E3:E".$filcu.")")
    ->setCellValue('F'. $filc1,  "=SUM(F3:F".$filcu.")")
    ->setCellValue('G'. $filc1,"=+D".$filc1."+E".$filc1."-F".$filc1)
    ->mergeCells('A'.$filc1.':C'.$filc1);
    cellColor('A'.$filc1.':G'.$filc1,'EEECE1');

    $spreadsheet->getActiveSheet()->getStyle('D'.$filc1.':G'.$filc1)->getNumberFormat()->setFormatCode(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::FORMAT_NUMBER_00);
    $spreadsheet->getActiveSheet()->getStyle("A".$filc1.":G".$filc1)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
    $spreadsheet->getActiveSheet()->getStyle("A".$filc1.":G".$filc1)->getFont()->setBold(true);

    $spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(45);
    $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(15);
    $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(15);
    $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(15);
    $spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(15);
    $spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(15);
    $spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth(15);
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
    $objDrawing->setHeight(40);
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