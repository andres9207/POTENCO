<?php
$stmt = $conn->prepare("SELECT per_clave_int, peu_perfiles FROM tbl_permisos_usuarios WHERE usu_clave_int = :id AND est_clave_int = 1");
$stmt->execute([':id' => $idUsuario]);
$permisos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Transformar el resultado a un arreglo accesible
$permisosUsuario = [];
foreach ($permisos as $permiso) {
    $permisosUsuario[$permiso['per_clave_int']] = $permiso['peu_perfiles'] ?? true;
}