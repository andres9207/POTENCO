<?php
include '../../data/conexion.php';

session_start();// activa la variable de sesion
error_reporting(0);
$usuario   = $_COOKIE['usuarioPo'];
$idUsuario = $_COOKIE['idusuarioPo'];
$perfil    = $_SESSION["perfilPo"];
include '../../data/validarpermisos.php';

$nom = $_POST['nom'];
$table = 'tbl_motivos';

$primaryKey = 'm.mot_clave_int';

$columns = array(
		array(
			'db' => 'm.mot_clave_int',
			'dt' => 'DT_RowId', 'field' => 'mot_clave_int',
			'formatter' => function( $d, $row ) {
				return 'row_'.$d;
			}
        ),
       
        array( 'db' => 'm.mot_nombre',    'dt' => 'Nombre',   'field' => 'mot_nombre' ),      
		array( 'db' => 'm.est_clave_int', 'dt' => 'Estado',   'field' => 'est_clave_int','formatter' => function( $d, $row ) {
			//return $d;   text-transform: lowercase;
				   if($d==1)
				   {
            			return '<input type="checkbox" disabled value="" checked>';
				   }
				   else
				   {
					   //return $row[4];
				   		return '<input type="checkbox" disabled value="">';
				   }
        		}),		
        array( 'db' => 'm.mot_clave_int', 'dt' => 'Edicion', 'field' => 'mot_clave_int','formatter'=>function($d,$row){

			global $p101,$p102;
			$edicion = "<div class='btn-group btn-group-sm'>";
			if($p101>0)
			{
				$edicion.="<a class='btn btn-warning' onClick=CRUDMOTIVOS('EDITAR','".$d."') title='Editar Motivo'  data-toggle='modal' data-target='#myModal'><i class='fas fa-edit'></i></a>";
			}
			if($p102>0)
			{
				$edicion.="<a class='btn btn-danger' onclick=CRUDMOTIVOS('ELIMINAR','".$d."') title='Eliminar Motivo'><i class='fas fa-trash'></i></a>";
			}
            $edicion.="</div>";

			return $edicion;
       })
);
$sql_details = array(
	'user' => 'usrpavashtg',
	'pass' => '9A12)WHFy$2p4v4s',
	'db'   => 'bdpotenco',
	'host' => 'localhost'
);

require( '../../data/ssp.class.php' );

$extraWhere="(`m`.`mot_nombre` LIKE REPLACE('".$nom."%',' ','%') OR '".$nom."' IS NULL OR '".$nom."' = '')  and m.est_clave_int not in(3)";

$groupBy = ' m.mot_clave_int';
$with = '';
$joinQuery = "FROM tbl_motivos m";
echo json_encode(SSP::simple($_POST, $sql_details, $table, $primaryKey, $columns, $joinQuery, $extraWhere, $groupBy,$with));

