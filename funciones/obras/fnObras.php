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
require_once "../../controladores/general.controller.php";
setlocale(LC_TIME, 'es_CO.UTF-8'); 
date_default_timezone_set('America/Bogota');
$fecha  = date("Y/m/d H:i:s");
$opcion = $_POST['opcion'];

$p1 =  isset($permisosUsuario[1]) ?? 0;
$p2 =  isset($permisosUsuario[2]) ?? 0;

if($opcion=="NUEVO" || $opcion=="EDITAR")
{
    if($opcion=="NUEVO" and $p1<=0)
    {
        echo "NO POSEE PERMISOS PARA CREAR OBRAS";
    }
    else if($opcion=="EDITAR" and $p2<=0)
    {
        echo "NO POSEE PERMISOS PARA MODIFICAR OBRAS";
    }
    else
    {
        $id = $_POST['id'];
        $gen = new General();
        $dat = $gen->editObra($id);        
        $nom        = $dat['obr_nombre'];      
        $cen        = $dat['obr_cencos'];
        $dir        = $dat['obr_encargado'];
        $vroperario = $dat['obr_vr_operador'];
        $vrsenalero = $dat['obr_vr_senalero'];
        $vrelevador = $dat['obr_vr_elevador'];        
        $vrmaquina  = $dat['obr_vr_maquina'];
        $hrmes      = $dat['obr_hr_mes'];
        $hrsemana   = $dat['obr_hr_semana'];
        $lun        = $dat['obr_lunes'];
        $mar        = $dat['obr_martes'];
        $mie        = $dat['obr_miercoles'];
        $jue        = $dat['obr_jueves'];
        $vie        = $dat['obr_viernes'];
        $sab        = $dat['obr_sabado'];
        $dom        = $dat['obr_domingo'];
        $est        = $dat['est_clave_int'];
        $aux        = $dat['obr_auxilio'];
        $vrauxilio  = $dat['obr_vr_auxilio'];
        $nompro     = $dat['obr_nom_proyecto'];
        $fecinicio  = $dat['obr_fec_inicio'];
        $ubi        = $dat['obr_ubicacion'];
        $operador   = $dat['obr_operador'];
        $senalero   = $dat['obr_senalero'];
        $contrato   = $dat['obr_contrato'];
   
    ?>
    <form id="frmobras" name="frmobras" action="_" method="post" enctype="multipart/form-data" class="form-horizontal" data-parsley-validate="">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Información Basica</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <label for="txtnombre">Nombre Cliente</label>
                        <input type="text" name="txtnombre" id="txtnombre" class="form-control form-control-sm" required data-parsley-error-message="Ingresar nombre del cliente" value="<?php echo $nom;?>">
                    </div>
                    <div class="col-md-4">
                        <label for="txtnombreproyecto">Nombre Proyecto</label>
                        <input type="text" name="txtnombreproyecto" id="txtnombreproyecto" class="form-control form-control-sm" required data-parsley-error-message="Ingresar nombre del proyecto" value="<?php echo $nompro;?>">
                    </div>
                    <div class="col-md-4">
                        <label for="txtfechainicio">Fecha Inicio Proyecto</label>
                        <input type="date" name="txtfechainicio" id="txtfechainicio" class="form-control form-control-sm" required data-parsley-error-message="Ingresar fecha inicio del proyecto" value="<?php echo $fecinicio;?>">
                    </div>
                    <div class="col-md-4">
                        <label for="txtcontrato">Numero Contrato</label>
                        <input type="text" name="txtcontrato" id="txtcontrato" class="form-control form-control-sm" required data-parsley-error-message="Ingresar número de contrato" value="<?php echo $contrato;?>">
                    </div>
                    <div class="col-md-4">
                        <label for="txtcenco">Centro de costos</label>
                        <input type="text" name="txtcenco" id="txtcenco" class="form-control form-control-sm" required  value="<?php echo $cen;?>"  minlength="14">
                    </div>
                    <div class="col-md-4">
                        <label for="txtubicacion">Ubicación</label>
                        <input type="text" name="txtubicacion" id="txtubicacion" class="form-control form-control-sm" required data-parsley-error-message="Ingresar ubicación del proyecto" value="<?php echo $fecinicio;?>">
                    </div>
                
                    <div class="col-md-4">                
                        <label for="seldirector">Director</label>
                        <select id="seldirector" name="seldirector" class="form-control form-control-sm selectpicker" required data-parsley-error-message="Seleccionar empleado" data-parsley-errors-container="#msn-error1" >
                        <option value=""></option>
                        <?php
                            $selectEmp = new General();
                            $selectEmp -> cargarEmpleados($dir,1);
                        ?>
                        </select>
                        <span id="msn-error1"></span>
                    </div>
                </div>
                <div class="row">
                     <div class="col-md-4">
                        Estado:<br>
                        <div class="btn-group btn-group-toggle" data-toggle="buttons">
                            <label class="btn btn-success <?php if($est!=2){ echo "active"; }?>">
                            <input type="radio" name="radestado" id="radestado1"  <?php if($est!=2){ echo "checked"; }?> value="1"> Activo
                            </label>
                            <label class="btn btn-danger <?php if($est==2){ echo "active"; }?>">
                            <input type="radio" name="radestado" id="radestado2"  value="2" <?php if($est==2){ echo "checked"; }?>> Inactivo
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Información Contrato</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <label for="txthoras">Horas Mes</label>
                        <input type="text" name="txthoras" id="txthoras" class="form-control form-control-sm" required  value="<?php echo $hrmes;?>" data-parsley-type="number" onkeypress="return validar_texto(event)" min="1">
                    </div>
                    <div class="col-md-3">
                        <label for="txtsemana">Horas Semana</label>
                        <input type="text" name="txtsemana" id="txtsemana" class="form-control form-control-sm" required  value="<?php echo $hrsemana;?>" data-parsley-type="number" onkeypress="return validar_texto(event)" min="1">
                    </div>
              
                    <div class="col-md-3">
                        <label for="txtvaloroperador">Vr.Hora Operador</label>
                        <input type="text" name="txtvaloroperador" id="txtvaloroperador" class="form-control form-control-sm currency" required  value="<?php echo $vroperario;?>" onkeypress="return validar_texto(event)">
                    </div>
                    <div class="col-md-3">
                        <label for="txtvalorsenalero">Vr.Hora Señalero</label>
                        <input type="text" name="txtvalorsenalero" id="txtvalorsenalero" class="form-control form-control-sm currency" required  value="<?php echo $vrsenalero;?>" onkeypress="return validar_texto(event)">
                    </div>
                    <div class="col-md-3">
                        <label for="txtvalorelevador">Vr.Hora Elevador</label>
                        <input type="text" name="txtvalorelevador" id="txtvalorelevador" class="form-control form-control-sm currency" required  value="<?php echo $vrelevador;?>" onkeypress="return validar_texto(event)">
                    </div>
                    <div class="col-md-3">
                        <label for="txtvalormaquina">Vr.Hora Maquina</label>
                        <input type="text" name="txtvalormaquina" id="txtvalormaquina" class="form-control form-control-sm currency" required  value="<?php echo $vrmaquina;?>" onkeypress="return validar_texto(event)">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <label for="txtoperador">Operador de Planta</label>
                        <input type="text" name="txtoperador" id="txtoperador" class="form-control form-control-sm currency"  value="<?php echo $operador;?>">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <label for="txtsenalero">Señalero de Planta</label>
                        <input type="text" name="txtsenalero" id="txtsenalero" class="form-control form-control-sm currency"  value="<?php echo $senalero;?>" >
                    </div>
                </div>
                <div class="row">
                     <div class="col-md-3">
                        <label for="">Aplica Auxilio Vehículo:</label><br>
                        <div class="btn-group btn-group-toggle" data-toggle="buttons">
                            <label class="btn btn-success <?php if($aux!=2){ echo "active"; }?>">
                            <input type="radio" name="radauxilio" id="radauxilio1"  <?php if($aux!=2){ echo "checked"; }?> value="1"> Si
                            </label>
                            <label class="btn btn-danger <?php if($aux==2){ echo "active"; }?>">
                            <input type="radio" name="radauxilio" id="radauxilio2"  value="2" <?php if($aux==2){ echo "checked"; }?>> No
                            </label>
                        </div>
                    </div>
                    <div class="col-md-3 <?php if($aux==2){ echo "hide"; }?>" id="divvrauxilio">
                        <label for="txtvalorauxilio">Vr.Auxilio</label>
                        <input type="text" name="txtvalorauxilio" id="txtvalorauxilio" class="form-control form-control-sm currency" required  value="<?php echo $vrauxilio;?>" onkeypress="return validar_texto(event)">
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
        <script> $('#frmobras').parsley().validate();</script>
        <?php } ?>  
        <script>
            $('input:radio[name=radauxilio]').on('change',function(e){
                var div = $('#divvrauxilio');
                var inp = $('#txtvalorauxilio');
                var v = $('input:radio[name=radauxilio]:checked').val();
                (v==2)?div.addClass('hide'):div.removeClass('hide');
                (v==2)?inp.val(0):inp.val(inpul.val);
                //(v==2)?inp.attr('required',false):inp.attr('required',true);
                //inp.parsley.validate();
            })
        </script>  
       
    </form>
    <?php echo "<script>INICIALIZARCONTENIDO();</script>";
    }
}
else if ($opcion == "GUARDAR") {
    $id        = $_POST['id'];
    $nom       = $_POST['nom'];
    $nompro    = $_POST['nompro'];
    $ubi       = $_POST['ubi'];
    $fecini    = $_POST['fecini'];
    $cen       = $_POST['cen'];
    $dir       = $_POST['dir'];
    $vo        = $_POST['vo'];
    $vm        = $_POST['vm'];
    $vs        = $_POST['vs'];
    $lun       = $_POST['lun'];
    $mar       = $_POST['mar'];
    $mie       = $_POST['mie'];
    $jue       = $_POST['jue'];
    $vie       = $_POST['vie'];
    $sab       = $_POST['sab'];
    $dom       = $_POST['dom'];
    $hor       = $_POST['hor'];
    $horsem    = $_POST['horsem'];
    $est       = $_POST['est'];
    $aux       = $_POST['aux'];
    $va        = $_POST['va'];
    $ve        = $_POST['ve'];
    $operador  = $_POST['operador'];
    $senalero  = $_POST['senalero'];
    $contrato  = $_POST['contrato'];
    $time      = time();

    // Verificar existencia
    $sqlVeri = "SELECT 1 FROM tbl_obras WHERE obr_nombre = :nom AND obr_nom_proyecto = :nompro AND est_clave_int != 3 AND obr_clave_int != :id";
    $stmtVeri = $conn->prepare($sqlVeri);
    $stmtVeri->bindParam(':nom', $nom);
    $stmtVeri->bindParam(':nompro', $nompro);
    $stmtVeri->bindParam(':id', $id, PDO::PARAM_INT);
    $stmtVeri->execute();

    if ($stmtVeri->rowCount() > 0) {
        $res = "error";
        $msn = "Ya hay una obra con el nombre ingresado. Verificar";
    } else {
        if ($id > 0) {
            // UPDATE
            $sqlUpdate = "UPDATE tbl_obras SET
                obr_nombre = :nom,
                obr_cencos = :cen,
                obr_encargado = :dir,
                obr_hr_mes = :hor,
                obr_vr_operador = :vo,
                obr_vr_maquina = :vm,
                obr_lunes = :lun,
                obr_martes = :mar,
                obr_miercoles = :mie,
                obr_jueves = :jue,
                obr_viernes = :vie,
                obr_sabado = :sab,
                obr_domingo = :dom,
                obr_usu_actualiz = :usuario,
                obr_fec_actualiz = :fecha,
                obr_vr_senalero = :vs,
                obr_hr_semana = :horsem,
                est_clave_int = :est,
                obr_auxilio = :aux,
                obr_vr_auxilio = :va,
                obr_vr_elevador = :ve,
                obr_nom_proyecto = :nompro,
                obr_fec_inicio = :fecini,
                obr_ubicacion = :ubi,
                obr_operador = :operador,
                obr_senalero = :senalero,
                obr_contrato = :contrato
            WHERE obr_clave_int = :id";

            $stmt = $conn->prepare($sqlUpdate);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        } else {
            // INSERT
            $sqlInsert = "INSERT INTO tbl_obras (
                obr_nombre, obr_cencos, obr_encargado, obr_creacion,
                obr_hr_mes, obr_vr_operador, obr_vr_maquina, obr_lunes,
                obr_martes, obr_miercoles, obr_jueves, obr_viernes,
                obr_sabado, obr_domingo, obr_usu_actualiz, obr_fec_actualiz,
                obr_vr_senalero, obr_hr_semana, est_clave_int, obr_auxilio,
                obr_vr_auxilio, obr_vr_elevador, obr_nom_proyecto, obr_fec_inicio,
                obr_ubicacion, obr_operador, obr_senalero, obr_contrato
            ) VALUES (
                :nom, :cen, :dir, :creador, :hor, :vo, :vm, :lun, :mar, :mie, :jue, :vie, :sab, :dom,
                :usuario, :fecha, :vs, :horsem, :est, :aux, :va, :ve, :nompro, :fecini,
                :ubi, :operador, :senalero, :contrato
            )";

            $stmt = $conn->prepare($sqlInsert);
            $stmt->bindParam(':creador', $idUsuario);
        }

        // Bind comunes
        $stmt->bindParam(':nom', $nom);
        $stmt->bindParam(':cen', $cen);
        $stmt->bindParam(':dir', $dir);
        $stmt->bindParam(':hor', $hor);
        $stmt->bindParam(':vo', $vo);
        $stmt->bindParam(':vm', $vm);
        $stmt->bindParam(':lun', $lun);
        $stmt->bindParam(':mar', $mar);
        $stmt->bindParam(':mie', $mie);
        $stmt->bindParam(':jue', $jue);
        $stmt->bindParam(':vie', $vie);
        $stmt->bindParam(':sab', $sab);
        $stmt->bindParam(':dom', $dom);
        $stmt->bindParam(':usuario', $usuario);
        $stmt->bindParam(':fecha', $fecha);
        $stmt->bindParam(':vs', $vs);
        $stmt->bindParam(':horsem', $horsem);
        $stmt->bindParam(':est', $est);
        $stmt->bindParam(':aux', $aux);
        $stmt->bindParam(':va', $va);
        $stmt->bindParam(':ve', $ve);
        $stmt->bindParam(':nompro', $nompro);
        $stmt->bindParam(':fecini', $fecini);
        $stmt->bindParam(':ubi', $ubi);
        $stmt->bindParam(':operador', $operador);
        $stmt->bindParam(':senalero', $senalero);
        $stmt->bindParam(':contrato', $contrato);

        if ($stmt->execute()) {
            if ($id == 0) {
                $id = $conn->lastInsertId();
                $msn = "Nueva Obra guardada correctamente";
            } else {
                $msn = "Obra modificada correctamente";
            }
            $res = "ok";
        } else {
            $res = "error";
            $msn = "Surgió un error al guardar/modificar la obra.";
        }
    }

    $datos[] = ["res" => $res, "id" => $id, "msn" => $msn];
    echo json_encode($datos);
}

