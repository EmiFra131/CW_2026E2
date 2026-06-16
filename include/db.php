<?php
    const DBHOST = "localhost";
    const DBUSER = "satec";
    const PASSWORD = "satec2026";
    const DB = "satec";

const ROL_ALUMNO = 1;
const ROL_PROFESOR = 2;
const ROL_ADMINISTRADOR = 3;

    function connect()
    {
        $conexion = mysqli_connect(DBHOST, DBUSER,PASSWORD,DB);
        //var_dump($conexion);
        return $conexion;
    }

    // El ciclo activo es el que de verdad tiene grupos creados, no simplemente
    // el último registro de ciclo_escolar (esa tabla puede tener ciclos futuros
    // precargados todavía sin grupos).
    function ciclo_activo($con)
    {
        $r = mysqli_query($con, "SELECT MAX(id_ciclo) as id_ciclo FROM grupo");
        return (int) (mysqli_fetch_assoc($r)['id_ciclo'] ?? 0);
    }

    $con = connect();
?>