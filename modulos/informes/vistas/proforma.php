<?php


setlocale(LC_TIME, 'es_CO.UTF-8'); 
date_default_timezone_set('America/Bogota');
$fecha  = date("Y/m/d H:i:s");
$diad = date('d',strtotime($fecha));
setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'esp');
$mesd = date("F", strtotime($fecha));
$anod = date("Y",strtotime($fecha));
$diaenvio = $diad." de ".$mesd." de ".$anod;
//$periodo = date("F", strtotime($ano."-".$mes."-01"));

//CONSULTA DATOS LIQUIDACIONES
$sqlLiquidacion = "SELECT u.usu_nombre nom, u.usu_apellido ape, u.usu_correo cor, l.liq_fecha fec, l.liq_inicio ini, l.liq_fin fin, l.liq_tipo tip, l.liq_hr_mes hrmes, liq_salario vrhor, o.obr_nombre nomobr, o.obr_cencos cenco, liq_consecutivo cons, o.obr_clave_int obr, liq_vr_maquina vhma, liq_codigo codigo FROM tbl_liquidar l join tbl_usuarios u on u.usu_clave_int = l.usu_clave_int left outer join tbl_obras o on o.obr_clave_int = l.obr_clave_int WHERE l.liq_clave_int = '".$id."'";

$stmtLiquidacion = $conn->prepare($sqlLiquidacion);
$stmtLiquidacion->execute();
$datl = $stmtLiquidacion->fetch(PDO::FETCH_ASSOC);

$nom    = $datl['nom'];
$ape    = $datl['ape'];
$cor    = $datl['cor'];
$fec    = $datl['fec'];
$ini    = $datl['ini'];
$fin    = $datl['fin'];
$hrmes  = $datl['hrmes'];
$vrhor  = $datl['vrhor'];
$obr    = $datl['obr'];
$nomobr = $datl['nomobr'];
$cenco  = $datl['cenco'];
$tip    = $datl['tip'];
$cons   = $datl['cons'];
$vhma   = $datl['vhma'];
$codigo = $datl['codigo'];

$salariobase = $hrmes*$vrhor;

if($tip==1)
{
    $archivo = 'Proforma Horas Extras x Empleado.pdf';//Cambiar por nuevo campo en BD para fecha cierre de la intervencion
    $titulo  = "Proforma Horas Extras x Empleado";
    $sqldias = "SELECT lid_fecha fec, lid_hi_man hiam, lid_hf_man hfam , lid_hi_tar hipm,  lid_hf_tar hfpm,lid_horas, lid_hedo, lid_hdf, lid_hedf, lid_heno, lid_henf, lid_rn, lid_hnf, lid_rd, lid_horario,lid_alimentacion,lid_val_alimentacion, lid_compensado, lid_remunerado, lid_observacion, diaHabil(lid_fecha) fes, WEEKDAY(lid_fecha) dia, lid_permisos permisos, lid_val_bonificacion,  (CASE WHEN lid_procede=1 and lid_alimentacion=1 THEN lid_val_alimentacion ELSE 0 END) AS alimentacion, lid_auxilio from tbl_liquidar_dias where liq_clave_int = '".$id."' order by fec";

    $sqlhoras = "select sum(lid_hedo) tothedo,sum(lid_hdf) tothdf,sum(lid_hedf) tothedf,sum(lid_heno) totheno,sum(lid_henf) tothenf,sum(lid_henf) tothenf,sum(lid_rn) totrn, sum(lid_hnf) tothnf, sum(lid_rd) totrd, sum(lid_horas) totalhoras, sum(lid_permisos) totpermisos, sum(lid_val_bonificacion) totbon, sum(CASE WHEN lid_procede=1 and lid_alimentacion=1 THEN lid_val_alimentacion ELSE 0 END) totali,  sum(lid_auxilio) totaux  from tbl_liquidar_dias where liq_clave_int = '".$id."'";

    
}
else
{
    $archivo = 'Proforma Horas Extras x Obra.pdf';
    $titulo  = "Proforma Horas Extras x Obra";
    $sqldias = "SELECT lid_fecha fec, lid_hi_man hiam, lid_hf_man hfam , lid_hi_tar hipm,  lid_hf_tar hfpm,lid_horas, lid_hedo, lid_hdf, lid_hedf, lid_heno, lid_henf, lid_rn, lid_hnf, lid_rd, lid_horario,lid_alimentacion,lid_val_alimentacion, lid_compensado, lid_remunerado,lid_observacion, diaHabil(lid_fecha) fes, WEEKDAY(lid_fecha) dia, lid_permisos permisos, lid_val_bonificacion,  (CASE WHEN lid_procede=1 and lid_alimentacion=1 THEN lid_val_alimentacion ELSE 0 END) AS alimentacion, lid_auxilio from tbl_liquidar_dias_obra where liq_clave_int = '".$id."' order by fec";

    $sqlhoras = "select sum(lid_hedo) tothedo,sum(lid_hdf) tothdf,sum(lid_hedf) tothedf,sum(lid_heno) totheno,sum(lid_henf) tothenf,sum(lid_rn) totrn, sum(lid_hnf) tothnf, sum(lid_rd) totrd, sum(lid_horas) totalhoras,sum(lid_horas_maquina) totalmaquina, sum(lid_permisos) totpermisos,SUM(lid_val_bonificacion) totbon, sum(CASE WHEN lid_procede=1 and lid_alimentacion=1 THEN lid_val_alimentacion ELSE 0 END) totali,  sum(lid_auxilio) totaux from tbl_liquidar_dias_obra where liq_clave_int = '".$id."'";
}



$stmtHoras = $conn->prepare($sqlhoras);
$stmtHoras->execute();
$dath = $stmtHoras->fetch(PDO::FETCH_ASSOC);

