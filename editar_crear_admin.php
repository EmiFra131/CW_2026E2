<?php
    include 'include/db.php';
    include 'include/validacion.php';

    /*
    if (!isset($_SESSION["usuario"]) || $_SESSION["rol"] != "Admin") {
        header("Location: index.php");
        exit();
    }
    */

    if($_SERVER["REQUEST_METHOD"] == "POST"){
        if(isset($_POST["crear"]))
            $editar = false;
        else{
            $editar = true;
            $usuario = $_POST["us_editar"];
        }

    }

    if(isset($_COOKIE["grupo"]))
        $grupo = $_COOKIE["grupo"];

?>

<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Admin Visual</title>
        <link rel="stylesheet" href="statics/styles/admin_style.css">
    </head>
    <body>
        <nav>
            <div class="logos_izquierda">
                <img src="../static/img/logo_unam.png" alt="UNAM">
                <img src="../static/img/logo_p6.png" alt="ENP6">
            </div>
            <div class="logo_derecha">
                <img src="../static/img/logo_ete.png" alt="ETE">
            </div>
        </nav>

        <header>
            <?php
                echo "<h1>Administrador Grupo " . htmlspecialchars($grupo) . "</h1>";
            ?>
        </header>

        <div class="contenido_principal">
            <?php
                if($editar){
                    echo"
                    <form action= admin_visual.php method= post>
                        <div>
                            <label for=correo>Cambiar el correo relacioado a esta cuenta?</label>
                            <input id=correo name=correo type=text placeholder= >
                        </div>
                        <div>
                            <label for=usuario>Cambiar el nombre relacionado a esta cuenta?</label>
                            <input id=usuario name=usuario type=text placeholder= >
                        </div>
                        <div>
                            <label for=grupo>Cambiar de grupo la cuenta asignada?</label>
                            <select name=grupo id=grupo >
                                <option value= disabled selected>Escoge el grupo en el que estas inscrito</option>
                                <option value=61-A>61-A</option>
                                <option value=61-B>61-B</option>
                                <option value=61-c>61-C</option>
                                <option value=61-D>61-D</option>
                                <option value=62-A>62-A</option>
                                <option value=62-B>62-B</option>
                                <option value=62-c>62-C</option>
                            </select>
                        </div>
                        <div>
                            <label for=tipo_us>Cambiar el tipo de usuario de esta cuenta?</label>
                            <select name=tipo_us id=tipo_us >
                                <option value= disabled selected>Cual es tu rol en el ETE?</option>
                                <option value=alumno>alumno</option>
                                <option value=profesor>profesor</option>
                                <option value=admin>administrador</option>
                            </select>
                        </div>
                        <div>
                            <label for=password>Deseas cambiar la contraseña del usuario?:</label>
                            <input id=password name=password type=password placeholder=>
                        </div>
                        <input type= hidden name= usuario_id value= '$usuario'>
                        <div>
                            <input type=submit value=crear cuenta>
                        </div>
                    </form>";
                }
                else{
                    echo"
                    <form action=admin_visual.php method= post>
                        <div>
                            <label for=correo>Correo electronico</label>
                            <input id=correo name=correo type=text placeholder= required>
                        </div>
                        <div>
                            <label for=usuario>Nombre del usuario</label>
                            <input id=usuario name=usuario type=text placeholder= required>
                        </div>
                        <div>
                            <label for=grupo>Grupo</label>
                            <select name=grupo id=grupo required>
                                <option value= disabled selected>Escoge el grupo en el que estas inscrito</option>
                                <option value=61-A>61-A</option>
                                <option value=61-B>61-B</option>
                                <option value=61-c>61-C</option>
                                <option value=61-D>61-D</option>
                                <option value=62-A>62-A</option>
                                <option value=62-B>62-B</option>
                                <option value=62-c>62-C</option>
                            </select>
                        </div>
                        <div>
                            <label for=tipo_us>tipo de usuario</label>
                            <select name=tipo_us id=tipo_us required>
                                <option value= disabled selected>Cual es tu rol en el ETE?</option>
                                <option value=alumno>alumno</option>
                                <option value=profesor>profesor</option>
                                <option value=admin>administrador</option>
                            </select>
                        </div>
                        <div>
                            <label for=password>Deseas cambiar la contraseña del usuario?:</label>
                            <input id=password name=password type=password placeholder= required>
                        </div>
                        <div>
                            <input type=submit value=crear cuenta>
                        </div>
                    </form>";
                }
            ?>
            
        </div>
    </body>
</html>