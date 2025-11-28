<?php
$HORARIO  = 8.5; //COLUMNA 4
$HORASDIA = 9;// COLUMNA Q
$HORASMANANA = 4;//COLUMNA L
$HORAINICIALTARDE = 10; //COLUMNA M
$FES      = 1;
$DIA      = "DOM";
$FESANTERIOR = 1;
$PERMISO  = "SI";
$COMPENSADO = "SI";
$PAGADOYCOMPENSADO = "";
$DIAANTERIOR = "LUNES";
$HORAFINAL =  21; //hora final de cada dia ingresado

//COLUMNA O ES LA HORAFINAL EN ENTERO ASI QUE LOS DECIMALES QUE PONEN EN LOS CONDICIONALES MULTIPLICARLO POR 24 Y PONER ESE NUMERO ENTERO EN VEZ DE LOS DECIMANLES SI ESTA MULTIPLICADO POR 24 OMITIR ESA MULTIPLICACION

$HENF = 0; // HORAS EXTRAS NOCTURNAS FESTIVAS
// =SI(O(G4=1;F4=1);((SI(Y(Q4>8;O4>0,916666666666667);O4-0,916666666666667;0)+
//SI(Y(Q4>8;O4<0,25;L4=0);ABS(M4-(1+O4)+(8/24));0))*24)+
//((SI(Y(Q4>8;O4<0,25;O4<>0;L4<>0);O4+0,0833333333333333;0))*24);0)+
//SI(Y(O(G5=1;F5=1);Y(F4<>1;G4<>1);O4<(6/24));O4*24;0)-
//SI(Y(F5<>1;G5<>1;O(F4=1;G4=1);O4<(6/24));O4*24;0)

$HENF = ((($DIA=="DOM" || $FES==1)?
            //ENTONCS SI
            (($HORASDIA>8 and $HORAFINAL>0.916666666666667/* 22 */)?
                $HORAFINAL-0.916666666666667:0
            )+
            ((($HORASDIA>8 and $HORAFINAL<0.25 and $HORAMANANA==0)? abs($HORAINICIALTARDE-(1+$HORAFINAL)+(8/24)):0)*24)+
            ((($HORASDIA>8 and $HORAFINAL<0.25 and $HORAFINAL!=0 and $HORAMANANA!=0)?$HORAFINAL+0.0833333333333333:0)*24):0)+
            ((($DIASIGUIENTE=="DOM" || $FESSEGUIENTE==1) and ($FES<>1 and $DIA!="DOM") and $HORAFINAL<(6/24))?$HORAFINAL*24:0)-
            (($FESSEGUIENTE!=1 and $DIASIGUIENTE!="DOM" and ($FES==1  || $DIA=="DOM") and $HORAFINAL<(6/24))?$HORAFINAL*24:0));

$HENO = 0; // HORAS EXTRAS NOCTURNAS ORDINARIAS
//
=SI(
        O(G6=1;F6=1);0;
        (
            (
                SI(Y(Q6>R6;O6>0,916666666666667)
                ;ABS(M6+(R6/24)-(O6))
                ;0)
                +SI(Y(Q6>R6;O6<0,25;L6=0);
                ABS(M6-(1+O6)+(R6/24));0
                )
            )*24
        )+
        (
            (
                SI(Y(Q6>R6;O6<0,25;O6<>0;L6<>0)
                    ;O6+0,0833333333333333;0)
            )*24
        )-AA6+
        SI(Y(I6<0,25;I6<>0;Q6>R6);
            SI( ((0,25-I6)*24)>(Q6-R6);
                Q6-R6;(0,25-I6)*24
            )
        )
    )+
    SI(Y(F7<>1;G7<>1;O(F6=1;G6=1);O6<(6/24));
        O6*24;
        0
    )




$HEDF = 0; // HORAS EXTRAS DIURNAS FESTIVAS
//=SI(Y(O(G4=1;F4=1);Q4>8);Q4-8;0)-AA4+SI(Y(O(G5=1;F5=1);Y(G4<>1;F4<>1);AA4>0);O4*24;0)-SI(Y(F5<>1;G5<>1;O(F4=1;G4=1);O4<(6/24));O4*24;0)
$HEDF = ((($DIA=="DOM" || $FES==1) and $HORASDIA>8)?$HORASDIA-8:0)-$HENF+
        ((($DIASIGUIENTE=="DOM" || $FESSEGUIENTE==1) and ($DIA!="DOM" AND $FES!=1) and $HENF>0)?$HORAFINAL*24:0)-
        (($FESSEGUIENTE!=1 AND $DIASIGUIENTE!="DOM" and ($FES==1 || $DIA=="DOM") and $horafinal<(6/24))?$HORAFINAL*24:0);
