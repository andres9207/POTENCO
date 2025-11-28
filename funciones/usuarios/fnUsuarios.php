<?php
session_start();
include '../../data/db.config.php';
include('../../data/conexion.php');
error_reporting(0);
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require ('../../PHPMailer-master/src/PHPMailer.php');
require ('../../PHPMailer-master/src/Exception.php');
require ('../../PHPMailer-master/src/SMTP.php');
require_once "../../controladores/general.controller.php";
//$login     = isset($_SESSION['persona']);
// cookie que almacena el numero de identificacion de la persona logueada
$usuario   = $_COOKIE['usuarioPo'];
$idUsuario = $_COOKIE['idusuarioPo'];
$perfil    = $_SESSION["perfilPo"];

include '../../data/validarpermisos.php';
setlocale(LC_TIME, 'es_CO.UTF-8'); 
date_default_timezone_set('America/Bogota');
$fecha  = date("Y/m/d H:i:s");

$opcion = $_POST['opcion'];
if($opcion == "NUEVO" || $opcion=="EDITAR" || $opcion=="NUEVOCLIENTE")
{
    $id = $_POST['id'];
    $ti = $_POST['ti']; 
    $opc = $_POST['opc'] ?? '';
    $obr = $_POST['obr'] ?? '';

    $ediusu = new General();
    $dat = $ediusu->editUsuario($id);    
    $idper = $dat['per'];
    $nom   = $dat['nom'];
    $ape   = $dat['ape'];
    $nit   = $dat['doc'];
    $usu   = $dat['usu'];
    $ema   = $dat['cor'];
    $pass  = $dat['cla'];
    $rut   = $dat['img'];
    $dir   = $dat['dir'];
    $bar   = $dat['bar'];
    $cel   = $dat['cel'];
    $tel   = $dat['fij'];
    $contacto  = $dat['con'];
    $pass  = "";// decrypt($pass,"p4v4sAp");
    $est   = $dat['est'];
    $idcenco = $dat['cen'];
    $ing = $dat['ing'];
    $sal = $dat['sal'];
    $hor = $dat['hor'];
    $aux = $dat['aux'];

    if($ti=="2"){
        $idper = 2;
    }

    if($opc==2)
    {
        $pass  = "ap2020*";
    }
    
    ?>
    <?php if($opc!=2){ ?>
    <form  id="frmusuario" name="frmusuario" data-id="<?php echo $id;?>" action="_" method="post" enctype="multipart/form-data" autocomplete="off" class="form-horizontal" data-parsley-validate=""><?php } ?>
        <div class="form-group row">
          
        
            <div class="col-md-6  col-sm-6  col-xs-12 hide">
               <label>Foto:</label>
               <span data-ruta = '<?php echo $rut;?>' id="rutausuario" class="hide"><?php echo $rut;
                  ?></span> 
               <h3 class="profile-username text-center"><input  class="form-control form-control-sm dropify " name="archivo" id="archivo" type="file" onChange="setpreview('rutausuario','archivo','frmusuario')" data-default-file="<?php echo $rut;?>" data-allowed-file-extensions="jpg png" /></h3>
            </div>
        
            <div class="col-md-12 <?php if($ti==2){ echo 'hide';}?>">
                <label for="selperfil">Perfil:</label>
                <select class="form-control form-control-sm selectpicker" id="selperfil" required data-parsley-error-message="Seleccionar el perfil del usuario" data-parsley-errors-container="#msn-error1">
                    <option value="">Seleccione</option>
                    <?php
                    $query = "SELECT prf_clave_int,prf_nombre FROM tbl_perfil WHERE est_clave_int = '1' or prf_clave_int = '$idper'"; 
                    foreach ($conn->query($query) as $row) {       
                        $idp = $row['prf_clave_int'];
                        $nomp = $row['prf_nombre'];                        
                        ?>
                        <option value="<?php echo $idp;?>" <?php if($idp==$idper){ echo "selected"; } ?>><?php echo $nomp;?></option>
                        <?php
                    }
                    ?>
                </select>
                <span id="msn-error1"></span>
            </div>
            <div class="col-md-6">               
                <label for="txtnombre">Nombre:</label> 
                <input type = "text" class="form-control form-control-sm" id="txtnombre" required value="<?php echo $nom;?>" data-parsley-error-message="Ingresar nombre">
            </div>
            <div class="col-md-6">
                
                <label for="txtapellido">Apellidos:</label> 
                <input type = "text" class="form-control form-control-sm" id="txtapellido"  value="<?php echo $ape; ?>" data-parsley-error-message="Ingresar apellido">
            </div>
            <div class="col-md-6">               
                <label for="txtnit">Nit/CC:</label> 
                <input type = "text" class="form-control form-control-sm" id="txtnit" required value="<?php echo $nit;?>" data-parsley-error-message="Ingresar nit u/o cedula">
            </div>
            <div class="col-md-6 hide <?php if($ti==2  || $idper==2){}else { echo 'hide';}?> " id="divcencos">
                <label for="selcencos">Centro de Costos:</label> 
                <select id="selcencos" <?php if($ti==2){ echo 'required';}?> class="form-control form-control-sm selectpicker" data-parsley-error-message="Seleccionar Centro de Costos" data-parsley-errors-container="#msn-errorcen" onchange="CARGARLABORES('selcencos','sellabor')">
                    <option data-divider="true"></option>
                    <?php 
                    $query ="SELECT cen_clave_int,cen_nombre,cen_codigo FROM tbl_cencos WHERE  est_clave_int=1";
                    foreach ($conn->query($query) as $row) {       
                    
                        $idcen = $row['cen_clave_int'];
                        $nomcen = $row['cen_nombre'];
                        $codcen = $row['cen_codigo'];
                        ?>
                        <option <?php if($idcenco==$idcen){ echo "selected"; }?> value="<?php echo $idcen; ?>"><?php echo $nomcen." - ".$codcen;?></option>
                        <?php
                    }
                    ?>
                </select>   
                <span id="msn-errorcen"></span> 
            </div>
            <div class="col-md-6 hide <?php if($idper!=2 || $opc==2){ echo "hide"; }?>" id="divbarrio">
                <label for="txtbarrio">Barrio:</label>
                <input type = "text" class="form-control form-control-sm" id="txtbarrio"   value="<?php echo $bar;?>" data-parsley-error-message="Ingresar el barrio">
            </div>
            <div class="col-md-6 hide <?php if($idper!=2){ echo "hide"; }?>" id="divdireccion">               
                <label for="txtdireccion">Dirección:</label>
                <input type = "text" class="form-control form-control-sm" id="txtdireccion" <?php if($ti=="2" and $opc!="2"){ echo "required"; }?>  value="<?php echo $dir;?>" data-parsley-error-message="Ingresar dirección">
            </div>
            <div class="col-md-6 hide" id="divcontacto">
                 <label for="txtcontacto">Contacto:</label>
                <input type = "text" class="form-control form-control-sm" id="txtcontacto"  value="<?php echo $contacto;?>" data-parsley-error-message="Ingresar el nombre del contacto">
            </div>
           

            <div class="col-md-6 <?php if($idper!=1){}else { echo 'hide';}?> " id="divingreso">
                <label for="txtfechaingreso">Fecha Ingreso:</label> 
                <input type = "date" class="form-control form-control-sm" id="txtfechaingreso" <?php if($ti=="2" and $opc!="2"){ echo "required"; }?>  value="<?php echo $ing;?>" data-parsley-error-message="Ingresar la fecha de ingreso" max="<?php echo date('Y-m-d');?>">              
            </div>

            <div class="col-md-6 <?php if($idper!=1){}else { echo 'hide';}?> " id="divsalario">
                <label for="txtsalario">Salario:</label> 
                <input type = "text" onkeypress="return validar_texto(event)" class="form-control form-control-sm currency" id="txtsalario" <?php if($ti=="2" and $opc!="2"){ echo "required"; }?>  value="<?php echo $sal;?>" data-parsley-error-message="Ingresar valor salario">              
            </div>
            <div class="col-md-6 <?php if($idper!=1){}else { echo 'hide';}?> " id="divauxilio">
                <label for="txtauxilio">Auxilio:</label> 
                <input type = "text" onkeypress="return validar_texto(event)" class="form-control form-control-sm currency" id="txtauxilio" <?php if($ti=="2" and $opc!="2"){ echo "required"; }?>  value="<?php echo $aux;?>" data-parsley-error-message="Ingresar valor auxili">              
            </div>
            <div class="col-md-6 <?php if($idper!=1){ }else{ echo "hide"; }?>" id="divtelefono">                
                <label for="txttelefono">Teléfono:</label>
                <input type = "text" class="form-control form-control-sm" id="txttelefono" <?php if($ti=="2" and $opc!="2"){ echo "required"; }?>  value="<?php echo $tel;?>" data-parsley-error-message="Ingresar el numero telefonico">
            </div>        
            <div class="col-md-6 <?php if($idper!=1){}else{ echo "hide"; }?>" id="divcelular">
                
                <label for="txtcelular">Celular:</label>
                <input type = "text" class="form-control form-control-sm" id="txtcelular" value="<?php echo $cel;?>" data-parsley-error-message="Ingresar celular">
            </div>
            <div class="col-md-6">
                
                <label for="txtusuario">Usuario</label>
                <input type = "text" class="form-control form-control-sm" id="txtusuario" <?php if($opc!=2){ echo "required"; }?> value="<?php echo $usu;?>" data-parsley-error-message="Ingresar usuario" autocomplete="nope">
            </div>
            <div class="col-md-6" >
                <label for="txtemail">Correo electrónico:</label>
                <input type = "email" class="form-control form-control-sm" id="txtemail" <?php if($opc==1){ echo "required"; }?> value="<?php echo $ema;?>" data-parsley-type="email" autocomplete="nope">
            </div>
            <div class="col-md-6">
                <label for="txtcontrasena">Contraseña:</label>
                <input type = "password"  class="form-control form-control-sm" id="txtcontrasena" required value="<?php echo $pass;?>" data-parsley-error-message="Ingresa la contraseña" autocomplete="new-password">
            </div>
            <div class="col-md-6 ">
                <label for="txtverificar">Verificar Contraseña:</label>
                <input type="password" class="form-control form-control-sm" id="txtverificar" required value="<?php echo $pass;?>" data-parsley-equalto="#txtcontrasena" autocomplete="new-password">
            </div>
        
            <div class="col-md-6">
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
        <div class="row">
            <div class="col-md-12">
                <label for="selhorario">Horario:</label>
                <select class="form-control form-control-sm selectpicker" id="selhorario" name="selhorario" onchange="CRUDUSUARIOS('INFOHORARIO')"  data-parsley-error-message="Seleccionar Horario" data-parsley-errors-container="#msn-errorhor" title="Seleccionar Horario">
                    <option value=""></option>
                    <?php
                        $selectHor = new General();
                        $selectHor -> cargarHorarios($hor);
                    ?>
                </select>
                <span id="msn-errorhor"></span>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12" id="divinfohorario"></div>
        </div>
        <?php if($opc!=2){ ?></form><?php } ?>
        <script>
        var id = $('#frmusuario').data('id');
        var con = $('#txtcontrasena');
        var ver = $('#txtverificar');
        // if(id>0)
        // {
            if( con.val()!="")
            {
                con.prop('required',true).parsley().validate();
                ver.prop('required',true).parsley().validate();
            }
            else
            {
                con.prop('required',false).parsley().validate();
                ver.prop('required',false).parsley().validate();
            }
        // }
        // else
        // {
        //     con.prop('required',true).parsley().validate();
        //     ver.prop('required',true).parsley().validate();
        // }
        $('#txtcontrasena').on('keyup', function()
        {
            // if(id>0)
            // {
            if( con.val()!="")
            {
                con.prop('required',true).parsley().validate();
                ver.prop('required',true).parsley().validate();
            }
            else
            {
                con.prop('required',false).parsley().validate();
                ver.prop('required',false).parsley().validate();
            }
            // }
            // else
            // {
            //     con.prop('required',true).parsley().validate();
            //     ver.prop('required',true).parsley().validate();
            // }
        })
		$('#selperfil').on('change',function(){
            var per = $('#selperfil').val();
            if(per!=1)
            {
                $('#divbarrio').removeClass('hide');
                $('#divdireccion').removeClass('hide');
                $('#divtelefono').removeClass('hide');
                $('#divcelular').removeClass('hide');
               // $('#divcontacto').removeClass('hide');
                //$('#txtbarrio').prop('required',true)
                $('#txtdireccion').prop('required',true)
                $('#txttelefono').prop('required',true)
                //$('#txtcontacto').prop('required',true);
                //$('#divcencos').removeClass('hide');
                $('#divingreso').removeClass('hide');
                $('#divsalario').removeClass('hide');
                $('#divauxilio').removeClass('hide');
                $('#txtfechaingreso').prop('required',true)
                $('#txtsalario').prop('required',true);
                $('#txtauxilio').prop('required',true);
                $('#txtemail').prop('required',false);
                $('#selhorario').prop('required',true)
            }
            else
            {
                $('#divbarrio').addClass('hide');
                $('#divdireccion').addClass('hide');
                $('#divtelefono').addClass('hide');
                $('#divcelular').addClass('hide');
               // $('#divcontacto').addClass('hide');
                //$('#txtbarrio').removeProp('required')
                $('#txtdireccion').removeProp('required')
                $('#txttelefono').removeProp('required');
                //$('#txtcontacto').removeProp('required');
                //$('#divcencos').addClass('hide');
                $('#divingreso').addClass('hide');
                $('#divsalario').addClass('hide');
                $('#divauxilio').addClass('hide');
                $('#txtfechaingreso').prop('required',false)
                $('#txtsalario').prop('required',false);
                $('#txtauxilio').prop('required',false);
                $('#txtemail').prop('required',true);
                $('#selhorario').prop('required',false)
            }
			$('#selperfil').parsley().validate();
            $('#txtemail').parsley().validate();
            $('#selhorario').parsley().validate();
        })

        $('#selhorario').on('change',function(){
            $('#selhorario').parsley().validate();
        })

		</script>
        <?php
    echo "<script>INICIALIZARCONTENIDO();</script>";
    echo "<script>CRUDUSUARIOS('INFOHORARIO');</script>";
}
else 
if ($opcion == "GUARDAR" || $opcion == "GUARDARCLIENTE") {
    $fecha = date("Y/m/d H:i:s");

    $nom = strtoupper($_POST['nombre']);
    $ape = strtoupper($_POST['apellido']);
    $pass = $_POST['pass'];
    $pass = encrypt($pass, 'p4v4sAp');

    $per = $_POST['perfil'];
    $ema = $_POST['ema'];
    $est = $_POST['est'];
    $usu = $_POST['usu'];
    $doc = $_POST['doc'];
    $tel = $_POST['fij'];
    $cel = $_POST['cel'];
    $dir = $_POST['dir'];
    $bar = $_POST['bar'];
    $ruta = $_POST['ruta'];
    $contacto = $_POST['contacto'];
    $tex = $_POST['tex'];
    $cen = $_POST['cen'];
    $ing = $_POST['ing'];
    $sal = $_POST['sal'];
    $hor = $_POST['hor'];
    $aux = $_POST['aux'];

    $trozos = explode(".", $ruta);
    $extension = end($trozos);
    $rutaold = "../../" . $ruta;

    // Verificar usuario/correo duplicado
    $stmt = $conn->prepare("SELECT 1 FROM tbl_usuarios WHERE (usu_correo = :ema OR usu_usuario = :usu) AND est_clave_int != 3");
    $stmt->bindParam(':ema', $ema);
    $stmt->bindParam(':usu', $usu);
    $stmt->execute();

    if ($stmt->rowCount() > 0 && $ema != "") {
        $res = "error";
        $msn = "Ya hay un usuario con el correo electrónico o usuario ingresado. Verificar";
    } else {
        // Insertar nuevo usuario
        $sql = "INSERT INTO tbl_usuarios (
                    usu_nombre, usu_apellido, usu_celular, usu_fijo, usu_correo, usu_direccion, 
                    prf_clave_int, est_clave_int, usu_usu_actualiz, usu_fec_actualiz, 
                    usu_documento, usu_usuario, usu_barrio, usu_contacto, cen_clave_int, 
                    usu_fec_ingreso, usu_salario, hor_clave_int, usu_auxilio
                ) VALUES (
                    :nom, :ape, :cel, :tel, :ema, :dir, :per, :est, :usuario, :fecha,
                    :doc, :usu, :bar, :contacto, :cen, :ing, :sal, :hor, :aux
                )";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':nom', $nom);
        $stmt->bindParam(':ape', $ape);
        $stmt->bindParam(':cel', $cel);
        $stmt->bindParam(':tel', $tel);
        $stmt->bindParam(':ema', $ema);
        $stmt->bindParam(':dir', $dir);
        $stmt->bindParam(':per', $per);
        $stmt->bindParam(':est', $est);
        $stmt->bindParam(':usuario', $usuario); // ya viene del sistema
        $stmt->bindParam(':fecha', $fecha);
        $stmt->bindParam(':doc', $doc);
        $stmt->bindParam(':usu', $usu);
        $stmt->bindParam(':bar', $bar);
        $stmt->bindParam(':contacto', $contacto);
        $stmt->bindParam(':cen', $cen);
        $stmt->bindParam(':ing', $ing);
        $stmt->bindParam(':sal', $sal);
        $stmt->bindParam(':hor', $hor);
        $stmt->bindParam(':aux', $aux);

        if ($stmt->execute()) {
            $id = $conn->lastInsertId();

            // Mover imagen
            $rutanew = "";
            if ($_POST['ruta'] != "" && rename($rutaold, "../../modulos/usuarios/fotosUsuarios/$id.$extension")) {
                $rutanew = "modulos/usuarios/fotosUsuarios/$id.$extension";

                $sqlImg = "UPDATE tbl_usuarios SET usu_imagen = :img WHERE usu_clave_int = :id";
                $stmtImg = $conn->prepare($sqlImg);
                $stmtImg->bindParam(':img', $rutanew);
                $stmtImg->bindParam(':id', $id, PDO::PARAM_INT);
                $stmtImg->execute();
            }

            // Insertar clave si existe
            if ($pass != "") {
                $stmtPass = $conn->prepare("UPDATE tbl_usuarios SET usu_clave = :clave WHERE usu_clave_int = :id");
                $stmtPass->bindParam(':clave', $pass);
                $stmtPass->bindParam(':id', $id, PDO::PARAM_INT);
                $stmtPass->execute();
            }

            // Insertar permisos del perfil
            $sqlPerm = "INSERT INTO tbl_permisos_usuarios (per_clave_int, usu_clave_int)
                        SELECT per_clave_int, :id FROM tbl_permisos_perfil WHERE prf_clave_int = :perfil";
            $stmtPerm = $conn->prepare($sqlPerm);
            $stmtPerm->bindParam(':id', $id, PDO::PARAM_INT);
            $stmtPerm->bindParam(':perfil', $per, PDO::PARAM_INT);
            $stmtPerm->execute();

            $res = 'ok';
            $msn = "Nuevo Usuario Guardado Correctamente";
        } else {
            $res = 'error';
            $msn = "Surgió un error al guardar usuario. Error BD";
            $rutanew = "";
        }
    }

    $datos[] = [
        "res" => $res,
        "imagen" => $rutanew . "?" . time(),
        "msn" => $msn,
        "id" => $id ?? null
    ];
    echo json_encode($datos);
}
else 
if ($opcion == "GUARDAREDICION") {
    error_reporting(E_ALL);
    $fecha = date("Y/m/d H:i:s");

    $id       = $_POST['id'];
    $nom      = strtoupper($_POST['nombre']);
    $ape      = strtoupper($_POST['apellido']);
    $pass     = $_POST['pass'];
    $pass     = encrypt($pass, 'p4v4sAp');
    $per      = $_POST['perfil'];
    $ema      = $_POST['ema'];
    $est      = $_POST['est'];
    $usu      = $_POST['usu'];
    $doc      = $_POST['doc'];
    $tel      = $_POST['fij'];
    $cel      = $_POST['cel'];
    $dir      = $_POST['dir'];
    $bar      = $_POST['bar'] ?? "";
    $contacto = $_POST['contacto'];
    $ruta     = $_POST['ruta'] ?? "";
    $rutaa    = $_POST['rutaa'];
    $cen      = $_POST['cen'];
    $ing      = $_POST['ing'];
    $sal      = $_POST['sal'];
    $hor      = $_POST['hor'];
    $aux      = $_POST['aux'];

    $trozos     = $ruta  ?  explode(".", $ruta): "";
    $extension  = $trozos ?  end($trozos): "";
    $rutaold    = "../../" . $ruta;
    $rutan      =  $extension ? "../../modulos/usuarios/fotosUsuarios/" . $id . "." . $extension: "";
    $rutanew    = "";
    $msn        = "";

    // Validar duplicado de correo o usuario
    $sqlCheck = "SELECT usu_clave_int FROM tbl_usuarios 
                 WHERE (usu_correo = :ema OR (usu_usuario = :usu AND usu_usuario != '')) 
                   AND est_clave_int != 3 
                   AND usu_clave_int != :id";
    $stmtCheck = $conn->prepare($sqlCheck);
    $stmtCheck->bindParam(':ema', $ema);
    $stmtCheck->bindParam(':usu', $usu);
    $stmtCheck->bindParam(':id', $id, PDO::PARAM_INT);
    $stmtCheck->execute();

    $result = $stmtCheck->fetch(PDO::FETCH_ASSOC);

    if ($result and $ema != "") {
        $res = "error";
        $msn = "Ya hay un usuario con el correo electrónico o usuario ingresado. Verificar";
    } else {
        // Actualizar datos del usuario
        $sql = "UPDATE tbl_usuarios SET 
                    usu_nombre = :nom,
                    usu_apellido = :ape,
                    usu_usuario = :usu,
                    usu_celular = :cel,
                    usu_fijo = :tel,
                    usu_correo = :ema,
                    usu_direccion = :dir,
                    prf_clave_int = :perfil,
                    est_clave_int = :estado,
                    usu_usu_actualiz = :usuario,
                    usu_fec_actualiz = :fecha,
                    usu_documento = :doc,
                    usu_barrio = :bar,
                    usu_contacto = :contacto,
                    cen_clave_int = :cen,
                    usu_fec_ingreso = :ing,
                    usu_salario = :sal,
                    hor_clave_int = :hor,
                    usu_auxilio = :aux
                WHERE usu_clave_int = :id";
        $stmt = $conn->prepare($sql);

        $stmt->bindParam(':nom', $nom);
        $stmt->bindParam(':ape', $ape);
        $stmt->bindParam(':usu', $usu);
        $stmt->bindParam(':cel', $cel);
        $stmt->bindParam(':tel', $tel);
        $stmt->bindParam(':ema', $ema);
        $stmt->bindParam(':dir', $dir);
        $stmt->bindParam(':perfil', $per);
        $stmt->bindParam(':estado', $est);
        $stmt->bindParam(':usuario', $usuario);
        $stmt->bindParam(':fecha', $fecha);
        $stmt->bindParam(':doc', $doc);
        $stmt->bindParam(':bar', $bar);
        $stmt->bindParam(':contacto', $contacto);
        $stmt->bindParam(':cen', $cen);
        $stmt->bindParam(':ing', $ing);
        $stmt->bindParam(':sal', $sal);
        $stmt->bindParam(':hor', $hor);
        $stmt->bindParam(':aux', $aux);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $res = "ok";
            $msn = "Datos Actualizados Correctamente";

            // Mover imagen si aplica
            if ($ruta != "" && rename($rutaold, $rutan)) {
                $rutanew = "modulos/usuarios/fotosUsuarios/" . $id . "." . $extension;

                $sqlImg = "UPDATE tbl_usuarios SET usu_imagen = :img WHERE usu_clave_int = :id";
                $stmtImg = $conn->prepare($sqlImg);
                $stmtImg->bindParam(':img', $rutanew);
                $stmtImg->bindParam(':id', $id, PDO::PARAM_INT);
                $stmtImg->execute();

                $msn .= "<br>Se modificó imagen";

                if ($id != $idUsuario) {
                    $rutanew = "";
                }
            }

            // Actualizar clave si viene
            if ($pass != "") {
                $sqlPass = "UPDATE tbl_usuarios SET usu_clave = :clave WHERE usu_clave_int = :id";
                $stmtPass = $conn->prepare($sqlPass);
                $stmtPass->bindParam(':clave', $pass);
                $stmtPass->bindParam(':id', $id, PDO::PARAM_INT);
                $stmtPass->execute();
            }

            clearstatcache();
        } else {
            $res = "error3";
            $rutanew = "";
            $msn = "Surgió un error al actualizar usuario";
        }
    }

    $datos[] = [
        "res" => $res,
        "imagen" => $rutanew . "?" . time(),
        "msn" => $msn
    ];
    echo json_encode($datos);
}

