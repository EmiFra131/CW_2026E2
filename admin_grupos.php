 <?php

    include 'include/db.php';
    include 'include/validacion.php';

    $gruposelec = "";

    if($_SERVER["REQUEST_METHOD"] == "POST"){
        $edit = true;
        $grupo_selec = $_POST['grupo_act'];
    }
    else{
        $edit = false;
    }

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
            foreach($grupos as $grupo){

                if(!$edit){
                    echo '<div class="grupo">
                        <h3> Grupo'.$grupo.'</h3>
                        <form action="integrantes_grupo.php ">
                            <button type="submit" class="boton"> ver interantes </button>
                        </form>
                        <br>
                        <form action="admin_grupos.php" method="post" name='.$grupo.'>
                            <input type="hidden" name="grupo_act" value='.$grupo.'> 
                            <button type="submit" class="boton"> Editar </button>
                        </form>
                    </div>';
                }
                else{
                    if($grupo == $grupo_selec){
                        echo '<div class="grupo">
                            <form action="admin_grupos.php" method="post" name='.$grupo.'>
                                <div>
                                    <label for="nombre">Cambiar el nombre del grupo:</label>
                                    <input id="nombre" name="nombre" type="text" placeholder='.$grupo.' required>
                                </div>
                                <div>
                                    <label for="turno">turno</label>
                                    <select name="turno" id="turno" required>
                                        <option value="" disabled selected>Escoge el nuevo turno para el grupo</option>
                                        <option value="matutino">matutino</option>
                                        <option value="vespertino">vespertino</option>
                                    </select>
                                </div>
                                <button type="submit" class="boton">Guardar</button>
                            </form>
                        </div>';
                    }
                     
                }
            }
            
        ?>
    </div>
</body>
</html>