$totalhedo  = $dath['tothedo'];
$totalhdf   = $dath['tothdf'];
$totalhedf  = $dath['tothedf'];
$totalheno  = $dath['totheno'];
$totalhenf  = $dath['tothenf'];
$totalrn    = $dath['totrn'];
$totalhnf   = $dath['tothnf'];
$totalrd    = $dath['totrd'];
$totalare   = $dath['totpermisos'];

$totalhedo  = ($totalare > 0) ? $totalhedo - $totalare : $totalhedo;

$totalbon = $dath['totbon'];
$totalali = $dath['totali'];
$totalaux = $dath['totaux'];


$totalhoraslaboradas = $dath['totalhoras']; //TOTAL HORAS LABORADAS
$totalhorasmaquina = $dath['totalmaquina'];
$totalhem = $totalhorasmaquina - $hrmes; // TOTAL EXTRAS MAQUINA
$totalhem = ($totalhem<0)?0:$totalhem;
$totalmaquina = $vhma * $totalhem; // VALOR TOTAL HORAS EXTRAS MAQUINA

if($tip==1){ $totalhem = 0; } // SI TIPO ES NOMINA ESTE VALOR ES CERO



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
$total =  $tothedo + $tothdf + $tothedf + $totheno + $tothenf + $totrn + $tothnf + $totrd + $totalmaquina + $totare;

$dscto = 0;
$iva = 0;
$rtefuente = 0;
$rteiva = 0;
$neto = $total + $iva  - $dscto - $rtefuente - $rteiva;

//CALCULO TOTALES FIN

//CONSULTA DATOS PLANITAS ASOCIADAS A DICHA LIQUIDACION

?>
<style>
    .padre {
    text-align: center;
    }
    th {
    border:1px solid;
    }
    .imgpro{
        width:auto;
        height:300px;
    }
    td{
        border:1px solid;
    }

    .titulo1{
        background-color:#90b7f0; 
    }
    .ocul{
        display: none !important ;

    }
    .casilla1
    {
        border-width: 0px 1px 1px 1px; border-style: solid; border-color:#000;
    }
  .col-md-1, .col-md-2,.col-md-3,.col-md-4,.col-md-5,.col-md-6,.col-md-7.col-md-8,.col-md-9,.col-md-10,.col-md-11
  {
      position:relative
  }
  .col-md-12 {
    width: 100%;
  }
  .col-md-11 {
    width: 91.66666667%;
  }
  .col-md-10 {
    width: 83.33333333%;
  }
  .col-md-9 {
    width: 75%;
  }
  .col-md-8 {
    width: 66.66666667%;
  }
  .col-md-7 {
    width: 58.33333333%;
  }
  .col-md-6 {
    width: 50%;
  }
  .col-md-5 {
    width: 41.66666667%;
  }
  .col-md-4 {
    width: 33.33333333%;
  }
  .col-md-3 {
    width: 25%;
  }
  .col-md-2 {
    width: 16.66666667%;
  }
  .col-md-1 {
    width: 8.33333333%;
  }

  .panel
  {
    margin: 5px;
    box-shadow: 0px 3px 2px #aab2bd;
    text-align: left;
  }
  .input-contrato{
    border-width: 0px 0px 3px 0px !important;
    border-style: solid !important;
    text-decoration: underline
  }

  .border-bot{
   text-decoration: underline
  }

  .divider
  {
    height:20px;
    min-height:20px
  }
  .tdsin{
    border-width:0px;
    border-color:#FFF;
    vertical-align:middle
  }
  .tdcon{
    border-width:0px 0px 2px 0px;
    border-color:#000;
    vertical-align:middle;
    padding: 3px;
  }
  
  .align-middle{
    vertical-align:middle;
  }
  .valign-top
  {
    vertical-align:top;
  }
  .text-center
  {
    text-align: center;
  }

  .text-right
  {
    text-align: right;
  }
  /*../../../img/marcaagua.png*/

  .divcontenido {
    width: 190mm; /* 210mm - 5mm x 2 (margen A4) - 5mm x 2 (margen tabla) */
    margin: 5mm;
  }
  .bg-terracota
  {
    background-color: #d96427;
    color: black;
  }

  .radius
  {
    border-radius:5px
  }
  .td-gray
  {
    color:darkgray;
  }
  .bg-secondary
  {
    background-color: #6c757d;
    color: #fff;
  }
  .bg-primary {
    background-color: #007bff;
    color: #fff;
  }
  .bg-danger {
    background: red;
    color: #fff;
  }
  .p-0
  {
    padding: 0px
  }
  .bold
  {
    font-weight: bold;
  }