else 
if ($opcion == "ELIMINAR") {
    $id  = $_POST['id'];
    $tex = $_POST['tex'];

    if ($id == $idUsuario) {
        $res = "error";
        $msn = "No te puedes dar de baja del sistema";
    } else {
        $sql = "UPDATE tbl_usuarios 
                SET est_clave_int = 3, 
                    usu_usu_actualiz = :usuario, 
                    usu_fec_actualiz = :fecha 
                WHERE usu_clave_int = :id";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':usuario', $usuario);
        $stmt->bindParam(':fecha', $fecha);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $res = "ok";
            $msn = ucwords($tex) . " eliminado correctamente";
        } else {
            $res = "error";
            $msn = "Surgió un error al eliminar el " . $tex . " seleccionado. Verificar";
        }
    }

    $datos[] = ["res" => $res, "msn" => $msn];
    echo json_encode($datos);
}

else if($opcion=="VALORESACTUAL"){
    ?>
     <ul class="nav nav-tabs"  role="tablist">
        <li class="nav-item active">
            <a onclick="CRUDUSUARIOS('LISTAUSUARIOS','Todos')" class="nav-link active" data-toggle="pill"  role="tab" aria-controls="custom-tabs-three-home" aria-selected="true">TODOS</a>
        </li>
        <?php
         $conp = $conn->prepare("SELECT p.prf_clave_int,p.prf_nombre,count(u.usu_clave_int) cant FROM tbl_perfil p JOIN tbl_usuarios u ON u.prf_clave_int = p.prf_clave_int  WHERE u.est_clave_int NOT IN ('3') GROUP BY p.prf_clave_int"); 
         $conp->execute();
         while($datp = $conp->fetch(PDO::FETCH_ASSOC)){
             $idp = $datp['prf_clave_int'];
             $nomp = $datp['prf_nombre'];
             $cant = $datp['cant'];
            ?>
            <li class="nav-item">
                <a onclick="CRUDUSUARIOS('LISTAUSUARIOS','<?php echo $idp;?>')" class="nav-link" data-toggle="pill" role="tab" aria-controls="custom-tabs-three-profile" aria-selected="false">
                <?PHP echo $nomp;?>
                    <span class="badge badge-warning  navbar-badge2"><?php echo $cant;?></span>
                </a>
                
            </li>
        <?php
        }
         ?>
    </ul>
    <?PHP
}
else if($opcion=="FILTROS")
{
    error_reporting(E_ALL);
    ?>
    <div class="col-md-3">
        <input type="search" class="form-control form-control-sm" id="busnombre" name="busnombre" placeholder="Buscar por nombre">
    </div>
    <div class="col-md-3">
        <input type="search" class="form-control form-control-sm" id="busapellido" name="busapellido" placeholder="Buscar por apellido">
    </div>
    <div class="col-md-3">
        <input type="search" class="form-control form-control-sm" id="buscorreo" name="buscorreo" placeholder="Buscar por correo">
    </div>
    <div class="col-md-3">
        <input type="search" class="form-control form-control-sm" id="bususuario" name="bususuario" placeholder="Buscar por usuario">
    </div>
    <div class="col-md-3">                
        <label for="busestado">Estado:</label>
        <select id="busestado" name="busestado" class="form-control form-control-sm selectpicker" onchange="CRUDUSUARIOS('LISTAUSUARIOS','')" data-live-search="true"  data-actions-box="true"  multiple>      
        <?php 
            $selectEst = new General();          
            $selectEst -> cargarEstados("",1);
        ?>
        </select>        
    </div>
            
    <script>
        $(document).ready(function(e) {
            $('#busnombre').on('keypress',function (e) {
                if(e.which==13) {
                console.log("Buscar por usuario");
                    CRUDUSUARIOS('LISTAUSUARIOS','')
                }
            });
            $('#busapellido').on('keypress',function (e) {
                if(e.which==13) {
                    CRUDUSUARIOS('LISTAUSUARIOS','')
                }
            });
            $('#buscorreo').on('keypress',function (e) {
                if(e.which==13) {
                    CRUDUSUARIOS('LISTAUSUARIOS','')
                }
            });
            $('#bususuario').on('keypress',function (e) {
                if(e.which==13) {
                    CRUDUSUARIOS('LISTAUSUARIOS','')
                }
            });
        });
    </script>
    <?php
    echo "<script>INICIALIZARCONTENIDO();</script>";
    echo "<script>CRUDUSUARIOS('LISTAUSUARIOS','');</script>";
}
else if($opcion=="LISTAUSUARIOS")
{
    $id = $_POST['id'];
    ?>
    <div class="card">   
        <!-- /.card-header -->
        <div class="card-body">
            <div class="table-responsive">
            <table style="width:100%" id="tbusuarios" data-per = '<?php echo $id;?>' class="table table-bordered table-striped">
            <thead>
            <tr> 
                <th class="dt-head-center bg-terracota" style="width: 100px;"></th>
                <th class="dt-head-center bg-terracota"></th>                       
                <th class="dt-head-center bg-terracota">Nombre</th>
                <th class="dt-head-center bg-terracota">Apellido</th>
                <th class="dt-head-center bg-terracota">Usuario</th>
                <th class="dt-head-center bg-terracota">Correo</th>
                <th class="dt-head-center bg-terracota">Salario</th>
                <th class="dt-head-center bg-terracota">Perfil</th>
                <th class="dt-head-center bg-terracota">Estado</th>
               
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
            </tr>
            </tfoot>
            </table>
            </div>
        </div>
    </div>
    <script src="jsdatatable/usuarios/jslistausuarios.js?<?PHP echo time();?>"></script>
    <?PHP
}
else 
if ($opcion == "RECUPERARCONTRASENA") {
    $email = $_POST['email'] ?? '';
    $datos = [];

    if ($email != '') {
        // Buscar usuario
        $sql = "SELECT * FROM tbl_usuarios WHERE usu_correo = :email AND est_clave_int != 3 LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $dato = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($dato) {
            $usucla = $dato['usu_clave_int'];
            $usu = $dato['usu_usuario'];
            $ema = $dato['usu_correo'];
            $clave = $dato['usu_clave'];

            // Verificar si ya hay un código de recuperación activo
            $sql = "SELECT * FROM tbl_recuperar WHERE usu_clave_int = :usucla AND rec_estado = 0";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':usucla', $usucla, PDO::PARAM_INT);
            $stmt->execute();
            $recuperacion = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($recuperacion) {
                $random = $recuperacion['rec_codigo'];
            } else {
                // Generar nuevo código
                $characters = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
                $random = substr(str_shuffle(str_repeat($characters, 50)), 0, 50);

                $sql = "INSERT INTO tbl_recuperar (rec_codigo, usu_clave_int, rec_estado, usu_email) 
                        VALUES (:codigo, :usucla, 0, :email)";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':codigo', $random, PDO::PARAM_STR);
                $stmt->bindParam(':usucla', $usucla, PDO::PARAM_INT);
                $stmt->bindParam(':email', $email, PDO::PARAM_STR);
                $stmt->execute();
            }

            // Enviar correo
            $asuntod = "Recuperación Clave POTENCO";
            $informacion = "POTENCO registra que has hecho una solicitud de recuperación de contraseña.";
            $detalleinformacion = "Para ingresar, solo debes hacer clic en el botón Recuperar o copiar y pegar el siguiente enlace en tu navegador:<br>";
            $detalleinformacion .= "<strong>Ingresar aquí:</strong> https://www.pavas.com.co/POTENCO/recover_password.php?codigo=" . $random . "<br>";
            $detalleinformacion .= "<strong>Fecha:</strong> " . date("d/m/Y H:i:s") . "<br><br>";
            $boton = "<br><a class='bubbly-button' href='https://www.pavas.com.co/POTENCO/recover_password.php?codigo=" . $random . "'>RECUPERAR</a>";

            $contenido = file_get_contents('../../modulos/plantillas/correoaprobacion.php');
            $contenido = str_replace('%titulo%', $asuntod, $contenido);
            $contenido = str_replace('%usunombre%', $usu, $contenido);
            $contenido = str_replace('%informacion%', $informacion, $contenido);
            $contenido = str_replace('%boton%', $boton, $contenido);
            $contenido = str_replace('%detalleinformacion%', $detalleinformacion, $contenido);

            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.hostinger.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'admin@pavasapps.com';
                $mail->Password = 'P4v4s2025**.';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS; // ssl
                $mail->Port = 465;

                // Opciones de tiempo (en segundos)
                $mail->Timeout = 5;          // timeout total
                $mail->SMTPOptions = [
                    'ssl' => [
                        'verify_peer'  => false,
                        'verify_peer_name' => false,
                        'allow_self_signed' => true,
                    ],
                ];
                $mail->CharSet = "UTF-8";
                $mail->isHTML(true);
                $mail->setFrom("admin.potenco@pavasapps.com", "POTENCO");
                $mail->addReplyTo("programador@pavas.co", "POTENCO");
                $mail->addAddress($ema, "Usuario: " . $usu);
                $mail->Subject = $asuntod;
                $mail->msgHTML($contenido);

                if (!$mail->send()) {
                    $res = "error";
                    $msn = 'No se envió el mensaje al email <strong>(' . $ema . ')</strong>: ' . $mail->ErrorInfo;
                } else {
                    $res = "ok";
                    $msn = "Solicitud de recuperación enviada. Revisa tu correo: " . $ema;
                }
            } catch (Exception $e) {
                $res = "error";
                $msn = "Error al enviar correo: " . $mail->ErrorInfo;
            }

        } else {
            $res = "error";
            $msn = "Correo electrónico no registrado o usuario inactivo.";
        }
    } else {
        $res = "error";
        $msn = "Correo electrónico no proporcionado.";
    }

    $datos[] = ["res" => $res, "msn" => $msn];
    echo json_encode($datos);
}

