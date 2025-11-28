
<?php

 function fnExtras($tip, $G5, $F5,$Q5,$R5,$M5, $O5, $L5,$AA5, $I5, $F6, $G6, $P5, $T5, $S5,$Y5, $AC5, $U5, $X5, $Z5, $AD5, $HIRN, $HFRN, $thorario = 8)//HIRN HORA INICIAL REGARGO NOCTURNO
    {
        // $thorario = 8;
        
        $HIRN = ($HIRN=="" || $HIRN==NULL)?0.916666666666667:$HIRN;
        $HFRN = ($HFRN=="" || $HFRN==NULL)?0.25:$HFRN;

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
          
            return $HENO;
        }

       


        if($tip==3)
        {
            $HEDF = 0; 
            
            //EXCELL
            //=SI(Y(O(G5=1;F5=1);Q5>8);Q5-8;0)-AA5+SI(Y(O(G6=1;F6=1);Y(G5<>1;F5<>1);AA5>0);O5*24;0)-SI(Y(F6<>1;G6<>1;O(F5=1;G5=1);O5<(6/24));O5*24;0)
           
          
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
         
            return $RD;
        }

        if($tip==6)
        {
            $RN = 0;
            //EXCELL
            //=SI(O(G5=1;F5=1);0;((SI(Y(L5=0;M5>=0,583333333333333;O5>0,916666666666667;Q5<=R5);M5+(R5/24)-(22/24);0)))*24+((SI(Y(L5=0;M5>0,583333333333333;O5<0,25);M5+((P5/24)-1)+(0,0833333333333);0))*24)-SI(Y(P5>8;L5=0;M5>0,583333333333333;O5<0,25);(P5-R5);0)+SI(Y(I5<0,25;I5<>0;Q5<=R5);(0,25-I5);0)*24)+SI(Y(I5<(6/24);Q5>R5;(0,25-I5)>(Q5/24-R5/24);I5<>0);(0,25-I5)-(Q5/24-R5/24);0)*24
          
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
            //=SI(O(G5=1;F5=1);Q5;0)-Y5-AA5+SI(Y(O(G6=1;F6=1);AA5>0;Y(G5<>1;F5<>1));O5*24;0)-SI(Y(O(G5=1;F5=1);S5="SI";T5="");SI(Q5<=8;Q5;"8");0)-SI(Y(F6<>1;G6<>1;O(F5=1;G5=1);O5<(6/24));O5*24;0)-AC5;
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
            return $HEDO;
        }
    }