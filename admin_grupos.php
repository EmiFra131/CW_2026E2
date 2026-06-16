<?php
session_start();

include 'include/db.php';
include 'include/validacion.php';

if (!isset($_SESSION["id_cuenta"]) || $_SESSION["rol"] !== "Administrador") {
    header("Location: index.php");
    exit();
}

$con = connect();

$editar = false;
$id_grupo_selec = 0;
$grupos = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $editar = true;

    if (isset($_POST['grupo_act'])) {
        $id_grupo_selec = (int) $_POST['grupo_act'];
    }

    if (isset($_POST["id_grupo"])) {
        $id_grupo = (int) $_POST['id_grupo'];
        $nombre = sanitizar_entrada($_POST['nombre']);
        $id_turno = (int) $_POST['id_turno'];

        if (turno_valido($con, $id_turno)) {
            $stmnt_actualizar = mysqli_prepare(
                $con,
                "UPDATE grupo SET nombre_grupo = ?, id_turno = ? WHERE id_grupo = ?"
            );
            mysqli_stmt_bind_param($stmnt_actualizar, 'sii', $nombre, $id_turno, $id_grupo);

            if (mysqli_stmt_execute($stmnt_actualizar)) {
                $editar = false;
            }
        }
    }
}

$result_grupos = mysqli_query(
    $con,
    "SELECT g.id_grupo, g.nombre_grupo, t.turno, ce.periodo
     FROM grupo g
     INNER JOIN turno t ON g.id_turno = t.id_turno
     INNER JOIN ciclo_escolar ce ON g.id_ciclo = ce.id_ciclo"
);

if ($result_grupos) {
    while ($fila = mysqli_fetch_assoc($result_grupos)) {
        $grupos[] = $fila;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control de Grupos</title>
    <link rel="stylesheet" href="statics/styles/admin_grupos.css">
</head>
<body>

    <nav>
        <img src="statics/img/logos/unam.png" alt="Universidad Nacional Autónoma de México" id="unam">
        <img src="statics/img/logos/enp.svg" alt="Escuela Nacional Preparatoria 6">
        <img src="statics/img/logos/ete.png" alt="Estudios Técnicos Especializados" id="ete">
    </nav>

    <header>
        <p>Administrador</p>
    </header>

    <main>
        <?php foreach ($grupos as $grupo): ?>

            <?php if (!$editar): ?>
                <article class="grupo">
                    <h2><?= htmlspecialchars($grupo['nombre_grupo']) ?></h2>

                    <form action="admin_visual.php" method="post">
                        <input type="hidden" name="grupo_act" value="<?= htmlspecialchars($grupo['id_grupo']) ?>">
                        <button type="submit" class="boton">Ver integrantes</button>
                    </form>

                    <form action="admin_grupos.php" method="post">
                        <input type="hidden" name="grupo_act" value="<?= htmlspecialchars($grupo['id_grupo']) ?>">
                        <button type="submit" class="boton">Editar</button>
                    </form>
                </article>

            <?php else: ?>
                <?php if ($grupo['id_grupo'] == $id_grupo_selec): ?>
                    <article class="grupo">
                        <form action="admin_grupos.php" method="post">
                            <div>
                                <label for="nombre">Cambiar el nombre del grupo:</label>
                                <input
                                    id="nombre"
                                    name="nombre"
                                    type="text"
                                    placeholder="<?= htmlspecialchars($grupo['nombre_grupo']) ?>"
                                    required
                                >
                            </div>
                            <div>
                                <label for="turno">Turno</label>
                                <select id="turno" name="turno" required>
                                    <option value="" disabled selected>Escoge el nuevo turno para el grupo</option>
                                    <?php while ($turno = mysqli_fetch_assoc($result_turnos)): ?>
                                        <option value="<?= htmlspecialchars($turno['id_turno']) ?>">
                                            <?= htmlspecialchars($turno['turno']) ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                           <input type="hidden" name="id_grupo" value="<?= htmlspecialchars($grupo['id_grupo']) ?>">
                            <button type="submit" class="boton">Guardar</button>
                        </form>
                    </article>
                <?php endif; ?>
            <?php endif; ?>
        <?php endforeach; ?>
                            
                        
              
    </main>

</body>
</html>