else 
if ($opcion == "RESTABLECER") {
    $cod = $_POST['cod'] ?? '';
    $con1 = $_POST['con1'] ?? '';
    $con2 = $_POST['con2'] ?? '';
    $datos = [];
    $ema = '';

    if ($cod === '') {
        $res = "error";
        $msn = "Código inválido.";
    } else {
        // Verificar si el código ya fue usado
        $sql = "SELECT * FROM tbl_recuperar WHERE rec_codigo = :codigo AND rec_estado = 1";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':codigo', $cod, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $res = "error";
            $msn = "Este código ya fue usado anteriormente.";
        } else {
            if ($con1 === $con2) {
                // Obtener datos del usuario asociado al código
                $sql = "SELECT * FROM tbl_recuperar WHERE rec_codigo = :codigo";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':codigo', $cod, PDO::PARAM_STR);
                $stmt->execute();
                $dato = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($dato) {
                    $usu = $dato['usu_clave_int'];
                    $ema = $dato['usu_email'];

                    // Encriptar nueva contraseña
                    $claveEncriptada = encrypt($con1, 'p4v4sAp');

                    // Actualizar estado del código y cambiar la contraseña
                    $conn->beginTransaction();

                    try {
                        $sql1 = "UPDATE tbl_recuperar SET rec_estado = 1 WHERE rec_codigo = :codigo";
                        $stmt1 = $conn->prepare($sql1);
                        $stmt1->bindParam(':codigo', $cod, PDO::PARAM_STR);
                        $stmt1->execute();

                        $sql2 = "UPDATE tbl_usuarios SET usu_clave = :clave WHERE usu_clave_int = :usu";
                        $stmt2 = $conn->prepare($sql2);
                        $stmt2->bindParam(':clave', $claveEncriptada, PDO::PARAM_STR);
                        $stmt2->bindParam(':usu', $usu, PDO::PARAM_INT);
                        $stmt2->execute();

                        $conn->commit();

                        $res = "ok";
                        $msn = "¡Su contraseña ha sido restablecida correctamente!";
                    } catch (Exception $e) {
                        $conn->rollBack();
                        $res = "error";
                        $msn = "Error al restablecer la contraseña: " . $e->getMessage();
                    }
                } else {
                    $res = "error";
                    $msn = "Código inválido o no encontrado.";
                }
            } else {
                $res = "error";
                $msn = "Las contraseñas no coinciden.";
            }
        }
    }

    $datos[] = ["res" => $res, "msn" => $msn, "ema" => $ema];
    echo json_encode($datos);
}

