<?php
include '../../data/db.config.php';
include '../../data/conexion.php';

session_start();// activa la variable de sesion
error_reporting(0);
$usuario   = $_COOKIE['usuarioPo'];
$idUsuario = $_COOKIE['idusuarioPo'];
$perfil    = $_SESSION["perfilPo"];
include '../../data/validarpermisos.php';

$emp = $_POST['emp'];
$emp = implode(', ', (array)$emp); if($emp==""){$emp1="''";}else {$emp1=$emp;}
$des = $_POST['des'];
$has = $_POST['has'];

$table = 'tbl_usuarios';

$primaryKey = 'u.usu_clave_int';

$columns = array(
    array(
        'db' => 'u.usu_clave_int',
        'dt' => 'DT_RowId', 'field' => 'usu_clave_int',
        'formatter' => function( $d, $row ) {
            return 'rowc_'.$d;
        }
    ),  
    array( 'db' => "u.usu_clave_int", 'dt' => 'Clave', 'as'=>'Clave', 'field' => 'Clave' ),      
    array( 'db' => "concat(u.usu_apellido,' ', u.usu_nombre)",'dt' => 'Nombre', 'as' => 'Nombre',      'field' => 'Nombre' ),   
    array( 'db' => "u.usu_documento", 'dt' => 'Cedula', 'as' => 'Cedula', 'field' => 'Cedula' ), 
    array( 'db' => "avg( l.liq_salario )",   'dt' => 'ValorHora',   'as' => 'ValorHora',   'field' => 'ValorHora' ),   
    array( 'db' => "(avg( l.liq_salario ) * avg( l.liq_hr_mes ) )", 'dt' => 'Basico', 'as' => 'Basico', 'field' => 'Basico' ),  


    /*
    array( 'db' => "getPorcentaje(u.usu_clave_int,'HEDO','".$des."','".$has."')", 'dt' => 'vhedo', 'as' => 'vhedo', 'field' => 'vhedo', 'formatter'=>function($d,$row){
        $vrhor = $row[4];
        $por = $d * $vrhor;
        return $por; 
    } ),
    array( 'db' => "getPorcentaje(u.usu_clave_int,'HDF','".$des."','".$has."')", 'dt' => 'vhdf', 'as' => 'vhdf', 'field' => 'vhdf', 'formatter'=>function($d,$row){
        $vrhor = $row[4];
        $por = $d * $vrhor;
        return $por; 
    } ),
    array( 'db' => "getPorcentaje(u.usu_clave_int,'HEDF','".$des."','".$has."')", 'dt' => 'vhedf', 'as' => 'vhedf', 'field' => 'vhedf', 'formatter'=>function($d,$row){
        $vrhor = $row[4];
        $por = $d * $vrhor;
        return $por; 
    }  ),
    array( 'db' => "getPorcentaje(u.usu_clave_int,'HENO','".$des."','".$has."')", 'dt' => 'vheno', 'as' => 'vheno', 'field' => 'vheno', 'formatter'=>function($d,$row){
        $vrhor = $row[4];
        $por = $d * $vrhor;
        return $por; 
    }  ),
    array( 'db' => "getPorcentaje(u.usu_clave_int,'HENF','".$des."','".$has."')", 'dt' => 'vhenf', 'as' => 'vhenf', 'field' => 'vhenf', 'formatter'=>function($d,$row){
        $vrhor = $row[4];
        $por = $d * $vrhor;
        return $por; 
    }  ),
    array( 'db' => "getPorcentaje(u.usu_clave_int,'RN','".$des."','".$has."')", 'dt' => 'vrn', 'as' => 'vrn', 'field' => 'vrn', 'formatter'=>function($d,$row){
        $vrhor = $row[4];
        $por = $d * $vrhor;
        return $por; 
    }  ),
    array( 'db' => "getPorcentaje(u.usu_clave_int,'HNF','".$des."','".$has."')", 'dt' => 'vhnf', 'as' => 'vhnf', 'field' => 'vhnf', 'formatter'=>function($d,$row){
        $vrhor = $row[4];
        $por = $d * $vrhor;
        return $por; 
    }  ),
    array( 'db' => "getPorcentaje(u.usu_clave_int,'RD','".$des."','".$has."')", 'dt' => 'vrd', 'as' => 'vrd', 'field' => 'vrd', 'formatter'=>function($d,$row){
        $vrhor = $row[4];
        $por = $d * $vrhor;
        return $por; 
    }  ),
    array( 'db' => "getPorcentaje(u.usu_clave_int,'ARE','".$des."','".$has."')", 'dt' => 'vare', 'as' => 'vare', 'field' => 'vare', 'formatter'=>function($d,$row){
        $vrhor = $row[4];
        $por = $d * $vrhor;
        return $por; 
    }  ),*/


    //inicio

    array( 'db' => "SUM(DISTINCT(IF(h.hor_nombre='HEDO',lih_porcentaje,0)))", 'dt' => 'vhedo', 'as' => 'vhedo', 'field' => 'vhedo', 'formatter'=>function($d,$row){
        $vrhor = $row[4];
        $por = $d * $vrhor;
        return $por; 
    } ),
    array( 'db' => "SUM(DISTINCT(IF(h.hor_nombre='HDF',lih_porcentaje,0)))", 'dt' => 'vhdf', 'as' => 'vhdf', 'field' => 'vhdf', 'formatter'=>function($d,$row){
        $vrhor = $row[4];
        $por = $d * $vrhor;
        return $por; 
    } ),
    array( 'db' => "SUM(DISTINCT(IF(h.hor_nombre='HEDF',lih_porcentaje,0)))", 'dt' => 'vhedf', 'as' => 'vhedf', 'field' => 'vhedf', 'formatter'=>function($d,$row){
        $vrhor = $row[4];
        $por = $d * $vrhor;
        return $por; 
    }  ),
    array( 'db' => "SUM(DISTINCT(IF(h.hor_nombre='HENO',lih_porcentaje,0)))", 'dt' => 'vheno', 'as' => 'vheno', 'field' => 'vheno', 'formatter'=>function($d,$row){
        $vrhor = $row[4];
        $por = $d * $vrhor;
        return $por; 
    }  ),
    array( 'db' => "SUM(DISTINCT(IF(h.hor_nombre='HENF',lih_porcentaje,0)))", 'dt' => 'vhenf', 'as' => 'vhenf', 'field' => 'vhenf', 'formatter'=>function($d,$row){
        $vrhor = $row[4];
        $por = $d * $vrhor;
        return $por; 
    }  ),
    array( 'db' => "SUM(DISTINCT(IF(h.hor_nombre='RN',lih_porcentaje,0)))", 'dt' => 'vrn', 'as' => 'vrn', 'field' => 'vrn', 'formatter'=>function($d,$row){
        $vrhor = $row[4];
        $por = $d * $vrhor;
        return $por; 
    }  ),
    array( 'db' => "SUM(DISTINCT(IF(h.hor_nombre='HNF',lih_porcentaje,0)))", 'dt' => 'vhnf', 'as' => 'vhnf', 'field' => 'vhnf', 'formatter'=>function($d,$row){
        $vrhor = $row[4];
        $por = $d * $vrhor;
        return $por; 
    }  ),
    array( 'db' => "SUM(DISTINCT(IF(h.hor_nombre='RD',lih_porcentaje,0)))", 'dt' => 'vrd', 'as' => 'vrd', 'field' => 'vrd', 'formatter'=>function($d,$row){
        $vrhor = $row[4];
        $por = $d * $vrhor;
        return $por; 
    }  ),
    array( 'db' => "SUM(DISTINCT(IF(h.hor_nombre='ARE',lih_porcentaje,0)))", 'dt' => 'vare', 'as' => 'vare', 'field' => 'vare', 'formatter'=>function($d,$row){
        $vrhor = $row[4];
        $por = $d * $vrhor;
        return $por; 
    }  ),
    //fin

    


    array( 'db' => "SUM(IF(h.hor_nombre='HNF',ld.lid_hedo,0))", 'dt' => 'hedo', 'as' => 'hedo', 'field' => 'hedo', 'formatter'=>function($d,$row){
        $are = $row[23];
        $tot  = ($are>0)? $d-$are: $d;
        $ext = $tot +  $row[18] + $row[19] + $row[17];
        return $tot;
    } ), //15
    array( 'db' => "sum(IF(h.hor_nombre='HDF',ld.lid_hdf,0))", 'dt' => 'hdf', 'as' => 'hdf', 'field' => 'hdf' ), //16
    array( 'db' => "sum(IF(h.hor_nombre='HEDF',ld.lid_hedf,0))", 'dt' => 'hedf', 'as' => 'hedf', 'field' => 'hedf' ), //17
    array( 'db' => "sum(IF(h.hor_nombre='HENO',ld.lid_heno,0))", 'dt' => 'heno', 'as' => 'heno', 'field' => 'heno' ), //18
    array( 'db' => "sum(IF(h.hor_nombre='HENF',ld.lid_henf,0))", 'dt' => 'henf', 'as' => 'henf', 'field' => 'henf' ), //19
    array( 'db' => "sum(IF(h.hor_nombre='RN',ld.lid_rn,0))", 'dt' => 'rn', 'as' => 'rn', 'field' => 'rn' ), //20
    array( 'db' => "sum(IF(h.hor_nombre='HNF',ld.lid_hnf,0))", 'dt' => 'hnf', 'as' => 'hnf', 'field' => 'hnf' ), //21
    array( 'db' => "sum(IF(h.hor_nombre='RD',ld.lid_rd,0))", 'dt' => 'rd', 'as' => 'rd', 'field' => 'rd' ), //22
    array( 'db' => "sum(IF(h.hor_nombre='ARE',ld.lid_permisos,0))", 'dt' => 'are', 'as' => 'are', 'field' => 'are' ), //revisar calculo ar   //23
    /*



    array( 'db' => "sum(ld.lid_hedo)", 'dt' => 'thedo', 'as' => 'thedo', 'field' => 'thedo', 'formatter'=>function($d, $row){
        $vrhor = $row[4];
        $are = $row[23];
        $tot  = ($are>0)? $d-$are: $d;
        $tothor = ($row[6] * $vrhor) * $tot;
        return $tothor;
    } ),
    array( 'db' => "sum(ld.lid_hdf)", 'dt' => 'thdf', 'as' => 'thdf', 'field' => 'thdf','formatter'=>function($d,$row){
        $vrhor = $row[4];
        $tothor = ($row[7]* $vrhor) * $d;
        return $tothor;
    
    } ),
    array( 'db' => "sum(ld.lid_hedf)", 'dt' => 'thedf', 'as' => 'thedf', 'field' => 'thedf','formatter'=>function($d,$row){
        $vrhor = $row[4];
        $tothor = ($row[8]* $vrhor) * $d;
        return $tothor;    
    }  ),
    array( 'db' => "sum(ld.lid_heno)", 'dt' => 'theno', 'as' => 'theno', 'field' => 'theno','formatter'=>function($d,$row){
        $vrhor = $row[4];
        $tothor = ($row[9]* $vrhor) * $d;
        return $tothor;    
    }  ),
    array( 'db' => "sum(ld.lid_henf)", 'dt' => 'thenf', 'as' => 'thenf', 'field' => 'thenf' ,'formatter'=>function($d,$row){
        $vrhor = $row[4];
        $tothor = ($row[10]* $vrhor) * $d;
        return $tothor;    
    } ),
    array( 'db' => "sum(ld.lid_rn)", 'dt' => 'trn', 'as' => 'trn', 'field' => 'trn' ,'formatter'=>function($d,$row){
        $vrhor = $row[4];
        $tothor = ($row[11]* $vrhor) * $d;
        return $tothor;    
    } ),
    array( 'db' => "sum(ld.lid_hnf)", 'dt' => 'thnf', 'as' => 'thnf', 'field' => 'thnf','formatter'=>function($d,$row){
        $vrhor = $row[4];
        $tothor = ($row[12]* $vrhor) * $d;
        return $tothor;    
    }  ),
    array( 'db' => "sum(ld.lid_rd)", 'dt' => 'trd', 'as' => 'trd', 'field' => 'trd' ,'formatter'=>function($d,$row){
        $vrhor = $row[4];
        $tothor = ($row[13]* $vrhor) * $d;
        return $tothor;    
    } ),
    array( 'db' => "sum(ld.lid_permisos)", 'dt' => 'tare', 'as' => 'tare', 'field' => 'tare','formatter'=>function($d,$row){
        $vrhor = $row[4];
        $tothor = ($row[14]* $vrhor) * $d;
        return $tothor;    
    }  ),//revisar calculo are

    array( 'db' => "sum(ld.lid_permisos)", 'dt' => 'total', 'as' => 'total', 'field' => 'total','formatter'=>function($d,$row){
        $vrhor = $row[4];
        $are = $row[23];
        $hedo  = ($are>0)? $row[15]-$are: $row[15];
        $total = ($vrhor * $row[6]) * $hedo;
        $total+= ($vrhor * $row[7]) * $row[16];        
        $total+= ($vrhor * $row[8]) * $row[17];               
        $total+= ($vrhor * $row[9]) * $row[18];               
        $total+= ($vrhor * $row[10]) * $row[19];               
        $total+= ($vrhor * $row[11]) * $row[20];               
        $total+= ($vrhor * $row[12]) * $row[21];                       
        $total+= ($vrhor * $row[13]) * $row[22];                       
        $total+= ($vrhor * $row[14]) * $row[23];
        return $total;
    } ),//revisar calculo are
    array( 'db' => "sum(ld.lid_hedo)", 'dt' => 'horasgeneradas', 'as' => 'horasgeneradas', 'field' => 'horasgeneradas', 'formatter'=>function($d,$row){
        $are = $row[23];
        $tot  = ($are>0)? $d-$are: $d;

        $ext = $tot +  $row[18] + $row[19] + $row[17];

        return $ext;
    } ),*/
);
$sql_details = array(
	'user' => 'usrpavashtg',
	'pass' => '9A12)WHFy$2p4v4s',
	'db'   => 'bdpotenco',
	'host' => 'localhost'
);

require( '../../data/ssp.class.php' );

$extraWhere=" (u.usu_clave_int  in(".$emp1.")  OR '".$emp."' IS NULL OR '".$emp."' = '') and u.est_clave_int in(1)";
if($des and $has){
    $extraWhere .= " and (ld.lid_fecha BETWEEN '".$des."' AND '".$has."')";
}
$groupBy = 'Clave, Nombre, Cedula';
$with = '';
$joinQuery = "FROM tbl_liquidar l JOIN tbl_liquidar_dias ld ON ld.liq_clave_int = l.liq_clave_int JOIN tbl_usuarios u ON u.usu_clave_int = l.usu_clave_int JOIN tbl_liquidar_horas lh on lh.liq_clave_int = l.liq_clave_int JOIN tbl_horas h ON lh.hor_clave_int = h.hor_clave_int ";
$having = "";
echo json_encode(SSP::simple($_POST, $sql_details, $table, $primaryKey, $columns, $joinQuery, $extraWhere, $groupBy,$with, $having));