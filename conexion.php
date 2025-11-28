<?php

	error_reporting(0);
	define("cServidor", "localhost");
	define("cUsuario", "usrpavashtg");
	define("cPass","9A12)WHFy$2p4v4s");
	define("cBd","bdpotenco");
	$userName = 'andres.199207@gmail.com'; //"adminpavas@pavas.com.co"
	$passWord = 'Bayron.1214';
	$from = "ADMIN CMAP";
	$smtp = true;

	$logopavas = "https://www.pavas.com.co/img/logoPavasStay.png";

    $conectar = mysqli_connect(cServidor, cUsuario, cPass, cBd);
	mysqli_query($conectar,"SET NAMES 'utf8'");
	$urlweb = $_SERVER["HTTP_HOST"]."/CMAP/";
    $inactivo = 60;

    function br2nl($string)
	{
	    return preg_replace('/\<br(\s*)?\/?\>/i', "\n", $string);
	}

	function decrypt($string, $key)
    {
       $result = "";
       $string = base64_decode($string);
       for($i=0; $i<strlen($string); $i++) 
       {
           $char = substr($string, $i, 1);
           $keychar = substr($key, ($i % strlen($key))-1, 1);
           $char = chr(ord($char)-ord($keychar));
           $result.=$char;
       }
       return $result;
    }
    function encrypt($string, $key) 
    {
       $result = "";	
       for($i=0; $i<strlen($string); $i++) 
       {	
           $char = substr($string, $i, 1);
           $keychar = substr($key, ($i % strlen($key))-1, 1);
           $char = chr(ord($char)+ord($keychar));
           $result.=$char;
       }
       return base64_encode($result);
    }

	function WeekToDate ($week, $year) 
	{ 
		$Jan1 = mktime (1, 1, 1, 1, 1, $year); 
		$iYearFirstWeekNum = (int) strftime("%W",mktime (1, 1, 1, 1, 1, $year)); 

		if ($iYearFirstWeekNum == 1) 
		{ 
			$week = $week - 1; 
		} 

		$weekdayJan1 = date ('w', $Jan1); 
		$FirstMonday = strtotime(((4-$weekdayJan1)%7-3) . ' days', $Jan1); 
		$CurrentMondayTS = strtotime(($week) . ' weeks', $FirstMonday); 
		return ($CurrentMondayTS); 
	} 
	function hourdiff($hour_1 , $hour_2 , $formated=true){
		
		if($hour_1!=NULL and $hour_2!=NULL)
		{
			$h= date("H", strtotime("00:00:00") + strtotime($hour_2) - strtotime($hour_1));
			$m= date("i", strtotime("00:00:00") + strtotime($hour_2) - strtotime($hour_1));
		}
		else{
			$h = 0; $m = 0;
		}
		return  (int)$h."h ".(int)$m."m";
	}

	function permisosVentana($idperfil,$idventana)
	{
		global $conectar; 
		$veri = mysqli_query($conectar, "SELECT pev_clave_int FROM tbl_permisos_ventana where prf_clave_int = '".$idperfil."' and ven_clave_int = '".$idventana."'");
		$numv = mysqli_num_rows($veri); if($numv<=0){ $numv = 0; }
		return $numv;
	}
	$msnGeneral = array(
		"correoInvalido"=>"Ingresar un correo valido",
		"errorBd" => "Surgió un error de base de datos"
	);

?>
