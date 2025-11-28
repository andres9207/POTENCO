<?php
session_start();
include '../../data/db.config.php';
include('../../data/conexion.php');
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
    $sql = "SELECT * FROM tbl_perfil WHERE prf_clave_int = :id LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    
    $dat = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($dat) {
        $nom = $dat['prf_nombre'];
        $est = $dat['est_clave_int'];
    }

    $sqlVentanas = "SELECT ven_clave_int, ven_descripcion FROM tbl_ventanas ORDER BY ven_descripcion ASC";
    $stmtVentanas = $conn->prepare($sqlVentanas);
    $stmtVentanas->execute();
    $ventanas = $stmtVentanas->fetchAll(PDO::FETCH_ASSOC);

    // Obtener todas las ventanas asociadas al perfil en una sola consulta
    $sqlAsociadas = "SELECT ven_clave_int FROM tbl_permisos_ventana WHERE prf_clave_int = :prf";
    $stmtAsociadas = $conn->prepare($sqlAsociadas);
    $stmtAsociadas->bindParam(':prf', $id, PDO::PARAM_INT);
    $stmtAsociadas->execute();
    $asociadas = $stmtAsociadas->fetchAll(PDO::FETCH_COLUMN, 0); // array con solo los IDs
    
    
    ?>
    <form  id="frmperfil" name="frmperfil" action="_" method="post" enctype="multipart/form-data" class="form-horizontal" data-parsley-validate="">
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
        <div class="col-md-12">
            <label>Acceso modulos:</label>
            <select multiple id="selventana" name="selventana" class="form-control selectpicker" data-actions-box="true">
            <?php foreach ($ventanas as $ventana): 
                $idven = $ventana['ven_clave_int'];
                $nomven = $ventana['ven_descripcion'];
                $selected = in_array($idven, $asociadas) ? 'selected' : '';
            ?>
                <option value="<?php echo $idven; ?>" <?php echo $selected; ?>>
                    <?php echo htmlspecialchars($nomven); ?>
                </option>
            <?php endforeach; ?>
            </select>
        </div>
    </div>
    <?php
    echo "<script>INICIALIZARCONTENIDO();</script>";
}
else if ($opcion == "GUARDAR") {
    $fecha = date("Y/m/d H:i:s");
    $nom = strtoupper($_POST['nombre']);
    $est = $_POST['est'];
    $ventanas = $_POST['ventanas'] ?? [];
    $res = "";
    $msn = "";

    // Verificar si ya existe el perfil
    $sqlVeri = "SELECT 1 FROM tbl_perfil WHERE prf_nombre = :nom AND est_clave_int != 3";
    $stmtVeri = $conn->prepare($sqlVeri);
    $stmtVeri->bindParam(':nom', $nom);
    $stmtVeri->execute();

    if ($stmtVeri->rowCount() > 0) {
        $res = "error";
        $msn = "Ya hay un perfil con el nombre ingresado. Verificar";
    } else {
        // Insertar nuevo perfil
        $sqlInsert = "INSERT INTO tbl_perfil (prf_nombre, est_clave_int, prf_usu_actualiz, prf_fec_actualiz) 
                      VALUES (:nom, :est, :usuario, :fecha)";
        $stmtInsert = $conn->prepare($sqlInsert);
        $stmtInsert->bindParam(':nom', $nom);
        $stmtInsert->bindParam(':est', $est);
        $stmtInsert->bindParam(':usuario', $usuario);
        $stmtInsert->bindParam(':fecha', $fecha);

        if ($stmtInsert->execute()) {
            $idperfil = $conn->lastInsertId();
            $res = "ok";
            $msn = "Nuevo perfil guardado correctamente";

            // Insertar ventanas si vienen
            if (!empty($ventanas)) {
                $sqlVent = "INSERT INTO tbl_permisos_ventana (prf_clave_int, ven_clave_int) VALUES ";
                $values = [];
                foreach ($ventanas as $idx => $venId) {
                    $values[] = "(:perfil, :ven$idx)";
                }
                $sqlVent .= implode(", ", $values);
                $stmtVent = $conn->prepare($sqlVent);

                $stmtVent->bindParam(':perfil', $idperfil, PDO::PARAM_INT);
                foreach ($ventanas as $idx => $venId) {
                    $stmtVent->bindValue(":ven$idx", $venId, PDO::PARAM_INT);
                }

                $stmtVent->execute();
            }
        } else {
            $res = "error";
            $msn = "Surgió un error al guardar perfil. Error BD";
        }
    }

    $datos[] = ["res" => $res, "msn" => $msn];
    echo json_encode($datos);
}