else 
if ($opcion == "ASIGNARPERMISOS") {
    $idu = $_POST['id'];
    
    $sql = "SELECT ven_clave_int, ven_descripcion FROM tbl_ventanas";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $ventanas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $idven = [];
    $nomventana = [];

    ?>
    <div class="row">
        <div class="col-7 col-sm-9">
            <div class="tab-content" id="vert-tabs-right-tabContent">
                <?php
                foreach ($ventanas as $index => $ventana) {
                    $idve = $ventana['ven_clave_int'];
                    $nomve = $ventana['ven_descripcion'];
                    $idven[] = $idve;
                    $nomventana[] = $nomve;
                    ?>
                    <div class="tab-pane fade <?php echo ($idve == 1) ? 'show active' : ''; ?>" 
                         id="ventana-<?php echo $idve; ?>" 
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
            <div class="nav flex-column nav-tabs nav-tabs-right h-100" id="vert-tabs-right-tab" role="tablist" aria-orientation="vertical">
                <?php
                foreach ($idven as $k => $venId) {
                    ?>
                    <a class="nav-link <?php echo ($venId == 1) ? 'active' : ''; ?>" 
                       id="vert-tabs-right-<?php echo $venId; ?>" 
                       data-toggle="pill" 
                       data-target="#ventana-<?php echo $venId; ?>" 
                       role="tab" 
                       aria-controls="vert-tabs-right-home" 
                       aria-selected="true" 
                       onclick="CRUDUSUARIOS('LISTAPERMISOS', '<?php echo $idu; ?>', '<?php echo $venId; ?>')">
                        <?php echo htmlspecialchars($nomventana[$k]); ?>
                    </a>
                    <?php
                }
                ?>
            </div>
        </div>
    </div>
    <?php
    echo "<script>CRUDUSUARIOS('LISTAPERMISOS', '$idu', '1');</script>";
}

else 
if ($opcion == "LISTAPERMISOS") {
    $idu = $_POST['id'];
    $ven = $_POST['ven'];

    $sql = "SELECT per_clave_int, per_descripcion FROM tbl_permisos WHERE ven_clave_int = :ven";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':ven', $ven, PDO::PARAM_INT);
    $stmt->execute();
    $permisos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <div class="row">
        <?php
        if (count($permisos) > 0) {
            foreach ($permisos as $permiso) {
                $idp = $permiso['per_clave_int'];
                $des = $permiso['per_descripcion'];

                // Verificar si el permiso está asignado al usuario
                $sqlv = "SELECT 1 FROM tbl_permisos_usuarios WHERE per_clave_int = :per AND usu_clave_int = :usu LIMIT 1";
                $stmtv = $conn->prepare($sqlv);
                $stmtv->bindParam(':per', $idp, PDO::PARAM_INT);
                $stmtv->bindParam(':usu', $idu, PDO::PARAM_INT);
                $stmtv->execute();
                $checked = $stmtv->fetch() ? "checked" : "";
                ?>
                <div class="col-md-6 col-sm-6 col-xs-12">
                    <ul class="list-group" style="list-style:none"> 
                        <li class="list-group-item list-group-item-action">
                            <label for="permiso-<?php echo $idp; ?>">
                                <input <?php echo $checked; ?> type="checkbox" class="flat" 
                                    onclick="CRUDUSUARIOS('GUARDARPERMISOS','<?php echo $idp; ?>','<?php echo $idu; ?>')" 
                                    id="permiso-<?php echo $idp; ?>">
                                <?php echo htmlspecialchars($des); ?>
                            </label>
                        </li>
                    </ul>
                </div>
                <?php
            }
        } else {
            echo "<div class='col-md-12'>NO HAY PERMISOS CREADOS PARA ESTE MÓDULO</div>";
        }
        ?>
    </div>
    <?php
    echo "<script>INICIALIZARCONTENIDO();</script>";
}

