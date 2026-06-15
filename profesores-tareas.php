<?php
session_start();
include './include/db.php';
include './include/validacion.php';


if (!isset($_SESSION["usuario"]) || $_SESSION["rol"] != "Profesor") {
    header("Location: index.php");
    exit();
}

$con = connect();
$error = null;
$exito = null;

// Traer grupos para el select
$stmt_grupos = mysqli_prepare($con, "SELECT id_grupo, nombre_grupo FROM grupo");
mysqli_stmt_execute($stmt_grupos);
$result_grupos = mysqli_stmt_get_result($stmt_grupos);
mysqli_stmt_close($stmt_grupos);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre      = $_POST["tarea"];
    $descripcion = $_POST["descripcion"];
    $id_profesor = $_SESSION["id_cuenta"];
    $id_grupo    = $_POST["grupo"];
    $fecha       = $_POST["fecha"];

    // INSERT actividad
    $stmt = mysqli_prepare($con,
        "INSERT INTO actividad (id_actividad, nombre, descripcion, id_cuenta_profesor, id_tipo_tarea)
         VALUES (UUID(), ?, ?, ?, 1)"
    );
    mysqli_stmt_bind_param($stmt, "ssi", $nombre, $descripcion, $id_profesor);
    $result = mysqli_stmt_execute($stmt);

    if ($result) {
        $id_actividad = mysqli_stmt_insert_id($stmt);
        mysqli_stmt_close($stmt);

        // INSERT actividad_grupo
        $stmt_grupo = mysqli_prepare($con,
            "INSERT INTO actividad_grupo (id_actividad_grupo, id_actividad, id_grupo, id_ciclo, fecha_de_entrega)
             VALUES (UUID(), ?, ?, 4, ?)"
        );
        mysqli_stmt_bind_param($stmt_grupo, "iis", $id_actividad, $id_grupo, $fecha);
        mysqli_stmt_execute($stmt_grupo);
        mysqli_stmt_close($stmt_grupo);

        $exito = "Tarea publicada con éxito";
    } else {
        mysqli_stmt_close($stmt);
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

    <?php if ($exito) echo "<p style='color:green'>$exito</p>"; ?>
    <?php if ($error) echo "<p style='color:red'>$error</p>"; ?>

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
                echo "<option value='" . $grupo["id_grupo"] . "'>" . $grupo["nombre_grupo"] . "</option>";
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