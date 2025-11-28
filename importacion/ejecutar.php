<?php
session_start();
error_reporting(0);
// variable login que almacena el login o nombre de usuario de la persona logueada
setlocale(LC_TIME, 'es_CO.UTF-8'); 
date_default_timezone_set('America/Bogota');
$fecha=date("Y/m/d H:i:s");
include ("../data/conexion.php");
require_once '../clases/PHPExcel/IOFactory.php';
ini_set('memory_limit','500M');
ini_set('max_execution_time', 600);
ini_set('upload_max_filesize', '300M');
//error_reporting(0);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);


$errores = 0;
$listaerrores = "";//Archivos: Linea: Columna: 

$archivo = "importacionusuarios.xls";
$objPHPEXCEL=PHPExcel_IOFactory::load($archivo);
$objPHPEXCEL->setActiveSheetIndex(0);
$objHoja=$objPHPEXCEL->getActiveSheet(0)->toArray(null,true,true,true,true,true,true);

$lineas = 0;

$perfil = 2;
$correos = array();
foreach($objHoja as $iIndice => $objCelda)
{
    $color = "";
    $COLA = trim($objCelda['A']); //CODDISTRITO
    $COLB = trim($objCelda['B']); //NOMBRE ZONA
    $COLC = trim($objCelda['C']); //COD VENDEDOR
    $COLD = trim($objCelda['D']); //NOMBRE VENDEDOR
    $COLE = trim($objCelda['E']); //MERCADO
    $COLF = trim($objCelda['F']); //NIT
    $COLG = trim($objCelda['G']); //CLIENTE
    $COLH = trim($objCelda['H']); //CIUDAD
    $COLI = trim($objCelda['I']); //DIRECCION
    $COLJ = trim($objCelda['J']); //CONTACTO
    $COLK = trim($objCelda['K']); //EMAIL
    $COLL = trim($objCelda['L']); //TELEFONO 1 
    $COLM = trim($objCelda['M']); //TELEFONO 2 
    $COLN = trim($objCelda['N']); //CELULAR
    if($COLF!="")
    {
        $correos[] = $COLF;
    }

    $pass = encrypt("ap2020*",'p4v4sAp');


    //$COLE = $objPHPEXCEL ->getActiveSheet() -> getCell('E'.$iIndice)->getCalculatedValue();//$objCelda['E'];
    //$color1 = $objPHPEXCEL ->getActiveSheet() ->getStyle('B'.$iIndice) ->getFill() ->getStartColor() ->getRGB();
    //$color  = "#".$color1;
    //VERDE = 92D050;   GRIS = C0C0C0 BLANCO = FFFFFF VERDE CLARO C4D79B
    
        
   /* if(str_replace(" ","",$COLA)==str_replace(" ","","CAP.") || str_replace(" ","",$COLB)==str_replace(" ","","ÍTEM") || str_replace(" ","",$COLC)==str_replace(" ","","DESCRIPCIÓN")  || str_replace(" ","",$COLE)==str_replace(" ","","CANT")  || str_replace(" ","",$COLF)==str_replace(" ","","VR.UNIT") || str_replace(" ","",$COLG)==str_replace(" ","","VR.TOTAL") || str_replace(" ","",$COLF)==str_replace(" ","","SUBTOTAL COSTO DIRECTO"))
    {
        if(str_replace(" ","",$COLA)==str_replace(" ","","CAP.") and str_replace(" ","",$COLB)==str_replace(" ","","ÍTEM") and str_replace(" ","",$COLC)==str_replace(" ","","DESCRIPCIÓN") and str_replace(" ","",$COLE)==str_replace(" ","","CANT")  and str_replace(" ","",$COLF)==str_replace(" ","","VR.UNIT") and str_replace(" ","",$COLG)==str_replace(" ","","VR.TOTAL"))
        {
            
        }
        else
        {
            $listaerrores .="<strong>Hoja:</strong> Presupuesto Inicial. <strong>Error:</strong> El Archivo no esta compuesto por las mismas columnas del formato establecido para la importación. <strong>Linea:</strong>".$iIndice." Columnas:A-".str_replace(" ","",$COLA)." B-".str_replace(" ","",$COLB)." C-".str_replace(" ","",$COLC)." D-".str_replace(" ","",$COLD)." E-".str_replace(" ","",$COLE)." F-".str_replace(" ","",$COLF)." G-".str_replace(" ","",$COLG)."</br>";
            $errores++;
        }
    }*/
    if($iIndice==1)
    {
        echo "Se linea de titulos. Linea.".$iIndice."<br>";
    }
    else
    {
        $valimercado  = mysqli_query($conectar, "SELECT mer_clave_int  from tbl_mercados where est_clave_int = 1 and replace(UPPER(mer_nombre),' ','') =replace( UPPER('".$COLE."'),' ','') limit 1");
        $datmer = mysqli_fetch_array($valimercado);
        $idmercado  = $datmer['mer_clave_int'];

        $convali = mysqli_query($conectar, "SELECT usu_clave_int FROM tbl_usuarios WHERE est_clave_int != 3 and replace(UPPER(usu_documento),' ','') = replace(UPPER('".$COLF."'),' ','') and replace(UPPER(usu_direccion),' ','') = replace(UPPER('".$COLI."'),' ','') and prf_clave_int = '2' LIMIT 1");

       // $convali = mysqli_query($conectar, "SELECT usu_clave_int FROM tbl_usuarios WHERE est_clave_int != 3 and replace(UPPER(usu_documento),' ','') = replace(UPPER('".$COLF."'),' ','') and prf_clave_int = '2' LIMIT 1");


        $numv = mysqli_num_rows($convali);
        if($numv>0 and $idmercado>0)
        {
            $datv = mysqli_fetch_array($convali);
            $idusuario = $datv['usu_clave_int'];

            echo "Existe cliente. dOC: ".$COLF." Linea.".$iIndice."<br>";
            
            $upd = mysqli_query($conectar, "UPDATE tbl_usuarios SET usu_nombre = '".$COLG."',usu_apellido = ''  ,usu_celular = '".$COLN."', usu_fijo = '".$COLL."',usu_fijo2 = '".$COLM."',usu_correo = '".$COLK."',usu_direccion = '".$COLI."',usu_clave = '".$pass."',prf_clave_int = '".$perfil."',est_clave_int = 1,usu_fec_actualiz = '".$fecha."',usu_documento = '".$COLF."' ,mer_clave_int = '".$idmercado."',usu_ciudad = '".$COLH."' ,usu_contacto = '".$COLJ."' WHERE usu_clave_int = '".$idusuario."'");
            if($upd>0)
            {
                $idusuario = mysqli_insert_id($conectar);
                
                //asociar 
                echo "Se actualizo cliente. Linea.".$iIndice."<br>";
                $validardistrito = mysqli_query($conectar, "SELECT usd_clave_int from tbl_usuarios_distritos WHERE usu_clave_int = '".$idusuario."' and dis_clave_int = '".$COLA."'");
                $numdis = mysqli_num_rows($validardistrito);
                if($numvdis>0)
                {
                    
                }
                else
                {
                    $insdis = mysqli_query($conectar, "INSERT INTO tbl_usuarios_distritos(dis_clave_int,usu_clave_int,usd_usu_actualiz,usd_fec_actualiz) VALUES('".$COLA."','".$idusuario."','importacion','".$fecha."')");
                }

            }
            else
            {                
                $listaerrores .="<strong>Hoja:</strong> Importación. <strong>Error:</strong> No se actualizo cliente. <strong>Linea:</strong> ".$iIndice.", COLE:".$COLE." <br>";
                $errores++;
            }

        }
        else if($idmercado>0)
        { 
            $COLF =  str_replace(" ","",$COLF);
            echo "sq: SELECT usu_clave_int FROM tbl_usuarios WHERE est_clave_int != 3 and replace(UPPER(usu_documento),' ','') = replace(UPPER('".$COLF."'),' ','')  and prf_clave_int = '2' LIMIT 1<br>";
            echo "Entro a insertar. Linea.".$iIndice."<br>";

            $ins = mysqli_query($conectar,"INSERT INTO tbl_usuarios(usu_nombre,usu_apellido,usu_celular, usu_fijo,usu_fijo2,usu_correo,usu_direccion,usu_clave,prf_clave_int,est_clave_int,usu_usu_actualiz,usu_fec_actualiz,usu_documento,usu_usuario,mer_clave_int,usu_ciudad,usu_contacto) VALUES ('".$COLG."','','".$COLN."','".$COLL."','".$COLM."','".$COLK."','".$COLI."','".$pass."','".$perfil."','1','importacion','".$fecha."','".$COLF."','','".$idmercado."','".$COLH."','".$COLJ."')");
            if($ins>0)
            {
                $idusuario = mysqli_insert_id($conectar);
                //asociar 
                echo "Se inserto cliente. Linea.".$iIndice."<br>";
                $validardistrito = mysqli_query($conectar, "SELECT usd_clave_int from tbl_usuarios_distritos WHERE usu_clave_int = '".$idusuario."' and dis_clave_int = '".$COLA."'");
                $numdis = mysqli_num_rows($validardistrito);
                if($numvdis>0)
                {
                    
                }
                else
                {
                    $insdis = mysqli_query($conectar, "INSERT INTO tbl_usuarios_distritos(dis_clave_int,usu_clave_int,usd_usu_actualiz,usd_fec_actualiz) VALUES('".$COLA."','".$idusuario."','importacion','".$fecha."')");
                }

            }
            else
            {                
                $listaerrores .="<strong>Hoja:</strong> Importación. <strong>Error:</strong> No se inserto cliente. <strong>Linea:</strong> ".$iIndice."<br>";
                $errores++;
            }
        }
        else
        {
            
            $listaerrores .="<strong>Hoja:</strong> Importación. <strong>Error:</strong> No hay mercado asociado al cliente. <strong>Linea:</strong> ".$iIndice.", COLE:".$COLE." <br>";
            $errores++;
        }	
    }	   
    $lineas++;
}
echo implode("','",$correos);
echo $listaerrores; 