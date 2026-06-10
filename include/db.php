<?php    
    const DBHOST = "localhost";
    const DBUSER = "root";
    const PASSWORD = "";
    const DB = "satec";

    function connect()
    {
        $conexion = mysqli_connect(DBHOST, DBUSER,PASSWORD,DB);
        //var_dump($conexion);
        return $conexion;
    }

    $con = connect();
?>