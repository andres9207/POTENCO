<?php
include '../../data/db.config.php';
include('../../data/conexion.php');
session_start();
error_reporting(0);
// cokie que almacena el numero de identificacion de la persona logueada
$usuario   = $_COOKIE['usuarioPo'];
$idUsuario = $_COOKIE['idusuarioPo'];
$perfil    = $_SESSION["perfilPo"];

include '../../data/validarpermisos.php';
setlocale(LC_TIME, 'es_CO.UTF-8'); 
date_default_timezone_set('America/Bogota');
$fecha  = date("Y/m/d H:i:s");
$opcion = $_POST['opcion'];

$p4 =  isset($permisosUsuario[4]) ?? 0;
$p5 =  isset($permisosUsuario[5]) ?? 0;

if($opcion=="NUEVO" || $opcion=="EDITAR")
{
    if($opcion=="NUEVO" and $p4<=0)
    {
        echo "NO POSEE PERMISOS PARA CREAR FESTIVOS";
    }
    else if($opcion=="EDITAR" and $p5<=0)
    {
        echo "NO POSEE PERMISOS PARA MODIFICAR FESTIVOS";
    }
    else
    {
        $id = $_POST['id'];
        $sql = "SELECT fes_clave_int, fes_descripcion, fes_fecha 
        FROM tbl_festivos 
        WHERE fes_clave_int = :id";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $dat = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($dat) {
            $fec = $dat['fes_fecha'];
            $des = $dat['fes_descripcion'];
        }

        ?>
        <form id="frmfestivos" name="frmfestivos"  enctype="multipart/form-data" class="form-horizontal" data-parsley-validate="">
            <div class="row">
                <div class="col-md-6">
                    <label for="txtnombre">Fecha:</label>
                    <input type="date" name="txtfecha" id="txtfecha" class="form-control form-control-sm" required data-parsley-error-message="Ingresar fecha" value="<?php echo $fec;?>" >
                </div>
                <div class="col-md-6">
                    <label for="txtdescripcion">Descripcion:</label>
                    <input type="text" name="txtdescripcion" id="txtdescripcion" class="form-control form-control-sm"  value="<?php echo $des;?>">
                </div>            
            </div>
        
        </form>
       <?php
    }
}
else if ($opcion == "GUARDAR") {
    $id  = $_POST['id'];
    $fec = $_POST['fec'];
    $des = $_POST['des'];
    $time = time();

    // Verificar si ya existe un festivo en esa fecha
    $sqlVeri = "SELECT 1 FROM tbl_festivos WHERE fes_fecha = :fec and fes_clave_int != :id";
    $stmtVeri = $conn->prepare($sqlVeri);
    $stmtVeri->bindParam(':fec', $fec);
    $stmtVeri->bindParam(':id', $id, PDO::PARAM_INT);
    $stmtVeri->execute();

    if ($stmtVeri->rowCount() > 0) {
        $res = "error";
        $msn = "Ya hay un festivo en la fecha ingresada. Verificar";
    } else {
        if ($id > 0) {
            // UPDATE
            $sqlUpdate = "UPDATE tbl_festivos 
                          SET fes_fecha = :fec, 
                              fes_descripcion = :des, 
                              fes_usu_actualiz = :usuario, 
                              fes_fec_actualiz = :fecha 
                          WHERE fes_clave_int = :id";
            $stmt = $conn->prepare($sqlUpdate);
            $stmt->bindParam(':fec', $fec);
            $stmt->bindParam(':des', $des);
            $stmt->bindParam(':usuario', $usuario);
            $stmt->bindParam(':fecha', $fecha);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        } else {
            // INSERT
            $sqlInsert = "INSERT INTO tbl_festivos 
                          (fes_fecha, fes_descripcion, fes_usu_actualiz, fes_fec_actualiz) 
                          VALUES (:fec, :des, :usuario, :fecha)";
            $stmt = $conn->prepare($sqlInsert);
            $stmt->bindParam(':fec', $fec);
            $stmt->bindParam(':des', $des);
            $stmt->bindParam(':usuario', $usuario);
            $stmt->bindParam(':fecha', $fecha);
        }

        if ($stmt->execute()) {
            $res = "ok";
            $msn = ($id > 0) ? "Festivo modificado correctamente" : "Festivo guardado correctamente";
        } else {
            $res = "error";
            $msn = "Surgió un error al " . ($id > 0 ? "modificar" : "guardar") . " festivo. Verificar";
        }
    }

    $datos[] = ["res" => $res, "id" => $id, "msn" => $msn];
    echo json_encode($datos);
}

else if($opcion=="FILTROS")
{
    ?>
    <div class="col-md-3">
        <label for="busfecha">Fecha:</label>
        <input type="date" name="busfecha" id="busfecha" class="form-control form-control-sm" onchange="CRUDFESTIVOS('LISTAFESTIVOS','')">
    </div>
    <div class="col-md-3">
        <label for="busdescripcion">Descripcion:</label>
        <input type="text" name="busdescripcion" id="busdescripcion" class="form-control form-control-sm">
    </div>
    <div class="col-md-3">
        <a class="btn btn-success btn-sm" onclick="CRUDFESTIVOS('LISTAFESTIVOS','')">Buscar <i class="fas fas-search"></i></a>
    </div>
    <script>
        $(document).ready(function(e) {
            $('#busfecha').on('change',function (e) {
                if(e.which==13) {
                    CRUDFESTIVOS('LISTAFESTIVOS','')
                }
            });
            $('#busdescripcion').on('keypress',function (e) {
                if(e.which==13) {
                    CRUDFESTIVOS('LISTAFESTIVOS','')
                }
            });
        })
    </script>
    <?php
    echo "<script>CRUDFESTIVOS('LISTAFESTIVOS','');</script>";
}
else if($opcion=="LISTAFESTIVOS")
{
    ?>
    <table class="table table-bordered table-striped" id="tbFestivos">
        <thead>
            <tr> 
                <th class="dt-head-center bg-terracota" style="width:20px"></th>
                <th class="dt-head-center bg-terracota" style="width:20px"></th>
                <th class="dt-head-center bg-terracota">Fecha</th>
                <th class="dt-head-center bg-terracota">Descripción</th>
            </tr>
        </thead>
        <tfoot class="hide">
            <tr>                
                <th></th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
        </tfoot>        
    </table>
    <script src="jsdatatable/festivos/jslistafestivos.js?<?php echo time();?>"></script>
    <?php
}
else if ($opcion == "ELIMINAR") {
    $id = $_POST['id'];

    $sql = "DELETE FROM tbl_festivos WHERE fes_clave_int = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        $res = "ok";
        $msn = "Festivo eliminado correctamente";
    } else {
        $res = "error";
        $msn = "Surgió un error al eliminar festivo. Verificar";
    }

    $datos[] = ["res" => $res, "msn" => $msn];
    echo json_encode($datos);
}
