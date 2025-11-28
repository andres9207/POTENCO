<?php
class general {
    // LISTA DE MES O SEGUN EL MIN O MAXIMO QUE SE ENVIE
    function selSelect($min = 0,$max = 0,$sel = "", $orden = "DESC")
    {
        if($orden=="DESC"){ 
            for($m=$max;$m>$min;$m--)
            {
                $selected = "";
                if($m==$sel){ $selected = "selected"; }
                echo "<option ".$selected." value='".$m."'>".$m."</option>";
            }
        }
        else
        { 
            for($m=$min;$m<$max;$m++)
            {
                $selected = "";
                if($m==$sel){ $selected = "selected"; }
                echo "<option ".$selected." value='".$m."'>".$m."</option>";
            }
        }        
    } 
    // LISTA DE AÑOS SEGUN LOS FESTIVOS REGISTRADOS
    function cargarPeriodos($selini = "", $selfin = "", $tipo = 2)
    {
        global $conn;
        $query = "select distinct liq_inicio ini, liq_fin fin from tbl_liquidar where liq_tipo = 2 ";
        // $conperiodo->execute();
        // $numperiodo = mysqli_num_rows($conperiodo);
        $periodos = array();
        foreach ($conn->query($query) as $row) {
        
            $ini = $row['ini'];
            $fin = $row['fin'];
            $selected = "";
            if($selini==$ini and $selfin==$fin){ $selected = "selected"; }
            $periodos[] = array("id"=>"", "ini"=>$ini,"fin"=>$fin, "nom"=>$ini." hasta ".$fin);
            if($tipo==1)
            {               
                echo "<option data-inicio = '".$ini."' data-fin = '".$fin."' ".$selected." value=''>".$ini." hasta ".$fin."</option>";
            }
        }
        if($tipo==2)
        {
            echo json_encode($periodos);
        }
    }
    // LISTA DE AÑOS SEGUN LOS FESTIVOS REGISTRADOS
    function cargarAnos($min,$max, $sel = "", $orden = "DESC", $tipo = 2)
    {
        global $conn;
        $maxi =  $max + 1;
        $query = "SELECT DISTINCT date_format(fes_fecha,'%Y') as ano FROM tbl_festivos WHERE date_format(fes_fecha,'%Y') <= ".$maxi." ORDER BY date_format(fes_fecha,'%Y')  ".$orden;

        $anos = array();

        foreach ($conn->query($query) as $row) {
            $an   = $row['ano'];
            $selected = "";
            if($an==$sel){ $selected = "selected"; }
            $anos[] = array("id"=>$an, "nom"=>$an);
            if($tipo==1)
            {               
                echo "<option ".$selected." value='".$an."'>".$an."</option>";
            }
        }
        if($tipo==2)
        {
            echo json_encode($anos);
        }
    }

    function cargarMes($ano = "",$min = 1,$max = 12,$sel = "",$orden="DESC",$tipo=2)
    {
        setlocale(LC_ALL,"es_ES.utf8","es_ES","esp");
        // $fechave = "".$datf['an']."-".$datf['me']."-01";
      
        if($ano==date('Y')){ $max = date('n');}
        $meses = array();
        if($orden=="DESC")
        { 
            for($m=$max;$m>$min;$m--)
            {
                $selected = "";
                //$nommes = date("M",strtotime($ano."-".$m."-01"));
                $nommes = date("F", strtotime($ano."-".$m."-01"));
                $meses[] = array("id"=>$m, "nom"=>ucfirst($nommes));
                
                //if($m==$sel){ $selected = "selected"; }
                if($m==$sel){ $selected = "selected"; }
                $meses[] = array("id"=>$m, "nom"=>ucfirst($nommes));
                if($tipo==1)
                {
                    echo "<option ".$selected." value='".$m."'>".ucfirst($nommes)."</option>";
                }
            }
        }
        else
        { 
            for($m=$min;$m<$max;$m++)
            {
                $selected = "";
                $nommes = date("M",strtotime($ano."-".$m."-01"));
               
                if($m==$sel){ $selected = "selected"; }
                $meses[] = array("id"=>$m, "nom"=>ucfirst($nommes));
                if($tipo==1)
                {
                    echo "<option ".$selected." value='".$m."'>".ucfirst($nommes)."</option>";
                }
                // 
            }
        }      
        if($tipo==2)
        {
            echo json_encode($meses);
        }
    }

    // LISTA DE SEMANAS
    function cargarSemanas($ano = 0, $mes = 1, $min = 0, $max = 0, $sel = "", $orden = "DESC", $tipo = 1, $emp = 0)
    {
        global $conn;
        $semanas = [];

        $rango = ($orden == "DESC") ? range($max, $min) : range($min, $max);
        if ($orden == "DESC") {
            rsort($rango);
        } else {
            sort($rango);
        }

        foreach ($rango as $m) {
            $selected = "";
            $sStartTS = WeekToDate($m, $ano);
            $y = date("y", $sStartTS);
            $sem = ($m < 10) ? $y . "0" . $m : $y . $m;
            $sema = ($m < 10) ? $ano . "0" . $m : $ano . $m;

            $classSem = "";
            $subSem = "Sin iniciar";

            if ($emp > 0) {
                // Verifica si ya tiene jornada creada
                $stmt = $conn->prepare("SELECT jor_clave_int, jor_estado FROM tbl_jornada WHERE usu_clave_int = :emp AND jor_semana = :sema");
                $stmt->execute([':emp' => $emp, ':sema' => $sema]);
                $datvs = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($datvs) {
                    $ests = $datvs['jor_estado'];
                    $classSem = ($ests == 0) ? "bg-warning" : (($ests == 1) ? "bg-success" : "");
                    $subSem = ($ests == 0) ? "Por Aprobar" : (($ests == 1) ? "Cerrada" : "");
                } else {
                    // Verifica si hay registros de jornada_dias sin cerrar
                    $stmt = $conn->prepare("SELECT COUNT(*) FROM tbl_jornada_dias WHERE usu_clave_int = :emp AND jod_semana = :sema AND jod_total > 0");
                    $stmt->execute([':emp' => $emp, ':sema' => $sema]);
                    $numini = $stmt->fetchColumn();
                    if ($numini > 0) {
                        $classSem = "bg-danger";
                        $subSem = "Por Cerrar";
                    }
                }
            }

            // Obtener fecha lunes y domingo de esa semana
            $stmt = $conn->prepare("SELECT 
                STR_TO_DATE(:semaLun, '%x%v %W') AS LUN, 
                STR_TO_DATE(:semaDom, '%x%v %W') AS DOM");
            $stmt->execute([
                ':semaLun' => $sema . ' Monday',
                ':semaDom' => $sema . ' Sunday'
            ]);
            $datld = $stmt->fetch(PDO::FETCH_ASSOC);
            $sLunes = $datld['LUN'];
            $sDomingo = $datld['DOM'];
            $subtext = $sLunes . " - " . $sDomingo;

            $semanas[] = [
                "id" => $sema,
                "nom" => $sema,
                "subtext" => $subtext,
                "inicio" => $sLunes,
                "fin" => $sDomingo,
                "classSem" => $classSem,
                "subSem" => $subSem
            ];

            if ($tipo == 1) {
                $selected = ($sema == $sel) ? "selected" : "";
                echo "<option data-subtext='$subSem' class='$classSem' data-inicio='$sLunes' data-fin='$sDomingo' $selected value='$sema'>$sema ($subtext)</option>";
            }
        }

        if ($tipo != 1) {
            echo json_encode($semanas);
        }
    }

    //LISTA DE OBRAS
    function cargarHoras($horai,$sel = "",$hf = 24,$tipo = 1)
    {
        $horai = "00:05";
        $horai = ($horai=="")?$sel: $horai;
        $datos = array();
        if($horai!="")
        {  
                
           
            $hi = str_replace(":","",substr($horai,0,2));
            $mi = str_replace(":","",substr($horai,2,3));
            $mi+= ($horai!="00:00")?0:0;
        
            while($hi <= $hf)
            {
                
                if($hi==$hf)
                {
                    $hi = ($hi*1);
                    $hi = ($hi<10)?"0".$hi:$hi;
                    $ho = $hi.":00";
                    $ho = ($ho=="24:00")?"00:00":$ho;
                    $ho12  = date("g:i a", strtotime($ho));
                    if( $ho==$sel){ $selected = "selected"; }else{ $selected = ""; }
                    $datos[] = array('id' => $ho, 'literal' => $ho12,"selected"=>$selected);
                    if($tipo==1)
                    {  
                        echo "<option ".$selected." value='".$ho."'>".$ho12."</option>";
                    }
                }
                else
                {
                    $hi = ($hi*1);
                    $hi = ($hi<10)?"0".$hi:$hi;
                    while($mi < 60)
                    {
                        $selected = "";
                        if($mi == 0 )
                        {
                            $ho = $hi.":00";
                        }
                        else
                        if($mi == 5)
                        {
                            $ho = $hi.":05";                       
                        }
                        else
                        {
                            $ho = $hi.":".$mi;
                        }
                        $ho = ($ho=="24:00")?"00:00":$ho;
                        $ho12  = date("g:i a", strtotime($ho));
                        if( $ho==$sel){ $selected = "selected"; }else{ $selected = ""; }
                        $datos[] = array('id' => $ho, 'literal' => $ho12,"selected"=>$selected);
                        if($tipo==1)
                        {  
                            echo "<option ".$selected." value='".$ho."'>".$ho12."</option>";
                        }
                        
                        /*else if($mi<60)
                        {
                            $ho = $hi.":".$mi;
                            $ho12  = date("g:i a", strtotime($ho));
                            if( $ho == $sel){ $selected = "selected"; }else{ $selected = ""; }
                            $datos[] = array('id' => $ho, 'literal' => $ho12,"selected"=>$selected);
                            if($tipo==1)
                            { 
                            echo "<option ".$selected." value='".$ho."'>".$ho12."</option>";
                            }
                        }
                        else
                        if($mi == 60 )
                        {
                            //$hi = $hi + 1;
                            if($hi<10){ $ho = "0".($hi+1).":00"; }else{
                                $ho =  ($hi+1).":00";
                            }
                            
                            $ho12  = date("g:i a", strtotime($ho));
                            if( $ho ==$sel){ $selected = "selected"; }else{ $selected = ""; }
                            $datos[] = array('id' => $ho, 'literal' => $ho12,"selected"=>$selected); 
                            if($tipo==1)
                            { 
                            echo "<option ".$selected." value='".$ho."'>".$ho12." - </option>";
                            }
                        }*/
                        
                        $mi+=5;                		
                    }
                }
                $hi++;
                $mi = "00";
            }
        }      
        if($tipo!=1)
        {
            echo json_encode($datos);
        }
    }

    // LISTA DE OBRAS
    function cargarObras($sel = "", $tip = 1)
    {
        global $conn;
    
        $sql = "SELECT obr_clave_int AS id, CONCAT(obr_nombre, '-', obr_nom_proyecto) AS nom, obr_cencos AS cen 
                FROM tbl_obras 
                WHERE est_clave_int = 1";
    
        $stmt = $conn->prepare($sql);
        $stmt->execute();
    
        $obras = [];
    
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $id  = $row['id'];
            $nom = $row['nom'];
            $cen = $row['cen'];
            $selected = ($id == $sel) ? "selected" : "";
    
            if ($tip == 1) {
                echo "<option $selected value='$id'>$nom - $cen</option>";
            } else {
                $obras[] = ["id" => $id, "nom" => "$nom - $cen"];
            }
        }
    
        if ($tip != 1) {
            echo json_encode($obras);
        }
    }

    // LISTA DE ESTADOS
    function cargarEstados($sel = "", $tip = 1)
    {
        global $conn;
        $query = "SELECT est_clave_int id, est_nombre nom from tbl_estados WHERE est_clave_int != 3 ";      
        $obra = array();
        foreach ($conn->query($query) as $row) {
            $id  = $row['id'];
            $nom = $row['nom'];
            $selected = "";
            if($id==$sel){ $selected = "selected"; }
            if($tip==1)
            {
                echo "<option ".$selected." value='".$id."'>".ucfirst($nom)."</option>";
            }
            else
            {
                $obra[] = array("id" =>$id, "nom" =>  ucfirst($nom) );
            }           
        }
        if($tip!=1)
        {
            echo json_encode($obra);
        }
    }

    //TIPO DE NOVEDADES 
    function cargarTipoNovedad($sel = "", $tip = 1, $opc = 1)
    {
        global $conn;
        $query = "SELECT tin_clave_int id, tin_nombre nom, tin_suma su from tbl_tipo_novedad WHERE  tin_aplica = 2 order by nom asc";
        if($opc==2)
        {
            $query ="SELECT tin_clave_int id, tin_nombre nom, tin_suma su from tbl_tipo_novedad WHERE tin_aplica = 1";
        }

        if($opc==3)
        {
            $query ="SELECT tin_clave_int id, tin_nombre nom, tin_suma su from tbl_tipo_novedad ";
        }
        
        $novedad = array();
        foreach ($conn->query($query) as $row) {
       
            $id  = $row['id'];
            $nom = $row['nom'];
            $sum = $row['su'];//
            $selected = "";
            if($id==$sel){ $selected = "selected"; }
            if($tip==1)
            {
                echo "<option data-sum='".$sum."' ".$selected." value='".$id."'>".$nom."</option>";
            }
            else
            {
                $novedad[] = array("id" =>$id, "nom" => $nom );
            }           
        }
        if($tip!=1)
        {
            echo json_encode($novedad);
        }
    }

    //LISTA DE EMPLEADOS
    function cargarEmpleados($sel = "",$tip = 1)
    {
        global $conn;
        $query = "SELECT u.usu_clave_int id, concat(usu_apellido,' ',usu_nombre) nom, usu_correo cor from tbl_usuarios u WHERE (u.est_clave_int = 1) or usu_clave_int = '".$sel."' order by usu_apellido";
        $empleados = array();
        foreach ($conn->query($query) as $row) {
        
            $id  = $row['id'];
            $nom = $row['nom'];
            $cor = $row['cor'];
            $selected = "";
            if($id==$sel){ $selected = "selected"; }
            if($tip==1)
            {
                echo "<option data-subtext= '".$cor."' ".$selected." value='".$id."'>".$nom."</option>";
            }
            else
            {
                $empleados[] = array("id" =>$id, "nom" => $nom, "cor" => $cor );  
            }
        }
        if($tip!=1)
        {
            echo json_encode($empleados);
        }
    }


    function cargarPeriodosLiquidados($sel = "",$tip = 1)
    {
        global $conn;
        $query = "SELECT distinct liq_inicio, liq_fin from tbl_liquidar order by liq_inicio desc";
        $periodos = array();
        foreach ($conn->query($query) as $row) {
        
            $inicio = $row['liq_inicio'];
            $fin    = $row['liq_fin'];
            $text = "".$inicio." a ".$fin;
            $selected = "";
            if($sel==$text){ $selected = "selected"; }
            if($tip==1)
            {
                echo "<option data-inicio='".$inicio."' data-fin='".$fin."' ".$selected." value='".$text."'>".$text."</option>";
            }
            else
            {
                $periodos[] = array("id" =>$text, "nom" => $text, "inicio" => $inicio, "fin"=>$fin );  
            }
        }
        if($tip!=1)
        {
            echo json_encode($periodos);
        }
    }

