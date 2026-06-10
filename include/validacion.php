<?php

/*
function esOpcionValida($genero){
    // Opciones permitidas en el SELECT de Género
    $generosPermitidos = ['Masculino', 'Femenino', 'Otro'];

    // Si lo que mandaron NO está en nuestra lista secreta de PHP, regresamos falso
    if (!in_array($genero, $generosPermitidos)) 
        return false;

    return true;
}
*/

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

    if (filter_var($email, FILTER_VALIDATE_EMAIL)) 
        echo "El correo '$email' es válido.\n";
}

function validar_numero($edad){

    if(filter_var($edad, FILTER_SANITIZE_NUMBER_INT))
        echo "La edad '$email' es válida.\n"; 

}


function hashear_password($pass){

    //Generamos el hash
    $password_hasheada = password_hash($pass, PASSWORD_DEFAULT);

    return $password_hasheada;
}


function validar_password($passLogin){

    // Traemos el hash que está guardado en la Base de Datos para ese usuario
    // (Imaginemos que ya hicimos el SELECT y lo guardamos en esta variable)
    $hash_base_de_datos = '$2y$10$abcdefghijklmnopqrstuvwxyz1234567890...'; 

    // Compara la contraseña limpia con el hash
    if (password_verify($passLogin, $hashDeLaBaseDeDatos)) 
        echo "¡Contraseña correcta! Bienvenido al sistema.";        
    else 
        echo "Contraseña incorrecta. Inténtalo de nuevo.";
    
}

?>


    