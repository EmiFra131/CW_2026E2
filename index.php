<?php

include './include/db.php';
include './include/validacion.php';

session_start();

if (isset($_POST["correo"])){

    $con = connect();

    $usuario = trim($_POST["correo"]); // Quitamos espacios al inicio y al final del string recibido por formulario
    $password = trim($_POST["password"]);

    $query = "SELECT c.id_cuenta, c.correo, c.nombre, c.contraseña t.rol FROM cuenta c 
    INNER JOIN tipo_usuario t ON c.id_tipo_usuario = t.id_tipo_usuario
    WHERE c.correo = '$usuario'"; 
    
    $result = mysqli_query( $con, $query);
    $registro = mysqli_fetch_assoc($result);
    //echo $query;
    //var_dump($registro);

    /*             

    if ($registro){
        $hash_base_de_datos = $registro["contraseña"];
        password_verify($password, $hash_base_de_datos) // Verificamos que coincidan usu y pass
        $_SESSION['usuario'] = $registro["nocta"];
        $_SESSION["rol"] = "usuario";
        $_SESSION["nombre_completo"] = $registro["nombre"] . " " . $registro["appat"] . " " . $registro["apmat"];
        setcookie("usuario", $registro["nocta"], time() + (86400)); // 1 dia = 86400 segundos, expirará en un dia
        header("Location: usuario.php");
    } else {
        $error = "No coinciden usuario o contraseña";
    }
    */
/*
} else {
    // Verificamos que tenga la cookie
    if (isset($_COOKIE["usuario"])){
        $usuario = $_COOKIE["usuario"];

        $query = "SELECT nocta, nombre, appat, apmat, password FROM usuarios WHERE nocta = $usuario";
        $result = mysqli_query( $con, $query);
        $registro = mysqli_fetch_assoc($result);

        $_SESSION['usuario'] = $registro["nocta"];
        $_SESSION["rol"] = "usuario";
        $_SESSION["nombre_completo"] = $registro["nombre"] . " " . $registro["appat"] . " " . $registro["apmat"];
        setcookie("usuario", $registro["nocta"], time() + (86400)); // 1 dia = 86400 segundos, expirará en un dia
        header("Location: usuario.php");

    }
}
*/
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
            <input type="text" placeholder="correo" minlength="9" maxlength="9" name = "correo" required>
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