    //LISTA DE HORAS CREADOS
    function cargarHorarios($sel = "", $tip = 1)
    {
        global $conn;
        $query = "SELECT hor_clave_int id, hor_nombre nom from tbl_horarios WHERE est_clave_int = 1 or hor_clave_int = '".$sel."'";
        $horarios = array();
        foreach ($conn->query($query) as $row) {        
            $id  = $row['id'];
            $nom = $row['nom'];
            $selected = "";
            if($id==$sel){ $selected = "selected"; }
            if($tip==1)
            {
                echo "<option ".$selected." value='".$id."'>".$nom."</option>";
            }
            else
            {
                $horarios[] = array("id" =>$id, "nom" => $nom );
            }           
        }
        if($tip!=1)
        {
            echo json_encode($horarios);
        }
    }

    //DATOS DE HORARIO SELECCIONADO
    function editHorario($id)
    {
        global $conn;
        $query = $conn->prepare( "SELECT hor_clave_int, hor_nombre,hor_1, hor_2, hor_3, hor_4, hor_5, hor_6, hor_7, hor_8 from tbl_horarios where hor_clave_int = :id limit 1");
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        $query->execute();
        $dat = $query->fetch(PDO::FETCH_ASSOC);
        return $dat;
    }

    function editUsuario($id)
    {
        global $conn;
        $query = $conn->prepare("select u.prf_clave_int per,u.usu_nombre nom,u.usu_apellido ape,u.usu_documento doc, u.usu_usuario usu, u.usu_correo cor, u.usu_clave cla, u.usu_imagen img, u.usu_direccion dir, u.usu_barrio bar,u.usu_celular cel, u.usu_fijo fij, u.usu_contacto con , u.est_clave_int est, c.cen_clave_int cen, u.usu_fec_ingreso ing, u.usu_salario sal, u.hor_clave_int hor, usu_auxilio aux from tbl_usuarios u left outer join tbl_cencos c on c.cen_clave_int = u.cen_clave_int  where u.usu_clave_int = :id limit 1");
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        $query->execute();
        $dat = $query->fetch(PDO::FETCH_ASSOC);
        return $dat;
    }

    function editObra($id)
    {
        global $conn;
    
        $sql = "SELECT 
                    obr_clave_int, 
                    obr_nombre,
                    obr_encargado, 
                    obr_vr_operador,
                    obr_vr_maquina,
                    obr_hr_mes,
                    obr_cencos,
                    obr_lunes, 
                    obr_martes, 
                    obr_miercoles, 
                    obr_jueves, 
                    obr_viernes, 
                    obr_sabado, 
                    obr_domingo, 
                    obr_vr_senalero, 
                    obr_hr_semana, 
                    est_clave_int, 
                    obr_auxilio, 
                    obr_vr_auxilio, 
                    obr_nom_proyecto,
                    obr_fec_inicio,
                    obr_ubicacion, 
                    obr_vr_elevador, 
                    obr_operador, 
                    obr_senalero, 
                    obr_contrato, 
                    obr_festivo 
                FROM tbl_obras 
                WHERE obr_clave_int = :id 
                LIMIT 1";
    
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT); // Asumiendo que el ID es un número
        $stmt->execute();
    
