<?php
	$params = array();
	$options =  array( "Scrollable" => SQLSRV_CURSOR_KEYSET );

//const SQLSRV_FETCH_ASSOC = '1';

if ( !defined( 'SQLSRV_FETCH_ASSOC' ) ){
      define('SQLSRV_FETCH_ASSOC', 2);
}
//$user = "aia";//sa
//$pass = "Nv3yWGr1z9-!";//Pavas.2018
//$bd = "aia";//AIA

$user = "sa";
$pass = "Pavas.2018";
$bd = "AIA";

//const SQLSRV_FETCH_ASSOC = '1';

//if (!defined('SQLSRV_FETCH_ASSOC')) {
    //const SQLSRV_FETCH_ASSOC = 0;
//}
//ip 192.168.1.9
//host gratuito den1.mssql8.gear.host Nv3yWGr1z9-! aia

	if (function_exists('sqlsrv_connect'))
	{
		//AIA PRUEBA
		//$serverName = "den1.mssql8.gear.host";
		//LOCAL
		$serverName = "PROGRAMADOR-PC\SQLEXPRESS, 1433"; //serverName\instanceName, portNumber (por defecto es 1433)
       
		//IP
		//$serverName = "190.7.157.34\SQLEXPRESS, 1433"; //serverName\instanceName, portNumber (por defecto es 1433)
		
		//WEBSERVICE
         //$serverName = "SAINT_CLOUD_RRM\SAINT"; //serverName\instanceName, portNumber (por defecto es 1433)
		//LOCAL
	     //$connectionInfo = array('CharacterSet' => 'UTF-8' ,  "Database"=>"AIA", "UID"=>"sa", "PWD"=>"Pavas.2018");

		//AIA PRUEBA
	     $connectionInfo = array('CharacterSet' => 'UTF-8' ,  "Database"=>$bd, "UID"=>$user, "PWD"=>$pass);

	     $conn = sqlsrv_connect($serverName, $connectionInfo);

		if( $conn )
		{
			//echo "Conectado a la Base de Datos.<br />";
		}
		else
		{
			echo "NO se puede conectar a la Base de Datos.<br />";
			die( print_r( sqlsrv_errors(), true));
		}
	}
	else
	{

		//$conn = new PDO("odbc:DSNName", "sa", "");
		$conn = new PDO("odbc:DSNName", $user,$pass);
		$conn->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );

		if (!function_exists('sqlsrv_query')) {
			function sqlsrv_query($pdo, $sql, $params = [], $options = [])
			{
				//global $conn;
				try {

					$stmt = $conn->prepare($sql);
					$stmt->execute();
					$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

					return $result;
				} catch (\Exception $e) {

					var_dump("sqlsrv_query error 1: ". $e->getMessage());
					var_dump("sqlsrv_query tsql 1: ". $sql);
					return [];
				}
			}
		}

		if (!function_exists('sqlsrv_fetch_array')) {
			function sqlsrv_fetch_array(&$statement, $fetchType = PDO::FETCH_ASSOC)
			{
				try {
					//$result = $statement->fetch(PDO::FETCH_ASSOC);
					//return $result;
					$result = current($statement);
					next($statement);
					return $result;
				} catch (\Exception $e) {
					var_dump("sqlsrv_fetch_array: ". $e->getMessage());
				}
			}
		}

		if (!function_exists('sqlsrv_num_rows')) {
			function sqlsrv_num_rows($statement)
			{
				try {
					//$result = $statement->rowCount();
					//return $result;
					return count($statement);
				} catch (\Exception $e) {
					var_dump("sqlsrv_num_rows: ". $e->getMessage());
				}
			}
		}

		if (!function_exists('sqlsrv_free_stmt')) {
			function sqlsrv_free_stmt($statement)
			{
				//$statement->closeCursor();
			}
		}

		if (!function_exists('sqlsrv_close')) {
			function sqlsrv_close($pdo)
			{
				$pdo = null;
			}
		}
	}