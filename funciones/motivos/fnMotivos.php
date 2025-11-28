<?php
include '../../data/db.config.php';
include('../../data/conexion.php');
session_start();
error_reporting(0);

//$login     = isset($_SESSION['persona']);
// cookie que almacena el numero de identificacion de la persona logueada
$usuario   = $_COOKIE['usuarioPo'];
$idUsuario = $_COOKIE['idusuarioPo'];
$perfil    = $_SESSION["perfilPo"];

//include '../../data/validarpermisos.php';
setlocale(LC_TIME, 'es_CO.UTF-8'); 
date_default_timezone_set('America/Bogota');
$fecha  = date("Y/m/d H:i:s");

$opcion = $_POST['opcion'];
if($opcion == "NUEVO" || $opcion=="EDITAR")
{
    $id = $_POST['id'];
    $condatos = mysqli_query($conectar, "select * from tbl_motivos where mot_clave_int = '".$id."' limit 1");
    $dat   = mysqli_fetch_array($condatos);
    $nom   = $dat['mot_nombre'];
    $est   = $dat['est_clave_int'];
    ?>
    <form  id="frmmotivos" name="frmmotivos" action="_" method="post" enctype="multipart/form-data" class="form-horizontal" data-parsley-validate="">
        <div class="form-group row">
        
        <div class="col-md-8">
            Nombre:
            <input type = "text" class="form-control form-control-sm" id="txtnombre" required value="<?php echo $nom;?>" data-parsley-error-message="Ingresar nombre">
        </div>
        <div class="col-md-4">
            Estado:<br>
            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                <label class="btn btn-success <?php if($est!=2){ echo "active"; }?>">
                <input type="radio" name="radestado" id="radestado1" autocomplete="off" <?php if($est!=2){ echo "checked"; }?> value="1"> Activo
                </label>
                <label class="btn btn-danger <?php if($est==2){ echo "active"; }?>">
                <input type="radio" name="radestado" id="radestado2" autocomplete="off" value="2" <?php if($est==2){ echo "checked"; }?>> Inactivo
                </label>
            </div>
        </div>
    </div>
    <?php
    echo "<script>INICIALIZARCONTENIDO();</script>";
}
else if($opcion=="GUARDAR")
{
    $fecha=date("Y/m/d H:i:s");
    $nom = strtoupper($_POST['nombre']);
    $est = $_POST['est'];

    $veri = mysqli_query($conectar, "SELECT * FROM tbl_motivos where mot_nombre = '".$nom."' and est_clave_int!=3");
    $numv = mysqli_num_rows($veri);
    
    if ($numv > 0){
        $res = "error";
        $msn = "Ya hay un motivo con el nombre ingresado. Verificar";
    }
    else
    { 
        $con = mysqli_query($conectar,"INSERT INTO tbl_motivos(mot_nombre,est_clave_int,mot_usu_actualiz,mot_fec_actualiz) VALUES ('".$nom."','".$est."','".$usuario."','".$fecha."')");
        if($con > 0)
        {     
            $idperfil = mysqli_insert_id($conectar);
            $res = 'ok';
            if($ventanas!="")
            {
                $ins = mysqli_query($conectar,"INSERT INTO tbl_permisos_ventana(mot_clave_int,ven_clave_int) SELECT '".$idperfil."',ven_clave_int FROM tbl_ventanas WHERE ven_clave_int in(".$ventanas.")");
            }
            $msn = "Nuevo motivo guardado correctamente";
        }
        else
        {
            $res =  'error';         
            $rutanew = "";
            $msn = "Surgió un error al guardar motivo. Error BD";
        }
    };
    $datos[] = array("res"=>$res,"msn"=>$msn);
    echo json_encode($datos);
}
else if ($opcion=="GUARDAREDICION")
{
    $fecha=date("Y/m/d H:i:s");
    $id = $_POST['id'];
    $nom = strtoupper($_POST['nombre']);
    $est = $_POST['est'];
    $veri = mysqli_query($conectar, "SELECT * FROM tbl_motivos where mot_nombre = '".$nom."' and est_clave_int!=3 and mot_clave_int !='".$id."'");
    $numv = mysqli_num_rows($veri);
    
    if ($numv > 0){
        $res = "error";
        $msn = "Ya hay un motivo con el nombre ingresado. Verificar";
    }
    else
    { 
        $con = mysqli_query($conectar,"UPDATE tbl_motivos SET mot_nombre='".$nom."',est_clave_int='".$est."',mot_usu_actualiz='".$usuario."',mot_fec_actualiz='".$fecha."' WHERE mot_clave_int='".$id."' ");    

        if($con > 0)
        {                  
            $res =  'ok';
            $msn = "Motivo actualizado correctamente";
           
         }
         else
         {
            $res =  'error';         
            $rutanew = "";
            $msn = "Surgió un error al actualizar motivo. Verificar";
         }
    }
   $datos[] = array("res"=>$res,"msn"=>$msn);
   echo json_encode($datos);
}
else if ($opcion=="ELIMINAR")
{
    $id = $_POST['id'];
     
    $con = mysqli_query($conectar,"UPDATE tbl_motivos SET est_clave_int=3,mot_usu_actualiz='".$usuario."',mot_fec_actualiz='".$fecha."' WHERE mot_clave_int= '".$id."'");
    
    if($con>0)
    {
        $res = "ok";
        $msn = "Motivo eliminado correctamente";
    }
    else 
    {
        $res = "error";
        $msn = "Surgió un error al eliminar el motivo seleccionado. Verificar";
    } 
   
    $datos[] = array( "res"=> $res, "msn"=> $msn);
    echo json_encode($datos);
}
else if($opcion=="LISTAMOTIVOS")
{
    $id = $_POST['id'];
    ?>
    <div class="card">   
        <!-- /.card-header -->
        <div class="card-body">
            <div class="table-responsive">
            <table style="width:100%" id="tbmotivos" data-per = '<?php echo $id;?>' class="table table-bordered table-striped">
            <thead>
            <tr>
                
                <th class="dt-head-center bg-blue">Nombre</th>
                <th class="dt-head-center bg-blue" style="width:50px">Estado</th>
                <th class="dt-head-center bg-blue" style="width:80px"></th>
            </tr>
            </thead>
            <tbody>
            </tbody>
            <tfoot>
            <tr>
                <th></th>
                <th></th>
                <th></th>
            </tr>
            </tfoot>
            </table>
            </div>
        </div>
    </div>
    <script src="jsdatatable/motivos/motivos.js?<?PHP echo time();?>"></script>
    <?PHP
}
