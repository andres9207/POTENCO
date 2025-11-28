<?php
    include '../../data/db.config.php';
	include '../../data/conexion.php';
	session_start();
    $usuario   = $_COOKIE['usuarioPo'];
    $idUsuario = $_COOKIE['idusuarioPo'];
    $perfil    = $_SESSION["perfilPo"];
	setlocale(LC_TIME, 'es_CO.UTF-8'); 
date_default_timezone_set('America/Bogota');
	$fecha=date("Y/m/d H:i:s");
    require_once "../../controladores/general.controller.php";
	// $ano = $_GET['ano'];
    // $mes = $_GET['mes']; $mes = ($mes<10)?"0".$mes:$mes;
    // $emp = $_GET['emp'];
    // $obr = $_GET['obr'];
    //$obr = implode(', ', (array)$obr); if($obr==""){$obr1="''";}else {$obr1=$obr;}
    //$des = $_GET['des'];
    //$has = $_GET['has'];
    // $tip = $_GET['tip'];
    error_reporting(0);
    $id = $_GET['id'];
    $id = decrypt($id,'p4v4sAp');
    
    ob_start();
    include('vistas/proforma.php');
    $content = ob_get_clean();

    //En una variable llamada $content se obtiene lo que tenga la ruta especificada
    //NOTA: Se usa ob_get_clean porque trae SOLO el contenido
    //EvitarÃ¡ este error tan comÃºn en FPDF:
    //FPDF error: Some data has already been output, can't send PDF
   
    // require_once('../../clases/pdf/html2pdf.class.php');
	require_once '../../vendor/autoload.php';

    use Spipu\Html2Pdf\Html2Pdf;
    use Spipu\Html2Pdf\Exception\Html2PdfException;
    use Spipu\Html2Pdf\Exception\ExceptionFormatter;
	
    try
    {        
        $html2pdf = new Html2Pdf('P', 'A4', 'es', true, 'UTF-8', array(5, 5, 5, 5)); //Configura la hoja
        $html2pdf->pdf->SetDisplayMode('fullpage'); //Ver otros parÃ¡metros para SetDisplaMode
        $html2pdf->pdf->SetAuthor('POTENCO');
        $html2pdf->setTestTdInOnePage(false);
        $html2pdf->pdf->SetTitle($titulo);
        $html2pdf->setTestIsImage(false);//pone una imagen en la etiqueta img cuando no se encuentra
        $html2pdf->writeHTML($content); //Se escribe el contenido 
        $html2pdf->Output($archivo); //Nombre default del PDF
    }
    catch (Html2PdfException $e) {
        $html2pdf->clean();    
        $formatter = new ExceptionFormatter($e);
        echo $formatter->getHtmlMessage();
    }