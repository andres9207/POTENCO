<?php

session_start();
setcookie("usuarioPo", "", time() - 3600, "/");
setcookie("idusuarioPo", "", time() - 3600, "/");
//setcookie("clave", "", time() - 3600, "/");
session_destroy();
header("LOCATION:../index.php");
?>