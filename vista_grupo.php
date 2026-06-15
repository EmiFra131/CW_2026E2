<?php
session_start();
include './include/db.php';

/*
if (!isset($_SESSION["usuario"]) || $_SESSION["rol"] != "Profesor") {
    header("Location: index.php");
    exit();
}
*/

$con = connect();
$id_grupo = $_GET["id"];

$query_grupo = "SELECT g.nombre_grupo, t.turno
                FROM grupo g
                INNER JOIN turno t ON g.id_turno = t.id_turno
                WHERE g.id_grupo = $id_grupo";
$result_grupo = mysqli_query($con, $query_grupo);
$grupo = mysqli_fetch_assoc($result_grupo);

$query_alumnos = "SELECT c.id_cuenta, c.nombre, c.correo
                  FROM ciclo_cuenta cc
                  INNER JOIN cuenta c ON cc.id_cuenta = c.id_cuenta
                  INNER JOIN tipo_usuario t ON c.id_tipo_usuario = t.id_tipo_usuario
                  WHERE cc.id_grupo = $id_grupo AND t.rol = 'Alumno'";
$result_alumnos = mysqli_query($con, $query_alumnos);
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
    PROFESOR > GRUPO <?php echo $grupo["nombre_grupo"]; ?>
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
        while ($alumno = mysqli_fetch_assoc($result_alumnos)) {
            echo "<tr class='fila_alumno'>";
            echo "<td class='col_icono'><div class='avatar'>👤</div></td>";
            echo "<td>" . $alumno["nombre"] . "</td>";
            echo "<td>" . $grupo["nombre_grupo"] . "</td>";
            echo "<td>" . $alumno["correo"] . "</td>";
            echo "<td><a href='alumno_tareas.php?id=" . $alumno["id_cuenta"] . "' class='enlace'>Ver más</a></td>";
            echo "</tr>";
        }
        ?>
    </table>
</div>

<div class="pie_pagina">
    Total de alumnos: <?php echo mysqli_num_rows($result_alumnos); ?>
</div>

</body>
</html>