<?php

//validar que el grupo sea válido
function grupo_valido($con, $grupo){
    $obtener_grupos= "SELECT nombre_grupo FROM grupo";
    $sql_grupos = mysqli_query($con, $obtener_grupos);
    $grupos = [];
    while($resultado = mysqli_fetch_assoc($sql_grupos))
    {
        $grupos[]=$resultado['nombre_grupo'];
    }
    if (!in_array($grupo, $grupos) )
        return false;

    return true;

}
//validar que el tipo de usuario sea válido
function usuario_valido($con, $tipo_usuario){
    $obtener_tipos_usuario= "SELECT rol FROM tipo_usuario";
    $sql_tipos_usuario = mysqli_query($con, $obtener_tipos_usuario);
    $tipos_usuario = [];
    while($resultado = mysqli_fetch_assoc($sql_tipos_usuario))
    {
        $tipos_usuario[]=$resultado['rol'];
    }
    if (!in_array($tipo_usuario, $tipos_usuario) )
        return false;

    return true;
}

//validar que el truno sea válido
function turno_valido($con, $turno){
    $obtener_turnos= "SELECT turno FROM turno";
    $sql_turnos = mysqli_query($con, $obtener_turnos);
    $turnos = [];
    while($resultado = mysqli_fetch_assoc($sql_turnos))
    {
        $turnos[]=$resultado['turno'];
    }
    if (!in_array($turno, $turnos) )
        return false;

    return true;
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
    $datosLimpio = mysqli_real_escape_string($conexion, $datos);
    
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

function validar_numero($numero){

    if(filter_var($numero, FILTER_SANITIZE_NUMBER_INT))
        echo "La edad '$numero' es válida.\n"; 

}

function hashear_password($pass){

    //Generamos el hash
    $password_hasheada = password_hash($pass, PASSWORD_DEFAULT);

    return $password_hasheada;
}

/*
function validar_password($pass_login, $hash_base_de_datos){
    password_verify($passLogin, $hash_base_de_datos)
}
*/
?>