else if($opcion=="FILTROS")
{
    ?>
    <div class="col-md-3">
        <label for="busnombre">Nombre Cliente:</label>
        <input type="text" name="busnombre" id="busnombre" class="form-control form-control-sm">
    </div>
    <div class="col-md-3">
        <label for="busnombreproyecto">Nombre Proyecto:</label>
        <input type="text" name="busnombreproyecto" id="busnombreproyecto" class="form-control form-control-sm">
    </div>
    <div class="col-md-3">
        <label for="buscontrato">Contrato/Cotización:</label>
        <input type="text" name="buscontrato" id="buscontrato" class="form-control form-control-sm">
    </div>
    <div class="col-md-3">
        <label for="busubicacion">Ubicación:</label>
        <input type="text" name="busubicacion" id="busubicacion" class="form-control form-control-sm">
    </div>
    <div class="col-md-3">
        <label for="buscenco">Cenco:</label>
        <input type="text" name="buscenco" id="buscenco" class="form-control form-control-sm">
    </div>
    <div class="col-md-3">                
        <label for="busdirector">Director:</label>
        <select id="busdirector" name="busdirector" class="form-control form-control-sm selectpicker" onchange="CRUDOBRAS('LISTAOBRAS','')" data-live-search="true"  data-actions-box="true"  multiple>
      
        <?php
            $selectEmp = new General();
            $selectEmp -> cargarEmpleados($dir,1);
        ?>
        </select>
        
    </div>
    <div class="col-md-3">                
        <label for="busestado">Estado:</label>
        <select id="busestado" name="busestado" class="form-control form-control-sm selectpicker" onchange="CRUDOBRAS('LISTAOBRAS','')" data-live-search="true"  data-actions-box="true"  multiple>      
        <?php           
            $selectEmp -> cargarEstados("",1);
        ?>
        </select>
        
    </div>
    <div class="col-md-1">
        <a class="btn btn-success btn-block btn-sm text-white" onclick="CRUDOBRAS('LISTAOBRAS','')">Buscar <i class="fas fas-search"></i></a>
    </div>
    <script>
        $(document).ready(function(e) {
            $('#busnombre').on('keypress',function (e) {
                if(e.which==13) {
                    CRUDOBRAS('LISTAOBRAS','')
                }
            });
            $('#busnombreproyecto').on('keypress',function (e) {
                if(e.which==13) {
                    CRUDOBRAS('LISTAOBRAS','')
                }
            });
            $('#buscontrato').on('keypress',function (e) {
                if(e.which==13) {
                    CRUDOBRAS('LISTAOBRAS','')
                }
            });
            $('#busubicacion').on('keypress',function (e) {
                if(e.which==13) {
                    CRUDOBRAS('LISTAOBRAS','')
                }
            });
            $('#buscenco').on('keypress',function (e) {
                if(e.which==13) {
                    CRUDOBRAS('LISTAOBRAS','')
                }
            });
        })
    </script>
    <?php
    echo "<script>INICIALIZARCONTENIDO();</script>";
    echo "<script>CRUDOBRAS('LISTAOBRAS','');</script>";
}
else if($opcion=="LISTAOBRAS")
{
    ?>
     <div class="card">   
        <!-- /.card-header -->
        <div class="card-body">
            <div class="table-responsive  table-responsive-sm table-responsive-md table-table-responsive-lg">
            <table class="table table-bordered" id="tbObras" style="width:100%">
                <thead>
                    <tr>  
                        <th class="dt-head-center bg-terracota" style="width:20px" rowspan="2"></th>
                        <th class="dt-head-center bg-terracota" style="width:20px" rowspan="2"></th>
                        <th class="dt-head-center bg-terracota" rowspan="2">Nombre Cliente</th>
                        <th class="dt-head-center bg-terracota" rowspan="2">Nombre Proyecto</th>
                        <th class="dt-head-center bg-terracota" rowspan="2">Contrato</th>
                        <th class="dt-head-center bg-terracota" rowspan="2">Fecha Inicio</th>
                        <th class="dt-head-center bg-terracota" rowspan="2">Ubicacion</th>
                        <th class="dt-head-center bg-terracota" rowspan="2">Director</th>
                        <th class="dt-head-center bg-terracota" rowspan="2">Cencos</th>
                        <th class="dt-head-center bg-terracota" rowspan="2">Horas Mes</th>
                        <th class="dt-head-center bg-terracota" rowspan="2">Horas Semana</th>
                        <th class="dt-head-center bg-terracota" rowspan="2">Vr Hora Operador</th>
                        <th class="dt-head-center bg-terracota" rowspan="2">Vr Hora Señalero</th>
                        <th class="dt-head-center bg-terracota" rowspan="2">Vr Hora Elevador</th>
                        <th class="dt-head-center bg-terracota" rowspan="2">Vr Hora Maquina</th>
                        <th class="dt-head-center bg-terracota" colspan="7">Cant. Horas Laboradas por dia</th>
                        <th class="dt-head-center bg-terracota" rowspan="2">Estado</th>
                      
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
                        <th></th>
                        <th></th>
                        <th></th>
                    </tr>
                </tfoot>        
            </table>
            </div>
        </div>
     </div>
    <script src="jsdatatable/obras/jslistaobras.js?<?php echo time();?>"></script>
    <?php
}
else if ($opcion == "ELIMINAR") {
    $id = $_POST['id'];

    $sql = "UPDATE tbl_obras 
            SET est_clave_int = 3, 
                obr_usu_actualiz = :usuario, 
                obr_fec_actualiz = :fecha 
            WHERE obr_clave_int = :id";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':usuario', $usuario);
    $stmt->bindParam(':fecha', $fecha);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        $res = "ok";
        $msn = "Obra eliminada correctamente";
    } else {
        $res = "error";
        $msn = "Surgió un error al eliminar obra. Verificar";
    }

    $datos[] = ["res" => $res, "msn" => $msn];
    echo json_encode($datos);
}
