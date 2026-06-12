<?php
    include 'include/db.php';
    include 'include/validacion.php';

    if($_SERVER["REQUEST_METHOD"] == "POST"){
        
        $con = connect();
        $nombre  =$_POST["usuario"];
        $correo = $_POST["correo"];
        $contrasena = $_POST["password"];
        $grupo = $_POST['grupo'];
        $user = $_POST['tipo_us'];

        //grupo_valido($grupo);
        //usuario_valido($user);
        if(!grupo_valido($grupo) || !usuario_valido($user)){    //verificacion de datos :p//
            echo "Datos invalidos";
            exit();
        }
        $nombre_s = sanitizar_entrada($con,$nombre);
        $correo_s = sanitizar_entrada($con,$correo);
        $correo_valido = validar_correo($correo_s);
        $contrasena_valida = validacion_contrasena($contrasena);

        $hash = hashear_password($contrasena);

        if($contrasena_valida == true && $correo_valido == true){
            $id = null;
            if($user == "alumno")
                $id = 1;
            if($user == "profesor")
                $id = 2;
            if($user == "admin")
                $id = 3;

            if($id == null){
                echo "Rol invalido";
                exit();
            }


            
            $cuenta_nueva = "INSERT INTO cuenta(id_cuenta, correo, nombre, contraseña, id_tipo_usuario)
            VALUES (UUID(), '$correo_s','$nombre_s', '$hash' ,$id)";

            $query = mysqli_query($con, $cuenta_nueva);
            if($query){

                $obtener_id = "SELECT id_cuenta FROM cuenta WHERE correo = '$correo_s'";

                $query_id = mysqli_query($con, $obtener_id);

                if($query_id){

                    $reslutado = mysqli_fetch_assoc($query_id);

                    $id_cuenta = $reslutado['id_cuenta'];

                    $cuenta_ciclo_nueva= "INSERT INTO ciclo_cuenta (id_ciclo_cuenta, id_cuenta, id_grupo, id_ciclo)
                    VALUES (UUID(), '$id_cuenta', 1, 3)";

                    $query_ciclo = mysqli_query($con, $cuenta_ciclo_nueva);

                    echo "Cuentra creada con exito";

                }

                
                //header("Location: index.php?registro=exitoso");
                //exit();
            }
        }
        else{
            echo "datos rechazados";
        }
    
    }
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <form action="crear_cuenta.php" method="post">
        <div>
            <label for="correo">Escribe tu correo:</label>
            <input id="correo" name="correo" type="text" placeholder="" required>
        </div>
        <div>
            <label for="usuario">Escribe tu nombre:</label>
            <input id="usuario" name="usuario" type="text" placeholder="" required>
        </div>
        <div>
            <label for="grupo">grupo</label>
            <select name="grupo" id="grupo" required>
                <option value="" disabled selected>Escoge el grupo en el que estas inscrito</option>
                <option value="61-A">61-A</option>
                <option value="61-B">61-B</option>
                <option value="61-c">61-C</option>
                <option value="61-D">61-D</option>
                <option value="62-A">62-A</option>
                <option value="62-B">62-B</option>
                <option value="62-c">62-C</option>
            </select>
        </div>
        <div>
            <label for="tipo_us">usuario</label>
            <select name="tipo_us" id="tipo_us" required>
                <option value="" disabled selected>Cual es tu rol en el ETE?</option>
                <option value="alumno">alumno</option>
                <option value="profesor">profesor</option>
                <option value="admin">administrador</option>
            </select>
        </div>
        <div>
            <label for="password">escoge una contraseña:</label>
            <input id="password" name="password" type="password" placeholder="" required>
        </div>
        <div>
            <input type="submit" value="crear cuenta">
        </div>
    </form>
</body>
</html>