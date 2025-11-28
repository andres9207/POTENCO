
<?php
session_start();
include '../../data/db.config.php';
include('../../data/conexion.php');
error_reporting(0);
// cookie que almacena el numero de identificacion de la persona logueada
$usuario   = $_COOKIE['usuarioPo'];
$idUsuario = $_COOKIE['idusuarioPo'];
$perfil    = $_SESSION["perfilPo"];
require_once "../../controladores/general.controller.php";
include '../../data/validarpermisos.php';
setlocale(LC_TIME, 'es_CO.UTF-8'); 
date_default_timezone_set('America/Bogota');
$fecha  = date("Y/m/d H:i:s");
$opcion = $_POST['opcion'];
$p7 =  isset($permisosUsuario[7]) ?? 0;
$p8 =  isset($permisosUsuario[8]) ?? 0;
// Crear Horarios

if($opcion=="NUEVO" || $opcion=="EDITAR")
{
    if($opcion=="NUEVO" and $p7<=0)
    {
        echo "NO POSEE PERMISOS PARA CREAR HORARIOS";
    }
    else if($opcion=="EDITAR" and $p8<=0)
    {
        echo "NO POSEE PERMISOS PARA MODIFICAR HORARIO";
    }
    else
    {
        $id = $_POST['id'];
        $gen = new General();
        $dat = $gen->editHorario($id);

        $nom = $dat['hor_nombre'];
        $lun = $dat['hor_1'];
        $mar = $dat['hor_2'];
        $mie = $dat['hor_3'];
        $jue = $dat['hor_4'];
        $vie = $dat['hor_5'];
        $sab = $dat['hor_6'];
        $dom = $dat['hor_7'];

    ?>
    <form id="frmhorarios" name="frmhorarios" action="_" method="post" enctype="multipart/form-data" class="form-horizontal" data-parsley-validate="">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Informacion Basica</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <label for="txtnombre">Nombre</label>
                        <input type="text" name="txtnombre" id="txtnombre" class="form-control form-control-sm" required data-parsley-error-message="Ingresar nombre del horario" value="<?php echo $nom;?>">
                    </div>                   
                </div>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Información Horas Dia</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2">
                        <label for="txtlunes">Lunes</label>
                        <input type="text" name="txtlunes" id="txtlunes" class="form-control form-control-sm" required  value="<?php echo $lun;?>" data-parsley-type="number" onkeypress="return validar_texto(event)">
                    </div>
                    <div class="col-md-2">
                        <label for="txtmartes">Martes</label>
                        <input type="text" name="txtmartes" id="txtmartes" class="form-control form-control-sm" required  value="<?php echo $mar;?>" data-parsley-type="number" onkeypress="return validar_texto(event)">
                    </div>
                    <div class="col-md-2">
                        <label for="txtmiercoles">Miercoles</label>
                        <input type="text" name="txtmiercoles" id="txtmiercoles" class="form-control form-control-sm" required  value="<?php echo $mie;?>" data-parsley-type="number" onkeypress="return validar_texto(event)">
                    </div>
                    <div class="col-md-2">
                        <label for="txtjueves">Jueves</label>
                        <input type="text" name="txtjueves" id="txtjueves" class="form-control form-control-sm" required  value="<?php echo $jue;?>" data-parsley-type="number" onkeypress="return validar_texto(event)">
                    </div>
                    <div class="col-md-2">
                        <label for="txtviernes">Viernes</label>
                        <input type="text" name="txtviernes" id="txtviernes" class="form-control form-control-sm" required  value="<?php echo $vie;?>" data-parsley-type="number" onkeypress="return validar_texto(event)">
                    </div>
                    <div class="col-md-2">
                        <label for="txtsabado">Sabado</label>
                        <input type="text" name="txtsabado" id="txtsabado" class="form-control form-control-sm" required  value="<?php echo $sab;?>" data-parsley-type="number" onkeypress="return validar_texto(event)">
                    </div>
                    <div class="col-md-2">
                        <label for="txtdomingo">Domingo</label>
                        <input type="text" name="txtdomingo" id="txtdomingo" class="form-control form-control-sm" required  value="<?php echo $dom;?>" data-parsley-type="number" onkeypress="return validar_texto(event)">
                    </div>
                 </div>            
            </div>
        </div>   
        <?php if($id>0){ ?>
        <script> $('#frmhorarios').parsley().validate();</script>
        <?php } ?>    
       
    </form>
    <?php echo "<script>INICIALIZARCONTENIDO();</script>";
    }
}
else 
if ($opcion == "GUARDAR") {
    $id  = $_POST['id'] ?? 0;
    $nom = $_POST['nom'];
    $lun = $_POST['lun'];
    $mar = $_POST['mar'];
    $mie = $_POST['mie'];
    $jue = $_POST['jue'];
    $vie = $_POST['vie'];
    $sab = $_POST['sab'];
    $dom = $_POST['dom'];
    $res = "";
    $msn = "";

    // Validar duplicado
    $sqlVeri = "SELECT 1 FROM tbl_horarios 
                WHERE hor_nombre = :nom 
                AND est_clave_int != 3 
                AND hor_clave_int != :id";
    $stmtVeri = $conn->prepare($sqlVeri);
    $stmtVeri->bindParam(':nom', $nom);
    $stmtVeri->bindParam(':id', $id, PDO::PARAM_INT);
    $stmtVeri->execute();

    if ($stmtVeri->rowCount() > 0) {
        $res = "error";
        $msn = "Ya hay un horario con el nombre ingresado. Verificar";
    } else {
        if ($id > 0) {
            // UPDATE
            $sql = "UPDATE tbl_horarios 
                    SET hor_nombre = :nom, hor_1 = :lun, hor_2 = :mar, hor_3 = :mie, hor_4 = :jue,
                        hor_5 = :vie, hor_6 = :sab, hor_7 = :dom,
                        hor_usu_actualiz = :usuario, hor_fec_actualiz = :fecha
                    WHERE hor_clave_int = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        } else {
            // INSERT
            $sql = "INSERT INTO tbl_horarios (
                        hor_nombre, hor_1, hor_2, hor_3, hor_4, hor_5, hor_6, hor_7, 
                        hor_usu_actualiz, hor_fec_actualiz, hor_creacion
                    ) VALUES (
                        :nom, :lun, :mar, :mie, :jue, :vie, :sab, :dom,
                        :usuario, :fecha, :creador
                    )";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':creador', $idUsuario, PDO::PARAM_INT);
        }

        // Enlazar valores comunes
        $stmt->bindParam(':nom', $nom);
        $stmt->bindParam(':lun', $lun);
        $stmt->bindParam(':mar', $mar);
        $stmt->bindParam(':mie', $mie);
        $stmt->bindParam(':jue', $jue);
        $stmt->bindParam(':vie', $vie);
        $stmt->bindParam(':sab', $sab);
        $stmt->bindParam(':dom', $dom);
        $stmt->bindParam(':usuario', $usuario);
        $stmt->bindParam(':fecha', $fecha);

        if ($stmt->execute()) {
            if ($id == 0) {
                $id = $conn->lastInsertId();
                $msn = "Nuevo horario guardado correctamente";
            } else {
                $msn = "Horario modificado correctamente";
            }
            $res = "ok";
        } else {
            $res = "error";
            $msn = $id > 0 
                ? "Surgió un error al modificar horario. Error BD" 
                : "Surgió un error al guardar horario. Verificar";
        }
    }

    $datos[] = ["res" => $res, "id" => $id, "msn" => $msn];
    echo json_encode($datos);
}

