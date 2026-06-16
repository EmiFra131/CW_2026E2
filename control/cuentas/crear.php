<?php
    session_start();
    include '../../include/db.php';
    include '../../include/validacion.php';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        $con             = connect();
        $nombre          = sanitizar_entrada($con, $_POST['nombre']);
        $correo          = sanitizar_entrada($con, $_POST['correo']);
        $contrasena      = $_POST['contrasena'];
        $id_grupo        = $_POST['id_grupo'] ?? '';
        $id_tipo_usuario = (int) $_POST['id_tipo_usuario'];

        $es_administrador = ($id_tipo_usuario == ROL_ADMINISTRADOR);

        if (!usuario_valido($con, $id_tipo_usuario) || (!$es_administrador && !grupo_valido($con, $id_grupo))) {
            echo "Datos inválidos";
            exit();
        }

        $correo_valido     = validar_correo_rol($correo, $id_tipo_usuario);
        $contrasena_valida = validacion_contrasena($contrasena);
        $hash              = hashear_password($contrasena);

        if ($contrasena_valida && $correo_valido) {

            $stmt = mysqli_prepare($con, "INSERT INTO cuenta (id_cuenta, correo, nombre, contrasena, id_tipo_usuario) VALUES (UUID(), ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt, 'sssi', $correo, $nombre, $hash, $id_tipo_usuario);
            $ok = mysqli_stmt_execute($stmt);

            if ($ok) {

                $stmt = mysqli_prepare($con, "SELECT id_cuenta FROM cuenta WHERE correo = ?");
                mysqli_stmt_bind_param($stmt, 's', $correo);
                mysqli_stmt_execute($stmt);
                $resultado = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

                if ($resultado) {
                    $id_cuenta = $resultado['id_cuenta'];

                    // Un Administrador no pertenece a ningún grupo/ciclo: no se inscribe en ciclo_cuenta
                    if (!$es_administrador) {
                        $id_grupo = (int) $id_grupo;
                        $stmt = mysqli_prepare($con, "SELECT id_ciclo FROM grupo WHERE id_grupo = ?");
                        mysqli_stmt_bind_param($stmt, 'i', $id_grupo);
                        mysqli_stmt_execute($stmt);
                        $id_ciclo = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['id_ciclo'];

                        $stmt = mysqli_prepare($con, "INSERT INTO ciclo_cuenta (id_ciclo_cuenta, id_cuenta, id_grupo, id_ciclo) VALUES (UUID(), ?, ?, ?)");
                        mysqli_stmt_bind_param($stmt, 'sii', $id_cuenta, $id_grupo, $id_ciclo);
                        mysqli_stmt_execute($stmt);
                    }

                    if (isset($_SESSION["id_tipo_usuario"]) && $_SESSION["id_tipo_usuario"] == ROL_ADMINISTRADOR) {
                        header($es_administrador
                            ? "Location: ../../control/cuentas/index.php"
                            : "Location: ../../grupos/$id_grupo/personas");
                    } else {
                        header("Location: ../../index.php");
                    }
                    exit();
                }
            } else {
                echo "Error al crear la cuenta: " . mysqli_error($con);
            }
        } else {
            echo "Contraseña o correo inválidos. La contraseña debe tener al menos 6 caracteres, una mayúscula y un número. "
               . "El correo debe ser tu número de cuenta@alumno.enp.unam.mx si eres Alumno, o nombre.apellido@enp.unam.mx si eres Profesor o Administrador.";
        }
    }
?>

<?php
    $con_form = connect();
    $ciclo    = ciclo_activo($con_form);
    $r_grupos = mysqli_query($con_form, "SELECT id_grupo, nombre_grupo FROM grupo WHERE id_ciclo = $ciclo ORDER BY nombre_grupo");
    $r_roles  = mysqli_query($con_form, "SELECT id_tipo_usuario, rol FROM tipo_usuario ORDER BY rol");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear cuenta | SATEC</title>
    <link rel="stylesheet" href="../../statics/styles/style.css">
</head>
<body>

<nav>
    <img src="../../statics/img/logos/unam.png" alt="UNAM">
    <img src="../../statics/img/logos/enp.svg" alt="ENP">
    <img src="../../statics/img/logos/ete.png" alt="ETE">
    <a href="../../dashboard/index.php">Inicio</a>
    <a href="../../index.php">Iniciar sesión</a>
</nav>

<form action="crear.php" method="post">
    <div>
        <label for="correo">Correo (Alumno: numerocuenta@alumno.enp.unam.mx — Profesor/Administrador: nombre.apellido@enp.unam.mx):</label>
        <input type="email" id="correo" name="correo" placeholder="" required>
    </div>
    <div>
        <label for="nombre">Nombre completo:</label>
        <input type="text" id="nombre" name="nombre" placeholder="" required>
    </div>
    <div>
        <label for="id_tipo_usuario">Rol:</label>
        <select name="id_tipo_usuario" id="id_tipo_usuario" required>
            <option value="" disabled selected>¿Cuál es tu rol en el ETE?</option>
            <?php while ($r = mysqli_fetch_assoc($r_roles)): ?>
                <option value="<?= $r['id_tipo_usuario'] ?>"><?= htmlspecialchars($r['rol']) ?></option>
            <?php endwhile; ?>
        </select>
    </div>
    <div>
        <label for="id_grupo">Grupo (no aplica si tu rol es Administrador):</label>
        <select name="id_grupo" id="id_grupo">
            <option value="" disabled selected>Escoge el grupo en el que estás inscrito:</option>
            <?php while ($g = mysqli_fetch_assoc($r_grupos)): ?>
                <option value="<?= $g['id_grupo'] ?>"><?= htmlspecialchars($g['nombre_grupo']) ?></option>
            <?php endwhile; ?>
        </select>
    </div>
    <div>
        <label for="contrasena">Contraseña:</label>
        <input type="password" id="contrasena" name="contrasena" placeholder="" required>
    </div>
    <div>
        <input type="submit" value="Crear cuenta">
    </div>
</form>
</body>
</html>