</style>
<page backimg="../../dist/img/Potenco-logo-mini.png" backtop="30mm" backbottom="10mm" backleft="10mm" backright = "20mm" style="font-size: 14px" backimgx="right" backimgy="5px" backimgw="20%" footer="date;time;page;" >
    <page_header> 
        <span  style="position: absolute; right: 0;top:15px;width: 100px;text-align:right">N° <strong><?PHP echo $codigo;?></strong></span>
       
        <table style="width:700px">
        <tr><td class="tdsin">POTENCO S.A.S</td></tr>
        <tr class="row">
          <td class="tdsin td-gray">DIRECCION:</td>
          <td class="tdsin td-gray" colspan="3"> CALLE 7 SUR 51 A 21 INT 118</td>
        </tr>
        <tr class="row">
          <td class="tdsin td-gray">NIT:</td>
          <td class="tdsin td-gray">900563153</td>
          <td class="tdsin td-gray">CIUDAD:</td>
          <td class="tdsin td-gray">MEDELLÍN</td>
        </tr>
        <tr class="row">
          <td class="tdsin td-gray">TEL:</td>
          <td class="tdsin td-gray" colspan="3">4488582</td>
        </tr>
        </table>
       
    </page_header>
    <div style="width:700px">
        <table style="width:700px" border=0>        
         
          <tr><td class="tdsin" colspan="2">MEDELLIN, <?php echo strtoupper($diaenvio); ?></td></tr>
        
          <tr><td class="tdsin" colspan="2">EMPLEADO: <?php echo $nom." ".$ape;  ?></td></tr>
          <?php if($tip==2){ ?>
          <tr>
            <td class="tdsin">OBRA: <?php echo $nomobr; ?></td>
            <td class="tdsin">CENCOS: <?php echo $cenco; ?></td>
          </tr>
          <?php } ?>
          <tr><td class="tdsin" colspan="2"><?php echo strtoupper($titulo); ?></td></tr>
          <tr><td class="tdsin" colspan="2">PERIODO: <?php echo $ini. " A ".$fin; ?></td></tr>
          <tr><td class="tdsin divider" colspan="2"></td></tr>
          <tr><td class="tdsin" colspan="2"></td></tr>
        </table>
        <table style="width:700px; font-size:11px" border=0 cellspacing="0" cellspading="0">
            <thead>
              
              <tr>
                <th class="text-center tdsin">REFERENCIA</th>
                <th class="text-center tdsin">DESCRIPCION</th>
                <th class="text-center tdsin">UNIDAD</th>
                <th class="text-center tdsin">CANTIDAD</th>
                <th class="text-center tdsin">PRECIO</th>
                <th class="text-center tdsin">% DSCTO</th>
                <th class="text-center tdsin">% IVA</th>
                <th class="text-center tdsin">% IMPO</th>
                <th class="text-center tdsin">VALOR</th>
              </tr>
              <tr><th class="divider tdsin" colspan="9"></th></tr>
            </thead>
            <tbody>
                <tr>
                  <td class="text-center tdsin"><?php echo $codhedo;?></td>
                  <td class="text-left tdsin"><?php echo $tithedo;?></td>
                  <td class="text-center tdsin">HORA</td>
                  <td class="text-center tdsin"><?php echo number_format($totalhedo,2);?></td>
                  <td class="text-right tdsin"><?php echo "$".number_format($vhhedo,2);?></td>
                  <td class="text-center tdsin"><?php echo number_format(0,2);?>%</td>
                  <td class="text-center tdsin"><?php echo number_format(19,2);?>% </td>
                  <td class="text-center tdsin"><?php echo number_format(0,2);?>%</td>
                  <td class="text-right tdsin"><?php echo "$".number_format($tothedo,2);?></td>                  
                </tr>
                <tr>
                  <td class="text-center tdsin"><?php echo $codhdf;?></td>
                  <td class="text-left tdsin"><?php echo $tithdf;?></td>
                  <td class="text-center tdsin">HORA</td>
                  <td class="text-center tdsin"><?php echo number_format($totalhdf,2);?></td>
                  <td class="text-right tdsin"><?php echo "$".number_format($vhhdf,2);?></td>
                  <td class="text-center tdsin"><?php echo number_format(0,2);?>%</td>
                  <td class="text-center tdsin"><?php echo number_format(19,2);?>% </td>
                  <td class="text-center tdsin"><?php echo number_format(0,2);?>%</td>
                  <td class="text-right tdsin"><?php echo "$".number_format($tothdf,2);?></td>                  
                </tr>
                <tr>
                  <td class="text-center tdsin"><?php echo $codhedf;?></td>
                  <td class="text-left tdsin"><?php echo $tithedf;?></td>
                  <td class="text-center tdsin">HORA</td>
                  <td class="text-center tdsin"><?php echo number_format($totalhedf,2);?></td>
                  <td class="text-right tdsin"><?php echo "$".number_format($vhhedf,2);?></td>
                  <td class="text-center tdsin"><?php echo number_format(0,2);?>%</td>
                  <td class="text-center tdsin"><?php echo number_format(19,2);?>% </td>
                  <td class="text-center tdsin"><?php echo number_format(0,2);?>%</td>
                  <td class="text-right tdsin"><?php echo "$".number_format($tothedf,2);?></td>                  
                </tr>
                <tr>
                  <td class="text-center tdsin"><?php echo $codheno;?></td>
                  <td class="text-left tdsin"><?php echo $titheno;?></td>
                  <td class="text-center tdsin">HORA</td>
                  <td class="text-center tdsin"><?php echo number_format($totalheno,2);?></td>
                  <td class="text-right tdsin"><?php echo "$".number_format($vhheno,2);?></td>
                  <td class="text-center tdsin"><?php echo number_format(0,2);?>%</td>
                  <td class="text-center tdsin"><?php echo number_format(19,2);?>% </td>
                  <td class="text-center tdsin"><?php echo number_format(0,2);?>%</td>
                  <td class="text-right tdsin"><?php echo "$".number_format($totheno,2);?></td>                  
                </tr>
                <tr>
                  <td class="text-center tdsin"><?php echo $codhenf;?></td>
                  <td class="text-left tdsin"><?php echo $tithenf;?></td>
                  <td class="text-center tdsin">HORA</td>
                  <td class="text-center tdsin"><?php echo number_format($totalhenf,2);?></td>
                  <td class="text-right tdsin"><?php echo "$".number_format($vhhenf,2);?></td>
                  <td class="text-center tdsin"><?php echo number_format(0,2);?>%</td>
                  <td class="text-center tdsin"><?php echo number_format(19,2);?>% </td>
                  <td class="text-center tdsin"><?php echo number_format(0,2);?>%</td>
                  <td class="text-right tdsin"><?php echo "$".number_format($tothenf,2);?></td>                  
                </tr>
                <tr>
                  <td class="text-center tdsin"><?php echo $codrn;?></td>
                  <td class="text-left tdsin"><?php echo $titrn;?></td>
                  <td class="text-center tdsin">HORA</td>
                  <td class="text-center tdsin"><?php echo number_format($totalrn,2);?></td>
                  <td class="text-right tdsin"><?php echo "$".number_format($vhrn,2);?></td>
                  <td class="text-center tdsin"><?php echo number_format(0,2);?>%</td>
                  <td class="text-center tdsin"><?php echo number_format(19,2);?>% </td>
                  <td class="text-center tdsin"><?php echo number_format(0,2);?>%</td>
                  <td class="text-right tdsin"><?php echo "$".number_format($totrn,2);?></td>                  
                </tr>
                <tr>
                  <td class="text-center tdsin"><?php echo $codhnf;?></td>
                  <td class="text-left tdsin"><?php echo $tithnf;?></td>
                  <td class="text-center tdsin">HORA</td>
                  <td class="text-center tdsin"><?php echo number_format($totalhnf,2);?></td>
                  <td class="text-right tdsin"><?php echo "$".number_format($vhhnf,2);?></td>
                  <td class="text-center tdsin"><?php echo number_format(0,2);?>%</td>
                  <td class="text-center tdsin"><?php echo number_format(19,2);?>% </td>
                  <td class="text-center tdsin"><?php echo number_format(0,2);?>%</td>
                  <td class="text-right tdsin"><?php echo "$".number_format($tothnf,2);?></td>                  
                </tr>
                <tr>
                  <td class="text-center tdsin"><?php echo $codrd;?></td>
                  <td class="text-left tdsin"><?php echo $titrd;?></td>
                  <td class="text-center tdsin">HORA</td>
                  <td class="text-center tdsin"><?php echo number_format($totalrd,2);?></td>
                  <td class="text-right tdsin"><?php echo "$".number_format($vhrd,2);?></td>
                  <td class="text-center tdsin"><?php echo number_format(0,2);?>%</td>
                  <td class="text-center tdsin"><?php echo number_format(19,2);?>% </td>
                  <td class="text-center tdsin"><?php echo number_format(0,2);?>%</td>
                  <td class="text-right tdsin"><?php echo "$".number_format($totrd,2);?></td>                  
                </tr>
                <?php 
                if($totalare>0)
                {
                  ?>
                   <tr>
                    <td class="text-center tdsin"><?php echo $codare;?></td>
                    <td class="text-left tdsin"><?php echo $titare;?></td>
                    <td class="text-center tdsin">HORA</td>
                    <td class="text-center tdsin"><?php echo number_format($totalare,2);?></td>
                    <td class="text-right tdsin"><?php echo "$".number_format($vhare,2);?></td>
                    <td class="text-center tdsin"><?php echo number_format(0,2);?>%</td>
                    <td class="text-center tdsin"><?php echo number_format(19,2);?>% </td>
                    <td class="text-center tdsin"><?php echo number_format(0,2);?>%</td>
                    <td class="text-right tdsin"><?php echo "$".number_format($totare,2);?></td>                  
                  </tr>
                  <?php
                }
                ?>
                <?php if($tip=="2"){ ?>
                  <tr>
                  <td class="text-center tdsin"><?php echo $codrd;?></td>
                  <td class="text-left tdsin">H.E MAQUINA</td>
                  <td class="text-center tdsin">HORA</td>
                  <td class="text-center tdsin"><?php echo number_format($totalhem,2);?></td>
                  <td class="text-right tdsin"><?php echo "$".number_format($vhma,2);?></td>
                  <td class="text-center tdsin"><?php echo number_format(0,2);?>%</td>
                  <td class="text-center tdsin"><?php echo number_format(19,2);?>% </td>
                  <td class="text-center tdsin"><?php echo number_format(0,2);?>%</td>
                  <td class="text-right tdsin"><?php echo "$".number_format($totalmaquina,2);?></td>                  
                </tr>
                <?php } ?>
                
            </tbody>
            <tfoot>
              <tr>
                <th class="text-right tdsin"></th>
                <th class="text-right tdsin"></th>
                <th class="text-right tdsin"></th>
                <th class="text-right tdsin">Total Cantidad</th>
                <th class="text-right tdsin"><?php echo number_format($totalhoras,2);?></th>
                <th class="text-right tdsin"></th>
                <th class="text-right tdsin"></th>
                <th class="text-right tdsin"></th>
                <th class="text-right tdsin"></th>
              </tr>
              <tr><th class="divider tdsin" colspan="9"></th></tr>
              <tr>
                <th class="text-right tdsin"></th>
                <th class="text-right tdsin"></th>
                <th class="text-right tdsin"></th>
                <th class="text-right tdsin"></th>
                <th class="text-right tdsin"></th>
                <th class="text-right tdsin"></th>
                <th class="text-right tdsin">SUBTOTAL:</th>
                <th class="text-right tdsin"><?php echo number_format($total,2);?></th>
                <th class="text-right tdsin"></th>
              </tr>
              <tr>
                <th class="text-right tdsin"></th>
                <th class="text-right tdsin"></th>
                <th class="text-right tdsin"></th>
                <th class="text-right tdsin"></th>
                <th class="text-right tdsin"></th>
                <th class="text-right tdsin"></th>
                <th class="text-right tdsin">DESCUENTO:</th>
                <th class="text-right tdsin"><?php echo number_format($dscto,2);?></th>
                <th class="text-right tdsin"></th>
              </tr>
              <tr>
                <th class="text-right tdsin"></th>
                <th class="text-right tdsin"></th>
                <th class="text-right tdsin"></th>
                <th class="text-right tdsin"></th>
                <th class="text-right tdsin"></th>
                <th class="text-right tdsin"></th>
                <th class="text-right tdsin">IVA:</th>
                <th class="text-right tdsin"><?php echo number_format($iva,2);?></th>
                <th class="text-right tdsin"></th>
              </tr>
              <tr>
                <th class="text-right tdsin"></th>
                <th class="text-right tdsin"></th>
                <th class="text-right tdsin"></th>
                <th class="text-right tdsin"></th>
                <th class="text-right tdsin"></th>
                <th class="text-right tdsin"></th>
                <th class="text-right tdsin">RTEFTE:</th>
                <th class="text-right tdsin"><?php echo number_format($rtefuente,2);?></th>
                <th class="text-right tdsin"></th>
              </tr>
              <tr>
                <th class="text-right tdsin"></th>
                <th class="text-right tdsin"></th>
                <th class="text-right tdsin"></th>
                <th class="text-right tdsin"></th>
                <th class="text-right tdsin"></th>
                <th class="text-right tdsin"></th>
                <th class="text-right tdsin">RTEIVA:</th>
                <th class="text-right tdsin"><?php echo number_format($rteiva,2);?></th>
                <th class="text-right tdsin"></th>
              </tr>
              <tr>
                <th class="text-right tdsin"></th>
                <th class="text-right tdsin"></th>
                <th class="text-right tdsin"></th>
                <th class="text-right tdsin"></th>
                <th class="text-right tdsin"></th>
                <th class="text-right tdsin"></th>
                <th class="text-right tdsin">NETO:</th>
                <th class="text-right tdsin"><?php echo number_format($neto,2);?></th>
                <th class="text-right tdsin"></th>
              </tr>
              <tr><th class="divider tdsin" colspan="9"></th></tr>
              
            </tfoot>
        </table>

        <?php 
          //echo $mes;
        if($tip==2)
        {
          $sqlth ="SELECT SUM(TIME_TO_SEC(TIMEDIFF(joh_fin, joh_inicio))/3600) as totalhoras, o.obr_clave_int ido, o.obr_nombre nom, obr_cencos cen,obr_hr_mes hrmes, o.obr_vr_maquina vm FROM tbl_jornada j join tbl_jornada_dias jd on j.jor_clave_int = jd.jor_clave_int JOIN tbl_jornada_horas jh ON jh.jod_clave_int = jd.jod_clave_int JOIN tbl_tipo_novedad tn on tn.tin_clave_int = jh.tin_clave_int join  tbl_obras o ON o.obr_clave_int = jh.obr_clave_int WHERE tn.tin_tipo = 1 and jd.jod_fecha between '".$ini."' and '".$fin."' and o.obr_clave_int = '".$obr."' and j.jor_estado = 1  GROUP BY o.obr_clave_int";
        
          $stmtTh = $conn->prepare($sqlth);
          $stmtTh->execute();
          $numtoth = $stmtTh->rowCount();
                 
          ?>
            <!-- <table  class="table table-bordered table-striped"  border=0 cellspacing="2" cellspading="2">
                <thead>
                    <tr>
                        <th class="text-center bg-terracota radius tdcon" style="width:30%">OBRA</th>
                        <th class="text-center bg-terracota radius tdcon">CENCOS</th>
                        <th class="text-center bg-terracota radius tdcon">H. MAQUINA</th>
                        <th class="text-center bg-terracota radius tdcon">H. CONTRATO</th>
                        <th class="text-center bg-terracota radius tdcon">H.E<BR> MAQUINA</th>
                        <th class="text-center bg-terracota radius tdcon">VR. HORA</th>
                        <th class="text-center bg-terracota radius tdcon">TOTAL H.E</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                        while ($dath = $stmtTh->fetch(PDO::FETCH_ASSOC)) {
                        
                            $totalhoras = $dath['totalhoras'];
                            $obra = $dath['nom']; //OBRA|
                            $cenco  = $dath['cen']; // CENCOS
                            $horascontrato = $dath['hrmes']; // HORAS CONTRATADAS POR CADA OBRA
                            $horasextras = $totalhoras - $horascontrato; // DIFERENCIA ENTRE LAS HORAS REALES Y LAS HORAS CONTRATADAS POR Obra
                            $vm = $dath['vm'];// VALOR HORA MAQUINA
                            $totalhe = ($horasextras>0)?$vm * $horasextras: 0; //VALOR TOTAL DE HORAS EXTRAS POR MAQUINA
                            ?>
                            <tr>
                                <td class="radius" style="width:30%"><?php echo $obra;?></td>
                                <td class="radius"><?php echo $cenco;?></td>
                                <td class="text-center radius"><?php echo number_format($totalhoras,2,".","");?></td>
                                <td class="text-center radius"><?php echo number_format($horascontrato,2,".","");?></td>
                                <td class="text-center radius"><?php echo number_format($horasextras,2,".","");?></td>
                                <td class="text-right radius"><?php echo "$".number_format($vm,2,".",",");?></td>
                                <td class="text-right radius"><?php echo "$".number_format($totalhe,2,".",",");?></td>
                            </tr>
                            <?PHP                            

                        }
                    ?>
                    
                </tbody>
            </table> -->
          <?php
        }
          ?>

        <table style="width:700px" border=0>    
          <tr><td class="tdsin divider" colspan="2"></td></tr>
          <tr><td class="tdsin divider" colspan="2"></td></tr>
        </table>
    </div>  
    <page_footer> 
      
    </page_footer>       
                  
