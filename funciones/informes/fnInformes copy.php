<?php
include '../../data/db.config.php';
include('../../data/conexion.php');
session_start();
error_reporting(E_ALL);
//$login     = isset($_SESSION['persona']);
// cookie que almacena el numero de identificacion de la persona logueada
$usuario   = $_COOKIE['usuarioPo'];
$idUsuario = $_COOKIE['idusuarioPo'];
$perfil    = $_SESSION["perfilPo"];
$IP = $_SERVER['REMOTE_ADDR'];
//include '../../data/validarpermisos.php';
setlocale(LC_TIME, 'es_CO.UTF-8'); 
date_default_timezone_set('America/Bogota');
$fecha  = date("Y/m/d H:i:s");

include ("../../data/validarpermisos.php");

require_once "../../controladores/general.controller.php";

use  PHPMailer\PHPMailer\PHPMailer;
use  PHPMailer\PHPMailer\Exception;
require ('../../PHPMailer-master/src/PHPMailer.php');
require ('../../PHPMailer-master/src/Exception.php');
//require ('../../PHPMailer-master/src/SMTP.php');
//require_once('../../clases/pdf/html2pdf.class.php');
//pg_free_result($conped);

$p121 = isset($permisosUsuario[121]) ?? 0;
$p122 = isset($permisosUsuario[122]) ?? 0;
$p38  = isset($permisosUsuario[38]) ?? 0;

$opcion = $_POST['opcion'];

 if ($opcion == "CONTADORHOME") {
    $poraprobar = 0;
    $aprobadas = 0;
    $liquidadas = 0;
    $cancelada = 0;
    $rechazada = 0;
    $poraprobarliq = 0;
    $porfacturar = 0;
    $facturadas = 0;
    $pendienteobra = 0;
    $poraprobarobra = 0;

    // Filtro dinámico por tipo
    $wh = "";
    if ($p121 > 0 || $p122 > 0) {
        $wh .= " AND (";
        if ($p121 > 0) $wh .= "liq_tipo = 1";
        if ($p121 > 0 && $p122 > 0) $wh .= " OR ";
        if ($p122 > 0) $wh .= "liq_tipo = 2";
        $wh .= ")";
    } else {
        $wh .= " AND liq_tipo NOT IN (1, 2)";
    }

    // Contadores principales de jornada
    $sqlJornada = "
        SELECT 
            (SELECT COUNT(jor_clave_int) FROM tbl_jornada WHERE jor_estado = 0) AS PORAPROBAR,
            (SELECT COUNT(jor_clave_int) FROM tbl_jornada WHERE jor_estado = 1) AS APROBADAS,
            (SELECT COUNT(jor_clave_int) FROM tbl_jornada WHERE jor_estado = 2) AS LIQUIDADAS
    ";
    $stmtJor = $conn->query($sqlJornada);
    if ($stmtJor) {
        $dat = $stmtJor->fetch(PDO::FETCH_ASSOC);
        $poraprobar = $dat['PORAPROBAR'];
        $aprobadas  = $dat['APROBADAS'];
        $liquidadas = $dat['LIQUIDADAS'];
    }

    // Contadores de liquidaciones
    $sqlLiq = "
        SELECT
            (SELECT COUNT(liq_clave_int) FROM tbl_liquidar WHERE liq_estado = 1 $wh) AS PORAPROBARLIQUIDACION,
            (SELECT COUNT(liq_clave_int) FROM tbl_liquidar WHERE liq_estado = 2 $wh) AS PORFACTURAR,
            (SELECT COUNT(liq_clave_int) FROM tbl_liquidar WHERE liq_estado = 3 $wh) AS FACTURADAS,
            (SELECT COUNT(liq_clave_int) FROM tbl_liquidar WHERE liq_estado = 4 $wh) AS RECHAZADAS
    ";
    $stmtLiq = $conn->query($sqlLiq);
    if ($stmtLiq) {
        $dat = $stmtLiq->fetch(PDO::FETCH_ASSOC);
        $poraprobarliq = $dat['PORAPROBARLIQUIDACION'];
        $porfacturar   = $dat['PORFACTURAR'];
        $facturadas    = $dat['FACTURADAS'];
        $rechazada     = $dat['RECHAZADAS'];
    }

    // Liquidaciones pendientes por obra
    $sqlPenObra = "
        SELECT COUNT(DISTINCT l.liq_clave_int) AS can
        FROM tbl_liquidar l
        JOIN tbl_liquidar_dias_obra ld ON ld.liq_clave_int = l.liq_clave_int
        WHERE l.liq_tipo = 2 AND l.lio_clave_int <= 0
    ";
    $stmtPenObra = $conn->query($sqlPenObra);
    if ($stmtPenObra) {
        $dat = $stmtPenObra->fetch(PDO::FETCH_ASSOC);
        $pendienteobra = $dat['can'];
    }

    // Obras pendientes por aprobar
    $sqlAproObra = "SELECT COUNT(DISTINCT l.lio_clave_int) AS can FROM tbl_liquidar_obras l WHERE lio_estado = 1";
    $stmtAproObra = $conn->query($sqlAproObra);
    if ($stmtAproObra) {
        $dat = $stmtAproObra->fetch(PDO::FETCH_ASSOC);
        $poraprobarobra = $dat['can'];
    }

    $datos[] = array(
        "poraprobar"      => $poraprobar,
        "aprobadas"       => $aprobadas,
        "liquidadas"      => $liquidadas,
        "cancelada"       => $cancelada,
        "rechazada"       => $rechazada,
        "poraprobarliq"   => $poraprobarliq,
        "facturadas"      => $facturadas,
        "porfacturar"     => $porfacturar,
        "pendienteobra"   => $pendienteobra,
        "poraprobarobra"  => $poraprobarobra
    );

    echo json_encode($datos);
}