else if ($opcion == "GUARDAREDICION") {
    $fecha = date("Y/m/d H:i:s");
    $id = $_POST['id'];
    $nom = strtoupper($_POST['nombre']);
    $est = $_POST['est'];
    $ventanas = $_POST['ventanas'] ?? [];
    $res = "";
    $msn = "";

    // Validar nombre duplicado (excluyendo el mismo ID)
    $sqlVeri = "SELECT 1 FROM tbl_perfil 
                WHERE prf_nombre = :nom 
                  AND est_clave_int != 3 
                  AND prf_clave_int != :id";
    $stmtVeri = $conn->prepare($sqlVeri);
    $stmtVeri->bindParam(':nom', $nom);
    $stmtVeri->bindParam(':id', $id, PDO::PARAM_INT);
    $stmtVeri->execute();

    if ($stmtVeri->rowCount() > 0) {
        $res = "error";
        $msn = "Ya hay un perfil con el nombre ingresado. Verificar";
    } else {
        // Actualizar perfil
        $sqlUpdate = "UPDATE tbl_perfil 
                      SET prf_nombre = :nom,
                          est_clave_int = :est,
                          prf_usu_actualiz = :usuario,
                          prf_fec_actualiz = :fecha
                      WHERE prf_clave_int = :id";
        $stmt = $conn->prepare($sqlUpdate);
        $stmt->bindParam(':nom', $nom);
        $stmt->bindParam(':est', $est);
        $stmt->bindParam(':usuario', $usuario);
        $stmt->bindParam(':fecha', $fecha);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $res = "ok";
            $msn = "Perfil actualizado correctamente";

            // Eliminar todas las ventanas asociadas al perfil
            $sqlDel = "DELETE FROM tbl_permisos_ventana WHERE prf_clave_int = :id";
            $stmtDel = $conn->prepare($sqlDel);
            $stmtDel->bindParam(':id', $id, PDO::PARAM_INT);
            $stmtDel->execute();

            // Insertar nuevas ventanas si existen
            if (!empty($ventanas)) {
                $sqlVent = "INSERT INTO tbl_permisos_ventana (prf_clave_int, ven_clave_int) VALUES ";
                $values = [];
                foreach ($ventanas as $i => $venId) {
                    $values[] = "(:id, :ven$i)";
                }
                $sqlVent .= implode(", ", $values);
                $stmtVent = $conn->prepare($sqlVent);
                $stmtVent->bindParam(':id', $id, PDO::PARAM_INT);
                foreach ($ventanas as $i => $venId) {
                    $stmtVent->bindValue(":ven$i", $venId, PDO::PARAM_INT);
                }
                $stmtVent->execute();
            }
        } else {
            $res = "error";
            $msn = "Surgió un error al actualizar perfil. Verificar";
        }
    }

    $datos[] = ["res" => $res, "msn" => $msn];
    echo json_encode($datos);
}

else if ($opcion == "ELIMINAR") {
    $id = $_POST['id'];

    $sql = "UPDATE tbl_perfil 
            SET est_clave_int = 3, 
                prf_usu_actualiz = :usuario, 
                prf_fec_actualiz = :fecha 
            WHERE prf_clave_int = :id";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':usuario', $usuario);
    $stmt->bindParam(':fecha', $fecha);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        $res = "ok";
        $msn = "Perfil eliminado correctamente";
    } else {
        $res = "error";
        $msn = "Surgió un error al eliminar el perfil seleccionado. Verificar";
    }

    $datos[] = ["res" => $res, "msn" => $msn];
    echo json_encode($datos);
}

