<?php
session_start();
include './include/db.php';

if (!isset($_SESSION["id_cuenta"]) || $_SESSION["rol"] != "Profesor") {
    header("Location: index.php");
    exit();
}

$con = connect();

$query = "SELECT g.id_grupo, g.nombre_grupo, t.turno, ce.periodo
          FROM grupo g
          INNER JOIN turno t ON g.id_turno = t.id_turno
          INNER JOIN ciclo_escolar ce ON g.id_ciclo = ce.id_ciclo";
$result = mysqli_query($con, $query);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Portal del Profesor</title>
    <link rel="stylesheet" href="./statics/styles/pantallaprofevista.css">
</head>
<body>

<div class="encabezado">
    <div class="logos_izquierda">
        <span class="logo">Logo UNAM</span>
        <span class="logo">Logo Prepa</span>
    </div>
    <div class="logo_derecha">
        <span class="logo">Logo ETE</span>
    </div>
</div>

<div class="barra_azul">
    PROFESOR > GRUPOS
</div>

<div class="menu_lateral">
    <button class="boton_crear" onclick="location.href='profesores-tareas.php'">
        <span class="mas">+</span> CREAR TAREAS/<br>ACTIVIDADES
    </button>
    <br><br><br>
    <button class="boton_naranja">Ver gráfica</button>
    <button class="boton_naranja">Ver perfil</button>
    <button class="boton_naranja">Configuración</button>
</div>

<div class="contenido">
    <div class="icono_flotante">
        <?php echo strtoupper(substr($_SESSION["nombre"], 0, 1)); ?>
    </div>
    <br><br>
    <table class="tabla_alumnos">
        <?php
        while ($grupo = mysqli_fetch_assoc($result)) {
            echo "<tr class='fila_alumno'>";
            echo "<td class='col_icono'><div class='avatar'>👤</div></td>";
            echo "<td>" . htmlspecialchars($grupo["nombre_grupo"]) . "</td>";
            echo "<td>" . htmlspecialchars($grupo["turno"]) . "</td>";
            echo "<td>" . htmlspecialchars($grupo["periodo"]) . "</td>";
            echo "<td><a href='vista_grupo.php?id=" . htmlspecialchars($grupo["id_grupo"]) . "' class='enlace'>Ver más</a></td>";
            echo "</tr>";
        }
        ?>
    </table>
</div>

<div class="pie_pagina">
    Total de grupos: <?php echo mysqli_num_rows($result); ?>
</div>

</body>
</html>