else 
if ($opcion == "GUARDARPERMISOS") {
    $idp = $_POST['idp'];
    $idu = $_POST['idu'];
    $fecha = date("Y-m-d H:i:s");

    // Verificar si ya existe el permiso
    $sql = "SELECT 1 FROM tbl_permisos_usuarios 
            WHERE per_clave_int = :idp AND usu_clave_int = :idu LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':idp', $idp, PDO::PARAM_INT);
    $stmt->bindParam(':idu', $idu, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->fetch()) {
        // Eliminar permiso existente
        $sql = "DELETE FROM tbl_permisos_usuarios 
                WHERE per_clave_int = :idp AND usu_clave_int = :idu";
        $stmt = $conn->prepare($sql);
    } else {
        // Insertar nuevo permiso (usando $usuario directamente sin declararlo aquí)
        $sql = "INSERT INTO tbl_permisos_usuarios 
                    (per_clave_int, usu_clave_int, peu_usu_actualiz, peu_fec_actualiz) 
                VALUES (:idp, :idu, :usuario, :fecha)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':usuario', $usuario, PDO::PARAM_STR); // ← Se asume definida previamente en el flujo
        $stmt->bindParam(':fecha', $fecha, PDO::PARAM_STR);
    }

    // Enlazar parámetros comunes
    $stmt->bindParam(':idp', $idp, PDO::PARAM_INT);
    $stmt->bindParam(':idu', $idu, PDO::PARAM_INT);

    if ($stmt->execute()) {
        $res = "ok";
        $msn = "";
    } else {
        $res = "error";
        $msn = "Surgió un error al modificar el permiso.";
    }

    $datos[] = ["res" => $res, "msn" => $msn];
    echo json_encode($datos);
}