else if($opcion=="FILTROS")
{
    ?>
     <input type="text" class="form-control" id="busnombre" name="busnombre" placeholder="Buscar por nombre">
     <script>
        $(document).ready(function(e) {
            $('#busnombre').on('keypress',function (e) {
                if(e.which==13) {
                console.log("Buscar por perfil");
                    CRUDPERFIL('LISTAPERFILES','')
                }
            });
     
        });
    </script>
    <?php
    echo "<script>CRUDPERFIL('LISTAPERFILES','');</script>";
}
else if($opcion=="LISTAPERFILES")
{
    $id = $_POST['id'];
    ?>
    <div class="card">   
        <!-- /.card-header -->
        <div class="card-body">
            <div class="table-responsive">
            <table style="width:100%" id="tbperfiles" data-per = '<?php echo $id;?>' class="table table-bordered table-striped">
            <thead>
            <tr>
                <th class="dt-head-center bg-terracota" style="width:20px"></th>
                <th class="dt-head-center bg-terracota" style="width:20px"></th>
                <th class="dt-head-center bg-terracota" style="width:20px"></th>
                <th class="dt-head-center bg-terracota">Nombre</th>
                <th class="dt-head-center bg-terracota" style="width:50px">Estado</th>               
            </tr>
            </thead>           
            <tfoot>
            <tr>
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
    <script src="jsdatatable/perfiles/perfiles.js?<?PHP echo time();?>"></script>
    <?PHP
}
else if ($opcion == "ASIGNARPERMISOS") {
    $idu = $_POST['id'];

    // Obtener ventanas
    $sql = "SELECT ven_clave_int, ven_descripcion FROM tbl_ventanas ORDER BY ven_descripcion ASC";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $ventanas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Inicializar arrays
    $idven = [];
    $nomventana = [];
    ?>

    <div class="row">
        <div class="col-7 col-sm-9">
            <!-- Tab panes -->
            <div class="tab-content" id="vert-tabs-right-tabContent">
                <?php
                foreach ($ventanas as $datve) {
                    $idve = $datve['ven_clave_int'];
                    $nomve = $datve['ven_descripcion'];
                    $idven[] = $idve;
                    $nomventana[] = $nomve;
                    ?>
                    <div class="tab-pane fade <?php echo ($idve == 1 ? "show active" : ""); ?>" 
                         id="<?php echo "ventana-" . $idve; ?>" 
                         role="tabpanel" 
                         aria-labelledby="vert-tabs-right-<?php echo $idve; ?>">
                        <?php echo htmlspecialchars($nomve); ?>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>

        <div class="col-5 col-sm-3">
            <!-- Nav tabs -->
            <div class="nav flex-column nav-tabs nav-tabs-right h-100" id="vert-tabs-right-tab" role="tablist" aria-orientation="vertical">
                <?php 
                foreach ($idven as $k => $idv) {
                    ?>
                    <a class="nav-link <?php echo ($idv == 1 ? "active" : ""); ?>"
                       id="vert-tabs-right-<?php echo $idv; ?>"
                       data-toggle="pill"
                       data-target="#ventana-<?php echo $idv; ?>"
                       role="tab"
                       aria-controls="ventana-<?php echo $idv; ?>"
                       aria-selected="true"
                       onclick="CRUDPERFIL('LISTAPERMISOS','<?php echo $idu; ?>','<?php echo $idv; ?>')">
                        <?php echo htmlspecialchars($nomventana[$k]); ?>
                    </a>
                    <?php
                }
                ?>
            </div>
        </div>
    </div>

    <?php
    echo "<script>CRUDPERFIL('LISTAPERMISOS','" . $idu . "','1');</script>";
}

else if ($opcion == "LISTAPERMISOS") {
    $idu = $_POST['id'];
    $ven = $_POST['ven'];

    // Obtener permisos del módulo (ventana)
    $sql = "SELECT per_clave_int, per_descripcion FROM tbl_permisos WHERE ven_clave_int = :ven";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':ven', $ven, PDO::PARAM_INT);
    $stmt->execute();
    $permisos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtener permisos ya asignados al perfil
    $sqlAsig = "SELECT per_clave_int FROM tbl_permisos_perfil WHERE prf_clave_int = :id";
    $stmtAsig = $conn->prepare($sqlAsig);
    $stmtAsig->bindParam(':id', $idu, PDO::PARAM_INT);
    $stmtAsig->execute();
    $permisosAsignados = $stmtAsig->fetchAll(PDO::FETCH_COLUMN, 0); // array de IDs de permisos

    ?>
    <div class="row">
        <?php if (count($permisos) > 0): ?>
            <?php foreach ($permisos as $permiso): 
                $idp = $permiso['per_clave_int'];
                $des = $permiso['per_descripcion'];
                $checked = in_array($idp, $permisosAsignados) ? 'checked' : '';
            ?>
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <ul class="list-group" style="list-style:none">
                        <li class="list-group-item list-group-item-action">
                            <label for="permiso-<?php echo $idp; ?>">
                                <input 
                                    type="checkbox" 
                                    class="flat" 
                                    id="permiso-<?php echo $idp; ?>" 
                                    onclick="CRUDPERFIL('GUARDARPERMISOS','<?php echo $idp; ?>','<?php echo $idu; ?>')" 
                                    <?php echo $checked; ?>
                                >
                                <?php echo htmlspecialchars($des); ?>
                            </label>
                        </li>
                    </ul>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class='col-md-12'>NO HAY PERMISOS CREADOS PARA ESTE MÓDULO</div>
        <?php endif; ?>
    </div>

    <script>
        INICIALIZARCONTENIDO();
    </script>
    <?php
}

