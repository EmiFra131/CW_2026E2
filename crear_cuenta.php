<?php
    session_start();
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
        if(!grupo_valido($con, $grupo) || !usuario_valido($con, $user)){    //verificacion de datos :p//
            echo "Datos invalidos";
            exit();
        }
        $nombre_s = sanitizar_entrada($con,$nombre);
        $correo_s = sanitizar_entrada($con,$correo);
        $correo_valido = validar_correo($correo_s);
        $contrasena_valida = validacion_contrasena($contrasena);

        $hash = hashear_password($contrasena);

        if($contrasena_valida == true && $correo_valido == true){
            /*$id = null;
            if($user == "alumno")
                $id = 1;
            if($user == "profesor")
                $id = 2;
            if($user == "admin")
                $id = 3;

            if($id == null){
                echo "Rol invalido";
                exit();*/


            
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
                    VALUES (UUID(), '$id_cuenta','$grupo', 3)";

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
    $obtener_t_usuarios = "SELECT id_tipo_usuario, rol FROM tipo_usuario";
    $obtener_grupos = "SELECT id_grupo, nombre_grupo FROM grupo";
    $grupos=mysqli_query($con, $obtener_grupos);
    $tipos_usuario=mysqli_query($con, $obtener_t_usuarios);
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
            <label for="grupo">Grupo</label>
            <select name="grupo" id="grupo" required>
                <option value="" disabled selected>Escoge el grupo en el que estás inscrito</option>
                <?php while ($g = mysqli_fetch_assoc($grupos)): ?>
                    <option value="<?= $g['id_grupo'] ?>"><?= htmlspecialchars($g['nombre_grupo']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div>
            <label for="tipo_us">Usuario:</label>
            <select name="tipo_us" id="tipo_us" required>
                <option value="" disabled selected>¿Cuál es tu rol en la ETE?</option>
                <?php while ($tu = mysqli_fetch_assoc($tipos_usuario)): ?>
                    <option value="<?= $tu['id_tipo_usuario'] ?>"><?= htmlspecialchars($tu['rol']) ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div>
            <label for="password">Escoge una contraseña:</label>
            <input id="password" name="password" type="password" placeholder="" required>
        </div>
        <div>
            <input type="submit" value="Crear cuenta">
        </div>
    </form>
</body>
</html>