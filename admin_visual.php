<?php
    session_start();
    include 'include/db.php';
    include 'include/validacion.php';

    /*
    if (!isset($_SESSION["usuario"]) || $_SESSION["rol"] != "Admin") {
        header("Location: index.php");
        exit();
    }
    */

    $con = connect();

    $seleccion = "";
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

        if(isset($_POST["editar"])){
            $editar = true;
        }

        if(isset($_POST["borrar"])){
            $borrar = true;
        }

        if(isset($_POST["usuario_id"])){

            $user = $_POST["usuario_id"];

            $query_edit = "SELECT cc.id_ciclo_cuenta, c.id_cuenta, c.correo, c.nombre, c.contraseña, t.rol, g.nombre_grupo
            FROM ciclo_cuenta cc
            INNER JOIN cuenta        c  ON cc.id_cuenta = c.id_cuenta
            INNER JOIN tipo_usuario  t  ON c.id_tipo_usuario = t.id_tipo_usuario
            INNER JOIN grupo         g  ON cc.id_grupo = g.id_grupo
            WHERE cc.id_ciclo_cuenta = '$user'";

            $consulta_edit = mysqli_query($con,$query_edit);

            $coincidencia = mysqli_fetch_assoc($consulta_edit);

            if($coincidencia !=null){
                $id_edit = $coincidencia['id_ciclo_cuenta'];
                $correo = $coincidencia['correo'];
                $nombre_edit = $coincidencia['nombre'];
                $roles_edit = $coincidencia['rol'];
                $nombres_grupo_edit = $coincidencia['nombre_grupo'];
                $contrseña_edit = $coincidencia['contraseña'];
                $cuenta = $coincidencia['id_cuenta'];

                echo "consulta exitosa";
            }
            
            if($_POST["correo"] == null)
                $correo_s = $correo;    
            else{
                $correo = $_POST["correo"];
                $correo_s = sanitizar_entrada($con,$correo);
                validar_correo($correo_s);
            }
                
            if($_POST["usuario"] == null)
                $nombre_edit_s = $nombre_edit;
            else{
                $nombre_edit = $_POST["usuario"];
                $nombre_edit_s = sanitizar_entrada($con,$nombre_edit)
            }
                
            if($_POST["grupo"]== null)
                $grupo_edit = $nombres_grupo_edit;
            else{
                $grupo_edit = $_POST['grupo'];
                $correo_valido = grupo_valido($grupo_edit);
            }
            if($_POST["tipo_us"]== null)
                $tipo_us_edit = $roles_edit;
            else{
                $tipo_us_edit = $_POST['tipo_us'];
                $usuario_valido = usuario_valido($tipo_us_edit);
            }
            if($_POST["password"]== null)
                $contra = $contrseña_edit;
            else{
                $contra = $_POST['password'];
                if(validacion_contrasena($contra)){
                    $hash = hashear_password($contra);
                }
            }

            if($correo_valido && $usuario_valido){
                $id_us = null;
                if($tipo_us == "alumno")
                    $id_us = 1;
                if($tipo_us == "profesor")
                    $id_us = 2;
                if($tipo_us == "admin")
                    $id_us = 3;

                if($id_us == null){
                    echo "Rol invalido";
                    exit();
                }

                if($_POST['coorreo'] == null || $_POST['usuario'] == null|| $_POST['grupo'] == null || $_POST['tipo_us'] == null || $_POST["password"]== null){

                    $act_datos = "UPDATE cuenta
                    SET correo = '$correo_s', nombre = '$nombre_edit_s, contraseña = $hash, id_tipo_usuario = $id_us'
                    WHERE id_cuenta = '$cuenta'";

                    $query_act = mysqli_query($con, $act_datos);

                    if($query_act){
                        $act_datos2 = "UPDATE ciclo_cuenta
                        SET id_grupo = 2
                        WHERE id_ciclo_cuenta = 'b2c3d4e5-f6a7-8901-bcde-f12345678901';"
                    }

                    
                }
            }

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
                <img src="../statics/img/logos/logo_unam.png" alt="UNAM">
                <img src="../statics/img/logos/logo_p6.png" alt="ENP6">
            </div>
            <div class="logo_derecha">
                <img src="../statics/img/logos/logo_ete.png" alt="ETE">
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
                    "<img src='../statics/img/iconos/icono_usuario.png' alt= 'usuario'>
                    <h2>" . htmlspecialchars($nombre_profe) . "</h2>
                    <p>Horario: Lunes-Viernes 12:00 a 13:40</p>
                    <p>Salón: LACEC</p>";
                }
                ?>
            </div>

            <div class="seccion_alumno">
                <?php
                    if(!$editar&&!$borrar){
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
                                <span>" . htmlspecialchars($alumno["id_ciclo_cuenta"]) ."</span>
                                <span>" . htmlspecialchars($alumno["nombre"]) . "</span>
                                <form action='editar_crear_admin.php' method='post'>
                                    <input type= hidden name= us_editar value= ". htmlspecialchars($alumno["id_ciclo_cuenta"]) .">
                                    <button type= submit >➕ Editar</button>
                                </form>
                            </div>";
                        } 
                    }
                ?>
                <div class="boton_agregar">
                    <?php
                        if($seleccion){
                            echo "<form action='editar_crear_admin.php' method='post'>
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
                                <button type= submit >➕Editar participantes</button>
                            </form>"
                            
                    ?>
                </div>
            </div>

        </div>
    </body>
</html>