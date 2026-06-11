<?php
    include 'include/db.php';
    include 'include/validacion.php';

    $con = connect();

    if($_SERVER["REQUEST_METHOD"] == "POST"){

        $grupo = $_POST['grupo_act'];

    }

    $alumnos_query = 
    "SELECT cc.id_ciclo_cuenta, c.nombre, t.rol, g.nombre_grupo, g.id_turno, ce.periodo
    FROM ciclo_cuenta cc
    INNER JOIN cuenta        c  ON cc.id_cuenta = c.id_cuenta
    INNER JOIN tipo_usuario  t  ON c.id_tipo_usuario = t.id_tipo_usuario
    INNER JOIN grupo         g  ON cc.id_grupo = g.id_grupo
    INNER JOIN ciclo_escolar ce ON cc.id_ciclo = ce.id_ciclo
    WHERE g.nombre_grupo = '$grupo'";
    
    $query = mysqli_query($con, $alumnos_query);

    if($query){
        $id = [];
        $nombres = [];
        $roles = [];
        $nombres_grupo = [];
        $turnos = [];
        $periodos = [];

        while($fila = mysqli_fetch_assoc($query)){
            $id[] = $fila['id_ciclo_cuenta'];
            $nombres[] = $fila['nombre'];
            $roles[] = $fila['rol'];
            $nombres_grupos[] = $fila['nombre_grupo'];
            $turnos[] = $fila['turno'];
            $periodos[] = $fila['periodo'];
        }
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
                <img src="../static/img/icono_usuario.png" alt="usuario">
                <h2>Nombre del profesor</h2>
                <p>Horario: Lunes-Viernes 12:00 a 13:40</p>
                <p>Salón: LACEC</p>
            </div>

            <div class="seccion_alumno">
                <?php
                    foreach($nombres as $nombre){
                        echo "<div class=fila_alumno>
                            <span>'$nombre'</span>
                            <span>$grupo</span>
                            <span>🗑️</span>
                        </div>";
                    }
                ?>
                <div class="boton_agregar">
                    <span>➕ Agregar miembros</span>
                </div>
            </div>

        </div>
    </body>
</html>