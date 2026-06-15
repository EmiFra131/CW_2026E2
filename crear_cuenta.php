<?php
session_start();
include 'include/db.php';
include 'include/validacion.php';

$con = connect();
$error = null;
$exito = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nombre    = $_POST["usuario"];
    $correo    = $_POST["correo"];
    $contrasena = $_POST["password"];
    $id_grupo  = $_POST['grupo'];
    $id_user   = $_POST['tipo_us'];

    if (!grupo_valido($con, $id_grupo) || !usuario_valido($con, $id_user)) {
        $error = "Datos inválidos";
    } else {

        $nombre_s = sanitizar_entrada($con, $nombre);
        $correo_s = sanitizar_entrada($con, $correo);

        if (!validar_correo($correo_s)) {
            $error = "Correo no válido";
        } else if (!validacion_contrasena($contrasena)) {
            $error = "La contraseña no cumple los requisitos";
        } else {

            // Convierte la contraseña a hash antes de guardarla
            $hash = hashear_password($contrasena);

            // Pto.1 preparamos el INSERT de la cuenta
            // El ? es un espacio reservado para el valor real, evitara SQL Injection
            $stmt = mysqli_prepare($con,
                "INSERT INTO cuenta (id_cuenta, correo, nombre, contraseña, id_tipo_usuario)
                 VALUES (UUID(), ?, ?, ?, ?)"
            );
            // Vincular los valores a los ? en orden: sssi = string, string, string, integer
            mysqli_stmt_bind_param($stmt, 'sssi', $correo_s, $nombre_s, $hash, $id_user);
            // Ejecuta el INSERT
            mysqli_stmt_execute($stmt);

            // Verifica que si se inserto al menos una fila
            if (mysqli_stmt_affected_rows($stmt) > 0) {

                // Pto.2 recupera el UUID que generó la BD para la cuenta recién creada
                // mysqli_insert_id no funciona con UUID, por eso hacemos un SELECT
                $stmt2 = mysqli_prepare($con,
                    "SELECT id_cuenta FROM cuenta WHERE correo = ?"
                );
                mysqli_stmt_bind_param($stmt2, 's', $correo_s);
                mysqli_stmt_execute($stmt2);
                $result2 = mysqli_stmt_get_result($stmt2);
                $resultado = mysqli_fetch_assoc($result2);
                // Guarda el id para usarlo en el siguiente INSERT
                $id_cuenta = $resultado['id_cuenta'];

                // Pto.3 inserta la relación cuenta-grupo en ciclo_cuenta
                // Usamos el id_cuenta que acabamos de recuperar
                $stmt3 = mysqli_prepare($con,
                    "INSERT INTO ciclo_cuenta (id_ciclo_cuenta, id_cuenta, id_grupo, id_ciclo)
                     VALUES (UUID(), ?, ?, 3)"
                );
                // si = string (id_cuenta es UUID/string), integer (id_grupo es entero)
                mysqli_stmt_bind_param($stmt3, 'si', $id_cuenta, $id_grupo);
                mysqli_stmt_execute($stmt3);

                header("Location: index.php?registro=exitoso");
                exit();

            } else {
                $error = "Error al crear la cuenta";
            }
        }
    }
}

$result_grupos = mysqli_query($con, "SELECT id_grupo, nombre_grupo FROM grupo");
$result_tipos  = mysqli_query($con, "SELECT id_tipo_usuario, rol FROM tipo_usuario");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear cuenta</title>
</head>
<body>

    <?php if ($error) echo "<p style='color:red'>" . htmlspecialchars($error) . "</p>"; ?>

    <form action="crear_cuenta.php" method="post">
        <div>
            <label for="correo">Escribe tu correo:</label>
            <input id="correo" name="correo" type="email" required>
        </div>
        <div>
            <label for="usuario">Escribe tu nombre:</label>
            <input id="usuario" name="usuario" type="text" required>
        </div>
        <div>
            <label for="grupo">Grupo</label>
            <select name="grupo" id="grupo" required>
                <option value="" disabled selected>Escoge el grupo en el que estás inscrito</option>
                <?php
                while ($g = mysqli_fetch_assoc($result_grupos)) {
                    echo "<option value='" . htmlspecialchars($g['id_grupo']) . "'>" . htmlspecialchars($g['nombre_grupo']) . "</option>";
                }
                ?>
            </select>
        </div>
        <div>
            <label for="tipo_us">Usuario:</label>
            <select name="tipo_us" id="tipo_us" required>
                <option value="" disabled selected>¿Cuál es tu rol en la ETE?</option>
                <?php
                while ($tu = mysqli_fetch_assoc($result_tipos)) {
                    echo "<option value='" . htmlspecialchars($tu['id_tipo_usuario']) . "'>" . htmlspecialchars($tu['rol']) . "</option>";
                }
                ?>
            </select>
        </div>
        <div>
            <label for="password">Escoge una contraseña:</label>
            <input id="password" name="password" type="password" required>
        </div>
        <div>
            <input type="submit" value="Crear cuenta">
        </div>
    </form>

</body>
</html>