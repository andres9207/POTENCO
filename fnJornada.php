<?php
session_start();
include('../../data/conexion.php');
error_reporting(0);
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require ('../../PHPMailer-master/src/PHPMailer.php');
require ('../../PHPMailer-master/src/Exception.php');
require ('../../PHPMailer-master/src/SMTP.php');
require_once "../../controladores/general.controller.php";

// cookie que almacena el numero de identificacion de la persona logueada
$usuario   = $_COOKIE['usuarioPo'];
$idUsuario = $_COOKIE['idusuarioPo'];
$perfil    = $_SESSION["perfilPo"];

include '../../data/validarpermisos.php';
setlocale(LC_TIME, 'es_CO.UTF-8'); 
date_default_timezone_set('America/Bogota');
$fecha  = date("Y/m/d H:i:s");
$semanaactual = date('W',  strtotime($fecha));

if($semanaactual<10)
{
    $semanaactual = "0".$semanaactual;
}
$opcion = $_POST['opcion'];
if($opcion=="NUEVO" || $opcion=="EDITAR" || $opcion=="APPROVED" || $opcion=="EDITARJORNADA")
{
    $id = $_POST['id'];
    $id = decrypt($id,'p4v4sAp');
    //echo "SELECT  j.usu_clave_int id, j.jor_ano ano,j.jor_semana sem FROM tbl_jornada j WHERE jor_clave_int = '".$id."'";
    $con = mysqli_query($conectar, "SELECT  j.usu_clave_int id, j.jor_ano ano,j.jor_semana sem FROM tbl_jornada j WHERE jor_clave_int = '".$id."'");
    $dat = mysqli_fetch_array($con);
    $emp = $dat['id'];
    $ano = $dat['ano'];
    if($ano=="" || $ano==date('Y')){ $ano = date('Y'); $sf = (int)$semanaactual; }else { $sf = 52; }
    $sem = $dat['sem'];
    
    $disinp = ($id>0)?"disabled":"";
    ?>
    <div class="col-md-12" title="<?php echo $id;?>">
        <form id="frmjornada" name="frmjornada" data-parsley-validate="">
            <div class="row">
                <div class="col-md-6">
                    <label for="">Empleado</label>
                    <select <?php echo $disinp;?> id="selempleado" class="form-control form-control-sm selectpicker" onchange= "CRUDGENERAL('CARGARSEMANAS', 'selsemana', 'selmes', 'selano', 'JORNADA','','selempleado')" required data-parsley-error-message="Seleccionar empleado" data-parsley-errors-container="#msn-error1" >
                    <option value=""></option>
                    <?php
                        $selectEmp = new General();
                        $selectEmp -> cargarEmpleados($emp);
                    ?>
                    </select>
                    <span id="msn-error1"></span>
                </div>
                <div class="col-md-3 hide">
                    <label for="selobra">Obra</label>
                    <select <?php echo $disinp;?> id="selobra" name="selobra" class="form-control form-control-sm selectpicker" data-live-search="true" >
                        <option value=""></option>
                        <?php
                        $selectObr = new General();
                        $selectObr -> cargarObras("",1);
                        ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="selano">Año</label>
                    <select <?php echo $disinp;?> name="selano" id="selano" class="form-control form-control-sm selectpicker" onchange="CRUDGENERAL('CARGARSEMANAS', 'selsemana', 'selmes', 'selano', 'JORNADA','','selempleado')"   data-live-search="true" >
                        <?php
                        //onchange="CRUDGENERAL('CARGARMES','','selmes','selano','','','selempleado')"
                        $selectAno = new General();
                        $selectAno -> cargarAnos(2020,date('Y'),$ano,"DESC",1);
                        ?>

                    </select>
                </div>
                <div class="col-md-3 hide">
                    <label for="selmes">Mes</label>
                    <select <?php echo $disinp;?> name="selmes" id="selmes" class="form-control form-control-sm selectpicker" onchange="CRUDGENERAL('CARGARSEMANAS', 'selsemana', 'selmes', 'selano', 'JORNADA','','selempleado')" data-live-search="true" >                    
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="selsemana">Semana</label>
                    <select <?php echo $disinp;?> name="selsemana" id="selsemana" class="form-control form-control-sm selectpicker" onchange="CRUDJORNADA('CARGARSEMANA', '')" required data-parsley-error-message="Seleccionar semana"  data-parsley-errors-container="#msn-error2" data-live-search="true" data-id="<?php echo $sem;?>">
                    <?php 
                    
                    
                    $sema= new General();
                    $sema->cargarSemanas($ano,'',1,$sf,$sem,"DESC",1,$emp);    
                    ?>
                    </select>
                    <span id="msn-error2"></span>
                </div>
                <?php if($opcion!="EDITARJORNADA"){ ?>
                <div class="col-md-1"><br>
                    <button class="btn btn-primary btn-sm" type="button" id="btnmasivo" onclick="CRUDJORNADA('REGISTROMASIVO')">Masivo</button>
                </div>
                <?php } ?>
            </div>
            <div class="row" id="divsemana"></div>
        </form>
    </div>
    
   <script>
       var sem = $('#selsemana');
       var optcla = $('#selsemana>option:selected').attr('class');
       $('#selsemana').attr('data-style',optcla);
       sem.on('change',function(){
        var optcla = $('#selsemana>option:selected').attr('class');
        $(this).attr('data-style',optcla).selectpicker('refresh');
       })

   </script>
    <?php
    //echo "<script>CRUDJORNADA('CARGARSEMANA');</script>";
    // echo "<script>CRUDGENERAL('CARGARMES','selsemana','selmes','selano','JORNADA');</script>";
    if($id>0)
    {
        echo "<script>CRUDJORNADA('CARGARSEMANA');</script>";
    }
    else
    {  
        echo "<script>CRUDGENERAL('CARGARSEMANAS', 'selsemana', 'selmes', 'selano', 'JORNADA','');</script>";
    }
    
}
else if($opcion=="CARGARSEMANA")
{
    // $msn = json_decode($msnGeneral,true);
    // echo $msn->correoInvalido;
    $emp = $_POST['emp'];
    $ano = $_POST['ano'];
    $mes = $_POST['mes'];
    $sem = $_POST['sem'];
    $ini = $_POST['ini'];
    $fin = $_POST['fin'];
    setlocale(LC_ALL,"es_ES.utf8","es_ES","esp");
    //error_reporting(0);
    if($sem=="" || $emp=="")
    {
        ?>
        <div class="col-md-12">
            <div class='alert alert-light text-center mt-2'>Seleccionar el empleado y la semana</div>
        </div>
        <?php
       
    }
    else
    {
        
        $consem = mysqli_query($conectar, "SELECT jor_clave_int, jor_semana,jor_estado, jor_nota FROM tbl_jornada where usu_clave_int = '".$emp."' and jor_semana = '".$sem."'");
        $numsem = mysqli_num_rows($consem);
        $datsem = mysqli_fetch_array($consem);
        $idjor = $datsem['jor_clave_int'];
        $semjor = $datsem['jor_semana'];
        $estjor = $datsem['jor_estado'];
        $notjor = br2nl($datsem['jor_nota']);
        $visbot = ($estjor==1 && $p99<=0)?"hide":""; // Ocultar botones si la semana ya esta aprobada
        $disinp = ($estjor==1 && $p99<=0)?"disabled":""; // Desabilitar input si la semana ya fue aprobada

        $contot = mysqli_query($conectar,"SELECT SUM(calculoHoras3(jd.jod_fecha,joh_inicio,joh_fin)) as totalhoras FROM tbl_jornada_dias jd JOIN tbl_jornada_horas jh ON jh.jod_clave_int = jd.jod_clave_int JOIN tbl_tipo_novedad tn on tn.tin_clave_int = jh.tin_clave_int WHERE tn.tin_tipo = 1 and jd.usu_clave_int = '".$emp."' and jd.jod_semana = '".$sem."'");
        $dattot = mysqli_fetch_array($contot);
        $totalhoras = $dattot['totalhoras'];
        $totalhoras = ($totalhoras=="" || $totalhoras==NULL)?0:$totalhoras;
        ?>
        <div class="col-md-12 table-responsive" >
        <table class="table table-striped table-bordered mt-1" style="width: 100%" id="tbJornada">
        <!--<thead>
                <tr>
                    <th colspan="2" class="dt-head-center">FECHA</th>
                    <th colspan="2" class="dt-head-center text-center">MAÑANA</th>
                    <th colspan="2" class="dt-head-center text-center">TARDE</th>
                    <th colspan="2" class="dt-head-center text-center">NOCHE</th>
                    <th rowspan="2" class="align-middle text-center">OBSERVACIONES</th>                
                </tr>
                <tr>
                    <th class="bg-terracota text-center">Dia Semana</th>
                    <th class="bg-terracota text-center">Dia/Mes/Año</th>
                    <th class="bg-terracota text-center">Desde</th>
                    <th class="bg-terracota text-center">Hasta</th>
                    <th class="bg-terracota text-center">Desde</th>
                    <th class="bg-terracota text-center">Hasta</th>
                    <th class="bg-terracota text-center">Desde</th>
                    <th class="bg-terracota text-center">Hasta</th>                                
                </tr>
            </thead> -->
            <tbody>
                <?php
                $fechaInicio=strtotime($ini);
                $fechaFin=strtotime($fin);
                for($i=$fechaInicio; $i<=$fechaFin; $i+=86400){
               

                    $fec = date("Y-m-d", $i);
                    $dia = strftime("%A", strtotime($fec));
                    $ids = strtotime($fec);

                    //VERIFICACION DE SI DIA ES FESTIVO
                    $confec = mysqli_query($conectar,"select diaHabil('".$fec."') as di");
                    $datfec = mysqli_fetch_array($confec);
                    $fes = $datfec['di'];
                    $bgfes = ($fes==1)?"bg-primary":""; //dia festivo
                    $titfes = ($fes==1)?"Dia Festivo":"";
                    //$bgfes = (date("D",strtotime($fec))=="Sun")?"bg-gradient-primary ":""; //dia domingo

                    $nov = 0;
                    $veri = mysqli_query($conectar, "SELECT jod_clave_int, jod_total, tin_clave_int FROM tbl_jornada_dias where usu_clave_int = '".$emp."' and jod_semana = '".$sem."' and jod_fecha = '".$fec."'");
                    $numv = mysqli_num_rows($veri);
                    $idfec = 0;
                    if($numv>0)
                    {
                        $datfec = mysqli_fetch_array($veri);
                        $idfec = $datfec['jod_clave_int'];
                        $totald = $datfec['jod_total'];
                        $nov = $datfec['tin_clave_int'];
                    }
                    else
                    {
                        $insfec = mysqli_query($conectar, "INSERT INTO tbl_jornada_dias(jod_fecha, usu_clave_int, jod_semana,jod_usu_actualiz,jod_fec_actualiz) VALUES('".$fec."','".$emp."','".$sem."','".$usuario."','".$fecha."')");
                        if($insfec>0)
                        {
                            $idfec = mysqli_insert_id($conectar);
                        }
                        else
                        {
                            echo "<tr><td colspan='2'>No inserto dia ".$fec."</td></tr>";
                        }                        
                    }
                    $totald = ($totald<=0)?0:$totald;
                    if($idfec>0)
                    {
                        ?>
                        <tr title="<?php echo $titfes;?>" class="<?php echo $bgfes;  if(strtotime($fec)>strtotime(date('Y-m-d'))){ echo " hide"; }?>">
                            <td class="text-center p-1"><span class="font-weight-bold"><?php echo ucfirst(($dia))."  (".$fec.")</span> <strong>Horas: </strong><span id='totaldia_".$idfec."'>".$totald."</span>";?></td>
                            <td class="text-center p-1" style="width:10%">
                                <select class="form-control form-control-sm selectpicker" id="selnovedaddia_<?php echo $idfec;?>" onchange="CRUDJORNADA('ACTUALIZARDIA','<?PHP echo $idfec;?>')" title="Seleccione Novedad">
                                <option value=""></option>
                                    
                                    <?php 
                                        $selectNov = new General();
                                        $selectNov -> cargarTipoNovedad($nov,1, 2);
                                    ?>
                                </select>
                            </td>
                            <td class="p-1 text-center" style="width:20px" ><button type="button" class="btn btn-sm btn-success <?php echo $visbot;?>" onclick="CRUDJORNADA('AGREGARHORA','<?PHP echo $idfec;?>')"><i class="fas fa-plus"></i></button></td>
                        </tr>
                        <tr class="<?php if(strtotime($fec)>strtotime(date('Y-m-d'))){ echo "hide"; }?>">
                            <td id="tdfec<?php echo $idfec;?>" colspan="3" class="pt-0 pb-1 pl-0 pr-0">
                                <?php
                                $conhor = mysqli_query($conectar, "SELECT joh_clave_int id, obr_clave_int obr, date_format(joh_inicio,'%H:%i') ini, date_format(joh_fin,'%H:%i') fin, tin_clave_int tin,joh_observacion obs from tbl_jornada_horas WHERE jod_clave_int = '".$idfec."'");
                                $numhor = mysqli_num_rows($conhor);
                                if($numhor<=0)
                                {
                                    ?>
                                        <div class='alert alert-light text-center m-0'>No hay Horarios Laborados</div>
                                    <?php
                                }
                                else
                                {
                                    ?>
                                    <table class="table m-0" cellspading="0" cellspacing="0" id="tbHoras_<?php echo $idfec;?>" style="width:100%">
                                    <?php
                                    $hori = array();
                                    $hori[] = "06:00";
                                    for($nh=1;$nh<=$numhor;$nh++)
                                    {
                                        $dath = mysqli_fetch_array($conhor);
                                        $joh = $dath['id'];
                                        $obr = $dath['obr'];
                                        $ini = $dath['ini'];
                                        $fin = $dath['fin'];
                                        $tin = $dath['tin']; if($tin=="" || $tin==NULL || $tin==0){ $tin = 1; }
                                        $obs = br2nl($dath['obs']);
                                        
                                        //$ini = ($ini=="" || $ini=="00:00")?"06:00":$ini;
                                        $tot = hourdiff($ini,$fin, true);
                                        $inilis = $hori[$nh-1];
                                        $hori[] = $fin;
                                        ?>
                                        <tr id="rowh_<?php echo $joh;?>" class=""> 
                                            <td  class="p-1" style="width:30%">
                                                <select title="Seleccione Obra"  id="selobra<?php echo $joh;?>" name="selobra<?php echo $joh;?>" class="form-control form-control-sm selectpicker seljh" onchange="CRUDJORNADA('ACTUALIZARHORA','<?php echo $joh;?>','<?php echo $idfec;?>')" required data-parsley-errors-container="#msn-errorobr<?php echo $joh;?>" <?php echo $disinp;?>>
                                                    
                                                    <?php
                                                    $selectObr = new General();
                                                    $selectObr -> cargarObras($obr,1);
                                                    ?>
                                                </select>
                                                <span id="msn-errorobr<?php echo $joh;?>"></span>
                                            </td>
                                            
                                            <td  class="p-1" style="width:10%">
                                                <div class="input-group bootstrap-timepicker timepicker hide">
                                                    <input class="form-control timepicker1" id="txtinicio<?php echo $joh;?>"  placeholder="Seleccione hora" type="text" value="<?php echo $ini;?>">
                                                    <div class="input-group-prepend">
                                                        <i class="fas fa-clock"></i>
                                                    </div>
                                                </div>
                                                <select title="Hora Inicio" data-container="body" name="selinicio<?php echo $joh;?>" id="selinicio<?php echo $joh;?>" class="form-control form-control-sm selectpicker seljh" onchange="CRUDJORNADA('ACTUALIZARHORA','<?php echo $joh;?>','<?php echo $idfec;?>')" required data-parsley-errors-container="#msn-errorini<?php echo $joh;?>" <?php echo $disinp;?>>
                                                <option value=""></option>
                                                <?php
                                                $selectHora = new General();
                                                $selectHora -> cargarHoras("00:00",$ini,24,1);
                                                ?>                        
                                                </select>
                                                <span id="msn-errorini<?php echo $joh;?>"></span>
                                            </td>
                                        
                                            <td  class="p-1" style="width:10%">
                                                <select title="Hora Fin"  data-container="body" name="selfin<?php echo $joh;?>" id="selfin<?php echo $joh;?>" class="form-control form-control-sm selectpicker seljh" onchange="CRUDJORNADA('ACTUALIZARHORA','<?php echo $joh;?>','<?php echo $idfec;?>')" required data-parsley-errors-container="#msn-errorfin<?php echo $joh;?>" <?php echo $disinp;?>>
                                                <option value=""></option>
                                                <?php
                                                $selectHora = new General();
                                                $selectHora -> cargarHoras("00:00",$fin,24,1);
                                                ?>    
                                                </select>
                                                <span id="msn-errorfin<?php echo $joh;?>"></span>
                                            </td>
                                            <td class="p-1 text-center" style="width:10%"><span id="spantot<?php echo $joh;?>"><?php echo $tot;?></span></td>
                                            <td  class="p-1" style="width:15%">
                                              
                                                <select title="Seleccione Novedad" id="seltiponovedad<?php echo $joh;?>" name="seltiponovedad<?php echo $joh;?>" class="form-control form-control-sm selectpicker seljh" onchange="CRUDJORNADA('ACTUALIZARHORA','<?php echo $joh;?>','<?php echo $idfec;?>')" required data-parsley-errors-container="#msn-errortin<?php echo $joh;?>" <?php echo $disinp;?>>
                                                    
                                                    <?php
                                                    $selectTin = new General();
                                                    $selectTin -> cargarTipoNovedad($tin,1);
                                                    ?>
                                                </select>
                                                <span id="msn-errortin<?php echo $joh;?>"></span>
                                            </td>
                                            <td  class="p-1" style="width:100px">
                                                <textarea placeholder="Observación" name="txtobservacion<?php echo $joh;?>" id="txtobservacion<?php echo $joh;?>" class="form-control form-control-sm" rows="1" onchange="CRUDJORNADA('ACTUALIZARHORA','<?php echo $joh;?>','<?php echo $idfec;?>')" <?php echo $disinp;?>><?php echo $obs;?></textarea>
                                            </td>                                       
                                            <td  class="p-1" style="width:20px"><a class="btn btn-sm bg-red text-white <?php echo $visbot;?>" onclick="CRUDJORNADA('ELIMINARHORA','<?php echo $joh;?>','<?php echo $idfec;?>')"><i class="fas fa-trash"></i></a></td>
                                        </tr>
                                        
                                        <?php
                                    }
                                    ?>
                                    </table>
                                    <?php
                                }
                                ?>
                            </td>
                        </th>
                        <?php
                        
                    }
                }
                ?>
            </tbody>
            <tfoot>
                <tr>
                    <th class="bg-terracota text-right" colspan="3">Total Horas: <span id="spantotalhoras"><?PHP echo number_format($totalhoras,2,".","");?></span></th>                                           
                </tr>
            </tfoot>
        </table>
        </div>
        <div class="col-md-12">
            Notas aclaratorias sobre las observaciones expuestas y/o ampliación de la información:
            <textarea id="txtnota" class="form-control form-control-sm" rows="1"><?php echo $notjor;?></textarea>
        </div>
        <div class="col-md-12 ">
           <button type="button" class="btn btn-terracota float-right text-white mt-1 <?php echo $visbot;?>" onclick="CRUDJORNADA('GUARDARSEMANA')">Guardar Semana</button>
        </div>
        <script>
            $('.seljh').on('change',function(){
                $(this).parsley().validate();
            })
        </script>
        <!-- <script src="jsdatatable/jornadas/jslistajornada.js?<?php echo time();?>"></script> -->
        <?php
    }
    echo "<script>INICIALIZARCONTENIDO();</script>";
}
else if($opcion=="CARGARHORARIOS")
{
    $idfec = $_POST['id'];
    $conhor = mysqli_query($conectar, "SELECT joh_clave_int id, obr_clave_int obr, date_format(joh_inicio,'%H:%i') ini, date_format(joh_fin,'%H:%i') fin, tin_clave_int tin,joh_observacion obs from tbl_jornada_horas WHERE jod_clave_int = '".$idfec."'");
    $numhor = mysqli_num_rows($conhor);
    if($numhor<=0)
    {
        ?>
            <div class='alert alert-light text-center m-0'>No hay Horarios Laborados</div>
        <?php
    }
    else
    {
        ?>
        <table class="table m-0" cellspading="0" cellspacing="0">
        <?php
        $hori = array();
        $hori[] = "06:00";
        for($nh=1;$nh<=$numhor;$nh++)
        {
            $dath = mysqli_fetch_array($conhor);
            $joh = $dath['id'];
            $obr = $dath['obr'];
            $ini = $dath['ini'];
            $fin = $dath['fin'];
            $tin = $dath['tin']; if($tin=="" || $tin==NULL || $tin==0){ $tin = 1; }
            $obs = br2nl($dath['obs']);
            $ini = ($ini=="" || $ini=="00:00")?"06:00":$ini;

           
            $tot = hourdiff($ini,$fin,true);

            $inilis = $hori[$nh-1];

            $hori[] = $fin;
            
            
            ?>
            <tr id="rowh_<?php echo $joh;?>"> 
                <td class="p-1" style="width:30%">
                    <select title="Seleccionar Obra" id="selobra<?php echo $joh;?>" name="selobra<?php echo $joh;?>" class="form-control form-control-sm selectpicker seljh" onchange="CRUDJORNADA('ACTUALIZARHORA','<?php echo $joh;?>','<?php echo $idfec;?>')" required data-parsley-errors-container="#msn-errorobr<?php echo $joh;?>">
                      
                        <?php
                        $selectObr = new General();
                        $selectObr -> cargarObras($obr,1);
                        ?>
                    </select>
                    <span id="msn-errorobr<?php echo $joh;?>"></span>
                </td>
                <td class="p-1" style="width:10%">
                    <div class="input-group bootstrap-timepicker timepicker hide">
                        <input class="form-control timepicker1" id="txtinicio<?php echo $joh;?>"  placeholder="Seleccione hora" type="text" value="<?php echo $ini;?>">
                        <div class="input-group-prepend">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                    <select title="Hora Inicio"  data-container="body" name="selinicio<?php echo $joh;?>" id="selinicio<?php echo $joh;?>" class="form-control form-control-sm selectpicker seljh" onchange="CRUDJORNADA('ACTUALIZARHORA','<?php echo $joh;?>','<?php echo $idfec;?>')" required data-parsley-errors-container="#msn-errorini<?php echo $joh;?>">
                    <option value=""></option>
                    <?php
                    $selectHora = new General();
                    $selectHora -> cargarHoras($inilis,$ini,24,1);
                    ?>                        
                    </select>
                    <span id="msn-errorini<?php echo $joh;?>"></span>
                </td>
            
                <td class="p-1" style="width:10%">
                    <select title="Hora Fin"  data-container="body" name="selfin<?php echo $joh;?>" id="selfin<?php echo $joh;?>" class="form-control form-control-sm selectpicker seljh" onchange="CRUDJORNADA('ACTUALIZARHORA','<?php echo $joh;?>','<?php echo $idfec;?>')" required data-parsley-errors-container="#msn-errorfin<?php echo $joh;?>">
                    <option value=""></option>
                    <?php
                    $selectHora = new General();
                    $selectHora -> cargarHoras($ini,$fin,24,1);
                    ?>    
                    </select>
                    <span id="msn-errorfin<?php echo $joh;?>"></span>
                </td>
                <td class="p-1 text-center" style="width:10%"><span id="spantot<?php echo $joh;?>"><?PHP echo $tot;?></span></td>
                <td class="p-1" style="width:15%">
                  
                    <select title="Seleccionar Novedad" id="seltiponovedad<?php echo $joh;?>" name="seltiponovedad<?php echo $joh;?>" class="form-control form-control-sm selectpicker seljh" onchange="CRUDJORNADA('ACTUALIZARHORA','<?php echo $joh;?>','<?php echo $idfec;?>')" required data-parsley-errors-container="#msn-errortin<?php echo $joh;?>"> 
                       
                        <?php
                        $selectTin = new General();
                        $selectTin -> cargarTipoNovedad($tin,1);
                        ?>
                    </select>
                    <span id="msn-errortin<?php echo $joh;?>"></span>
                </td>
                <td class="p-1" style="width:100px">
                    <textarea name="txtobservacion<?php echo $joh;?>" id="txtobservacion<?php echo $joh;?>" class="form-control form-control-sm" rows="1" onchange="CRUDJORNADA('ACTUALIZARHORA','<?php echo $joh;?>','<?php echo $idfec;?>')"><?php echo $obs;?></textarea>
                </td>              
                <td class="p-1" style="width:20px"><a class="btn btn-sm rounded-circle bg-red text-white" onclick="CRUDJORNADA('ELIMINARHORA','<?php echo $joh;?>','<?php echo $idfec;?>')"><i class="fas fa-trash"></i></a></td>
            </tr>
            
            <?php
        }
        ?>
        </table>
        <script>
            $('.seljh').on('change',function(){
                $(this).parsley().validate();
            })
        </script>
        <?php
    }
    echo "<script>INICIALIZARCONTENIDO();</script>";
}
else if($opcion=="ACTUALIZARDIA")
{
    $id = $_POST['id'];
    $nov = $_POST['nov'];
    $upd = mysqli_query($conectar, "UPDATE tbl_jornada_dias SET tin_clave_int = '".$nov."', jod_usu_actualiz = '".$usuario."', jod_fec_actualiz = '".$fecha."' WHERE jod_clave_int = '".$id."'");
    if($upd>0)
    {
        $res = "ok";
        $msn = "";
        $reg = new General();
        $datemp = $reg->fechaDia($id);
        $emp = $datemp['emp'];
        $fec = $datemp['fec'];
        $msn.="Fec:".$fec;
        $msn.= $reg->calcularDiaLiquidar($emp,$fec,$usuario, $fecha);

        //SELECCIONAR LAS HORAS DE ESE DIA PARA RECALCULAR
        $conhoras = mysqli_query($conectar,"SELECT obr_clave_int obr FROM tbl_jornada_horas WHERE jod_clave_int = '".$id."'");
        $numhoras = mysqli_num_rows($conhoras);
        if($numhoras>0)
        {   
            for($h=0;$h<$numhoras; $h++)
            {
                $dath = mysqli_fetch_array($conhoras);
                $obr = $dath['obr'];
                $msn.= "<br>".$reg->calcularDiaObra($emp,$fec,$obr,$usuario,$fecha); 
            }
        }
    }
    else
    {
        $res = "error";
        $msn = "Surgió un error al actualizar dia con la novedad. Verificar";
    }
    
    $datos[] = array("res"=>$res, "msn"=>$msn);
    echo json_encode($datos);
}
else if($opcion=="AGREGARHORA")
{
    $id = $_POST['id'];
    //VERIFICAR QUE EL HORARIO ANTERIOR NO ES EL VALOR MAXIMO
    $ins = mysqli_query($conectar, "INSERT INTO tbl_jornada_horas(jod_clave_int,joh_usu_actualiz,joh_fec_actualiz) VALUES('".$id."','".$usuario."','".$fecha."')");
    if($ins>0)
    {
        $res = "ok";
        $msn = "";
    }
    else
    {
        $res = "error";
        $msn = "Surgio un error al agregar hora";
    }
    $datos[] = array("res"=>$res,"msn"=>$msn);
    echo json_encode($datos);
}
else if($opcion=="ELIMINARHORA")
{
    $id = $_POST['id'];
    $idfec = $_POST['idfec'];
    $reg = new General();
    $datemp = $reg->fechaDia($idfec);
    $emp = $datemp['emp'];
    $fec = $datemp['fec'];

    $datob = $reg->datosHora($id);
    $obrant = $datob['obr'];
    //VERIFICAR QUE EL HORARIO ANTERIOR NO ES EL VALOR MAXIMO
    $ins = mysqli_query($conectar, "DELETE FROM  tbl_jornada_horas WHERE joh_clave_int = '".$id."'");
    if($ins>0)
    {
        $res = "ok";
        $msn = "Hora eliminada correctamente";
        $msn.= "<br>".$reg->calcularDiaLiquidar($emp,$fec,$usuario, $fecha);       
                  
        if($obrant>0)
        {           
            $msn.= "<br>".$reg->calcularDiaObra($emp,$fec,$obrant,$usuario, $fecha); 
        }        
    }
    else
    {
        $res = "error";
        $msn = "Surgio un error al agregar hora";
    }
    $datos[] = array("res"=>$res,"msn"=>$msn);
    echo json_encode($datos);
}
else if($opcion=="ACTUALIZARHORA")
{
    $joh = $_POST['id'];
    $obr = $_POST['obr'];
    $ini = $_POST['ini'];
    $fin = $_POST['fin'];
    $tin = $_POST['tin'];
    $obs = $_POST['obs'];
    $idfec = $_POST['idfec'];   
    
    $reg = new General();
    $datemp = $reg->fechaDia($idfec);
    $emp = $datemp['emp'];
    $fec = $datemp['fec'];

    $datob = $reg->datosHora($joh);
    $obrant = $datob['obr'];

    $upd = mysqli_query($conectar,"UPDATE tbl_jornada_horas SET obr_clave_int = '".$obr."',joh_inicio = '".$ini."', joh_fin = '".$fin."', tin_clave_int = '".$tin."',joh_observacion = '".$obs."', joh_usu_actualiz = '".$usuario."', joh_fec_actualiz = '".$fecha."' WHERE joh_clave_int = '".$joh."'");
    $tot = 0;
    if($upd>0)
    {
        $res = "ok";
        $msn = "";
        $tot = hourdiff($ini,$fin, true);
        
        $msn.= $reg->calcularDiaLiquidar($emp,$fec,$usuario, $fecha);
        
        if($obr>0)
        {
            $msn.= $reg->calcularDiaObra($emp,$fec,$obr,$usuario, $fecha);
            if($obr!=$obrant and $obrant>0)
            {           
                $msn.= $reg->calcularDiaObra($emp,$fec,$obrant,$usuario, $fecha); 
            }
        }
    }
    else
    {
        $res = "error";
        $msn = "Surgio un error al actualizar hora. Verificar";
    }
    $datos[] = array("res"=>$res, "msn"=>$msn, "tot"=>$tot);
    echo json_encode($datos);
}
else if($opcion=="CALCULOHORAS")
{
    $emp = $_POST['emp'];
    $sem = $_POST['sem'];
    $idfec = $_POST['idfec'];

    $contot = mysqli_query($conectar,"SELECT SUM(calculoHoras3(jd.jod_fecha,joh_inicio,joh_fin)) as totalhoras FROM tbl_jornada_dias jd JOIN tbl_jornada_horas jh ON jh.jod_clave_int = jd.jod_clave_int JOIN tbl_tipo_novedad tn on tn.tin_clave_int = jh.tin_clave_int WHERE tn.tin_tipo = 1 and jd.usu_clave_int = '".$emp."' and jd.jod_semana = '".$sem."'");
    $dattot = mysqli_fetch_array($contot);
    $totalhoras = $dattot['totalhoras'];
    $totalhoras = ($totalhoras=="" || $totalhoras==NULL)?0:$totalhoras;

    //RECALCULO DE LAS HORAS PARA DISTRIBUIRR EN CADA TIPO DE HORAS SI SE PASA DE LAS HORAS ORDINARIAS
    $dia = "";
    $fes = 0;
    $totalhorasdia = 0;
    if($idfec>0)
    {
        $contotd = mysqli_query($conectar,"SELECT SUM(calculoHoras3(jd.jod_fecha,joh_inicio,joh_fin)) as totalhoras,jod_fecha dia,diaHabil(jod_fecha) as fes,jd.jod_clave_int id FROM tbl_jornada_dias jd JOIN tbl_jornada_horas jh ON jh.jod_clave_int = jd.jod_clave_int JOIN tbl_tipo_novedad tn on tn.tin_clave_int = jh.tin_clave_int WHERE tn.tin_tipo = 1 and jd.jod_clave_int = '".$idfec."' GROUP BY jd.jod_clave_int");
        $dattotd = mysqli_fetch_array($contotd);
        $totalhorasdia = $dattotd['totalhoras'];
        $dia = $dattotd['dia'];
        $fes = $dattotd['fes'];
        $totalhorasdia = ($totalhorasdia=="" || $totalhorasdia==NULL)?0:$totalhorasdia; //TOTAL HORAS OR
        $upddia = mysqli_query($conectar, "UPDATE tbl_jornada_dias SET jod_total = '".$totalhorasdia."' WHERE jod_clave_int = '".$idfec."'");
    }


    $datos[] = array("totalhoras"=>number_format($totalhoras,2,".",""),"dia"=>$dia, "fes"=>$fes, "totalhorasdia"=>$totalhorasdia);
    echo json_encode($datos); 
}   
else if($opcion=="ACTUALIZARHORAOLD")
{
    $id = $_POST['id'];
    $emp = $_POST['emp'];
    $ano = $_POST['ano'];
    $sem = $_POST['sem'];
    $fec = $_POST['fec'];
    $inicio = $_POST['inicio'];
    $fin = $_POST['fin'];
    $iniciot = $_POST['iniciot'];
    $fint = $_POST['fint'];
    $iniciot = $_POST['iniciot'];
    $fint = $_POST['fint'];
    $obs = $_POST['obs'];
    if($id>0)
    {
        //ACTUALIZAR DE HORARIO
        $sql = "UPDATE tbl_jornada_detalle SET jod_inicio = '".$inicio."', jod_fin = '".$fin."',jod_inicio_tarde = '".$iniciot."', jod_fin_tarde = '".$fint."',jod_inicio_noche = '".$inicion."', jod_fin_noche = '".$finn."', jod_observacion = '".$obs."', jod_fecha = '".$fec."', jod_empleado = '".$emp."',jod_ano = '".$ano."',jod_semana = '".$sem."', jod_fec_actualiz = '".$fecha."',jod_usu_actualiz = '".$usuario."' WHERE jod_clave_int = '".$id."'";
    }
    else
    {
        //INSERCION DE HORARIO
        $condet = mysqli_query($conectar, "SELECT jod_clave_int, jod_inicio, jod_fin,jod_inicio_tarde, jod_fin_tarde,jod_inicio_noche, jod_fin_noche,jod_observacion FROM tbl_jornada_detalle where jod_empleado = '".$emp."' and jod_ano = '".$ano."' and jod_semana = '".$sem."' and jod_fecha = '".$fec."' LIMIT 1");
        $numdet = mysqli_num_rows($condet);
        if($numdet>0)
        {
            $datdet = mysqli_fetch_array($condet);
            $id = $datdet['jod_clave_int'];
            //ACTUALIZAR REGISTRO EXISTENTE
            $sql = "UPDATE tbl_jornada_detalle SET jod_inicio = '".$inicio."', jod_fin = '".$fin."',jod_inicio_tarde = '".$iniciot."', jod_fin_tarde = '".$fint."',jod_inicio_noche = '".$inicion."', jod_fin_noche = '".$finn."', jod_observacion = '".$obs."', jod_fecha = '".$fec."', jod_empleado = '".$emp."',jod_ano = '".$ano."',jod_semana = '".$sem."', jod_fec_actualiz = '".$fecha."',jod_usu_actualiz = '".$usuario."' WHERE jod_clave_int = '".$id."'";
        }
        else
        {
            //INSERCION DE HORARIO
            $sql = "INSERT INTO tbl_jornada_detalle (jod_inicio, jod_fin,jod_inicio_tarde, jod_fin_tarde,jod_inicio_noche, jod_fin_noche,jod_fecha, jod_observacion,jod_empleado,jod_ano,jod_semana, jod_fec_actualiz,jod_usu_actualiz) VALUES('".$inicio."','".$fin."','".$iniciot."','".$fint."','".$inicion."','".$finn."', '".$fec."','".$obs."','".$emp."','".$ano."','".$sem."','".$fecha."','".$usuario."')";
        }
    }
    $con = mysqli_query($conectar, $sql);
    if($con>0)
    {
        $res = "ok";
        $msn = "Información horaria actualizada";
    }
    else
    {
        $res = "error";
        $msn = "Surgió un error al actualizar información";
    }
    $datos[] = array("res"=>$res, "msn"=>$msn);
    echo json_encode($datos);
}
else if($opcion=="GUARDARSEMANA")
{
    $emp = $_POST['emp'];
    $sem = $_POST['sem'];
    $ano = $_POST['ano'];
    $tot = $_POST['tot'];
    $not = $_POST['not'];
    $veri = mysqli_query($conectar, "SELECT jor_clave_int, jor_estado FROM tbl_jornada WHERE usu_clave_int = '".$emp."' and jor_semana = '".$sem."'");
    $num = mysqli_num_rows($veri);
    if($num>0)
    {
        $dat = mysqli_fetch_array($veri);
        $idjor = $dat['jor_clave_int'];
        $upd = mysqli_query($conectar, "UPDATE tbl_jornada SET jor_total = '".$tot."',jor_usu_actualiz = '".$usuario."', jor_fec_actualiz = '".$fecha."',jor_nota = '".$not."' WHERE jor_clave_int = '".$idjor."'");
        if($upd>0)
        {
            $res = "ok";
            $msn = "Información actualizada correctamente";
        }
        else
        {
            $res = "error";
            $msn = "Surgió un error al actualizar información";
        }
    }
    else{
        $ins = mysqli_query($conectar, "INSERT INTO tbl_jornada(jor_fec_creacion,jor_creacion,usu_clave_int,jor_semana,jor_ano,jor_nota,jor_usu_actualiz, jor_fec_actualiz,jor_total) VALUES('".$fecha."','".$idUsuario."','".$emp."','".$sem."','".$ano."','".$not."','".$usuario."','".$fecha."','".$tot."')");
        if($ins>0)
        {
            $idjor = mysqli_insert_id($conectar);
            $upddia = mysqli_query($conectar, "UPDATE tbl_jornada_dias SET jor_clave_int = '".$idjor."' WHERE usu_clave_int = '".$emp."' and jod_semana = '".$sem."'");
            $res = "ok";
            $msn = "Información guardada correctamente";
        }
        else
        {
            $res = "error";
            $msn = "Surgió un error al guardar información";
        }
    }

    //SELECCIONAR LOS DIA SE ESA SEMANA DE ESE EMPLEADO Y HACER LAS ACTUALIZACION EN LIQUIDAR DIAS Y EN LIQUIDAR OBRA



    $datos[] = array("res"=>$res, "msn"=>$msn);
    echo json_encode($datos);
}
else if($opcion=="FILTROS")
{
    ?>
    <div class="col-md-3">
        <label for="busempleado">Empleado</label>
        <select id="busempleado" name="busempleado" class="form-control form-control-sm selectpicker" onchange="CRUDJORNADA('LISTAPLANILLAS', '')">
        <option value=""></option>
        <?php
            $selectEmp = new General();
            $selectEmp -> cargarEmpleados();
        ?>
        </select>
        <span id="msn-error1"></span>
    </div>
    <div class="col-md-3">
        <label for="busobra">Obra</label>
        <select id="busobra" name="busobra" class="form-control form-control-sm selectpicker" onchange="CRUDJORNADA('LISTAPLANILLAS', '')">
            <option value=""></option>
            <?php
            $selectObr = new General();
            $selectObr -> cargarObras("",1);
            ?>
        </select>
    </div>
    <div class="col-md-3">
        <label for="busano">Año</label>
        <select name="busano" id="busano" class="form-control form-control-sm selectpicker" onchange="CRUDGENERAL('CARGARSEMANAS', 'bussemana', 'busmes', 'busano', 'PLANILLA');">
            <option value=""></option>
            <?php
            $selectAno = new General();
            $selectAno -> selSelect(2020,date('Y'));
            ?>

        </select>
    </div>
    
    <div class="col-md-3">
        <label for="bussemana">Semana</label>
        <select name="bussemana" id="bussemana" class="form-control form-control-sm selectpicker" onchange="CRUDJORNADA('LISTAPLANILLAS', '')">
        <option value=""></option>
        <?php 
        //$sf = $semanaactual;
        //$sem = new General();
        //$sem->cargarSemanas(date('Y'),'',2,$sf);    
        ?>
        </select>
        <span id="msn-error2"></span>
    </div>
    <?php
    echo "<script>CRUDGENERAL('CARGARSEMANAS', 'bussemana', 'busmes', 'busano', 'PLANILLA');</script>";
    //echo "<script>CRUDJORNADA('LISTAPLANILLAS');</script>";
    echo "<script>INICIALIZARCONTENIDO();</script>";

}
else if($opcion=="LISTAPLANILLAS")
{
    ?>
    <div class="col-md-12 table-responsive">
        <table class="table table-bordered table-striped" id="tbJornada">
            <thead>
                <tr>
                    <th class="dt-head-center bg-terracota" style="width:20px"></th>
                    <th class="dt-head-center bg-terracota" style="width:20px"></th>
                    <th class="dt-head-center bg-terracota">Empleado</th>
                    <th class="dt-head-center bg-terracota">Año</th>
                    <th class="dt-head-center bg-terracota">Semana</th>
                    <th class="dt-head-center bg-terracota">Inicio</th>
                    <th class="dt-head-center bg-terracota">Fin</th>
                    <th class="dt-head-center bg-terracota">Total Horas</th>
                    <th class="dt-head-center bg-terracota">Estado</th>
                    <th class="dt-head-center bg-terracota">Creada Por</th>
                    <th class="dt-head-center bg-terracota">Fecha Registro</th>


                </tr>
            </thead>
            <tfoot>
                <tr>
                    <th class="dt-head-center"></th>
                    <th class="dt-head-center"></th>
                    <th class="dt-head-center"></th>
                    <th class="dt-head-center"></th>
                    <th class="dt-head-center"></th>
                    <th class="dt-head-center"></th>
                    <th class="dt-head-center"></th>
                    <th class="dt-head-center"></th>
                    <th class="dt-head-center"></th>
                    <th class="dt-head-center"></th>
                    <th class="dt-head-center"></th>
                </tr>
            </tfoot>
        </table>
    </div>
    <script src="jsdatatable/jornadas/jslistajornada.js?<?php echo time();?>"></script>
    <?php
}

