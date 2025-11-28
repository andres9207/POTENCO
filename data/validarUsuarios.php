<?php
	session_start();
	error_reporting(0);
	ini_set("session.gc_maxlifetime","2592000");
	include('conexion.php');
	include 'db.config.php';
	setlocale(LC_TIME, 'es_CO.UTF-8'); 
date_default_timezone_set('America/Bogota');
	$email= $_POST['loginmail'];
	$contrasena= $_POST['loginpass'];
	$contrasenaMala = "0";
    $res = "";
    $msn = "";
    $url = "";
	$contrasenaEncript= md5($contrasena);
	
	if (($contrasena == NULL) || ($email == NULL)) {
    $contrasenaMala = "1";
    $res = "error";
    $msn = "Correo electrónico o Contraseña incorrectos. En caso de haber olvidado tu datos de inicio de sesión puedes recuperar tus datos en el link ¿Olvidaste Tu contraseña? ";
    session_destroy();
} else if ($contrasena != '' && $email != '') {
    $contrasenaMala = "2";

    $stmt = $conn->prepare("SELECT * FROM tbl_usuarios WHERE (usu_correo = :email OR UPPER(usu_usuario) = UPPER(:email)) AND est_clave_int != 3 LIMIT 1");
    $stmt->bindParam(':email', $email);
    $stmt->execute();
    $dato = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($dato) {
        $usu = $dato['usu_usuario'];
        $nom = $dato['usu_nombre'] . " " . $dato['usu_apellido'];
        if ($usu == "" || $usu == NULL) {
            $usu = $nom;
        }
        $ema = $dato['usu_correo'];
        $img = $dato['usu_imagen'];
        $contra = decrypt($dato['usu_clave'], 'p4v4sAp');
        $idu = $dato['usu_clave_int'];
        $per = $dato['prf_clave_int'];
        $est = $dato['est_clave_int'];

        if ($contra != $contrasena) {
            $res = "error";
            $msn = "Correo electrónico o Contraseña incorrectos. En caso de haber olvidado tu datos de inicio de sesión puedes recuperar tus datos en el link ¿Olvidaste Tu contraseña? ";
            session_destroy();
        } else if ($est == 0) {
            $res = "error";
            $msn = "El usuario ingresado se encuentra inactivo";
            session_destroy();
        } else {
            $_SESSION['idusuarioPo'] = $idu;
            $_SESSION["perfilPo"] = $per;

            setcookie("imgPo", $img, time() + (86400), "/");
            setcookie("nombrePo", $usu, time() + (86400), "/");
            setcookie("usuarioPo", $usu, time() + (86400), "/");
            setcookie("idusuarioPo", $idu, time() + (86400), "/");

            $identificador = session_id();
            $url = "principal.php#/Inicio";
            $res = "ok";
            $msn = "";
        }
    } else {
        $res = "error";
        $msn = "Correo electrónico o Contraseña incorrectos. En caso de haber olvidado tu datos de inicio de sesión puedes recuperar tus datos en el link ¿Olvidaste Tu contraseña? ";
        session_destroy();
    }
}

$datos = array(
    "res" => $res,
    "msn" => $msn,
    "url" => $url
);

echo json_encode($datos, JSON_FORCE_OBJECT);
?> 