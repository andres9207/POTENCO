<?php
error_reporting(0);
$ruta = $_POST['ruta'];
$input = $_POST['input'];
$formu = $_POST['formu'];
$tmp = $_POST['tmp'];
ini_set('memory_limit','600M');
ini_set('max_execution_time', 1000);
ini_set('upload_max_filesize', '600M');
 
$carpeta=$tmp."/";
 
$arch = basename($_FILES['input']['name']);
$name=$carpeta."".basename($_FILES['input']['name']);
if(move_uploaded_file($_FILES['input']['tmp_name'], $name))
{
    $res = "ok";
    $url = $name;
}
else
{
    $res = "error";
    $url = "";
    $msn = "No se movio el archivo";
}
 
$datos = array();
$datos["res"] = $res;
$datos["url"] = $url;
$datos["msn"] = $msn;
$datos['name']= $arch;
 
$json = json_encode($datos);
echo json_encode($datos, JSON_FORCE_OBJECT);