else if($opcion=="REGISTROMASIVO")
{
    setlocale(LC_ALL,"es_ES.utf8","es_ES","esp");
    $emp = $_POST['emp'];
    $sem = $_POST['sem'];
    $condias = mysqli_query($conectar, "SELECT jod_clave_int, jod_fecha FROM tbl_jornada_dias WHERE usu_clave_int = '".$emp."' and jod_semana = '".$sem."' and jod_fecha<=CURDATE()");
    $numdias = mysqli_num_rows($condias);
    $gen = new General();
    ?>
    <form id="frmmasivo" name="frmmasivo" data-parsley-validate="">
    <div class="row">
        <div class="col-md-12">
            <label for="seldias">Fechas:</label>
            <select class="form-control form-control-sm selectpicker" multiple id="seldias" name="seldias" required data-parsley-error-message="Seleccionar la fechas" data-parsley-errors-container="#errorm1" data-actions-box="true" data-size="7">
            <?php 
            while($datd = mysqli_fetch_array($condias))
            {
                $iddia = $datd['jod_clave_int'];
                $fec = $datd['jod_fecha'];
                $dia = strftime("%A", strtotime($fec));
                ?>
                <option  data-subtext="<?php echo $fec;?>" value="<?php echo $iddia;?>" selected><?php echo $dia;?></option>
                <?php
            }   
            ?>
            </select>
            <span id="errorm1"></span>
        </div>
        <div class="col-md-4">
            <label for="selobramasivo">Obra:</label>
            <select class="form-control form-control-sm selectpicker"  id="selobramasivo" name="selobramasivo" required data-parsley-error-message="Seleccionar la obra" data-parsley-errors-container="#errorm2">
                <?php
                $gen -> cargarObras("",1);
                ?>
            </select>
            <span id="errorm2"></span>
        </div>
        <div class="col-md-4">
            <label>Novedad:</label>
            <select class="form-control form-control-sm selectpicker"  id="selnovedad" name="selnovedad" required data-parsley-error-message="Seleccionar novedad" data-parsley-errors-container="#errorm3">
                <?php                 
                $gen -> cargarTipoNovedad(1,1,3);
                ?>
            </select>
            <span id="errorm3"></span>
        </div>
        <div class="col-md-2">
            <label>Hora inicio:</label>
            <select title="Seleccionar hora inicial" class="form-control form-control-sm selectpicker" id="selinicio" name="selinicio">
                <?php           
                $gen -> cargarHoras("00:00","",24,1);
                ?>
            </select>
        </div>
        <div class="col-md-2">
            <label>Hora Final:</label>
            <select title="Selecciona hora final" class="form-control form-control-sm selectpicker" id="selfin" name="selfin">
                <?php           
                $gen -> cargarHoras("00:00","",24,1);
                ?>
            </select>
        </div>
        <div class="col-md-12">
            <label>Observación:</label>
            <textarea name="txtobservacion" id="txtobservacion" class="form-control form-control-sm"  rows="1"></textarea>
        </div>
        <div class="col-md-1 hide"><br>
            <button class="btn btn-success btn-sm" type="button" onclick="CRUDJORNADA('GUARDARMASIVO')">Agregar</button>
        </div>
    </div>
    </form>
    <?php
    echo "<script>INICIALIZARCONTENIDO();</script>";
}
else if($opcion=="GUARDARMASIVO")
{
    $emp = $_POST['emp'];
    $sem = $_POST['sem'];
    $obr = $_POST['obr'];
    $dias = $_POST['dias'];
    $dias = implode(',', (array)$dias);
    $hi = $_POST['hi'];
    $hf = $_POST['hf'];
    $nov = $_POST['nov'];
    $obs = $_POST['obs'];


    if($hi=="" || $hf=="")
    {
        
        //LA NOVEDA SELECCIONADA SE ASOCIA A LOS DIAS Y NO SE REGISTRA HORAS
        $update = mysqli_query($conectar, "UPDATE tbl_jornada_dias SET tin_clave_int = '".$nov."', jod_usu_actualiz = '".$usuario."', jod_fec_actualiz = '".$fecha."' where jod_clave_int in(".$dias.")");
        if($update>0)
        {
            $res = "ok";
            $msn = "Dias seleccionados modificados con la novedad seleccionada";
        }
        else
        {
            $res = "error";
            $msn = "Surgió un error al modificar los dias seleccionados. Verificar";
        }
    }
    else
    {
        $insert = mysqli_query($conectar, "INSERT INTO tbl_jornada_horas (jod_clave_int,obr_clave_int,joh_inicio,joh_fin,tin_clave_int,joh_observacion,joh_usu_actualiz,joh_fec_actualiz) SELECT jod_clave_int, '".$obr."' obr, '".$hi."','".$hf."','".$nov."' nov,'".$obs."' obs, '".$usuario."','".$fecha."' FROM tbl_jornada_dias WHERE jod_clave_int in(".$dias.")");
        if($insert>0)
        {
            $res = "ok";
            $msn = "Jornada registrada correctamente a los dias seleccionados";
        }
        else
        {
            $res = "error";
            $msn = "Surgió un error al guardar. Verificar";
        }
    }
    if($res=="ok")
    {
        $reg = new General();
        $diasa = $_POST['dias'];
        for($d=0;$d<count($diasa);$d++)
        {
            $id = $diasa[$d];
            $datemp = $reg->fechaDia($id);
            $fec = $datemp['fec'];

            $contotd = mysqli_query($conectar,"SELECT SUM(calculoHoras3(jd.jod_fecha,joh_inicio,joh_fin)) as totalhoras FROM tbl_jornada_dias jd JOIN tbl_jornada_horas jh ON jh.jod_clave_int = jd.jod_clave_int JOIN tbl_tipo_novedad tn on tn.tin_clave_int = jh.tin_clave_int WHERE tn.tin_tipo = 1 and jd.jod_clave_int = '".$id."' GROUP BY jd.jod_clave_int");
            $dattotd = mysqli_fetch_array($contotd);
            $totalhorasdia = $dattotd['totalhoras'];           
            $totalhorasdia = ($totalhorasdia=="" || $totalhorasdia==NULL)?0:$totalhorasdia; //TOTAL HORAS OR
            $upddia = mysqli_query($conectar, "UPDATE tbl_jornada_dias SET jod_total = '".$totalhorasdia."' WHERE jod_clave_int = '".$id."'");

           
            $msn.="Fec:".$fec;
            $msn.= $reg->calcularDiaLiquidar($emp,$fec,$usuario, $fecha);
            $msn.= "<br>".$reg->calcularDiaObra($emp,$fec,$obr,$usuario,$fecha); 
        }
    }
    $datos[] = array("res"=>$res, "msn"=>$msn, "SELECT jod_clave_int, '".$obr."', '".$hi."','".$hf."','".$nov."','".$obs."', '".$usuario."','".$fecha."' FROM tbl_jornada_dias WHERE jod_clave_int in(".$dias.")");
    echo json_encode($datos);
}
// SELECT MIN(jor_inicio), MAX(jor_fin) from tin_tipo = 1 and time_format(jor_ini,'%p')='AM' //HORAS AM  
// SELECT MIN(jor_inicio), MAX(jor_fin) from tin_tipo = 1 and time_format(jor_ini,'%p')='PM' //HORAS AM
// SELECT MIN(jor_inicio), MAX(jor_fin) from tin_tipo = 2' //HORAS DESCANSO