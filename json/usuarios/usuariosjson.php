<?php
include '../../data/db.config.php';
include '../../data/conexion.php';

session_start();// activa la variable de sesion
error_reporting(0);
$usuario   = $_COOKIE['usuarioPo'];
$idUsuario = $_COOKIE['idusuarioPo'];
$perfil    = $_SESSION["perfilPo"];
include '../../data/validarpermisos.php';
$per = $_POST['per'];
$nom = $_POST['nom'];
$ape = $_POST['ape'];
$ema = $_POST['ema'];
$usu = $_POST['usu'];
$ti = $_POST['ti'];
$est = $_POST['est'];
$est = implode(', ', (array)$est); if($est==""){$est1="''";}else {$est1=$est;}

$table = 'tbl_usuarios';

$primaryKey = 'u.usu_clave_int';

$p41 = isset($permisosUsuario[41]);
$p42 = isset($permisosUsuario[42]);
$p37 = isset($permisosUsuario[37]);

$columns = array(
		array(
			'db' => 'u.usu_clave_int',
			'dt' => 'DT_RowId', 'field' => 'usu_clave_int',
			'formatter' => function( $d, $row ) {
				return 'row_'.$d;
			}
        ),
        array( 'db' => 'u.usu_imagen','dt' => 'Imagen',  'field' => 'usu_imagen','formatter' => function( $d, $row ) {
            return '<img style ="height:30px ;width: auto"src="'.$d.'?'.time().'" class="img-responsive img-circle" >';
        }),
			array( 'db' => 'u.usu_clave_int', 'dt' => 'Codigo',   'field' => 'usu_clave_int' ),
			array( 'db' => 'u.usu_nombre',    'dt' => 'Nombre',   'field' => 'usu_nombre' ),
			array( 'db' => 'u.usu_apellido',  'dt' => 'Apellido', 'field' => 'usu_apellido' ),
			array( 'db' => 'u.usu_celular',   'dt' => 'Celular',  'field' => 'usu_celular' ),
			array( 'db' => 'u.usu_fijo',      'dt' => 'Fijo',     'field' => 'usu_fijo' ),
			array( 'db' => 'p.prf_nombre',    'dt' => 'Perfil',   'field' => 'prf_nombre' ),
			array( 'db' => 'u.usu_correo',    'dt' => 'Correo',   'field' => 'usu_correo' ),	
			array( 'db' => 'u.usu_direccion', 'dt' => 'Direccion','field' => 'usu_direccion' ),
			array( 'db' => 'u.usu_usuario',   'dt' => 'Usuario',  'field' => 'usu_usuario' ),
			array( 'db' => 'e.est_nombre', 	  'dt' => 'Estado',   'field' => 'est_nombre'),		
        array( 'db' => 'u.usu_clave_int', 'dt' => 'Edicion',  'field' => 'usu_clave_int','formatter'=>function($d,$row){
			 global $p41,$p42,$p37;		

			$edicion= "<div class='btn-group btn-group-sm  d-block d-sm-block d-lg-none'>";
			$edicion.="<button type='button' class='btn btn-default dropdown-toggle' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'></button>
			<div class='dropdown-menu'><a class='dropdown-item' href='#'>Acciones</a>";
			if($p41>0)
			{
				$edicion.="<a class='dropdown-item' onClick=CRUDUSUARIOS('EDITAR','".$d."') title='Editar usuario'  data-toggle='modal' data-target='#myModal'><i class='fas fa-edit text-warning'></i> Editar</a>";
			}
			if($p42>0)
			{
				$edicion.="<a class='dropdown-item' onclick=CRUDUSUARIOS('ELIMINAR','".$d."') title='Eliminar Usuario'><i class='fas fa-trash text-danger'></i> Eliminar</a>";
			}
			if($p37>0)
			{	
				$edicion.="<a class='dropdown-item' onClick=CRUDUSUARIOS('ASIGNARPERMISOS','".$d."') title='Asignar permisos' data-toggle='modal' data-target='#myModal'><i class='fas fa-lock text-info'></i> Permisos</a>";
			}		
				
			$edicion.="</div></div>";
			$edicion.= "<div class='btn-group btn-group-sm d-none d-sm-none d-lg-block'>";
			if($p41>0)
			{
				$edicion.="<a class='btn btn-warning rounded-circle' onClick=CRUDUSUARIOS('EDITAR','".$d."') title='Editar usuario'  data-toggle='modal' data-target='#myModal'><i class='fas fa-edit'></i></a>";
			}
			if($p42>0)
			{
				$edicion.="<a class='btn btn-danger rounded-circle' onclick=CRUDUSUARIOS('ELIMINAR','".$d."') title='Eliminar Usuario'><i class='fas fa-trash'></i></a>";
			}
			if($p37>0)
			{	
				$edicion.="<a class='btn btn-info rounded-circle' onClick=CRUDUSUARIOS('ASIGNARPERMISOS','".$d."') title='Asignar permisos' data-toggle='modal' data-target='#myModal'><i class='fas fa-lock'></i></a>";
			}
			$edicion.="</div>";

			return $edicion;
	   }),	 
	array( 'db' => 'u.usu_documento', 'dt' => 'Documento','field' => 'usu_documento' ),
	array( 'db' => 'u.usu_salario', 'dt' => 'Salario','field' => 'usu_salario', 'formatter'=> function($d,$row){
		return "$".number_format($d, 0, ',', '.');
	}),
);
$sql_details = array(
	'user' => 'usrpavashtg',
	'pass' => '9A12)WHFy$2p4v4s',
	'db'   => 'bdpotenco',
	'host' => 'localhost'
);

require( '../../data/ssp.class.php' );

$extraWhere=" (`u`.`usu_nombre` LIKE REPLACE('".$nom."%',' ','%') OR '".$nom."' IS NULL OR '".$nom."' = '') and (`u`.`usu_apellido` LIKE REPLACE('".$ape."%',' ','%') OR '".$ape."' IS NULL OR '".$ape."' = '')and (`u`.`usu_correo` LIKE REPLACE('".$ema."%',' ','%') OR '".$ema."' IS NULL OR '".$ema."' = '')and (`u`.`usu_usuario` LIKE REPLACE('".$usu."%',' ','%') OR '".$usu."' IS NULL OR '".$usu."' = '') and u.est_clave_int not in(3)  and ( u.est_clave_int  IN (".$est1.") OR '".$est."' IS NULL OR '".$est."' = '')";

if($per!= "" and $per!="Todos"){ $extraWhere.=" and u.prf_clave_int in (".$per.")";}
if($ti==2){ $extraWhere.=" and u.prf_clave_int = '".$ti."'";}
$groupBy = ' u.usu_clave_int';
$with = '';
$joinQuery = "FROM tbl_usuarios AS u JOIN tbl_perfil p ON p.prf_clave_int= u.prf_clave_int join tbl_estados e on e.est_clave_int = u.est_clave_int";
echo json_encode(SSP::simple($_POST, $sql_details, $table, $primaryKey, $columns, $joinQuery, $extraWhere, $groupBy,$with));