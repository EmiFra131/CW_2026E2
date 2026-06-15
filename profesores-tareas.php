<?php
session_start();
include './include/db.php';
include './include/validacion.php';

if (!isset($_SESSION["id_cuenta"]) || $_SESSION["rol"] != "Profesor") {
    header("Location: index.php");
    exit();
}

$con = connect();
$error = null;
$exito = null;

// Traer grupos para el select
$query_grupos = "SELECT id_grupo, nombre_grupo FROM grupo";
$result_grupos = mysqli_query($con, $query_grupos);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre      = sanitizar_entrada($con, $_POST["tarea"]);
    $descripcion = sanitizar_entrada($con, $_POST["descripcion"]);
    $id_profesor = $_SESSION["id_cuenta"];
    $id_grupo    = $_POST["grupo"];
    $fecha       = $_POST["fecha"];

    $query = "INSERT INTO actividad (id_actividad, nombre, descripcion, id_cuenta_profesor, id_tipo_tarea)
              VALUES (UUID(), '$nombre', '$descripcion', '$id_profesor', 1)";
    $result = mysqli_query($con, $query);

    if ($result) {
        $id_actividad = mysqli_insert_id($con);

        $query_grupo = "INSERT INTO actividad_grupo (id_actividad_grupo, id_actividad, id_grupo, id_ciclo, fecha_de_entrega)
                        VALUES (UUID(), '$id_actividad', '$id_grupo', 4, '$fecha')";
        mysqli_query($con, $query_grupo);

        $exito = "Tarea publicada con éxito";
    } else {
        $error = "Error al publicar la tarea";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profesor</title>
    <link rel="stylesheet" href="./statics/styles/profesores-tareas.css">
</head>
<body>

<header class="logos">
    <div class="img-logos">
        <img id="unam" src="./statics/img/logos/unam.png" alt="UNAM">
        <img src="./statics/img/logos/enp.png" alt="ENP">
        <img id="ete" src="./statics/img/logos/ete.png" alt="ETE">
    </div>
    <div class="usuario">
        <h1>PROFESOR-TAREAS</h1>
    </div>
</header>

<main>
    <img src="./statics/img/iconos/tarea.png" alt="tarea">
    <h2>TAREA:</h2>

    <?php if ($exito) echo "<p style='color:green'>" . htmlspecialchars($exito) . "</p>"; ?>
    <?php if ($error) echo "<p style='color:red'>" . htmlspecialchars($error) . "</p>"; ?>

    <form method="post" action="profesores-tareas.php" enctype="multipart/form-data">

        <div class="titulo">
            <input type="text" id="tarea" name="tarea" placeholder="Nombre de la tarea" required>
        </div>

        <h3>Descripción:</h3>
        <textarea name="descripcion" id="descripcion" cols="30" rows="10"></textarea>

        <h3>Fecha de entrega:</h3>
        <input type="datetime-local" name="fecha" required>

        <h3>Grupo:</h3>
        <select name="grupo" required>
            <option value="" disabled selected>Selecciona un grupo</option>
            <?php
            while ($grupo = mysqli_fetch_assoc($result_grupos)) {
                echo "<option value='" . htmlspecialchars($grupo["id_grupo"]) . "'>" . htmlspecialchars($grupo["nombre_grupo"]) . "</option>";
            }
            ?>
        </select>

        <fieldset class="publicacion">
            <label class="opcion" for="Publicar-todos">Publicar para todos:</label>
            <input type="radio" id="Publicar-todos" name="tipo_publicacion" value="todos"><br>

            <label class="opcion" for="Publicar-ind">Publicar individualmente:</label>
            <input type="radio" id="Publicar-ind" name="tipo_publicacion" value="individual"><br>

            <label class="opcion" for="Agregar">Agregar multimedia y archivos:</label>
            <input type="file" id="Agregar" name="archivo"><br>

            <input class="opcion" type="submit" value="Publicar">
        </fieldset>

    </form>
</main>

</body>
</html>