<?php
    include 'include/db.php';
    include 'include/validacion.php';

    $con = connect();

    $seleccion = "";
    $crear = false;
    $editar = false;
    $borrar = false;

    if($_SERVER["REQUEST_METHOD"] == "POST"){

        if(isset($_POST["nuevo"]))
            $seleccion = true;
        else
            $seleccion =false;
        
        if(isset($_POST['grupo_act'])){
            $grupo_temp = $_POST['grupo_act'];
            setcookie("grupo", $grupo_temp, time() + 86400);
        }

        if(isset($_COOKIE["grupo"]))
            $grupo = $_COOKIE["grupo"];

        if(isset($_POST["crear"])){
            $crear = true;
        }

        if(isset($_POST["editar"])){
            $editar = true;
        }

        if(isset($_POST["borrar"])){
            $borrar = true;
        }

    }

    $alumnos_query = 
    "SELECT cc.id_ciclo_cuenta, c.nombre, t.rol, g.nombre_grupo, ce.periodo
    FROM ciclo_cuenta cc
    INNER JOIN cuenta        c  ON cc.id_cuenta = c.id_cuenta
    INNER JOIN tipo_usuario  t  ON c.id_tipo_usuario = t.id_tipo_usuario
    INNER JOIN grupo         g  ON cc.id_grupo = g.id_grupo
    INNER JOIN ciclo_escolar ce ON cc.id_ciclo = ce.id_ciclo
    WHERE g.nombre_grupo = '$grupo' AND t.rol = 'alumno'";
    
    $query_al = mysqli_query($con, $alumnos_query);

    if($query_al){
        $id = [];
        $nombres = [];
        $roles = [];
        $nombres_grupo = [];
        $periodos = [];

        $alumnos = [];

        while($fila = mysqli_fetch_assoc($query_al)){

            var_dump($fila);
            $id[] = $fila['id_ciclo_cuenta'];
            $nombres[] = $fila['nombre'];
            $roles[] = $fila['rol'];
            $nombres_grupos[] = $fila['nombre_grupo'];
            $periodos[] = $fila['periodo'];

            $alumnos[] = $fila;
        }
    }

    $profesor_query = 
    "SELECT cc.id_ciclo_cuenta, c.nombre, t.rol, g.nombre_grupo, g.id_turno, ce.periodo
    FROM ciclo_cuenta cc
    INNER JOIN cuenta        c  ON cc.id_cuenta = c.id_cuenta
    INNER JOIN tipo_usuario  t  ON c.id_tipo_usuario = t.id_tipo_usuario
    INNER JOIN grupo         g  ON cc.id_grupo = g.id_grupo
    INNER JOIN ciclo_escolar ce ON cc.id_ciclo = ce.id_ciclo
    WHERE g.nombre_grupo = '$grupo' AND t.rol = 'profesor'";

    $query_prof = mysqli_query($con, $profesor_query);

    $resultado = mysqli_fetch_assoc($query_prof);

    if($resultado !=null){
        $id_profe = $resultado['id_ciclo_cuenta'];
        $nombre_profe = $resultado['nombre'];
        $roles_profe = $resultado['rol'];
        $nombres_grupos_profe = $resultado['nombre_grupo'];
        $periodos_profe = $resultado['periodo'];

        echo "consulta exitosa";
    }
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
                echo "<h1>Administrador > Grupo $grupo</h1>"
            ?>
        </header>

        <div class="contenido_principal">

            <div class="tarjeta_profesor">
                <?php
                if($resultado != null){
                    echo 
                    "<img src='../static/img/icono_usuario.png' alt= 'usuario'>
                    <h2>".$nombre_profe."</h2>
                    <p>Horario: Lunes-Viernes 12:00 a 13:40</p>
                    <p>Salón: LACEC</p>";
                }
                ?>
            </div>

            <div class="seccion_alumno">
                <?php
                    if(!$crear&&!$editar&&!$borrar){
                        foreach($nombres as $nombre){
                            echo "<div class=fila_alumno>
                                <span>'$nombre'</span>
                                <span>$grupo</span>
                            </div>";
                        }
                    }
                    if($editar){
                       foreach($alumnos as $alumno){
                            echo "<div class=fila_alumno>
                                <span>".$alumno['id_ciclo_cuenta']."</span>
                                <span>".$alumno['nombre']."</span>
                                <form action='admin_visual.php' method='post'>
                                    <input type= hidden name= us_editar value= ".$alumno['id_ciclo_cuenta'].">
                                    <button type= submit >➕ editar</button>
                                </form>
                            </div>";
                        } 
                    }
                ?>
                <div class="boton_agregar">
                    <?php
                        if($seleccion){
                           echo "<form action='admin_visual.php' method='post'>
                                <input type= hidden name= crear>
                                <button type= submit >➕ Crear cuenta</button>
                            </form>";
                            echo "<form action='admin_visual.php' method='post'>
                                <input type= hidden name= editar>
                                <button type= submit >➕ Editar Cuenta</button>
                            </form>"; 
                            echo "<form action='admin_visual.php' method='post'>
                                <input type= hidden name= Borrar>
                                <button type= submit >➕ Eliminar cuenta</button>
                            </form>";
                        }
                        else
                            echo "<form action='admin_visual.php' method='post'>
                                <input type= hidden name= nuevo>
                                <button type= submit >➕ Agregar miembros</button>
                            </form>"
                            
                    ?>
                </div>
            </div>

        </div>
    </body>
</html>