else 
if ($opcion == "REGLANEGOCIO") {
    $disreg = "disabled";
    $p67 = isset($permisosUsuario[67]);
    if ($p67 > 0) {
        $disreg = "";
    }
    $reg = new General();
    $datr = $reg->fnReglas();

    $hirn = $datr['hirn'];
    $hfrn = $datr['hfrn'];
    $han  = $datr['han'];
    $hanpm = $datr['hanpm'];
    $hao  = $datr['hao'];
    $vhan = $datr['vhan'];
    $vhanpm = $datr['vhanpm'];
    $vhao = $datr['vhao']; 
    $hmes = $datr['hmes'];  
    $hsemana = $datr['hsemana'];
    $limex = $datr['limex'];
    $limexsemana = $datr['limexsemana'];

    // MIGRADO A PDO
    $sql = "SELECT hor_clave_int, hor_nombre, hor_porcentaje, hor_codigo, hor_descripcion FROM tbl_horas";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $horas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <!-- HTML -->
    <form id="frmreglas" name="frmreglas">
        <div class="row">
            <div class="col-md-7">
                <div class="card">
                    <div class="card-body">

                        <!-- BLOQUE 1 -->
                        <div class="row">
                            <div class="col-md-6">
                                <label>Hora Inicial RN:</label>
                                <select id="selhirn" class="form-control form-control-sm selectpicker" required data-parsley-error-message="Seleccionar hora inicial recargo nocturno" data-parsley-errors-container="#msn-error1">
                                    <option value=""></option>
                                    <?php $reg->cargarHoras("00:00", $hirn, 24, 1); ?>
                                </select>
                                <span id="msn-error1"></span>
                            </div>
                            <div class="col-md-6">
                                <label>Hora Final RN:</label>
                                <select id="selhfrn" class="form-control form-control-sm selectpicker" required data-parsley-error-message="Seleccionar hora final recargo nocturno" data-parsley-errors-container="#msn-error2">
                                    <option value=""></option>
                                    <?php $reg->cargarHoras("00:00", $hfrn, 24, 1); ?>
                                </select>
                                <span id="msn-error2"></span>
                            </div>
                        </div>

                        <!-- BLOQUE 2 -->
                        <div class="row">
                            <div class="col-md-6">
                                <label>Hora Alimentación Nómina:</label>
                                <select id="selhan" class="form-control form-control-sm selectpicker" required data-parsley-error-message="Seleccionar hora alimentación nómina" data-parsley-errors-container="#msn-error3">
                                    <option value=""></option>
                                    <?php $reg->cargarHoras("00:00", $han, 24, 1); ?>
                                </select>
                                <span id="msn-error3"></span>
                            </div>
                            <div class="col-md-6">
                                <label>Valor Hora Alimentación Nómina:</label>
                                <input type="text" onkeypress="return validar_texto(event)" class="form-control form-control-sm currency" id="txtvhan" value="<?php echo $vhan; ?>" required>
                            </div>
                        </div>

                        <!-- BLOQUE 3 -->
                        <div class="row">
                            <div class="col-md-6">
                                <label>Hora Alimentación Nómina > 11:00pm:</label>
                                <select id="selhanpm" class="form-control form-control-sm selectpicker" required data-parsley-error-message="Seleccionar hora alimentación nómina" data-parsley-errors-container="#msn-error5">
                                    <option value=""></option>
                                    <?php $reg->cargarHoras("23:00", $hanpm, 24, 1); ?>
                                </select>
                                <span id="msn-error5"></span>
                            </div>
                            <div class="col-md-6">
                                <label>Valor Hora Alimentación Nómina > 11:00pm:</label>
                                <input type="text" onkeypress="return validar_texto(event)" class="form-control form-control-sm currency" id="txtvhanpm" value="<?php echo $vhanpm; ?>" required>
                            </div>
                        </div>

                        <!-- BLOQUE 4 -->
                        <div class="row">
                            <div class="col-md-6">
                                <label>Hora Alimentación Obra:</label>
                                <select id="selhao" class="form-control form-control-sm selectpicker" required data-parsley-error-message="Seleccionar hora alimentación obra" data-parsley-errors-container="#msn-error4">
                                    <option value=""></option>
                                    <?php $reg->cargarHoras("00:00", $hao, 24, 1); ?>
                                </select>
                                <span id="msn-error4"></span>
                            </div>
                            <div class="col-md-6">
                                <label>Valor Hora Alimentación Obra:</label>
                                <input type="text" onkeypress="return validar_texto(event)" class="form-control form-control-sm currency" id="txtvhao" value="<?php echo $vhao; ?>" required>
                            </div>
                        </div>

                        <!-- BLOQUE 5 -->
                        <div class="row">
                            <div class="col-md-3">
                                <label>Total Horas Semana:</label>
                                <input type="text" onkeypress="return validar_texto(event)" class="form-control form-control-sm" id="txthsemana" value="<?php echo $hsemana; ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label>Total Horas Mes:</label>
                                <input type="text" onkeypress="return validar_texto(event)" class="form-control form-control-sm" id="txthmes" value="<?php echo $hmes; ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label>Límite Extras Día:</label>
                                <input type="text" onkeypress="return validar_texto(event)" class="form-control form-control-sm" id="txtlimex" value="<?php echo $limex; ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label>Límite Extras Semana:</label>
                                <input type="text" onkeypress="return validar_texto(event)" class="form-control form-control-sm" id="txtlimexsemana" value="<?php echo $limexsemana; ?>" required>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- BLOQUE DERECHA -->
            <div class="col-md-5">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Porcentajes Horas Extras</h3>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>CÓDIGO</th>
                                    <th>DESCRIPCIÓN</th>
                                    <th>%</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($horas as $dath): ?>
                                    <tr>
                                        <td class="p-0"><?php echo $dath['hor_codigo']; ?></td>
                                        <td class="p-0"><?php echo $dath['hor_descripcion']; ?></td>
                                        <td class="p-0">
                                            <input type="text" id="txtpor_<?php echo $dath['hor_clave_int']; ?>" 
                                                onkeyup="CRUDUSUARIOS('UPDATEEXTRA','<?php echo $dath['hor_clave_int']; ?>')" 
                                                onkeypress="return validar_texto(event)" 
                                                value="<?php echo $dath['hor_porcentaje']; ?>" 
                                                class="form-control form-control-sm">
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <script>
        $('select').on('change', function () {
            $(this).parsley().validate();
        });
    </script>
    <?php
    echo "<script>INICIALIZARCONTENIDO();</script>";
}

