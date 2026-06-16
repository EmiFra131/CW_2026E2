<?php
session_start();
include './include/db.php';
include './include/validacion.php';

if (isset($_GET["cerrar"])) {
    session_destroy();
    header("Location: /index.php");
    exit();
}

if (isset($_POST["correo"])) {

    $con = connect();

    $correo     = trim($_POST["correo"]);
    $contrasena = trim($_POST["contrasena"]);

    $stmt = mysqli_prepare($con, "SELECT c.id_cuenta, c.nombre, c.contrasena, t.id_tipo_usuario, t.rol
        FROM cuenta c
        INNER JOIN tipo_usuario t ON c.id_tipo_usuario = t.id_tipo_usuario
        WHERE c.correo = ?");
    mysqli_stmt_bind_param($stmt, "s", $correo);
    mysqli_stmt_execute($stmt);
    $result   = mysqli_stmt_get_result($stmt);
    $registro = mysqli_fetch_assoc($result);

    if ($registro) {
        if (password_verify($contrasena, $registro["contrasena"])) {
            $_SESSION["id_cuenta"]       = $registro["id_cuenta"];
            $_SESSION["id_tipo_usuario"] = (int) $registro["id_tipo_usuario"];
            $_SESSION["rol"]             = $registro["rol"];
            $_SESSION["nombre"]          = $registro["nombre"];
            header("Location: /dashboard/index.php");
            exit();
        } else {
            $error = "Contraseña incorrecta";
        }
    } else {
        $error = "No se encontró el correo";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SATEC — Iniciar sesión</title>
    <link rel="stylesheet" href="/statics/styles/style.css">
</head>
<body>

<nav>
    <img src="/statics/img/logos/unam.png" alt="UNAM">
    <img src="/statics/img/logos/enp.svg" alt="ENP">
    <img src="/statics/img/logos/ete.png" alt="ETE">
</nav>

<div id="contenido">
    <h1>Iniciar sesión</h1>

    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>

    <form action="index.php" method="post">
        <div>
            <label for="correo">Correo:</label>
            <input type="email" id="correo" name="correo" required>
        </div>
        <div>
            <label for="contrasena">Contraseña:</label>
            <input type="password" id="contrasena" name="contrasena" required>
        </div>
        <div>
            <button type="submit">Entrar</button>
        </div>
    </form>

    <p><a href="/control/cuentas/crear.php">¿No tienes cuenta? Regístrate aquí</a></p>
</div>

</body>
</html>
