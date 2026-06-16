<?php

function grupo_valido($con, $id_grupo){
    $stmt = mysqli_prepare($con, "SELECT 1 FROM grupo WHERE id_grupo = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id_grupo);
    mysqli_stmt_execute($stmt);
    return (bool) mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

function usuario_valido($con, $id_tipo_usuario){
    $id = (int) $id_tipo_usuario;
    $stmt = mysqli_prepare($con, "SELECT 1 FROM tipo_usuario WHERE id_tipo_usuario = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    return (bool) mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

function turno_valido($con, $id_turno){
    $id_turno = (int) $id_turno;
    $stmt = mysqli_prepare($con, "SELECT 1 FROM turno WHERE id_turno = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id_turno);
    mysqli_stmt_execute($stmt);
    return (bool) mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}


function validacion_contrasena($pass) {
    if (strlen($pass) < 6)
        return false;
    $tiene_mayus = false;
    $tiene_numeros= false;

    for ($i = 0; $i < strlen($pass); $i++) {
        if (ctype_upper($pass[$i])) 
            $tiene_mayus = true;
        if (ctype_digit($pass[$i]))             
            $tiene_numeros = true;
    }
    return ($tiene_mayus && $tiene_numeros);
}



// $max: límite de caracteres (50 para campos cortos como nombre, 1000 para descripciones).
function sanitizar_entrada($conexion, $datos, $max = 50) {
    $datos = trim($datos);
    $datos = str_replace(['--', '/*', '*/'], '', $datos);
    $datos = substr($datos, 0, $max);
    return mysqli_real_escape_string($conexion, $datos);
}

function validar_correo($email){

    if (filter_var($email, FILTER_VALIDATE_EMAIL)){
        return true;
    }
    else{
        return false;
    }
}

// Cada rol tiene un formato y dominio de correo institucional distinto:
// Alumno:    número de cuenta@alumno.enp.unam.mx  (ej. 321056900@alumno.enp.unam.mx)
// Profesor / Administrador: nombre.apellido@enp.unam.mx (ej. carlos.campos@enp.unam.mx)
function validar_correo_rol($correo, $id_tipo_usuario){
    if (!validar_correo($correo)) return false;
    if ((int)$id_tipo_usuario == ROL_ALUMNO)
        return (bool) preg_match('/^[0-9]+@alumno\.enp\.unam\.mx$/', $correo);
    return (bool) preg_match('/^[a-zA-Z]+(\.[a-zA-Z]+)+@enp\.unam\.mx$/', $correo);
}

function validar_numero($numero){
    return filter_var($numero, FILTER_VALIDATE_INT) !== false;
}

// Valida que un entero esté dentro del rango [min, max] inclusive.
function validar_rango($valor, $min, $max){
    $valor = (int) $valor;
    return $valor >= $min && $valor <= $max;
}

// Valida que un string de fecha sea un datetime-local válido (YYYY-MM-DDTHH:MM).
function validar_fecha($fecha){
    if (empty($fecha)) return false;
    if (!preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $fecha)) return false;
    [$fecha_parte, $hora_parte] = explode('T', $fecha);
    [$anio, $mes, $dia]         = explode('-', $fecha_parte);
    [$hora, $minuto]            = explode(':', $hora_parte);
    return checkdate((int)$mes, (int)$dia, (int)$anio)
        && (int)$hora >= 0 && (int)$hora <= 23
        && (int)$minuto >= 0 && (int)$minuto <= 59;
}

function hashear_password($pass){

    //Generamos el hash
    //
    //
    //
    //    $password_hasheada = password_hash($pass, PASSWORD_DEFAULT);

    return $password_hasheada;
}

/*
function validar_password($pass_login, $hash_base_de_datos){
    password_verify($passLogin, $hash_base_de_datos)
}
*/
?>