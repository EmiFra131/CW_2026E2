<?php

//validar que el grupo sea válido
function grupo_valido($con, $id_grupo){
    stmt = mysqli_prepare ($con, "SELECT 1 FROM grupo WHERE id_grupo = ?"); //significa que solo confirma que existe ese dato en la BD, no envía datos como tal
    mysqli_stmt_bind_param($stmt, "i", $id_grupo);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $id_grupo);

    return (bool) mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

}
//validar que el tipo de usuario sea válido
function usuario_valido($con, $tipo_usuario){
    stmt = mysqli_prepare ($con, "SELECT 1 FROM tipo_usuario WHERE rol = ?");
    mysqli_stmt_bind_param($stmt, "i", $rol);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $rol);

    return (bool) mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

//validar que el truno sea válido
function turno_valido($con, $id_turno){
    stmt = mysqli_prepare ($con, "SELECT 1 FROM turno WHERE id_turno = ?");
    mysqli_stmt_bind_param($stmt, "i", $id_turno);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $id_turno);

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



function sanitizar_entrada($conexion, $datos) {

    // Quitamos espacios en blanco vacíos al inicio y al final
    $datos = trim($datos);
    $datos = stripslashes($datos);

    // Si meten "--", lo cambiamos por "".
    $datos = str_replace('--', '', $datos);

    // Si meten "/*", lo cambiamos por "".
    $datos = str_replace('/*', '', $datos);
    
    // Si meten "*/", lo cambiamos por "".
    $datos = str_replace('*/', '', $datos);

    // Límite de tamaño (Protección contra textos gigantes)
    // Corta el texto a un máximo de 50 caracteres para no saturar la BD
    $datos = substr($datos, 0, 50);

    // Busca comillas simples (') o dobles (") y les pone una diagonal inversa (\) antes.
    // Así la base de datos sabe que es parte del nombre y NO un comando SQL.
    $datosLimpio = mysqli_real_escape_string($con, $datos);
    
    return $datosLimpio;
}

function validar_correo($email){

    if (filter_var($email, FILTER_VALIDATE_EMAIL)){
        return true;
    }  
    else{
        return false;
    }       
}

function validar_correo_rol ($correo, $rol){
    if (!validar_correo($correo)){
        return false;
    }

    if ($rol == 'Alumno'){
        return (bool) preg_match('/^[0-9]+@alumno\.enp\.unam\.mx$/', $correo);
    }

    return (bool) preg_match('/^[a-zA-Z]+(\.[a-zA-Z]+)+@enp\.unam\.mx$/', $correo);
}

function validar_rango ($valor, $min, $max){
    $valor = (int) $valor;
    return $valor >= $min && $valor <= $max;
}
//se usan patrones regex para ir separando en base a los marcadores
function validar_fecha($fecha){ 
    if (empty($fecha)) return false;
    if (!preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$/', $fecha)) return false;
    //estos son los marcadores
    [$fecha_parte, $hora_parte] = explode('T', $fecha);
    [$anio, $mes, $dia]          = explode('-', $fecha_parte);
    [$hora, $minuto]             = explode(':', $hora_parte);
    return checkdate((int)$mes, (int)$dia, (int)$anio)
    //aqui checa que los numeros tengan sentido, por ejemplo no se puede escribir 78 en horas
        && (int)$hora >= 0 && (int)$hora <= 23
        && (int)$minuto >= 0 && (int)$minuto <= 59;
}

function hashear_password($pass){
    $password_hasheada = password_hash($pass, PASSWORD_DEFAULT);

    return $password_hasheada;
}
?>