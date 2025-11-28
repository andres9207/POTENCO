<?php
include ("data/conexion.php");
session_start();// activa la variable de sesion
error_reporting(0);
$usuario   = $_COOKIE['usuarioPo'];
$idUsuario = $_COOKIE['idusuarioPo'];
$perfil    = $_SESSION["perfilPo"];

$conperfil = mysqli_query($conectar, "SELECT prf_clave_int FROM tbl_usuarios where usu_clave_int  = '".$idUsuario."'");
$datperfil = mysqli_fetch_array($conperfil);
$idperfil  = $datperfil['prf_clave_int'];
include '../../data/validarpermisos.php';
$wh = "";
if($p64>0 and $idperfil!=1)
{
$wh.=" and d.doc_aprobador = '".$idUsuario."'";
}
if($p65<=0 and $p64<=0)
{
$wh.=" and (d.doc_usu_creacion='".$idUsuario."' or d.doc_aprobador = '".$idUsuario."') ";
} 

$sql = "SELECT d.doc_clave_int, d.doc_radicado,td.tid_nombre,d.doc_estado FROM tbl_documentos d join tbl_tipo_documento td ON td.tid_clave_int = d.tid_clave_int JOIN tbl_usuarios u on u.usu_clave_int  = d.usu_clave_int WHERE ( doc_radicado LIKE '%".$_POST['query']."%' OR d.doc_radicado LIKE '%".$_POST['query']."%'  OR '".$_GET['query']."' IS NULL OR '".$_POST['query']."' = '' ) ".$wh."  ORDER BY d.doc_radicado ASC";


$resultset = mysqli_query($conectar, $sql);
$json = array();
while( $rows = mysqli_fetch_array($resultset) ) {
	$mod = "";
	$json[] = array("radicado"=> $rows["doc_radicado"],"tipo"=>$rows['tid_nombre'],"mod"=>$mod,'id'=>encrypt($rows['doc_clave_int'],'p4v4sAp'));
}
echo json_encode($json);
?>

