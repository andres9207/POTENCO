<?php
include '../../data/db.config.php';
include '../../data/conexion.php';
session_start();
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

$reg = new General();
$datr = $reg->fnReglas();
$hirn = $datr['hirn'];
$hfrn = $datr['hfrn'];
$han  = $datr['han'];
$hao  = $datr['hao'];
$vhan = $datr['vhan'];
$vhao = $datr['vhao']; 
$hmes = $datr['hmes'];
$vhanpm = $datr['vhanpm'];
$hanpm = $datr['hanpm'];
$limex = $datr['limex']; //EXTRAS LIMIT POR DIA
$limexsemana = $datr['limexsemana']; // EXTRAS LIMITE SEMANA
$hsemana = $datr['hsemana'];

$p121 = isset($permisosUsuario[121]) ?? 0;
$p122 = isset($permisosUsuario[122]) ?? 0;

$opcion = $_POST['opcion'];

if($opcion=="NUEVO" || $opcion=="EDITAR" || $opcion=="APPROVED")
{
    $id = $_POST['id'];
    $id = ($id!="")?decrypt($id,"p4v4sAp"):"";

    $sql = "SELECT 
    l.usu_clave_int AS usu,
    l.obr_clave_int AS obr,
    l.liq_ano AS ano,
    l.liq_inicio AS ini,
    l.liq_fin AS fin,
    l.liq_tipo AS tip,
    l.liq_estado AS est,
    l.liq_hr_mes AS hor,
    l.liq_salario AS sal,
    l.liq_total AS tot,
    l.liq_notas AS nota
    FROM tbl_liquidar l
    WHERE l.liq_clave_int = :id";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();

    $datliq = $stmt->fetch(PDO::FETCH_ASSOC);

    $emp   = $datliq['usu'];
    $obr   = $datliq['obr'];
    $ano   = $datliq['ano']; $ano = ($ano==""|| $ano==null)?date('Y'):$ano;
    $ini   = $datliq['ini'];
    $fin   = $datliq['fin'];
    $tip   = $datliq['tip'];
    $est   = $datliq['est'];
    $hor   = $datliq['hor'];
    $sal   = $datliq['sal'];
    $tot   = $datliq['tot'];
    $nota  = $datliq['nota'];
    $disa = ($id>0)?"disabled":"";
    $btng = ($id>0)?"Actualizar":"Generar";

    $disinp = "";
    $gen = new General();
    ?>
    <div class="col-md-12">
        <form id="frmliquidar" name="frmliquidar" data-parsley-validate="">
            <div class="row justify-content-center ">
                <div class="col-6 col-sm-6 col-md-2">
                    <label for="txtdesde">Desde:</label>
                    <input <?php echo $disa;?> type="date" name="txtdesde" id="txtdesde" class="form-control form-control-sm" onchange="CRUDLIQUIDAR('CARGAREMPLEADO', '') " required data-parsley-error-message="Seleccionar fecha desde" value="<?php echo $ini;?>">
                </div>
                <div class="col-6 col-sm-6 col-md-2">
                    <label for="txthasta">Hasta:</label>
                    <input <?php echo $disa;?> type="date" name="txthasta" id="txthasta" class="form-control form-control-sm" onchange="CRUDLIQUIDAR('CARGAREMPLEADO', '')" required data-parsley-error-message="Seleccionar fecha hasta" value="<?php echo $fin;?>">
                </div>
                <div class="col-md-2">
                    <label for="seltipo">Tipo:</label>
                    <select  name="seltipo" id="seltipo" class="form-control form-control-sm selectpicker" onchange="CRUDLIQUIDAR('CARGAREMPLEADO', '')" <?php echo $disa;?>>

                        <?php if($p121>0){ ?> <option value="1" <?php if($tip==1){ echo "selected"; }?>>Por empleado</option><?php } ?>
                        <?php if($p122>0){ ?> <option value="2" <?php if($tip==2){ echo "selected"; }?>>Por Obra</option><?php } ?>
                    </select>
                </div>
                <div class="col-6 col-sm-6 col-md-1 hide">
                    <label for="selano">Año</label>
                    <select <?php echo $disinp;?> name="selano" id="selano" class="form-control form-control-sm selectpicker" onchange="CRUDGENERAL('CARGARMES','','selmes','selano')" data-live-search="true" <?php echo $disa;?>>
                        <option value=""></option>
                        <?php
                        $gen -> cargarAnos(2020,date('Y'),$ano,"DESC",1);
                        ?>

                    </select>
                </div>
                <div class="col-6 col-sm-6 col-md-2 hide">
                    <label for="selmes">Mes</label>
                    <select <?php echo $disinp;?> name="selmes" id="selmes" class="form-control form-control-sm selectpicker" data-live-search="true"  onchange="CRUDLIQUIDAR('CARGARLIQUIDAR', '')" <?php echo $disa;?>>
                    <?php
                        $gen -> cargarmes(date('Y'),1,12,"","DESC",1);
                        ?>
                    </select>
                </div>
                <div class="col-md-3 <?php if($tip!="2"){ echo "hide"; }?>" id="divobra">
                    <label for="selobra">Obra</label>
                    <select id="selobra" name="selobra"  class="form-control form-control-sm selectpicker" onchange="CRUDLIQUIDAR('CARGAREMPLEADO', '')" data-parsley-error-message="Seleccionar obra" data-parsley-errors-container="#msn-error1" <?php echo $disa;?>> 
                        <option value=""></option>
                        <?php
                        $gen -> cargarObras($obr,1);
                        ?>
                    </select>
                    <span id="msn-error2"></span>
                </div>
                <div class="col-md-3" id="divempleado">
                    <label for="selempleadoliquidar">Empleado</label>
                    <select id="selempleadoliquidar" name="selempleadoliquidar" class="form-control form-control-sm selectpicker" onchange="CRUDLIQUIDAR('CARGARLIQUIDAR', '')" required data-parsley-error-message="Seleccionar el empleado" data-parsley-errors-container="#msn-error1" <?php echo $disa;?>  data-actions-box="true">
                        <option value=""></option>
                        <?php
                        $gen -> cargarEmpleados($emp);
                        ?>
                    </select>
                    <span id="msn-error1"></span>
                </div> 
                         
            </div>
            <div class="row justify-content-center mt-2">
                <div class="col-md-12 text-center">
                    <button type="button" class="btn btn-sm btn-success" onclick="CRUDLIQUIDAR('GENERARPROFORMA','<?php echo $id;?>', '<?php echo $emp;?>')" ><?php echo $btng; ?> Proforma</button>
                    <?PHP if($est==1 and $p55>0 and $opcion=="APPROVED") { ?>
                    <button type="button" class="btn btn-sm btn-primary" onclick="CRUDLIQUIDAR('APROBARPROFORMA','<?php echo $id;?>', '<?php echo $emp;?>')" >Aprobar Proforma <i class="fas fa-check-double"></i></button>
                    <button type="button" class="btn btn-sm btn-danger" onclick="CRUDLIQUIDAR('RECHAZARPROFORMA','<?php echo $id;?>', '<?php echo $emp;?>')" >Rechazar Proforma <i class="fas fa-ban"></i></button>
                    <?php }  ?>                
                    <button type="button" class="btn btn-sm btn-default" onclick="CRUDLIQUIDAR('PREVIAPROFORMA','<?php echo encrypt($id,'p4v4sAp');?>', '<?php echo $emp;?>')" >Previa Liquidación <i class="fas fa-file-pdf text-red"></i></button>
                </div>
            </div>
        </form>
    </div>
    
    <div class="col-md-12 table-responsive" id="divplanilla"></div>
    <script>
        $('#selempleadoliquidar').on('change',function(){
            $(this).parsley().validate();
        })
        $('#seltipo').on('change',function(){
            var ti = $(this).val();
            if(ti==1)
            {
                $('#divobra').addClass('hide');
                //$('#divempleado').removeClass('hide');
            }
            else
            {
                $('#divobra').removeClass('hide');
                //$('#divempleado').addClass('hide');
                //llamar funcion para cargar los empleados segun la obra
            }
        })
    </script>
    <?php
    echo "<script>INICIALIZARCONTENIDO();</script>";
    echo "<script>CRUDLIQUIDAR('CARGARLIQUIDAR', '');</script>";
}
else if($opcion=="CARGARLIQUIDAR")
{
    // iniciar buffer para capturar la salida HTML y convertirla a Excel
    if(!ob_get_level()) ob_start();
    $ano = $_POST['ano'];
    $mes = $_POST['mes']; $mes = ($mes<10)?"0".$mes:$mes;
    $emp = $_POST['emp'];
    $obr = $_POST['obr'];
    //$obr = implode(', ', (array)$obr); if($obr==""){$obr1="''";}else {$obr1=$obr;}
    $ini = $_POST['ini'];
    $fin = $_POST['fin'];
    $tip = $_POST['tip'];
    setlocale(LC_ALL,"es_ES.utf8","es_ES","esp");
    if($ini=="" || $fin=="")
    {
        echo "<div class='alert alert-info mt-1'>Seleccionar rango de fechas a liquidar</div>";
    }
    else if($emp<=0)
    {
        echo "<div class='alert alert-info mt-1'>Seleccionar empleado a generar liquidación</div>";
    }
    else if($obr<=0 and $tip==2)
    {
        echo "<div class='alert alert-info mt-1'>Seleccionar obra y empleado a generar liquidación</div>";
    }
    else
    if($tip==1)
    {
        $toh = new General();
        $sqldias =  "SELECT jd.jod_clave_int id, jd.jod_fecha fec, DAYOFWEEK(jd.jod_fecha) dia,jd.jod_semana sem,jd.jod_estado est,jd.jod_observacion obs,totalhorasDia(jd.jod_clave_int) tothoras,diaHabil(jod_fecha) fes,diaHabil(adddate(jod_fecha, interval 1 day)) fess, tn.tin_clave_int idnov, tn.tin_suma sumnov, tn.tin_nombre nomnov, j.jor_clave_int  jor  FROM tbl_jornada_dias jd LEFT OUTER JOIN tbl_jornada j ON j.jor_clave_int = jd.jor_clave_int LEFT OUTER JOIN tbl_tipo_novedad tn on tn.tin_clave_int = jd.tin_clave_int WHERE jd.jod_fecha between '".$ini."' and '".$fin."'  and jd.usu_clave_int = '".$emp."'  order by jod_fecha  asc"; // and j.jor_estado = 1
        // $numdias = mysqli_num_rows($condias);
        $numali = 0;
        $numbon = 0;


        $stmtDias = $conn->prepare($sqldias);
        $stmtDias->execute();
        $numdias = $stmtDias->rowCount();

        // echo $sqldias;

        ?>
        <table class="table table-bordered table-striped"  id="tbLiquidar<?php echo $emp;?>">
            <thead>
                <tr>
                    <th class="dt-head-center bg-terracota align-middle"  rowspan="2"></th>   
                    <th class="dt-head-center bg-terracota align-middle"  rowspan="2">Alimentación</th>                    
                    <th class="dt-head-center bg-terracota align-middle"  rowspan="2" style="width:100px">Bonificación</th>
                    <th class="dt-head-center bg-terracota align-middle"  rowspan="2" style="width:100px">Auxilio</th>
                    <th class="dt-head-center bg-terracota align-middle" style="width:100px">Fecha</th>
                    <th class="dt-head-center bg-terracota align-middle" colspan="3">Mañana</th>
                    <th class="dt-head-center bg-terracota align-middle">Total Horas</th>
                    <th class="dt-head-center bg-terracota align-middle" colspan="3">Tarde</th>
                    <th class="dt-head-center bg-terracota align-middle">Total Horas</th>
                    <th class="dt-head-center bg-terracota align-middle">Horas Dia</th>
                    <th rowspan="2" class="dt-head-center bg-terracota align-middle">Horario</th>
                    <th rowspan="2" class="dt-head-center bg-terracota align-middle">Compensado</th>
                    <th rowspan="2" class="dt-head-center bg-terracota align-middle">Pagado y compensado</th>
                    <th rowspan="2" class="dt-head-center bg-terracota align-middle">Permiso Remunerado</th>
                    <th rowspan="2" class="dt-head-center bg-terracota align-middle">Observaciones</th>
                    <th class="dt-head-center align-middle">HEDO</th>
                    <th class="dt-head-center align-middle">HDF</th>
                    <th class="dt-head-center align-middle">HEDF</th>
                    <th class="dt-head-center align-middle">HENO</th>
                    <th class="dt-head-center align-middle">HENF</th>
                    <th class="dt-head-center align-middle">RN</th>
                    <th class="dt-head-center align-middle">HNF</th>
                    <th class="dt-head-center align-middle">RD</th>
                    <th class="dt-head-center align-middle" rowspan="2">T.E</th>

                </tr>
                <tr>
                    <th class="dt-head-center align-middle">Año/Mes/Dia</th>
                    <th class="dt-head-center align-middle">Desde</th>
                    <th class="dt-head-center align-middle"></th>
                    <th class="dt-head-center align-middle">Hasta</th>
                    <th class="dt-head-center align-middle">#</th>
                    <th class="dt-head-center align-middle">Desde</th>
                    <th class="dt-head-center align-middle"></th>
                    <th class="dt-head-center align-middle">Hasta</th>
                    <th class="dt-head-center align-middle">#</th>
                    <th class="dt-head-center align-middle">#</th>
                    <th class="dt-head-center align-middle">002</th>
                    <th class="dt-head-center align-middle">014</th>
                    <th class="dt-head-center align-middle">004</th>
                    <th class="dt-head-center align-middle">003</th>
                    <th class="dt-head-center align-middle">005</th>
                    <th class="dt-head-center align-middle">006</th>
                    <th class="dt-head-center align-middle">008</th>
                    <th class="dt-head-center align-middle">013</th>
                    
                </tr>
            </thead>
            <tbody>
                <?php 
                   //TOTALES PARCIALES 
                $TOTALHEDO = 0;
                $TOTALHDF  = 0;
                $TOTALHEDF = 0;
                $TOTALHENO = 0;
                $TOTALHENF = 0;
                $TOTALRN   = 0;
                $TOTALHNF  = 0;
                $TOTALRD   = 0;
                $TOTALHE  = 0;

                //TOTALES GENERAL 
                $TOTALGHEDO = 0;
                $TOTALGHDF  = 0;
                $TOTALGHEDF = 0;
                $TOTALGHENO = 0;
                $TOTALGHENF = 0;
                $TOTALGRN   = 0;
                $TOTALGHNF  = 0;
                $TOTALGRD   = 0;
                $TOTALGHE = 0;

                //ACUMULADO POR SEMANA
                $acusem = 0;

                if ($numdias > 0) {
                    while ($datd = $stmtDias->fetch(PDO::FETCH_ASSOC)) {
                        $idfec = $datd['id'];
                        $dia  = $datd['dia'];
                        $sem  = $datd['sem'];
                        $fec  = $datd['fec'];
                        $tothoras = $datd['tothoras'];
                        $nmes = date("F", strtotime($fec));
                        $ndia = date("l", strtotime($fec));
                        $numd = date("d", strtotime($fec));
                        $anod = date("D", strtotime($fec));
                        $fecha = $ndia.", ". $numd." de ".$nmes." de ".$anod;
                        
                        $fes = $datd['fes'];
                        $fess = $datd['fess'];//FESTIVO SIGUIENTE
                        $bgfes = ($fes==1)?"bg-secondary":""; //dia festivo
                        $titfes = ($fes==1)?"Dia Festivo":"";
                        
                        //datos para calculo de compensado y permisos remunerados
                        $idnov = $datd['idnov'];
                        $sumnov = $datd['sumnov'];
                        $nomnov = $datd['nomnov'];                        
                        
                        $T5 = "";
                        //DIA INICIAL Y FINAL
                        $diainicial = $numd;
                        $diafinal = date("t",strtotime($fec));

                        $sqldia = "SELECT 
                            lid_clave_int, 
                            lid_fecha AS fec, 
                            lid_hi_man, 
                            lid_hf_man,
                            lid_hi_tar, 
                            lid_hf_tar, 
                            lid_horas, 
                            lid_horario,
                            lid_hedo, 
                            lid_hdf,
                            lid_hedf, 
                            lid_heno, 
                            lid_henf, 
                            lid_rn, 
                            lid_hnf, 
                            lid_rd, 
                            lid_observacion, 
                            lid_alimentacion, 
                            lid_val_alimentacion, 
                            lid_estado, 
                            lid_compensado, 
                            lid_remunerado,
                            lid_procede, 
                            lid_auxilio, 
                            lid_bonificacion, 
                            lid_labores, 
                            lid_val_bonificacion, 
                            lid_auxilio, 
                            lid_permisos 
                        FROM tbl_liquidar_dias 
                        WHERE lid_fecha = :fec AND usu_clave_int = :emp 
                        LIMIT 1";

                        $stmt = $conn->prepare($sqldia);
                        $stmt->bindParam(':fec', $fec);
                        $stmt->bindParam(':emp', $emp, PDO::PARAM_INT);
                        $stmt->execute();

                        $datdia = $stmt->fetch(PDO::FETCH_ASSOC);

                        $iddia = $datdia['lid_clave_int'];

                        $aini = $datdia['lid_hi_man'];
                        $afin = $datdia['lid_hf_man'];
                        $pini = $datdia['lid_hi_tar'];
                        $pfin = $datdia['lid_hf_tar'];
                        $horario = $datdia['lid_horario'];
                        
                        $HEDO = $datdia['lid_hedo']; // HORAS EXTRAS DIURNAS ORDINA
                        $HDF  = $datdia['lid_hdf'];
                        $HEDF = $datdia['lid_hedf'];
                        $HENO = $datdia['lid_heno'];
                        $HENF = $datdia['lid_henf'];
                        $RN   = $datdia['lid_rn'];
                        $HNF  = $datdia['lid_hnf'];
                        $RD   = $datdia['lid_rd'];
                        $obs  = $datdia['lid_observacion'];
                        $ARE  = $datdia['lid_permisos'];

                        $ali = $datdia['lid_alimentacion'];
                        if($ali==1){ $numali++;}
                        $icali = $ali>0 ? "<i class='fas fa-utensils'></i>":"";
                        $valalimentacion = $datdia['lid_val_alimentacion'];
                        $compensado = $datdia['lid_compensado'];
                        $remunerado = $datdia['lid_remunerado'];
                        $procede =  $datdia['lid_procede'];
                        $auxilio = $datdia['lid_auxilio'];
                        $bonificacion = $datdia['lid_bonificacion'];
                        if($bonificacion==1){ $numbon++;}
                        $labores = $datdia['lid_labores'];
                        $valbonificacion = $datdia['lid_val_bonificacion'];
                        $auxilio = $datdia['lid_auxilio'];
                        
                        //TOTAL HORAS
                        $totalam = $toh->fnHoras($fec,$aini,$afin);
                        $totalpm = $toh->fnHoras($fec,$pini,$pfin);
                        $tothoras = $totalam + $totalpm; 
                        
                        $thorario = $horario;
                        $horario = ($fes==1)?0:$horario;
                        
                        $aminit  = ($aini!="" and $totalam>0)? date("g:i a", strtotime($aini)): "";
                        // $aminit  = ($aini!="" and $totalam>0)? $aini: "";
                        $amfint  = ($afin!="" and $totalam>0)? date("g:i a", strtotime($afin)): "";
                        $pminit  = ($pini!="" and $totalpm>0)? date("g:i a", strtotime($pini)): "";
                        $pmfint  = ($pfin!="" and $totalpm>0)? date("g:i a", strtotime($pfin)): "";
                   
                        $TOTALHEDO+= $HEDO;
                        $TOTALHDF+= $HDF;
                        $TOTALHEDF+= $HEDF;
                        $TOTALHENO+= $HENO;
                        $TOTALHENF+= $HENF;
                        $TOTALRN+= $RN;
                        $TOTALHNF+= $HNF;
                        $TOTALRD+= $RD;
                        $TOTALARE+=$ARE;

                        $TOTALGHEDO+= $HEDO;
                        $TOTALGHDF+= $HDF;
                        $TOTALGHEDF+= $HEDF;
                        $TOTALGHENO+= $HENO;
                        $TOTALGHENF+= $HENF;
                        $TOTALGRN+= $RN;
                        $TOTALGHNF+= $HNF;
                        $TOTALGRD+= $RD;
                        $TOTALGARE+=$ARE;

                        $HEDO  = $HEDO!=null ? str_replace(",",".",$HEDO) : 0;
                        $HDF  = $HDF!=null ? str_replace(",",".",$HDF): 0;
                        $HEDF  = $HEDF!=null ? str_replace(",",".",$HEDF): 0;
                        $HENO  = $HENO!=null ? str_replace(",",".",$HENO): 0;
                        $HENF  = $HENF!=null ? str_replace(",",".",$HENF): 0;
                        $RN  = $RN !=null ? str_replace(",",".",$RN): 0;
                        $HNF  = $HNF!=null ? str_replace(",",".",$HNF): 0;
                        $RD = $RD!=null ?str_replace(",",".",$RD): 0;
                        $ARE = $ARE!=null ? str_replace(",",".",$ARE): 0;

                        $tothoras = $tothoras!=null ? str_replace(",",".",$tothoras) : 0;

                        //JORNADA EXTRA
                        $HE = $HEDO + $HDF + $HEDF + $HENO + $HENF + $RN + $HNF + $RD;
                        $TED = $HEDO + $HEDF + $HENO + $HENF;
                        $DE = $TED-$limex;
                        $ME = ($TED>$limex)?" title='Excedio el límite de horas Extras en ".$DE.". Limite de Horas extras dia: ".$limex."' data-toggle='tooltip' ":"";
                        $CE = ($TED>$limex)?"limex":""; //CLASE QUE INDICA QUE EXCEDE EL MAXIO DE HORAS EXTRAS DISPONIBLE

                        $TOTALHE+=$HE;
                        $TOTALGHE+=$HE;

                        $jor = encrypt($datd['jor'],'p4v4sAp');

                        ?>
                        
                
                        <tr class="<?php echo $bgfes;?> <?PHP echo $CE;?>" id="rowfec_<?php echo $iddia;?>" <?php echo $ME;?>>
                            <td class="p-0 align-middle text-center"><?PHP echo $dia;?></td>
                            <td class="p-0 align-middle text-center"><?php echo  $icali; if($valalimentacion>0) { ?> <span class="currency"><?php echo $valalimentacion;?></span><?php } ?></td>
                            <td class="p-0 align-middle" style="width:100px">
                                <input class="form-control form-control-sm currency <?php if($bonificacion<=0){ echo "hide"; }?>" id="bon_<?php echo $iddia;?>" value="<?php echo $valbonificacion;?>" style="width:100px" onkeypress="return validar_texto(event)" onkeyup="CRUDLIQUIDAR('UPDATEBON','<?PHP echo $iddia;?>', 1)">
                            </td>
                            <td class="p-0 align-middle" style="width:100px">
                                <input class="form-control form-control-sm currency <?php if($tothoras<=0){ echo "hide"; }?>" id="aux_<?php echo $iddia;?>" value="<?php echo $auxilio;?>" style="width:100px" onkeypress="return validar_texto(event)"  onkeyup="CRUDLIQUIDAR('UPDATEAUX','<?PHP echo $iddia;?>', 1)">
                            </td>
                            <td class="p-0 text-center align-middle" style="width:100px; cursor:pointer" title="Clic para realizar cambios <?php echo $fecha; ?>" onclick="CRUDJORNADA('EDITARJORNADA','<?php echo $jor;?>')"><?php echo $fec;?></td>
                            <td class="text-center p-0 align-middle"><?php echo $aminit;?></td>
                            <td class="text-center p-0 align-middle">a</td>
                            <td class="text-center p-0 align-middle" title="<?php echo $afin;?>"><?php echo $amfint;?></td>
                            <td class="text-center p-0 align-middle"><?php echo $totalam;?></td>
                            <td class="text-center p-0 align-middle"><?php echo $pminit;?></td>
                            <td class="text-center p-0 align-middle">a</td>
                            <td class="text-center p-0 align-middle"><?php echo $pmfint;?></td>
                            <td class="text-center p-0 align-middle"><?php echo $totalpm;?></td>
                            <td class="text-center p-0 align-middle"><?php echo number_format($tothoras,2,".","");?></td>
                            <td class="text-center p-0 align-middle"><?php echo number_format($horario,2,".","");?></td>
                            <td class="text-center p-0 align-middle"><?PHP echo $compensado;?></td>
                            <td class="text-center p-0 align-middle"><?PHP echo "";?></td>
                            <td class="text-center p-0 align-middle"><?PHP echo $remunerado;?></td>
                            <td class="text-center p-0 align-middle"><?PHP echo $obs;?></td>
                            <td class="text-center p-0 align-middle <?php if($HEDO<0){ echo "bg-danger"; } ?>"><?php echo number_format($HEDO,2);?></td>
                            <td class="text-center p-0 align-middle <?php if($HDF<0){ echo "bg-danger"; } ?>"><?php echo number_format($HDF,2);?></td>
                            <td class="text-center p-0 align-middle <?php if($HEDF<0){ echo "bg-danger"; } ?>"><?php echo number_format($HEDF,2);?></td>
                            <td class="text-center p-0 align-middle <?php if($HENO<0){ echo "bg-danger"; } ?>"><?php echo number_format($HENO,2);?></td>
                            <td class="text-center p-0 align-middle <?php if($HENF<0){ echo "bg-danger"; } ?>"><?php echo number_format($HENF,2);?></td>
                            <td class="text-center p-0 align-middle <?php if($RN<0){ echo "bg-danger"; } ?>"><?php echo number_format($RN,2);?></td>
                            <td class="text-center p-0 align-middle <?php if($HNF<0){ echo "bg-danger"; } ?>"><?php echo number_format($HNF,2);?></td>
                            <td class="text-center p-0 align-middle <?php if($RD<0){ echo "bg-danger"; } ?>"><?php echo number_format($RD,2);?></td>
                            <td class="text-center p-0 align-middle <?php if($TE>0){ echo "bg-success"; } ?>"><?php echo number_format($HE,2);?></td>                            
                        </tr>
                        <?PHP
                        // CALCULAR SI DIA ES DOMINGO SI LA SUMA DE LA SEMANA MENOS LAS EXTRAS ES MENOR A 48 SI SI ES MENOR A LAS HORAS SEMANA LA DIFERENTE PARA LLEGAR A 48 SE LE RESTAN A LAS EXTRAS DE ESA SEMANA
                        if($dia==9)// pone en uno para activar
                        {
                            // Obtener lunes y domingo de la semana usando STR_TO_DATE con parámetros preparados
                            $sqlFecha = "SELECT 
                            STR_TO_DATE(CONCAT(:sem, ' Monday'), '%x%v %W') AS LUN, 
                            STR_TO_DATE(CONCAT(:sem, ' Sunday'), '%x%v %W') AS DOM";

                            $stmtFecha = $conn->prepare($sqlFecha);
                            $stmtFecha->bindParam(':sem', $sem);
                            $stmtFecha->execute();

                            $datld = $stmtFecha->fetch(PDO::FETCH_ASSOC);
                            $sLunes = $datld['LUN'];
                            $sDomingo = $datld['DOM'];

                            // Obtener resumen de horas semanales
                            $sqlHoras = "SELECT 
                            SUM(lid_hedo) AS tothedo,
                            SUM(lid_hdf) AS tothdf,
                            SUM(lid_hedf) AS tothedf,
                            SUM(lid_heno) AS totheno,
                            SUM(lid_henf) AS tothenf,
                            SUM(lid_rn) AS totrn,
                            SUM(lid_hnf) AS tothnf,
                            SUM(lid_rd) AS totrd,
                            SUM(lid_horas) AS totalhoras,
                            SUM(lid_permisos) AS totpermisos,
                            SUM(lid_val_bonificacion) AS totbon,
                            SUM(CASE WHEN lid_procede = 1 AND lid_alimentacion = 1 THEN lid_val_alimentacion ELSE 0 END) AS totali,
                            SUM(lid_auxilio) AS totaux
                            FROM tbl_liquidar_dias
                            WHERE usu_clave_int = :emp
                            AND lid_fecha BETWEEN :lunes AND :domingo";

                            $stmtHoras = $conn->prepare($sqlHoras);
                            $stmtHoras->bindParam(':emp', $emp, PDO::PARAM_INT);
                            $stmtHoras->bindParam(':lunes', $sLunes);
                            $stmtHoras->bindParam(':domingo', $sDomingo);
                            $stmtHoras->execute();

                            $datsem = $stmtHoras->fetch(PDO::FETCH_ASSOC);

                            $HEDOSEM = $datsem['tothedo']; // HORAS EXTRAS DIURNAS ORDINA
                            $HDFSEM  = $datsem['tothdf'];
                            $HEDFSEM = $datsem['tothedf'];
                            $HENOSEM = $datsem['totheno'];
                            $HENFSEM = $datsem['tothenf'];
                            $RNSEM   = $datsem['totrn'];
                            $HNFSEM  = $datsem['tothnf'];
                            $RDSEM   = $datsem['totrd'];
                            $TOTALSEM = $datsem['totalhoras'];
                             //JORNADA EXTRA
                            $HESEM = $HEDOSEM + $HDFSEM + $HEDFSEM + $HENOSEM + $HENFSEM + $RNSEM + $HNFSEM + $RDSEM;
                            $TEDSEM = $HEDOSEM + $HEDFSEM + $HENOSEM + $HENFSEM;

                            $DIF1 = $TOTALSEM - $HEDOSEM;
                            if($DIF1<$hsemana)
                            {
                                $HEDOSEM = $HEDOSEM - ($hsemana - $DIF1);
                            }
                            ?>
                            <tr>
                                <td class="bg-info"></td>
                                <td class="bg-info"></td>
                                <td class="bg-info"></td>
                                <td class="bg-info"></td>                                
                                <td class="bg-info">Total Semana</td>
                                <td class="bg-info"></td>
                                <td class="bg-info"></td>
                                <td class="bg-info"></td>
                                <td class="bg-info"></td>
                                <td class="bg-info"></td>
                                <td class="bg-info"></td>
                                <td class="bg-info"></td>
                                <td class="bg-info"></td>
                                <td class="bg-info"><?php echo number_format($TOTALSEM,2);?></td>
                                <td class="bg-info"></td>
                                <td class="bg-info"></td>
                                <td class="bg-info"></td>
                                <td class="bg-info"></td>
                                <td class="bg-info"></td>
                                <td class="text-center p-0 align-middle bg-info currencyinf "><?php echo number_format($HEDOSEM,2);?></td>
                                <td class="text-center p-0 align-middle bg-info currencyinf "><?php echo number_format($HDFSEM,2);?></td>
                                <td class="text-center p-0 align-middle bg-info currencyinf "><?php echo number_format($HEDFSEM,2);?></td>
                                <td class="text-center p-0 align-middle bg-info currencyinf "><?php echo number_format($HENOSEM,2);?></td>
                                <td class="text-center p-0 align-middle bg-info currencyinf "><?php echo number_format($HENFSEM,2);?></td>
                                <td class="text-center p-0 align-middle bg-info currencyinf "><?php echo number_format($RNSEM,2);?></td>
                                <td class="text-center p-0 align-middle bg-info currencyinf "><?php echo number_format($HNFSEM,2);?></td>
                                <td class="text-center p-0 align-middle bg-info currencyinf "><?php echo number_format($RDSEM,2);?></td>                 
                                <td class="text-center p-0 align-middle bg-info currencyinf "><?php echo number_format($HESEM,2);?></td>                 
                            </tr>
                            <?PHP
                        }

                        ?>
                        <?php                         
                         
                        if(($numd==15 || $numd==$diafinal) and $d!=0)
                        {
                            ?>
                             <tr>
                                <td class="bg-primary"></td>
                                <td class="bg-primary"></td>
                                <td class="bg-primary"></td>
                                <td class="bg-primary"></td>                                
                                <td class="bg-primary">Quincena: <?php echo $nmes." ".$numd; ?></td>
                                <td class="bg-primary"></td>
                                <td class="bg-primary"></td>
                                <td class="bg-primary"></td>
                                <td class="bg-primary"></td>
                                <td class="bg-primary"></td>
                                <td class="bg-primary"></td>
                                <td class="bg-primary"></td>
                                <td class="bg-primary"></td>
                                <td class="bg-primary"></td>
                                <td class="bg-primary"></td>
                                <td class="bg-primary"></td>
                                <td class="bg-primary"></td>
                                <td class="bg-primary"></td>
                                <td class="bg-primary"></td>
                                <td class="text-center p-0 align-middle bg-primary currencyinf "><?php echo number_format($TOTALHEDO,2);?></td>
                                <td class="text-center p-0 align-middle bg-primary currencyinf "><?php echo number_format($TOTALHDF,2);?></td>
                                <td class="text-center p-0 align-middle bg-primary currencyinf "><?php echo number_format($TOTALHEDF,2);?></td>
                                <td class="text-center p-0 align-middle bg-primary currencyinf "><?php echo number_format($TOTALHENO,2);?></td>
                                <td class="text-center p-0 align-middle bg-primary currencyinf "><?php echo number_format($TOTALHENF,2);?></td>
                                <td class="text-center p-0 align-middle bg-primary currencyinf "><?php echo number_format($TOTALRN,2);?></td>
                                <td class="text-center p-0 align-middle bg-primary currencyinf "><?php echo number_format($TOTALHNF,2);?></td>
                                <td class="text-center p-0 align-middle bg-primary currencyinf "><?php echo number_format($TOTALRD,2);?></td>                 
                                <td class="text-center p-0 align-middle bg-primary currencyinf "><?php echo number_format($TOTALHE,2);?></td>                 
                            </tr>
                            <?php
                             $TOTALHE = 0;
                             $TOTALHEDO = 0;
                             $TOTALHDF  = 0;
                             $TOTALHEDF = 0;
                             $TOTALHENO = 0;
                             $TOTALHENF = 0;
                             $TOTALRN   = 0;
                             $TOTALHNF  = 0;
                             $TOTALRD   = 0;
                        }
                    } 
                }
                ?>
            </tbody>
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
                    <th class="text-center p-0 align-middle bg-secondary currencyinf "><?php echo number_format($TOTALGHEDO,2);?></th>
                    <th class="text-center p-0 align-middle bg-secondary currencyinf "><?php echo number_format($TOTALGHDF,2);?></th>
                    <th class="text-center p-0 align-middle bg-secondary currencyinf "><?php echo number_format($TOTALGHEDF,2);?></th>
                    <th class="text-center p-0 align-middle bg-secondary currencyinf "><?php echo number_format($TOTALGHENO,2);?></th>
                    <th class="text-center p-0 align-middle bg-secondary currencyinf "><?php echo number_format($TOTALGHENF,2);?></th>
                    <th class="text-center p-0 align-middle bg-secondary currencyinf "><?php echo number_format($TOTALGRN,2);?></th>
                    <th class="text-center p-0 align-middle bg-secondary currencyinf "><?php echo number_format($TOTALGHNF,2);?></th>
                    <th class="text-center p-0 align-middle bg-secondary currencyinf "><?php echo number_format($TOTALGRD,2);?></th>
                    <th class="text-center p-0 align-middle bg-secondary currencyinf "><?php echo number_format($TOTALGHE,2);?></th>                              
                </tr>
            </tfoot>
        </table>
        <!-- <script src="jsdatatable/jornadas/jsliquidar.js?<?php echo time();?>"></script> -->
       
        <?php
        /*$numali = 0;
        //echo $numbon;
        if($numali>0)
        {
            echo "<script>$('table#tbLiquidar".$emp." > tr > td:nth-child(1), table#tbLiquidar".$emp." > tr > th:nth-child(1)').show();</script>";
        }
        else
        {
            echo "<script>$('table#tbLiquidar".$emp." > tr > td:nth-child(1), table#tbLiquidar".$emp." > tr > th:nth-child(1)').hide()</script>";
        }
        if($numbon>0)
        {
            //echo $numbon;
            echo "<script>$('#tbLiquidar".$emp." > tr > td:nth-child(2), table#tbLiquidar".$emp." > tr > th:nth-child(2)').show();</script>";
        }
        else
        {
            echo "<script>$('table#tbLiquidar".$emp." > tr > td:nth-child(2), #tbLiquidar".$emp." > tr > th:nth-child(2)').hide()</script>";  
        }*/
        //CALCULO TOTALES INICI
        $info = $toh->editUsuario($emp);
        $salariobase = $info['sal'];
        $vrhor = $salariobase/$hmes;

        $hedo = $toh->getPorcentaje('HEDO');
        $hdf  = $toh->getPorcentaje('HDF');
        $hedf = $toh->getPorcentaje('HEDF');
        $heno = $toh->getPorcentaje('HENO');
        $henf = $toh->getPorcentaje('HENF');
        $rn   = $toh->getPorcentaje('RN');
        $hnf  = $toh->getPorcentaje('HNF');
        $rd   = $toh->getPorcentaje('RD');
        $are  = $toh->getPorcentaje('ARE');
        $TOTALGHEDO  = ($TOTALGARE>0)? $TOTALGHEDO-$TOTALGARE: $TOTALGHEDO;
        $tithedo = $hedo['hor_descripcion'];  
        $codhedo = $hedo['hor_codigo'];  
        $porhedo = $hedo['hor_porcentaje'];        
        $vhhedo  = $porhedo * $vrhor;
        $tothedo = $vhhedo*$TOTALGHEDO;

        $tithdf = $hdf['hor_descripcion'];  
        $codhdf = $hdf['hor_codigo'];  
        $porhdf = $hdf['hor_porcentaje'];        
        $vhhdf  = $porhdf * $vrhor;
        $tothdf = $vhhdf*$TOTALGHDF;

        $tithedf = $hedf['hor_descripcion'];  
        $codhedf = $hedf['hor_codigo'];  
        $porhedf = $hedf['hor_porcentaje'];        
        $vhhedf  = $porhedf * $vrhor;
        $tothedf = $vhhedf*$TOTALGHEDF;

        $titheno = $heno['hor_descripcion'];  
        $codheno = $heno['hor_codigo'];  
        $porheno = $heno['hor_porcentaje'];        
        $vhheno  = $porheno * $vrhor;
        $totheno = $vhheno*$TOTALGHENO;

        $tithenf = $henf['hor_descripcion']; 
        $codhenf = $henf['hor_codigo'];   
        $porhenf = $henf['hor_porcentaje'];        
        $vhhenf  = $porhenf * $vrhor;
        $tothenf = $vhhenf*$TOTALGHENF;

        $titrn = $rn['hor_descripcion'];  
        $codrn = $rn['hor_codigo'];  
        $porrn = $rn['hor_porcentaje'];        
        $vhrn  = $porrn * $vrhor;
        $totrn = $vhrn*$TOTALGRN;

        $tithnf = $hnf['hor_descripcion']; 
        $codhnf = $hnf['hor_codigo'];   
        $porhnf = $hnf['hor_porcentaje'];        
        $vhhnf  = $porhnf * $vrhor;
        $tothnf = $vhhnf*$TOTALGHNF;

        $titrd  = $rd['hor_descripcion'];  
        $codrd = $rd['hor_codigo'];  
        $porrd  = $rd['hor_porcentaje'];        
        $vhrd   = $porrd * $vrhor;
        $totrd  = $vhrd*$TOTALGRD;

        $totalare = 0;
        $titare  = $are['hor_descripcion'];  
        $codare = $are['hor_codigo'];  
        $porare  = $are['hor_porcentaje'];        
        $vhare   = $porare * $vrhor;
        $totare  = $vhare* $TOTALGARE; //$totalare;

        $totalhoras = $TOTALGHEDO + $TOTALGHDF + $TOTALGHEDF + $TOTALGHENO + $TOTALGHENF + $TOTALGRN + $TOTALGHNF + $TOTALGRD; // + $totalhem + $totalare;
        $total =  $tothedo + $tothdf + $tothedf + $totheno + $tothenf + $totrn + $tothnf + $totrd + $totalmaquina + $totare;

        $dscto = 0;
        $iva = 0;
        $rtefuente = 0;
        $rteiva = 0;
        $neto = $total + $iva  - $dscto - $rtefuente - $rteiva;


        ?>
        <hr>
        <div class="col-md-6">
      
        
            
        <table  style="width:50%; font-size:12px" border=0 cellspacing="1" cellspading="1">
            <thead>
            <tr>
                <th colspan="5" class="divider tdsin"></th>
            </tr>
            <tr>
                <th>Salario Base:</th>
                <th><?php echo "$".number_format($salariobase,2);?></th>
                <th class="tdsin"></th>
                <th class="tdsin"></th>
                <th class="tdsin"></th>
            </tr>
            <tr>
                <th>Valor Hora:</th>
                <th><?php echo "$".number_format($vrhor,2);?></th>
                <th class="tdsin"></th>
                <th class="tdsin"></th>
                <th class="tdsin"></th>
            </tr>
            <?php if($totalali>0){ ?>
            <tr>
                <th>Alimentación:</th>
                <th><?php echo "$".number_format($totalali,2);?></th>
                <th class="tdsin"></th>
                <th class="tdsin"></th>
                <th class="tdsin"></th>
            </tr>
            <?php } ?>
            <?php if($totalaux>0){ ?>
            <tr>
                <th>Auxilio Vehiculo:</th>
                <th><?php echo "$".number_format($totalaux,2);?></th>
                <th class="tdsin"></th>
                <th class="tdsin"></th>
                <th class="tdsin"></th>
            </tr>
            <?php } ?>
            <?php if($totalbon>0){ ?>
            <tr>
                <th>Bonificación:</th>
                <th><?php echo "$".number_format($totalbon,2);?></th>
                <th class="tdsin"></th>
                <th class="tdsin"></th>
                <th class="tdsin"></th>
            </tr>
            <?php } ?>
            <tr>
                <th colspan="5" class="divider tdsin"></th>
            </tr>
            <tr>
                <th class="bg-primary text-center">DESCRIPCION</th>
                <th class="bg-primary text-center"># HORAS</th>
                <th class="bg-primary text-center">%</th>
                <th class="bg-primary text-center">VALOR</th>
                <th class="bg-primary text-center">TOTAL</th>            
            </tr>
            
            </thead>
            <tbody>
            <tr>
                <td class="bold"><?php echo $tithedo; ?></td>
                <td class="text-center"><?php echo number_format($TOTALGHEDO,2);?></td>
                <td class="text-center"><?php echo number_format($porhedo*100,0) ?>%</td>
                <td class="text-right"><?php echo "$".number_format($vhhedo,2);?></td>
                <td class="text-right"><?php echo "$".number_format($tothedo,2);?></td>            
            </tr>
            <tr>
                <td class="bold"><?php echo $tithdf; ?></td>
                <td class="text-center"><?php echo number_format($TOTALGHDF,2);?></td>
                <td class="text-center"><?php echo number_format($porhdf*100,0) ?>%</td>
                <td class="text-right"><?php echo "$".number_format($vhhdf,2);?></td>
                <td class="text-right"><?php echo "$".number_format($tothdf,2);?></td>            
            </tr>
            <tr>
                <td class="bold"><?php echo $tithedf; ?></td>
                <td class="text-center"><?php echo number_format($TOTALGHEDF,2);?></td>
                <td class="text-center"><?php echo number_format($porhedf*100,0) ?>%</td>
                <td class="text-right"><?php echo "$".number_format($vhhedf,2);?></td>
                <td class="text-right"><?php echo "$".number_format($tothedf,2);?></td>            
            </tr>
            <tr>
                <td class="bold"><?php echo $titheno; ?></td>
                <td class="text-center"><?php echo number_format($TOTALGHENO,2);?></td>
                <td class="text-center"><?php echo number_format($porheno*100,0) ?>%</td>
                <td class="text-right"><?php echo "$".number_format($vhheno,2);?></td>
                <td class="text-right"><?php echo "$".number_format($totheno,2);?></td>            
            </tr>
            <tr>
                <td class="bold"><?php echo $tithenf; ?></td>
                <td class="text-center"><?php echo number_format($TOTALGHENF,2);?></td>
                <td class="text-center"><?php echo number_format($porhenf*100,0) ?>%</td>
                <td class="text-right"><?php echo "$".number_format($vhhenf,2);?></td>
                <td class="text-right"><?php echo "$".number_format($tothenf,2);?></td>            
            </tr>
            <tr>
                <td class="bold"><?php echo $titrn; ?></td>
                <td class="text-center"><?php echo number_format($TOTALGRN,2);?></td>
                <td class="text-center"><?php echo number_format($porrn*100,0) ?>%</td>
                <td class="text-right"><?php echo "$".number_format($vhrn,2);?></td>
                <td class="text-right"><?php echo "$".number_format($totrn,2);?></td>            
            </tr>
            <tr>
                <td class="bold"><?php echo $tithnf; ?></td>
                <td class="text-center"><?php echo number_format($TOTALGHNF,2);?></td>
                <td class="text-center"><?php echo number_format($porhnf*100,0) ?>%</td>
                <td class="text-right"><?php echo "$".number_format($vhhnf,2);?></td>
                <td class="text-right"><?php echo "$".number_format($tothnf,2);?></td>            
            </tr>
            <tr>
                <td class="bold"><?php echo $titrd; ?></td>
                <td class="text-center"><?php echo number_format($TOTALGRD,2);?></td>
                <td class="text-center"><?php echo number_format($porrd*100,0) ?>%</td>
                <td class="text-right"><?php echo "$".number_format($vhrd,2);?></td>
                <td class="text-right"><?php echo "$".number_format($totrd,2);?></td>            
            </tr>
            <?php if($TOTALGARE>0){ ?>
            <tr>
                <td class="bold"><?php echo $titare; ?></td>
                <td class="text-center"><?php echo number_format($TOTALGARE,2);?></td>
                <td class="text-center"><?php echo number_format($porare*100,0) ?>%</td>
                <td class="text-right"><?php echo "$".number_format($vhare,2);?></td>
                <td class="text-right"><?php echo "$".number_format($totare,2);?></td>            
            </tr>
            <?php 
            }
            ?>
            </tbody>
            <tfoot>
            <tr>
                <th class="bold bg-primary text-center" colspan="4">TOTAL</th>
                <th class="text-right bg-primary"><?php echo "$".number_format($total,2);?></th>            
            </tr>
            </tfoot>
            </table>
        </div>  
        <script>
            var numali = <?php echo $numali;?>;
            var numbon = <?php echo $numbon;?>;
            var visali = numali<=0?false:true;
            var visbon = numbon<=0?false:true;
            var table = $('#tbLiquidar<?php echo $emp;?>' ).DataTable({
                "columnDefs": [
                    { "targets": [1],"visible": visali },
                    { "targets": [2],"visible": visbon } 
                ],
                "searching": false,
                "ordering": false,
                "paging":false,
                "info": false,
                //keys: true,
                stateSave: true
            });
            var idtr = "";
            $('#tbLiquidar<?php echo $emp;?> tbody').on( 'click', 'tr', function () {
                var id = $(this).attr('id');
                if ( $(this).hasClass('selected') && id!=idtr ) {
                    $(this).removeClass('selected');
                }
                else {
                    table.$('tr.selected').removeClass('selected');
                    $(this).addClass('selected');
                    idtr = id;
                }
            } );
        </script>
        <?php
    }
    else

    if($tip==2 and $obr>0)
    {
        $toh = new General();
        // echo $nom;
        // date_format(jod_fecha,'%Y') = '".$ano."' and date_format(jod_fecha,'%m') = '".$mes."'
        // $condias = mysqli_query($conectar, "SELECT  jd.jod_clave_int id, jd.jod_fecha fec, DAYOFWEEK(jd.jod_fecha) dia,jd.jod_semana sem,jd.jod_estado est,jd.jod_observacion obs,totalhorasDia(jd.jod_clave_int) tothoras,diaHabil(jod_fecha) fes,diaHabil(adddate(jod_fecha, interval 1 day)) fess, tn.tin_clave_int idnov, tn.tin_suma sumnov, tn.tin_nombre nomnov, j.jor_clave_int jor FROM tbl_jornada_dias jd LEFT OUTER JOIN tbl_jornada j ON j.jor_clave_int = jd.jor_clave_int LEFT OUTER JOIN tbl_tipo_novedad tn on tn.tin_clave_int = jd.tin_clave_int WHERE jd.jod_fecha between '".$ini."' and '".$fin."'  and jd.usu_clave_int = '".$emp."'  order by jod_fecha  asc"); //and j.jor_estado = 1
        // $numdias = mysqli_num_rows($condias);

        $sqlDias = "SELECT  
        jd.jod_clave_int AS id, 
        jd.jod_fecha AS fec, 
        DAYOFWEEK(jd.jod_fecha) AS dia,
        jd.jod_semana AS sem,
        jd.jod_estado AS est,
        jd.jod_observacion AS obs,
        totalhorasDia(jd.jod_clave_int) AS tothoras,
        diaHabil(jd.jod_fecha) AS fes,
        diaHabil(ADDDATE(jd.jod_fecha, INTERVAL 1 DAY)) AS fess,
        tn.tin_clave_int AS idnov,
        tn.tin_suma AS sumnov,
        tn.tin_nombre AS nomnov,
        j.jor_clave_int AS jor
        FROM tbl_jornada_dias jd 
        LEFT OUTER JOIN tbl_jornada j ON j.jor_clave_int = jd.jor_clave_int 
        LEFT OUTER JOIN tbl_tipo_novedad tn ON tn.tin_clave_int = jd.tin_clave_int 
        WHERE jd.jod_fecha BETWEEN :ini AND :fin 
        AND jd.usu_clave_int = :emp 
        ORDER BY jd.jod_fecha ASC";

        $stmtDias = $conn->prepare($sqlDias);
        $stmtDias->bindParam(':ini', $ini);
        $stmtDias->bindParam(':fin', $fin);
        $stmtDias->bindParam(':emp', $emp, PDO::PARAM_INT);
        $stmtDias->execute();

        $dias = $stmtDias->fetchAll(PDO::FETCH_ASSOC);
        $numdias = count($dias);

        $numali = 0;
        $numbon = 0;
        ?>
        <table class="table table-bordered table-striped" style="width:100%" id="tbLiquidar<?php echo $emp;?>">
            <thead>
                <tr>
                    <th class="dt-head-center bg-terracota align-middle" rowspan="2"></th>
                    <th class="dt-head-center bg-terracota align-middle" colspan="2">Alimentación</th>
                    <th class="dt-head-center bg-terracota align-middle" rowspan="2" style="width:100px">Bonificación</th>
                    <th class="dt-head-center bg-terracota align-middle" style="width:100px">Fecha</th>
                    <th class="dt-head-center bg-terracota align-middle" colspan="3">Mañana</th>
                    <th class="dt-head-center bg-terracota align-middle">Total Horas</th>
                    <th class="dt-head-center bg-terracota align-middle" colspan="3">Tarde</th>
                    <th class="dt-head-center bg-terracota align-middle">Total Horas</th>
                    <th class="dt-head-center bg-terracota">Horas Dia</th>
                    <th rowspan="2" class="dt-head-center bg-terracota align-middle">Horario</th>
                    <th rowspan="2" class="dt-head-center bg-terracota align-middle">Compensado</th>
                    <th rowspan="2" class="dt-head-center bg-terracota align-middle">Pagado y compensado</th>
                    <th rowspan="2" class="dt-head-center bg-terracota align-middle">Permiso Remunerado</th>
                    <th rowspan="2" class="dt-head-center bg-terracota align-middle">Observaciones</th>
                    <th class="dt-head-center align-middle">HEDO</th>
                    <th class="dt-head-center align-middle">HDF</th>
                    <th class="dt-head-center align-middle">HEDF</th>
                    <th class="dt-head-center align-middle">HENO</th>
                    <th class="dt-head-center align-middle">HENF</th>
                    <th class="dt-head-center align-middle">RN</th>
                    <th class="dt-head-center align-middle">HNF</th>
                    <th class="dt-head-center align-middle">RD</th>

                </tr>
                <tr>
                    <th class="dt-head-center align-middle">Valor</th>
                    <th class="dt-head-center align-middle">Procede</th>
                    <th class="dt-head-center align-middle">Año/Mes/Dia</th>
                    <th class="dt-head-center align-middle">Desde</th>
                    <th class="dt-head-center align-middle"></th>
                    <th class="dt-head-center align-middle">Hasta</th>
                    <th class="dt-head-center align-middle">#</th>
                    <th class="dt-head-center align-middle">Desde</th>
                    <th class="dt-head-center align-middle"></th>
                    <th class="dt-head-center align-middle">Hasta</th>
                    <th class="dt-head-center align-middle">#</th>
                    <th class="dt-head-center align-middle">#</th>
                    <th class="dt-head-center align-middle">002</th>
                    <th class="dt-head-center align-middle">014</th>
                    <th class="dt-head-center align-middle">004</th>
                    <th class="dt-head-center align-middle">003</th>
                    <th class="dt-head-center align-middle">005</th>
                    <th class="dt-head-center align-middle">006</th>
                    <th class="dt-head-center align-middle">008</th>
                    <th class="dt-head-center align-middle">013</th>
                </tr>
            </thead>
            <tbody>
                <?php
                //TOTALES PARCIALES 
                $TOTALHEDO = 0;
                $TOTALHDF  = 0;
                $TOTALHEDF = 0;
                $TOTALHENO = 0;
                $TOTALHENF = 0;
                $TOTALRN   = 0;
                $TOTALHNF  = 0;
                $TOTALRD   = 0;

                //TOTALES GENERAL 
                $TOTALGHEDO = 0;
                $TOTALGHDF  = 0;
                $TOTALGHEDF = 0;
                $TOTALGHENO = 0;
                $TOTALGHENF = 0;
                $TOTALGRN   = 0;
                $TOTALGHNF  = 0;
                $TOTALGRD   = 0;
                if ($numdias > 0) {
                    while ($datd = $stmtDias->fetch(PDO::FETCH_ASSOC)) {
                
                        $idfec = $datd['id'];
                        $dia  = $datd['dia'];
                        $fec  = $datd['fec'];
                        $tothoras = $datd['tothoras'];
                        $nmes = date("F", strtotime($fec));
                        $ndia = date("l", strtotime($fec));
                        $numd = date("d", strtotime($fec));
                        $anod = date("Y", strtotime($fec));
                        $fecha = $ndia.", ". $numd." de ".$nmes." de ".$anod;
                        
                        $fes = $datd['fes'];
                        $fess = $datd['fess'];//FESTIVO SIGUIENTE
                        $bgfes = ($fes==1)?"bg-secondary":""; //dia festivo
                        $titfes = ($fes==1)?"Dia Festivo":"";
                        
                        //datos para calculo de compensado y permisos remunerados
                        $idnov = $datd['idnov'];
                        $sumnov = $datd['sumnov'];
                        $nomnov = $datd['nomnov'];                        
                        
                        $T5 = "";
                        //DIA INICIAL Y FINAL
                        $diainicial = $numd;
                        $diafinal = date("t",strtotime($fec));

                        $sqldia = "SELECT 
                        lid_clave_int, 
                        lid_fecha AS fec, 
                        lid_hi_man, 
                        lid_hf_man,
                        lid_hi_tar, 
                        lid_hf_tar, 
                        lid_horas,
                        lid_horas_maquina,
                        lid_horario,
                        lid_hedo, 
                        lid_hdf,
                        lid_hedf, 
                        lid_heno, 
                        lid_henf, 
                        lid_rn, 
                        lid_hnf, 
                        lid_rd, 
                        lid_observacion, 
                        lid_alimentacion, 
                        lid_val_alimentacion, 
                        lid_estado, 
                        lid_compensado, 
                        lid_remunerado,
                        lid_procede, 
                        lid_auxilio, 
                        lid_bonificacion, 
                        lid_labores, 
                        lid_val_bonificacion
                        FROM tbl_liquidar_dias_obra 
                        WHERE lid_fecha = :fec 
                        AND usu_clave_int = :emp 
                        AND obr_clave_int = :obr 
                        LIMIT 1";
                        
                        $stmt = $conn->prepare($sqldia);
                        $stmt->bindParam(':fec', $fec);
                        $stmt->bindParam(':emp', $emp, PDO::PARAM_INT);
                        $stmt->bindParam(':obr', $obr, PDO::PARAM_INT);
                        $stmt->execute();
                        
                        $datdia = $stmt->fetch(PDO::FETCH_ASSOC);
                        $iddia = $datdia['lid_clave_int'] ?? null;

                        $aini = $datdia['lid_hi_man'];
                        $afin = $datdia['lid_hf_man'];
                        $pini = $datdia['lid_hi_tar'];
                        $pfin = $datdia['lid_hf_tar'];
                        $horario = $datdia['lid_horario'];
                        
                        $HEDO = $datdia['lid_hedo']; // HORAS EXTRAS DIURNAS ORDINA
                        $HDF  = $datdia['lid_hdf'];
                        $HEDF = $datdia['lid_hedf'];
                        $HENO = $datdia['lid_heno'];
                        $HENF = $datdia['lid_henf'];
                        $RN   = $datdia['lid_rn'];
                        $HNF  = $datdia['lid_hnf'];
                        $RD   = $datdia['lid_rd'];
                        $obs  = $datdia['lid_observacion'];

                        $ali = $datdia['lid_alimentacion'];
                        if($ali==1){ $numali++;}
                        
                        $icali = $ali>0 ? "<i class='fas fa-utensils'></i>":"";
                        $valalimentacion = $datdia['lid_val_alimentacion'];
                        $compensado = $datdia['lid_compensado'];
                        $remunerado = $datdia['lid_remunerado'];
                        $procede =  $datdia['lid_procede'];
                        $auxilio = $datdia['lid_auxilio'];
                        $bonificacion = $datdia['lid_bonificacion'];
                        if($bonificacion==1){ $numbon++;}
                        
                        $labores = $datdia['lid_labores'];
                        $valbonificacion = $datdia['lid_val_bonificacion'];
                        
                        //TOTAL HORAS
                        $totalam = $toh->fnHoras($fec,$aini,$afin);
                        $totalpm = $toh->fnHoras($fec,$pini,$pfin);
                        $tothoras = $totalam + $totalpm; 
                        
                        $tothorasmaquina = $datdia['lid_horas_maquina'];
                        $thorario = $horario;
                        $horario = ($fes==1)?0:$horario;

                        $aminit  = ($aini!="" and $totalam>0)? date("g:i a", strtotime($aini)): "";
                        $amfint  = ($afin!="" and $totalam>0)? date("g:i a", strtotime($afin)): "";
                        $pminit  = ($pini!="" and $totalpm>0)? date("g:i a", strtotime($pini)): "";
                        $pmfint  = ($pfin!="" and $totalpm>0)? date("g:i a", strtotime($pfin)): "";

                        $TOTALHEDO+= $HEDO;
                        $TOTALHDF+= $HDF;
                        $TOTALHEDF+= $HEDF;
                        $TOTALHENO+= $HENO;
                        $TOTALHENF+= $HENF;
                        $TOTALRN+= $RN;
                        $TOTALHNF+= $HNF;
                        $TOTALRD+= $RD;

                        $TOTALGHEDO+= $HEDO;
                        $TOTALGHDF+= $HDF;
                        $TOTALGHEDF+= $HEDF;
                        $TOTALGHENO+= $HENO;
                        $TOTALGHENF+= $HENF;
                        $TOTALGRN+= $RN;
                        $TOTALGHNF+= $HNF;
                        $TOTALGRD+= $RD;

                        $HEDO  = str_replace(",",".",$HEDO);
                        $HDF  = str_replace(",",".",$HDF);
                        $HEDF  = str_replace(",",".",$HEDF);
                        $HENO  = str_replace(",",".",$HENO);
                        $HENF  = str_replace(",",".",$HENF);
                        $RN  = str_replace(",",".",$RN);
                        $HNF  = str_replace(",",".",$HNF);
                        $RD = str_replace(",",".",$RD);

                        $tothoras = str_replace(",",".",$tothoras);
                        $tothorasmaquina = str_replace(",",".",$tothorasmaquina); 
                        
                        $jor = encrypt($datd['jor'],'p4v4sAp');

                        $TED = $HEDO + $HEDF + $HENO + $HENF;
                        $DE = $TED-$limex;
                        $ME = ($TED>$limex)?" title='Excedio el límite de horas Extras en ".$DE.". Limite de Horas extras dia: ".$limex."' data-toggle='tooltip' ":"";
                        $CE = ($TED>$limex)?"limex":""; //CLASE QUE INDICA QUE EXCEDE EL MAXIO DE HORAS EXTRAS DISPONIBLE
                        ?>
                
                        <tr class="<?php echo $bgfes;?> <?PHP echo $CE;?>" id="rowfec_<?php echo $iddia;?>" <?PHP echo $ME;?>>
                            <td class="text-center p-0 align-middle"><?php echo $dia;?></td>
                            <td class="text-center p-0 align-middle"><?php echo $icali; if($valalimentacion>0) { ?> <span class="currency"><?php echo $valalimentacion;?></span><?php } ?></td>
                            <td class="text-center p-0 align-middle">
                                <div class="btn-group btn-group-toggle btn-group-sm <?php if($ali!=1){ echo "hide"; }?>" data-toggle="buttons">
                                    <label class="btn btn-success <?php if($procede==1){ echo "active"; }?>">
                                        <input type="radio" onchange="CRUDLIQUIDAR('UPDATEPROCEDE','<?PHP echo $iddia;?>')" name="radprocede<?php echo $iddia;?>" id="radestado1_<?php echo $iddia; ?>"  <?php if($procede==1){ echo "checked"; }?> value="1">  Si
                                    </label>
                                    <label class="btn btn-danger <?php if($procede!=1){ echo "active"; }?>">
                                        <input type="radio" onchange="CRUDLIQUIDAR('UPDATEPROCEDE','<?PHP echo $iddia;?>')" name="radprocede<?php echo $iddia;?>"  id="radestado2_<?php echo $iddia;?>"  value="0" <?php if($procede!=1){ echo "checked"; }?>> No
                                    </label>
                                </div>
                            </td>
                            <td class="text-center p-0 align-middle" style="width:100px">
                                <input class="form-control form-control-sm currency <?php if($bonificacion<=0){ echo "hide"; } ?>" id="bon_<?php echo $iddia;?>" value="<?php echo $valbonificacion;?>" onkeyup="CRUDLIQUIDAR('UPDATEBON','<?PHP echo $iddia;?>', 2)">
                            </td>
                            <td class="p-0 text-center align-middle" style="width:100px; cursor:pointer" title="Doble clic para realizar cambios" onclick="CRUDJORNADA('EDITARJORNADA','<?php echo $jor;?>')"><?php echo $fec;?></td>
                            <td class="text-center p-0 align-middle"><?php echo $aminit;?></td>
                            <td class="text-center p-0 align-middle">a</td>
                            <td class="text-center p-0 align-middle"><?php echo $amfint;?></td>
                            <td class="text-center p-0 align-middle"><?php echo $totalam;?></td>
                            <td class="text-center p-0 align-middle"><?php echo $pminit;?></td>
                            <td class="text-center p-0 align-middle">a</td>
                            <td class="text-center p-0 align-middle"><?php echo $pmfint;?></td>
                            <td class="text-center p-0 align-middle"><?php echo $totalpm;?></td>
                            <td class="text-center p-0 align-middle"><?php echo number_format($tothoras,2,".","");?></td>
                            <td class="text-center p-0 align-middle"><?php echo number_format($horario,2,".","");?></td>
                            <td class="text-center p-0 align-middle"><?PHP echo $compensado;?></td>
                            <td class="text-center p-0 align-middle"><?PHP echo $T5;?></td>
                            <td class="text-center p-0 align-middle"><?PHP echo $remuneador;?></td>
                            <td class="text-center p-0 align-middle"><?php echo $obs;?></td>
                            <td class="text-center p-0 align-middle <?php if($HEDO<0){ echo "bg-danger"; } ?>"><?php echo number_format($HEDO,2);?></td>
                            <td class="text-center p-0 align-middle <?php if($HDF<0){ echo "bg-danger"; } ?>"><?php echo number_format($HDF,2);?></td>
                            <td class="text-center p-0 align-middle <?php if($HEDF<0){ echo "bg-danger"; } ?>"><?php echo number_format($HEDF,2);?></td>
                            <td class="text-center p-0 align-middle <?php if($HENO<0){ echo "bg-danger"; } ?>"><?php echo number_format($HENO,2);?></td>
                            <td class="text-center p-0 align-middle <?php if($HENF<0){ echo "bg-danger"; } ?>"><?php echo number_format($HENF,2);?></td>
                            <td class="text-center p-0 align-middle <?php if($RN<0){ echo "bg-danger"; } ?>"><?php echo number_format($RN,2);?></td>
                            <td class="text-center p-0 align-middle <?php if($HNF<0){ echo "bg-danger"; } ?>"><?php echo number_format($HNF,2);?></td>
                            <td class="text-center p-0 align-middle <?php if($RD<0){ echo "bg-danger"; } ?>"><?php echo number_format($RD,2);?></td>
                        </tr>
                        <?php 
                        
                        if(($numd==15 || $numd==$diafinal) and $d!=0)
                        {
                            ?>
                             <tr>
                                <td class="bg-primary"></td>
                                <td class="bg-primary"></td>
                                <td class="bg-primary"></td>
                                <td class="bg-primary"></td>
                                <td class="bg-primary"></td>
                                <td class="bg-primary">Quincena: <?php echo $nmes." ".$numd; ?></td>
                                <td class="bg-primary"></td>
                                <td class="bg-primary"></td>
                                <td class="bg-primary"></td>
                                <td class="bg-primary"></td>
                                <td class="bg-primary"></td>
                                <td class="bg-primary"></td>
                                <td class="bg-primary"></td>
                                <td class="bg-primary"></td>
                                <td class="bg-primary"></td>
                                <td class="bg-primary"></td>
                                <td class="bg-primary"></td>
                                <td class="bg-primary"></td>
                                <td class="bg-primary"></td>
                                <td class="text-center p-0 align-middle bg-primary currencyinf "><?php echo number_format($TOTALHEDO,2);?></td>
                                <td class="text-center p-0 align-middle bg-primary currencyinf "><?php echo number_format($TOTALHDF,2);?></td>
                                <td class="text-center p-0 align-middle bg-primary currencyinf "><?php echo number_format($TOTALHEDF,2);?></td>
                                <td class="text-center p-0 align-middle bg-primary currencyinf "><?php echo number_format($TOTALHENO,2);?></td>
                                <td class="text-center p-0 align-middle bg-primary currencyinf "><?php echo number_format($TOTALHENF,2);?></td>
                                <td class="text-center p-0 align-middle bg-primary currencyinf "><?php echo number_format($RN,2);?></td>
                                <td class="text-center p-0 align-middle bg-primary currencyinf "><?php echo number_format($TOTALHNF,2);?></td>
                                <td class="text-center p-0 align-middle bg-primary currencyinf "><?php echo number_format($TOTALRD,2);?></td>                      
                            </tr>
                            <?php
                            $TOTALHEDO = 0;
                            $TOTALHDF  = 0;
                            $TOTALHEDF = 0;
                            $TOTALHENO = 0;
                            $TOTALHENF = 0;
                            $TOTALRN   = 0;
                            $TOTALHNF  = 0;
                            $TOTALRD   = 0;
                        }
                    } 
                }
                ?>
            </tbody>
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
                    <th class="text-center p-0 align-middle bg-secondary currencyinf "><?php echo number_format($TOTALGHEDO,2);?></th>
                    <th class="text-center p-0 align-middle bg-secondary currencyinf "><?php echo number_format($TOTALGHDF,2);?></th>
                    <th class="text-center p-0 align-middle bg-secondary currencyinf "><?php echo number_format($TOTALGHEDF,2);?></th>
                    <th class="text-center p-0 align-middle bg-secondary currencyinf "><?php echo number_format($TOTALGHENO,2);?></th>
                    <th class="text-center p-0 align-middle bg-secondary currencyinf "><?php echo number_format($TOTALGHENF,2);?></th>
                    <th class="text-center p-0 align-middle bg-secondary currencyinf "><?php echo number_format($TOTALGRN,2);?></th>
                    <th class="text-center p-0 align-middle bg-secondary currencyinf "><?php echo number_format($TOTALGHNF,2);?></th>
                    <th class="text-center p-0 align-middle bg-secondary currencyinf "><?php echo number_format($TOTALGRD,2);?></th>              
                </tr>
            </tfoot>
        </table>
        <!-- <script src="jsdatatable/jornadas/jsliquidar.js?<?php echo time();?>"></script> -->
        <?php 
        /*if($numali>0)
        {
            echo "<script>$('#tbLiquidar".$emp." tr > td:nth-child(1), #tbLiquidar".$emp." tbody > tr > td:nth-child(2)').show();</script>";
            echo "<script>$('#tbLiquidar".$emp." tr > th:nth-child(1), #tbLiquidar".$emp." tbody > tr > th:nth-child(2)').show();</script>";
        }
        else
        {
            echo "<script>$('#tbLiquidar".$emp." tr > td:nth-child(1), #tbLiquidar".$emp." tbody > tr > td:nth-child(2)').hide()</script>";  
            echo "<script>$('#tbLiquidar".$emp." tr > th:nth-child(1), #tbLiquidar".$emp." tbody > tr > th:nth-child(2)').hide()</script>";  
        }
        if($numbon>0)
        {
            echo "<script>$('#tbLiquidar".$emp." > tr > td:nth-child(3)').show();</script>";
            echo "<script>$('#tbLiquidar".$emp." > tr > th:nth-child(3)').show();</script>";
        }
        else
        {
            echo "<script>$('#tbLiquidar".$emp." > tr > td:nth-child(3)').hide()</script>";  
            echo "<script>$('#tbLiquidar".$emp." > tr > th:nth-child(3)').hide()</script>";  
        }*/
        ?>
        <script>
            var numali = <?php echo $numali;?>;
            var numbon = <?php echo $numbon;?>;
            var visali = numali<=0?false:true;
            var visbon = numbon<=0?false:true;
            var table = $('#tbLiquidar<?php echo $emp;?>' ).DataTable({
                "columnDefs": [
                    { "targets": [0,1],"visible": visali },
                    { "targets": [2],"visible": visbon } 
                ],
                "searching": false,
                "ordering": false,
                "paging":false,
                "info": false,
                //keys: true,
                stateSave: true
            });
            var idtr = "";
            $('#tbLiquidar<?php echo $emp;?> tbody').on( 'click', 'tr', function () {
                var id = $(this).attr('id');
                if ( $(this).hasClass('selected') && id!=idtr ) {
                    $(this).removeClass('selected');
                }
                else {
                    table.$('tr.selected').removeClass('selected');
                    $(this).addClass('selected');
                    idtr = id;
                }
            } );
        </script>
        <?php
    }
    else if($tip==3)//RESUMEN DE LA OBRA
    {
        //and date_format(jd.jod_fecha,'%Y') = '".$ano."' and date_format(jd.jod_fecha,'%m') = '".$mes."'
        $sql = "SELECT 
            SUM(TIME_TO_SEC(TIMEDIFF(joh_fin, joh_inicio)) / 3600) AS totalhoras, 
            o.obr_clave_int AS ido, 
            o.obr_nombre AS nom, 
            o.obr_cencos AS cen, 
            o.obr_hr_mes AS hrmes, 
            o.obr_vr_maquina AS vm 
        FROM tbl_jornada j 
        JOIN tbl_jornada_dias jd ON j.jor_clave_int = jd.jor_clave_int 
        JOIN tbl_jornada_horas jh ON jh.jod_clave_int = jd.jod_clave_int 
        JOIN tbl_tipo_novedad tn ON tn.tin_clave_int = jh.tin_clave_int 
        JOIN tbl_obras o ON o.obr_clave_int = jh.obr_clave_int 
        WHERE tn.tin_tipo = 1 
          AND j.jor_estado = 1 
          AND jd.jod_fecha BETWEEN :ini AND :fin";

        if (!empty($obr)) {
            $sql .= " AND jh.obr_clave_int = :obr";
        }

        $sql .= " GROUP BY o.obr_clave_int";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':ini', $ini);
        $stmt->bindParam(':fin', $fin);
        if (!empty($obr)) {
            $stmt->bindParam(':obr', $obr, PDO::PARAM_INT);
        }

        $stmt->execute();
        $tothoras = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $numtoth = count($tothoras);

       
        ?>
        <table  id="tbLiquidarObra" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th class="dt-head-center bg-terracota">Obra</th>
                    <th class="dt-head-center bg-terracota">Cencos</th>
                    <th class="dt-head-center bg-terracota">Horas Maquina</th>
                    <th class="dt-head-center bg-terracota">Horas por Contrato</th>
                    <th class="dt-head-center bg-terracota">Horas Extras Maquina</th>
                    <th class="dt-head-center bg-terracota">Valor Hora</th>
                    <th class="dt-head-center bg-terracota">Total H.E</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                     while ($dath = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $totalhoras = $dath['totalhoras'];
                        $obra = $dath['nom']; //OBRA|
                        $cenco  = $dath['cen']; // CENCOS
                        $horascontrato = $dath['hrmes']; // HORAS CONTRATADAS POR CADA OBRA
                        $horasextras = $totalhoras - $horascontrato; // DIFERENCIA ENTRE LAS HORAS REALES Y LAS HORAS CONTRATADAS POR Obra
                        $vm = $dath['vm'];// VALOR HORA MAQUINA
                        $totalhe = ($horasextras>0)?$vm * $horasextras: 0; //VALOR TOTAL DE HORAS EXTRAS POR MAQUINA
                        ?>
                        <tr>
                            <td><?php echo $obra;?></td>
                            <td><?php echo $cenco;?></td>
                            <td><?php echo number_format($totalhoras,2,".","");?></td>
                            <td><?php echo number_format($horascontrato,2,".","");?></td>
                            <td><?php echo number_format($horasextras,2,".","");?></td>
                            <td><?php echo "$".number_format($vm,2,".",",");?></td>
                            <td><?php echo "$".number_format($totalhe,2,".",",");?></td>
                        </tr>
                        <?PHP

                    }
                ?>
                
            </tbody>
        </table>
        <?php
    }
    // Capturar el HTML generado por el bloque y generar Excel
    $html_output = '';
    if(ob_get_level()){
        $html_output = ob_get_clean();
        // Volver a imprimir el HTML para que el flujo normal no cambie
        echo $html_output;
    }

    // Generar archivo Excel usando PHPExcel (similar a informegeneralexcell.php)
    try{
        require_once __DIR__ . '/../../clases/PHPExcel.php';
        // crear archivo temporal con el HTML y usar PHPExcel_Reader_HTML para cargarlo
        $tmpHtml = tempnam(sys_get_temp_dir(), 'liq') . '.html';
        file_put_contents($tmpHtml, $html_output);

        $reader = new PHPExcel_Reader_HTML();
        $objPHPExcelFromHtml = $reader->load($tmpHtml);

        // nombre y carpeta destino (misma carpeta que informegeneralexcell.php)
        $destDir = __DIR__ . '/../../modulos/informes/informe/';
        if(!is_dir($destDir)){
            @mkdir($destDir, 0755, true);
        }
        $fileName = 'LIQUIDACION_'.($emp ?? 'NA').'_'.date('Ymd_His').'.xlsx';
        $destPath = $destDir . $fileName;

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcelFromHtml, 'Excel2007');
        $objWriter->save($destPath);
        @unlink($tmpHtml);

        // Enlace relativo para acceso desde la web (ajustar si la estructura cambia)
        $relativePath = 'modulos/informes/informe/' . $fileName;
        echo "<div class='mt-2'>Exportado a Excel: <a href='".$relativePath."' target='_blank'>".$fileName."</a></div>";
    }catch(Exception $e){
        // si falla la generación del excel mostrar un mensaje sin interrumpir la salida
        error_log('Error exportando liquidacion a Excel: '. $e->getMessage());
        echo "<div class='alert alert-warning mt-2'>No se pudo generar el archivo Excel.</div>";
    }

    echo "<script>INICIALIZARCONTENIDO();</script>";
}