else if($opcion=="GRAFICO" and $p38>0)
{
    setlocale(LC_ALL,"es_ES.utf8","es_ES","esp");
    $m = 1;
    $y = $_POST['ano']; $y = ($y=="")? date('Y'): $y;
    $ymin = $y-1;
    //consultar tipo de horas en el año seleccionado

    $sql = "
    SELECT 
        SUM(ld.lid_hedo) AS hedo,
        SUM(ld.lid_hdf) AS hdf,
        SUM(ld.lid_hedf) AS hedf,
        SUM(ld.lid_heno) AS heno,
        SUM(ld.lid_henf) AS henf,
        SUM(ld.lid_rn) AS rn,
        SUM(ld.lid_hnf) AS hnf,
        SUM(ld.lid_rd) AS rd,
        SUM(ld.lid_permisos) AS are
    FROM tbl_liquidar_dias ld
    WHERE DATE_FORMAT(ld.lid_fecha, '%Y') = :year";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':year', $y, PDO::PARAM_INT);
    $stmt->execute();

    $dat = $stmt->fetch(PDO::FETCH_ASSOC);

    $are  = $dat['are'];
    $hedo = $dat['hedo'];
    $hedo = ($are > 0) ? $hedo - $are : $hedo;
    $hdf  = $dat['hdf'];
    $hedf = $dat['hedf'];
    $heno = $dat['heno'];
    $henf = $dat['henf'];
    $rn   = $dat['rn'];
    $hnf  = $dat['hnf'];
    $rd   = $dat['rd'];


    ?>
      <div class="col-md-6">
        <!-- LINE CHART -->
        <div class="card card-info">
            <div class="card-header bg-blue hide">
            <h3 class="card-title">Horas Extras</h3>

            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
                </button>
            
            </div>
            </div>
            <div class="card-body">
            <div class="chart">
                <div id="divordenesestado"></div>
            </div>
            </div>
            <!-- /.card-body -->
        </div>
            <!-- /.card -->
    </div>
    <div class="col-md-6">
        <!-- LINE CHART -->
        <div class="card card-info">
            <div class="card-header bg-blue hide">
            <h3 class="card-title hide">Tiempo Extra <?php echo $y;?></h3>

            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i>
                </button>
                
            </div>
            </div>
            <div class="card-body">
            <div class="chart">
                
                <div id="divhoraspormes"></div>
            </div>
            </div>
            <!-- /.card-body -->
        </div>
        <!-- /.card -->
    </div>
    <script>
    var hedo = parseInt('<?php echo $hedo;?>');  
    var hdf = parseInt('<?php echo $hdf;?>');
    var hedf = parseInt('<?php echo $hedf;?>');
    var heno = parseInt('<?php echo $heno;?>');
    var henf = parseInt('<?php echo $henf;?>');
    var rn = parseInt('<?php echo $rn;?>');
    var hnf = parseInt('<?php echo $hnf;?>');
    var rd = parseInt('<?php echo $rd;?>');
    var are = parseInt('<?php echo $are;?>');

    hedo = (hedo=="" || hedo==null || hedo==undefined || isNaN(hedo))?0:hedo;
    hdf = (hdf=="" || hdf==null || hdf==undefined || isNaN(hdf))?0:hdf;
    hedf = (hedf=="" || hedf==null || hedf==undefined || isNaN(hedf))?0:hedf;
    heno = (heno=="" || heno==null || heno==undefined || isNaN(heno))?0:heno;
    henf = (henf=="" || henf==null || henf==undefined || isNaN(henf))?0:henf;
    rn = (rn=="" || rn==null || rn==undefined || isNaN(rn))?0:rn;
    hnf = (hnf=="" || hnf==null || hnf==undefined || isNaN(hnf))?0:hnf;
    rd = (rd=="" || rd==null || rd==undefined || isNaN(rd))?0:rd;
    are = (are=="" || are==null || are==undefined || isNaN(are))?0:are;

    Highcharts.setOptions({
        lang:{
            downloadCSV: 'Descargar  CSV',
            downloadJPEG: 'Download Imagen JPG',
            downloadPDF: 'Descargar PDF',
            downloadPNG: 'Descargar Imagen PNG',
            downloadSVG: 'Descargar SVG',
            downloadXLS:'Descargar XLS',
            exitFullscreen:"Salir de pantalla completa",
            loading:'Cargando...',
            
            noData:'No hay datos',
            printChart:'Imprimir grafico',
            resetZoom:'Reiniciar zoom', 
            viewFullscreen:'Ver en pantalla completa'
        }
    })
    //grafico torta
    Highcharts.chart('divordenesestado', {
    chart: {
        plotBackgroundColor: null,
        plotBorderWidth: null,
        plotShadow: false,
        type: 'pie'
    },
    title: {
        text: 'Horas Extras  - <?PHP echo $y;?>'
    },
    tooltip: {
        pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b> Cant:<b>{point.y}</b>'
    },
    accessibility: {
        point: {
            valueSuffix: '%'
        }
    },
    plotOptions: {
        pie: {
            allowPointSelect: true,
            cursor: 'pointer',
            dataLabels: {
                enabled: true,
                format: '<b>{point.name}</b>: {point.percentage:.1f} %'
            },
            colors : ['#fd7e14', '#ffc107', '#28a745', '#17a2b8', '#dc3545', '#6f42c1'],
            events: {
                    click: function (event) {
                        console.log(event.point.opcion);
                        //CRUDCORRESPONDENCIA('LISTAORDENESESTADO','',event.point.opcion,event.point.name)
                    }
            }
        }
    },
    series: [{
        name: 'Porcentaje',
        colorByPoint: true,
        data: [
            {
                name: 'HEDO',
                y: hedo,
                opcion:'HEDO',
                sliced: true,
                selected: true,           
            }, {
                name: 'HDF',
                y: hdf,
                opcion:'HDF',
            }, 
            {
                name: 'HEDF',
                y: hedf,
                opcion:'HEDF',
            }, 
            {
                name: 'HENO',
                y: heno,
                opcion:'HENO',
            },
            {
                name: 'HENF',
                y: henf,
                opcion:'HENF',
            },        
            {
                name: 'RN',
                y: rn,
                opcion:'RN',
            },        
            {
                name: 'HNF',
                y: hnf,
                opcion:'HNF',
            },        
            {
                name: 'ARE',
                y: are,
                opcion:'ARE',
            }
        ]
    }]
    });
    //grafico de barras
    Highcharts.chart('divhoraspormes', {

        title: {
            text: 'Tiempo Extra Mes - <?PHP echo $y;?>'
        },

        chart: {
            type: 'column'
        },

        colors: ['#28E', '#4A0'],

        xAxis: [{
            labels: {
                autoRotation: 0
            },
            //opposite: true,
            reversed: true,
            type: 'category'
        }],
        /*
        yAxis: [{
            min: -23,
            max: 0,
            visible: false
        }, {
            min: 0,
            max: 23,
            visible: false
        }],*/

        accessibility: {
            point: {
                descriptionFormatter: function (point) {
                    return (
                        point.options.custom.value + ' en el ' + point.series.options.custom.gender + ' ' + point.name + '.'
                    );
                }
            }
        },

        tooltip: {
            headerFormat: '',
            pointFormat: (
                '{series.options.custom.gender}'+ ' {point.name}<br />' +
                '{point.options.custom.value}'
            )
        },

        plotOptions: {
            series: {
                dataSorting: {
                    enabled: true,
                    sortKey: 'name'
                },
                keys: ['name', 'custom.value', 'y','custom.mes'], // 4th data position as custom property
                stacking: 'normal',
                events: {
                    click: function (event) {
                        console.log(event);
                        console.log(event.point.custom.mes);
                        
                        //CRUDCORRESPONDENCIA('LISTAORDENESMES','',event.point.custom.mes,'')
                        
                    }
                }
            }
        },

        series: [{
            name: 'Meses',
            xAxis: 0,
            yAxis: 0,
            custom: {
                gender: 'Mes'
            },
            data: [
                <?php $m = 1; $k = 0; $wh = "";
               
                while($k<12){ $me = $m; if($me<10){ $me = "0".$me; } $fecham = $y."-".$m."-01"; $mes = date("F",strtotime($fecham));  
                $mese = $y."-".$me;

                $sql = "
                    SELECT SUM(
                        ld.lid_hedo + ld.lid_hdf + ld.lid_hedf + 
                        ld.lid_heno + ld.lid_henf + ld.lid_rn + 
                        ld.lid_hnf + ld.lid_rd
                    ) AS tot
                    FROM tbl_liquidar_dias ld
                    WHERE DATE_FORMAT(ld.lid_fecha, '%Y-%m') = :mese $wh
                ";
            
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':mese', $mese);
                $stmt->execute();
                
                $dattot = $stmt->fetch(PDO::FETCH_ASSOC);
                $tot = isset($dattot['tot']) ? round($dattot['tot'], 0) : 0;
                    
                ?>
                ['<?php echo ucfirst($mes);?>', '<?php echo number_format($tot,0,'',','); ?>', <?php echo $tot;?>,'<?php echo $mese;?>'],
                <?php
                 $m++;  $k++;
                }
                ?>
            ]
        },]

    });

    </script>
    <div id="divordenestado" class="col-md-12"></div>
    <?php
}
else if($opcion=="FILTROSCOMPENSATORIOS")
{
    ?>
     <div class="col-md-4">
        <label for="CRUDINFORMES('LISTACOMPENSATORIOS')">Empleado</label>
        <select id="busempleado" multiple name="busempleado" class="form-control form-control-sm selectpicker" onchange= "CRUDINFORMES('LISTACOMPENSATORIOS')"  data-actions-box="true">
       
        <?php
            $selectEmp = new General();
            $selectEmp -> cargarEmpleados("");
        ?>
        </select>       
    </div>
    <?php
    echo "<script>INICIALIZARCONTENIDO();</script>";
    echo "<script>CRUDINFORMES('LISTACOMPENSATORIOS');</script>";
}
else if($opcion=="LISTACOMPENSATORIOS")
{
    ?>
    <div class="col-md-12">
        <div id="smartwizard" class="mt-2">
            <ul class="nav">
            
                <li><a class="nav-link" data-empleado = "<?php echo $id;?>" data-target="#step-0">Nomina</a></li>
                <li><a class="nav-link" data-empleado = "<?php echo $id;?>" data-target="#step-1">Logistica</a></li>
                
            </ul>          
            <div class="tab-content">
                <div id="step-0" class="tab-pane invoice table-responsive" role="tabpanel">
                <button type="button" class="btn btn-sm btn-default" onclick="CRUDINFORMES('EXPORTARCOMPENSATORIO',1,1)" >Exportar Por Año<i class="fas fa-file-excel text-success"></i></button>
                <button type="button" class="btn btn-sm btn-default" onclick="CRUDINFORMES('EXPORTARCOMPENSATORIO',1,2)" >Exportar Por Mes<i class="fas fa-file-excel text-success"></i></button>
                    <table id="tbCompensatorios" class="table table-bordered table-striped" style="width:100%">
                        <thead>
                            <tr>
                                <th class="dt-head-center align-middle bg-terracota">Empleado</th>
                                <th class="dt-head-center align-middle bg-terracota">Generados</th>
                                <th class="dt-head-center align-middle bg-terracota">Compensatorios</th>
                                <th class="dt-head-center align-middle bg-terracota">Por Mes</th>
                                <th class="dt-head-center align-middle bg-terracota">Por Año</th>
                                <th class="dt-head-center align-middle bg-terracota">Tomados</th>
                                <th class="dt-head-center align-middle bg-terracota">Remunerados</th>
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
                            </tr>
                        </tfoot>
                    </table>
                </div> 
                <div id="step-1" class="tab-pane invoice table-responsive" role="tabpanel">
                    <table id="tbCompensatoriosObra" class="table table-bordered table-striped" style="width:100%">
                        <thead>
                            <tr>
                                <th class="dt-head-center align-middle bg-terracota">Empleado</th>
                                <th class="dt-head-center align-middle bg-terracota">Generados</th>
                                <th class="dt-head-center align-middle bg-terracota">Compensatorios</th>
                        
                                <th class="dt-head-center align-middle bg-terracota">Tomados</th>
                                <th class="dt-head-center align-middle bg-terracota">Remunerados</th>
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
    </div>
    <script src="jsdatatable/informes/jscompensatorios.js?<?php echo time();?>"></script>
    <?php
}
else if($opcion=="FILTROSLIMITE")
{
    $gen = new General();
    ?>
     <div class="col-md-6">
        <label for="">Empleado</label>
        <select  id="selempleado" class="form-control form-control-sm selectpicker" onchange= "CRUDGENERAL('CARGARSEMANAS', 'selsemana', 'selmes', 'selano', 'JORNADA','','selempleado')" required data-parsley-error-message="Seleccionar empleado" data-parsley-errors-container="#msn-error1" >
        <option value=""></option>
        <?php
          
            $gen -> cargarEmpleados($emp);
        ?>
        </select>
        <span id="msn-error1"></span>
    </div>
    <div class="col-md-2">
        <label for="selano">Año</label>
        <select  name="selano" id="selano" class="form-control form-control-sm selectpicker" onchange="CRUDGENERAL('CARGARSEMANAS', 'selsemana', 'selmes', 'selano', 'JORNADA','','selempleado')"   data-live-search="true" >
            <?php
            //onchange="CRUDGENERAL('CARGARMES','','selmes','selano','','','selempleado')"           
            $gen -> cargarAnos(2020,date('Y'),$ano,"DESC",1);
            ?>

        </select>
    </div>
    <div class="col-md-3">
        <label for="selsemana">Semana</label>
        <select <?php echo $disinp;?> name="selsemana" id="selsemana" class="form-control form-control-sm selectpicker" onchange="CRUDINFORMES('LISTALIMITE', '')" required data-parsley-error-message="Seleccionar semana"  data-parsley-errors-container="#msn-error2" data-live-search="true" data-id="<?php echo $sem;?>">
        <?php 
        $sf = $semanaactual;        
        $gen->cargarSemanas(date('Y'),'',2,$sf,$sem,"DESC",1,$emp);    
        ?>
        </select>
        <span id="msn-error2"></span>
    </div>
    <?php
    echo "<script>INICIALIZARCONTENIDO();</script>";
    echo "<script>CRUDINFORMES('LISTALIMITE');</script>";
}
else if($opcion=="LISTALIMITE")
{
    ?>
    <div class="col-md-12">
        <div id="smartwizard" class="mt-2">
            <ul class="nav">
            
                <li><a class="nav-link" data-empleado = "<?php echo $id;?>" data-target="#step-0">Nomina</a></li>
                <li><a class="nav-link" data-empleado = "<?php echo $id;?>" data-target="#step-1">Logistica</a></li>
                
            </ul>          
            <div class="tab-content">
                
                <div id="step-0" class="tab-pane invoice table-responsive" role="tabpanel">
                   
                    <table id="tbLimites" class="table table-bordered table-striped" style="width:100%">
                        <thead>
                            <tr>
                                <th class="dt-head-center align-middle bg-terracota">Año</th>
                                <th class="dt-head-center align-middle bg-terracota">Semana</th>
                                <th class="dt-head-center align-middle bg-terracota">Desde</th>
                                <th class="dt-head-center align-middle bg-terracota">Hasta</th>
                                <th class="dt-head-center align-middle bg-terracota">Empleado</th>
                                <th class="dt-head-center align-middle bg-terracota">Total Extras</th>
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
                            </tr>
                        </tfoot>
                    </table>
                </div> 
                <div id="step-1" class="tab-pane invoice table-responsive" role="tabpanel">
                    <table id="tbLimitesObra" class="table table-bordered table-striped" style="width:100%">
                        <thead>
                            <tr>
                                <th class="dt-head-center align-middle bg-terracota">Año</th>
                                <th class="dt-head-center align-middle bg-terracota">Semana</th>
                                <th class="dt-head-center align-middle bg-terracota">Desde</th>
                                <th class="dt-head-center align-middle bg-terracota">Hasta</th>
                                <th class="dt-head-center align-middle bg-terracota">Empleado</th>
                                <th class="dt-head-center align-middle bg-terracota">Total Extras</th>
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
                            </tr>
                        </tfoot>
                    </table>
                </div>                         
            </div>
        </div>        
    </div>
    <script src="jsdatatable/informes/jslimites.js?<?php echo time();?>"></script>
    <?php
}
else if($opcion=="FILTROSCONTABILIDAD")
{
    $selectEmp = new General();
    $sql = "SELECT DISTINCT liq_inicio, liq_fin 
    FROM tbl_liquidar 
    ORDER BY liq_inicio DESC 
    LIMIT 1";

    $stmt = $conn->prepare($sql);
    $stmt->execute();

    $datfec = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($datfec) {
        $des = $datfec['liq_inicio'];
        $has = $datfec['liq_fin'];
    }


    ?>
     <div class="col-md-4">
        <label for="busempleado">Empleado</label>
        <select id="busempleado" multiple name="busempleado" class="form-control form-control-sm selectpicker" onchange= "CRUDINFORMES('LISTACONTABILIDAD')"  data-actions-box="true">       
        <?php           
            $selectEmp -> cargarEmpleados("");
        ?>
        </select>       
    </div>
    <div class="col-md-2">
        <label for="busfrecuencia">Frecuencia</label>
        <select id="busfrecuencia" name="busfrecuencia" class="form-control form-control-sm selectpicker" onchange= "CRUDINFORMES('LISTACONTABILIDAD')"  data-actions-box="true">       
            <option value="1">Por Rango de Fecha</option>
            <option value="2">Por periodo Liquidado</option>
        </select>       
    </div>
    <div class="col-md-2 hide" id="divperiodos">
        <label for="busperiodos">Periodos Liquidados</label>
        <select id="busperiodos" name="busperiodos" class="form-control form-control-sm selectpicker" onchange= "CRUDINFORMES('LISTACONTABILIDAD')"  data-actions-box="true">       
        <?php           
            $selectEmp -> cargarPeriodosLiquidados("");
        ?>
        </select>       
    </div>
    <div class="col-6 col-sm-6 col-md-2" id="divdesde">
        <label for="busdesde">Desde</label>
        <input type="date" name="busdesde" id="busdesde" class="form-control form-control-sm" onchange="CRUDINFORMES('LISTACONTABILIDAD', '') " required data-parsley-error-message="Seleccionar fecha desde" value="<?php echo $des;?>">
    </div>
    <div class="col-6 col-sm-6 col-md-2" id="divhasta">
        <label for="bushasta">Hasta</label>
        <input type="date" name="bushasta" id="bushasta" class="form-control form-control-sm" onchange="CRUDINFORMES('LISTACONTABILIDAD', '')" required data-parsley-error-message="Seleccionar fecha hasta" value="<?php echo $has;?>">
    </div>
    <div class="col-md-2">
        <label for="bustipo">Tipo Informe</label>
        <select id="bustipo" name="bustipo" class="form-control form-control-sm selectpicker" onchange= "CRUDINFORMES('LISTACONTABILIDAD')"  data-actions-box="true">       
        <option value="1">Horas</option>
        <option value="2">Alimentación</option>
        </select>       
    </div>
    <div class="col-2 col-sm-2 col-md-2">
        <br>
        <button type="button" class="btn btn-sm btn-default" onclick="CRUDINFORMES('EXPORTARCONTABILIDAD')" >Exportar <i class="fas fa-file-excel text-success"></i></button>
    </div>
    <script>
        $('#busfrecuencia').on('change',function(){
            var fre = $(this).val();
            if(fre==2)
            {
                $('#divperiodos').removeClass('hide');
                $('#divdesde').addClass('hide');
                $('#divhasta').addClass('hide');
            }
            else
            {
                $('#divperiodos').addClass('hide');
                $('#divdesde').removeClass('hide');
                $('#divhasta').removeClass('hide');
            }
        })
    </script>
    <?php
  
    echo "<script>CRUDINFORMES('LISTACONTABILIDAD');</script>";
    echo "<script>INICIALIZARCONTENIDO();</script>";
}
else if($opcion=="LISTACONTABILIDAD")
{
    $tip = $_POST['tip'];
    $des = $_POST['des'];
    $has = $_POST['has'];
    
    if($des=="" || $has=="")
    {
        echo "<div class='alert alert-info mt-1'>Seleccionar rango de fechas</div>";
    }
    else
    if($tip==1)
    {
        ?>
        <table id="tbContabilidad" class="table table-bordered table-striped table-valign-middle" style="font-size:12px">
            <thead>
                <tr>
                    <th class="dt-head-center" rowspan="2">EMPLEADO</th>
                    <th class="dt-head-center" rowspan="2">CEDULA</th>
                    <th class="dt-head-center" rowspan="2">BASICO</th>
                    <th class="dt-head-center p-0 bg-white">002</th>
                    <th class="dt-head-center p-0 bg-white">004</th>
                    <th class="dt-head-center p-0 bg-white">014</th>
                    <th class="dt-head-center p-0 bg-white">003</th>
                    <th class="dt-head-center p-0 bg-white">005</th>
                    <th class="dt-head-center p-0 bg-white">006</th>
                    <th class="dt-head-center p-0 bg-white">013</th>
                    <th class="dt-head-center p-0 bg-white">008</th>
                    <th class="dt-head-center p-0 bg-white">010</th>

                    <th class="dt-head-center bg-purple"  rowspan="2">TOTAL HORAS</th>  
                    <th class="dt-head-center bg-purple"  rowspan="2">TOTAL HORAS</th>  
                    <th class="dt-head-center bg-purple"  rowspan="2">HORAS A AJUSTAR</th>  
                    <th class="dt-head-center bg-purple"  rowspan="2">HORAS AJUSTADAS</th>  
                    <th class="dt-head-center bg-green"  rowspan="2">HORAS FESTIVA COMO BONIFICACION</th>  
                    <th class="dt-head-center bg-green"  rowspan="2">HORAS ORDINARIAS COMO BONIFICACION</th>  

                    <th class="dt-head-center p-0 bg-white">002</th>
                    <th class="dt-head-center p-0 bg-white">010</th>
                    <th class="dt-head-center p-0 bg-white">006</th>
                    <th class="dt-head-center p-0 bg-white">008</th>
                    <th class="dt-head-center p-0 bg-white">003</th>
                    <th class="dt-head-center p-0 bg-white">005</th>
                    <th class="dt-head-center p-0 bg-white">004</th>
                    <th class="dt-head-center p-0 bg-white">013</th>
                    <th class="dt-head-center p-0 bg-white">014</th>
                
                    <th class="dt-head-center p-0 bg-white">002</th>
                    <th class="dt-head-center p-0 bg-white">006</th>
                    <th class="dt-head-center p-0 bg-white">008</th>
                    <th class="dt-head-center p-0 bg-white">004</th>
                    <th class="dt-head-center p-0 bg-white">014</th>
                    <th class="dt-head-center p-0 bg-white">003</th>
                    <th class="dt-head-center p-0 bg-white">013</th>
                    <th class="dt-head-center p-0 bg-white">005</th>
                    <th class="dt-head-center p-0 bg-white">010</th>
                            
                    <th class="dt-head-center p-0 bg-secondary" rowspan="2">VALOR TOTAL SIN DEDUCCIONES</th>
                    
                    <th class="dt-head-center bg-green"  rowspan="2">VALOR HORA EXTRA COMO BONIFICACION</th>  
                    <th class="dt-head-center bg-green"  rowspan="2">VALOR EXTRA DIURNA FESTIVA COMO BONIFICACION</th>  
                    <th class="dt-head-center bg-green"  rowspan="2">TOTAL BONIFICACION</th>  
                </tr>
                <tr>
                    <th class="dt-head-center bg-blue-dark">VALOR EXTRA ORDINARIA</th>
                    <th class="dt-head-center bg-blue-dark">VALOR EXTRA DIURNAS FESTIVA</th>
                    <th class="dt-head-center bg-blue-dark">VALOR DIURNA FESTIVA</th>
                    <th class="dt-head-center bg-blue-dark">VALOR EXTRA NOCTURNA ORDINARIA</th>
                    <th class="dt-head-center bg-blue-dark">VALOR EXTRA NOCTURNA FESTIVA</th>
                    <th class="dt-head-center bg-blue-dark">VALOR RECARGO NOCTURNO</th>
                    <th class="dt-head-center bg-blue-dark">VALOR RECARGO DOMINICAL</th>
                    <th class="dt-head-center bg-blue-dark">VALOR RECARGO NOCTURNO DOMINICAL </th>
                    <th class="dt-head-center bg-blue-dark">VALOR RECARGO EXTRAS ORDINARIA</th> 

                                

                    <th class="dt-head-center bg-terracota">TOTAL EXTRAS ORDINARIAS LABORADAS</th>
                    <th class="dt-head-center bg-terracota">TOTAL RECARGO EXTRAS ORDINARIAS</th>
                    <th class="dt-head-center bg-terracota">TOTAL RECARGO NOCTURNO</th>
                    <th class="dt-head-center bg-terracota">TOTAL RECARGO NOCTURNAS DOMINICAL</th>
                    <th class="dt-head-center bg-terracota">TOTAL EXTRAS NOCTURNAS</th>
                    <th class="dt-head-center bg-terracota">TOTAL EXTRAS NOCTURNAS FESTIVA</th>
                    <th class="dt-head-center bg-terracota">TOTAL EXTRAS DIURNAS FESTIVAS</th>
                    <th class="dt-head-center bg-terracota">TOTAL RECARGO DOMINICAL</th>
                    <th class="dt-head-center bg-terracota">TOTAL EXTRAS FESTIVAS</th> 

                    <th class="dt-head-center bg-blue-dark">VALOR EXTRAS ORDINARIAS</th>
                    <th class="dt-head-center bg-blue-dark">VALOR RECARGO NOCTURNO</th>
                    <th class="dt-head-center bg-blue-dark">VALOR RECARGO NOCTURNO DOMINICAL</th>
                    <th class="dt-head-center bg-blue-dark">VALOR EXTRAS FESTIVAS</th>
                    <th class="dt-head-center bg-blue-dark">VALOR DIURNA FESTIVAS</th>
                    <th class="dt-head-center bg-blue-dark">VALOR EXTRAS NOCTURNO</th>
                    <th class="dt-head-center bg-blue-dark">VALOR RECARGO DOMINICAL SEMANA</th>
                    <th class="dt-head-center bg-blue-dark">VALOR EXTRAS DOMINICAL NOCTURNO</th>
                    <th class="dt-head-center bg-blue-dark">VALOR RECARGO EXTRAS ORDINARIAS</th> 
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
        <script src="jsdatatable/informes/jscontabilidad.js?v=1"></script>
        <?php
    }
    else
    {

        $emp = "";
        $emp1 = "''";
        if(isset($_POST['emp']))
        {
            $emp = $_POST['emp'];
            $emp =  implode(', ', (array)$emp) ; 
            if($emp==""){$emp1="''";}else {$emp1=$emp;}
        }

        $sql = "
            SELECT 
                CONCAT(u.usu_apellido, ' ', u.usu_nombre) AS nom, 
                ld.lid_fecha AS fec, 
                SUM(ld.lid_val_alimentacion) AS alimentacion
            FROM tbl_usuarios u
            JOIN tbl_liquidar l ON l.usu_clave_int = u.usu_clave_int
            JOIN tbl_liquidar_dias ld ON ld.liq_clave_int = l.liq_clave_int
            WHERE 
                l.liq_inicio = :des 
                AND l.liq_fin = :has 
                AND ld.lid_alimentacion = 1 
                AND (
                    u.usu_clave_int IN ($emp1) 
                    OR :emp IS NULL 
                    OR :emp = ''
                )
            GROUP BY u.usu_nombre, u.usu_apellido, ld.lid_fecha
            ORDER BY nom, ld.lid_fecha
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':des', $des);
        $stmt->bindParam(':has', $has);
        $stmt->bindParam(':emp', $emp); // <- cuidado: solo si $emp es escalar

        $stmt->execute();

        ?>
        <table id="tbAlimentacion" class="table table-bordered table-striped table-valign-middle" style="font-size:12px">
        <thead>
            <tr>
                <th class="dt-head-center bg-terracota">EMPLEADO</th>
                <th class="dt-head-center bg-terracota">FECHA</th>
                <th class="dt-head-center bg-terracota">VALOR</th>
            </tr>
        </thead>
        <?php
        while ($dat = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $fec = $dat['fec'];
            $nom = $dat['nom'];
            $ali = $dat['alimentacion'];

            ?>
            <tr>
                <td><?php echo strtoupper($nom);?></td>
                <td><?php echo $fec;?></td>
                <td>$ <?php echo number_format($ali, 0,',','.'); ?></td>
                
            </tr>
            <?php
        }
        ?>
        <tfoot>
            <tr>
                <th></th>
                <th></th>
                <th></th>
               
            </tr>
        </tfoot>
        </table>
        <script src="jsdatatable/informes/jsalimentacion.js?<?php echo time();?>"></script>
        <?php
    }
}
else if($opcion=="FILTROSHORAS")
{
    $reg = new General();
    $datr = $reg->fnReglas();
    //reg_hor_mes hmes, reg_hor_semana hsemana, reg_lim_extras limex, reg_lim_extras_semana limexsemana
    $hmes = $datr['hmes'];
    $hsemana = $datr['hsemana'];
    $limex = $datr['limex']; // LIMITE DIA
    $limexsemana = $datr['limexsemana']; // LIMITE SEMANA 

    ?>
     <div class="col-md-2 hide">
        <label for="seltipo">Tipo:</label>
        <select  name="seltipo" id="seltipo" class="form-control form-control-sm selectpicker" onchange="CRUDLIQUIDAR('CARGAREMPLEADO', '')" <?php echo $disa;?>>

            <?php if($p121>0){ ?> <option value="1" <?php if($tip==1){ echo "selected"; }?>>Por empleado</option><?php } ?>
            <?php if($p122>0){ ?> <option value="2" <?php if($tip==2){ echo "selected"; }?>>Por Obra</option><?php } ?>
        </select>
    </div>
    <div class="col-md-4">
        <label for="busempleado">Empleado</label>
        <select id="busempleado" multiple name="busempleado" class="form-control form-control-sm selectpicker" onchange= "CRUDINFORMES('LISTAHORAS')"  data-actions-box="true">    
        <?php           
            $reg -> cargarEmpleados("");
        ?>
        </select>       
    </div>
    <div class="col-6 col-sm-6 col-md-2">
        <label for="busdesde">Desde:</label>
        <input type="date" name="busdesde" id="busdesde" class="form-control form-control-sm" onchange="CRUDINFORMES('LISTAHORAS', '') " required data-parsley-error-message="Seleccionar fecha desde" value="">
    </div>
    <div class="col-6 col-sm-6 col-md-2">
        <label for="bushasta">Hasta:</label>
        <input type="date" name="bushasta" id="bushasta" class="form-control form-control-sm" onchange="CRUDINFORMES('LISTAHORAS', '')" required data-parsley-error-message="Seleccionar fecha hasta" value="">
    </div>
    <div class="col-2 col-sm-2 col-md-2 hide">
        <br>
        <button type="button" class="btn btn-sm btn-default" onclick="CRUDINFORMES('EXPORTARHORAS')" >Exportar <i class="fas fa-file-excel text-success"></i></button>
    </div>
    <div class="col-md-12">
        <div class="row mt-1">
            <div class="col-md-3 bg-danger p-2 text-white">Supera las <strong><?php echo $hmes;?> horas</strong> mes</div>
            <div class="col-md-3 bg-danger p-2 text-white bg-opacity-75">Supera el limite de <strong><?php echo $hsemana;?> horas</strong>   semana</div>
            <div class="col-md-3 bg-danger p-2 text-dark bg-opacity-50">Supera el limite de <strong><?php echo $limexsemana;?> horas</strong> extras semana</div>
            <div class="col-md-3 bg-danger p-2 text-dark bg-opacity-25">Supera el limite de <strong><?php echo $limex;?> horas</strong>  extras dia</div>
        </div>
        <div id="smartwizard" class="mt-2">
            <ul class="nav">
                <li><a class="nav-link" data-empleado = "" data-target="#step-0">Limite Horas Dia</a></li>
                <li><a class="nav-link" data-empleado = "" data-target="#step-1">Limite Horas Semana</a></li>
                <li><a class="nav-link" data-empleado = "" data-target="#step-2">Limite Horas Mes</a></li>
            </ul>          
            <div class="tab-content">
                <div id="step-0" class="tab-pane invoice table-responsive" role="tabpanel"></div> 
                <div id="step-1" class="tab-pane invoice table-responsive" role="tabpanel"></div> 
                <div id="step-2" class="tab-pane invoice table-responsive" role="tabpanel"></div>  
            </div>
        </div>
        <script>
            localStorage.setItem('step','0');
            $('#smartwizard').smartWizard({
                selected: 0, // Initial selected step, 0 = first step
                theme: 'arrows', // theme for the wizard, related css need to include for other than default theme
                justified: false, // Nav menu justification. true/false
                darkMode: false, // Enable/disable Dark Mode if the theme supports. true/false
                autoAdjustHeight: false, // Automatically adjust content height
                cycleSteps: false, // Allows to cycle the navigation of steps
                backButtonSupport: true, // Enable the back button support
                enableURLhash: true, // Enable selection of the step based on url hash
                transition: {
                    animation: 'none', // Effect on navigation, none/fade/slide-horizontal/slide-vertical/slide-swing
                    speed: '400', // Transion animation speed
                    easing:'' // Transition animation easing. Not supported without a jQuery easing plugin
                },
                toolbarSettings: {
                    toolbarPosition: 'top', // none, top, bottom, both
                    toolbarButtonPosition: 'right', // left, right, center
                    showNextButton: false, // show/hide a Next button
                    showPreviousButton: false, // show/hide a Previous button
                    /* toolbarExtraButtons: [
                        $('<button type="button"></button>').text('Generar Liquidación')
                                .addClass('btn btn-success')
                                .on('click', function(){ 
                                    var emp = localStorage.getItem('emp');
                                    CRUDLIQUIDAR('GENERARPROFORMA','',emp);
                                                                
                        })
                    ]*/ // Extra buttons to show on toolbar, array of jQuery input/buttons elements
                },
                anchorSettings: {
                    anchorClickable: true, // Enable/Disable anchor navigation
                    enableAllAnchors: true, // Activates all anchors clickable all times
                    markDoneStep: true, // Add done state on navigation
                    markAllPreviousStepsAsDone: false, // When a step selected by url hash, all previous steps are marked done
                    removeDoneStepOnNavigateBack: false, // While navigate back done step after active step will be cleared
                    enableAnchorOnDoneStep: true // Enable/Disable the done steps navigation
                },
                keyboardSettings: {
                    keyNavigation: false, // Enable/Disable keyboard navigation(left and right keys are used if enabled)
                    keyLeft: [37], // Left key code
                    keyRight: [39] // Right key code
                },
                lang: { // Language variables for button
                    next: 'Siguiente',
                    previous: 'Anterior'
                },
                disabledSteps: [], // Array Steps disabled
                errorSteps: [], // Highlight step with errors
                hiddenSteps: [] // Hidden steps
            });

            $("#smartwizard").on("stepContent", function(e, anchorObject, stepIndex, stepDirection) {
                var elmForm = "step-" + stepIndex;
                localStora('step', stepIndex);
                CRUDINFORMES('LISTAHORAS');           
                //$("#smartwizard"+ d.Id).smartWizard("loader", "show");     
            });
        </script>
    </div>
    <?php
    echo "<script>CRUDINFORMES('LISTAHORAS');</script>";
    echo "<script>INICIALIZARCONTENIDO();</script>";
}
else if($opcion=="LISTAHORAS")
{
    $opc = $_POST['opc'];
    $tit = ($opc==0?"DIA":($opc==1?"SEMANA":"MES"));
    ?>

    
    <table id="tbHoras_<?php echo $opc;?>" class="table table-bordered table-striped table-valign-middle" style="font-size:12px">
        <thead>
            <tr>
                <th class="dt-head-center" rowspan="2">EMPLEADO</th>
                <th class="dt-head-center" rowspan="2">CEDULA</th>
                <th class="dt-head-center" rowspan="2"><?php echo $tit;?></th>
                <th class="dt-head-center" rowspan="2">RANGO</th>
                <th  class="dt-head-center p-0 bg-blue-dark" rowspan="2">TOTAL HORAS LABORADAS</th>
                <th class="dt-head-center p-0 bg-white">002</th>
                <th class="dt-head-center p-0 bg-white">010</th>
                <th class="dt-head-center p-0 bg-white">006</th>
                <th class="dt-head-center p-0 bg-white">008</th>
                <th class="dt-head-center p-0 bg-white">003</th>
                <th class="dt-head-center p-0 bg-white">005</th>
                <th class="dt-head-center p-0 bg-white">004</th>
                <th class="dt-head-center p-0 bg-white">013</th>
                <th class="dt-head-center p-0 bg-white">014</th>                
                <th  class="dt-head-center p-0 bg-secondary" rowspan="2">TOTAL EXTRAS</th>
            </tr>
            <tr>
                <th class="dt-head-center bg-terracota">HEDO</th>
                <th class="dt-head-center bg-terracota">AJ REC EXTRA</th>
                <th class="dt-head-center bg-terracota">RN</th>
                <th class="dt-head-center bg-terracota">HNF</th>
                <th class="dt-head-center bg-terracota">HENO</th>
                <th class="dt-head-center bg-terracota">HENF</th>
                <th class="dt-head-center bg-terracota">HEDF</th>
                <th class="dt-head-center bg-terracota">RD</th>
                <th class="dt-head-center bg-terracota">HDF</th>                
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
            </tr>
        </tfoot>
    </table>
    <script src="jsdatatable/informes/jshoras.js?<?php echo time();?>"></script>
    <?php
}