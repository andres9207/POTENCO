<?php
session_start();
include '../../data/db.config.php';
include('../../data/conexion.php');
error_reporting(0);
// cookie que almacena el numero de identificacion de la persona logueada
$usuario   = $_COOKIE['usuarioPo'];
$idUsuario = $_COOKIE['idusuarioPo'];
$perfil    = $_SESSION["perfilPo"];

include '../../data/validarpermisos.php';
setlocale(LC_TIME, 'es_CO.UTF-8'); 
date_default_timezone_set('America/Bogota');
$fecha  = date("Y/m/d H:i:s");
$opcion = $_POST['opcion'];

$p117 =  isset($permisosUsuario[117]) ?? 0;
$p118 =  isset($permisosUsuario[118]) ?? 0;
$p120 =  isset($permisosUsuario[120]) ?? 0;

if($opcion=="NUEVO" || $opcion=="EDITAR")
{
    if($opcion=="NUEVO" and $p117<=0)
    {
        echo "NO POSEE PERMISOS PARA CREAR CENTROS DE COSTOS";
    }
    else if($opcion=="EDITAR" and $p118<=0)
    {
        echo "NO POSEE PERMISOS PARA MODIFICAR CENTROS DE COSTOS";
    }
    else
    {
    $id = $_POST['id'] ?? 0;
    $sql = "SELECT cen_clave_int, cen_codigo, cen_nombre, cen_membrete, cen_folder_id 
    FROM tbl_cencos 
    WHERE cen_clave_int = :id";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    $dat = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($dat) {
    $nom = $dat['cen_nombre'];
    $cod = $dat['cen_codigo'];
    $mem = $dat['cen_membrete'];
    $fol = $dat['cen_folder_id'];
    }
   
    ?>
    <form id="frmcencos" name="frmcencos" action="_" method="post" enctype="multipart/form-data" class="form-horizontal" data-parsley-validate="">
        <div class="row">
            <div class="col-md-6">
                <label for="txtnombre">Nombre:</label>
                <input type="text" name="txtnombre" id="txtnombre" class="form-control form-control-sm" required data-parsley-error-message="Ingresar nombre del centro de costos" value="<?php echo $nom;?>" data-parsley-group='["grcenco"]'>
            </div>
            <div class="col-md-6">
                <label for="txtcodigo">Código:</label>
                <input type="text" name="txtcodigo" id="txtcodigo" class="form-control form-control-sm" required  value="<?php echo $cod;?>" data-parsley-group='["grcenco"]' data-parsley-type="integer" minlength="14">
            </div>
            <div class="col-md-4 hide">
                <label for="txtnombre">Membrete:</label>
                <input type="file" name="txtmembrete" id="txtmembrete" class="form-control form-control-sm dropify"  onChange="setpreview('rutamembrete','txtmembrete','frmcencos')" data-default-file="<?php echo $mem;?>" data-allowed-file-extensions="jpg png" >
                <span id="rutamembrete" data-rutaa="<?php echo $rutaa;?>"></span>
            </div>
        </div>
        <?php if($id>0)
        {
            if($p120>0)
            {
            ?>

            <div class="row align-items-end hide">
                <div class="col-md-5">
                    <label for="">Nombre de la labor</label>
                    <input type="text" name="txtnombrelabor" id="txtnombrelabor" class="form-control form-control-sm" data-parsley-group='["grlabor"]' required data-parsley-error-message="Ingresar nombre de la labor">
                </div>
                <div class="col-md-5">
                    <label for="">Descripción</label>
                    <textarea name="txtdescripcionlabor" id="txtdescripcionlabor" cols="30" rows="1" class="form-control form-control-sm" data-parsley-group='["grlabor"]' required data-parsley-error-message="Ingresar descripción de la labor"></textarea>
                </div>
                <div class="col-md-2">
                    <a class="btn btn-outline-success btn-sm" onclick="CRUDCENCOS('AGREGARLABOR','<?PHP echo $id;?>')">Agregar <i class="fas fa-plus"></i></a>
                </div>
            </div>
            <?php
            }
            ?>
            <div class="row mt-1 hide">
                <div class="col-md-12 table-responsive">
                    <table id="tbLabores" class="table table-striped" data-id="<?php echo $id;?>" style="width:100%">
                        <thead>
                            <tr>
                                <th class="dt-head-center">Labor</th>
                                <th class="dt-head-center">Descripción</th>
                                <th class="dt-head-center" style="width:20px"></th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <th></th>
                                <th></th>
                                <th></th>
                               
                            </tr>
                        </tfoot>
                        
                    </table>
                    <script src="jsdatatable/cencos/jslistalabores.js?<?php echo time();?>"></script>
                </div>
            </div>
            <?php
        }
        ?>
    </form>
    <?php echo "<script>INICIALIZARCONTENIDO();</script>";
    }
}
else if ($opcion == "GUARDAR") {
    $id = $_POST['id'];
    $nom = $_POST['nom'];
    $cod = $_POST['cod'];
    $ruta = $_POST['ruta'];
    $rutaa = $_POST['rutaa'];
    $trozos = explode(".", $ruta);
    $rutaold = "../../" . $ruta;
    $extension = end($trozos);
    $time = time();

    // Verificar duplicado
    $sqlCheck = "SELECT 1 FROM tbl_cencos WHERE cen_nombre = :nom AND est_clave_int != 3";
    $stmtCheck = $conn->prepare($sqlCheck);
    $stmtCheck->bindParam(':nom', $nom);
    $stmtCheck->execute();
    $num = $stmtCheck->rowCount();

    if ($num > 0) {
        $res = "error";
        $msn = "Ya hay un centro de costo con el nombre ingresado. Verificar";
    } else {
        if ($id > 0) {
            // UPDATE
            $sql = "UPDATE tbl_cencos 
                    SET cen_nombre = :nom, cen_codigo = :cod, 
                        cen_usu_actualiz = :usuario, cen_fec_actualiz = :fecha 
                    WHERE cen_clave_int = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':nom', $nom);
            $stmt->bindParam(':cod', $cod);
            $stmt->bindParam(':usuario', $usuario);
            $stmt->bindParam(':fecha', $fecha);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);

            if ($stmt->execute()) {
                // Procesar membrete si viene ruta
                if (!empty($ruta) && rename($rutaold, "../../modulos/cencos/membretes/$id-$time.$extension")) {
                    $rutanew = "modulos/cencos/membretes/$id-$time.$extension";

                    $sqlUpMemb = "UPDATE tbl_cencos SET cen_membrete = :memb WHERE cen_clave_int = :id";
                    $stmtMemb = $conn->prepare($sqlUpMemb);
                    $stmtMemb->bindParam(':memb', $rutanew);
                    $stmtMemb->bindParam(':id', $id);
                    $stmtMemb->execute();

                    if (file_exists("../../$rutaa")) {
                        unlink("../../$rutaa");
                    }
                }

                $res = "ok";
                $msn = "Centro de Costos modificado correctamente";
            } else {
                $res = "error";
                $msn = "Surgió un error al modificar centro de costos. Error BD";
            }
        } else {
            // INSERT
            $sql = "INSERT INTO tbl_cencos (cen_nombre, cen_codigo, cen_creacion, cen_usu_actualiz, cen_fec_actualiz) 
                    VALUES (:nom, :cod, :creador, :usuario, :fecha)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':nom', $nom);
            $stmt->bindParam(':cod', $cod);
            $stmt->bindParam(':creador', $idUsuario); // quien crea
            $stmt->bindParam(':usuario', $usuario);
            $stmt->bindParam(':fecha', $fecha);

            if ($stmt->execute()) {
                $id = $conn->lastInsertId();

                if (!empty($ruta) && rename($rutaold, "../../modulos/cencos/membretes/$id-$time.$extension")) {
                    $rutanew = "modulos/cencos/membretes/$id-$time.$extension";

                    $sqlUpMemb = "UPDATE tbl_cencos SET cen_membrete = :memb WHERE cen_clave_int = :id";
                    $stmtMemb = $conn->prepare($sqlUpMemb);
                    $stmtMemb->bindParam(':memb', $rutanew);
                    $stmtMemb->bindParam(':id', $id);
                    $stmtMemb->execute();

                    if (file_exists("../../$rutaa")) {
                        unlink("../../$rutaa");
                    }
                }

                $res = "ok";
                $msn = "Nuevo centro de costos guardado correctamente";
            } else {
                $res = "error";
                $msn = "Surgió un error al guardar centro de costos. Verificar";
            }
        }
    }

    $datos[] = ["res" => $res, "id" => $id, "msn" => $msn];
    echo json_encode($datos);
}

