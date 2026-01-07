<?php
	session_start();
	error_reporting(0);
	ini_set("session.gc_maxlifetime","2592000");
	include('conexion.php');
	include 'db.config.php';
	setlocale(LC_TIME, 'es_CO.UTF-8'); 
date_default_timezone_set('America/Bogota');

	// Función para obtener información del dispositivo
	function getDeviceInfo() {
		$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
		$device = 'Unknown';
		$platform = 'Unknown';

		// Detectar plataforma
		if (preg_match('/Windows/i', $userAgent)) {
			$platform = 'Windows';
		} elseif (preg_match('/Macintosh|Mac OS X/i', $userAgent)) {
			$platform = 'macOS';
		} elseif (preg_match('/Linux/i', $userAgent)) {
			$platform = 'Linux';
		} elseif (preg_match('/Android/i', $userAgent)) {
			$platform = 'Android';
		} elseif (preg_match('/iPhone|iPad|iPod/i', $userAgent)) {
			$platform = 'iOS';
		}

		// Detectar tipo de dispositivo
		if (preg_match('/Mobile|Android|iPhone|iPod/i', $userAgent)) {
			$device = 'Mobile';
		} elseif (preg_match('/iPad|Tablet/i', $userAgent)) {
			$device = 'Tablet';
		} else {
			$device = 'Desktop';
		}

		return [
			'device' => $device,
			'platform' => $platform,
			'userAgent' => $userAgent
		];
	}

	// Función para registrar el log de sesión
	function sesionLog($conn, $usuId, $nombre) {
		$deviceInfo = getDeviceInfo();
		
		try {
			$stmt = $conn->prepare("INSERT INTO tbl_sesiones (usu_id, usu_name, ses_user_agent, ses_platform, ses_device) VALUES (:usu_id, :usu_name, :user_agent, :platform, :device)");
			$stmt->bindParam(':usu_id', $usuId);
			$stmt->bindParam(':usu_name', $nombre);
			$stmt->bindParam(':user_agent', $deviceInfo['userAgent']);
			$stmt->bindParam(':platform', $deviceInfo['platform']);
			$stmt->bindParam(':device', $deviceInfo['device']);
			$stmt->execute();
		} catch (Exception $e) {
            $datos = array(
                "res" => "error",
                "msn" => "Error al registrar el log de sesión: " . $e->getMessage(),
                "url" => ""
            );

			echo json_encode($datos, JSON_FORCE_OBJECT);
		}
	}

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

            // Registrar log de sesión
            sesionLog($conn, $idu, $nom);

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