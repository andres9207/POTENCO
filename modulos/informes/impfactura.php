<?php
    include '../../data/db.config.php';
	include '../../data/conexion.php';
	session_start();
	$idpedido = $_GET['clave'];	
	ob_start();
    include('vistas/factura.php');
    //En una variable llamada $content se obtiene lo que tenga la ruta especificada
    //NOTA: Se usa ob_get_clean porque trae SOLO el contenido
    //EvitarÃ¡ este error tan comÃºn en FPDF:
    //FPDF error: Some data has already been output, can't send PDF
    $content = ob_get_clean();
    require_once('../../clases/pdf/html2pdf.class.php');
	// variable login que almacena el login o nombre de usuario de la persona logueada
    $usuario   = $_COOKIE['usuarioPo'];
    $idUsuario = $_COOKIE['idusuarioPo'];
    $perfil    = $_SESSION["perfilPo"];
	setlocale(LC_TIME, 'es_CO.UTF-8'); 
date_default_timezone_set('America/Bogota');
	$fecha=date("Y/m/d H:i:s");
		
	// verifica si no se ha loggeado
	

	$archivo = 'Orden de compra N°.pdf';//Cambiar por nuevo campo en BD para fecha cierre de la intervencion
    	
    try
    {
        $html2pdf = new HTML2PDF('P', 'A4', 'es', true, 'UTF-8', array(15, 10, 15, 15)); //Configura la hoja
        $html2pdf->pdf->SetDisplayMode('fullpage'); //Ver otros parÃ¡metros para SetDisplaMode
        $html2pdf->setTestTdInOnePage(false);
        $html2pdf->setTestIsImage(false);//pone una imagen en la etiqueta img cuando no se encuentra
        $html2pdf->writeHTML($content); //Se escribe el contenido 
        $html2pdf->Output($archivo); //Nombre default del PDF
    }
    catch(HTML2PDF_exception $e) {
        echo $e;
        exit;
    }

    ?>