else 
if ($opcion == "GUARDARREGLA") {
    $hirn = $_POST['hirn'];
    $hfrn = $_POST['hfrn'];
    $vhan = $_POST['vhan'];
    $vhao = $_POST['vhao'];
    $hian = $_POST['han'];
    $hfao = $_POST['hao'];   
    $hmes = $_POST['hmes'];
    $hsemana = $_POST['hsemana'];
    $vhanpm = $_POST['vhanpm'];
    $hianpm = $_POST['hanpm'];
    $limex = $_POST['limex'];
    $limexsemana = $_POST['limexsemana'];

    $sql = "UPDATE tbl_reglas 
            SET reg_hi_rn = :hirn,
                reg_hf_rn = :hfrn,
                reg_ali_nomina = :hian,
                reg_ali_obra = :hfao,
                reg_val_nomina = :vhan,
                reg_val_obra = :vhao,
                reg_hor_mes = :hmes,
                reg_hor_semana = :hsemana,
                reg_ali_nomina_pm = :hianpm,
                reg_val_nomina_pm = :vhanpm,
                reg_lim_extras = :limex,
                reg_lim_extras_semana = :limexsemana";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':hirn', $hirn);
    $stmt->bindParam(':hfrn', $hfrn);
    $stmt->bindParam(':hian', $hian);
    $stmt->bindParam(':hfao', $hfao);
    $stmt->bindParam(':vhan', $vhan);
    $stmt->bindParam(':vhao', $vhao);
    $stmt->bindParam(':hmes', $hmes);
    $stmt->bindParam(':hsemana', $hsemana);
    $stmt->bindParam(':hianpm', $hianpm);
    $stmt->bindParam(':vhanpm', $vhanpm);
    $stmt->bindParam(':limex', $limex);
    $stmt->bindParam(':limexsemana', $limexsemana);

    if ($stmt->execute()) {
        $res = "ok";
        $msn = "Regla de negocio modificada correctamente";
    } else {
        $res = "error";
        $msn = "Surgió un error al modificar la regla de negocio";
    }

    $datos[] = ["res" => $res, "msn" => $msn];
    echo json_encode($datos);
}

else 
if ($opcion == "UPDATEEXTRA") {
    $id = $_POST['id'];
    $por = $_POST['por'];

    $sql = "UPDATE tbl_horas 
            SET hor_porcentaje = :porcentaje, 
                hor_usu_actualiz = :usuario, 
                hor_fec_actualiz = :fecha 
            WHERE hor_clave_int = :id";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':porcentaje', $por, PDO::PARAM_STR);
    $stmt->bindParam(':usuario', $usuario, PDO::PARAM_STR); // ya definido en el sistema
    $stmt->bindParam(':fecha', $fecha, PDO::PARAM_STR);     // ya definido en el sistema
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        $res = "ok";
        $msn = "";
    } else {
        $res = "error";
        $msn = "Surgió un error al actualizar hora";
    }

    $datos[] = ["res" => $res, "msn" => $msn];
    echo json_encode($datos);
}