</page>
<page backimg="../../dist/img/Potenco-logo-mini.png" backtop="30mm" backbottom="10mm" backleft="0mm" backright = "0mm" style="font-size: 14px" backimgx="right" backimgy="5px" backimgw="20%" footer="date;time;page;" orientation="landscape" >
    <page_header> 
        <span  style="position: absolute; right: 0;top:15px;width: 100px;text-align:right">N° <strong><?PHP echo $codigo;?></strong></span>
       
        <table style="width:700px">
        <tr><td class="tdsin">POTENCO S.A.S</td></tr>
        <tr class="row">
          <td class="tdsin td-gray">DIRECCION:</td>
          <td class="tdsin td-gray" colspan="3"> CALLE 7 SUR 51 A 21 INT 118</td>
        </tr>
        <tr class="row">
          <td class="tdsin td-gray">NIT:</td>
          <td class="tdsin td-gray">900563153</td>
          <td class="tdsin td-gray">CIUDAD:</td>
          <td class="tdsin td-gray">MEDELLÍN</td>
        </tr>
        <tr class="row">
          <td class="tdsin td-gray">TEL:</td>
          <td class="tdsin td-gray" colspan="3">4488582</td>
        </tr>
        </table>
        

       
    </page_header>
    <div style="width:700px">
      
       
        <table style="width:100%; font-size:10px" border=0 cellspacing="1" cellspading="1">
     
            <tr>
              <th rowspan="2"></th>
              <th class="text-center bg-terracota tdcon" style="width:120px">Fecha</th>
              <th class="text-center bg-terracota tdcon" colspan="3">Mañana</th>
              <th class="text-center bg-terracota tdcon ">Total <br>Horas</th>
              <th class="text-center bg-terracota tdcon" colspan="3">Tarde</th>
              <th class="text-center bg-terracota tdcon">Total<br>Horas</th>
              <th class="text-center bg-terracota tdcon">Horas<br> Dia</th>
              <th rowspan="2" class="text-center bg-terracota align-middle tdcon">Horario</th>
              <th rowspan="2" class="text-center bg-terracota align-middle tdcon">Compensado</th>
              <th rowspan="2" class="text-center bg-terracota align-middle tdcon">Pagado <br/>y compensado</th>
              <th rowspan="2" class="text-center bg-terracota align-middle tdcon">Permiso <br>Remunerado</th>
              <th rowspan="2" class="text-center bg-terracota align-middle tdcon">Observaciones</th>
              <th class="text-center tdcon">HEDO</th>
              <th class="text-center tdcon">HDF</th>
              <th class="text-center tdcon">HEDF</th>
              <th class="text-center tdcon">HENO</th>
              <th class="text-center tdcon">HENF</th>
              <th class="text-center tdcon">RN</th>
              <th class="text-center tdcon">HNF</th>
              <th class="text-center tdcon">RD</th>
            </tr>
            <tr>
              <th class="text-center tdcon">Dia/Mes/Año</th>
              <th class="text-center tdcon">Desde</th>
              <th class="text-center tdcon"></th>
              <th class="text-center tdcon">Hasta</th>
              <th class="text-center tdcon">#</th>
              <th class="text-center tdcon">Desde</th>
              <th class="text-center tdcon"></th>
              <th class="text-center tdcon">Hasta</th>
              <th class="text-center tdcon">#</th>
              <th class="text-center tdcon">#</th>
              <th class="text-center tdcon">002</th>
              <th class="text-center tdcon">014</th>
              <th class="text-center tdcon">004</th>
              <th class="text-center tdcon">003</th>
              <th class="text-center tdcon">005</th>
              <th class="text-center tdcon">006</th>
              <th class="text-center tdcon">008</th>
              <th class="text-center tdcon">013</th>
            </tr>
      
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

                $stmtDias = $conn->prepare($sqldias);
                $stmtDias->execute();
                $numdias = $stmtDias->rowCount();
                
                if ($numdias > 0) {
                    while ($datd = $stmtDias->fetch(PDO::FETCH_ASSOC)) {
                
                        $idfec = $datd['id'];
                        $dia   = $datd['dia'];
                        $fec   = $datd['fec'];
                        $tothoras = $datd['lid_horas'];
                        // $nmes = date("F", strtotime($fec));
                        // $ndia = date("l", strtotime($fec));
                        // $numd = date("d", strtotime($fec));
                        // $anod = date("Y", strtotime($fec));
                        // $fechafec = $ndia.", ". $numd." de ".$nmes." de ".$anod;

                        $fechafec = strftime("%A %d de %B del %Y", strtotime($fec));

                        $fes = $datd['fes']; $bgfes = ($fes==1)?"bg-secondary":""; //dia festivo
                        $titfes = ($fes==1)?"Dia Festivo":"";

                          //DIA INICIAL Y FINAL
                        $diainicial = $numd;
                        $diafinal = date("t",strtotime($fec));
                        $horario = $datd['lid_horario'];                   
                        $aini = $datd['hiam'];
                        $afin = $datd['hfam'];
                        $pini = $datd['hipm'];
                        $pfin = $datd['hfpm'];

                        $tsaini = $toh->time_to_sec($aini);
                        $tsafin = $toh->time_to_sec($afin);
                        $tspini = $toh->time_to_sec($pini);
                        $tspfin = $toh->time_to_sec($pfin);

                        $totalam = $toh->fnHoras($fec,$aini,$afin);
                        $totalpm = $toh->fnHoras($fec,$pini,$pfin);

                        $aminit  = ($aini!="")? date("g:i a", strtotime($aini)): $aini;
                        $amfint  = ($afin!="")? date("g:i a", strtotime($afin)): $afin;
                        $pminit  = ($pini!="")? date("g:i a", strtotime($pini)): $pini;
                        $pmfint  = ($pfin!="")? date("g:i a", strtotime($pfin)): $pfin;

                        $ali = $datd['lid_alimentacion'];
                        $valorali = $datd['lid_val_alimentacion'];
                        $icali = "";
                        if($ali==1)
                        {
                          //buscar imagen de alimentacion
                          $icali = "";
                        }
                    
                        $HEDO = 0; // HORAS EXTRAS DIURNAS ORDINA
                        $HDF  = 0;
                        $HEDF = 0;
                        $HENO = 0;
                        $HENF = 0;
                        $RN   = 0;
                        $HDF  = 0;
                        $RD   = 0;
                        
                        
                        // pagado y compensado = T5 => "SI" o vacio
                        $T5 = "";//
                        // compensado = S5 = >Si o Ambos o vacion
                        $S5  = $datd['lid_compensado'];
                        // PERMISO REMUNERADO = U5=> SI
                        $U5  = $datd['lid_remunerado'];

                        $obs = $datd['lid_observacion'];
                       

                        $HENF = $datd['lid_henf'];

                        $HENO = $datd['lid_heno'];

                        $HEDF = $datd['lid_hedf'];                       

                        $HNF  = $datd['lid_hnf'];

                        $RD   = $datd['lid_rd'];

                        $RN   = $datd['lid_rn'];

                        $HDF  = $datd['lid_hdf'];

                        $HEDO = $datd['lid_hedo'];                        
                  

                        ?>
                
                        <tr class="<?php echo $bgfes;?>">
                            <td class="p-0 align-middle tdcon"><?php echo $icali;?></td>
                            <td class="p-0 align-middle tdcon" style="width:120px"><?php echo $fechafec;?></td>
                            <td class="text-center p-0 align-middle tdcon"><?php echo $aminit;?></td>
                            <td class="text-center p-0 align-middle tdcon">a</td>
                            <td class="text-center p-0 align-middle tdcon"><?php echo $amfint;?></td>
                            <td class="text-center p-0 align-middle tdcon"><?php echo $totalam;?></td>
                            <td class="text-center p-0 align-middle tdcon"><?php echo $pminit;?></td>
                            <td class="text-center p-0 align-middle tdcon">a</td>
                            <td class="text-center p-0 align-middle tdcon"><?php echo $pmfint;?></td>
                            <td class="text-center p-0 align-middle tdcon"><?php echo $totalpm;?></td>
                            <td class="text-center p-0 align-middle tdcon"><?php echo number_format($tothoras,2,".","");?></td>
                            <td class="text-center p-0 align-middle tdcon"><?php echo number_format($horario,2,".","");?></td>
                            <td class="text-center p-0 align-middle tdcon"><?php echo $S5;?></td>
                            <td class="text-center p-0 align-middle tdcon"><?php echo $T5;?></td>
                            <td class="text-center p-0 align-middle tdcon"><?PHP echo $U5;?></td>
                            <td class="text-center p-0 align-middle tdcon"><?PHP echo $obs; ?></td>
                            <td class="text-center p-0 align-middle tdcon  <?php if($HEDO<0){ echo "bg-danger"; } ?>"><?php echo number_format($HEDO,2);?></td>
                            <td class="text-center p-0 align-middle tdcon <?php if($HDF<0){ echo "bg-danger"; } ?>"><?php echo number_format($HDF,2);?></td>
                            <td class="text-center p-0 align-middle tdcon <?php if($HEDF<0){ echo "bg-danger"; } ?>"><?php echo number_format($HEDF,2);?></td>
                            <td class="text-center p-0 align-middle tdcon <?php if($HENO<0){ echo "bg-danger"; } ?>"><?php echo number_format($HENO,2);?></td>
                            <td class="text-center p-0 align-middle tdcon <?php if($HENF<0){ echo "bg-danger"; } ?>"><?php echo number_format($HENF,2);?></td>
                            <td class="text-center p-0 align-middle tdcon <?php if($RN<0){ echo "bg-danger"; } ?>"><?php echo number_format($RN,2);?></td>
                            <td class="text-center p-0 align-middle tdcon <?php if($HNF<0){ echo "bg-danger"; } ?>"><?php echo number_format($HNF,2);?></td>
                            <td class="text-center p-0 align-middle tdcon <?php if($RD<0){ echo "bg-danger"; } ?>"><?php echo number_format($RD,2);?></td>
                        </tr>
                        <?php 
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
                         
                        if(($numd==15 || $numd==$diafinal) and $d!=0)
                        {
                            ?>
                             <tr>
                                <td class="bg-primary tdcon"></td>
                                <td class="bg-primary tdcon">Quincena: <?php echo $nmes." ".$numd; ?></td>
                                <td class="bg-primary tdcon"></td>
                                <td class="bg-primary tdcon"></td>
                                <td class="bg-primary tdcon"></td>
                                <td class="bg-primary tdcon"></td>
                                <td class="bg-primary tdcon"></td>
                                <td class="bg-primary tdcon"></td>
                                <td class="bg-primary tdcon"></td>
                                <td class="bg-primary tdcon"></td>
                                <td class="bg-primary tdcon"></td>
                                <td class="bg-primary tdcon"></td>
                                <td class="bg-primary tdcon"></td>
                                <td class="bg-primary tdcon"></td>
                                <td class="bg-primary tdcon"></td>
                                <td class="bg-primary tdcon"></td>
                                <td class="text-center p-0 align-middle bg-primary tdcon  "><?php echo number_format($TOTALHEDO,2);?></td>
                                <td class="text-center p-0 align-middle bg-primary tdcon  "><?php echo number_format($TOTALHDF,2);?></td>
                                <td class="text-center p-0 align-middle bg-primary tdcon  "><?php echo number_format($TOTALHEDF,2);?></td>
                                <td class="text-center p-0 align-middle bg-primary tdcon  "><?php echo number_format($TOTALHENO,2);?></td>
                                <td class="text-center p-0 align-middle bg-primary tdcon  "><?php echo number_format($TOTALHENF,2);?></td>
                                <td class="text-center p-0 align-middle bg-primary tdcon  "><?php echo number_format($RN,2);?></td>
                                <td class="text-center p-0 align-middle bg-primary tdcon  "><?php echo number_format($TOTALHNF,2);?></td>
                                <td class="text-center p-0 align-middle bg-primary tdcon  "><?php echo number_format($TOTALRD,2);?></td>                 
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
      
            <!-- <tfoot>
           
              <tr><th></th>
                  <th class="tdcon text-right">TOTALES</th>
                                     
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
                  <th class="text-center p-0 align-middle bg-secondary  "><?php echo number_format($TOTALGHEDO,2);?></th>
                  <th class="text-center p-0 align-middle bg-secondary  "><?php echo number_format($TOTALGHDF,2);?></th>
                  <th class="text-center p-0 align-middle bg-secondary  "><?php echo number_format($TOTALGHEDF,2);?></th>
                  <th class="text-center p-0 align-middle bg-secondary  "><?php echo number_format($TOTALGHENO,2);?></th>
                  <th class="text-center p-0 align-middle bg-secondary  "><?php echo number_format($TOTALGHENF,2);?></th>
                  <th class="text-center p-0 align-middle bg-secondary  "><?php echo number_format($TOTALGRN,2);?></th>
                  <th class="text-center p-0 align-middle bg-secondary  "><?php echo number_format($TOTALGHNF,2);?></th>
                  <th class="text-center p-0 align-middle bg-secondary  "><?php echo number_format($TOTALGRD,2);?></th>                              
              </tr>
            </tfoot>                     -->
        </table>
      </div>
    <page_footer> 
      
    </page_footer>
        
</page>
<page backimg="../../dist/img/Potenco-logo-mini.png" backtop="30mm" backbottom="10mm" backleft="0mm" backright = "0mm" style="font-size: 14px" backimgx="right" backimgy="5px" backimgw="20%" footer="date;time;page;" orientation="landscape" >
    <page_header> 
        <span  style="position: absolute; right: 0;top:15px;width: 100px;text-align:right">N° <strong><?PHP echo $codigo;?></strong></span>
       
        <table style="width:700px">
        <tr><td class="tdsin">POTENCO S.A.S</td></tr>
        <tr class="row">
          <td class="tdsin td-gray">DIRECCION:</td>
          <td class="tdsin td-gray" colspan="3"> CALLE 7 SUR 51 A 21 INT 118</td>
        </tr>
        <tr class="row">
          <td class="tdsin td-gray">NIT:</td>
          <td class="tdsin td-gray">900563153</td>
          <td class="tdsin td-gray">CIUDAD:</td>
          <td class="tdsin td-gray">MEDELLÍN</td>
        </tr>
        <tr class="row">
          <td class="tdsin td-gray">TEL:</td>
          <td class="tdsin td-gray" colspan="3">4488582</td>
        </tr>
        </table>
        

       
    </page_header>
    <div style="width:700px">
      
        
        <table style="width:50%; font-size:10px" border=0 cellspacing="1" cellspading="1">
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
            <td class="text-center"><?php echo number_format($totalhedo,2);?></td>
            <td class="text-center"><?php echo number_format($porhedo*100,0) ?>%</td>
            <td class="text-right"><?php echo "$".number_format($vhhedo,2);?></td>
            <td class="text-right"><?php echo "$".number_format($tothedo,2);?></td>            
          </tr>
          <tr>
            <td class="bold"><?php echo $tithdf; ?></td>
            <td class="text-center"><?php echo number_format($totalhdf,2);?></td>
            <td class="text-center"><?php echo number_format($porhdf*100,0) ?>%</td>
            <td class="text-right"><?php echo "$".number_format($vhhdf,2);?></td>
            <td class="text-right"><?php echo "$".number_format($tothdf,2);?></td>            
          </tr>
          <tr>
            <td class="bold"><?php echo $tithedf; ?></td>
            <td class="text-center"><?php echo number_format($totalhedf,2);?></td>
            <td class="text-center"><?php echo number_format($porhedf*100,0) ?>%</td>
            <td class="text-right"><?php echo "$".number_format($vhhedf,2);?></td>
            <td class="text-right"><?php echo "$".number_format($tothedf,2);?></td>            
          </tr>
          <tr>
            <td class="bold"><?php echo $titheno; ?></td>
            <td class="text-center"><?php echo number_format($totalheno,2);?></td>
            <td class="text-center"><?php echo number_format($porheno*100,0) ?>%</td>
            <td class="text-right"><?php echo "$".number_format($vhheno,2);?></td>
            <td class="text-right"><?php echo "$".number_format($totheno,2);?></td>            
          </tr>
          <tr>
            <td class="bold"><?php echo $tithenf; ?></td>
            <td class="text-center"><?php echo number_format($totalhenf,2);?></td>
            <td class="text-center"><?php echo number_format($porhenf*100,0) ?>%</td>
            <td class="text-right"><?php echo "$".number_format($vhhenf,2);?></td>
            <td class="text-right"><?php echo "$".number_format($tothenf,2);?></td>            
          </tr>
          <tr>
            <td class="bold"><?php echo $titrn; ?></td>
            <td class="text-center"><?php echo number_format($totalrn,2);?></td>
            <td class="text-center"><?php echo number_format($porrn*100,0) ?>%</td>
            <td class="text-right"><?php echo "$".number_format($vhrn,2);?></td>
            <td class="text-right"><?php echo "$".number_format($totrn,2);?></td>            
          </tr>
          <tr>
            <td class="bold"><?php echo $tithnf; ?></td>
            <td class="text-center"><?php echo number_format($totalhnf,2);?></td>
            <td class="text-center"><?php echo number_format($porhnf*100,0) ?>%</td>
            <td class="text-right"><?php echo "$".number_format($vhhnf,2);?></td>
            <td class="text-right"><?php echo "$".number_format($tothnf,2);?></td>            
          </tr>
          <tr>
            <td class="bold"><?php echo $titrd; ?></td>
            <td class="text-center"><?php echo number_format($totalrd,2);?></td>
            <td class="text-center"><?php echo number_format($porrd*100,0) ?>%</td>
            <td class="text-right"><?php echo "$".number_format($vhrd,2);?></td>
            <td class="text-right"><?php echo "$".number_format($totrd,2);?></td>            
          </tr>
          <?php if($totalare>0){ ?>
          <tr>
            <td class="bold"><?php echo $titare; ?></td>
            <td class="text-center"><?php echo number_format($totalare,2);?></td>
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
    <page_footer> 
      
    </page_footer>
        
                  
</page>