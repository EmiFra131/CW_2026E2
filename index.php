<?php

session_start();

include './include/db.php';
include './include/validacion.php';



if (isset($_POST["correo"])){

    $con = connect();

    $correo = trim($_POST["correo"]); // Quitamos espacios al inicio y al final del string recibido por formulario
    $contraseña = trim($_POST["password"]);

    $query = "SELECT c.id_cuenta, c.correo, c.nombre, c.contraseña, t.rol FROM cuenta c 
    INNER JOIN tipo_usuario t ON c.id_tipo_usuario = t.id_tipo_usuario
    WHERE c.correo = '$correo'"; 
    
    $result = mysqli_query( $con, $query);
    $registro = mysqli_fetch_assoc($result);
    //echo $query;
    //var_dump($registro);

           

    if ($registro){
        $hash_base_de_datos = $registro["contraseña"];
        if(password_verify($contraseña, $hash_base_de_datos)){
            $_SESSION['usuario'] = $registro["correo"];
            $_SESSION["rol"] = $registro["rol"];
            $_SESSION["nombre_completo"] = $registro["nombre"];
            setcookie("usuario", $registro["correo"], time() + (86400)); // 1 dia = 86400 segundos, expirará en un dia
        header("Location: usuario.php");
        exit();
        }
    } else {
        $error = "No coinciden usuario o contraseña";
    }

} else {
    // Verificamos que tenga la cookie
    if (isset($_COOKIE["usuario"])){
        $con = connect();
        $usuario = $_COOKIE["usuario"];

        $query = "SELECT c.id_cuenta, c.correo, c.nombre, c.contraseña, t.rol FROM cuenta c 
        INNER JOIN tipo_usuario t ON c.id_tipo_usuario = t.id_tipo_usuario
        WHERE c.correo = '$usuario'"; 
        $result = mysqli_query( $con, $query);
        $registro = mysqli_fetch_assoc($result);

        if ($registro){
    $hash_base_de_datos = $registro["contraseña"];
    if(password_verify($contraseña, $hash_base_de_datos)){
        $_SESSION['usuario'] = $registro["id_cuenta"]; 
        $_SESSION["rol"] = $registro["rol"];
        $_SESSION["nombre"] = $registro["nombre"];
        setcookie("usuario", $registro["correo"], time() + (86400));

        // Redirigir según el rol
        if ($registro["rol"] == "Profesor") {
            header("Location: profesores-vista.php");
            exit();
        }
    }
}
        
        
        /*$_SESSION['usuario'] = $registro["correo"];
        $_SESSION["rol"] = $registro["rol"];
        $_SESSION["nombre_completo"] = $registro["nombre"];
        setcookie("usuario", $registro["correo"], time() + (86400)); // 1 dia = 86400 segundos, expirará en un dia
        header("Location: templates/alumno_tareas.html");
*/
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="./statics/styles/index.css">
</head>
<body>
    <header>
        <img src="./statics/img/logo_unam.svg" alt="Logo de la UNAM" class="logo" id="logo_unam">
        <img src="./statics/img/logo_p6.png" alt="Logo de la ENP 6"  class="logo" id="logo_p6">
        <img src="./statics/img/logo_ete.png" alt="Logo de los ETE" class="logo" id="logo_ete">
    </header>
    <main id="contenedor_inicio">
        <h1>INICIAR SESIÓN</h1>
        <img src="./statics/img/usuario.png" alt="Icono de perfil de usuario" id="icono_perfil">
        <form id="formulario_inicio" method="post" action="index.php">
            <label>USUARIO:</label>
            <input type="text" placeholder="correo" name = "correo" required>
            <span></span> <!--si el usuario ingresa mal los datos aquí le aparecerá el aviso-->
            <label>CONTRASEÑA:</label>
            <input type="password" placeholder="contrasena" name = "password" required>
            <input type="submit" value="Iniciar sesión">
        </form>
    </main>
    <footer>
        <p>Si no tienes una cuenta,
            <form action="crear_cuenta.php">
                <button type="submit"> crea una </button>
            </form>
        </p>
    </footer>
</body>
</html>