else if ($opcion == "AGREGARLABOR") {
    $cen   = $_POST['id'];
    $labor = $_POST['labor'];
    $des   = $_POST['des'];

    // Verificar si ya existe una labor con ese nombre en el mismo centro de costos
    $sqlVeri = "SELECT 1 FROM tbl_labores WHERE lab_nombre = :labor AND cen_clave_int = :cen";
    $stmtVeri = $conn->prepare($sqlVeri);
    $stmtVeri->bindParam(':labor', $labor);
    $stmtVeri->bindParam(':cen', $cen, PDO::PARAM_INT);
    $stmtVeri->execute();

    if ($stmtVeri->rowCount() > 0) {
        $res = "error";
        $msn = "Ya hay una labor con el nombre ingresado. Verificar";
    } else {
        // Insertar nueva labor
        $sqlIns = "INSERT INTO tbl_labores (
                        lab_nombre, lab_usu_actualiz, lab_fec_actualiz, 
                        cen_clave_int, lab_creacion, lab_descripcion
                   ) VALUES (
                        :labor, :usuario, :fecha, :cen, :creador, :descripcion
                   )";

        $stmtIns = $conn->prepare($sqlIns);
        $stmtIns->bindParam(':labor', $labor);
        $stmtIns->bindParam(':usuario', $usuario);
        $stmtIns->bindParam(':fecha', $fecha);
        $stmtIns->bindParam(':cen', $cen, PDO::PARAM_INT);
        $stmtIns->bindParam(':creador', $idUsuario, PDO::PARAM_INT);
        $stmtIns->bindParam(':descripcion', $des);

        if ($stmtIns->execute()) {
            $ido = $conn->lastInsertId();
            $res = "ok";
            $msn = "Labor guardada correctamente";
        } else {
            $res = "error";
            $msn = "Surgió un error al guardar la labor. Verificar";
        }
    }

    $datos[] = ["res" => $res, "msn" => $msn, "ido" => $ido ?? null];
    echo json_encode($datos);
}

