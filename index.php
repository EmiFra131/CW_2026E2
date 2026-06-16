<?php

session_start();

include './include/db.php';
include './include/validacion.php';

if (isset($_POST["correo"])){

    $con = connect();

    $correo = trim($_POST["correo"]); // Quitamos espacios al inicio y al final del string recibido por formulario
    $contraseña = trim($_POST["password"]);

    $stmt = mysqli_prepare($con, "SELECT c.id_cuenta, c.correo, c.nombre, c.contraseña, t.rol FROM cuenta c 
    INNER JOIN tipo_usuario t ON c.id_tipo_usuario = t.id_tipo_usuario
    WHERE c.correo = ?"); 
    mysqli_stmt_bind_param($stmt, 's', $correo);
    mysql_stmt_execute($stmt);
    $result = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt););

    if ($result){
        $hash_base_de_datos = $result["contraseña"];
        if(password_verify($contraseña, $hash_base_de_datos)){
            $_SESSION['id'] = $result["id_cuenta"];
            $_SESSION["nombre"] = $result["nombre"];
            $_SESSION["rol"] = $result["rol"];
            $_SESSION["correo"] = $result["correo"];
            set_cookie("usuario", $result["correo"], time()+(86400));
        exit();
        }
    } else {
        $error = "No coinciden usuario o contraseña";
    }

} else {
    
        $error = "No coincide el formulario"
}
        
    

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión | SATEC</title>
    <link rel="stylesheet" href="statics/styles/index.css">
</head>
<body>
    <header>
        <img src="statics/img/logos/unam.jpg" class="logo" id="logo_unam">
        <img src="statics/img/logos/logo_p6.png" class="logo" id="logo_p6">
        <img src="statics/img/logos/ete.png" class="logo" id="logo_ete">
    </header>
    <main id="contenedor_inicio">
        <h1>INICIAR SESIÓN</h1>
        <img src="./statics/img/iconos/usuario.png" alt="Icono de perfil de usuario" id="icono_perfil">
        <?php if(!empty($error)):?>
            <p class="error"><?= htmlspecialchars($error)?></p>
        <?php endif;?>
        <form id="formulario_inicio" method="post" action="index.php">
            <label for="correo">Correo:</label>
            <input type="text"  name = "correo" id="correo" required>
            <span></span> <!--si el usuario ingresa mal los datos aquí le aparecerá el aviso-->
            <label for="contraseña">CONTRASEÑA:</label>
            <input type="password" placeholder="********" name = "contraseña" id="contraseña" required>
            <input type="submit" value="Iniciar sesión">
        </form>
    </main>
    <footer>
        <p>Si no tienes una cuenta,
            <form action="control/cuentas/crear.php">
                <button type="submit"> crea una </button>
            </form>
        </p>
    </footer>
</body>
</html>