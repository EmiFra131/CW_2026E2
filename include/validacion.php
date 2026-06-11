<?php


function grupo_valido($grupo){  //Cambiar con una peticion sql para mantener los grpos actualizados con el servidor
    // Opciones permitidas en el SELECT de Género
    $grupos = ['61-A', '61-B', '61-C', '61-D', '62-A', '62-B', '62-C' ];

    // Si lo que mandaron NO está en nuestra lista secreta de PHP, regresamos falso
    if (!in_array($grupo, $grupos) )
        return false;

    return true;
}

function usuario_valido($user){
    // Opciones permitidas en el SELECT de Género
    $usuarios = ['alumno','profesor','admin'];

    // Si lo que mandaron NO está en nuestra lista secreta de PHP, regresamos falso
    if (!in_array($user, $usuarios) )
        return false;

    return true;
}

function turno_valido($turno){
    // Opciones permitidas en el SELECT de Género
    $turnos = ['matutino', 'vespertino'];

    // Si lo que mandaron NO está en nuestra lista secreta de PHP, regresamos falso
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


    