else if($opcion=="MODIFICARLABOR")
{
    $id = $_POST['id'];
    $nom = $_POST['nom'];
    $doc = $_POST['doc'];
}
else if($opcion=="FILTROS")
{
    ?>
    <div class="col-md-3">
        <label for="busnombre">Nombre:</label>
        <input type="text" name="busnombre" id="busnombre" class="form-control form-control-sm">
    </div>
    <div class="col-md-3">
        <label for="buscodigo">Codigo:</label>
        <input type="text" name="buscodigo" id="buscodigo" class="form-control form-control-sm">
    </div>
    <div class="col-md-3">
        <a class="btn btn-success btn-sm" onclick="CRUDCENCOS('LISTACENCOS','')">Buscar <i class="fas fas-search"></i></a>
    </div>
    <script>
        $(document).ready(function(e) {
            $('#busnombre').on('keypress',function (e) {
                if(e.which==13) {
                    CRUDCENCOS('LISTACENCOS','')
                }
            });
            $('#buscodigo').on('keypress',function (e) {
                if(e.which==13) {
                    CRUDCENCOS('LISTACENCOS','')
                }
            });
        })
    </script>
    <?php
    echo "<script>CRUDCENCOS('LISTACENCOS','');</script>";
}
else if($opcion=="LISTACENCOS")
{
    ?>
    <table class="table table-bordered table-striped" id="tbCencos">
        <thead>
            <tr>
                <th class="dt-head-center bg-terracota">NOMBRE</th>
                <th class="dt-head-center bg-terracota">CODIGO</th>
                <th class="dt-head-center bg-terracota" style="width:20px">MEMBRETE</th>
                <th class="dt-head-center bg-terracota" style="width:20px"></th>
                <th class="dt-head-center bg-terracota" style="width:20px"></th>
            </tr>
        </thead>
        <tfoot class="hide">
            <tr>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>
            </tr>
        </tfoot>        
    </table>
    <script src="jsdatatable/cencos/jslistacencos.js?<?php echo time();?>"></script>
    <?php
}
else if ($opcion == "ELIMINAR") {
    $id = $_POST['id'];

    $sql = "UPDATE tbl_cencos 
            SET est_clave_int = 3, 
                cen_usu_actualiz = :usuario, 
                cen_fec_actualiz = :fecha 
            WHERE cen_clave_int = :id";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':usuario', $usuario);
    $stmt->bindParam(':fecha', $fecha);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        $res = "ok";
        $msn = "Centro de Costos eliminado correctamente";
    } else {
        $res = "error";
        $msn = "Surgió un error al eliminar centro de costos. Verificar";
    }

    $datos[] = ["res" => $res, "msn" => $msn];
    echo json_encode($datos);
}

else if ($opcion == "ELIMINARLABOR") {
    $id = $_POST['id'];

    $sql = "UPDATE tbl_labores 
            SET est_clave_int = 3, 
                lab_usu_actualiz = :usuario, 
                lab_fec_actualiz = :fecha 
            WHERE lab_clave_int = :id";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':usuario', $usuario);
    $stmt->bindParam(':fecha', $fecha);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        $res = "ok";
        $msn = "Labor eliminada correctamente";
    } else {
        $res = "error";
        $msn = "Surgió un error al eliminar labor. Verificar";
    }

    $datos[] = ["res" => $res, "msn" => $msn];
    echo json_encode($datos);
}

else if ($opcion == "CARGARLABORES") {
    $idcenco = $_POST['soc'];

    // Consulta con condición para incluir todos si $idcenco está vacío o null
    $sql = "SELECT lab_clave_int, lab_nombre, lab_descripcion 
            FROM tbl_labores 
            WHERE (cen_clave_int = :idcenco OR :idcenco = '' OR :idcenco IS NULL) 
              AND est_clave_int = 1";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':idcenco', $idcenco);
    $stmt->execute();
    $labores = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($labores) {
        foreach ($labores as $datob) {
            $idl = $datob['lab_clave_int'];
            $nom = $datob['lab_nombre'];
            $des = $datob['lab_descripcion'];

            $datos[] = [
                "res" => "si",
                "id"  => $idl,
                "nom" => $nom,
                "des" => $des
            ];
        }
    } else {
        $datos[] = [
            "res" => "no",
            "id"  => "",
            "nom" => "",
            "des" => ""
        ];
    }

    echo json_encode($datos);
}
