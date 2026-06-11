 <?php

    include 'include/db.php';
    include 'include/validacion.php';

    $con = connect();

    $gruposquery = "SELECT g.id_grupo, g.nombre_grupo, t.turno, ce.periodo
    FROM grupo g
    INNER JOIN turno t ON g.id_turno = t.id_turno
    INNER JOIN ciclo_escolar ce ON g.id_ciclo = ce.id_ciclo";

    $query = mysqli_query($con, $gruposquery);

    if($query){

        $id = [];
        $grupos = [];
        $turno = [];
        $periodo = [];
        while($fila = mysqli_fetch_assoc($query)){
            $id[] = $fila['id_grupo'];
            $grupos[] = $fila['nombre_grupo'];
            $turno[] = $fila['turno'];
            $periodo[] = $fila['periodo'];
        }
        
    }
    else{
        echo "NADA";
    }
 ?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>admin_grupos</title>
    <link rel="stylesheet" href="statics/styles/admin_grupos.css">
</head>
<body>
    <nav>
        <img src="../statics/img/unam.png" alt="Universidad Nacional Autonoma De Mexico" id="unam">
        <img src="../statics/img/enp.svg" alt="Escuela Nacional Preparatoria 6">
        <img src="../statics/img/ete.png" alt="Estudios Tecnicos Especializados" id="ete">
    </nav>
    <div id="user">
        <p><strong>Administrador</strong></p>
    </div>
    <div id="contenedor">

        <?php
            echo '<div class="grupo">
                <h3>Grupo 61 A</h3>
                <button class="boton"> Ver integrantes </button>
                <br>
                <button class="boton"> Contactar Profesor </button>
            </div>'
        ?>
        <div class="grupo">
            <h3>Grupo 61 A</h3>
            <button class="boton"> Ver integrantes </button>
            <br>
            <button class="boton"> Contactar Profesor </button>
        </div>
        <div class="grupo">
            <h3>Grupo 61 A</h3>
            <button class="boton"> Ver integrantes </button>
            <br>
            <button class="boton"> Contactar Profesor </button>
        </div>
        <div class="grupo">
            <h3>Grupo 61 A</h3>
            <button class="boton"> Ver integrantes </button>
            <br>
            <button class="boton"> Contactar Profesor </button>
        </div>
    </div>
</body>
</html>