//los datos que se multiplican por venticuatro es para definir el numero de hora final de 1 a las 24 en entero  y 6/24 = 6 hora en entero
//convertir hora en entero y quitar toda las multiplicacion x 24 e igual las divisiones por 24 para php      



$HNF  = 0; // HORAS NOCTURNAS FESTIVAS
//=((SI(Y(O(F6=1;G6=1);L6=0;M6>0,583333333333333;O6>0,916666666666667);M6+(8/24)-(22/24);0)*24))+((SI(Y(O(F6=1;G6=1);L6=0;M6>0,583333333333333;O6<0,25);M6+((P6/24)-1)+(0,0833333333333);0))*24)-SI(Y(O(F6=1;G6=1);P6>8;L6=0;M6>0,583333333333333;O6<0,25);(P6-8);0)+SI(Y(O(F6=1;G6=1);I6<0,25;I6<>0);(0,25-I6)*24;0)
//$HNF = ((($FES==1 || $dia=="DOM") and $HORAMANANA==0 and $HORAINICIALTARDE>14))


$RD = 0; // RECARGO DOMINICAL
//=SI(T4="SI";0;SI(Y(O(G4=1;F4=1);O(S4="SI";S4="Ambos"));SI(Q4<=8;Q4;8);0))
$RD = ($PAGADOYCOMPENSADO=="SI")?0:(($DIA=="DOM" || $FES==1) and ($COMPENSADO=="SI" || $COMPENSADO=="Ambos"))?(($HORASDIA<=8)?$HORASDIA:8):0;


$RN   = 0; // RECARGO NOCTURNO
//=SI
    (
        O(G4=1;F4=1);0;
        ((SI
            (Y(L4=0;M4>=0,583333333333333;O4>0,916666666666667;Q4<=R4);M4+(R4/24)-(22/24);0)
        )
        )*24+
        ((SI
            (Y(L4=0;M4>0,583333333333333;O4<0,25);M4+((P4/24)-1)+(0,0833333333333);0))*24)-
        SI
            (Y(P4>8;L4=0;M4>0,583333333333333;O4<0,25);(P4-R4);0)+
        SI(
            Y(I4<0,25;I4<>0;Q4<=R4);(0,25-I4);0)*24
    )
        
  +SI(
        Y(I4<(6/24);Q4>R4;(0,25-I4)>(Q4/24-R4/24);I4<>0);(0,25-I4)-(Q4/24-R4/24);0)*24




$HDF  = 0; // HORAS DIURNAS FESTIVAS
//=SI(O(G5=1;F5=1);Q5;0)-Y5-AA5+SI(Y(O(G6=1;F6=1);AA5>0;Y(G5<>1;F5<>1));O5*24;0)
//-SI(Y(O(G5=1;F5=1);S5="SI";T5="");SI(Q5<=8;Q5;"8");0)
//-SI(Y(F6<>1;G6<>1;O(F5=1;G5=1);O5<(6/24));O5*24;0)-AC5
$HDF = ($DIA=="DOM" || $FES==1)?$HORASDIA:0;
$HDF-= $HEDF-$HENF+
    /*PRIMERA CONDICION*/((($DIASIGUIENTE=="DOM" || $FES==1) and $HENF>0 and ($DIAANTERIOR!="DOM" and $FES!=1))? $HORAFINAL*24:0)-
    /*SEGUNDA CONDICION*/((($DIA=="DOM" || $FES==1) and $COMPENSADO=="SI" and $PAGADOYCOMPENSADO=="")?(($HORASDIA<=8)?$HORASDIA:8):0)-
    /*TERCERA CONDICION */(($FES!=1 and $DIA!="DOM" and ($FESANTERIOR==1 || $DIA=="DOM") and $HORAFINAL<(6/24))?$HORAFINAL*24:0)-$HNF;


$HEDO = 0; // HORAS EXTRAS DIURNAS ORDINA
//SI(R5>Q5;SI(U5="SI";0;Q5-R5);Q5-R5)-X5-Z5-AA5-Y5-AC5-AD5  =>EXCEL
$HEDO = ($HORARIO>0)?(($PERMISO=="SI")?0:$HORASDIA-$HORARIO):$HORARIO-$HORARIO;
$HEDO-= $HDF-$HENO-$HENF-$HNF-$RN;