else if($opcion=="FILTROS")
{
    ?>
    <div class="col-md-3">
        <label for="busnombre">Nombre:</label>
        <input type="text" name="busnombre" id="busnombre" class="form-control form-control-sm">
    </div>
    <div class="col-md-3">
        <a class="btn btn-success btn-sm text-white" onclick="CRUDHORARIOS('LISTAHORARIOS','')">Buscar <i class="fas fas-search"></i></a>
    </div>
    <script>
        $(document).ready(function(e) {
            $('#busnombre').on('keypress',function (e) {
                if(e.which==13) {
                    CRUDHORARIOS('LISTAHORARIOS','')
                }
            });
        })
    </script>
    <?php
    echo "<script>INICIALIZARCONTENIDO();</script>";
    echo "<script>CRUDHORARIOS('LISTAHORARIOS','');</script>";
}
else if($opcion=="LISTAHORARIOS")
{
    ?>
     <div class="card">   
        <!-- /.card-header -->
        <div class="card-body">
            <div class="table-responsive  table-responsive-sm table-responsive-md table-table-responsive-lg">
            <table class="table table-bordered" id="tbHorarios" style="width:100%" >
                <thead>
                    <tr>
                        <th class="dt-head-center bg-terracota" style="width:20px" rowspan="2"></th>
                        <th class="dt-head-center bg-terracota" style="width:20px" rowspan="2"></th>
                        <th class="dt-head-center bg-terracota" rowspan="2">Nombre</th>
                        <th class="dt-head-center bg-terracota" colspan="7">Cant. Horas Laboradas por dia</th>                        
                    </tr>
                    <tr>
                        <th class="dt-head-center bg-terracota">Lunes</th>
                        <th class="dt-head-center bg-terracota">Martes</th>
                        <th class="dt-head-center bg-terracota">Miercoles</th>
                        <th class="dt-head-center bg-terracota">Jueves</th>
                        <th class="dt-head-center bg-terracota">Viernes</th>
                        <th class="dt-head-center bg-terracota">Sabado</th>
                        <th class="dt-head-center bg-terracota">Domingo</th>                        
                    </tr>
                </thead>
                <tfoot>
                    <tr>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                        <th></th>
                    </tr>
                </tfoot>        
            </table>
            </div>
        </div>
     </div>
    <script src="jsdatatable/horarios/jshorarios.js?<?php echo time();?>"></script>
    <?php
}
else if ($opcion == "ELIMINAR") {
    $id = $_POST['id'];
    $sql = "UPDATE tbl_horarios 
            SET est_clave_int = 3, 
                hor_usu_actualiz = :usuario, 
                hor_fec_actualiz = :fecha 
            WHERE hor_clave_int = :id";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':usuario', $usuario);
    $stmt->bindParam(':fecha', $fecha);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        $res = "ok";
        $msn = "Horario eliminado correctamente";
    } else {
        $res = "error";
        $msn = "Surgió un error al eliminar horario. Verificar";
    }

    $datos[] = ["res" => $res, "msn" => $msn];
    echo json_encode($datos);
}