else if ($opcion == "GUARDARPERMISOS") {
    $idp = $_POST['idp'];
    $idu = $_POST['idu'];

    // Verificar si ya existe
    $sqlVeri = "SELECT 1 FROM tbl_permisos_perfil WHERE per_clave_int = :idp AND prf_clave_int = :idu";
    $stmtVeri = $conn->prepare($sqlVeri);
    $stmtVeri->bindParam(':idp', $idp, PDO::PARAM_INT);
    $stmtVeri->bindParam(':idu', $idu, PDO::PARAM_INT);
    $stmtVeri->execute();

    if ($stmtVeri->rowCount() > 0) {
        // Eliminar permiso existente
        $sqlDel = "DELETE FROM tbl_permisos_perfil WHERE per_clave_int = :idp AND prf_clave_int = :idu";
        $stmt = $conn->prepare($sqlDel);
        $stmt->bindParam(':idp', $idp, PDO::PARAM_INT);
        $stmt->bindParam(':idu', $idu, PDO::PARAM_INT);
    } else {
        // Insertar nuevo permiso
        $sqlIns = "INSERT INTO tbl_permisos_perfil 
                   (per_clave_int, prf_clave_int, pep_usu_actualiz, pep_fec_actualiz) 
                   VALUES (:idp, :idu, :usuario, :fecha)";
        $stmt = $conn->prepare($sqlIns);
        $stmt->bindParam(':idp', $idp, PDO::PARAM_INT);
        $stmt->bindParam(':idu', $idu, PDO::PARAM_INT);
        $stmt->bindParam(':usuario', $usuario); // definido en el entorno global
        $stmt->bindParam(':fecha', $fecha);     // definido en el entorno global
    }

    if ($stmt->execute()) {
        $res = "ok";
        $msn = "";
    } else {
        $res = "error";
        $msn = "Surgió un error al modificar permiso";
    }

    $datos[] = ["res" => $res, "msn" => $msn];
    echo json_encode($datos);
}