else if($opcion=="CARGARLIQUIDAROLD")
{
    $ano = $_POST['ano'];
    $mes = $_POST['mes']; $mes = ($mes<10)?"0".$mes:$mes;
    $emp = $_POST['emp'];
    $obr = $_POST['obr'];
    //$obr = implode(', ', (array)$obr); if($obr==""){$obr1="''";}else {$obr1=$obr;}
    $ini = $_POST['ini'];
    $fin = $_POST['fin'];
    $tip = $_POST['tip'];
    setlocale(LC_ALL,"es_ES.utf8","es_ES","esp");
    if($ini=="" || $fin=="")
    {
        echo "<div class='alert alert-info mt-1'>Seleccionar rango de fechas a liquidar</div>";
    }
    else if($emp<=0)
    {
        echo "<div class='alert alert-info mt-1'>Seleccionar empleado a generar liquidación</div>";
    }
    else if($obr<=0 and $tip==2)
    {
        echo "<div class='alert alert-info mt-1'>Seleccionar obra y empleado a generar liquidación</div>";
    }
    else
    if($tip==1)
    {
        $condat = mysqli_query($conectar, "SELECT hor_clave_int FROM tbl_usuarios WHERE usu_clave_int = '".$emp."' LIMIT 1");
        $dat = mysqli_fetch_array($condat);
        $hor = $dat['hor_clave_int'];
        $reghor = new General();
        $datr = $reghor->editHorario($hor);
        $nom = $datr['hor_nombre'];
        $lun = $datr['hor_1'];
        $mar = $datr['hor_2'];
        $mie = $datr['hor_3'];
        $jue = $datr['hor_4'];
        $vie = $datr['hor_5'];
        $sab = $datr['hor_6'];
        $dom = $datr['hor_7'];


        $arrayhedo = array();
        $arrayhdf  = array();
        $arrayhedf = array();
        $arrayheno = array();
        $arrayhenf = array();
        $arrayrn   = array();
        $arrayhnf  = array();
        $arrayrd   = array();

        $toh = new General();
        //echo $nom;
        //date_format(jod_fecha,'%Y') = '".$ano."' and date_format(jod_fecha,'%m') = '".$mes."'
        $condias = mysqli_query($conectar, "SELECT  jd.jod_clave_int id, jd.jod_fecha fec, WEEKDAY(jd.jod_fecha) dia,jd.jod_semana sem,jd.jod_estado est,jd.jod_observacion obs,totalhorasDia(jd.jod_clave_int) tothoras,diaHabil(jod_fecha) fes,diaHabil(adddate(jod_fecha, interval 1 day)) fess, tn.tin_clave_int idnov, tn.tin_suma sumnov, tn.tin_nombre nomnov  FROM tbl_jornada_dias jd join tbl_jornada j ON j.jor_clave_int = jd.jor_clave_int LEFT OUTER JOIN tbl_tipo_novedad tn on tn.tin_clave_int = jd.tin_clave_int WHERE jd.jod_fecha between '".$ini."' and '".$fin."'  and jd.usu_clave_int = '".$emp."' and j.jor_estado = 1 order by jod_fecha  asc");
        $numdias = mysqli_num_rows($condias);
        ?>
        <table class="table table-bordered table-striped" style="width:100%" id="tbLiquidar">
            <thead>
                <tr>
                    <th rowspan="2"></th>
                    <th class="dt-head-center bg-terracota align-middle" style="width:200px">Fecha</th>
                    <th class="dt-head-center bg-terracota align-middle" colspan="3">Mañana</th>
                    <th class="dt-head-center bg-terracota align-middle">Total Horas</th>
                    <th class="dt-head-center bg-terracota align-middle" colspan="3">Tarde</th>
                    <th class="dt-head-center bg-terracota align-middle">Total Horas</th>
                    <th class="dt-head-center bg-terracota align-middle">Horas Dia</th>
                    <th rowspan="2" class="dt-head-center bg-terracota align-middle">Horario</th>
                    <th rowspan="2" class="dt-head-center bg-terracota align-middle">Compensado</th>
                    <th rowspan="2" class="dt-head-center bg-terracota align-middle">Pagado y compensado</th>
                    <th rowspan="2" class="dt-head-center bg-terracota align-middle">Permiso Remunerado</th>
                    <th rowspan="2" class="dt-head-center bg-terracota align-middle">Observaciones</th>
                    <th class="dt-head-center align-middle">HEDO</th>
                    <th class="dt-head-center align-middle">HDF</th>
                    <th class="dt-head-center align-middle">HEDF</th>
                    <th class="dt-head-center align-middle">HENO</th>
                    <th class="dt-head-center align-middle">HENF</th>
                    <th class="dt-head-center align-middle">RN</th>
                    <th class="dt-head-center align-middle">HNF</th>
                    <th class="dt-head-center align-middle">RD</th>
                    <th class="dt-head-center align-middle" rowspan="2">T.E</th>

                </tr>
                <tr>
                    <th class="dt-head-center align-middle">Dia/Mes/Año</th>
                    <th class="dt-head-center align-middle">Desde</th>
                    <th class="dt-head-center align-middle"></th>
                    <th class="dt-head-center align-middle">Hasta</th>
                    <th class="dt-head-center align-middle">#</th>
                    <th class="dt-head-center align-middle">Desde</th>
                    <th class="dt-head-center align-middle"></th>
                    <th class="dt-head-center align-middle">Hasta</th>
                    <th class="dt-head-center align-middle">#</th>
                    <th class="dt-head-center align-middle">#</th>
                    <th class="dt-head-center align-middle">002</th>
                    <th class="dt-head-center align-middle">014</th>
                    <th class="dt-head-center align-middle">004</th>
                    <th class="dt-head-center align-middle">003</th>
                    <th class="dt-head-center align-middle">005</th>
                    <th class="dt-head-center align-middle">006</th>
                    <th class="dt-head-center align-middle">008</th>
                    <th class="dt-head-center align-middle">013</th>
                    
                </tr>
            </thead>
            <tbody>
                <?php 
                   //TOTALES PARCIALES 
                $TOTALHEDO = 0;
                $TOTALHDF  = 0;
                $TOTALHEDF = 0;
                $TOTALHENO = 0;
                $TOTALHENF = 0;
                $TOTALRN   = 0;
                $TOTALHNF  = 0;
                $TOTALRD   = 0;
                $TOTALHE  = 0;

                //TOTALES GENERAL 
                $TOTALGHEDO = 0;
                $TOTALGHDF  = 0;
                $TOTALGHEDF = 0;
                $TOTALGHENO = 0;
                $TOTALGHENF = 0;
                $TOTALGRN   = 0;
                $TOTALGHNF  = 0;
                $TOTALGRD   = 0;
                $TOTALGHE = 0;

                if($numdias>0)
                {                    
                    for($d=0;$d<$numdias;$d++)
                    {
                        $datd = mysqli_fetch_array($condias);
                
                        $idfec = $datd['id'];
                        $dia  = $datd['dia'];
                        $fec  = $datd['fec'];
                        $tothoras = $datd['tothoras'];
                        $nmes = date("F", strtotime($fec));
                        $ndia = date("l", strtotime($fec));
                        $numd = date("d", strtotime($fec));
                        $anod = date("Y", strtotime($fec));
                        $fecha = $ndia.", ". $numd." de ".$nmes." de ".$anod;
                        $fes = $datd['fes'];
                        $fess = $datd['fess'];//FESTIVO SIGUIENTE
                        $bgfes = ($fes==1)?"bg-secondary":""; //dia festivo
                        $titfes = ($fes==1)?"Dia Festivo":"";

                        //datos para calculo de compensado y permisos remunerados
                        $idnov = $datd['idnov'];
                        $sumnov = $datd['sumnov'];
                        $nomnov = $datd['nomnov'];
                        //DATOS QUE DEFINE SI COMPENSADO SI REMUNERADO
                        // compensado = S5 = >Si o Ambos o vacion
                        $S5  = "";

                        // pagado y compensado = T5 => "SI" o vacio
                        $T5 = "";//

                        // PERMISO REMUNERADO = U5=> SI
                        $U5  = "";

                        $obs = "";
                      

                          //DIA INICIAL Y FINAL
                        $diainicial = $numd;
                        $diafinal = date("t",strtotime($fec));
  

                        //VERIFICAR SI LA HORA INICIAL del dia es pm o am en caso de que sea pm no hay horari am
                        $conam = mysqli_query($conectar, "SELECT joh_clave_int,joh_inicio ini,TIME_FORMAT(joh_inicio,'%p') dig FROM tbl_jornada_horas jh JOIN tbl_tipo_novedad t on t.tin_clave_int = jh.tin_clave_int WHERE  t.tin_tipo = 1 and jod_clave_int = '".$idfec."' order by joh_clave_int ASC LIMIT 1"); //HORAS PM and time_format(joh_fin,'%p')='PM'
                        $datam = mysqli_fetch_array($conam);
                        $amini = $datam['ini'];
                        $dig   = $datam['dig']; 

                        //MINIMO AM SI EXISTIERA
                        $conam = mysqli_query($conectar, "SELECT MIN(joh_inicio) ini FROM tbl_jornada_horas jh JOIN tbl_tipo_novedad t on t.tin_clave_int = jh.tin_clave_int WHERE  t.tin_tipo = 1 and time_format(joh_inicio,'%p')='AM' and jod_clave_int = '".$idfec."'"); //HORAS AM  
                        $datam = mysqli_fetch_array($conam);
                        $aminimin = $datam['ini'];

                        $conpm = mysqli_query($conectar, "SELECT MIN(joh_inicio) fin,TIME_FORMAT(joh_inicio,'%p') dig  FROM tbl_jornada_horas jh JOIN tbl_tipo_novedad t on t.tin_clave_int = jh.tin_clave_int WHERE  t.tin_tipo = 1 and time_format(joh_inicio,'%p')='PM' and jod_clave_int = '".$idfec."'"); //HORAS AM  
                        $datpm = mysqli_fetch_array($conpm);
                        $pminimin=  $datpm['fin'];
                        $pmdig = $datpm['dig'];

                        //HORA FINAL REGISTRADA
                        $conpmf = mysqli_query($conectar, "SELECT joh_clave_int,joh_fin fin FROM tbl_jornada_horas jh JOIN tbl_tipo_novedad t on t.tin_clave_int = jh.tin_clave_int WHERE  t.tin_tipo = 1 and jod_clave_int = '".$idfec."' order by joh_clave_int DESC LIMIT 1"); //HORAS PM and time_format(joh_fin,'%p')='PM'
                        $datpmf = mysqli_fetch_array($conpmf);
                    

                        $aini  = $dig =="PM" ? "" : $aminimin;
                        $afin = $dig =="PM" ? "" : (strtotime($datpm['fin'])>strtotime("12:00")?"12:00":$datpm['fin']);
                        $pini = $pminimin;
                        $pfin = $datpmf['fin'];

                        $pini = ($aini!="" and $afin=="" and $pfin!="")?"12:00":$pini;
                        $afin = ($afin=="" and $pminimin=="" and $pfin!="")?"12:00":$afin;

                        $tsaini = $toh->time_to_sec($aini);
                        $tsafin = $toh->time_to_sec($afin);
                        $tspini = $toh->time_to_sec($pini);
                        $tspfin = $toh->time_to_sec($pfin);

                        //HORAS RECARGO NOCTURNO
                        $tshirn = $toh->time_to_sec($hirn);
                        $tshfrn = $toh->time_to_sec($hfrn);

                        //TOTAL HORAS
                        $totalam = $toh->fnHoras($fec,$aini,$afin);
                        $totalpm = $toh->fnHoras($fec,$pini,$pfin);
                        $tothoras = $totalam + $totalpm;
                        
                        $horario = $dia==0? $lun: ($dia==1?$mar:($dia==2?$mie:($dia==3?$jue:($dia==4?$vie:($dia==5?$sab:$dom)))));
                        $thorario = $horario;
                        $horario = ($fes==1)?0:$horario;

                        //VALIDACION HORA ALIMENTACION
                        $tshan = $toh->time_to_sec($han);
                        $tshanpm = $toh->time_to_sec($hanpm);
                        $ali = 0; $valorali = 0;
                        $icali = "";
                        $txtcali = "$tspfin<$tspini and $pmdig=='PM' and $tshan>=$tspfin) || $tspfin>=$tshan";
                        if(($tspfin<$tspini and $pmdig=="PM" and $tshan>$tspini) || $tspfin>$tshan)
                        {
                            $icali = "<i class='fas fa-utensils'></i>";
                            $ali = 1; $valorali = $vhan;
                        }


                        // VALIDAR SI LA HORA INICIAL PM ES MAYOR A LAS HORA ESTABLECIDAD EN LAS REGLAS Y ESTA HORA FINAL ESTA SOBRE LAS PM 
                        // SIN VALIDAR SI LA HORA FINAL PM ES MENOR A LA HORA ALIMENTACION PM Y HORA FIAL ES MENOR A LA H ORA FINAL RECARGO NOCTURNO
                        $strin="(($tspini>=$tshanpm and $pmdig=='PM') || ($tspfin<$tshanpm and $tspfin<=$tshfrn)) and $tothoras>0";
                        if((($tspini>=$tshanpm and $pmdig=="PM") || ($tspfin<$tshanpm and $tspfin<=$tshfrn)) and $tothoras>0)

                        //if(($tspfin<$tspini and $pmdig=="AM" and $tshanpm>$tspini) || $tspfin>$tshanpm)
                        {   
                            $icali = "<i class='fas fa-utensils'></i>";
                            $ali = 1; $valorali = $vhanpm;
                        }
                        
                        //SUMA O NO SEGUN EL TIPO DE NOVEDAD
                        if($sumnov>=3)
                        {
                            $obs = $nomnov;
                            $S5 = ($sumnov==4)?"SI":"";
                            //$tothoras = ($sumnov==4  and $tothoras<=$thorario)?$thorario:$tothoras;
                            $U5 = ($sumnov==5)?"SI":"";
                            //$tothoras = ($sumnov==5 and $tothoras<=$thorario)?$thorario:$tothoras;
                        }                        
                    
                        $HEDO = 0; // HORAS EXTRAS DIURNAS ORDINA
                        $HDF  = 0;
                        $HEDF = 0;
                        $HENO = 0;
                        $HENF = 0;
                        $RN   = 0;
                        $HDF  = 0;
                        $RD   = 0;
                        
                        //$amfin = (strtotime($datpm['fin'])>strtotime("12:00"))?"12:00":$datpm['fin'];

                        //$pmini = (strtotime($datpm['fin'])>strtotime("12:00"))? $datpm['fin']:$amfin;

                        //$conam = mysqli_query($conectar, "SELECT MIN(joh_inicio) ini FROM tbl_jornada_horas jh JOIN tbl_tipo_novedad t on t.tin_clave_int = jh.tin_clave_int WHERE  t.tin_tipo = 1 and time_format(joh_inicio,'%p')='PM' and jod_clave_int = '".$idfec."'"); //HORAS INI PM 
                        ///$datam = mysqli_fetch_array($conam);
                        //$pmini = $datam['ini'];

                        $sqlam = "SELECT MIN(joh_inicio) ini FROM tbl_jornada_horas jh JOIN tbl_tipo_novedad t on t.tin_clave_int = jh.tin_clave_int WHERE  t.tin_tipo = 1 and time_format(joh_inicio,'%p')='AM' and jod_clave_int = '".$idfec."'"; 
                    
                        // SELECT MIN(jor_inicio), MAX(jor_fin) from tin_tipo = 1 and time_format(jor_ini,'%p')='PM' //HORAS AM
                        // SELECT MIN(jor_inicio), MAX(jor_fin) from tin_tipo = 2' //HORAS DESCANSO

                        //CALCULOS HORAS EXTRAS
                        //$totalam = hourdiff($amini,$amfin,true);
                        //$totalpm = hourdiff($pmini,$pmfin,true);

                        $aminit  = ($aini!="")? date("g:i a", strtotime($aini)): $aini;
                        $amfint  = ($afin!="")? date("g:i a", strtotime($afin)): $afin;
                        $pminit  = ($pini!="")? date("g:i a", strtotime($pini)): $pini;
                        $pmfint  = ($pfin!="")? date("g:i a", strtotime($pfin)): $pfin;
                       
                        // Lista de parametros
                        // fes = G5 || F5
                        $G5 = $fes; $F5 = $fes;
                        // totalhoras =  Q5
                        $Q5 = $tothoras;
                        // horario = R5
                        $R5 = $horario;
                        // hit = M5 =>hora inicial tarde
                        $M5 = $tspini;
                        // hft = O5 =>hora final tarde
                        $O5 = $tspfin;
                        // horasmanana = L5
                        $L5 = $totalam;
                        // HENF = AA5
                        $AA5 = 0;
                        // him = I5 => hora inicial mañana
                        $I5 = $tsaini;
                        // fess = F6 || G6
                        $F6 = $fess; $G6 = $fess;
                        // horastarde = P5
                        $P5 = $totalpm;                       
                        // HEDF = Y5
                        $Y5  = 0;
                        // HNF = AC5
                        $AC5 = 0;
                        // HDF = X5
                        $X5  = 0;
                        // HENO = Z5
                        $Z5  = 0;
                        // RD = AD5
                        $AD5 = 0;

                        // VALIDAR QUE SI DIA ES DOMINGO VERIFICAR SI LA SUMA DE LOS TIEMPOS DE LOS DIAS ANTERIORES DE LA MISMA SEMANA DE ESE DOMINGO SUPERA EN CANTIDAD EL TOTAL DE HORAS ORDINARIAS SEMANA (48 HORAS), LAS HORAS DE DOMINGO SE TOMANA AUTOMATICAMENTE PARA LAS HORAS EXTRAS NO PARA LAS HORAS ORDINARIAS
                        $VD = 0; // SI ES CERO LOS CALCULO SE DISTRIBUYEN NORMAL SI ES UNO SE DISTRIBUYEN  EN LAS HORAS EXTRAS

                        $HENF = $toh->fnExtras(1, $G5, $F5,$Q5,$R5,$M5, $O5, $L5,$AA5, $I5, $F6, $G6, $P5, $T5, $S5,$Y5, $AC5, $U5, $X5, $Z5, $AD5, $tshirn, $tshfrn, $thorario);
                        $AA5  = $HENF;
                        //$HENF   = ($HENF<0)?0:$AA5;

                        $HENO = $toh->fnExtras(2, $G5, $F5,$Q5,$R5,$M5, $O5, $L5,$AA5, $I5, $F6, $G6, $P5, $T5, $S5,$Y5, $AC5, $U5, $X5, $Z5, $AD5, $tshirn, $tshfrn, $thorario);
                        $Z5   = $HENO;

                        //CALCULO EN REVISION
                        $HEDF = $toh->fnExtras(3, $G5, $F5,$Q5,$R5,$M5, $O5, $L5,$AA5, $I5, $F6, $G6, $P5, $T5, $S5,$Y5, $AC5, $U5, $X5, $Z5, $AD5, $tshirn, $tshfrn, $thorario);
                        $Y5   = ($HEDF<0)?0:$HEDF;
                        //$HEDF   = ($HEDF<0)?0:$Y5;

                        $HEDFT =$toh->fnExtrasText(3, $G5, $F5,$Q5,$R5,$M5, $O5, $L5,$AA5, $I5, $F6, $G6, $P5, $T5, $S5,$Y5, $AC5, $U5, $X5, $Z5, $AD5, $tshirn, $tshfrn, $thorario);
                      
                        $HNF  = $toh->fnExtras(4, $G5, $F5,$Q5,$R5,$M5, $O5, $L5,$AA5, $I5, $F6, $G6, $P5, $T5, $S5,$Y5, $AC5, $U5, $X5, $Z5, $AD5, $tshirn, $tshfrn, $thorario);
                        $AC5  = $HNF;

                        $RD   = $toh->fnExtras(5, $G5, $F5,$Q5,$R5,$M5, $O5, $L5,$AA5, $I5, $F6, $G6, $P5, $T5, $S5,$Y5, $AC5, $U5, $X5, $Z5, $AD5, $tshirn, $tshfrn, $thorario);
                        $AD5  = $RD;

                        $RN   = $toh->fnExtras(6, $G5, $F5,$Q5,$R5,$M5, $O5, $L5,$AA5, $I5, $F6, $G6, $P5, $T5, $S5,$Y5, $AC5, $U5, $X5, $Z5, $AD5, $tshirn, $tshfrn, $thorario);

                        $HDF  = $toh->fnExtras(7, $G5, $F5,$Q5,$R5,$M5, $O5, $L5,$AA5, $I5, $F6, $G6, $P5, $T5, $S5,$Y5, $AC5, $U5, $X5, $Z5, $AD5, $tshirn, $tshfrn, $thorario);
                        $X5   = $HDF;
                        //$HDF   = ($HDF<0)?0:$X5;

                        //if($tothoras>0)
                        //{
                            $HEDO = $toh->fnExtras(8, $G5, $F5,$Q5,$R5,$M5, $O5, $L5,$AA5, $I5, $F6, $G6, $P5, $T5, $S5,$Y5, $AC5, $U5, $X5, $Z5, $AD5, $tshirn, $tshfrn, $thorario);
                        //}

                        $HEDOT = "($R5>$Q5?($U5=='SI'?0:$Q5-$R5):$Q5-$R5)-$X5-$Z5-$AA5-$Y5-$AC5-$AD5";

                        $M5 = (int)$M5;
                        $O5 = (int)$O5;
                        //echo "<tr><td colspan='22'>(($G5==1 || $F5==1)?0:(((($L5==0 and $M5>=0.583333333333333 and $O5>0.916666666666667 and $Q5<=$R5)?$M5+($R5/24)-(22/24):0)))*24 +(((($L5==0 and $M5>0.583333333333333 and $O5<0.25)?$M5+(($P5/24)-1)+(0.0833333333333):0))*24)-(($P5>8 and $L5==0 and $M5>0.583333333333333 and $O5<0.25)?($P5-$R5):0)+(($I5<0.25 and $I5<>0 and $Q5<=$R5)?(0.25-$I5):0)*24)+(($I5<(6/24) and $Q5>$R5 and (0.25-$I5)>($Q5/24-$R5/24) and $I5<>0)?(0.25-$I5)-($Q5/24-$R5/24):0)*24</td></tr>";

                        // echo "<tr><td colspan='22'>$HEDO</td></tr>";
                        // echo "<tr><td colspan='22'>".($R5>$Q5?($U5=='SI'?0:$Q5-$R5):$Q5-$R5)-$X5-$Z5-$AA5-$Y5-$AC5-$AD5."</td></tr>";                        
                        $TOTALHEDO+= $HEDO;
                        $TOTALHDF+= $HDF;
                        $TOTALHEDF+= $HEDF;
                        $TOTALHENO+= $HENO;
                        $TOTALHENF+= $HENF;
                        $TOTALRN+= $RN;
                        $TOTALHNF+= $HNF;
                        $TOTALRD+= $RD;

                        $TOTALGHEDO+= $HEDO;
                        $TOTALGHDF+= $HDF;
                        $TOTALGHEDF+= $HEDF;
                        $TOTALGHENO+= $HENO;
                        $TOTALGHENF+= $HENF;
                        $TOTALGRN+= $RN;
                        $TOTALGHNF+= $HNF;
                        $TOTALGRD+= $RD;

                        $HEDO  = str_replace(",",".",$HEDO);
                        $HDF  = str_replace(",",".",$HDF);
                        $HEDF  = str_replace(",",".",$HEDF);
                        $HENO  = str_replace(",",".",$HENO);
                        $HENF  = str_replace(",",".",$HENF);
                        $RN  = str_replace(",",".",$RN);
                        $HNF  = str_replace(",",".",$HNF);
                        $RD = str_replace(",",".",$RD);

                        $tothoras = str_replace(",",".",$tothoras);

                        $veridia = mysqli_query($conectar, "SELECT lid_clave_int FROM tbl_liquidar_dias where usu_clave_int = '".$emp."' and  lid_fecha = '".$fec."'");
                        $numdia = mysqli_num_rows($veridia);
                        $iddia = 0;
                        if($numdia>0)
                        {
                            $datdia = mysqli_fetch_array($veridia);
                            $iddia = $datdia['lid_clave_int'];
                            //$totald = $datfec['jod_total'];
                            $insfec = mysqli_query($conectar, "UPDATE tbl_liquidar_dias SET lid_fecha = '".$fec."', lid_horario = '".$horario."', lid_hi_man = '".$aini."', lid_hf_man = '".$afin."',lid_hi_tar = '".$pini."',lid_hf_tar = '".$pfin."',lid_horas = '".$tothoras."', lid_hedo = '".$HEDO."',lid_hdf = '".$HDF."' , lid_hedf = '".$HEDF."', lid_heno = '".$HENO."', lid_henf = '".$HENF."', lid_rn = '".$RN."', lid_hnf = '".$HNF."', lid_rd = '".$RD."',lid_usu_actualiz = '".$usuario."', lid_fec_actualiz = '".$fecha."', lid_alimentacion = '".$ali."',lid_val_alimentacion = '".$valorali."', lid_compensado = '".$S5."', lid_remunerado = '".$U5."', lid_observacion = '".$obs."' WHERE lid_clave_int = '".$iddia."'");
                            if($insfec>0)
                            {
                                //$iddia = mysqli_insert_id($conectar);
                                //echo "<tr><td colspan='22'>UPDATE tbl_liquidar_dias SET lid_fecha = '".$fec."', lid_horario = '".$horario."', lid_hi_man = '".$aini."', lid_hf_man = '".$afin."',lid_hi_tar = '".$pini."',lid_hf_tar = '".$pfin."',lid_horas = '".$tothoras."', lid_hedo = '".$HEDO."',lid_hdf = '".$HDF."' , lid_hedf = '".$HEDF."', lid_heno = '".$HENO."', lid_henf = '".$HENF."', lid_rn = '".(float)$RN."', lid_hnf = '".$HNF."', lid_rd = '".$RD."',lid_usu_actualiz = '".$usuario."', lid_fec_actualiz = '".$fecha."', lid_alimentacion = '".$ali."',lid_val_alimentacion = '".$valorali."', lid_compensado = '".$S5."', lid_remunerado = '".$U5."', lid_observacion = '".$obs."' WHERE lid_clave_int = '".$iddia."'</td></tr>";
                               // echo "<tr><td colspan='22'>hedo =  ".$HEDOT."</td></tr>";
                            }
                            else
                            {
                                echo "<tr><td colspan='22'>No actualizo dia ".$fec."</td></tr>";
                            }                
                        }
                        else
                        {
                            $insfec = mysqli_query($conectar, "INSERT INTO tbl_liquidar_dias(lid_fecha, lid_horario, lid_hi_man, lid_hf_man,lid_hi_tar,lid_hf_tar,lid_horas,lid_hedo,lid_hdf,lid_hedf, lid_heno,lid_henf,lid_rn,lid_hnf, lid_rd, usu_clave_int,lid_usu_actualiz,lid_fec_actualiz,lid_compensado,lid_remunerado, lid_observacion, tin_clave_int) VALUES('".$fec."','".$horario."','".$aini."','".$afin."','".$pini."','".$pfin."','".$tothoras."','".$HEDO."','".$HDF."','".$HEDF."','".$HENO."','".$HENF."','".$RN."','".$HNF."','".$RD."','".$emp."','".$usuario."','".$fecha."','".$S5."','".$U5."','".$obs."','".$idnov."')");
                            if($insfec>0)
                            {
                                $idfec = mysqli_insert_id($conectar);
                                //echo "<tr><td colspan='22'>RN =  ".$RN."</td></tr>";
                            }
                            else
                            {
                                echo "<tr><td colspan='22'>No inserto dia ".$fec."".mysqli_error($conectar)."</td></tr>";
                            }                        
                        }

                        //JORNADA EXTRA
                        $HE = $HEDO + $HDF + $HEDF + $HENO + $HENF + $RN + $HNF + $RD;
                        $TOTALHE+=$HE;
                        $TOTALGHE+=$HE;

                        $HEDFTE = (((1==1 || 1==1) and 9>8)? 9-8:0)- 1.9999999999999+(((0==1 || 0==1) and (1<>1 and 1<>1) and 1.9999999999999>0)?0.95833333333333*24:0)- ((0<>1 and 0<>1 and (1==1 || 1==1) and 0.95833333333333<(6/24))?0.95833333333333*24:0);
                        ?>
                
                        <tr class="<?php echo $bgfes;?>" id="rowfec_<?php echo $iddia;?>">
                            <td class="p-0"><?php echo  $icali;?></td>
                            <td class="p-0" style="width:200px"><?php echo $fecha;?></td>
                            <td class="text-center p-0 align-middle"><?php echo $aminit;?></td>
                            <td class="text-center p-0 align-middle">a</td>
                            <td class="text-center p-0 align-middle"><?php echo $amfint;?></td>
                            <td class="text-center p-0 align-middle"><?php echo $totalam;?></td>
                            <td class="text-center p-0 align-middle"><?php echo $pminit;?></td>
                            <td class="text-center p-0 align-middle">a</td>
                            <td class="text-center p-0 align-middle"><?php echo $pmfint;?></td>
                            <td class="text-center p-0 align-middle"><?php echo $totalpm;?></td>
                            <td class="text-center p-0 align-middle"><?php echo number_format($tothoras,2,".","");?></td>
                            <td class="text-center p-0 align-middle"><?php echo number_format($horario,2,".","");?></td>
                            <td class="text-center p-0 align-middle"><?PHP echo $S5;?></td>
                            <td class="text-center p-0 align-middle"><?PHP echo $T5;?></td>
                            <td class="text-center p-0 align-middle"><?PHP echo $U5;?></td>
                            <td class="text-center p-0 align-middle"><?PHP echo $obs;?></td>
                            <td class="text-center p-0 align-middle <?php if($HEDO<0){ echo "bg-danger"; } ?>"><?php echo number_format($HEDO,2);?></td>
                            <td class="text-center p-0 align-middle <?php if($HDF<0){ echo "bg-danger"; } ?>"><?php echo number_format($HDF,2);?></td>
                            <td class="text-center p-0 align-middle <?php if($HEDF<0){ echo "bg-danger"; } ?>"><?php echo number_format($HEDF,2);?></td>
                            <td class="text-center p-0 align-middle <?php if($HENO<0){ echo "bg-danger"; } ?>"><?php echo number_format($HENO,2);?></td>
                            <td class="text-center p-0 align-middle <?php if($HENF<0){ echo "bg-danger"; } ?>"><?php echo number_format($HENF,2);?></td>
                            <td class="text-center p-0 align-middle <?php if($RN<0){ echo "bg-danger"; } ?>"><?php echo number_format($RN,2);?></td>
                            <td class="text-center p-0 align-middle <?php if($HNF<0){ echo "bg-danger"; } ?>"><?php echo number_format($HNF,2);?></td>
                            <td class="text-center p-0 align-middle <?php if($RD<0){ echo "bg-danger"; } ?>"><?php echo number_format($RD,2);?></td>
                            <td class="text-center p-0 align-middle <?php if($TE>0){ echo "bg-success"; } ?>"><?php echo number_format($HE,2);?></td>
                            <!-- <td><?php echo $tshirn." - ".$tshfrn;?></td> -->
                            
                        </tr>
                        <?php 
                        
                         
                        if(($numd==15 || $numd==$diafinal) and $d!=0)
                        {
                            ?>
                             <tr>
                                 <td class="bg-primary"></td>
                                <td class="bg-primary">Quincena: <?php echo $nmes." ".$numd; ?></td>
                                <td class="bg-primary"></td>
                                <td class="bg-primary"></td>
                                <td class="bg-primary"></td>
                                <td class="bg-primary"></td>
                                <td class="bg-primary"></td>
                                <td class="bg-primary"></td>
                                <td class="bg-primary"></td>
                                <td class="bg-primary"></td>
                                <td class="bg-primary"></td>
                                <td class="bg-primary"></td>
                                <td class="bg-primary"></td>
                                <td class="bg-primary"></td>
                                <td class="bg-primary"></td>
                                <td class="bg-primary"></td>
                                <td class="text-center p-0 align-middle bg-primary currencyinf "><?php echo number_format($TOTALHEDO,2);?></td>
                                <td class="text-center p-0 align-middle bg-primary currencyinf "><?php echo number_format($TOTALHDF,2);?></td>
                                <td class="text-center p-0 align-middle bg-primary currencyinf "><?php echo number_format($TOTALHEDF,2);?></td>
                                <td class="text-center p-0 align-middle bg-primary currencyinf "><?php echo number_format($TOTALHENO,2);?></td>
                                <td class="text-center p-0 align-middle bg-primary currencyinf "><?php echo number_format($TOTALHENF,2);?></td>
                                <td class="text-center p-0 align-middle bg-primary currencyinf "><?php echo number_format($RN,2);?></td>
                                <td class="text-center p-0 align-middle bg-primary currencyinf "><?php echo number_format($TOTALHNF,2);?></td>
                                <td class="text-center p-0 align-middle bg-primary currencyinf "><?php echo number_format($TOTALRD,2);?></td>                 
                                <td class="text-center p-0 align-middle bg-primary currencyinf "><?php echo number_format($TOTALHE,2);?></td>                 
                            </tr>
                            <?php
                             $TOTALHE = 0;
                             $TOTALHEDO = 0;
                             $TOTALHDF  = 0;
                             $TOTALHEDF = 0;
                             $TOTALHENO = 0;
                             $TOTALHENF = 0;
                             $TOTALRN   = 0;
                             $TOTALHNF  = 0;
                             $TOTALRD   = 0;
                        }
                    } 
                }
                ?>
            </tbody>
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
                    <th class="text-center p-0 align-middle bg-secondary currencyinf "><?php echo number_format($TOTALGHEDO,2);?></th>
                    <th class="text-center p-0 align-middle bg-secondary currencyinf "><?php echo number_format($TOTALGHDF,2);?></th>
                    <th class="text-center p-0 align-middle bg-secondary currencyinf "><?php echo number_format($TOTALGHEDF,2);?></th>
                    <th class="text-center p-0 align-middle bg-secondary currencyinf "><?php echo number_format($TOTALGHENO,2);?></th>
                    <th class="text-center p-0 align-middle bg-secondary currencyinf "><?php echo number_format($TOTALGHENF,2);?></th>
                    <th class="text-center p-0 align-middle bg-secondary currencyinf "><?php echo number_format($TOTALGRN,2);?></th>
                    <th class="text-center p-0 align-middle bg-secondary currencyinf "><?php echo number_format($TOTALGHNF,2);?></th>
                    <th class="text-center p-0 align-middle bg-secondary currencyinf "><?php echo number_format($TOTALGRD,2);?></th>
                    <th class="text-center p-0 align-middle bg-secondary currencyinf "><?php echo number_format($TOTALGHE,2);?></th>                              
                </tr>
            </tfoot>
        </table>
        <!-- <script src="jsdatatable/jornadas/jsliquidar.js?<?php echo time();?>"></script> -->
       
        <?php
    }
    else
    if($tip==2 and $obr>0)
    {
        $condat = mysqli_query($conectar, "SELECT hor_clave_int FROM tbl_usuarios WHERE usu_clave_int = '".$emp."' LIMIT 1");
        $dat = mysqli_fetch_array($condat);
        $hor = $dat['hor_clave_int'];

        $reghor = new General();
        $datr = $reghor->editObra($obr);
        //$datr = $reghor->editHorario($hor);
        //$nom = $datr['hor_nombre'];
        /*$lun = $datr['hor_1'];
        $mar = $datr['hor_2'];
        $mie = $datr['hor_3'];
        $jue = $datr['hor_4'];
        $vie = $datr['hor_5'];
        $sab = $datr['hor_6'];
        $dom = $datr['hor_7'];*/

        $vroperario = $datr['obr_vr_operador'];
        $vrsenalero = $datr['obr_vr_senalero'];
        $vrmaquina  = $datr['obr_vr_maquina'];
        $hrmes      = $datr['obr_hr_mes'];
        $hrsemana   = $datr['obr_hr_semana'];
        $lun        = $datr['obr_lunes'];
        $mar        = $datr['obr_martes'];
        $mie        = $datr['obr_miercoles'];
        $jue        = $datr['obr_jueves'];
        $vie        = $datr['obr_viernes'];
        $sab        = $datr['obr_sabado'];
        $dom        = $datr['obr_domingo'];

        $arrayhedo = array();
        $arrayhdf  = array();
        $arrayhedf = array();
        $arrayheno = array();
        $arrayhenf = array();
        $arrayrn   = array();
        $arrayhnf  = array();
        $arrayrd   = array();

        $toh = new General();
        //echo $nom;
        //date_format(jod_fecha,'%Y') = '".$ano."' and date_format(jod_fecha,'%m') = '".$mes."'
        $condias = mysqli_query($conectar, "SELECT  jd.jod_clave_int id, jd.jod_fecha fec, WEEKDAY(jd.jod_fecha) dia,jd.jod_semana sem,jd.jod_estado est,jd.jod_observacion obs,totalhorasDia(jd.jod_clave_int) tothoras,diaHabil(jod_fecha) fes,diaHabil(adddate(jod_fecha, interval 1 day)) fess, tn.tin_clave_int idnov, tn.tin_suma sumnov, tn.tin_nombre nomnov FROM tbl_jornada_dias jd join tbl_jornada j ON j.jor_clave_int = jd.jor_clave_int LEFT OUTER JOIN tbl_tipo_novedad tn on tn.tin_clave_int = jd.tin_clave_int WHERE jd.jod_fecha between '".$ini."' and '".$fin."'  and jd.usu_clave_int = '".$emp."' and j.jor_estado = 1 order by jod_fecha  asc");
        $numdias = mysqli_num_rows($condias);
        ?>
        <table class="table table-bordered table-striped" style="width:100%" id="tbLiquidar">
            <thead>
                <tr>
                    <th class="dt-head-center bg-terracota align-middle" rowspan="2" colspan="2">Alimentación</th>
                    <th class="dt-head-center bg-terracota align-middle" style="width:200px">Fecha</th>
                    <th class="dt-head-center bg-terracota align-middle" colspan="3">Mañana</th>
                    <th class="dt-head-center bg-terracota align-middle">Total Horas</th>
                    <th class="dt-head-center bg-terracota align-middle" colspan="3">Tarde</th>
                    <th class="dt-head-center bg-terracota align-middle">Total Horas</th>
                    <th class="dt-head-center bg-terracota">Horas Dia</th>
                    <th rowspan="2" class="dt-head-center bg-terracota align-middle">Horario</th>
                    <th rowspan="2" class="dt-head-center bg-terracota align-middle">Compensado</th>
                    <th rowspan="2" class="dt-head-center bg-terracota align-middle">Pagado y compensado</th>
                    <th rowspan="2" class="dt-head-center bg-terracota align-middle">Permiso Remunerado</th>
                    <th rowspan="2" class="dt-head-center bg-terracota align-middle">Observaciones</th>
                    <th class="dt-head-center align-middle">HEDO</th>
                    <th class="dt-head-center align-middle">HDF</th>
                    <th class="dt-head-center align-middle">HEDF</th>
                    <th class="dt-head-center align-middle">HENO</th>
                    <th class="dt-head-center align-middle">HENF</th>
                    <th class="dt-head-center align-middle">RN</th>
                    <th class="dt-head-center align-middle">HNF</th>
                    <th class="dt-head-center align-middle">RD</th>

                </tr>
                <tr>
                    <th class="dt-head-center align-middle">Dia/Mes/Año</th>
                    <th class="dt-head-center align-middle">Desde</th>
                    <th class="dt-head-center align-middle"></th>
                    <th class="dt-head-center align-middle">Hasta</th>
                    <th class="dt-head-center align-middle">#</th>
                    <th class="dt-head-center align-middle">Desde</th>
                    <th class="dt-head-center align-middle"></th>
                    <th class="dt-head-center align-middle">Hasta</th>
                    <th class="dt-head-center align-middle">#</th>
                    <th class="dt-head-center align-middle">#</th>
                    <th class="dt-head-center align-middle">002</th>
                    <th class="dt-head-center align-middle">014</th>
                    <th class="dt-head-center align-middle">004</th>
                    <th class="dt-head-center align-middle">003</th>
                    <th class="dt-head-center align-middle">005</th>
                    <th class="dt-head-center align-middle">006</th>
                    <th class="dt-head-center align-middle">008</th>
                    <th class="dt-head-center align-middle">013</th>
                </tr>
            </thead>
            <tbody>
                <?php
                //TOTALES PARCIALES 
                $TOTALHEDO = 0;
                $TOTALHDF  = 0;
                $TOTALHEDF = 0;
                $TOTALHENO = 0;
                $TOTALHENF = 0;
                $TOTALRN   = 0;
                $TOTALHNF  = 0;
                $TOTALRD   = 0;

                //TOTALES GENERAL 
                $TOTALGHEDO = 0;
                $TOTALGHDF  = 0;
                $TOTALGHEDF = 0;
                $TOTALGHENO = 0;
                $TOTALGHENF = 0;
                $TOTALGRN   = 0;
                $TOTALGHNF  = 0;
                $TOTALGRD   = 0;
                if($numdias>0)
                {
                    
                    for($d=0;$d<$numdias;$d++)
                    {
                        $datd = mysqli_fetch_array($condias);                
                        $idfec = $datd['id'];
                        $dia  = $datd['dia'];
                        $fec  = $datd['fec'];
                        $tothoras = $datd['tothoras'];
                        $nmes = date("F", strtotime($fec));
                        $ndia = date("l", strtotime($fec));
                        $numd = date("d", strtotime($fec));
                        $anod = date("Y", strtotime($fec));
                        $fecha = $ndia.", ". $numd." de ".$nmes." de ".$anod;
                        $fes = $datd['fes'];
                        $fess = $datd['fess'];//FESTIVO SIGUIENTE
                        $bgfes = ($fes==1)?"bg-secondary":""; //dia festivo
                        $titfes = ($fes==1)?"Dia Festivo":"";

                        //datos para calculo de compensado y permisos remunerados
                        $idnov = $datd['idnov'];
                        $sumnov = $datd['sumnov'];
                        $nomnov = $datd['nomnov'];
                        //DATOS QUE DEFINE SI COMPENSADO SI REMUNERADO
                        // compensado = S5 = >Si o Ambos o vacion
                        $S5  = "";

                        // pagado y compensado = T5 => "SI" o vacio
                        $T5 = "";//

                        // PERMISO REMUNERADO = U5=> SI
                        $U5  = "";

                        $obs = "";
                        // if($sumnov>=3)
                        // {
                        //     $obs = $nomnov;
                        //     $S5 = ($sumnov==4)?"SI":"";
                        //     $U5 = ($sumnov==5)?"SI":"";
                        // }

                        //DIA INICIAL Y FINAL
                        $diainicial = $numd;
                        $diafinal = date("t",strtotime($fec));

                        //VERIFICAR SI LA HORA INICIAL del dia es pm o am en caso de que sea pm no hay horari am
                        $conam = mysqli_query($conectar, "SELECT joh_clave_int,joh_inicio ini,TIME_FORMAT(joh_inicio,'%p') dig FROM tbl_jornada_horas jh JOIN tbl_tipo_novedad t on t.tin_clave_int = jh.tin_clave_int WHERE  t.tin_tipo = 1 and jod_clave_int = '".$idfec."' and jh.obr_clave_int = '".$obr."' order by joh_clave_int ASC LIMIT 1"); //HORAS PM and time_format(joh_fin,'%p')='PM'
                        $datam = mysqli_fetch_array($conam);
                        $amini = $datam['ini'];
                        $dig   = $datam['dig']; 

                        //MINIMO AM SI EXISTIERA
                        $conam = mysqli_query($conectar, "SELECT MIN(joh_inicio) ini FROM tbl_jornada_horas jh JOIN tbl_tipo_novedad t on t.tin_clave_int = jh.tin_clave_int WHERE  t.tin_tipo = 1 and time_format(joh_inicio,'%p')='AM' and jod_clave_int = '".$idfec."' and jh.obr_clave_int = '".$obr."'"); //HORAS AM  
                        $datam = mysqli_fetch_array($conam);
                        $aminimin = $datam['ini'];

                        $conpm = mysqli_query($conectar, "SELECT MIN(joh_inicio) fin,TIME_FORMAT(joh_inicio,'%p') dig  FROM tbl_jornada_horas jh JOIN tbl_tipo_novedad t on t.tin_clave_int = jh.tin_clave_int WHERE  t.tin_tipo = 1 and time_format(joh_inicio,'%p')='PM' and jod_clave_int = '".$idfec."' and jh.obr_clave_int = '".$obr."'"); //HORAS AM  
                        $datpm = mysqli_fetch_array($conpm);
                        $pminimin=  $datpm['fin'];
                        $pmdig = $datpm['dig'];

                        //HORA FINAL REGISTRADA
                        $conpmf = mysqli_query($conectar, "SELECT joh_clave_int,joh_fin fin FROM tbl_jornada_horas jh JOIN tbl_tipo_novedad t on t.tin_clave_int = jh.tin_clave_int WHERE  t.tin_tipo = 1 and jod_clave_int = '".$idfec."' and jh.obr_clave_int = '".$obr."' order by joh_clave_int DESC LIMIT 1"); //HORAS PM and time_format(joh_fin,'%p')='PM'
                        $datpmf = mysqli_fetch_array($conpmf);                    

                        $aini  = $dig =="PM" ? "" : $aminimin;
                        $afin = $dig =="PM" ? "" : (strtotime($datpm['fin'])>strtotime("12:00")?"12:00":$datpm['fin']);
                        $pini = $pminimin;
                        $pfin = $datpmf['fin'];

                        $pini = ($aini!="" and $afin=="" and $pfin!="")?"12:00":$pini;
                        $afin = ($afin=="" and $pminimin=="" and $pfin!="")?"12:00":$afin;

                        $tsaini = $toh->time_to_sec($aini);
                        $tsafin = $toh->time_to_sec($afin);
                        $tspini = $toh->time_to_sec($pini);
                        $tspfin = $toh->time_to_sec($pfin);

                        //HORAS RECARGO NOCTURNO
                        $tshirn = $toh->time_to_sec($hirn);
                        $tshfrn = $toh->time_to_sec($hfrn);

                        //TOTAL HORAS
                        $totalam = $toh->fnHoras($fec,$aini,$afin);
                        $totalpm = $toh->fnHoras($fec,$pini,$pfin);
                        $tothoras = $totalam + $totalpm;  
                        
                        $horario = $dia==0? $lun: ($dia==1?$mar:($dia==2?$mie:($dia==3?$jue:($dia==4?$vie:($dia==5?$sab:$dom)))));
                        $thorario = $horario;
                        $horario = ($fes==1)?0:$horario;                        

                         //SUMA O NO SEGUN EL TIPO DE NOVEDAD
                         if($sumnov>=3)
                         {
                             $obs = $nomnov;
                             $S5 = ($sumnov==4)?"SI":"";
                             $tothoras = ($sumnov==4 and $tothoras<=$thorario)?$thorario:$tothoras;
                             $U5 = ($sumnov==5)?"SI":"";
                             $tothoras = ($sumnov==5 and $tothoras<=$thorario)?$thorario:$tothoras;
                         }

                         $tothorasmaquina = ($tothoras>0 and $tothoras<$thorario)?$thorario: $tothoras;

                        //VALIDACION HORA ALIMENTACION
                        $tshao = $toh->time_to_sec($hao);
                        $tshanpm = $toh->time_to_sec($hanpm);
                        $ali = 0; $valorali = 0;
                        $icali = "";
                        $txtcali = "$tspfin<$tspini and $pmdig=='PM' and $tshao>=$tspfin) || $tspfin>=$tshao";
                        if(($tspfin<$tspini and $pmdig=="PM" and $tshao>$tspini) || $tspfin>$tshao)
                        {
                            $icali = "<i class='fas fa-utensils'></i>";
                            $ali = 1; $valorali = $vhao;
                        }
                        /*if((($tspini>=$tshanpm and $pmdig=="PM") || ($tspfin<$tshanpm and $tspfin<=$tshfrn)) and $tothoras>0)

                        //if(($tspfin<$tspini and $pmdig=="AM" and $tshanpm>$tspini) || $tspfin>$tshanpm)
                        {   
                            $icali = "<i class='fas fa-utensils'></i>";
                            $ali = 1; $valorali = $vhanpm;
                        }*/
                    
                        $HEDO = 0; // HORAS EXTRAS DIURNAS ORDINA
                        $HDF  = 0;
                        $HEDF = 0;
                        $HENO = 0;
                        $HENF = 0;
                        $RN   = 0;
                        $HDF  = 0;
                        $RD   = 0;
                        
                        //$amfin = (strtotime($datpm['fin'])>strtotime("12:00"))?"12:00":$datpm['fin'];
                        //$pmini = (strtotime($datpm['fin'])>strtotime("12:00"))? $datpm['fin']:$amfin;

                        //$conam = mysqli_query($conectar, "SELECT MIN(joh_inicio) ini FROM tbl_jornada_horas jh JOIN tbl_tipo_novedad t on t.tin_clave_int = jh.tin_clave_int WHERE  t.tin_tipo = 1 and time_format(joh_inicio,'%p')='PM' and jod_clave_int = '".$idfec."'"); //HORAS INI PM 
                        ///$datam = mysqli_fetch_array($conam);
                        //$pmini = $datam['ini'];
                        $sqlam = "SELECT MIN(joh_inicio) ini FROM tbl_jornada_horas jh JOIN tbl_tipo_novedad t on t.tin_clave_int = jh.tin_clave_int WHERE  t.tin_tipo = 1 and time_format(joh_inicio,'%p')='AM' and jod_clave_int = '".$idfec."' and  jh.obr_clave_int = '".$obr."'"; 
                    
                        // SELECT MIN(jor_inicio), MAX(jor_fin) from tin_tipo = 1 and time_format(jor_ini,'%p')='PM' //HORAS AM
                        // SELECT MIN(jor_inicio), MAX(jor_fin) from tin_tipo = 2' //HORAS DESCANSO

                        //CALCULOS HORAS EXTRAS                        
                        //$totalam = hourdiff($amini,$amfin,true);
                        //$totalpm = hourdiff($pmini,$pmfin,true);                                      

                        $aminit  = ($aini!="")? date("g:i a", strtotime($aini)): $aini;
                        $amfint  = ($afin!="")? date("g:i a", strtotime($afin)): $afin;
                        $pminit  = ($pini!="")? date("g:i a", strtotime($pini)): $pini;
                        $pmfint  = ($pfin!="")? date("g:i a", strtotime($pfin)): $pfin;                       

                        // Lista de parametros
                        // fes = G5 || F5
                        $G5 = $fes; $F5 = $fes;
                        // totalhoras =  Q5
                        $Q5 = $tothoras;
                        // horario = R5
                        $R5 = $horario;
                        // hit = M5 =>hora inicial tarde
                        $M5 = $tspini;
                        // hft = O5 =>hora final tarde
                        $O5 = $tspfin;
                        // horasmanana = L5
                        $L5 = $totalam;
                        // HENF = AA5
                        $AA5 = 0;
                        // him = I5 => hora inicial mañana
                        $I5 = $tsaini;
                        // fess = F6 || G6
                        $F6 = $fess; $G6 = $fess;
                        // horastarde = P5
                        $P5 = $totalpm;
                       
                        // HEDF = Y5
                        $Y5  = 0;
                        // HNF = AC5
                        $AC5 = 0;
                      
                        // HDF = X5
                        $X5  = 0;
                        // HENO = Z5
                        $Z5  = 0;
                        // RD = AD5
                        $AD5 = 0;

                        $HENF = $toh->fnExtras(1, $G5, $F5,$Q5,$R5,$M5, $O5, $L5,$AA5, $I5, $F6, $G6, $P5, $T5, $S5,$Y5, $AC5, $U5, $X5, $Z5, $AD5, $tshirn, $tshfrn, $thorario);
                        $AA5  = $HENF;
                        // $HENF   = ($HENF<0)?0:$AA5;

                        $HENO = $toh->fnExtras(2, $G5, $F5,$Q5,$R5,$M5, $O5, $L5,$AA5, $I5, $F6, $G6, $P5, $T5, $S5,$Y5, $AC5, $U5, $X5, $Z5, $AD5, $tshirn, $tshfrn, $thorario);
                        $Z5   = $HENO;

                        $HEDF = $toh->fnExtras(3, $G5, $F5,$Q5,$R5,$M5, $O5, $L5,$AA5, $I5, $F6, $G6, $P5, $T5, $S5,$Y5, $AC5, $U5, $X5, $Z5, $AD5, $tshirn, $tshfrn, $thorario);
                        $Y5   = $HEDF;
                        $HEDF   = ($HEDF<0)?0:$Y5;


                        $HNF  = $toh->fnExtras(4, $G5, $F5,$Q5,$R5,$M5, $O5, $L5,$AA5, $I5, $F6, $G6, $P5, $T5, $S5,$Y5, $AC5, $U5, $X5, $Z5, $AD5, $tshirn, $tshfrn, $thorario);
                        $AC5  = $HNF;

                        $RD   = $toh->fnExtras(5, $G5, $F5,$Q5,$R5,$M5, $O5, $L5,$AA5, $I5, $F6, $G6, $P5, $T5, $S5,$Y5, $AC5, $U5, $X5, $Z5, $AD5, $tshirn, $tshfrn, $thorario);
                        $AD5  = $RD;

                        $RN   = $toh->fnExtras(6, $G5, $F5,$Q5,$R5,$M5, $O5, $L5,$AA5, $I5, $F6, $G6, $P5, $T5, $S5,$Y5, $AC5, $U5, $X5, $Z5, $AD5, $tshirn, $tshfrn, $thorario);

                        $HDF  = $toh->fnExtras(7, $G5, $F5,$Q5,$R5,$M5, $O5, $L5,$AA5, $I5, $F6, $G6, $P5, $T5, $S5,$Y5, $AC5, $U5, $X5, $Z5, $AD5, $tshirn, $tshfrn, $thorario);
                        $X5   = $HDF;

                        if($tothoras>0)
                        {
                            $HEDO = $toh->fnExtras(8, $G5, $F5,$Q5,$R5,$M5, $O5, $L5,$AA5, $I5, $F6, $G6, $P5, $T5, $S5,$Y5, $AC5, $U5, $X5, $Z5, $AD5, $tshirn, $tshfrn, $thorario);
                        }
                        $M5 = (int)$M5;
                        $O5 = (int)$O5;
                        //echo "<tr><td colspan='22'>(($G5==1 || $F5==1)?0:(((($L5==0 and $M5>=0.583333333333333 and $O5>0.916666666666667 and $Q5<=$R5)?$M5+($R5/24)-(22/24):0)))*24 +(((($L5==0 and $M5>0.583333333333333 and $O5<0.25)?$M5+(($P5/24)-1)+(0.0833333333333):0))*24)-(($P5>8 and $L5==0 and $M5>0.583333333333333 and $O5<0.25)?($P5-$R5):0)+(($I5<0.25 and $I5<>0 and $Q5<=$R5)?(0.25-$I5):0)*24)+(($I5<(6/24) and $Q5>$R5 and (0.25-$I5)>($Q5/24-$R5/24) and $I5<>0)?(0.25-$I5)-($Q5/24-$R5/24):0)*24</td></tr>";

                        // echo "<tr><td colspan='22'>$HEDO</td></tr>";
                        // echo "<tr><td colspan='22'>".($R5>$Q5?($U5=='SI'?0:$Q5-$R5):$Q5-$R5)-$X5-$Z5-$AA5-$Y5-$AC5-$AD5."</td></tr>";
                        $TOTALHEDO+= $HEDO;
                        $TOTALHDF+= $HDF;
                        $TOTALHEDF+= $HEDF;
                        $TOTALHENO+= $HENO;
                        $TOTALHENF+= $HENF;
                        $TOTALRN+= $RN;
                        $TOTALHNF+= $HNF;
                        $TOTALRD+= $RD;

                        $TOTALGHEDO+= $HEDO;
                        $TOTALGHDF+= $HDF;
                        $TOTALGHEDF+= $HEDF;
                        $TOTALGHENO+= $HENO;
                        $TOTALGHENF+= $HENF;
                        $TOTALGRN+= $RN;
                        $TOTALGHNF+= $HNF;
                        $TOTALGRD+= $RD;

                        $HEDO  = str_replace(",",".",$HEDO);
                        $HDF  = str_replace(",",".",$HDF);
                        $HEDF  = str_replace(",",".",$HEDF);
                        $HENO  = str_replace(",",".",$HENO);
                        $HENF  = str_replace(",",".",$HENF);
                        $RN  = str_replace(",",".",$RN);
                        $HNF  = str_replace(",",".",$HNF);
                        $RD = str_replace(",",".",$RD);

                        $tothoras = str_replace(",",".",$tothoras);
                        $tothorasmaquina = str_replace(",",".",$tothorasmaquina);

                        $veridia = mysqli_query($conectar, "SELECT lid_clave_int, lid_estado, lid_procede FROM tbl_liquidar_dias_obra where usu_clave_int = '".$emp."' and  lid_fecha = '".$fec."' and obr_clave_int = '".$obr."'");
                        $numdia = mysqli_num_rows($veridia);
                        $iddia = 0;
                        if($numdia>0)
                        {
                            $datdia = mysqli_fetch_array($veridia);
                            $iddia = $datdia['lid_clave_int'];
                            $pro = $datdia['lid_procede'];
                            //$totald = $datfec['jod_total'];
                            $insfec = mysqli_query($conectar, "UPDATE tbl_liquidar_dias_obra SET lid_fecha = '".$fec."', lid_horario = '".$horario."', lid_hi_man = '".$aini."', lid_hf_man = '".$afin."',lid_hi_tar = '".$pini."',lid_hf_tar = '".$pfin."',lid_horas = '".$tothoras."', lid_hedo = '".$HEDO."',lid_hdf = '".$HDF."' , lid_hedf = '".$HEDF."', lid_heno = '".$HENO."', lid_henf = '".$HENF."', lid_rn = '".$RN."', lid_hnf = '".$HNF."', lid_rd = '".$RD."',lid_usu_actualiz = '".$usuario."', lid_fec_actualiz = '".$fecha."', lid_alimentacion = '".$ali."',lid_val_alimentacion = '".$valorali."', lid_compensado = '".$S5."', lid_remunerado = '".$U5."', lid_observacion = '".$obs."', tin_clave_int = '".$idnov."', lid_horas_maquina = '".$tothorasmaquina."' WHERE lid_clave_int = '".$iddia."'");
                            if($insfec>0)
                            {
                                //$iddia = mysqli_insert_id($conectar);
                            }
                            else
                            {
                                echo "<tr><td colspan='22'>No actualizo dia ".$fec."</td></tr>";
                            }                
                        }
                        else
                        {
                            $pro = 0;
                            $insfec = mysqli_query($conectar, "INSERT INTO tbl_liquidar_dias_obra(lid_fecha, lid_horario, lid_hi_man, lid_hf_man,lid_hi_tar,lid_hf_tar,lid_horas,lid_hedo,lid_hdf,lid_hedf, lid_heno,lid_henf,lid_rn,lid_hnf, lid_rd, usu_clave_int,lid_usu_actualiz,lid_fec_actualiz,obr_clave_int, lid_compensado, lid_remunerado, lid_observacion, tin_clave_int, lid_horas_maquina) VALUES('".$fec."','".$horario."','".$aini."','".$afin."','".$pini."','".$pfin."','".$tothoras."','".$HEDO."','".$HDF."','".$HEDF."','".$HENO."','".$HENF."','".$RN."','".$HNF."','".$RD."','".$emp."','".$usuario."','".$fecha."','".$obr."','".$S5."','".$U5."','".$obs."','".$idnov."', '".$tothorasmaquina."')");
                            if($insfec>0)
                            {
                                $iddia = mysqli_insert_id($conectar);
                            }
                            else
                            {
                                echo "<tr><td colspan='22'>No inserto dia ".$fec."".mysqli_error($conectar)."</td></tr>";
                            }                        
                        }
                        ?>
                
                        <tr class="<?php echo $bgfes;?>" id="rowfec_<?php echo $iddia;?>">
                            <td class="p-0"><?php echo $icali;?></td>
                            <td class="p-0">
                            <div class="btn-group btn-group-toggle btn-group-sm <?php if($ali!=1){ echo "hide"; }?>" data-toggle="buttons">
                                <label class="btn btn-success <?php if($pro==1){ echo "active"; }?>">
                                <input type="radio" onchange="CRUDLIQUIDAR('UPDATEPROCEDE','<?PHP echo $iddia;?>')" name="radprocede<?php echo $iddia;?>" id="radestado1_<?php echo $iddia; ?>"  <?php if($pro==1){ echo "checked"; }?> value="1">  Si
                                </label>
                                <label class="btn btn-danger <?php if($pro!=1){ echo "active"; }?>">
                                <input type="radio" onchange="CRUDLIQUIDAR('UPDATEPROCEDE','<?PHP echo $iddia;?>')" name="radprocede<?php echo $iddia;?>"  id="radestado2_<?php echo $iddia;?>"  value="0" <?php if($pro!=1){ echo "checked"; }?>> No
                                </label>
                            </div>
                            </td>
                            <td class="p-0" style="width:200px"><?php echo $fecha;?></td>
                            <td class="text-center p-0 align-middle"><?php echo $aminit;?></td>
                            <td class="text-center p-0 align-middle">a</td>
                            <td class="text-center p-0 align-middle"><?php echo $amfint;?></td>
                            <td class="text-center p-0 align-middle"><?php echo $totalam;?></td>
                            <td class="text-center p-0 align-middle"><?php echo $pminit;?></td>
                            <td class="text-center p-0 align-middle">a</td>
                            <td class="text-center p-0 align-middle"><?php echo $pmfint;?></td>
                            <td class="text-center p-0 align-middle"><?php echo $totalpm;?></td>
                            <td class="text-center p-0 align-middle"><?php echo number_format($tothoras,2,".","");?></td>
                            <td class="text-center p-0 align-middle"><?php echo number_format($horario,2,".","");?></td>
                            <td class="text-center p-0 align-middle"><?PHP echo $S5;?></td>
                            <td class="text-center p-0 align-middle"><?PHP echo $T5;?></td>
                            <td class="text-center p-0 align-middle"><?PHP echo $U5;?></td>
                            <td class="text-center p-0 align-middle">
                               <?php echo $obs;?>
                            </td>
                            <td class="text-center p-0 align-middle <?php if($HEDO<0){ echo "bg-danger"; } ?>"><?php echo number_format($HEDO,2);?></td>
                            <td class="text-center p-0 align-middle <?php if($HDF<0){ echo "bg-danger"; } ?>"><?php echo number_format($HDF,2);?></td>
                            <td class="text-center p-0 align-middle <?php if($HEDF<0){ echo "bg-danger"; } ?>"><?php echo number_format($HEDF,2);?></td>
                            <td class="text-center p-0 align-middle <?php if($HENO<0){ echo "bg-danger"; } ?>"><?php echo number_format($HENO,2);?></td>
                            <td class="text-center p-0 align-middle <?php if($HENF<0){ echo "bg-danger"; } ?>"><?php echo number_format($HENF,2);?></td>
                            <td class="text-center p-0 align-middle <?php if($RN<0){ echo "bg-danger"; } ?>"><?php echo number_format($RN,2);?></td>
                            <td class="text-center p-0 align-middle <?php if($HNF<0){ echo "bg-danger"; } ?>"><?php echo number_format($HNF,2);?></td>
                            <td class="text-center p-0 align-middle <?php if($RD<0){ echo "bg-danger"; } ?>"><?php echo number_format($RD,2);?></td>
                        </tr>
                        <?php 
                        // $TOTALHEDO+= $HEDO;
                        // $TOTALHDF+= $HDF;
                        // $TOTALHEDF+= $HEDF;
                        // $TOTALHENO+= $HENO;
                        // $TOTALHENF+= $HENF;
                        // $TOTALRN+= $RN;
                        // $TOTALHNF+= $HNF;
                        // $TOTALRD+= $RD;

                        // $TOTALGHEDO+= $HEDO;
                        // $TOTALGHDF+= $HDF;
                        // $TOTALGHEDF+= $HEDF;
                        // $TOTALGHENO+= $HENO;
                        // $TOTALGHENF+= $HENF;
                        // $TOTALGRN+= $RN;
                        // $TOTALGHNF+= $HNF;
                        // $TOTALGRD+= $RD;
                        
                        if(($numd==15 || $numd==$diafinal) and $d!=0)
                        {
                            ?>
                             <tr>
                                <td class="bg-primary"></td>
                                <td class="bg-primary"></td>
                                <td class="bg-primary"></td>
                                <td class="bg-primary">Quincena: <?php echo $nmes." ".$numd; ?></td>
                                <td class="bg-primary"></td>
                                <td class="bg-primary"></td>
                                <td class="bg-primary"></td>
                                <td class="bg-primary"></td>
                                <td class="bg-primary"></td>
                                <td class="bg-primary"></td>
                                <td class="bg-primary"></td>
                                <td class="bg-primary"></td>
                                <td class="bg-primary"></td>
                                <td class="bg-primary"></td>
                                <td class="bg-primary"></td>
                                <td class="bg-primary"></td>
                                <td class="bg-primary"></td>
                                <td class="text-center p-0 align-middle bg-primary currencyinf "><?php echo number_format($TOTALHEDO,2);?></td>
                                <td class="text-center p-0 align-middle bg-primary currencyinf "><?php echo number_format($TOTALHDF,2);?></td>
                                <td class="text-center p-0 align-middle bg-primary currencyinf "><?php echo number_format($TOTALHEDF,2);?></td>
                                <td class="text-center p-0 align-middle bg-primary currencyinf "><?php echo number_format($TOTALHENO,2);?></td>
                                <td class="text-center p-0 align-middle bg-primary currencyinf "><?php echo number_format($TOTALHENF,2);?></td>
                                <td class="text-center p-0 align-middle bg-primary currencyinf "><?php echo number_format($RN,2);?></td>
                                <td class="text-center p-0 align-middle bg-primary currencyinf "><?php echo number_format($TOTALHNF,2);?></td>
                                <td class="text-center p-0 align-middle bg-primary currencyinf "><?php echo number_format($TOTALRD,2);?></td>                      
                            </tr>
                            <?php
                            $TOTALHEDO = 0;
                            $TOTALHDF  = 0;
                            $TOTALHEDF = 0;
                            $TOTALHENO = 0;
                            $TOTALHENF = 0;
                            $TOTALRN   = 0;
                            $TOTALHNF  = 0;
                            $TOTALRD   = 0;
                        }
                    } 
                }
                ?>
            </tbody>
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
                    <th class="text-center p-0 align-middle bg-secondary currencyinf "><?php echo number_format($TOTALGHEDO,2);?></th>
                    <th class="text-center p-0 align-middle bg-secondary currencyinf "><?php echo number_format($TOTALGHDF,2);?></th>
                    <th class="text-center p-0 align-middle bg-secondary currencyinf "><?php echo number_format($TOTALGHEDF,2);?></th>
                    <th class="text-center p-0 align-middle bg-secondary currencyinf "><?php echo number_format($TOTALGHENO,2);?></th>
                    <th class="text-center p-0 align-middle bg-secondary currencyinf "><?php echo number_format($TOTALGHENF,2);?></th>
                    <th class="text-center p-0 align-middle bg-secondary currencyinf "><?php echo number_format($TOTALGRN,2);?></th>
                    <th class="text-center p-0 align-middle bg-secondary currencyinf "><?php echo number_format($TOTALGHNF,2);?></th>
                    <th class="text-center p-0 align-middle bg-secondary currencyinf "><?php echo number_format($TOTALGRD,2);?></th>              
                </tr>
            </tfoot>
        </table>
        <!-- <script src="jsdatatable/jornadas/jsliquidar.js?<?php echo time();?>"></script> -->
        <script>

        </script>
        <?php
    }
    else if($tip==3)//RESUMEN DE LA OBRA
    {
        //and date_format(jd.jod_fecha,'%Y') = '".$ano."' and date_format(jd.jod_fecha,'%m') = '".$mes."'
        $contoth = mysqli_query($conectar,"SELECT SUM(TIME_TO_SEC(TIMEDIFF(joh_fin, joh_inicio))/3600) as totalhoras, o.obr_clave_int ido, o.obr_nombre nom, obr_cencos cen,obr_hr_mes hrmes, o.obr_vr_maquina vm FROM tbl_jornada j join tbl_jornada_dias jd on j.jor_clave_int = jd.jor_clave_int JOIN tbl_jornada_horas jh ON jh.jod_clave_int = jd.jod_clave_int JOIN tbl_tipo_novedad tn on tn.tin_clave_int = jh.tin_clave_int join  tbl_obras o ON o.obr_clave_int = jh.obr_clave_int WHERE tn.tin_tipo = 1 and (jh.obr_clave_int  = '".$obr."' or  '".$obr."' IS NULL or '".$obr."' = '') and j.jor_estado = 1 and jd.jod_fecha between '".$ini."' and '".$fin."' GROUP BY o.obr_clave_int");

        //echo "SELECT SUM(TIME_TO_SEC(TIMEDIFF(joh_fin, joh_inicio))/3600) as totalhoras, o.obr_clave_int ido, o.obr_nombre nom, obr_cencos cen,obr_hr_mes hrmes, o.obr_vr_maquina vm FROM tbl_jornada j join tbl_jornada_dias jd on j.jor_clave_int = jd.jor_clave_int JOIN tbl_jornada_horas jh ON jh.jod_clave_int = jd.jod_clave_int JOIN tbl_tipo_novedad tn on tn.tin_clave_int = jh.tin_clave_int join  tbl_obras o ON o.obr_clave_int = jh.obr_clave_int WHERE tn.tin_tipo = 1 and date_format(jd.jod_fecha,'%Y') = '".$ano."' and date_format(jd.jod_fecha,'%m') = '".$mes."' and (jh.obr_clave_int in(".$obr1.") or  '".$obr."' IS NULL '".$obr."' = '') and j.jor_estado = 1  GROUP BY o.obr_clave_int";
        $numtoth = mysqli_num_rows($contoth);
       
        ?>
        <table  id="tbLiquidarObra" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th class="dt-head-center bg-terracota">Obra</th>
                    <th class="dt-head-center bg-terracota">Cencos</th>
                    <th class="dt-head-center bg-terracota">Horas Maquina</th>
                    <th class="dt-head-center bg-terracota">Horas por Contrato</th>
                    <th class="dt-head-center bg-terracota">Horas Extras Maquina</th>
                    <th class="dt-head-center bg-terracota">Valor Hora</th>
                    <th class="dt-head-center bg-terracota">Total H.E</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                    while( $dath = mysqli_fetch_array($contoth))
                    {
                        $totalhoras = $dath['totalhoras'];
                        $obra = $dath['nom']; //OBRA|
                        $cenco  = $dath['cen']; // CENCOS
                        $horascontrato = $dath['hrmes']; // HORAS CONTRATADAS POR CADA OBRA
                        $horasextras = $totalhoras - $horascontrato; // DIFERENCIA ENTRE LAS HORAS REALES Y LAS HORAS CONTRATADAS POR Obra
                        $vm = $dath['vm'];// VALOR HORA MAQUINA
                        $totalhe = ($horasextras>0)?$vm * $horasextras: 0; //VALOR TOTAL DE HORAS EXTRAS POR MAQUINA
                        ?>
                        <tr>
                            <td><?php echo $obra;?></td>
                            <td><?php echo $cenco;?></td>
                            <td><?php echo number_format($totalhoras,2,".","");?></td>
                            <td><?php echo number_format($horascontrato,2,".","");?></td>
                            <td><?php echo number_format($horasextras,2,".","");?></td>
                            <td><?php echo "$".number_format($vm,2,".",",");?></td>
                            <td><?php echo "$".number_format($totalhe,2,".",",");?></td>
                        </tr>
                        <?PHP

                    }
                ?>
                
            </tbody>
        </table>
        <?php
    }
    echo "<script>INICIALIZARCONTENIDO();</script>";
}
else if($opcion=="CARGAREMPLEADO")
{
    $ini = $_POST['ini'];
    $fin = $_POST['fin'];
    $obr = $_POST['obr'];
    $tip = $_POST['tip'];
    $emp = $_POST['emp'];
    if($tip==1)
    {
        $selectEmp = new General();
        $selectEmp -> cargarEmpleados($emp,2);
    }
    else
    {
        $sql = "SELECT DISTINCT 
        u.usu_clave_int AS id, 
        CONCAT(u.usu_apellido, ' ', u.usu_nombre) AS nom, 
        u.usu_correo AS cor 
        FROM tbl_usuarios u 
        JOIN tbl_jornada j ON j.usu_clave_int = u.usu_clave_int 
        JOIN tbl_jornada_dias d ON d.jor_clave_int = j.jor_clave_int 
        JOIN tbl_jornada_horas h ON h.jod_clave_int = d.jod_clave_int 
        WHERE u.est_clave_int = 1 
        AND j.jor_estado = 1 
        AND d.jod_fecha BETWEEN :ini AND :fin 
        AND h.obr_clave_int = :obr 
        ORDER BY u.usu_apellido ASC";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':ini', $ini);
        $stmt->bindParam(':fin', $fin);
        $stmt->bindParam(':obr', $obr, PDO::PARAM_INT);
        $stmt->execute();

        $empleados = [];

        while ($dat = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $id  = $dat['id'];
            $nom = $dat['nom'];
            $cor = $dat['cor'];
            $selected = ($id == $sel) ? "selected" : "";

            if ($tip == 1) {
                echo "<option data-subtext='" . htmlspecialchars($cor, ENT_QUOTES) . "' $selected value='" . htmlspecialchars($id, ENT_QUOTES) . "'>" . htmlspecialchars($nom, ENT_QUOTES) . "</option>";
            } else {
                $empleados[] = [
                    "id"  => $id,
                    "nom" => $nom,
                    "cor" => $cor
                ];
            }
        }
        echo json_encode($empleados);    
    }
}
else if($opcion=="GENERARPROFORMAOLD2")
{
    $id = $_POST['id'];
    $tip = $_POST['tip'];
    $emp = $_POST['emp'];    
    $ano = $_POST['ano'];
    $mes = $_POST['mes'];
    $obr = $_POST['obr'];
    $ini = $_POST['ini'];
    $fin = $_POST['fin'];
    $salario = 0;
    //validar si hay dias en liquidar_dias // o liquidar_dias_obra
    $wh = ($id>0)?" and liq_clave_int != '".$id."'":"";
    $infousu = new General();
    $info = $infousu->editUsuario($emp);
    $per = $info['per'];
    //HORAS RECARGO NOCTURNO
    $tshirn = $infousu->time_to_sec($hirn);
    $tshfrn = $infousu->time_to_sec($hfrn);
    $tshirn = str_replace(",",".",$tshirn);
    $tshfrn = str_replace(",",".",$tshfrn);

    if($tip==1){
       
        $salario = $info['sal'];
        $vrhora = $salario/$hmes;
        $vm = 0;
        $sqlv = "SELECT * FROM tbl_liquidar_dias WHERE usu_clave_int = '".$emp."' and lid_fecha between '".$ini."' and '".$fin."'".$wh;
        $msn = "Liquidación Nomina Generada Correctamente";
    }
    else if($tip==2)
    {
        $infob = $infousu->editObra($obr);
        $vs = $infob['obr_vr_senalero'];
        $vo = $infob['obr_vr_operador'];
        $vm = $infob['obr_vr_maquina'];
        $ve = $infob['obr_vr_elevador'];
        $hmes = $infob['obr_hr_mes'];
        $vrhora = ($per==2?$vo:($per==5?$ve:$vs));
        $sqlv = "SELECT * FROM tbl_liquidar_dias_obra WHERE usu_clave_int = '".$emp."' and obr_clave_int = '".$obr."' and lid_fecha between '".$ini."' and '".$fin."'".$wh;
        $msn = "Liquidación Obra Generada Correctamente";
    }
    $veridia = mysqli_query($conectar,$sqlv);
    $numvdia = mysqli_num_rows($veridia);
    //validar si ya hay dias del rango seleccionado liquidados
    $verificar = mysqli_query($conectar, "SELECT * FROM tbl_liquidar WHERE ((liq_inicio between '".$ini."' and '".$ini."') or (liq_fin between '".$fin."' and '".$fin."')) and usu_clave_int = '".$emp."' and liq_tipo = '".$tip."' and liq_clave_int!='".$id."'");
    $numv = mysqli_num_rows($verificar);
    if($numv>0)
    {
        $res = "error";
        $msn = "Ya se realizo una liquidación ";
    }
    else if($numvdia<=0)
    {
        $res = "error";
        $msn = "No hay horarios ingresado en el rango de fechas seleccionado";
    }
    else
    {
        if($id>0)
        {
            $upd = mysqli_query($conectar, "UPDATE tbl_liquidar SET liq_inicio = '".$ini."', liq_fin = '".$fin."', liq_usu_actualiz = '".$usuario."', liq_fec_actualiz = '".$fecha."',liq_hr_mes = '".$hmes."', liq_salario = '".$vrhora."', liq_vr_maquina = '".$vm."', liq_hi_rn = '".$tshirn."', liq_hf_rn = '".$tshfrn."' WHERE liq_clave_int = '".$id."'");
            if($upd>0)
            {
                $idliquidar = $id;
                $res = "ok";
                $msn = "Liquidación modificada correctamente";
            }
            else
            {
                $res = "error";
                $msn = "Surgió un error al actualizar liquidación. Verificar";
            }
        }
        else
        {
            $conmax = mysqli_query($conectar, "SELECT max(liq_consecutivo) ma FROM tbl_liquidar WHERE liq_tipo = '".$tip."'");
            $datmax = mysqli_fetch_array($conmax);
            $conse = $datmax['ma']; $conse = ($conse<=0 || $conse==NULL)?1:$conse+1;
            
	        $codigo = sprintf('%010d',$conse);

            $ins = mysqli_query($conectar, "INSERT INTO tbl_liquidar (obr_clave_int, usu_clave_int, liq_fecha,liq_creacion, liq_ano, liq_tipo, liq_inicio, liq_fin, liq_usu_actualiz, liq_fec_actualiz,liq_hr_mes, liq_salario, liq_consecutivo,liq_vr_maquina, liq_hi_rn, liq_hf_rn, liq_codigo) VALUES ('".$obr."','".$emp."','".$fecha."','".$idUsuario."','".$ano."','".$tip."','".$ini."','".$fin."','".$usuario."','".$fecha."','".$hmes."','".$vrhora."', '".$conse."','".$vm."','".$tshirn."','".$tshfrn."','".$codigo."')");
            if($ins>0)
            {
                $idliquidar = mysqli_insert_id($conectar);
                
            
                if($tip==1){
                    $sqlu = "UPDATE tbl_liquidar_dias  SET liq_clave_int = '".$idliquidar."' WHERE usu_clave_int = '".$emp."' and lid_fecha between '".$ini."' and '".$fin."'";
                }
                else
                {
                    $sqlu = "UPDATE tbl_liquidar_dias_obra SET liq_clave_int = '".$idliquidar."' WHERE usu_clave_int = '".$emp."' and obr_clave_int = '".$obr."' and lid_fecha between '".$ini."' and '".$fin."'";
                }
                //INSERTAR CON QUE PORCENTAJES DE HORAS SE LIQUIDO YA QUE CON EL TIEMPO PUEDES VARIAR LOS PORCENTAHES
                $inshora = mysqli_query($conectar, "INSERT INTO tbl_liquidar_horas(liq_clave_int, hor_clave_int,lih_porcentaje) SELECT '".$idliquidar."', hor_clave_int, hor_porcentaje FROM tbl_horas WHERE est_clave_int = 1");

                $upd = mysqli_query($conectar,$sqlu);
                $res = "ok";
                
            }
            else
            {
                $res = "error";
                $msn  ="Surgió un error al guardar liquidación. Error BD". mysqli_error($conectar);
            }
        }
    }
    $datos[] = array("res"=>$res,"msn"=>$msn, "idliquidar"=>encrypt($idliquidar,'p4v4sAp'));
    echo json_encode($datos);
    //DESPUES DE GENERAR SELECCIO
}
else if ($opcion == "GENERARPROFORMA") {
    $id  = $_POST['id'];
    $tip = $_POST['tip'];
    $emp = $_POST['emp'];
    $ano = $_POST['ano'];
    $mes = $_POST['mes'];
    $obr = $_POST['obr'];
    $ini = $_POST['ini'];
    $fin = $_POST['fin'];

    $salario = 0;
    $wh = ($id > 0) ? " AND liq_clave_int != :id" : "";

    $infousu = new General();
    $info = $infousu->editUsuario($emp);
    $per = $info['per'];

   

    $tshirn  = str_replace(",", ".", $infousu->time_to_sec($hirn));
    $tshfrn = str_replace(",", ".", $infousu->time_to_sec($hfrn));

    if ($tip == 1) {
        $salario = $info['sal'];
        $vrhora = $salario / $hmes;
        $vm = 0;
        $sqlv = "SELECT * FROM tbl_liquidar_dias 
                 WHERE usu_clave_int = :emp 
                   AND lid_fecha BETWEEN :ini AND :fin $wh";
        $msn = "Liquidación Nómina Generada Correctamente";
    } else if ($tip == 2) {
        $infob = $infousu->editObra($obr);
        $vs = $infob['obr_vr_senalero'];
        $vo = $infob['obr_vr_operador'];
        $vm = $infob['obr_vr_maquina'];
        $ve = $infob['obr_vr_elevador'];
        $hmes = $infob['obr_hr_mes'];
        $vrhora = ($per == 2 ? $vo : ($per == 5 ? $ve : $vs));
        $sqlv = "SELECT * FROM tbl_liquidar_dias_obra 
                 WHERE usu_clave_int = :emp AND obr_clave_int = :obr 
                   AND lid_fecha BETWEEN :ini AND :fin $wh";
        $msn = "Liquidación Obra Generada Correctamente";
    }

    // Validar si hay días en el rango
    $stmtVal = $conn->prepare($sqlv);
    $stmtVal->bindParam(':emp', $emp, PDO::PARAM_INT);
    $stmtVal->bindParam(':ini', $ini);
    $stmtVal->bindParam(':fin', $fin);
    if ($id > 0) {
        $stmtVal->bindParam(':id', $id);
    }
    if ($tip == 2) {
        $stmtVal->bindParam(':obr', $obr, PDO::PARAM_INT);
    }
    $stmtVal->execute();
    $numvdia = $stmtVal->rowCount();

    // Verificar si ya existe liquidación para esas fechas
    $sqlVerificar = "SELECT * FROM tbl_liquidar 
                     WHERE ((liq_inicio BETWEEN :ini AND :ini) 
                        OR (liq_fin BETWEEN :fin AND :fin)) 
                       AND usu_clave_int = :emp 
                       AND liq_tipo = :tip 
                       AND liq_clave_int != :id";
    $stmtCheck = $conn->prepare($sqlVerificar);
    $stmtCheck->bindParam(':ini', $ini);
    $stmtCheck->bindParam(':fin', $fin);
    $stmtCheck->bindParam(':emp', $emp, PDO::PARAM_INT);
    $stmtCheck->bindParam(':tip', $tip);
    $stmtCheck->bindParam(':id', $id);
    $stmtCheck->execute();
    $numv = $stmtCheck->rowCount();

    if ($numv > 0) {
        $res = "error";
        $msn = "Ya se realizó una liquidación";
    } else if ($numvdia <= 0) {
        $res = "error";
        $msn = "No hay horarios ingresados en el rango de fechas seleccionado";
    } else {
        if ($id > 0) {
            // UPDATE
            $stmtUpd = $conn->prepare("UPDATE tbl_liquidar SET 
                    liq_inicio = :ini, liq_fin = :fin, 
                    liq_usu_actualiz = :usuario, liq_fec_actualiz = :fecha,
                    liq_hr_mes = :hmes, liq_salario = :salario, liq_vr_maquina = :vm,
                    liq_hi_rn = :tshirn, liq_hf_rn = :tshfrn 
                    WHERE liq_clave_int = :id");
            $stmtUpd->execute([
                ':ini' => $ini,
                ':fin' => $fin,
                ':usuario' => $usuario,
                ':fecha' => $fecha,
                ':hmes' => $hmes,
                ':salario' => $vrhora,
                ':vm' => $vm,
                ':tshirn' => $tshirn,
                ':tshfrn' => $tshfrn,
                ':id' => $id
            ]);
            $idliquidar = $id;
            $res = "ok";
            $msn = "Liquidación modificada correctamente";
        } else {
            // INSERT
            $stmtCon = $conn->query("SELECT MAX(liq_consecutivo) AS ma FROM tbl_liquidar WHERE liq_tipo = $tip");
            $conse = $stmtCon->fetch(PDO::FETCH_ASSOC)['ma'] ?? 0;
            $conse = ($conse <= 0) ? 1 : $conse + 1;
            $codigo = sprintf('%010d', $conse);

            $stmtIns = $conn->prepare("INSERT INTO tbl_liquidar 
                (obr_clave_int, usu_clave_int, liq_fecha, liq_creacion, liq_ano, liq_tipo, 
                 liq_inicio, liq_fin, liq_usu_actualiz, liq_fec_actualiz, 
                 liq_hr_mes, liq_salario, liq_consecutivo, liq_vr_maquina, 
                 liq_hi_rn, liq_hf_rn, liq_codigo)
                VALUES 
                (:obr, :emp, :fecha, :idUsuario, :ano, :tip, 
                 :ini, :fin, :usuario, :fecha, 
                 :hmes, :vrhora, :conse, :vm, 
                 :tshirn, :tshfrn, :codigo)");
            $stmtIns->execute([
                ':obr' => $obr,
                ':emp' => $emp,
                ':fecha' => $fecha,
                ':idUsuario' => $idUsuario,
                ':ano' => $ano,
                ':tip' => $tip,
                ':ini' => $ini,
                ':fin' => $fin,
                ':usuario' => $usuario,
                ':hmes' => $hmes,
                ':vrhora' => $vrhora,
                ':conse' => $conse,
                ':vm' => $vm,
                ':tshirn' => $tshirn,
                ':tshfrn' => $tshfrn,
                ':codigo' => $codigo
            ]);

            $idliquidar = $conn->lastInsertId();

            // UPDATE tbl_liquidar_dias / dias_obra
            if ($tip == 1) {
                $conn->prepare("UPDATE tbl_liquidar_dias 
                                SET liq_clave_int = :liq 
                                WHERE usu_clave_int = :emp 
                                  AND lid_fecha BETWEEN :ini AND :fin")
                     ->execute([':liq' => $idliquidar, ':emp' => $emp, ':ini' => $ini, ':fin' => $fin]);
            } else {
                $conn->prepare("UPDATE tbl_liquidar_dias_obra 
                                SET liq_clave_int = :liq 
                                WHERE usu_clave_int = :emp 
                                  AND obr_clave_int = :obr 
                                  AND lid_fecha BETWEEN :ini AND :fin")
                     ->execute([':liq' => $idliquidar, ':emp' => $emp, ':obr' => $obr, ':ini' => $ini, ':fin' => $fin]);
            }

            // INSERTAR porcentajes horas
            $conn->query("INSERT INTO tbl_liquidar_horas(liq_clave_int, hor_clave_int, lih_porcentaje)
                          SELECT $idliquidar, hor_clave_int, hor_porcentaje 
                          FROM tbl_horas WHERE est_clave_int = 1");

            $res = "ok";
        }
    }

    $datos[] = [
        "res" => $res,
        "msn" => $msn,
        "idliquidar" => encrypt($idliquidar, 'p4v4sAp')
    ];
    echo json_encode($datos);
}

// GENERAR TODAS LAS PROFORMAS DE LOS EMPLEADOS SELECCIONADOS
else if($opcion=="GENERARALLPROFORMAOLD2")
{
    $id = $_POST['id'];
    $tip = $_POST['tip'];
    // $emp = $_POST['emp'];    
    $ano = $_POST['ano'];
    $mes = $_POST['mes'];
    $obr = $_POST['obr'];
    $ini = $_POST['ini'];
    $fin = $_POST['fin'];
    $salario = 0;

    $emp = $_POST['emp']; //    
    $emp = implode(', ', (array)$emp); if($emp==""){ $emp1="''";}else { $emp1=$emp; }    
    $who = ($tipo==2)?" and h.obr_clave_int = '".$obr."'":"";

    $cone = mysqli_query($conectar,"SELECT DISTINCT u.usu_clave_int id, concat(usu_apellido,' ',usu_nombre) nom, usu_correo cor, u.prf_clave_int per, u.usu_salario sal  from tbl_usuarios u join tbl_jornada j on j.usu_clave_int  = u.usu_clave_int join tbl_jornada_dias d on d.jor_clave_int = j.jor_clave_int join tbl_jornada_horas h on h.jod_clave_int = d.jod_clave_int WHERE (u.est_clave_int = 1 ) and  j.jor_estado = 1 and d.jod_fecha between '".$ini."' and '".$fin."' and (j.usu_clave_int  in(".$emp1.")  OR '".$emp."' IS NULL OR '".$emp."' = '') ".$who. " ORDER BY usu_apellido ASC"); // or usu_clave_int = '".$sel."'
    $nume = mysqli_num_rows($cone);

    //validar si hay dias en liquidar_dias // o liquidar_dias_obra
    $wh = ""; // ($id>0)?" and liq_clave_int != '".$id."'":"";


    $infousu = new General();
    
    //HORAS RECARGO NOCTURNO
    $tshirn = $infousu->time_to_sec($hirn);
    $tshfrn = $infousu->time_to_sec($hfrn);
    $tshirn = str_replace(",",".",$tshirn);
    $tshfrn = str_replace(",",".",$tshfrn);

    $numgeneradas = 0;
    $numnogeneradas = 0;
    $numexistentes = 0;
    $generadas = "<table class='table' style='font-size:11px'>";
    $generadas.="<tr><td>EMPLEADO</td><td>MENSAJE</td></tr>";
    $nogeneradas = "<table class='table' style='font-size:11px'>";
    $nogeneradas.="<tr><td>EMPLEADO</td><td>MENSAJE</td></tr>";

    $vs = 0; $vo = 0; $vm = 0; $ve = 0;
    
    if($tip==2)
    {
        $infob = $infousu->editObra($obr);
        $vs = $infob['obr_vr_senalero'];
        $vo = $infob['obr_vr_operador'];
        $vm = $infob['obr_vr_maquina'];
        $ve = $infob['obr_vr_elevador'];
        $hmes = $infob['obr_hr_mes'];               
    }

    if($nume<=0)
    {
        $res = "error";
        $msn = "No hay empleados con planillas registradas en ese rango de fecha";
    }
    else
    {
        for($ne=0;$ne<$nume;$ne++)
        {
            $date = mysqli_fetch_array($cone);
            $ide = $date['id'];
            $nom = $date['nom'];
            $cor = $date['cor'];
            // $info = $infousu->editUsuario($ide);
            // $per = $info['per'];
            $per = $date['per'];
            $salario = $date['sal'];

            if($tip==1){
       
                // $salario = $info['sal'];
                $vrhora = $salario/$hmes;
                $vm = 0;
                $sqlv = "SELECT * FROM tbl_liquidar_dias WHERE usu_clave_int = '".$ide."' and lid_fecha between '".$ini."' and '".$fin."'".$wh;
                $msn = "Liquidación Nomina Generada Correctamente";
            }
            else if($tip==2)
            {
                // $infob = $infousu->editObra($obr);
                // $vs = $infob['obr_vr_senalero'];
                // $vo = $infob['obr_vr_operador'];
                // $vm = $infob['obr_vr_maquina'];
                // $ve = $infob['obr_vr_elevador'];
                // $hmes = $infob['obr_hr_mes'];
                $vrhora = ($per==2?$vo:($per==5?$ve:$vs));
                $sqlv = "SELECT * FROM tbl_liquidar_dias_obra WHERE usu_clave_int = '".$ide."' and obr_clave_int = '".$obr."' and lid_fecha between '".$ini."' and '".$fin."'".$wh;
                $msn = "Liquidación Obra Generada Correctamente";
            }
            // iINICIO CALCULOS
            $veridia = mysqli_query($conectar,$sqlv);
            $numvdia = mysqli_num_rows($veridia);
            //validar si ya hay dias del rango seleccionado liquidados
            $verificar = mysqli_query($conectar, "SELECT * FROM tbl_liquidar WHERE ((liq_inicio between '".$ini."' and '".$ini."') or (liq_fin between '".$fin."' and '".$fin."')) and usu_clave_int = '".$ide."' and liq_tipo = '".$tip."' "); // and liq_clave_int!='".$id."'
            $numv = mysqli_num_rows($verificar);
            if($numv>0)
            {
                $numnogeneradas++;
                $numexistentes++;
                 $update = mysqli_query($conectar, "UPDATE tbl_liquidar SET liq_hr_mes = '".$hmes."', liq_salario = '".$vrhora."', liq_vr_maquina = '".$vm."' WHERE ((liq_inicio between '".$ini."' and '".$ini."') or (liq_fin between '".$fin."' and '".$fin."')) and usu_clave_int = '".$ide."' and liq_tipo = '".$tip."'");
                // $res = "error";
                $msn = "Ya se realizo una liquidación ";
                $nogeneradas.="<tr><td>".$nom."</td><td>".$msn."</td></tr>";
            }
            else if($numvdia<=0)
            {
                $numnogeneradas++;
                // $res = "error";
                $msn = "No hay horarios ingresado en el rango de fechas seleccionado";
                $nogeneradas.="<tr><td>".$nom."</td><td>".$msn."</td></tr>";
            }
            else
            {
                
                $conmax = mysqli_query($conectar, "SELECT max(liq_consecutivo) ma FROM tbl_liquidar WHERE liq_tipo = '".$tip."'");
                $datmax = mysqli_fetch_array($conmax);
                $conse = $datmax['ma']; $conse = ($conse<=0 || $conse==NULL)?1:$conse+1;                
                $codigo = sprintf('%010d',$conse);

                $ins = mysqli_query($conectar, "INSERT INTO tbl_liquidar (obr_clave_int, usu_clave_int, liq_fecha,liq_creacion, liq_ano, liq_tipo, liq_inicio, liq_fin, liq_usu_actualiz, liq_fec_actualiz,liq_hr_mes, liq_salario, liq_consecutivo,liq_vr_maquina, liq_hi_rn, liq_hf_rn, liq_codigo) VALUES ('".$obr."','".$ide."','".$fecha."','".$idUsuario."','".$ano."','".$tip."','".$ini."','".$fin."','".$usuario."','".$fecha."','".$hmes."','".$vrhora."', '".$conse."','".$vm."','".$tshirn."','".$tshfrn."','".$codigo."')");
                if($ins>0)
                {
                    $idliquidar = mysqli_insert_id($conectar);
                    
                
                    if($tip==1){
                        $sqlu = "UPDATE tbl_liquidar_dias  SET liq_clave_int = '".$idliquidar."' WHERE usu_clave_int = '".$ide."' and lid_fecha between '".$ini."' and '".$fin."'";
                    }
                    else
                    {
                        $sqlu = "UPDATE tbl_liquidar_dias_obra SET liq_clave_int = '".$idliquidar."' WHERE usu_clave_int = '".$ide."' and obr_clave_int = '".$obr."' and lid_fecha between '".$ini."' and '".$fin."'";
                    }
                    //INSERTAR CON QUE PORCENTAJES DE HORAS SE LIQUIDO YA QUE CON EL TIEMPO PUEDES VARIAR LOS PORCENTAHES
                    $inshora = mysqli_query($conectar, "INSERT INTO tbl_liquidar_horas(liq_clave_int, hor_clave_int,lih_porcentaje) SELECT '".$idliquidar."', hor_clave_int, hor_porcentaje FROM tbl_horas WHERE est_clave_int = 1");

                    $upd = mysqli_query($conectar,$sqlu);
                    $numgeneradas++;
                    $generadas.="<tr><td>".$nom."</td><td>".$msn."</td></tr>";
                    // $res = "ok";
                    
                }
                else
                {
                    $numnogeneradas++;
                    // $res = "error";
                    $msn  ="Surgió un error al guardar liquidación. Error BD". mysqli_error($conectar);
                    $nogeneradas.="<tr><td>".$nom."</td><td>".$msn."</td></tr>";
                }            
            }
        }
    }

    $generadas.="</table>";
    $nogeneradas.="</table>";
    if($numnogeneradas>0 and $numnogeneradas==$numexistentes)
    {
        $res = "ok";
        $msn = "Liquidaciónes generadas correctamente";
    }
    else
    if($numgeneradas>0)
    {
        $res = "ok";
        $msn = "Liquidaciónes generadas correctamente";
    }
    else
    {
        $res = "error";
        $msn = "Surgieron errores para generar las liquidaciones";
    }

    $datos[] = array("res"=>$res,"msn"=>$msn, "numnogeneradas"=>$numnogeneradas, "numgeneradas"=>$numgeneradas, "generadas"=>$generadas,"nogeneradas"=>$nogeneradas);
    echo json_encode($datos);
}
else
if ($opcion == "GENERARALLPROFORMA") {
    $id     = $_POST['id'];
    $tip    = $_POST['tip'];
    $ano    = $_POST['ano'];
    $mes    = $_POST['mes'];
    $obr    = $_POST['obr'];
    $ini    = $_POST['ini'];
    $fin    = $_POST['fin'];
    $emp    = $_POST['emp'];
    $fecha  = date("Y-m-d"); // Asegúrate de tener la fecha actual si no la recibes por POST

    $empList = is_array($emp) ? $emp : [$emp];
    $empPlaceholders = implode(',', array_fill(0, count($empList), '?'));

    $infousu = new General();
    $tshirn = str_replace(",", ".", $infousu->time_to_sec($hirn));
    $tshfrn = str_replace(",", ".", $infousu->time_to_sec($hfrn));

    $generadas = "<table class='table' style='font-size:11px'><tr><td>EMPLEADO</td><td>MENSAJE</td></tr>";
    $nogeneradas = "<table class='table' style='font-size:11px'><tr><td>EMPLEADO</td><td>MENSAJE</td></tr>";

    $numgeneradas = $numnogeneradas = $numexistentes = 0;

    // Datos de la obra (si es obra)
    $vs = $vo = $vm = $ve = 0;
    if ($tip == 2) {
        $infob = $infousu->editObra($obr);
        $vs = $infob['obr_vr_senalero'];
        $vo = $infob['obr_vr_operador'];
        $vm = $infob['obr_vr_maquina'];
        $ve = $infob['obr_vr_elevador'];
        $hmes = $infob['obr_hr_mes'];
    } else {
        // hmes ya esta global 20250616
        //$hmes = 240; // Valor por defecto si no es tipo obra
    }

    // Obtener empleados con jornada en rango
    $sqlEmp = "SELECT DISTINCT 
                    u.usu_clave_int AS id, 
                    CONCAT(u.usu_apellido, ' ', u.usu_nombre) AS nom, 
                    u.usu_correo AS cor, 
                    u.prf_clave_int AS per, 
                    u.usu_salario AS sal
                FROM tbl_usuarios u
                JOIN tbl_jornada j ON j.usu_clave_int = u.usu_clave_int
                JOIN tbl_jornada_dias d ON d.jor_clave_int = j.jor_clave_int
                JOIN tbl_jornada_horas h ON h.jod_clave_int = d.jod_clave_int
                WHERE u.est_clave_int = 1
                  AND j.jor_estado = 1
                  AND d.jod_fecha BETWEEN ? AND ?
                  AND (j.usu_clave_int IN ($empPlaceholders))";

    if ($tip == 2) {
        $sqlEmp .= " AND h.obr_clave_int = ?";
    }
    $sqlEmp .= " ORDER BY u.usu_apellido ASC";

    $params = [$ini, $fin];
    $params = array_merge($params, $empList);
    if ($tip == 2) {
        $params[] = $obr;
    }

    $stmtEmp = $conn->prepare($sqlEmp);
    $stmtEmp->execute($params);
    $empleados = $stmtEmp->fetchAll(PDO::FETCH_ASSOC);

    if (empty($empleados)) {
        echo json_encode([
            "res" => "error",
            "msn" => "No hay empleados con planillas registradas en ese rango de fecha"
        ]);
        exit;
    }

    foreach ($empleados as $e) {
        $ide = $e['id'];
        $nom = $e['nom'];
        $per = $e['per'];
        $salario = $e['sal'];

        if ($tip == 1) {
            $vrhora = $salario / $hmes;
            $vm = 0;
            $sqlv = "SELECT 1 FROM tbl_liquidar_dias 
                     WHERE usu_clave_int = :ide AND lid_fecha BETWEEN :ini AND :fin";
        } else {
            $vrhora = ($per == 2 ? $vo : ($per == 5 ? $ve : $vs));
            $sqlv = "SELECT 1 FROM tbl_liquidar_dias_obra 
                     WHERE usu_clave_int = :ide AND obr_clave_int = :obr AND lid_fecha BETWEEN :ini AND :fin";
        }

        $stmtCheckDias = $conn->prepare($sqlv);
        $stmtCheckDias->bindValue(':ide', $ide, PDO::PARAM_INT);
        $stmtCheckDias->bindValue(':ini', $ini);
        $stmtCheckDias->bindValue(':fin', $fin);
        if ($tip == 2) $stmtCheckDias->bindValue(':obr', $obr, PDO::PARAM_INT);
        $stmtCheckDias->execute();
        $numvdia = $stmtCheckDias->rowCount();

        $stmtVerificar = $conn->prepare("SELECT 1 FROM tbl_liquidar 
                                         WHERE ((liq_inicio BETWEEN :ini AND :ini) OR (liq_fin BETWEEN :fin AND :fin))
                                           AND usu_clave_int = :ide AND liq_tipo = :tip");
        $stmtVerificar->execute([
            ':ini' => $ini,
            ':fin' => $fin,
            ':ide' => $ide,
            ':tip' => $tip
        ]);
        $numv = $stmtVerificar->rowCount();

        if ($numv > 0) {
            $numnogeneradas++;
            $numexistentes++;
            $conn->prepare("UPDATE tbl_liquidar 
                            SET liq_hr_mes = :hmes, liq_salario = :sal, liq_vr_maquina = :vm 
                            WHERE ((liq_inicio BETWEEN :ini AND :ini) OR (liq_fin BETWEEN :fin AND :fin)) 
                              AND usu_clave_int = :ide AND liq_tipo = :tip")
                 ->execute([
                     ':hmes' => $hmes,
                     ':sal' => $vrhora,
                     ':vm' => $vm,
                     ':ini' => $ini,
                     ':fin' => $fin,
                     ':ide' => $ide,
                     ':tip' => $tip
                 ]);
            $nogeneradas .= "<tr><td>$nom</td><td>Ya se realizó una liquidación</td></tr>";
        } elseif ($numvdia <= 0) {
            $numnogeneradas++;
            $nogeneradas .= "<tr><td>$nom</td><td>No hay horarios en el rango</td></tr>";
        } else {
            $stmtMax = $conn->query("SELECT MAX(liq_consecutivo) AS ma FROM tbl_liquidar WHERE liq_tipo = $tip");
            $conse = $stmtMax->fetchColumn();
            $conse = ($conse <= 0 || $conse == null) ? 1 : $conse + 1;
            $codigo = sprintf('%010d', $conse);

            $stmtIns = $conn->prepare("INSERT INTO tbl_liquidar 
                (obr_clave_int, usu_clave_int, liq_fecha, liq_creacion, liq_ano, liq_tipo, 
                 liq_inicio, liq_fin, liq_usu_actualiz, liq_fec_actualiz, 
                 liq_hr_mes, liq_salario, liq_consecutivo, liq_vr_maquina, 
                 liq_hi_rn, liq_hf_rn, liq_codigo)
                VALUES (:obr, :ide, :fecha, :idUsuario, :ano, :tip, :ini, :fin, :usuario, :fecha, 
                        :hmes, :vrhora, :conse, :vm, :tshirn, :tshfrn, :codigo)");

            $stmtIns->execute([
                ':obr' => $obr,
                ':ide' => $ide,
                ':fecha' => $fecha,
                ':idUsuario' => $idUsuario,
                ':ano' => $ano,
                ':tip' => $tip,
                ':ini' => $ini,
                ':fin' => $fin,
                ':usuario' => $usuario,
                ':hmes' => $hmes,
                ':vrhora' => $vrhora,
                ':conse' => $conse,
                ':vm' => $vm,
                ':tshirn' => $tshirn,
                ':tshfrn' => $tshfrn,
                ':codigo' => $codigo
            ]);

            $idliquidar = $conn->lastInsertId();

            if ($tip == 1) {
                $conn->prepare("UPDATE tbl_liquidar_dias 
                                SET liq_clave_int = :liq 
                                WHERE usu_clave_int = :ide 
                                  AND lid_fecha BETWEEN :ini AND :fin")
                     ->execute([
                         ':liq' => $idliquidar,
                         ':ide' => $ide,
                         ':ini' => $ini,
                         ':fin' => $fin
                     ]);
            } else {
                $conn->prepare("UPDATE tbl_liquidar_dias_obra 
                                SET liq_clave_int = :liq 
                                WHERE usu_clave_int = :ide AND obr_clave_int = :obr 
                                  AND lid_fecha BETWEEN :ini AND :fin")
                     ->execute([
                         ':liq' => $idliquidar,
                         ':ide' => $ide,
                         ':obr' => $obr,
                         ':ini' => $ini,
                         ':fin' => $fin
                     ]);
            }

            // Insertar porcentaje horas
            $conn->query("INSERT INTO tbl_liquidar_horas(liq_clave_int, hor_clave_int, lih_porcentaje)
                          SELECT $idliquidar, hor_clave_int, hor_porcentaje 
                          FROM tbl_horas WHERE est_clave_int = 1");

            $numgeneradas++;
            $generadas .= "<tr><td>$nom</td><td>Liquidación generada</td></tr>";
        }
    }

    $generadas .= "</table>";
    $nogeneradas .= "</table>";

    $res = ($numgeneradas > 0) ? "ok" : "error";
    $msn = ($numgeneradas > 0) ? "Liquidaciones generadas correctamente" : "Surgieron errores al generar las liquidaciones";

    echo json_encode([
        "res" => $res,
        "msn" => $msn,
        "numnogeneradas" => $numnogeneradas,
        "numgeneradas" => $numgeneradas,
        "generadas" => $generadas,
        "nogeneradas" => $nogeneradas
    ]);
}

else if($opcion=="FILTROS")
{
    $gen = new General();
    ?>
    <div class="col-md-3">
        <label for="busempleado">Empleado</label>
        <select  id="busempleado" name="busempleado" multiple class="form-control form-control-sm selectpicker" onchange="CRUDLIQUIDAR('LISTALIQUIDACIONES', '')" data-actions-box="true">        
        <?php
            $gen -> cargarEmpleados();
        ?>
        </select>
        <span id="msn-error1"></span>
    </div>
    <div class="col-md-3">
        <label for="busobra">Obra</label>
        <select id="busobra" name="busobra" multiple class="form-control form-control-sm selectpicker" onchange="CRUDLIQUIDAR('LISTALIQUIDACIONES', '')"  data-actions-box="true">            
            <?php
            $gen -> cargarObras("",1);
            ?>
        </select>
    </div>
    <div class="col-md-3">
        <label for="busano">Año</label>
        <select name="busano" id="busano" class="form-control form-control-sm selectpicker" onchange="CRUDLIQUIDAR('LISTALIQUIDACIONES', '');">
            <option value=""></option>
            <?php
            $gen -> selSelect(2020,date('Y'));
            ?>
        </select>
    </div>
    <?php
    echo "<script>INICIALIZARCONTENIDO();</script>";
    echo "<script>CRUDLIQUIDAR('LISTALIQUIDACIONES', '')</script>";
}
else if($opcion=="LISTALIQUIDACIONES")
{
    $tip = $_POST['tip'];
    ?>
    <div class="col-md-12 table-responsive">
    <table class="table table-bordered" id="tbLiquidaciones" style="width:100%" >
        <thead>
            <tr> 
                <th class="dt-head-center bg-terracota" style="width:20px"></th>
                <th class="dt-head-center bg-terracota" style="width:20px"></th>
                <th class="dt-head-center bg-terracota" style="width:20px"></th>
                <th class="dt-head-center bg-terracota" style="width:20px"></th>
                <th class="dt-head-center bg-terracota">N°</th>
                <th class="dt-head-center bg-terracota">Obra</th>
                <th class="dt-head-center bg-terracota">Cenco</th>
                <th class="dt-head-center bg-terracota">Empleado</th>
                <th class="dt-head-center bg-terracota">Fecha Creación</th>
                <th class="dt-head-center bg-terracota">Creada Por</th>
                <th class="dt-head-center bg-terracota">Tipo</th>
                <th class="dt-head-center bg-terracota">Desde</th>
                <th class="dt-head-center bg-terracota">Hasta</th>
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
                <th></th>
                <th></th>
                <th></th>
                <th></th>
                <th></th>               
            </tr>
        </tfoot>
    </table>
    </div>
    <script src="jsdatatable/jornadas/jsliquidaciones.js?<?php echo time();?>"></script>
    <?php
}
else if($opcion=="PREVIAPROFORMA")
{
    $id = $_POST['id'];    
    $src = "modulos/informes/proformapdf.php?id=".$id;
    ?>
    <div class='embed-responsive embed-responsive-4by3' >
        <iframe class='embed-responsive-item' src='<?php echo $src;?>' frameborder='0' allowfullscreen></iframe>
    </div>    
    <?php
}
else if($opcion=="PREVIAPROFORMAOBRA")
{
    $id = $_POST['id'];    
    $src = "modulos/informes/proformaobrapdf.php?id=".$id;
    ?>
    <div class='embed-responsive embed-responsive-4by3' >
        <iframe class='embed-responsive-item' src='<?php echo $src;?>' frameborder='0' allowfullscreen></iframe>
    </div>    
    <?php
}
else if($opcion=="PDFPROFORMA")
{
    $tip = $_POST['tip'];
    $emp = $_POST['emp'];
    $ano = $_POST['ano'];
    $mes = $_POST['mes'];
    $obr = $_POST['obr'];
    $src = "modulos/informes/proformapdf.php?tip=".$tip."&emp=".$emp."&ano=".$ano."&mes=".$mes."&obr=".$obr;
    ?>
    <div class='embed-responsive embed-responsive-4by3' >
        <iframe class='embed-responsive-item' src='<?php echo $src;?>' frameborder='0' allowfullscreen></iframe>
    </div>
    <?php
}
else if ($opcion == "UPDATEPROCEDE") {
    $id  = $_POST['id'];
    $pro = $_POST['pro'];

    try {
        $stmt = $conn->prepare("UPDATE tbl_liquidar_dias_obra 
                                SET lid_procede = :pro, 
                                    lid_usu_actualiz = :usuario, 
                                    lid_fec_actualiz = :fecha 
                                WHERE lid_clave_int = :id");
        $stmt->execute([
            ':pro'     => $pro,
            ':usuario' => $usuario,
            ':fecha'   => $fecha,
            ':id'      => $id
        ]);

        $res = ($stmt->rowCount() > 0) ? "ok" : "error";
        $msn = ($res === "ok") ? "" : "No se actualizó ningún registro";

    } catch (PDOException $e) {
        $res = "error";
        $msn = "Error BD: " . $e->getMessage();
    }

    echo json_encode([["res" => $res, "msn" => $msn]]);
}

else if ($opcion == "UPDATEAUX") {
    $id  = $_POST['id'];
    $aux = $_POST['aux'];
    $tip = $_POST['tip']; // 1: NOMINA | 2: OBRA

    try {
        if ($tip == 1) {
            $sql = "UPDATE tbl_liquidar_dias 
                    SET lid_auxilio = :aux, 
                        lid_usu_actualiz = :usuario, 
                        lid_fec_actualiz = :fecha 
                    WHERE lid_clave_int = :id";
        } else {
            $sql = "UPDATE tbl_liquidar_dias_obra 
                    SET lid_auxilio = :aux, 
                        lid_usu_actualiz = :usuario, 
                        lid_fec_actualiz = :fecha 
                    WHERE lid_clave_int = :id";
        }

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':aux'     => $aux,
            ':usuario' => $usuario,
            ':fecha'   => $fecha,
            ':id'      => $id
        ]);

        $res = ($stmt->rowCount() > 0) ? "ok" : "error";
        $msn = ($res === "ok") ? "" : "No se actualizó ningún registro";

    } catch (PDOException $e) {
        $res = "error";
        $msn = "Error BD: " . $e->getMessage();
    }

    echo json_encode([["res" => $res, "msn" => $msn]]);
}

else if ($opcion == "UPDATEBON") {
    $id  = $_POST['id'];
    $bon = $_POST['bon'];
    $tip = $_POST['tip']; // 1: NOMINA | 2: OBRA

    try {
        if ($tip == 1) {
            $sql = "UPDATE tbl_liquidar_dias 
                    SET lid_val_bonificacion = :bon, 
                        lid_usu_actualiz = :usuario, 
                        lid_fec_actualiz = :fecha 
                    WHERE lid_clave_int = :id";
        } else {
            $sql = "UPDATE tbl_liquidar_dias_obra 
                    SET lid_val_bonificacion = :bon, 
                        lid_usu_actualiz = :usuario, 
                        lid_fec_actualiz = :fecha 
                    WHERE lid_clave_int = :id";
        }

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':bon'     => $bon,
            ':usuario' => $usuario,
            ':fecha'   => $fecha,
            ':id'      => $id
        ]);

        $res = ($stmt->rowCount() > 0) ? "ok" : "error";
        $msn = ($res === "ok") ? "" : "No se actualizó ningún registro";

    } catch (PDOException $e) {
        $res = "error";
        $msn = "Error BD: " . $e->getMessage();
    }

    echo json_encode([["res" => $res, "msn" => $msn]]);
}


else if($opcion=="NUEVALIQUIDACION")
{
    $gen = new General();
    ?>
    <div class="col-md-12">
        <form id="frmliquidar" name="frmliquidar" data-parsley-validate="">
            <div class="row justify-content-center ">
                <div class="col-6 col-sm-6 col-md-2">
                    <label for="txtdesde">Desde:</label>
                    <input type="date" name="txtdesde" id="txtdesde" class="form-control form-control-sm" onchange="CRUDLIQUIDAR('CARGAREMPLEADOSLIQUIDAR', '') " required data-parsley-error-message="Seleccionar fecha desde" value="<?php echo $ini;?>">
                </div>
                <div class="col-6 col-sm-6 col-md-2">
                    <label for="txthasta">Hasta:</label>
                    <input type="date" name="txthasta" id="txthasta" class="form-control form-control-sm" onchange="CRUDLIQUIDAR('CARGAREMPLEADOSLIQUIDAR', '')" required data-parsley-error-message="Seleccionar fecha hasta" value="<?php echo $fin;?>">
                </div>
                <div class="col-md-2">
                    <label for="seltipo">Tipo:</label>
                    <select name="seltipo" id="seltipo" class="form-control form-control-sm selectpicker" onchange="CRUDLIQUIDAR('CARGAREMPLEADOSLIQUIDAR', '')" <?php echo $disa;?>>
                        <!-- <option value="1" <?php if($tip==1){ echo "selected"; }?>>Por empleado</option>
                        <option value="2" <?php if($tip==2){ echo "selected"; }?>>Por Obra</option> -->
                        <?php if($p121>0){ ?> <option value="1" <?php if($tip==1){ echo "selected"; }?>>Por empleado</option><?php } ?>
                        <?php if($p122>0){ ?> <option value="2" <?php if($tip==2){ echo "selected"; }?>>Por Obra</option><?php } ?>
                    </select>
                </div>
                <div class="col-6 col-sm-6 col-md-1 hide">
                    <label for="selano">Año</label>
                    <select <?php echo $disinp;?> name="selano" id="selano" class="form-control form-control-sm selectpicker" onchange="CRUDGENERAL('CARGARMES','','selmes','selano')" data-live-search="true" <?php echo $disa;?>>
                        <option value=""></option>
                        <?php
                        $gen -> cargarAnos(2020,date('Y'),$ano,"DESC",1);
                        ?>
    
                    </select>
                </div>
                <div class="col-6 col-sm-6 col-md-2 hide">
                    <label for="selmes">Mes</label>
                    <select <?php echo $disinp;?> name="selmes" id="selmes" class="form-control form-control-sm selectpicker" data-live-search="true"  onchange="CRUDLIQUIDAR('VEREMPLEADOSLIQUIDAR', '')" <?php echo $disa;?>>
                    <?php
                        $gen -> cargarmes(date('Y'),1,12,"","DESC",1);
                        ?>
                    </select>
                </div>
                <div class="col-md-3 <?php if($tip!="2"){ echo "hide"; }?>" id="divobra">
                    <label for="selobra">Obra</label>
                    <select id="selobra" name="selobra"  class="form-control form-control-sm selectpicker" onchange="CRUDLIQUIDAR('CARGAREMPLEADOSLIQUIDAR', '')" data-parsley-error-message="Seleccionar obra" data-parsley-errors-container="#msn-error1" <?php echo $disa;?>> 
                        <option value=""></option>
                        <?php
                        $gen -> cargarObras($obr,1);
                        ?>
                    </select>
                    <span id="msn-error2"></span>
                </div>
                <div class="col-md-3" id="divempleado">
                    <label for="selempleadoliquidar">Empleado</label>
                    <select id="selempleadoliquidar" name="selempleadoliquidar" multiple class="form-control form-control-sm selectpicker" data-actions-box="true" onchange="CRUDLIQUIDAR('VEREMPLEADOSLIQUIDAR', '')" required data-parsley-error-message="Seleccionar el empleado" data-parsley-errors-container="#msn-error1" <?php echo $disa;?>>
                    <option value=""></option>
                    <?php
                        if($emp>0)
                        {
                            $gen -> cargarEmpleados($emp);
                        }
                    ?>
                    </select>
                    <span id="msn-error1"></span>
                </div>
                <div class="col-md-2"><br>
                     <button type="button" class="btn btn-sm btn-success" onclick="CRUDLIQUIDAR('GENERARALLPROFORMA','', '')" >Generar Todas las Liquidaciones</button>
                </div>   
                 <div class="col-md-1"><br>
                     <button type="button" class="btn btn-sm btn-success" onclick="CRUDLIQUIDAR('EXPORTARLIQUIDACIONMASIVA','', '')" >Exportar</button>
                </div>   
            </div>
    
            <div class="row mt-2 justify-content-center">
                <!-- aca se cargar las planilla segun el tipo , empleados o  -->
                <div class="col-md-12" id="divplanilla"></div>
            </div>
        </form>
    </div>

    <script>
        $('#selempleadoliquidar').on('change',function(){
            $(this).parsley().validate();
        })
        $('#seltipo').on('change',function(){
            var ti = $(this).val();
            if(ti==1)
            {
                $('#divobra').addClass('hide');
                //$('#divempleado').removeClass('hide');
            }
            else
            {
                $('#divobra').removeClass('hide');
                //$('#divempleado').addClass('hide');
                //llamar funcion para cargar los empleados segun la obra
            }
        })
    </script>
    <?php
    echo "<script>INICIALIZARCONTENIDO();</script>";
    echo "<script>CRUDLIQUIDAR('VEREMPLEADOSLIQUIDAR', '');</script>";

}
else if ($opcion == "CARGAREMPLEADOSLIQUIDAR") {
    error_reporting(E_ALL);
    $ini  = $_POST['ini'];
    $fin  = $_POST['fin'];
    $tipo = $_POST['tip'];
    $obr  = $_POST['obr'];

    $whereObra = ($tipo == 2) ? " AND h.obr_clave_int = :obr" : "";

    $sql = "SELECT DISTINCT 
                u.usu_clave_int AS id, 
                CONCAT(u.usu_apellido, ' ', u.usu_nombre) AS nom, 
                u.usu_correo AS cor 
            FROM tbl_usuarios u 
            JOIN tbl_jornada j ON j.usu_clave_int = u.usu_clave_int 
            JOIN tbl_jornada_dias d ON d.jor_clave_int = j.jor_clave_int 
            JOIN tbl_jornada_horas h ON h.jod_clave_int = d.jod_clave_int 
            WHERE u.est_clave_int = 1 
              AND j.jor_estado = 1 
              AND d.jod_fecha BETWEEN :ini AND :fin 
              $whereObra 
            ORDER BY u.usu_apellido ASC";
   // echo $sql;
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':ini', $ini);
    $stmt->bindParam(':fin', $fin);
    if ($tipo == 2) {
        $stmt->bindParam(':obr', $obr, PDO::PARAM_INT);
    }
    $stmt->execute();

    $empleados = [];
    while ($dat = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $id  = $dat['id'];
        $nom = strtoupper($dat['nom']);
        $cor = $dat['cor'];

        // if ($tipo == 1) {
            // echo "<option data-subtext='" . htmlspecialchars($cor, ENT_QUOTES) . "' value='" . htmlspecialchars($id, ENT_QUOTES) . "'>" . htmlspecialchars($nom, ENT_QUOTES) . "</option>";
        // } else {
            $empleados[] = [
                "id"  => $id,
                "nom" => $nom,
                "cor" => $cor
            ];
        // }
    }

    // if ($tipo != 1) {
        echo json_encode($empleados);
    // }
}

else if ($opcion == "VEREMPLEADOSLIQUIDAR") {
    $emp  = $_POST['emp'] ?? [];
    $ini  = $_POST['ini'];
    $fin  = $_POST['fin'];
    $tipo = $_POST['tip'];
    $obr  = $_POST['obr'];

    $empList = is_array($emp) ? $emp : [$emp];
    $placeholders = implode(',', array_fill(0, count($empList), '?'));
    $params = array_merge([$ini, $fin], $empList);
    
    $sql = "SELECT DISTINCT 
                u.usu_clave_int AS id, 
                CONCAT(u.usu_apellido, ' ', u.usu_nombre) AS nom, 
                u.usu_correo AS cor 
            FROM tbl_usuarios u
            JOIN tbl_jornada j ON j.usu_clave_int = u.usu_clave_int
            JOIN tbl_jornada_dias d ON d.jor_clave_int = j.jor_clave_int
            JOIN tbl_jornada_horas h ON h.jod_clave_int = d.jod_clave_int
            WHERE u.est_clave_int = 1 
              AND j.jor_estado = 1 
              AND d.jod_fecha BETWEEN ? AND ? 
              AND (j.usu_clave_int IN ($placeholders))";

    if ($tipo == 2) {
        $sql .= " AND h.obr_clave_int = ?";
        $params[] = $obr;
    }

    $sql .= " ORDER BY u.usu_apellido ASC";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $empleadosData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $num = count($empleadosData);

    $empleados = [];
    if ($num > 0 && (($tipo == 1 && !empty($empList)) || $tipo == 2)) {
        $idinicial = $empleadosData[0]['id'];
        ?>
        <div id="smartwizard" class="mt-2">
            <ul class="nav">
                <?php
                foreach ($empleadosData as $i => $dat) {
                    $id  = $dat['id'];
                    $nom = strtoupper($dat['nom']);
                    $cor = $dat['cor'];

                    $stmtVa = $conn->prepare("SELECT DISTINCT l.liq_clave_int AS liquidacion, l.liq_estado 
                                              FROM tbl_liquidar_dias ld 
                                              JOIN tbl_liquidar l ON l.liq_clave_int = ld.liq_clave_int 
                                              WHERE ld.usu_clave_int = :id AND l.liq_inicio = :ini AND l.liq_fin = :fin");
                    $stmtVa->execute([':id' => $id, ':ini' => $ini, ':fin' => $fin]);
                    $numva = $stmtVa->rowCount();

                    $empleados[] = $id;
                    ?>
                    <li title="<?php echo $numva; ?>">
                        <a class="nav-link <?php echo ($numva > 0) ? 'generate' : ''; ?>" 
                           data-empleado="<?php echo $id; ?>" 
                           data-target="#step-<?php echo $i; ?>">
                            <?php echo $nom; ?>
                        </a>
                    </li>
                    <?php
                }
                ?>
            </ul>
            <div class="tab-content">
                <?php foreach ($empleados as $k => $empId) { ?>
                    <div id="step-<?php echo $k; ?>" data-empleado="<?php echo $empId; ?>" class="tab-pane invoice table-responsive" role="tabpanel"><?php echo $k; ?></div>
                <?php } ?>
            </div>
        </div>
        <script>
            localStorage.setItem('emp', '<?php echo $empleados[0]; ?>');
            localStorage.setItem('step', '0');
            $('#smartwizard').smartWizard({
                selected: 0,
                theme: 'progress',
                justified: false,
                darkMode: false,
                autoAdjustHeight: false,
                cycleSteps: false,
                backButtonSupport: true,
                enableURLhash: true,
                transition: {
                    animation: 'none',
                    speed: '400',
                    easing: ''
                },
                toolbarSettings: {
                    toolbarPosition: 'top',
                    toolbarButtonPosition: 'right',
                    showNextButton: false,
                    showPreviousButton: false,
                    toolbarExtraButtons: [
                        $('<button type="button"></button>').text('Generar Liquidación')
                            .addClass('btn btn-success')
                            .on('click', function () {
                                var emp = localStorage.getItem('emp');
                                CRUDLIQUIDAR('GENERARPROFORMA', '', emp);
                            })
                    ]
                },
                anchorSettings: {
                    anchorClickable: true,
                    enableAllAnchors: true,
                    markDoneStep: true,
                    markAllPreviousStepsAsDone: false,
                    removeDoneStepOnNavigateBack: false,
                    enableAnchorOnDoneStep: true
                },
                keyboardSettings: {
                    keyNavigation: false,
                    keyLeft: [37],
                    keyRight: [39]
                },
                lang: {
                    next: 'Siguiente',
                    previous: 'Anterior'
                }
            });

            $("#smartwizard").on("stepContent", function (e, anchorObject, stepIndex, stepDirection) {
                var emp = $('#step-' + stepIndex).attr('data-empleado');
                localStora('emp', emp);
                localStora('step', stepIndex);
                CRUDLIQUIDAR('VERDETALLELIQUIDAR', emp, stepIndex);
            });

            CRUDLIQUIDAR('VERDETALLELIQUIDAR', '<?php echo $idinicial; ?>', '0');
        </script>
        <?php
    } else {
        ?>
        <div class="error-content text-center">
            <h3><i class="fas fa-exclamation-triangle text-info"></i> ¡No se encontraron datos.</h3>
            <p></p>
        </div>
        <?php
    }
}

else if($opcion=="FILTROSLIQUIDAROBRA")
{
    $tip = $_POST['tip'];
    $gen = new General();
    ?>
      <div class="col-md-3">
        <label for="busempleado">Empleado</label>
        <select  id="busempleado" name="busempleado" multiple class="form-control form-control-sm selectpicker" onchange="CRUDLIQUIDAR('LISTALIQUIDAROBRA', '')" data-actions-box="true">        
        <?php
            $gen -> cargarEmpleados();
        ?>
        </select>
        <span id="msn-error1"></span>
    </div>
    <div class="col-md-3">
        <label for="busobra">Obra</label>
        <select title="Seleccionar obra" id="busobra" name="busobra"  class="form-control form-control-sm selectpicker" onchange="CRUDLIQUIDAR('LISTALIQUIDAROBRA', '')" >            
            <?php
            $gen -> cargarObras("",1);
            ?>
        </select>
    </div>
    <div class="col-md-3 hide">
        <label for="busano">Año</label>
        <select name="busano" id="busano" class="form-control form-control-sm selectpicker" onchange="CRUDLIQUIDAR('LISTALIQUIDAROBRA', '');">
            <option value=""></option>
            <?php           
            $gen -> selSelect(2020,date('Y'));
            ?>
        </select>
    </div>
    <div class="col-md-3">
        <label for="busperiodo">Periodos</label>
        <select title="Seleccionar Periodo" name="busperiodo" id="busperiodo" class="form-control form-control-sm selectpicker" onchange="CRUDLIQUIDAR('LISTALIQUIDAROBRA', '');">
           
            <?php           
            $gen -> cargarPeriodos("","",1);
            ?>
        </select>
    </div>
    <?php if($tip=="Pendientes"){ ?>
    <div class="col-md-2"><br>
        <button type="button" id="btnliquidarobra" class="btn btn-success btn-sm" onclick="liquidarObra()">Liquidar Obra</button>
    </div>    
    <?php
    }
    echo "<script>INICIALIZARCONTENIDO();</script>";
    echo "<script>CRUDLIQUIDAR('LISTALIQUIDAROBRA', '');</script>";
}
else if($opcion=="LISTALIQUIDAROBRA")
{
    $tip = $_POST['tip'];
    ?>
    <div class="col-md-12 table-responsive">
    <?php
    if($tip=="Pendientes")
    {
        // liq_tipo  = 2 and lio_clave_int<=0
        ?>        
        <table class="table table-bordered" id="tbLiquidacionesObra" style="width:100%" >
            <thead>
                <tr> 
                    <th class="dt-head-center bg-terracota" style="width:20px"><input name="select_all" value="1" type="checkbox"></th>
                    <th class="dt-head-center bg-terracota" style="width:20px"></th>
                    <th class="dt-head-center bg-terracota">N°</th>
                    <th class="dt-head-center bg-terracota">Obra</th>
                    <th class="dt-head-center bg-terracota">Cenco</th>
                    <th class="dt-head-center bg-terracota">Empleado</th>
                    <th class="dt-head-center bg-terracota">Fecha Creación</th>
                    <th class="dt-head-center bg-terracota">Creada Por</th>
                    <th class="dt-head-center bg-terracota">Tipo</th>
                    <th class="dt-head-center bg-terracota">Desde</th>
                    <th class="dt-head-center bg-terracota">Hasta</th>
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
                    <th></th>
                    <th></th>
                    <th></th>               
                </tr>
            </tfoot>
        </table>
    
        <script src="jsdatatable/jornadas/jsliquidacionesobra.js?<?php echo time();?>"></script>
        <?php
    }
    else
    {
    ?>
         <table  id="tbLiquidarObra" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th class="dt-head-center bg-terracota"></th>
                    <th class="dt-head-center bg-terracota">Codigo</th>
                    <th class="dt-head-center bg-terracota">Obra</th>
                    <th class="dt-head-center bg-terracota">Cencos</th>
                    <th class="dt-head-center bg-terracota">Horas Maquina</th>
                    <th class="dt-head-center bg-terracota">Horas por Contrato</th>
                    <th class="dt-head-center bg-terracota">Horas Extras Maquina</th>
                    <th class="dt-head-center bg-terracota">Valor Hora</th>
                    <th class="dt-head-center bg-terracota">Total H.E</th>
                    <th class="dt-head-center bg-terracota">Observación</th>
                    <th class="dt-head-center bg-terracota">Generada Por</th>
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
                    <th></th>
                    <th></th>
                    <th></th>
                </tr>
            </tfoot>
         </table>
         <script src="jsdatatable/jornadas/jsliquidarobra.js?<?php echo time();?>"></script>
    <?php
    }
    ?>
    </div>
    <?PHP
}
else if($opcion=="LIQUIDAROBRA")
{
    $liquidaciones = $_POST['liquidaciones'];
    $liquidaciones = str_replace("-", ",", $liquidaciones);
    $obr = $_POST['obr'];
    $ini = $_POST['ini'];
    $fin = $_POST['fin'];

    //ARMAR INFORME CONSOLIDADO DE TODAS LAS LIQUIDACION A UNIFICAR PARA LIQUIDAR A LA OBRA

    $sqlhoras = "select SUM(lid_hedo) tothedo,SUM(lid_hdf) tothdf,SUM(lid_hedf) tothedf,SUM(lid_heno) totheno,SUM(lid_henf) tothenf,SUM(lid_rn) totrn, SUM(lid_hnf) tothnf, SUM(lid_rd) totrd, SUM(lid_horas) totalhoras,SUM(lid_horas_maquina) totalmaquina, SUM(lid_permisos) totpermisos,SUM(lid_val_bonificacion) totbon, sum(CASE WHEN lid_procede=1 and lid_alimentacion=1 THEN lid_val_alimentacion ELSE 0 END) totali,  sum(lid_auxilio) totaux, l.liq_clave_int , nameUser(l.usu_clave_int) Empleado, o.obr_nombre Obra, o.obr_cencos Cencos, o.obr_hr_mes HorasContrato, o.obr_vr_maquina ValorHora, u.usu_documento Cedula, l.liq_salario vrhor from tbl_liquidar l join  tbl_liquidar_dias_obra lo on lo.liq_clave_int = l.liq_clave_int JOIN tbl_obras o on o.obr_clave_int = l.obr_clave_int join tbl_usuarios u on u.usu_clave_int = l.usu_clave_int where l.liq_clave_int  in(".$liquidaciones.") group by l.liq_clave_int ";
    $stmtHoras = $conn->prepare($sqlhoras);
    $stmtHoras->execute();
    $numhoras = $stmtHoras->rowCount();
    
    //ECHO $sqlhoras;
    ?>
    <div class="row">
        <div class="col-md-12">
            <table  id="tbLiquidacionesObra" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th class="dt-head-center bg-terracota">Empleado</th>
                        <th class="dt-head-center bg-terracota">Cedula</th>
                        <th class="dt-head-center bg-terracota">Obra</th>
                        <th class="dt-head-center bg-terracota">Cencos</th>
                        <th class="dt-head-center bg-terracota">Horas Maquina</th>                    
                    </tr>
                </thead>  
                <tbody>
                    <?php 
                    $totalHM = 0;
                    $valorhora = 0;
                    $horascontrato = 0;
        
                    $totalhedo  = 0;
                    $totalhdf   = 0;
                    $totalhedf  = 0;
                    $totalheno  = 0;
                    $totalhenf  = 0;
                    $totalrn    = 0;
                    $totalhnf   = 0;
                    $totalrd    = 0;
                    $totalare   = 0;
        
                    $vrhor = 0;
                    
        
                    while ($dath = $stmtHoras->fetch(PDO::FETCH_ASSOC)) {
                        $empleado = $dath['Empleado'];
                        $cedula = $dath['Cedula'];
                        $vrhor = $dath['vrhor'];
                        $obra = $dath['Obra'];
                        $cenco = $dath['Cencos'];
                        $totalhorasmaquina = $dath['totalmaquina'];
                        $horascontrato = $dath['HorasContrato'];
                        $valorhora = $dath['ValorHora']; 
                        $totalHM+=$totalhorasmaquina;             
                        $thedo  = $dath['tothedo'];
                        $thdf   = $dath['tothdf'];
                        $thedf  = $dath['tothedf'];
                        $theno  = $dath['totheno'];
                        $thenf  = $dath['tothenf'];
                        $trn    = $dath['totrn'];
                        $thnf   = $dath['tothnf'];
                        $trd    = $dath['totrd'];
                        $tare   = $dath['totpermisos'];
                        $thedo  = ($tare>0)? $thedo-$tare: $thedo;
        
                        $totalhedo  += $thedo;
                        $totalhdf   += $thdf;
                        $totalhedf  += $thedf;
                        $totalheno  += $theno;
                        $totalhenf  += $thenf;
                        $totalrn    += $trn;
                        $totalhnf   += $thnf;
                        $totalrd    += $trd;
                        $totalare   += $tare;
                    
                        ?>
                      
                        <tr>
                            <td class=""><?php echo $empleado;?></td>
                            <td class=""><?php echo $cedula;?></td>
                            <td class=""><?php echo $obra;?></td>
                            <td class=""><?php echo $cenco;?></td>
                            <td class="text-center"><?php echo number_format($totalhorasmaquina,2);?></td>                   
                        </tr>            
                        <?php
                    }
        
                    $totalhem = $totalHM - $horascontrato;
                    $totalhem = ($totalhem<0)?0:$totalhem;
        
                    $totalmaquina = $valorhora * $totalhem; // VALOR TOTAL HORAS EXTRAS MAQUINA
                    $toh = new General();
                    //CALCULO TOTALES INICI
                    $hedo = $toh->getPorcentaje('HEDO');
                    $hdf  = $toh->getPorcentaje('HDF');
                    $hedf = $toh->getPorcentaje('HEDF');
                    $heno = $toh->getPorcentaje('HENO');
                    $henf = $toh->getPorcentaje('HENF');
                    $rn   = $toh->getPorcentaje('RN');
                    $hnf  = $toh->getPorcentaje('HNF');
                    $rd   = $toh->getPorcentaje('RD');
                    $are  = $toh->getPorcentaje('ARE');
        
                    $tithedo = $hedo['hor_descripcion'];  
                    $codhedo = $hedo['hor_codigo'];  
                    $porhedo = $hedo['hor_porcentaje'];        
                    $vhhedo  = $porhedo * $vrhor;
                    $tothedo = $vhhedo*$totalhedo;
        
                    $tithdf = $hdf['hor_descripcion'];  
                    $codhdf = $hdf['hor_codigo'];  
                    $porhdf = $hdf['hor_porcentaje'];        
                    $vhhdf  = $porhdf * $vrhor;
                    $tothdf = $vhhdf*$totalhdf;
        
                    $tithedf = $hedf['hor_descripcion'];  
                    $codhedf = $hedf['hor_codigo'];  
                    $porhedf = $hedf['hor_porcentaje'];        
                    $vhhedf  = $porhedf * $vrhor;
                    $tothedf = $vhhedf*$totalhedf;
        
                    $titheno = $heno['hor_descripcion'];  
                    $codheno = $heno['hor_codigo'];  
                    $porheno = $heno['hor_porcentaje'];        
                    $vhheno  = $porheno * $vrhor;
                    $totheno = $vhheno*$totalheno;
        
                    $tithenf = $henf['hor_descripcion']; 
                    $codhenf = $henf['hor_codigo'];   
                    $porhenf = $henf['hor_porcentaje'];        
                    $vhhenf  = $porhenf * $vrhor;
                    $tothenf = $vhhenf*$totalhenf;
        
                    $titrn = $rn['hor_descripcion'];  
                    $codrn = $rn['hor_codigo'];  
                    $porrn = $rn['hor_porcentaje'];        
                    $vhrn  = $porrn * $vrhor;
                    $totrn = $vhrn*$totalrn;
        
                    $tithnf = $hnf['hor_descripcion']; 
                    $codhnf = $hnf['hor_codigo'];   
                    $porhnf = $hnf['hor_porcentaje'];        
                    $vhhnf  = $porhnf * $vrhor;
                    $tothnf = $vhhnf*$totalhnf;
        
                    $titrd  = $rd['hor_descripcion'];  
                    $codrd = $rd['hor_codigo'];  
                    $porrd  = $rd['hor_porcentaje'];        
                    $vhrd   = $porrd * $vrhor;
                    $totrd  = $vhrd*$totalrd;
        
                    $titare  = $are['hor_descripcion'];  
                    $codare = $are['hor_codigo'];  
                    $porare  = $are['hor_porcentaje'];        
                    $vhare   = $porare * $vrhor;
                    $totare  = $vhare*$totalare;
        
                    $totalhoras = $totalhedo + $totalhdf + $totalhedf + $totalheno + $totalhenf + $totalrn + $totalhnf + $totalrd + $totalhem + $totalare;
                    $total =  $tothedo + $tothdf + $tothedf + $totheno + $tothenf + $totrn + $tothnf + $totrd + $totare;
        
                    $dscto = 0;
                    $iva = 0;
                    $rtefuente = 0;
                    $rteiva = 0;
                    $neto = $total + $iva  - $dscto - $rtefuente - $rteiva;
                    
                    ?>
                </tbody>
            </table>
            <table class="table table-bordered table-striped" id="tbHorasObra" data-horas="<?php echo $horascontrato;?>" data-maquina = "<?php echo $totalHM;?>" data-extras="<?php echo $totalhem;?>" data-valor-maquina="<?php echo $valorhora;?>" data-valor-hora="<?php echo $vrhor;?>">
                <thead>
                    <tr>
                        <th class="pt-0 pb-0">VALOR HORA</th>
                        <th class="pt-0 pb-0"><?php echo "$".number_format($vrhor,2);?></th>
                    </tr>
                    <tr>
                        <th colspan="5" class="p-1"></th>
                    </tr>
                    <tr>
                        <th class="pt-0 pb-0 bg-primary text-center">DESCRIPCION</th>
                        <th class="pt-0 pb-0 bg-primary text-center"># HORAS</th>
                        <th class="pt-0 pb-0 bg-primary text-center">%</th>
                        <th class="pt-0 pb-0 bg-primary text-center">VALOR</th>
                        <th class="pt-0 pb-0 bg-primary text-center">TOTAL</th>            
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="font-weight-bold pt-0 pb-0"><?php echo $tithedo; ?></td>
                        <td class="text-center pt-0 pb-0"><?php echo number_format($totalhedo,2);?></td>
                        <td class="text-center pt-0 pb-0"><?php echo number_format($porhedo*100,0) ?>%</td>
                        <td class="text-right pt-0 pb-0"><?php echo "$".number_format($vhhedo,2);?></td>
                        <td class="text-right pt-0 pb-0"><?php echo "$".number_format($tothedo,2);?></td>            
                    </tr>
                    <tr>
                        <td class="font-weight-bold pt-0 pb-0"><?php echo $tithdf; ?></td>
                        <td class="text-center pt-0 pb-0"><?php echo number_format($totalhdf,2);?></td>
                        <td class="text-center pt-0 pb-0"><?php echo number_format($porhdf*100,0) ?>%</td>
                        <td class="text-right pt-0 pb-0"><?php echo "$".number_format($vhhdf,2);?></td>
                        <td class="text-right pt-0 pb-0"><?php echo "$".number_format($tothdf,2);?></td>            
                    </tr>
                    <tr>
                        <td class="font-weight-bold pt-0 pb-0"><?php echo $tithedf; ?></td>
                        <td class="text-center pt-0 pb-0"><?php echo number_format($totalhedf,2);?></td>
                        <td class="text-center pt-0 pb-0"><?php echo number_format($porhedf*100,0) ?>%</td>
                        <td class="text-right pt-0 pb-0"><?php echo "$".number_format($vhhedf,2);?></td>
                        <td class="text-right pt-0 pb-0"><?php echo "$".number_format($tothedf,2);?></td>            
                    </tr>
                    <tr>
                        <td class="font-weight-bold pt-0 pb-0"><?php echo $titheno; ?></td>
                        <td class="text-center pt-0 pb-0"><?php echo number_format($totalheno,2);?></td>
                        <td class="text-center pt-0 pb-0"><?php echo number_format($porheno*100,0) ?>%</td>
                        <td class="text-right pt-0 pb-0"><?php echo "$".number_format($vhheno,2);?></td>
                        <td class="text-right pt-0 pb-0"><?php echo "$".number_format($totheno,2);?></td>            
                    </tr>
                    <tr>
                        <td class="font-weight-bold pt-0 pb-0"><?php echo $tithenf; ?></td>
                        <td class="text-center pt-0 pb-0"><?php echo number_format($totalhenf,2);?></td>
                        <td class="text-center pt-0 pb-0"><?php echo number_format($porhenf*100,0) ?>%</td>
                        <td class="text-right pt-0 pb-0"><?php echo "$".number_format($vhhenf,2);?></td>
                        <td class="text-right pt-0 pb-0"><?php echo "$".number_format($tothenf,2);?></td>            
                    </tr>
                    <tr>
                        <td class="font-weight-bold pt-0 pb-0"><?php echo $titrn; ?></td>
                        <td class="text-center pt-0 pb-0"><?php echo number_format($totalrn,2);?></td>
                        <td class="text-center pt-0 pb-0"><?php echo number_format($porrn*100,0) ?>%</td>
                        <td class="text-right pt-0 pb-0"><?php echo "$".number_format($vhrn,2);?></td>
                        <td class="text-right pt-0 pb-0"><?php echo "$".number_format($totrn,2);?></td>            
                    </tr>
                    <tr>
                        <td class="font-weight-bold pt-0 pb-0"><?php echo $tithnf; ?></td>
                        <td class="text-center pt-0 pb-0"><?php echo number_format($totalhnf,2);?></td>
                        <td class="text-center pt-0 pb-0"><?php echo number_format($porhnf*100,0) ?>%</td>
                        <td class="text-right pt-0 pb-0"><?php echo "$".number_format($vhhnf,2);?></td>
                        <td class="text-right pt-0 pb-0"><?php echo "$".number_format($tothnf,2);?></td>            
                    </tr>
                    <tr>
                        <td class="font-weight-bold pt-0 pb-0"><?php echo $titrd; ?></td>
                        <td class="text-center pt-0 pb-0"><?php echo number_format($totalrd,2);?></td>
                        <td class="text-center pt-0 pb-0"><?php echo number_format($porrd*100,0) ?>%</td>
                        <td class="text-right pt-0 pb-0"><?php echo "$".number_format($vhrd,2);?></td>
                        <td class="text-right pt-0 pb-0"><?php echo "$".number_format($totrd,2);?></td>            
                    </tr>
                    <?php if($totalare>0){ ?>
                    <tr>
                        <td class="font-weight-bold pt-0 pb-0"><?php echo $titare; ?></td>
                        <td class="text-center pt-0 pb-0"><?php echo number_format($totalare,2);?></td>
                        <td class="text-center pt-0 pb-0"><?php echo number_format($porare*100,0) ?>%</td>
                        <td class="text-right pt-0 pb-0"><?php echo "$".number_format($vhare,2);?></td>
                        <td class="text-right pt-0 pb-0"><?php echo "$".number_format($totare,2);?></td>            
                    </tr>
                    <?php 
                    }
                    ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th class="pt-0 pb-0 font-weight-bold bg-primary text-center" colspan="4">TOTAL</th>
                        <th class="pt-0 pb-0 font-weight-bold text-right bg-primary"><?php echo "$".number_format($total,2);?></th>            
                    </tr>
                
                    <tr><th colspan="5"></th></tr>
                    <tr>
                        <th class="pt-0 pb-0">TOTAL HORAS MAQUINA</th>
                        <th class="pt-0 pb-0"><?php echo number_format($totalHM,2);?></th>
                        <th class="pt-0 pb-0 text-center align-middle" colspan="2" rowspan="2">$</th> 
                        <th class="pt-0 pb-0 text-center align-middle" colspan="2" rowspan="2">TOTAL H.E</th>
                    </tr>
                    <tr>
                        <th class="pt-0 pb-0">HORAS POR CONTRATO</th>
                        <th class="pt-0 pb-0"><?php echo number_format($horascontrato,0);?></th>
                    </tr>
                    <tr>
                        <th class="pt-0 pb-0">HORAS EXTRA MÁQUINA</th>
                        <th class="pt-0 pb-0"><?php echo number_format($totalhem,2);?></th> 
                        <th class="pt-0 pb-0 text-center" colspan="2" ><?php echo "$".number_format($valorhora,2);?></th> 
                        <th class="pt-0 pb-0 text-center" colspan="2" ><?php echo "$".number_format($totalmaquina,2);?></th> 
                    </tr>
                </tfoot>  
            </table>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <textarea id="txtobservacionliquidacion" class="form-control form-control-sm" rows="1"></textarea>
        </div>
    </div>
    <?PHP
    echo "<script>INICIALIZARCONTENIDO();</script>";
}
else if ($opcion == "GUARDARLIQUIDAROBRA") {
    $liquidaciones      = $_POST['liquidaciones'];
    $obr                = $_POST['obr'];
    $ini                = $_POST['ini'];
    $fin                = $_POST['fin'];
    $horascontrato      = $_POST['horascontrato'];
    $horasmaquina       = $_POST['horasmaquina'];
    $extras             = $_POST['extras'];
    $vrhoramaquina      = $_POST['vrhoramaquina'];
    $vrhoraempleado     = $_POST['vrhoraempleado'];
    $obs                = $_POST['obs'];
    $fecha              = date('Y-m-d'); // o tu variable de fecha

    try {
        // INICIAR TRANSACCIÓN
        $conn->beginTransaction();

        // INSERTAR LIQUIDACIÓN OBRA
        $stmtInsert = $conn->prepare("
            INSERT INTO tbl_liquidar_obras (
                obr_clave_int, lio_horas_maquina, lio_horas_contrato, lio_horas_extras,
                lio_vr_hora_maquina, lio_vr_hora, lio_inicio, lio_fin, usu_liquidador,
                lio_fec_creacion, lio_usu_actualiz, lio_fec_actualiz, lio_observacion
            ) VALUES (
                :obr, :maquina, :contrato, :extras, :vrmaquina, :vrempleado, :ini, :fin,
                :usuario, :fecha, :usuario, :fecha, :obs
            )");

        $stmtInsert->execute([
            ':obr'        => $obr,
            ':maquina'    => $horasmaquina,
            ':contrato'   => $horascontrato,
            ':extras'     => $extras,
            ':vrmaquina'  => $vrhoramaquina,
            ':vrempleado' => $vrhoraempleado,
            ':ini'        => $ini,
            ':fin'        => $fin,
            ':usuario'    => $usuario,
            ':fecha'      => $fecha,
            ':obs'        => $obs
        ]);

        $idli = $conn->lastInsertId();
        $codigo = sprintf('%010d', $idli);

        // ACTUALIZAR LAS LIQUIDACIONES ASOCIADAS
        $liquidaciones = str_replace("-", ",", $liquidaciones);
        $stmtUpdate = $conn->prepare("
            UPDATE tbl_liquidar 
            SET lio_clave_int = :idli 
            WHERE liq_clave_int IN ($liquidaciones)
        ");
        $stmtUpdate->execute([':idli' => $idli]);

        // ACTUALIZAR EL CÓDIGO DE LA LIQUIDACIÓN DE OBRA
        $stmtCod = $conn->prepare("
            UPDATE tbl_liquidar_obras 
            SET lio_codigo = :codigo 
            WHERE lio_clave_int = :idli
        ");
        $stmtCod->execute([
            ':codigo' => $codigo,
            ':idli'   => $idli
        ]);

        // INSERTAR PORCENTAJES DE HORAS
        $stmtHoras = $conn->prepare("
            INSERT INTO tbl_liquidar_obras_horas (lio_clave_int, hor_clave_int, lih_porcentaje)
            SELECT :idli, hor_clave_int, hor_porcentaje 
            FROM tbl_horas 
            WHERE est_clave_int = 1
        ");
        $stmtHoras->execute([':idli' => $idli]);

        $conn->commit();

        $res = "ok";
        $msn = "Liquidación de Obra con código N° $codigo generada correctamente";

    } catch (PDOException $e) {
        $conn->rollBack();
        $res = "error";
        $msn = "Error al generar liquidación de obra: " . $e->getMessage();
    }

    echo json_encode([["res" => $res, "msn" => $msn]]);
}