        $dat = $stmt->fetch(PDO::FETCH_ASSOC);
        return $dat;
    }
    
    //REGLAS DE NEGOCIOS
    function fnReglas()
    {
        global $conn;
        $query = $conn->prepare( "SELECT TIME_FORMAT(reg_hi_rn,'%H:%i') hirn, TIME_FORMAT(reg_hf_rn,'%H:%i') hfrn,TIME_FORMAT(reg_ali_nomina,'%H:%i') han,TIME_FORMAT(reg_ali_obra,'%H:%i') hao,TIME_FORMAT(reg_ali_nomina_pm,'%H:%i') hanpm,reg_val_nomina vhan,reg_val_obra vhao,reg_val_nomina_pm vhanpm, reg_hor_mes hmes, reg_hor_semana hsemana, reg_lim_extras limex, reg_lim_extras_semana limexsemana from tbl_reglas LIMIT 1");
        $query->execute();
        $datr = $query->fetch(PDO::FETCH_ASSOC);
        return $datr;
    }

    //TOTAL DE HORAS ENTRE DOS HORAS
    function fnHoras($fecha, $hi, $hf)
    {
        global $conn;    
        $sql = "SELECT calculoHoras3(:fecha, :hi, :hf) AS ho";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':fecha', $fecha);
        $stmt->bindParam(':hi', $hi);
        $stmt->bindParam(':hf', $hf);
        $stmt->execute();    
        $datr = $stmt->fetch(PDO::FETCH_ASSOC);
        return $datr['ho'];
    }

    //CALCULO DE HORAS EXTRAS SEGUN CADA TIPO DE HORA
    function fnExtras($tip, $G5, $F5,$Q5,$R5,$M5, $O5, $L5,$AA5, $I5, $F6, $G6, $P5, $T5, $S5,$Y5, $AC5, $U5, $X5, $Z5, $AD5, $HIRN, $HFRN, $thorario = 8)//HIRN HORA INICIAL REGARGO NOCTURNO
    {
        // $thorario = 8;
        
        $HIRN = ($HIRN=="" || $HIRN==NULL)?0.916666666666667:$HIRN;
        $HFRN = ($HFRN=="" || $HFRN==NULL)?0.25:$HFRN;

        //HORA INICIAL PARA CONTEO PARA LOS CALCULOS DE RECARGO NOCTURNO
        //0,58333333333333333333333333333333 2:00 pm cambiar por 1:pmm
        //0,54166666666666666666666666666667 1:00 pm


        //HORA FINAL PARA CONTEN PARA LOS CALCULOS DE RECARGO NOCTURNO

       // 0,08333333333333333333333333333333 2AM
       // 0,04166666666666666666666666666667 1AM



        /*$G5  =  (int)$G5;
        $F5  =  (int)$F5;
        $Q5  =  (int)$Q5;
        $R5  =  (int)$R5;
        $M5  =  (int)$M5;
        $O5  =  (int)$O5;
        $L5  =  (int)$L5;
        $AA5 =  (int)$AA5;
        $I5  =  (int)$I5;
        $F6  =  (int)$F6;
        $G6  =  (int)$G6;
        $P5  =  (int)$P5;
        //$T5  =  (int)$T5;
        //$S5  =  (int)$S5;
        $Y5  =  (int)$Y5;
        $AC5 =  (int)$AC5;
        //$U5  =  (int)$U5;
        $X5  =  (int)$X5;
        $Z5  =  (int)$Z5;
        $AD5 =  (int)$AD5;*/

        $G5  = str_replace(",",".",$G5);
        $F5  = str_replace(",",".",$F5);
        $Q5  = str_replace(",",".",$Q5);
        $R5  = str_replace(",",".",$R5);
        $M5  = str_replace(",",".",$M5);
        $O5  = str_replace(",",".",$O5);
        $L5  = str_replace(",",".",$L5);
        $AA5 = str_replace(",",".",$AA5);
        $I5  = str_replace(",",".",$I5);
        $F6  = str_replace(",",".",$F6);
        $G6  = str_replace(",",".",$G6);
        $G6  = str_replace(",",".",$G6);
        //$T5  =  (int)$T5;
        //$S5  =  (int)$S5;
        $Y5  = str_replace(",",".",$Y5);
        $AC5 = str_replace(",",".",$AC5);
        //$U5  =  (int)$U5;
        $X5  = str_replace(",",".",$X5);
        $Z5  = str_replace(",",".",$Z5);
        $AD5 = str_replace(",",".",$AD5);
        
        // Lista de parametros
        // fes = G5 || F5
        // totalhoras =  Q5
        // horario = R5
        // hit = M5 =>hora inicial tarde
        // hft = O5 =>hora final tarde
        // horamanana = L5
        // HENF = AA5
        // him = I5 => hora inicial mañana
        // fesa = F6 || G6
        // horastarde = P5
        // pagado y compensado = T5
        // compensado = S5
        // HEDF = Y5
        // HNF = AC5
        // PERMISO REMUNERADO = U5
        // HDF = X5
        // HENO = Z5
        // RD = AD5

        //valores por defaut
        // 0.583333333333333 = 2pm
        // $HIRN = 10pm
        // $HFRN = 6am
        // 0,0833333333333333 = 2am       
        if($tip==1)
        {
            
            $HENF = 0;
            //$HENF = (($G5==1 || $F5==1)?(((($Q5>8 and $O5>$HIRN)?$O5-$HIRN:0)+(($Q5>8 and $O5<$HFRN and $L5==0)?abs($M5-(1+$O5)+(8/24)):0))*24)+(((($Q5>8 and $O5<$HFRN and $O5<>0 and $L5<>0)?$O5+0.04166666666666666666666666666667:0))*24):0)+((($G6==1 || $F6==1) and ($F5<>1 and $G5<>1) and $O5<(6/24))?$O5*24:0)-(($F6<>1 and $G6<>1 and ($F5==1 || $G5==1) and $O5<(6/24))?$O5*24:0);
            $HENF = 
            (
                ($G5==1 || $F5==1)?
                (
                    (
                        (($Q5>$thorario and $O5>$HIRN)?
                        $O5-$HIRN:0)
                        +(
                            ($Q5>$thorario and $O5<$HFRN and $L5==0)? 
                            abs($M5-(1+$O5)+($thorario/24)):0
                        )
                    )*24
                )+
                (((($Q5>8 and $O5<$HFRN and $O5<>0 and $L5<>0)?$O5+0.04166666666666666666666666666667:0))*24):0
            )+
            (
                (($G6==1 || $F6==1) and ($F5<>1 and $G5<>1) and $O5<(6/24))?$O5*24:0
            )- 
            (
                ($F6!=1 and $G6<>1 and ($F5==1 || $G5==1) and $O5<(6/24))?$O5*24:0
            );
            // return round($HENF,2);
            return $HENF;
        }

        if($tip==2)
        {
            $HENO = 0; // HORAS EXTRAS NOCTURNAS ORDINARIAS
            //EXCELL
            //=SI(O(G5=1;F5=1);0;((SI(Y(Q5>R5;O5>0,916666666666667);abs(M5+(R5/24)-(O5));0)+SI(Y(Q5>R5;O5<0,25;L5=0);abs(M5-(1+O5)+(R5/24));0))*24)+((SI(Y(Q5>R5;O5<0,25;O5<>0;L5<>0);O5+0,0833333333333333;0))*24)-AA5+SI(Y(I5<0,25;I5<>0;Q5>R5);SI(((0,25-I5)*24)>(Q5-R5);Q5-R5;(0,25-I5)*24)))+SI(Y(F6<>1;G6<>1;O(F5=1;G5=1);O5<(6/24));O5*24;0)

            // $HENO=(($G5==1 || $F5==1)?0:(((($Q5>$R5 and $O5>$HIRN)?abs($M5+($R5/24)-($O5)):0)+(($Q5>$R5 and $O5<$HFRN and $L5==0)?abs($M5-(1+$O5)+($R5/24)):0))*24)+(((($Q5>$R5 and $O5<$HFRN and $O5<>0 and $L5<>0)?$O5+0.04166666666666666666666666666667:0))*24)-$AA5+(($I5<$HFRN and $I5<>0 and $Q5>$R5)?((($HFRN-$I5)*24)>($Q5-$R5)?$Q5-$R5:($HFRN-$I5)*24):0))+(($F6<>1 and $G6<>1 and ($F5==1 || $G5==1) and $O5<(6/24))?$O5*24:0);
            //return round($HENO,2);

            //+0.04166666666666666666666666666667
            
            $HENO = (($G5==1 || $F5==1)?0:
            
            ((
                //(($Q5>$R5 and $O5>$HIRN)?abs($M5+($R5/24)-($O5)):0)// REVISAR CUANDO ES SABADO Y SE PASA DE LAS 9 DE LA NOCHE NO CALCULA  
                (($Q5>$R5 and $O5>$HIRN)?abs($O5-$HIRN):0) // CALCULO ES LA HORA FINAL TARDE- HORA INICIA RECARGO NOCTURNO
               +
               (($Q5>$R5 and $O5<=$HFRN and $O5<=$HIRN and $L5<>0)? (24-($HIRN*24))/24:0)//   abs($M5+($R5/24)-($O5)):0)  //calculo cuando hora final en despues de las 12 am y tuvo jornada en la mañaña MODIFICACION A LA FORMULA HENO

               +(($Q5>$R5 and $O5<=$HFRN and $L5==0)?abs($M5-(1+$O5)+($R5/24)):0)// CALCULO DE HORAS SI INICIO JORNADA DESPUES DEL MEDIO DIA  ORDINARIO y NO TUVO JORNADA EN LA MAÑANA L5
            )*24)
            
            +(((($Q5>$R5 and $O5<=$HFRN and $O5<>0 and $L5<>0)?$O5:0))*24)// CALCULO HORAS DESPUES DE LAS 12PM PERO SEGUN LA HORA FINAL DE LA TARDE Y QUE HAYA LABORADO EN LA MAÑANA DIA ORDINARIO
            -$AA5
            
            +(($I5<$HFRN and $I5<>0 and $Q5>$R5)?((($HFRN-$I5)*24)>($Q5-$R5)?$Q5-$R5:($HFRN-$I5)*24):0) //CALCULO HORAS ANTES DE LAS 6 AM SEGUN LA JORNADA DE LA MAÑANA EN UN DIA ORDINARIO
            
            )
            +(($F6<>1 and $G6<>1 and ($F5==1 || $G5==1) and $O5<(6/24))?$O5*24:0)//HORAS NOCTURNA CUANDO ES FESTIVO Y EL DIA SIGUIENTE NO Y HORA FINAL MENOR 6 AM
            ;
            //return round($HENO,2);

            //$HENO =  (($G5==1 || $F5==1)?0:(((($Q5>$R5 and $O5>$HIRN)?abs($M5+($R5/24)-($O5)):0)+(($Q5>$R5 and $O5<$HFRN and  $L5==0)?abs($M5-(1+$O5)+($R5/24)):0))*24)+(((($Q5>$R5 and $O5<$HFRN and $O5<>0 and $L5<>0)?$O5:0))*24)-$AA5+($I5<$HFRN and $I5<>0 and $Q5>$R5?((($HFRN-$I5)*24)>($Q5-$R5)?$Q5-$R5:0):($HFRN-$I5)*24)) +(($F6<>1 and $G6<>1 and ($F5==1 || $G5==1) and $O5<(6/24))?$O5*24:0);
          
            //$HENO = 0.5;// $HIRN;
            return $HENO;
        }

       


        if($tip==3)
        {
            $HEDF = 0; 
            
            //EXCELL
            //=SI(Y(O(G5=1;F5=1);Q5>8);Q5-8;0)-AA5+SI(Y(O(G6=1;F6=1);Y(G5<>1;F5<>1);AA5>0);O5*24;0)-SI(Y(F6<>1;G6<>1;O(F5=1;G5=1);O5<(6/24));O5*24;0)
            //$HEDF = ((($G5==1|| $F5==1) and $Q5>8)?$Q5-8:0)-$AA5+((($G6==1 ||$F6==1) and ($G5<>1 and $F5<>1) and $AA5>0)?$O5*24:0)-(($F6<>1 and $G6<>1 and ($F5==1 || $G5==1) and $O5<(6/24))?$O5*24:0);

            //$HEDF =((($G5==1 || $F5==1) and $Q5>$thorario)? $Q5-$thorario:0)-
            //$AA5+((($G6==1 || $F6==1) and ($G5<>1 and $F5<>1) and $AA5>0)?$O5*24:0)-
            //(($F6<>1 and $G6<>1 and ($F5==1 || $G5==1) and $O5<(6/24))?$O5*24:0);
            // return round($HEDF,2);

            $HEDF =((($G5==1 || $F5==1) and $Q5>$thorario)?$Q5-$thorario:0)-
            $AA5+((($G6==1||$F6==1) and ($G5<>1 and $F5<>1) and $AA5>0)?$O5*24:0)-
            (($F6<>1 and $G6<>1 and ($F5==1 || $G5==1) and $O5<(6/24))?$O5*24:0)
            //-$AC5
            ;        
            return $HEDF;
        }

        if($tip==4)
        {
            $HNF  = 0;
            //Excel
            //=((SI(Y(O(F5=1;G5=1);L5=0;M5>0,583333333333333;O5>0,916666666666667);M5+(8/24)-(22/24);0)*24))+((SI(Y(O(F5=1;G5=1);L5=0;M5>0,583333333333333;O5<0,25);M5+((P5/24)-1)+(0,0833333333333);0))*24)-SI(Y(O(F5=1;G5=1);P5>8;L5=0;M5>0,583333333333333;O5<0,25);(P5-8);0)+SI(Y(O(F5=1;G5=1);I5<0,25;I5<>0);(0,25-I5)*24;0)
            //$HNF = ((((($F5==1 || $G5==1) and $L5=0 and $M5>0.54166666666666666666666666666667 and $O5>$HIRN)?$M5+(8/24)-(22/24):0)*24))+((((($F5==1 ||$G5==1)and $L5==0 and $M5>0.54166666666666666666666666666667 and $O5<$HFRN)?$M5+(($P5/24)-1)+(0.04166666666666666666666666666667):0))*24)-((($F5==1 || $G5==1) and $P5>8 and $L5==0 and $M5>0.54166666666666666666666666666667 and $O5<$HFRN)?($P5-8):0)+((($F5==1 || $G5==1) and $I5<$HFRN and $I5<>0)?($HFRN-$I5)*24:0);
            $HNF = ((((($F5==1 || $G5==1) and $L5=0 and $M5>0.54166666666666666666666666666667 and $O5>$HIRN)?$M5+($thorario/24)-($HIRN):0)*24))+((((($F5==1 ||$G5==1)and $L5==0 and $M5>0.54166666666666666666666666666667 and $O5<$HFRN)?$M5+(($P5/24)-1)+(0.04166666666666666666666666666667):0))*24)-((($F5==1 || $G5==1) and $P5>$thorario and $L5==0 and $M5>0.54166666666666666666666666666667 and $O5<$HFRN)?($P5-$thorario):0)+((($F5==1 || $G5==1) and $I5<$HFRN and $I5<>0)?($HFRN-$I5)*24:0);
            // return round($HNF,2);
            return $HNF;
        }

       
        if($tip==5)
        {
            $RD = 0;
            //EXCELL
            //=SI(T5="SI";0;SI(Y(O(G5=1;F5=1);O(S5="SI";S5="Ambos"));SI(Q5<=8;Q5;8);0))
            $RD = ($T5=="SI"?0:((($G5==1 || $F5==1) and ($S5=="SI" || $S5=="Ambos"))?($Q5<=$thorario?$Q5:$thorario):0));
            // return round($RD,2);
            return $RD;
        }

        if($tip==6)
        {
            $RN = 0;
            //EXCELL
            //=SI(O(G5=1;F5=1);0;((SI(Y(L5=0;M5>=0,583333333333333;O5>0,916666666666667;Q5<=R5);M5+(R5/24)-(22/24);0)))*24+((SI(Y(L5=0;M5>0,583333333333333;O5<0,25);M5+((P5/24)-1)+(0,0833333333333);0))*24)-SI(Y(P5>8;L5=0;M5>0,583333333333333;O5<0,25);(P5-R5);0)+SI(Y(I5<0,25;I5<>0;Q5<=R5);(0,25-I5);0)*24)+SI(Y(I5<(6/24);Q5>R5;(0,25-I5)>(Q5/24-R5/24);I5<>0);(0,25-I5)-(Q5/24-R5/24);0)*24
            //$RN = (($G5==1 || $F5==1)?0:(((($L5==0 and $M5>=0.54166666666666666666666666666667 and $O5>$HIRN and $Q5<=$R5)?$M5+($R5/24)-(22/24):0)))*24 +(((($L5==0 and $M5>0.54166666666666666666666666666667 and $O5<$HFRN)?$M5+(($P5/24)-1)+(0.04166666666666666666666666666667):0))*24)-(($P5>8 and $L5==0 and $M5>0.54166666666666666666666666666667 and $O5<$HFRN)?($P5-$R5):0)+(($I5<$HFRN and $I5<>0 and $Q5<=$R5)?($HFRN-$I5):0)*24)+(($I5<(6/24) and $Q5>$R5 and ($HFRN-$I5)>($Q5/24-$R5/24) and $I5<>0)?($HFRN-$I5)-($Q5/24-$R5/24):0)*24;
            $RN = (
                ($G5==1 || $F5==1)?0:
                (((($L5==0 and $M5>=0.54166666666666666666666666666667 and $O5>$HIRN and $Q5<=$R5)?$M5+($R5/24)-($HIRN):0)))*24 + // SI NO GENERO HORARIO EN LA MAÑANA Y HORA INICIAL TARDE MAYOR A LA 1PM AND HORA FINAL TARDE MAYOR A HORA INICIAL RECARGO Y TOTAL HORAS <= HORARIO ENTONCS IGUAL HORAFINAL TARDE MAS HORARIO /24 MENOS HIRN POR 24
                (((($L5==0 and $M5>0.54166666666666666666666666666667 and $O5<$HFRN)?$M5+(($P5/24)-1)+(0.04166666666666666666666666666667):0))*24)- // SI NO LABORO EN LA MAÑANA AND HORA INICIAL TARDE MAYOR A LA  1PM AND HORA FINAL TARDE MENOR A LA HORA FINAL RECARGO NOCTURNO(TERMINO EN LA MAÑA DEL SIGUIENTE DIA) ENTONCS = HORA INICIAL TARDE + TOTAL HORAS TARDE -1 + 1am  * 24
                (($P5>8 and $L5==0 and $M5>0.54166666666666666666666666666667 and $O5<$HFRN)?($P5-$R5):0)+ // SI TOTAL HORAS TARDE MAYOR A 8 AND NO LABORO EN LA MAÑA Y HORA INICIAL TARDE MAYOR 1PM Y HORA FINAL TARDES MENOR A HORA FINAL RECARGO(TERMINO AM) ENTONS TOTAL HORAR TARDE MENOS EL HORARIO SINO CERO
                (($I5<$HFRN and $I5<>0 and $Q5<=$R5)?($HFRN-$I5):0)*24 // SI HORA INICIAL AM ES MENOR HORA HFRN(INICIO ANTES DE LAS 6 AM AND HORA INI MAYOR DIF A CERO AND TOTAL HORAS ES MENOR AL HORARIO) A LAS 6:AM RESTELE HORA INICIAL
            )+
            (
                ($I5<(6/24) and $Q5>$R5 and ($HFRN-$I5)>($Q5/24-$R5/24) and $I5<>0)?
                ($HFRN-$I5)-($Q5/24-$R5/24):0 // SI HORA INIICAL AM < HFRN Y TOTAL HORAS MAYOR A HORARIO Y HORAFINAL RECARGO MENOS HORA INICIAL  ES MAYOR A TOTALHORAS/24  - HORARIO/24 AND INICIO EN LA MAÑANA ENTONCS ES IGUAL (HFRN MENOS HORA INICIAL AM) MENOS (TOTAL HORAS/24 MENOS HORARIO/24) SINO 0
            )*24;
            // return round($RN,2);
            return $RN;
        }

        if($tip==7)
        {
            $HDF  = 0;
            //EXCELL
            //=SI(O(G5=1;F5=1);Q5;0)-Y5-AA5+SI(Y(O(G6=1;F6=1);AA5>0;Y(G5<>1;F5<>1));O5*24;0)-SI(Y(O(G5=1;F5=1);S5="SI";T5="");SI(Q5<=8;Q5;"8");0)-SI(Y(F6<>1;G6<>1;O(F5=1;G5=1);O5<(6/24));O5*24;0)-AC5
            
            //$HDF = (($G5==1 || $F5==1)?$Q5:0)-$Y5-$AA5+((($G6==1 || $F6==1) and $AA5>0 and ($G5<>1 and $F5<>1))?$O5*24:0)-((($G5==1 || $F5==1) and $S5=="SI" and  $T5=="")?($Q5<=8?$Q5:8):0)-(($F6<>1 and $G6<>1 and ($F5==1 || $G5==1) and $O5<(6/24))?$O5*24:0)-$AC5;

            /*$HDF =(($G5==1 || $F5== 1)?$Q5:0)-$Y5-$AA5+
                ((($G6==1 || $F6==1) and $AA5>0 and ($G5<>1 and $F5<>1))? $O5*24:0)-
                ((($G5==1 || $F5==1) and $S5=="SI" and $T5=="")?($Q5<=$thorario?$Q5:$thorario):0)-
                (($F6<>1 and $G6<>1 and ($F5==1 || $G5==1) and $O5<(6/24))?$O5*24:0)-$AC5;*/

            $HDF =(($G5==1 || $F5==1)?$Q5:0)-$Y5-$AA5+
            ((($G6==1 || $F6==1) and $AA5>0 and ($G5<>1 and $F5<>1))?$O5*24:0)-//
            ((($G5==1 || $F5==1) and $S5=="SI" and $T5=="")? ($Q5<=$thorario?$Q5:$thorario):0)-
            (($F6<>1 and $G6<>1 and ($F5==1 || $G5==1) and $O5<($HFRN))?$O5*24:0) - ($S5=="SI" ? 0:$AC5);


            // return round($HDF,2);
            return $HDF;
        }

        if($tip==8)
        {
            $HEDO = 0; // HORAS EXTRAS DIURNAS ORDINA
            //EXCELL
            //=SI(R5>Q5;SI(U5="SI";0;Q5-R5);Q5-R5)-X5-Z5-AA5-Y5-AC5-AD5
            $HEDO = ($R5>$Q5?($U5=='SI'?0:$Q5-$R5):$Q5-$R5)-$X5-$Z5-$AA5-$Y5-$AC5-$AD5;
            //$HEDO = $HEDO - $X5-$Z5-$AA5-$Y5-$AC5-$AD5;
            //$HEDO = "($R5>$Q5?($U5=='SI'?0:$Q5-$R5):$Q5-$R5)-$X5-$Z5-$AA5-$Y5-$AC5-$AD5";
            //return $HEDO;
            // return round($HEDO,2);
            return $HEDO;
        }
    }

    function fnExtrasText($tip, $G5, $F5,$Q5,$R5,$M5, $O5, $L5,$AA5, $I5, $F6, $G6, $P5, $T5, $S5,$Y5, $AC5, $U5, $X5, $Z5, $AD5, $HIRN, $HFRN, $thorario = 8)//HIRN HORA INICIAL REGARGO NOCTURNO
    {
        //$thorario = 8;
        $HIRN = ($HIRN=="" || $HIRN==NULL)?0.916666666666667:$HIRN;
        $HFRN = ($HFRN=="" || $HFRN==NULL)?0.25:$HFRN;

        //HORA INICIAL PARA CONTEO PARA LOS CALCULOS DE RECARGO NOCTURNO
        //0,58333333333333333333333333333333 2:00 pm cambiar por 1:pmm
        //0,54166666666666666666666666666667 1:00 pm


        //HORA FINAL PARA CONTEN PARA LOS CALCULOS DE RECARGO NOCTURNO

       // 0,08333333333333333333333333333333 2AM
       // 0,04166666666666666666666666666667 1AM



        /*$G5  =  (int)$G5;
        $F5  =  (int)$F5;
        $Q5  =  (int)$Q5;
        $R5  =  (int)$R5;
        $M5  =  (int)$M5;
        $O5  =  (int)$O5;
        $L5  =  (int)$L5;
        $AA5 =  (int)$AA5;
        $I5  =  (int)$I5;
        $F6  =  (int)$F6;
        $G6  =  (int)$G6;
        $P5  =  (int)$P5;
        //$T5  =  (int)$T5;
        //$S5  =  (int)$S5;
        $Y5  =  (int)$Y5;
        $AC5 =  (int)$AC5;
        //$U5  =  (int)$U5;
        $X5  =  (int)$X5;
        $Z5  =  (int)$Z5;
        $AD5 =  (int)$AD5;*/

        $G5  = str_replace(",",".",$G5);
        $F5  = str_replace(",",".",$F5);
        $Q5  = str_replace(",",".",$Q5);
        $R5  = str_replace(",",".",$R5);
        $M5  = str_replace(",",".",$M5);
        $O5  = str_replace(",",".",$O5);
        $L5  = str_replace(",",".",$L5);
        $AA5 = str_replace(",",".",$AA5);
        $I5  = str_replace(",",".",$I5);
        $F6  = str_replace(",",".",$F6);
        $G6  = str_replace(",",".",$G6);
        $G6  = str_replace(",",".",$G6);
        //$T5  =  (int)$T5;
        //$S5  =  (int)$S5;
        $Y5  = str_replace(",",".",$Y5);
        $AC5 = str_replace(",",".",$AC5);
        //$U5  =  (int)$U5;
        $X5  = str_replace(",",".",$X5);
        $Z5  = str_replace(",",".",$Z5);
        $AD5 = str_replace(",",".",$AD5);

        $thorario = str_replace(",",".",$thorario);
        
        // Lista de parametros
        // fes = G5 || F5
        // totalhoras =  Q5
        // horario = R5
        // hit = M5 =>hora inicial tarde
        // hft = O5 =>hora final tarde
        // horamanana = L5
        // HENF = AA5
        // him = I5 => hora inicial mañana
        // fesa = F6 || G6
        // horastarde = P5
        // pagado y compensado = T5
        // compensado = S5
        // HEDF = Y5
        // HNF = AC5
        // PERMISO REMUNERADO = U5
        // HDF = X5
        // HENO = Z5
        // RD = AD5

        //valores por defaut
        // 0.583333333333333 = 2pm
        // $HIRN = 10pm
        // $HFRN = 6am
        // 0,0833333333333333 = 2am

        if($tip==1)
        {
            $HENF = 0;
            //$HENF = (($G5==1 || $F5==1)?(((($Q5>8 and $O5>$HIRN)?$O5-$HIRN:0)+(($Q5>8 and $O5<$HFRN and $L5==0)?abs($M5-(1+$O5)+(8/24)):0))*24)+(((($Q5>8 and $O5<$HFRN and $O5<>0 and $L5<>0)?$O5+0.04166666666666666666666666666667:0))*24):0)+((($G6==1 || $F6==1) and ($F5<>1 and $G5<>1) and $O5<(6/24))?$O5*24:0)-(($F6<>1 and $G6<>1 and ($F5==1 || $G5==1) and $O5<(6/24))?$O5*24:0);
            $HENF = "(($G5==1 || $F5==1)?(((($Q5>$thorario and $O5>$HIRN)?$O5-$HIRN:0)+(($Q5>$thorario and $O5<$HFRN and $L5==0)? abs($M5-(1+$O5)+($thorario/24)):0))*24)+
            (((($Q5>8 and $O5<$HFRN and $O5<>0 and $L5<>0)?$O5+0.04166666666666666666666666666667:0))*24):0)+
            ((($G6==1 || $F6==1) and ($F5<>1 and $G5<>1) and $O5<(6/24))?$O5*24:0)-
            (($F6<>1 and $G6<>1 and ($F5==1 || $G5==1) and $O5<(6/24))?$O5*24:0);
            // return round($HENF,2)";
            return $HENF;
        }

        if($tip==2)
        {
            $HENO = 0; // HORAS EXTRAS NOCTURNAS ORDINARIAS
            //EXCELL
            //=SI(O(G5=1;F5=1);0;((SI(Y(Q5>R5;O5>0,916666666666667);abs(M5+(R5/24)-(O5));0)+SI(Y(Q5>R5;O5<0,25;L5=0);abs(M5-(1+O5)+(R5/24));0))*24)+((SI(Y(Q5>R5;O5<0,25;O5<>0;L5<>0);O5+0,0833333333333333;0))*24)-AA5+SI(Y(I5<0,25;I5<>0;Q5>R5);SI(((0,25-I5)*24)>(Q5-R5);Q5-R5;(0,25-I5)*24)))+SI(Y(F6<>1;G6<>1;O(F5=1;G5=1);O5<(6/24));O5*24;0)
            $HENO="(($G5==1 || $F5==1)?0:(((($Q5>$R5 and $O5>$HIRN)?abs($M5+($R5/24)-($O5)):0)+(($Q5>$R5 and $O5<$HFRN and $L5==0)?abs($M5-(1+$O5)+($R5/24)):0))*24)+(((($Q5>$R5 and $O5<$HFRN and $O5<>0 and $L5<>0)?$O5+0.04166666666666666666666666666667:0))*24)-$AA5+(($I5<$HFRN and $I5<>0 and $Q5>$R5)?((($HFRN-$I5)*24)>($Q5-$R5)?$Q5-$R5:($HFRN-$I5)*24):0))+(($F6<>1 and $G6<>1 and ($F5==1 || $G5==1) and $O5<(6/24))?$O5*24:0);
            //return round($HENO,2)";
            return $HENO;
        }

        if($tip==3)
        {
            $HEDF = 0; 
            
            //EXCELL
            //=SI(Y(O(G5=1;F5=1);Q5>8);Q5-8;0)-AA5+SI(Y(O(G6=1;F6=1);Y(G5<>1;F5<>1);AA5>0);O5*24;0)-SI(Y(F6<>1;G6<>1;O(F5=1;G5=1);O5<(6/24));O5*24;0)
            //$HEDF = ((($G5==1|| $F5==1) and $Q5>8)?$Q5-8:0)-$AA5+((($G6==1 ||$F6==1) and ($G5<>1 and $F5<>1) and $AA5>0)?$O5*24:0)-(($F6<>1 and $G6<>1 and ($F5==1 || $G5==1) and $O5<(6/24))?$O5*24:0);

            //$HEDF =((($G5==1 || $F5==1) and $Q5>$thorario)? $Q5-$thorario:0)-
            //$AA5+((($G6==1 || $F6==1) and ($G5<>1 and $F5<>1) and $AA5>0)?$O5*24:0)-
            //(($F6<>1 and $G6<>1 and ($F5==1 || $G5==1) and $O5<(6/24))?$O5*24:0);
            // return round($HEDF,2);

            $HEDF ="((($G5==1 || $F5==1) and $Q5>$thorario)?$Q5-$thorario:0)-
            $AA5+((($G6==1||$F6==1) and ($G5<>1 and $F5<>1) and $AA5>0)?$O5*24:0)-
            (($F6<>1 and $G6<>1 and ($F5==1 || $G5==1) and $O5<(6/24))?$O5*24:0)";
        
            return $HEDF;
        }

        if($tip==4)
        {
            $HNF  = 0;
            //Excel
            //=((SI(Y(O(F5=1;G5=1);L5=0;M5>0,583333333333333;O5>0,916666666666667);M5+(8/24)-(22/24);0)*24))+((SI(Y(O(F5=1;G5=1);L5=0;M5>0,583333333333333;O5<0,25);M5+((P5/24)-1)+(0,0833333333333);0))*24)-SI(Y(O(F5=1;G5=1);P5>8;L5=0;M5>0,583333333333333;O5<0,25);(P5-8);0)+SI(Y(O(F5=1;G5=1);I5<0,25;I5<>0);(0,25-I5)*24;0)
            //$HNF = ((((($F5==1 || $G5==1) and $L5=0 and $M5>0.54166666666666666666666666666667 and $O5>$HIRN)?$M5+(8/24)-(22/24):0)*24))+((((($F5==1 ||$G5==1)and $L5==0 and $M5>0.54166666666666666666666666666667 and $O5<$HFRN)?$M5+(($P5/24)-1)+(0.04166666666666666666666666666667):0))*24)-((($F5==1 || $G5==1) and $P5>8 and $L5==0 and $M5>0.54166666666666666666666666666667 and $O5<$HFRN)?($P5-8):0)+((($F5==1 || $G5==1) and $I5<$HFRN and $I5<>0)?($HFRN-$I5)*24:0);
            $HNF = "((((($F5==1 || $G5==1) and $L5=0 and $M5>0.54166666666666666666666666666667 and $O5>$HIRN)?$M5+($thorario/24)-(($HIRN*24)/24):0)*24))+((((($F5==1 ||$G5==1)and $L5==0 and $M5>0.54166666666666666666666666666667 and $O5<$HFRN)?$M5+(($P5/24)-1)+(0.04166666666666666666666666666667):0))*24)-((($F5==1 || $G5==1) and $P5>$thorario and $L5==0 and $M5>0.54166666666666666666666666666667 and $O5<$HFRN)?($P5-$thorario):0)+((($F5==1 || $G5==1) and $I5<$HFRN and $I5<>0)?($HFRN-$I5)*24:0)";
            // return round($HNF,2);
            return $HNF;
        }

        if($tip==5)
        {
            $RD = 0;
            //EXCELL
            //=SI(T5="SI";0;SI(Y(O(G5=1;F5=1);O(S5="SI";S5="Ambos"));SI(Q5<=8;Q5;8);0))
            $RD = "($T5=='SI'?0:((($G5==1 || $F5==1) and ($S5=='SI' || $S5=='Ambos'))?($Q5<=$thorario?$Q5:$thorario):0))";
            // return round($RD,2);
            return $RD;
        }

        if($tip==6)
        {
            $RN = 0;
            //EXCELL
            //=SI(O(G5=1;F5=1);0;((SI(Y(L5=0;M5>=0,583333333333333;O5>0,916666666666667;Q5<=R5);M5+(R5/24)-(22/24);0)))*24+((SI(Y(L5=0;M5>0,583333333333333;O5<0,25);M5+((P5/24)-1)+(0,0833333333333);0))*24)-SI(Y(P5>8;L5=0;M5>0,583333333333333;O5<0,25);(P5-R5);0)+SI(Y(I5<0,25;I5<>0;Q5<=R5);(0,25-I5);0)*24)+SI(Y(I5<(6/24);Q5>R5;(0,25-I5)>(Q5/24-R5/24);I5<>0);(0,25-I5)-(Q5/24-R5/24);0)*24
            //$RN = (($G5==1 || $F5==1)?0:(((($L5==0 and $M5>=0.54166666666666666666666666666667 and $O5>$HIRN and $Q5<=$R5)?$M5+($R5/24)-(22/24):0)))*24 +(((($L5==0 and $M5>0.54166666666666666666666666666667 and $O5<$HFRN)?$M5+(($P5/24)-1)+(0.04166666666666666666666666666667):0))*24)-(($P5>8 and $L5==0 and $M5>0.54166666666666666666666666666667 and $O5<$HFRN)?($P5-$R5):0)+(($I5<$HFRN and $I5<>0 and $Q5<=$R5)?($HFRN-$I5):0)*24)+(($I5<(6/24) and $Q5>$R5 and ($HFRN-$I5)>($Q5/24-$R5/24) and $I5<>0)?($HFRN-$I5)-($Q5/24-$R5/24):0)*24;
            $RN = "(($G5==1 || $F5==1)?0:(((($L5==0 and $M5>=0.54166666666666666666666666666667 and $O5>$HIRN and $Q5<=$R5)?$M5+($R5/24)-(($HIRN*24)/24):0)))*24 +(((($L5==0 and $M5>0.54166666666666666666666666666667 and $O5<$HFRN)?$M5+(($P5/24)-1)+(0.04166666666666666666666666666667):0))*24)-(($P5>8 and $L5==0 and $M5>0.54166666666666666666666666666667 and $O5<$HFRN)?($P5-$R5):0)+(($I5<$HFRN and $I5<>0 and $Q5<=$R5)?($HFRN-$I5):0)*24)+(($I5<(6/24) and $Q5>$R5 and ($HFRN-$I5)>($Q5/24-$R5/24) and $I5<>0)?($HFRN-$I5)-($Q5/24-$R5/24):0)*24";
            // return round($RN,2);
            return $RN;
        }

        if($tip==7)
        {
            $HDF  = 0;
            //EXCELL
            //=SI(O(G5=1;F5=1);Q5;0)-Y5-AA5+SI(Y(O(G6=1;F6=1);AA5>0;Y(G5<>1;F5<>1));O5*24;0)-SI(Y(O(G5=1;F5=1);S5="SI";T5="");SI(Q5<=8;Q5;"8");0)-SI(Y(F6<>1;G6<>1;O(F5=1;G5=1);O5<(6/24));O5*24;0)-AC5
            
            //$HDF = (($G5==1 || $F5==1)?$Q5:0)-$Y5-$AA5+((($G6==1 || $F6==1) and $AA5>0 and ($G5<>1 and $F5<>1))?$O5*24:0)-((($G5==1 || $F5==1) and $S5=="SI" and  $T5=="")?($Q5<=8?$Q5:8):0)-(($F6<>1 and $G6<>1 and ($F5==1 || $G5==1) and $O5<(6/24))?$O5*24:0)-$AC5;

            $HDF ="(($G5==1 || $F5== 1)?$Q5:0)-
                $Y5-$AA5+
                ((($G6==1 || $F6==1) and $AA5>0 and ($G5<>1 and $F5<>1))? $O5*24:0)-
                ((($G5==1 || $F5==1) and $S5=='SI' and $T5=='')?($Q5<=$thorario?$Q5:$thorario):0)-
                (($F6<>1 and $G6<>1 and ($F5==1 || $G5==1) and $O5<(6/24))?$O5*24:0)-$AC5";
            // return round($HDF,2);
            return $HDF;
        }

        if($tip==8)
        {
            $HEDO = 0; // HORAS EXTRAS DIURNAS ORDINA
            //EXCELL
            //=SI(R5>Q5;SI(U5="SI";0;Q5-R5);Q5-R5)-X5-Z5-AA5-Y5-AC5-AD5
            $HEDO = "($R5>$Q5?($U5=='SI'?0:$Q5-$R5):$Q5-$R5)-$X5-$Z5-$AA5-$Y5-$AC5-$AD5";
            //$HEDO = $HEDO - $X5-$Z5-$AA5-$Y5-$AC5-$AD5;
            //$HEDO = "($R5>$Q5?($U5=='SI'?0:$Q5-$R5):$Q5-$R5)-$X5-$Z5-$AA5-$Y5-$AC5-$AD5";
            //return $HEDO;
            // return round($HEDO,2);
            return $HEDO;
        }
    }

    /*function fnExtrasOld($tip, $G5, $F5,$Q5,$R5,$M5, $O5, $L5,$AA5, $I5, $F6, $G6, $P5, $T5, $S5,$Y5, $AC5, $U5, $X5, $Z5, $AD5, $HIRN, $HFRN, $thorario = 8)//HIRN HORA INICIAL REGARGO NOCTURNO
    {

        $HIRN = ($HIRN=="" || $HIRN==NULL)?0.916666666666667:$HIRN;
        $HFRN = ($HFRN=="" || $HFRN==NULL)?0.25:$HFRN;

        //HORA INICIAL PARA CONTEO PARA LOS CALCULOS DE RECARGO NOCTURNO
        //0,58333333333333333333333333333333 2:00 pm cambiar por 1:pmm
        //0,54166666666666666666666666666667 1:00 pm


        //HORA FINAL PARA CONTEN PARA LOS CALCULOS DE RECARGO NOCTURNO

       // 0,08333333333333333333333333333333 2AM
       // 0,04166666666666666666666666666667 1AM



        // $G5  =  (int)$G5;
        // $F5  =  (int)$F5;
        // $Q5  =  (int)$Q5;
        // $R5  =  (int)$R5;
        // $M5  =  (int)$M5;
        // $O5  =  (int)$O5;
        // $L5  =  (int)$L5;
        // $AA5 =  (int)$AA5;
        // $I5  =  (int)$I5;
        // $F6  =  (int)$F6;
        // $G6  =  (int)$G6;
        // $P5  =  (int)$P5;
        // //$T5  =  (int)$T5;
        // //$S5  =  (int)$S5;
        // $Y5  =  (int)$Y5;
        // $AC5 =  (int)$AC5;
        // //$U5  =  (int)$U5;
        // $X5  =  (int)$X5;
        // $Z5  =  (int)$Z5;
        // $AD5 =  (int)$AD5;

        $G5  = str_replace(",",".",$G5);
        $F5  = str_replace(",",".",$F5);
        $Q5  = str_replace(",",".",$Q5);
        $R5  = str_replace(",",".",$R5);
        $M5  = str_replace(",",".",$M5);
        $O5  = str_replace(",",".",$O5);
        $L5  = str_replace(",",".",$L5);
        $AA5 = str_replace(",",".",$AA5);
        $I5  = str_replace(",",".",$I5);
        $F6  = str_replace(",",".",$F6);
        $G6  = str_replace(",",".",$G6);
        $G6  = str_replace(",",".",$G6);
        //$T5  =  (int)$T5;
        //$S5  =  (int)$S5;
        $Y5  = str_replace(",",".",$Y5);
        $AC5 = str_replace(",",".",$AC5);
        //$U5  =  (int)$U5;
        $X5  = str_replace(",",".",$X5);
        $Z5  = str_replace(",",".",$Z5);
        $AD5 = str_replace(",",".",$AD5);
        
        // Lista de parametros
        // fes = G5 || F5
        // totalhoras =  Q5
        // horario = R5
        // hit = M5 =>hora inicial tarde
        // hft = O5 =>hora final tarde
        // horamanana = L5
        // HENF = AA5
        // him = I5 => hora inicial mañana
        // fesa = F6 || G6
        // horastarde = P5
        // pagado y compensado = T5
        // compensado = S5
        // HEDF = Y5
        // HNF = AC5
        // PERMISO REMUNERADO = U5
        // HDF = X5
        // HENO = Z5
        // RD = AD5

        //valores por defaut
        // 0.583333333333333 = 2pm
        // $HIRN = 10pm
        // $HFRN = 6am
        // 0,0833333333333333 = 2am

        if($tip==1)
        {
            $HENF = 0;
            //$HENF = (($G5==1 || $F5==1)?(((($Q5>8 and $O5>$HIRN)?$O5-$HIRN:0)+(($Q5>8 and $O5<$HFRN and $L5==0)?abs($M5-(1+$O5)+(8/24)):0))*24)+(((($Q5>8 and $O5<$HFRN and $O5<>0 and $L5<>0)?$O5+0.04166666666666666666666666666667:0))*24):0)+((($G6==1 || $F6==1) and ($F5<>1 and $G5<>1) and $O5<(6/24))?$O5*24:0)-(($F6<>1 and $G6<>1 and ($F5==1 || $G5==1) and $O5<(6/24))?$O5*24:0);
            $HENF = (($G5==1 || $F5==1)?(((($Q5>8 and $O5>$HIRN)?$O5-$HIRN:0)+(($Q5>8 and $O5<$HFRN and $L5==0)? abs($M5-(1+$O5)+(8/24)):0))*24)+
            (((($Q5>8 and $O5<$HFRN and $O5<>0 and $L5<>0)?$O5+0.04166666666666666666666666666667:0))*24):0)+
            ((($G6==1 || $F6==1) and ($F5<>1 and $G5<>1) and $O5<(6/24))?$O5*24:0)-
            (($F6<>1 and $G6<>1 and ($F5==1 || $G5==1) and $O5<(6/24))?$O5*24:0);
            // return round($HENF,2);
            return $HENF;
        }

        if($tip==2)
        {
            $HENO = 0; // HORAS EXTRAS NOCTURNAS ORDINARIAS
            //EXCELL
            //=SI(O(G5=1;F5=1);0;((SI(Y(Q5>R5;O5>0,916666666666667);abs(M5+(R5/24)-(O5));0)+SI(Y(Q5>R5;O5<0,25;L5=0);abs(M5-(1+O5)+(R5/24));0))*24)+((SI(Y(Q5>R5;O5<0,25;O5<>0;L5<>0);O5+0,0833333333333333;0))*24)-AA5+SI(Y(I5<0,25;I5<>0;Q5>R5);SI(((0,25-I5)*24)>(Q5-R5);Q5-R5;(0,25-I5)*24)))+SI(Y(F6<>1;G6<>1;O(F5=1;G5=1);O5<(6/24));O5*24;0)
            //$HENO=(($G5==1 || $F5==1)?0:(((($Q5>$R5 and $O5>$HIRN)?abs($M5+($R5/24)-($O5)):0)+(($Q5>$R5 and $O5<$HFRN and $L5==0)?abs($M5-(1+$O5)+($R5/24)):0))*24)+(((($Q5>$R5 and $O5<$HFRN and $O5<>0 and $L5<>0)?$O5+0.04166666666666666666666666666667:0))*24)-$AA5+(($I5<$HFRN and $I5<>0 and $Q5>$R5)?((($HFRN-$I5)*24)>($Q5-$R5)?$Q5-$R5:($HFRN-$I5)*24):0))+(($F6<>1 and $G6<>1 and ($F5==1 || $G5==1) and $O5<(6/24))?$O5*24:0);
            //return round($HENO,2);

            $HENO =  (($G5==1 || $F5==1)?0:(((($Q5>$R5 and $O5>0.916666666666667)?abs($M5+($R5/24)-($O5)):0)+(($Q5>$R5 and $O5<0.25 and  $L5==0)?abs($M5-(1+$O5)+($R5/24)):0))*24)+(((($Q5>$R5 and $O5<0.25 and $O5<>0 and $L5<>0)?$O5+0.0833333333333333:0))*24)-$AA5+($I5<0.25 and $I5<>0 and $Q5>$R5?(((0.25-$I5)*24)>($Q5-$R5)?$Q5-$R5:0):(0.25-$I5)*24)) +(($F6<>1 and $G6<>1 and ($F5==1 || $G5==1) and $O5<(6/24))?$O5*24:0);

            // =SI(O($G5==1 || $F5==1)?0:((SI(Y($Q5>$R5 and $O5>0.916666666666667)?ABS($M5+($R5/24)-($O5)):0)+SI(Y($Q5>$R5 and $O5<0.25 and  $L5==0)?ABS($M5-(1+O5)+($R5/24)):0))*24)+((SI(Y($Q5>$R5 and $O5<0.25 and $O5<>0 and $L5<>0)?$O5+0.0833333333333333:0))*24)-$AA5+
            // =SI(O(G12=1;F12=1);0; ((SI(Y(Q12>R12;O12>0,916666666666667);ABS(M12+(R12/24)-(O12));0)+SI(Y(Q12>R12;O12<0,25;L12=0);ABS(M12-(1+O12)+(R12/24));0))*24)+
            ((SI(Y(Q12>R12;O12<0,25;O12<>0;L12<>0);O12+0,0833333333333333;0))*24)-AA12+SI(Y(I12<0,25;I12<>0;Q12>R12);SI(((0,25-I12)*24)>(Q12-R12);Q12-R12;(0,25-I12)*24)))
            +SI(Y(F13<>1;G13<>1;O(F12=1;G12=1);O12<(6/24));O12*24;0)

       

            return $HENO;
        }

        if($tip==3)
        {
            $HEDF = 0; 
            
            //EXCELL
            //=SI(Y(O(G5=1;F5=1);Q5>8);Q5-8;0)-AA5+SI(Y(O(G6=1;F6=1);Y(G5<>1;F5<>1);AA5>0);O5*24;0)-SI(Y(F6<>1;G6<>1;O(F5=1;G5=1);O5<(6/24));O5*24;0)
            //$HEDF = ((($G5==1|| $F5==1) and $Q5>8)?$Q5-8:0)-$AA5+((($G6==1 ||$F6==1) and ($G5<>1 and $F5<>1) and $AA5>0)?$O5*24:0)-(($F6<>1 and $G6<>1 and ($F5==1 || $G5==1) and $O5<(6/24))?$O5*24:0);
            $HEDF =((($G5==1 || $F5==1) and $Q5>8)? $Q5-8:0)-
            $AA5+((($G6==1 || $F6==1) and ($G5<>1 and $F5<>1) and $AA5>0)?$O5*24:0)-
            (($F6<>1 and $G6<>1 and ($F5==1 || $G5==1) and $O5<(6/24))?$O5*24:0);
            // return round($HEDF,2);
            return $HEDF;
        }

        if($tip==4)
        {
            $HNF  = 0;
            //Excel
            //=((SI(Y(O(F5=1;G5=1);L5=0;M5>0,583333333333333;O5>0,916666666666667);M5+(8/24)-(22/24);0)*24))+((SI(Y(O(F5=1;G5=1);L5=0;M5>0,583333333333333;O5<0,25);M5+((P5/24)-1)+(0,0833333333333);0))*24)-SI(Y(O(F5=1;G5=1);P5>8;L5=0;M5>0,583333333333333;O5<0,25);(P5-8);0)+SI(Y(O(F5=1;G5=1);I5<0,25;I5<>0);(0,25-I5)*24;0)
            //$HNF = ((((($F5==1 || $G5==1) and $L5=0 and $M5>0.54166666666666666666666666666667 and $O5>$HIRN)?$M5+(8/24)-(22/24):0)*24))+((((($F5==1 ||$G5==1)and $L5==0 and $M5>0.54166666666666666666666666666667 and $O5<$HFRN)?$M5+(($P5/24)-1)+(0.04166666666666666666666666666667):0))*24)-((($F5==1 || $G5==1) and $P5>8 and $L5==0 and $M5>0.54166666666666666666666666666667 and $O5<$HFRN)?($P5-8):0)+((($F5==1 || $G5==1) and $I5<$HFRN and $I5<>0)?($HFRN-$I5)*24:0);
            $HNF = ((((($F5==1 || $G5==1) and $L5=0 and $M5>0.54166666666666666666666666666667 and $O5>$HIRN)?$M5+(8/24)-(($HIRN*24)/24):0)*24))+((((($F5==1 ||$G5==1)and $L5==0 and $M5>0.54166666666666666666666666666667 and $O5<$HFRN)?$M5+(($P5/24)-1)+(0.04166666666666666666666666666667):0))*24)-((($F5==1 || $G5==1) and $P5>8 and $L5==0 and $M5>0.54166666666666666666666666666667 and $O5<$HFRN)?($P5-8):0)+((($F5==1 || $G5==1) and $I5<$HFRN and $I5<>0)?($HFRN-$I5)*24:0);
            // return round($HNF,2);
            return $HNF;
        }

        if($tip==5)
        {
            $RD = 0;
            //EXCELL
            //=SI(T5="SI";0;SI(Y(O(G5=1;F5=1);O(S5="SI";S5="Ambos"));SI(Q5<=8;Q5;8);0))
            $RD = ($T5=="SI"?0:((($G5==1 || $F5==1) and ($S5=="SI" || $S5=="Ambos"))?($Q5<=8?$Q5:8):0));
            // return round($RD,2);
            return $RD;
        }

        if($tip==6)
        {
            $RN = 0;
            //EXCELL
            //=SI(O(G5=1;F5=1);0;((SI(Y(L5=0;M5>=0,583333333333333;O5>0,916666666666667;Q5<=R5);M5+(R5/24)-(22/24);0)))*24+((SI(Y(L5=0;M5>0,583333333333333;O5<0,25);M5+((P5/24)-1)+(0,0833333333333);0))*24)-SI(Y(P5>8;L5=0;M5>0,583333333333333;O5<0,25);(P5-R5);0)+SI(Y(I5<0,25;I5<>0;Q5<=R5);(0,25-I5);0)*24)+SI(Y(I5<(6/24);Q5>R5;(0,25-I5)>(Q5/24-R5/24);I5<>0);(0,25-I5)-(Q5/24-R5/24);0)*24
            //$RN = (($G5==1 || $F5==1)?0:(((($L5==0 and $M5>=0.54166666666666666666666666666667 and $O5>$HIRN and $Q5<=$R5)?$M5+($R5/24)-(22/24):0)))*24 +(((($L5==0 and $M5>0.54166666666666666666666666666667 and $O5<$HFRN)?$M5+(($P5/24)-1)+(0.04166666666666666666666666666667):0))*24)-(($P5>8 and $L5==0 and $M5>0.54166666666666666666666666666667 and $O5<$HFRN)?($P5-$R5):0)+(($I5<$HFRN and $I5<>0 and $Q5<=$R5)?($HFRN-$I5):0)*24)+(($I5<(6/24) and $Q5>$R5 and ($HFRN-$I5)>($Q5/24-$R5/24) and $I5<>0)?($HFRN-$I5)-($Q5/24-$R5/24):0)*24;
            $RN = (($G5==1 || $F5==1)?0:(((($L5==0 and $M5>=0.54166666666666666666666666666667 and $O5>$HIRN and $Q5<=$R5)?$M5+($R5/24)-(($HIRN*24)/24):0)))*24 +(((($L5==0 and $M5>0.54166666666666666666666666666667 and $O5<$HFRN)?$M5+(($P5/24)-1)+(0.04166666666666666666666666666667):0))*24)-(($P5>8 and $L5==0 and $M5>0.54166666666666666666666666666667 and $O5<$HFRN)?($P5-$R5):0)+(($I5<$HFRN and $I5<>0 and $Q5<=$R5)?($HFRN-$I5):0)*24)+(($I5<(6/24) and $Q5>$R5 and ($HFRN-$I5)>($Q5/24-$R5/24) and $I5<>0)?($HFRN-$I5)-($Q5/24-$R5/24):0)*24;
            // return round($RN,2);
            return $RN;
        }

        if($tip==7)
        {
            $HDF  = 0;
            //EXCELL
            //=SI(O(G5=1;F5=1);Q5;0)-Y5-AA5+SI(Y(O(G6=1;F6=1);AA5>0;Y(G5<>1;F5<>1));O5*24;0)-SI(Y(O(G5=1;F5=1);S5="SI";T5="");SI(Q5<=8;Q5;"8");0)-SI(Y(F6<>1;G6<>1;O(F5=1;G5=1);O5<(6/24));O5*24;0)-AC5
            
            //$HDF = (($G5==1 || $F5==1)?$Q5:0)-$Y5-$AA5+((($G6==1 || $F6==1) and $AA5>0 and ($G5<>1 and $F5<>1))?$O5*24:0)-((($G5==1 || $F5==1) and $S5=="SI" and  $T5=="")?($Q5<=8?$Q5:8):0)-(($F6<>1 and $G6<>1 and ($F5==1 || $G5==1) and $O5<(6/24))?$O5*24:0)-$AC5;

            $HDF =(($G5==1 || $F5== 1)?$Q5:0)-
                $Y5-$AA5+
                ((($G6==1 || $F6==1) and $AA5>0 and ($G5<>1 and $F5<>1))? $O5*24:0)-
                ((($G5==1 || $F5==1) and $S5=="SI" and $T5=="")?($Q5<=8?$Q5:8):0)-
                (($F6<>1 and $G6<>1 and ($F5==1 || $G5==1) and $O5<(6/24))?$O5*24:0)-$AC5;
            // return round($HDF,2);
            return $HDF;
        }

        if($tip==8)
        {
            $HEDO = 0; // HORAS EXTRAS DIURNAS ORDINA
            //EXCELL
            //=SI(R5>Q5;SI(U5="SI";0;Q5-R5);Q5-R5)-X5-Z5-AA5-Y5-AC5-AD5
            $HEDO = ($R5>$Q5?($U5=='SI'?0:$Q5-$R5):$Q5-$R5)-$X5-$Z5-$AA5-$Y5-$AC5-$AD5;
            //$HEDO = $HEDO - $X5-$Z5-$AA5-$Y5-$AC5-$AD5;
            //$HEDO = "($R5>$Q5?($U5=='SI'?0:$Q5-$R5):$Q5-$R5)-$X5-$Z5-$AA5-$Y5-$AC5-$AD5";
            //return $HEDO;
            // return round($HEDO,2);
            return $HEDO;
        }
    }*/

    function calcularDiaLiquidar($emp,$fecj, $usuario, $fecha)
    {
        global $conectar;

        $datr = self::fnReglas();
        $hirn = $datr['hirn'];
        $hfrn = $datr['hfrn'];
        $han  = $datr['han'];
        $hao  = $datr['hao'];
        $vhan = $datr['vhan'];
        $vhao = $datr['vhao']; 
        $hmes = $datr['hmes'];
        $vhanpm = $datr['vhanpm'];
        $hanpm  = $datr['hanpm'];

        $condat = mysqli_query($conectar, "SELECT hor_clave_int, usu_auxilio FROM tbl_usuarios WHERE usu_clave_int = '".$emp."' LIMIT 1");
        $dat = mysqli_fetch_array($condat);
        $hor = $dat['hor_clave_int'];
        $auxilio = $dat['usu_auxilio'];

        $datr = self::editHorario($hor);
        $nom = $datr['hor_nombre'];
        $lun = $datr['hor_1'];
        $mar = $datr['hor_2'];
        $mie = $datr['hor_3'];
        $jue = $datr['hor_4'];
        $vie = $datr['hor_5'];
        $sab = $datr['hor_6'];
        $dom = $datr['hor_7'];
        $festivo = $datr['hor_8'];

        $condias = mysqli_query($conectar, "SELECT jd.jod_clave_int id, jd.jod_fecha fec, DAYOFWEEK(jd.jod_fecha) dia,jd.jod_semana sem,jd.jod_estado est,jd.jod_observacion obs,totalhorasDia(jd.jod_clave_int) tothoras,diaHabil(jod_fecha) fes,diaHabil(adddate(jod_fecha, interval 1 day)) fess, tn.tin_clave_int idnov, tn.tin_suma sumnov, tn.tin_nombre nomnov  FROM tbl_jornada_dias jd LEFT OUTER JOIN tbl_jornada j ON j.jor_clave_int = jd.jor_clave_int LEFT OUTER JOIN tbl_tipo_novedad tn on tn.tin_clave_int = jd.tin_clave_int WHERE jd.jod_fecha =  '".$fecj."' and jd.usu_clave_int = '".$emp."' order by jod_fecha  asc limit 1");
        $numdias = mysqli_num_rows($condias);
        $datd = mysqli_fetch_array($condias);                
        $idfec = $datd['id'];
        $dia  = $datd['dia'];
        $fec  = $datd['fec'];
        $tothoras = $datd['tothoras'];
        $fes = $datd['fes'];
        $fess = $datd['fess'];//FESTIVO SIGUIENTE
        $bgfes = ($fes==1)?"bg-secondary":""; //dia festivo
        $titfes = ($fes==1)?"Dia Festivo":"";
        $horario = $dia==2? $lun: ($dia==3?$mar:($dia==4?$mie:($dia==5?$jue:($dia==6?$vie:($dia==7?$sab:$dom)))));
        $thorario = ($fes==1)?$festivo:$horario;
        $horario = ($fes==1)?0:$horario;     


        //CONSULTA DE BONIFICACION
        $conbon = mysqli_query($conectar, "SELECT concatLabores('".$emp."','".$fec."','') labores, conBonificacion('".$emp."','".$fec."','') bon");
        $datbon = mysqli_fetch_array($conbon);
        $labores = $datbon['labores'];
        $bonificacion = ($datbon['bon']>0)?1:0; 

        if($numdias>0 and $tothoras!=0)
        {           
            $nmes = date("F", strtotime($fec));
            $ndia = date("l", strtotime($fec));
            $numd = date("d", strtotime($fec));
            $anod = date("Y", strtotime($fec));
            // $fecha = $ndia.", ". $numd." de ".$nmes." de ".$anod;
           
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
    
            //total por permisos
            $conper = mysqli_query($conectar,"SELECT SUM(calculoHoras3(jd.jod_fecha,joh_inicio,joh_fin)) as totalpermisos FROM tbl_jornada_dias jd JOIN tbl_jornada_horas jh ON jh.jod_clave_int = jd.jod_clave_int JOIN tbl_tipo_novedad tn on tn.tin_clave_int = jh.tin_clave_int WHERE tn.tin_suma = 6 and jd.jod_clave_int = '".$idfec."' GROUP BY jd.jod_clave_int");
            $datper = mysqli_fetch_array($conper);
            $totalpermiso = $datper['totalpermisos'];
    
    
            //VERIFICAR SI LA HORA INICIAL del dia es pm o am en caso de que sea pm no hay horari am
            $conam = mysqli_query($conectar, "SELECT joh_clave_int,joh_inicio ini,joh_fin fin,TIME_FORMAT(joh_inicio,'%p') dig FROM tbl_jornada_horas jh JOIN tbl_tipo_novedad t on t.tin_clave_int = jh.tin_clave_int WHERE  t.tin_tipo = 1 and jod_clave_int = '".$idfec."' order by joh_clave_int ASC LIMIT 1"); //HORAS PM and time_format(joh_fin,'%p')='PM'
            $datam = mysqli_fetch_array($conam);
            $amini = $datam['ini']; // hora inicial del primer registro
            $aini = $amini;
            $dig   = $datam['dig']; // digito del primer registro
            $hfini = $datam['fin'];
            // $aini = 
    
            // VERIFICAR HORAS DE MINIMO PM 
            $conpm = mysqli_query($conectar, "SELECT MIN(joh_fin) fin, MAX(joh_fin) maxfin,TIME_FORMAT(joh_fin,'%p') dig  FROM tbl_jornada_horas jh JOIN tbl_tipo_novedad t on t.tin_clave_int = jh.tin_clave_int WHERE  t.tin_tipo = 1 and time_format(joh_fin,'%p')='PM' and jod_clave_int = '".$idfec."'"); //HORAS AM  
            $datpm = mysqli_fetch_array($conpm);
            $pmminfin=  $datpm['fin'];
            $pmmaxfin = $datpm['maxfin'];
            $pmdigfin = $datpm['dig'];
    
    
            // CON HORA DESCANSO
            $condes = mysqli_query($conectar, "SELECT joh_inicio ini,joh_fin fin FROM tbl_jornada_horas jh JOIN tbl_tipo_novedad t on t.tin_clave_int = jh.tin_clave_int WHERE  t.tin_suma = 2 and jod_clave_int = '".$idfec."' order by joh_clave_int ASC LIMIT 1"); //HORAS PM and time_format(joh_fin,'%p')='PM'
            $datdes = mysqli_fetch_array($condes); 
            $hides = $datdes['ini'];
            $hfdes = $datdes['fin'];   
    
            //MINIMO AM SI EXISTIERA
            $conam = mysqli_query($conectar, "SELECT MIN(joh_inicio) ini, MAX(joh_fin) ammax FROM tbl_jornada_horas jh JOIN tbl_tipo_novedad t on t.tin_clave_int = jh.tin_clave_int WHERE  t.tin_tipo = 1 and time_format(joh_inicio,'%p')='AM' and jod_clave_int = '".$idfec."'"); //HORAS AM  
            $datam = mysqli_fetch_array($conam);

            $aminimin = $datam['ini'];
            $amfinmax = $datam['ammax']; // la hora am max


    
            $conpm = mysqli_query($conectar, "SELECT MIN(joh_inicio) fin,TIME_FORMAT(joh_inicio,'%p') dig  FROM tbl_jornada_horas jh JOIN tbl_tipo_novedad t on t.tin_clave_int = jh.tin_clave_int WHERE  t.tin_tipo = 1 and time_format(joh_inicio,'%p')='PM' and jod_clave_int = '".$idfec."'"); //HORAS AM  
            $datpm = mysqli_fetch_array($conpm);
            $pminimin=  $datpm['fin'];
            $pmdig = $datpm['dig'];
    
            //HORA FINAL REGISTRADA
            $conpmf = mysqli_query($conectar, "SELECT joh_clave_int,joh_fin fin,TIME_FORMAT(joh_fin,'%p') dig FROM tbl_jornada_horas jh JOIN tbl_tipo_novedad t on t.tin_clave_int = jh.tin_clave_int WHERE  t.tin_tipo = 1 and jod_clave_int = '".$idfec."' order by joh_clave_int DESC LIMIT 1"); //HORAS PM and time_format(joh_fin,'%p')='PM'
            $datpmf = mysqli_fetch_array($conpmf); 
            $digfin = $datpmf['dig']; // DIGITO DE LA HORA FINAL
            $hffin = $datpmf['fin'];
            


            $aini = $dig == "PM"  ? "" : $aminimin; //OK 20220124
            $hfini = $dig == "PM"  ? "" : "";
    
            //$afin = ($dig == "PM" and self::time_to_sec($hfini)>self::time_to_sec("12:00")? "12:04" : (self::time_to_sec($hfini)==self::time_to_sec($datpmf['fin']) and $digfin=="AM"?"12:03":"12:00"));
    
            if($dig == "PM" and self::time_to_sec($hfini)>self::time_to_sec("12:00"))
            {
                $afin  =  "";
            }
            else if(self::time_to_sec($hfini)==self::time_to_sec($datpmf['fin']) and $digfin=="AM")
            {
                $afin = $hfini;
            }
            else if($aini!="")
            {
                $afin = "12:00";
            }
            
            // (self::time_to_sec($pmmaxfin)==self::time_to_sec($datpmf['fin']) and $digfin=="AM"?$pmmaxfin:"12:00"); 
    
            // $afin =  ($dig =="PM" ? "":(self::time_to_sec($datpm['fin'])>self::time_to_sec("12:00") ?"12:00":$datpm['fin']));//;$datpm['fin']);      
            // $afin = (self::time_to_sec($pmminfin)==self::time_to_sec("12:00")?$pmminfin:$afin);
    
            // $pini = (self::time_to_sec($pmminfin)==self::time_to_sec("12:00") and self::time_to_sec($pmmaxfin)==self::time_to_sec("12:00")?"": $pminimin);
            // $pfin = (self::time_to_sec($pmminfin)==self::time_to_sec("12:00") and self::time_to_sec($pmmaxfin)==self::time_to_sec("12:00")?"": $datpmf['fin']);//$datpmf['fin'];
    
             // validar hora final de la mañana si el almuerzo hora inicial fue mayor a las 12 - 20211207
            // $afin = ($hides!="" and $hides!="00:00:00" ? "12:30":"12:30"); 
            // if($hides!="" and $hides!="00:00:00" and strtotime($hides)>=strtotime("12:00") and strtotime($hfdes)<=strtotime($pini)){ $afin = $hides; }
            // $hides;// (strtotime($hides)>=strtotime("12:00") and strtotime($hfdes)<=strtotime($pini)?$hides: $afin);
            // $afin = (strtotime($hides)>=strtotime("12:00") and strtotime($hfdes)<=strtotime($pini)?$hides: $afin);
            // $pini = ($aini!="" and $afin=="" and $pfin!="")?"12:00":$pini;
            // $afin = ($afin=="" and $pminimin=="" and $pfin!="")?"12:00":$afin;
    
            //$pini = 
            $sq = "";
            if($pminimin=="" and $datpmf['fin']!=""){ 
                $sq.="ENTRO:1 "; 
                $pini = $afin; 
            } else{ 
                $sq.="ENTRO:2 "; 
                    
                $pini =  $pminimin; 
            } 
            $sq.= "PINI:".$pini. " AFIN:".$afin." PMINIMIN:".$pminimin. " DATPMF:".$datpmf['fin'];
            $pfin = $datpmf['fin'];
    
             // validar hora final de la mañana si el almuerzo hora inicial fue mayor a las 12 - 20211207
            // $afin = ($hides!="" and $hides!="00:00:00" ? "12:30":"12:30"); 
            if($hides!="" and $hides!="00:00:00" and strtotime($hides)>=strtotime("12:00") and strtotime($hfdes)<=strtotime($pini)){ $afin = $hides; }
            // $hides;// (strtotime($hides)>=strtotime("12:00") and strtotime($hfdes)<=strtotime($pini)?$hides: $afin);
            // $afin = (strtotime($hides)>=strtotime("12:00") and strtotime($hfdes)<=strtotime($pini)?$hides: $afin);
            
            // $pini = $aini!="" and $afin=="" and $pfin!=""?"12:00":$pini;
            if(self::time_to_sec($hfini)>self::time_to_sec("12:00") and self::time_to_sec($hfini)==self::time_to_sec($pini)){
                $pini = "12:00"; } 


    
            //INICIO
    
            
            //FIN
            $tsaini = self::time_to_sec($aini);
            $tsafin = self::time_to_sec($afin);
            $tspini = self::time_to_sec($pini);
            $tspfin = self::time_to_sec($pfin);
    
            //HORAS RECARGO NOCTURNO
            $tshirn = self::time_to_sec($hirn);
            $tshfrn = self::time_to_sec($hfrn);
    
            //TOTAL HORAS
            $totalam = self::fnHoras($fec,$aini,$afin);
            $totalpm = self::fnHoras($fec,$pini,$pfin);
            $tothoras = $totalam + $totalpm;
             
            
    
            //VALIDACION HORA ALIMENTACION
            $tshan = self::time_to_sec($han);
            $tshanpm = self::time_to_sec($hanpm);
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
                $U5 = ($sumnov==5)?"SI":"";
                //$tothoras = ($sumnov==5 and $tothoras<=$thorario)?$thorario:$tothoras;
                $tothoras = (($sumnov==5  || $sumnov==4 || $sumnov==7) and $tothoras<=$thorario and $fes!=1)?$thorario:$tothoras;
                if($totalpermiso<=0 and $sumnov==6)
                {
                    $totalpermiso = $thorario;
                }
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
    
            //$sqlam = "SELECT MIN(joh_inicio) ini FROM tbl_jornada_horas jh JOIN tbl_tipo_novedad t on t.tin_clave_int = jh.tin_clave_int WHERE  t.tin_tipo = 1 and time_format(joh_inicio,'%p')='AM' and jod_clave_int = '".$idfec."'"; 
        
            // SELECT MIN(jor_inicio), MAX(jor_fin) from tin_tipo = 1 and time_format(jor_ini,'%p')='PM' //HORAS AM
            // SELECT MIN(jor_inicio), MAX(jor_fin) from tin_tipo = 2' //HORAS DESCANSO
    
            // CALCULOS HORAS EXTRAS
            // $totalam = hourdiff($amini,$amfin,true);
            // $totalpm = hourdiff($pmini,$pmfin,true);
    
            $aminit  = ($aini!="")? date("g:i a", strtotime($aini)): $aini;
            $amfint  = ($afin!="")? date("g:i a", strtotime($afin)): $afin;
            $pminit  = ($pini!="")? date("g:i a", strtotime($pini)): $pini;
            $pmfint  = ($pfin!="")? date("g:i a", strtotime($pfin)): $pfin;
           
            // Lista de parametros
            // fes = G5 || F5
            $G5 = $fes; 
            $F5 = $fes;
            // totalhoras =  Q5
            $Q5 =  ($tothoras<$horario and $totalpermiso<=0)?$horario:$tothoras;
            $tothoras = ($tothoras<$horario and $totalpermiso<=0)?$horario:$tothoras;
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
    
            $HENF = self::fnExtras(1, $G5, $F5,$Q5,$R5,$M5, $O5, $L5,$AA5, $I5, $F6, $G6, $P5, $T5, $S5,$Y5, $AC5, $U5, $X5, $Z5, $AD5, $tshirn, $tshfrn, $thorario);
            $AA5  = $HENF;
            //$HENF   = ($HENF<0)?0:$AA5;
    
            $HENO = self::fnExtras(2, $G5, $F5,$Q5,$R5,$M5, $O5, $L5,$AA5, $I5, $F6, $G6, $P5, $T5, $S5,$Y5, $AC5, $U5, $X5, $Z5, $AD5, $tshirn, $tshfrn, $thorario);
            $Z5 = $HENO;
    

            $HNF  = self::fnExtras(4, $G5, $F5,$Q5,$R5,$M5, $O5, $L5,$AA5, $I5, $F6, $G6, $P5, $T5, $S5,$Y5, $AC5, $U5, $X5, $Z5, $AD5, $tshirn, $tshfrn, $thorario);
            $AC5  = $HNF;
            //CALCULO EN REVISION
            $HEDF = self::fnExtras(3, $G5, $F5,$Q5,$R5,$M5, $O5, $L5,$AA5, $I5, $F6, $G6, $P5, $T5, $S5,$Y5, $AC5, $U5, $X5, $Z5, $AD5, $tshirn, $tshfrn, $thorario);
            $Y5   = ($HEDF<0)?0:$HEDF;
            //$HEDF   = ($HEDF<0)?0:$Y5;
    
            $HEDFT =self::fnExtrasText(3, $G5, $F5,$Q5,$R5,$M5, $O5, $L5,$AA5, $I5, $F6, $G6, $P5, $T5, $S5,$Y5, $AC5, $U5, $X5, $Z5, $AD5, $tshirn, $tshfrn, $thorario);
          
           
    
            $RD   = self::fnExtras(5, $G5, $F5,$Q5,$R5,$M5, $O5, $L5,$AA5, $I5, $F6, $G6, $P5, $T5, $S5,$Y5, $AC5, $U5, $X5, $Z5, $AD5, $tshirn, $tshfrn, $thorario);
            $AD5  = $RD;
    
            $RN   = self::fnExtras(6, $G5, $F5,$Q5,$R5,$M5, $O5, $L5,$AA5, $I5, $F6, $G6, $P5, $T5, $S5,$Y5, $AC5, $U5, $X5, $Z5, $AD5, $tshirn, $tshfrn, $thorario);
    
            $HDF  = self::fnExtras(7, $G5, $F5,$Q5,$R5,$M5, $O5, $L5,$AA5, $I5, $F6, $G6, $P5, $T5, $S5,$Y5, $AC5, $U5, $X5, $Z5, $AD5, $tshirn, $tshfrn, $thorario);
            $X5   = $HDF;
            //$HDF   = ($HDF<0)?0:$X5;
    
            //if($tothoras>0)
            //{
                $HEDO = self::fnExtras(8, $G5, $F5,$Q5,$R5,$M5, $O5, $L5,$AA5, $I5, $F6, $G6, $P5, $T5, $S5,$Y5, $AC5, $U5, $X5, $Z5, $AD5, $tshirn, $tshfrn, $thorario);
            //}
    
            $HEDOT = "($R5>$Q5?($U5=='SI'?0:$Q5-$R5):$Q5-$R5)-$X5-$Z5-$AA5-$Y5-$AC5-$AD5";
    
            $M5 = (int)$M5;
            $O5 = (int)$O5;
            //echo "<tr><td colspan='22'>(($G5==1 || $F5==1)?0:(((($L5==0 and $M5>=0.583333333333333 and $O5>0.916666666666667 and $Q5<=$R5)?$M5+($R5/24)-(22/24):0)))*24 +(((($L5==0 and $M5>0.583333333333333 and $O5<0.25)?$M5+(($P5/24)-1)+(0.0833333333333):0))*24)-(($P5>8 and $L5==0 and $M5>0.583333333333333 and $O5<0.25)?($P5-$R5):0)+(($I5<0.25 and $I5<>0 and $Q5<=$R5)?(0.25-$I5):0)*24)+(($I5<(6/24) and $Q5>$R5 and (0.25-$I5)>($Q5/24-$R5/24) and $I5<>0)?(0.25-$I5)-($Q5/24-$R5/24):0)*24</td></tr>";
    
            // echo "<tr><td colspan='22'>$HEDO</td></tr>";
            // echo "<tr><td colspan='22'>".($R5>$Q5?($U5=='SI'?0:$Q5-$R5):$Q5-$R5)-$X5-$Z5-$AA5-$Y5-$AC5-$AD5."</td></tr>";                        
            $HEDO = $HEDO<0?0:$HEDO;
    
            $HEDO  = str_replace(",",".",$HEDO);
            $HDF  = str_replace(",",".",$HDF);
            $HEDF  = str_replace(",",".",$HEDF);
            $HENO  = str_replace(",",".",$HENO);
            $HENF  = str_replace(",",".",$HENF);
            $RN  = str_replace(",",".",$RN);
            $HNF  = str_replace(",",".",$HNF);
            $RD = str_replace(",",".",$RD);
    
            $tothoras = str_replace(",",".",$tothoras);  
            
            
            
            $sqlvliq = mysqli_query($conectar, "SELECT liq_clave_int FROM tbl_liquidar WHERE usu_clave_int = '".$emp."' and obr_clave_int<= 0 and '".$fec."' between liq_inicio and liq_fin limit 1");
            $numvliq = mysqli_num_rows($sqlvliq);
            if($numvliq<=0){ $idliq = 0; }else{ $datvliq = mysqli_fetch_array($sqlvliq); $idliq = $datvliq['liq_clave_int']; }
    
            // VERIFICAR SI EXISTE O NO EL DIA
            $veridia = mysqli_query($conectar, "SELECT lid_clave_int, lid_auxilio, liq_clave_int FROM tbl_liquidar_dias where usu_clave_int = '".$emp."' and  lid_fecha = '".$fec."'");
            $numdia = mysqli_num_rows($veridia);
            $iddia = 0;
            if($numdia>0)
            {
                $datdia = mysqli_fetch_array($veridia);
                $iddia = $datdia['lid_clave_int'];
                $idliq = ($datdia['liq_clave_int']>0?$datdia['liq_clave_int']:$idliq);
                $aux   = $datdia['liq_auxilio'];
                //$totald = $datfec['jod_total'];
                $insfec = mysqli_query($conectar, "UPDATE tbl_liquidar_dias SET lid_fecha = '".$fec."', lid_horario = '".$horario."', lid_hi_man = '".$aini."', lid_hf_man = '".$afin."',lid_hi_tar = '".$pini."',lid_hf_tar = '".$pfin."',lid_horas = '".$tothoras."', lid_hedo = '".$HEDO."',lid_hdf = '".$HDF."' , lid_hedf = '".$HEDF."', lid_heno = '".$HENO."', lid_henf = '".$HENF."', lid_rn = '".$RN."', lid_hnf = '".$HNF."', lid_rd = '".$RD."',lid_usu_actualiz = '".$usuario."', lid_fec_actualiz = '".$fecha."', lid_alimentacion = '".$ali."',lid_val_alimentacion = '".$valorali."', lid_compensado = '".$S5."', lid_remunerado = '".$U5."', lid_observacion = '".$obs."',tin_clave_int = '".$idnov."', lid_labores = '".$labores."',lid_bonificacion = '".$bonificacion."', lid_permisos = '".$totalpermiso."', liq_clave_int = '".$idliq."' WHERE lid_clave_int = '".$iddia."'");
                if($insfec>0)
                {
                    $msn = "Dia actualizado fec:".$fec." - afin:".$afin.". pminimin:".$pminimin." hfini:".$hfini." - hides:".$hides." aminimin:".$aminimin." pini:".$pini." - pfin:.".$pfin.". datpmfin:".$datpm['fin']." .".$hffin.".- Liquidación: ".$idliq." Q5=".$Q5." THORARIO: ".$thorario;
                }
                else
                {
                    $msn = "No actualizo dia ".$fec;
                }                
            }
            else
            {
                $insfec = mysqli_query($conectar, "INSERT INTO tbl_liquidar_dias(lid_fecha, lid_horario, lid_hi_man, lid_hf_man,lid_hi_tar,lid_hf_tar,lid_horas,lid_hedo,lid_hdf,lid_hedf, lid_heno,lid_henf,lid_rn,lid_hnf, lid_rd, usu_clave_int,lid_usu_actualiz,lid_fec_actualiz,lid_compensado,lid_remunerado, lid_observacion, tin_clave_int, lid_labores, lid_bonificacion, lid_auxilio, lid_permisos, liq_clave_int) VALUES('".$fec."','".$horario."','".$aini."','".$afin."','".$pini."','".$pfin."','".$tothoras."','".$HEDO."','".$HDF."','".$HEDF."','".$HENO."','".$HENF."','".$RN."','".$HNF."','".$RD."','".$emp."','".$usuario."','".$fecha."','".$S5."','".$U5."','".$obs."','".$idnov."','".$labores."', '".$bonificacion."', '".$auxilio."','".$totalpermiso."','".$idliq."')");
                if($insfec>0)
                {
                    $idfec = mysqli_insert_id($conectar);
                }
                else
                {
                    $msn = "No inserto dia ".$fec."".mysqli_error($conectar);
                }                        
            }
        }
        else
        {
           
            if($numdias>0)
            {
                $idnov = $datd['idnov'];
                $sumnov = $datd['sumnov'];
                $nomnov = $datd['nomnov'];

                if($sumnov>=3)
                {
                    $obs = $nomnov;
                    $S5 = ($sumnov==4)?"SI":"";           
                    $U5 = ($sumnov==5)?"SI":"";
                    //$tothoras = ($sumnov==5 and $tothoras<=$thorario)?$thorario:$tothoras;
                    $tothoras = (($sumnov==5  || $sumnov==4 || $sumnov==7) and $tothoras<=$thorario and $fes!=1)?$thorario:$tothoras;
                    if($totalpermiso<=0 and $sumnov==6)
                    {
                        $totalpermiso = $thorario;
                    }
                }         

                $veridia = mysqli_query($conectar, "SELECT lid_clave_int, lid_auxilio, liq_clave_int FROM tbl_liquidar_dias where usu_clave_int = '".$emp."' and  lid_fecha = '".$fec."'");
                $numdia = mysqli_num_rows($veridia);
                $iddia = 0;
                if($numdia>0)
                {
                    $datdia = mysqli_fetch_array($veridia);
                    $iddia = $datdia['lid_clave_int'];
                    $idliq = ($datdia['liq_clave_int']>0?$datdia['liq_clave_int']:$idliq);
                    $aux   = $datdia['liq_auxilio'];
                    //$totald = $datfec['jod_total'];
                    $insfec = mysqli_query($conectar, "UPDATE tbl_liquidar_dias SET lid_fecha = '".$fec."', lid_horario = '".$horario."', lid_hi_man = '00:00:00', lid_hf_man = '00:00:00',lid_hi_tar = '00:00:00',lid_hf_tar = '00:00:00',lid_horas = '".$tothoras."', lid_hedo = '0',lid_hdf = '0' , lid_hedf = '0', lid_heno = '0', lid_henf = '0', lid_rn = '0', lid_hnf = '0', lid_rd = '0',lid_usu_actualiz = '".$usuario."', lid_fec_actualiz = '".$fecha."', lid_alimentacion = '0',lid_val_alimentacion = '0', lid_compensado = '".$S5."', lid_remunerado = '".$U5."', lid_observacion = '".$obs."',tin_clave_int = '".$idnov."', lid_labores = '".$labores."',lid_bonificacion = '".$bonificacion."', lid_permisos = '".$totalpermiso."', liq_clave_int = '".$idliq."' WHERE lid_clave_int = '".$iddia."'");
                    if($insfec>0)
                    {
                        $msn = "Dia actualizado fec:".$fec." - afin:".$afin.". pminimin:".$pminimin." hfini:".$hfini." - hides:".$hides." aminimin:".$aminimin." pini:".$pini." - pfin:.".$pfin.". datpmfin:".$datpm['fin']." .".$hffin.".- Liquidación: ".$idliq." - SQ:".$sq;
                    }
                    else
                    {
                        $msn = "No actualizo dia n: ".$fec. "UPDATE tbl_liquidar_dias SET lid_fecha = '".$fec."', lid_horario = '".$horario."', lid_hi_man = '00:00:00', lid_hf_man = '00:00:00',lid_hi_tar = '00:00:00',lid_hf_tar = '00:00:00',lid_horas = '".$tothoras."', lid_hedo = '0',lid_hdf = '0' , lid_hedf = '0', lid_heno = '0', lid_henf = '0', lid_rn = '0', lid_hnf = '0', lid_rd = '0',lid_usu_actualiz = '".$usuario."', lid_fec_actualiz = '".$fecha."', lid_alimentacion = 0',lid_val_alimentacion = '0', lid_compensado = '".$S5."', lid_remunerado = '".$U5."', lid_observacion = '".$obs."',tin_clave_int = '".$idnov."', lid_labores = '".$labores."',lid_bonificacion = '".$bonificacion."', lid_permisos = '".$totalpermiso."', liq_clave_int = '".$idliq."' WHERE lid_clave_int = '".$iddia."'";
                    }                
                }
                else
                {
                    $insfec = mysqli_query($conectar, "INSERT INTO tbl_liquidar_dias(lid_fecha, lid_horario, lid_hi_man, lid_hf_man,lid_hi_tar,lid_hf_tar,lid_horas,lid_hedo,lid_hdf,lid_hedf, lid_heno,lid_henf,lid_rn,lid_hnf, lid_rd, usu_clave_int,lid_usu_actualiz,lid_fec_actualiz,lid_compensado,lid_remunerado, lid_observacion, tin_clave_int, lid_labores, lid_bonificacion, lid_auxilio, lid_permisos, liq_clave_int) VALUES('".$fec."','".$horario."','00:00:00','00:00:00','00:00:00','00:00:00','".$tothoras."','0','0','0','0','0','0','0','0','".$emp."','".$usuario."','".$fecha."','".$S5."','".$U5."','".$obs."','".$idnov."','".$labores."', '".$bonificacion."', '0','".$totalpermiso."','".$idliq."')");
                    if($insfec>0)
                    {
                        $idfec = mysqli_insert_id($conectar);
                    }
                    else
                    {
                        $msn = "No inserto dia ".$fec."".mysqli_error($conectar);
                    }                        
                }
            }
            else
            {       
                $msn = "No hay jornada en ese dia numdias: ".$numdias. " tothoras:".$tothoras;     
                $veridia = mysqli_query($conectar, "SELECT lid_clave_int, lid_auxilio, liq_clave_int FROM tbl_liquidar_dias where usu_clave_int = '".$emp."' and  lid_fecha = '".$fec."'");
                $numdia = mysqli_num_rows($veridia);
                if($numdia>0)
                {
                    $del = mysqli_query($conectar, "UPDATE tbl_liquidar_dias SET  where usu_clave_int = '".$emp."' and  lid_fecha = '".$fec."'");
                }
            }
        }
        return $msn;
    }

    function calcularDiaObra($emp,$fecj, $obr, $usuario, $fecha)
    {
        global $conectar;
        $datr = self::fnReglas();
        $hirn = $datr['hirn'];
        $hfrn = $datr['hfrn'];
        $han  = $datr['han'];
        $hao  = $datr['hao'];
        $vhan = $datr['vhan'];
        $vhao = $datr['vhao']; 
        $hmes = $datr['hmes'];
        $vhanpm = $datr['vhanpm'];
        $hanpm = $datr['hanpm'];
        $msn = "";

        $condat = mysqli_query($conectar, "SELECT hor_clave_int, usu_auxilio FROM tbl_usuarios WHERE usu_clave_int = '".$emp."' LIMIT 1");
        $dat = mysqli_fetch_array($condat);
        $hor = $dat['hor_clave_int'];
        $auxilio = $dat['usu_auxilio'];
        $datr = self::editHorario($hor);
        $nom = $datr['hor_nombre'];
        // $lun = $datr['hor_1'];
        // $mar = $datr['hor_2'];
        // $mie = $datr['hor_3'];
        // $jue = $datr['hor_4'];
        // $vie = $datr['hor_5'];
        // $sab = $datr['hor_6'];
        // $dom = $datr['hor_7'];
        $datr = self::editObra($obr);
        
        $vroperario = $datr['obr_vr_operador'];
        $vrsenalero = $datr['obr_vr_senalero'];
        $vrelevador = $datr['obr_vr_elevador'];
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
        $festivo    = $datr['obr_festivo'];
        $auxilio    = ($datr['obr_auxilio']==1 and $datr['obr_vr_auxilio']>0)?$datr['obr_vr_auxilio']:$auxilio;

        $condias = mysqli_query($conectar, "SELECT  jd.jod_clave_int id, jd.jod_fecha fec, DAYOFWEEK(jd.jod_fecha) dia,jd.jod_semana sem,jd.jod_estado est,jd.jod_observacion obs,totalhorasDia(jd.jod_clave_int) tothoras,diaHabil(jod_fecha) fes,diaHabil(adddate(jod_fecha, interval 1 day)) fess, tn.tin_clave_int idnov, tn.tin_suma sumnov, tn.tin_nombre nomnov  FROM tbl_jornada_dias jd LEFT OUTER JOIN tbl_jornada j ON j.jor_clave_int = jd.jor_clave_int LEFT OUTER JOIN tbl_tipo_novedad tn on tn.tin_clave_int = jd.tin_clave_int WHERE jd.jod_fecha =  '".$fecj."' and jd.usu_clave_int = '".$emp."'  order by jod_fecha  asc");
        $datd = mysqli_fetch_array($condias);                
        $idfec = $datd['id'];
        $dia  = $datd['dia'];
        $fec  = $datd['fec'];
        $tothoras = $datd['tothoras'];
        $nmes = date("F", strtotime($fec));
        $ndia = date("l", strtotime($fec));
        $numd = date("d", strtotime($fec));
        $anod = date("Y", strtotime($fec));
        // $fecha = $ndia.", ". $numd." de ".$nmes." de ".$anod;
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

        // CON HORA DESCANSO
        $condes = mysqli_query($conectar, "SELECT joh_inicio ini,joh_fin fin FROM tbl_jornada_horas jh JOIN tbl_tipo_novedad t on t.tin_clave_int = jh.tin_clave_int WHERE  t.tin_suma = 2 and jod_clave_int = '".$idfec."'  and jh.obr_clave_int = '".$obr."' order by joh_clave_int ASC LIMIT 1"); //HORAS PM and time_format(joh_fin,'%p')='PM'
        $datdes = mysqli_fetch_array($condes); 
        $hides = $datdes['ini'];
        $hfdes = $datdes['fin'];   


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

        if($hides!="" and $hides!="00:00:00" and strtotime($hides)>=strtotime("12:00") and strtotime($hfdes)<=strtotime($pini)){ $afin = $hides; }

        $pini = ($aini!="" and $afin=="" and $pfin!="")?"12:00":$pini;
        $afin = ($afin=="" and $pminimin=="" and $pfin!="")?"12:00":$afin;

        $tsaini = self::time_to_sec($aini);
        $tsafin = self::time_to_sec($afin);
        $tspini = self::time_to_sec($pini);
        $tspfin = self::time_to_sec($pfin);

        //HORAS RECARGO NOCTURNO
        $tshirn = self::time_to_sec($hirn);
        $tshfrn = self::time_to_sec($hfrn);

        //TOTAL HORAS
        $totalam = self::fnHoras($fec,$aini,$afin);
        $totalpm = self::fnHoras($fec,$pini,$pfin);
        $tothoras = $totalam + $totalpm;  
        
        // $horario = $dia==0? $lun: ($dia==1?$mar:($dia==2?$mie:($dia==3?$jue:($dia==4?$vie:($dia==5?$sab:$dom)))));
        $horario = $dia==2? $lun: ($dia==3?$mar:($dia==4?$mie:($dia==5?$jue:($dia==6?$vie:($dia==7?$sab:$dom)))));
        $thorario = ($fes==1)?$festivo:$horario;
        $horario = ($fes==1)?0:$horario; 
        
        $conper = mysqli_query($conectar,"SELECT SUM(calculoHoras3(jd.jod_fecha,joh_inicio,joh_fin)) as totalpermisos FROM tbl_jornada_dias jd JOIN tbl_jornada_horas jh ON jh.jod_clave_int = jd.jod_clave_int JOIN tbl_tipo_novedad tn on tn.tin_clave_int = jh.tin_clave_int WHERE tn.tin_suma = 6 and jd.jod_clave_int = '".$idfec."' and jh.obr_clave_int = '".$obr."' GROUP BY jd.jod_clave_int");
        $datper = mysqli_fetch_array($conper);
        $totalpermiso = $datper['totalpermisos'];

        //SUMA O NO SEGUN EL TIPO DE NOVEDAD
        if($sumnov>=3)
        {
            $obs = $nomnov;
            $S5 = ($sumnov==4)?"SI":"";          
            $U5 = ($sumnov==5)?"SI":"";
            // $tothoras = (($sumnov==5  || $sumnov==4) and $tothoras<=$thorario)?$thorario:$tothoras;
            $tothoras = (($sumnov==5  || $sumnov==4 || $sumnov==7) and $tothoras<=$thorario and $fes!=1)?$thorario:$tothoras;
            if($totalpermiso<=0 and $sumnov==6)
            {
                $totalpermiso = $thorario;
            }
        }

        $tothorasmaquina = ($tothoras>0 and $tothoras<$thorario)?$thorario: $tothoras;

        //VALIDACION HORA ALIMENTACION
        $tshao = self::time_to_sec($hao);
        $tshanpm = self::time_to_sec($hanpm);
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
        //$sqlam = "SELECT MIN(joh_inicio) ini FROM tbl_jornada_horas jh JOIN tbl_tipo_novedad t on t.tin_clave_int = jh.tin_clave_int WHERE  t.tin_tipo = 1 and time_format(joh_inicio,'%p')='AM' and jod_clave_int = '".$idfec."' and  jh.obr_clave_int = '".$obr."'"; 
    
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

        $HENF = self::fnExtras(1, $G5, $F5,$Q5,$R5,$M5, $O5, $L5,$AA5, $I5, $F6, $G6, $P5, $T5, $S5,$Y5, $AC5, $U5, $X5, $Z5, $AD5, $tshirn, $tshfrn, $thorario);
        $AA5  = $HENF;
        // $HENF   = ($HENF<0)?0:$AA5;

        $HENO = self::fnExtras(2, $G5, $F5,$Q5,$R5,$M5, $O5, $L5,$AA5, $I5, $F6, $G6, $P5, $T5, $S5,$Y5, $AC5, $U5, $X5, $Z5, $AD5, $tshirn, $tshfrn, $thorario);
        $Z5   = $HENO;

        $HEDF = self::fnExtras(3, $G5, $F5,$Q5,$R5,$M5, $O5, $L5,$AA5, $I5, $F6, $G6, $P5, $T5, $S5,$Y5, $AC5, $U5, $X5, $Z5, $AD5, $tshirn, $tshfrn, $thorario);
        $Y5   = $HEDF;
        $HEDF   = ($HEDF<0)?0:$Y5;


        $HNF  = self::fnExtras(4, $G5, $F5,$Q5,$R5,$M5, $O5, $L5,$AA5, $I5, $F6, $G6, $P5, $T5, $S5,$Y5, $AC5, $U5, $X5, $Z5, $AD5, $tshirn, $tshfrn, $thorario);
        $AC5  = $HNF;

        $RD   = self::fnExtras(5, $G5, $F5,$Q5,$R5,$M5, $O5, $L5,$AA5, $I5, $F6, $G6, $P5, $T5, $S5,$Y5, $AC5, $U5, $X5, $Z5, $AD5, $tshirn, $tshfrn, $thorario);
        $AD5  = $RD;

        $RN   = self::fnExtras(6, $G5, $F5,$Q5,$R5,$M5, $O5, $L5,$AA5, $I5, $F6, $G6, $P5, $T5, $S5,$Y5, $AC5, $U5, $X5, $Z5, $AD5, $tshirn, $tshfrn, $thorario);

        $HDF  = self::fnExtras(7, $G5, $F5,$Q5,$R5,$M5, $O5, $L5,$AA5, $I5, $F6, $G6, $P5, $T5, $S5,$Y5, $AC5, $U5, $X5, $Z5, $AD5, $tshirn, $tshfrn, $thorario);
        $X5   = $HDF;

        if($tothoras>0)
        {
            $HEDO = self::fnExtras(8, $G5, $F5,$Q5,$R5,$M5, $O5, $L5,$AA5, $I5, $F6, $G6, $P5, $T5, $S5,$Y5, $AC5, $U5, $X5, $Z5, $AD5, $tshirn, $tshfrn, $thorario);
        }
        $M5 = (int)$M5;
        $O5 = (int)$O5;

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

        $conbon = mysqli_query($conectar, "SELECT concatLabores('".$emp."','".$fec."','".$obr."') labores, conBonificacion('".$emp."','".$fec."','".$obr."') bon");
        $datbon = mysqli_fetch_array($conbon);
        $labores = $datbon['labores'];
        $bonificacion = ($datbon['bon']>0)?1:0;

        $sqlvliq = mysqli_query($conectar, "SELECT liq_clave_int FROM tbl_liquidar WHERE usu_clave_int = '".$emp."' and obr_clave_int = '".$obr."' and '".$fec."' between liq_inicio and liq_fin limit 1");
        $numvliq = mysqli_num_rows($sqlvliq);
        if($numvliq<=0){ $idliq = 0; }else{ $datvliq = mysqli_fetch_array($sqlvliq); $idliq = $datvliq['liq_clave_int']; }

        $veridia = mysqli_query($conectar, "SELECT lid_clave_int, lid_estado, liq_clave_int FROM tbl_liquidar_dias_obra where usu_clave_int = '".$emp."' and  lid_fecha = '".$fec."' and obr_clave_int = '".$obr."'");
        $numdia = mysqli_num_rows($veridia);
        $iddia = 0;
        if($numdia>0)
        {
            $datdia = mysqli_fetch_array($veridia);
            $idliq = ($datdia['liq_clave_int']>0?$datdia['liq_clave_int']:$idliq);
            $iddia = $datdia['lid_clave_int'];
            //$totald = $datfec['jod_total'];
            $insfec = mysqli_query($conectar, "UPDATE tbl_liquidar_dias_obra SET lid_fecha = '".$fec."', lid_horario = '".$horario."', lid_hi_man = '".$aini."', lid_hf_man = '".$afin."',lid_hi_tar = '".$pini."',lid_hf_tar = '".$pfin."',lid_horas = '".$tothoras."', lid_hedo = '".$HEDO."',lid_hdf = '".$HDF."' , lid_hedf = '".$HEDF."', lid_heno = '".$HENO."', lid_henf = '".$HENF."', lid_rn = '".$RN."', lid_hnf = '".$HNF."', lid_rd = '".$RD."',lid_usu_actualiz = '".$usuario."', lid_fec_actualiz = '".$fecha."', lid_alimentacion = '".$ali."',lid_val_alimentacion = '".$valorali."', lid_compensado = '".$S5."', lid_remunerado = '".$U5."', lid_observacion = '".$obs."', tin_clave_int = '".$idnov."', lid_horas_maquina = '".$tothorasmaquina."', lid_labores = '".$labores."', lid_bonificacion = '".$bonificacion."', lid_permisos = '".$totalpermiso."', liq_clave_int = '".$idliq."' WHERE lid_clave_int = '".$iddia."'");
            if($insfec>0)
            {
                //$iddia = mysqli_insert_id($conectar);
                $msn.= " Se actualizo dia obra ".$fec;
            }
            else
            {
                $msn.= " No actualizo dia obra ".$fec.mysqli_error($conectar);
            }                
        }
        else
        {
            $insfec = mysqli_query($conectar, "INSERT INTO tbl_liquidar_dias_obra(lid_fecha, lid_horario, lid_hi_man, lid_hf_man,lid_hi_tar,lid_hf_tar,lid_horas,lid_hedo,lid_hdf,lid_hedf, lid_heno,lid_henf,lid_rn,lid_hnf, lid_rd, usu_clave_int,lid_usu_actualiz,lid_fec_actualiz,obr_clave_int, lid_compensado, lid_remunerado, lid_observacion, tin_clave_int, lid_horas_maquina, lid_labores, lid_bonificacion, lid_auxilio, lid_permisos, liq_clave_int) VALUES('".$fec."','".$horario."','".$aini."','".$afin."','".$pini."','".$pfin."','".$tothoras."','".$HEDO."','".$HDF."','".$HEDF."','".$HENO."','".$HENF."','".$RN."','".$HNF."','".$RD."','".$emp."','".$usuario."','".$fecha."','".$obr."','".$S5."','".$U5."','".$obs."','".$idnov."', '".$tothorasmaquina."', '".$labores."','".$bonificacion."', '".$auxilio."','".$totalpermiso."', '".$idliq."')");
            if($insfec>0)
            {
                $idfec = mysqli_insert_id($conectar);
            }
            else
            {
                $msn.= "No inserto dia ".$fec." ".mysqli_error($conectar);
            }                        
        }
        return $msn;
    }   

     // TIEMPO NUMERICO DE CADA HORA 
    function time_to_sec($time) {
		$exp = explode(":",$time);
		$r = ((((int)$exp[0]) * 60) + ((int)$exp[1]))/60/24;
        $r = str_replace(",",".",$r);
		return $r;
	}

    //PORCENTAJE POR CADA TIPO DE HORA
    function getPorcentaje($pdo, $tipo, $opc = 1, $id = 0) // $pdo es el objeto PDO previamente creado
    {
        global $conn;
        switch ($opc) {
            case 1:
                $sql = "SELECT hor_porcentaje, hor_codigo, hor_descripcion 
                        FROM tbl_horas 
                        WHERE hor_nombre = :tipo 
                        LIMIT 1";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':tipo', $tipo, PDO::PARAM_STR);
                break;
    
            case 2:
                $sql = "SELECT lih_porcentaje, hor_codigo, hor_descripcion 
                        FROM tbl_horas h 
                        JOIN tbl_liquidar_horas l ON l.hor_clave_int = h.hor_clave_int 
                        WHERE hor_nombre = :tipo AND l.liq_clave_int = :id 
                        LIMIT 1";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':tipo', $tipo, PDO::PARAM_STR);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                break;
    
            case 3:
                $sql = "SELECT lih_porcentaje, hor_codigo, hor_descripcion 
                        FROM tbl_horas h 
                        JOIN tbl_liquidar_obras_horas l ON l.hor_clave_int = h.hor_clave_int 
                        WHERE hor_nombre = :tipo AND l.lio_clave_int = :id 
                        LIMIT 1";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':tipo', $tipo, PDO::PARAM_STR);
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                break;
    
            default:
                return null;
        }
    
        $stmt->execute();
        $dat = $stmt->fetch(PDO::FETCH_ASSOC);
        return $dat;
    }
    

    function fechaDia($pdo, $idfec)
    {
        global $conn;
        $sql = "SELECT jod_fecha AS fec, usu_clave_int AS emp 
                FROM tbl_jornada_dias 
                WHERE jod_clave_int = :idfec";
    
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':idfec', $idfec, PDO::PARAM_INT);
        $stmt->execute();
    
        $dat = $stmt->fetch(PDO::FETCH_ASSOC);
        return $dat;
    }

    function datosHora($pdo, $id)
    {
        global $conn;
        $sql = "SELECT obr_clave_int AS obr 
                FROM tbl_jornada_horas 
                WHERE joh_clave_int = :id";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $dat = $stmt->fetch(PDO::FETCH_ASSOC);
        return $dat;
    }
}