if ($opcion == "AJUSTECUENTA") 
{
    $idusu=$_POST['id'];
    $sql = "SELECT * FROM tbl_usuarios WHERE usu_clave_int = :id AND est_clave_int != 3";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $idUsuario, PDO::PARAM_INT);
    $stmt->execute();
    $datausu = $stmt->fetch(PDO::FETCH_ASSOC);
    $img = $datausu['usu_imagen'];
    $Nombre = $datausu['usu_nombre'];
    $Apellido = $datausu['usu_apellido'];
    $Fenaci = $datausu['usu_fecha_naci'];
    $Celular = $datausu['usu_celular'];
    $fijo = $datausu['usu_fijo'];
    $Correo = $datausu['usu_correo'];
    //$Direccion = $datausu['usu_direccion'];
    $Perfil = $datausu['prf_clave_int'];
    $estado = $datausu['est_clave_int'];
    ?>
    <form  id="frmusuario" name="frmusuario" action="_" method="post" enctype="multipart/form-data" class="form-horizontal">
     
          <div class="row">
              <div class="col-md-6">
                  <label for="txtnombre1">Nombre:</label>
                  <input type="text" disabled class="form-control edit" id="txtnombre" name="txtnombre" required data-parsley-error-message="Diligenciar el nombre" value="<?php echo $Nombre ?>">
              </div>
              <div class="col-md-6">
                  <label for="txtapellido1">Apellido:</label>
                  <input type="text" disabled class="form-control edit" id="txtapellido" name="txtapellido" value="<?php echo $Apellido ?>">
              </div>
          </div>

          <div class="row">
              <div class="col-md-6">
                  <label for="txtcelular1">Celular:</label>
                  <input type="text" class="form-control edit" id="txtcelular" name="txtcelular" value="<?php echo $Celular ?>">
              </div>
              <div class="col-md-6">
                  <label for="txtfijo1">Teléfono fijo:</label>
                  <input type="text" class="form-control edit" id="txtfijo" name="txtfijo"value="<?php echo $fijo ?>">
              </div>
          </div>

          <div class="row">
              <div class="col-md-12">
                  <label for="txtcorreo1">Correo:</label>
                  <input type="email" disabled	data-parsley-type="email" class="form-control edit" id="txtcorreo" name="txtcorreo1" value="<?php echo $Correo ?>" required autocomplete="nope">
              </div>
              <div class="col-md-6 hide">
                  <label for="txtdireccion1">Dirección:</label>
                  <input type="text" class="form-control edit" id="txtdireccion" name="txtdireccion" value="<?php echo $Direccion ?>">
              </div>
          </div>

          <div class="row">
              <div class="col-md-6">
                  <label for="txtclave1">Clave:</label>
                  <input type="password" class="form-control edit" id="txtcontrasena" name="txtcontrasena" autocomplete="nope">
              </div>
              <div class="col-md-6">
                  <label for="txtclave22">Repita la clave:</label>
                  <input type="password" class="form-control" id="txtverificar" name="txtverificar" data-parsley-equalto="#txtcontrasena" autocomplete="nope">
              </div>
          </div>

          <div class="row hide">
              <br>
              <div class="col-md-6">
                  <a class="btn btn-warning" id ="btnguardarajuste" onclick="CRUDUSUARIOS('GUARDARAJUSTES','<?php echo $idUsuario ?>')" >Guardar</a>
              </div>
          </div>
        
      <script type="text/javascript">
        $(document).ready(function(){
            var datos = [];
            var datosactual = [];
            var datospass = [];

            var Pass = '';
            var Veri = '';

            var Nombre = '<?php echo $Nombre; ?>';
            var Apellido = '<?php echo $Apellido; ?>';
            var Celular = '<?php echo $Celular; ?>';
            var fijo = '<?php echo $fijo; ?>';
            var Correo = '<?php echo $Correo; ?>';

            var datosactual = [Nombre,Apellido,Celular,fijo,Correo];
            var datosactualpass = [Pass,Veri]
            console.log(datosactual);

            $('#btnguardar').addClass('hide');
            $('.edit').on('keyup change',function(e){
                var Nombre1 = $('#txtnombre').val();
                var Apellido1 = $('#txtapellido').val();
                var Celular1 =  $('#txtcelular').val();
                var fijo1 =  $('#txtfijo').val();
                var Correo1 =  $('#txtcorreo').val();

                var Pass1 = $('#txtcontrasena').val();
                var Veri1 = $('#txtverificar').val();

                var Correo1 =  $('#txtcorreo').val();
                //var Direccion1 =  $('#txtdireccion').val();
                var datosnuevos = [Nombre1,Apellido1,Celular1,fijo1,Correo1];

                var datospass = [Pass1,Veri1];
                //console.log(datosnuevos);
                //console.log(datosactual);

                var dat1 = datosnuevos.toString();
                var dat2 = datosactual.toString();
                console.log(dat1);
                console.log(dat2);
                console.log("bont<?php echo $id; ?>");
                
                if(datosactualpass.toString() !== datospass.toString()){
                      $('#btnguardar').removeClass('hide');
                }
                else
                if(dat1 === dat2){
                      $('#btnguardar').addClass('hide');
                }                
                else
                {
                    $('#btnguardar').removeClass('hide');
                }
            })
            // Toolbar extra buttons
        });
      </script>
    </form>
    <iframe src="about:blank" name="null" style="display:none"></iframe>
    <?php 
    //echo "<script>iniValidar('formcole');</script>";
}

else 
if ($opcion == "GUARDARAJUSTE") {
    $id       = $_POST['id'];
    $Nombre   = $_POST['nom'];
    $Apellido = $_POST['ape'];
    $Celular  = $_POST['cel'];
    $fijo     = $_POST['fij'];
    $Correo   = $_POST['ema'];
    $pass     = $_POST['pass1'];
    $rutanew  = "";
    $errorv   = "";
    $sqle     = "";

    // Encriptar contraseña si viene
    $pass = $pass != "" ? encrypt($pass, 'p4v4sAp') : "";

    // Actualizar datos básicos del usuario
    $sql = "UPDATE tbl_usuarios 
            SET usu_nombre = :nombre,
                usu_apellido = :apellido,
                usu_celular = :celular,
                usu_fijo = :fijo,
                usu_correo = :correo,
                usu_usu_actualiz = :usuario,
                usu_fec_actualiz = :fecha 
            WHERE usu_clave_int = :id";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':nombre', $Nombre);
    $stmt->bindParam(':apellido', $Apellido);
    $stmt->bindParam(':celular', $Celular);
    $stmt->bindParam(':fijo', $fijo);
    $stmt->bindParam(':correo', $Correo);
    $stmt->bindParam(':usuario', $usuario); // ← definido globalmente
    $stmt->bindParam(':fecha', $fecha);     // ← definido globalmente
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);

    if ($stmt->execute()) {
        $res = 'ok';
        $msn = "Información actualizada correctamente";

        // Si viene nueva contraseña, actualiza
        if ($pass != "") {
            $sqlPass = "UPDATE tbl_usuarios SET usu_clave = :clave WHERE usu_clave_int = :id";
            $stmtPass = $conn->prepare($sqlPass);
            $stmtPass->bindParam(':clave', $pass);
            $stmtPass->bindParam(':id', $id, PDO::PARAM_INT);
            $stmtPass->execute();
        }
    } else {
        $res = 'error';
        $msn = "Surgió un error al actualizar datos de usuario.";
    }

    $datos[] = [
        "res"   => $res,
        "imagen"=> $rutanew . "?" . time(),
        "msn"   => $msn,
        "error" => $errorv,
        "sq"    => $sqle
    ];

    echo json_encode($datos);
}

else if($opcion=="INFOHORARIO")
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
    <table class="table table-bordered" id="tbHorarios">
        <thead>
            <tr>
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
        <tbody>
            <tr>
               <td><?php echo $nom;?></td>
               <td><?php echo $lun;?></td>
               <td><?php echo $mar;?></td>
               <td><?php echo $mie;?></td>
               <td><?php echo $jue;?></td>
               <td><?php echo $vie;?></td>
               <td><?php echo $sab;?></td>
               <td><?php echo $dom;?></td>              
        </tfoot>        
    </table>
    <?php
}