<?php
session_start();
include '../../include/db.php';

if (!isset($_SESSION["id_cuenta"]) || $_SESSION["rol"] != "Administrador") {
    header("Location: ../../index.php");
    exit();
}

$con   = connect();
$ciclo = isset($_GET["ciclo"]) ? (int)$_GET["ciclo"] : ciclo_activo($con);

// Selector de ciclos
$query_ciclos  = "SELECT DISTINCT g.id_ciclo, ce.periodo FROM grupo g INNER JOIN ciclo_escolar ce ON g.id_ciclo = ce.id_ciclo ORDER BY g.id_ciclo DESC";
$result_ciclos = mysqli_query($con, $query_ciclos);
$ciclos = [];
while ($fila = mysqli_fetch_assoc($result_ciclos)) {
    $ciclos[] = $fila;
}

// Selector de grupos para retroalimentacion
$query_grupos  = "SELECT id_grupo, nombre_grupo FROM grupo WHERE id_ciclo = $ciclo ORDER BY nombre_grupo";
$result_grupos = mysqli_query($con, $query_grupos);
$grupos = [];
while ($fila = mysqli_fetch_assoc($result_grupos)) {
    $grupos[] = $fila;
}
$id_grupo = isset($_GET["grupo"]) ? (int)$_GET["grupo"] : ($grupos[0]['id_grupo'] ?? 0);

// Comparativa entre profesores
$query_prof = "SELECT c.nombre,
    GROUP_CONCAT(DISTINCT g.nombre_grupo ORDER BY g.nombre_grupo SEPARATOR ', ') AS grupos,
    (SELECT COUNT(*) FROM actividad a
        INNER JOIN actividad_grupo ag ON ag.id_actividad = a.id_actividad
        WHERE a.id_cuenta_profesor = c.id_cuenta AND ag.id_ciclo = $ciclo) AS actividades,
    (SELECT ROUND(AVG(e.calificacion), 1) FROM entrega e
        INNER JOIN actividad_grupo ag ON e.id_actividad_grupo = ag.id_actividad_grupo
        INNER JOIN actividad a ON ag.id_actividad = a.id_actividad
        WHERE a.id_cuenta_profesor = c.id_cuenta AND ag.id_ciclo = $ciclo AND e.calificacion IS NOT NULL) AS promedio
FROM ciclo_cuenta cc
    INNER JOIN cuenta c ON cc.id_cuenta = c.id_cuenta
    INNER JOIN grupo g ON cc.id_grupo = g.id_grupo
WHERE c.id_tipo_usuario = 2 AND cc.id_ciclo = $ciclo
GROUP BY c.id_cuenta, c.nombre
ORDER BY actividades DESC";
$result_prof = mysqli_query($con, $query_prof);

// KPI 8 — Retroalimentación por actividad
$query8 = "SELECT a.nombre AS actividad, tt.tipo,
    COUNT(r.id_cuenta) AS respuestas,
    ROUND(AVG(r.valoracion), 1) AS valoracion,
    SUM(CASE WHEN r.pregunta1 = 1 THEN 1 ELSE 0 END) AS objetivos_cumplidos,
    SUM(CASE WHEN r.pregunta2 = 1 THEN 1 ELSE 0 END) AS tiempo_adecuado,
    SUM(CASE WHEN r.pregunta3 = 1 THEN 1 ELSE 0 END) AS tienen_dudas
FROM actividad_grupo ag
    INNER JOIN actividad a ON ag.id_actividad = a.id_actividad
    INNER JOIN tipo_tarea tt ON a.id_tipo_tarea = tt.id_tipo_tarea
    LEFT JOIN retroalimentacion_alumno r ON r.id_actividad_grupo = ag.id_actividad_grupo
WHERE ag.id_grupo = $id_grupo AND ag.id_ciclo = $ciclo
GROUP BY ag.id_actividad_grupo, a.nombre, tt.tipo
ORDER BY valoracion ASC";
$result8 = mysqli_query($con, $query8);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KPIs de actividades | SATEC</title>
    <link rel="stylesheet" href="../../statics/styles/style.css">
</head>
<body>

<nav>
    <img src="../../statics/img/logos/unam.png" alt="UNAM">
    <img src="../../statics/img/logos/enp.svg" alt="ENP">
    <img src="../../statics/img/logos/ete.png" alt="ETE">
    <a href="../../dashboard/index.php">Inicio</a>
    <a href="../kpi.php">Reportes globales</a>
    <a href="../alumno/kpi.php">Alumnos</a>
    <a href="kpi.php">Actividades</a>
</nav>

<div id="contenido">

    <h1>KPIs de actividades y profesores</h1>

    <form method="get" action="kpi.php">
        <label for="ciclo">Ciclo escolar:</label>
        <select name="ciclo" id="ciclo" onchange="this.form.submit()">
            <?php foreach ($ciclos as $c): ?>
                <option value="<?php echo $c['id_ciclo']; ?>" <?php if ($c['id_ciclo'] == $ciclo) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($c['periodo']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <h2>Comparativa entre profesores</h2>
    <table>
        <thead>
            <tr><th>Profesor</th><th>Grupos</th><th>Actividades</th><th>Promedio</th></tr>
        </thead>
        <tbody>
            <?php while ($fila = mysqli_fetch_assoc($result_prof)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($fila['nombre']); ?></td>
                    <td><?php echo htmlspecialchars($fila['grupos']); ?></td>
                    <td><?php echo $fila['actividades']; ?></td>
                    <td><?php echo $fila['promedio'] ?? '—'; ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <h2>⑧ Retroalimentación por actividad</h2>

    <form method="get" action="kpi.php">
        <input type="hidden" name="ciclo" value="<?php echo $ciclo; ?>">
        <label for="grupo">Grupo:</label>
        <select name="grupo" id="grupo" onchange="this.form.submit()">
            <?php foreach ($grupos as $g): ?>
                <option value="<?php echo $g['id_grupo']; ?>" <?php if ($g['id_grupo'] == $id_grupo) echo 'selected'; ?>>
                    <?php echo htmlspecialchars($g['nombre_grupo']); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>

    <table>
        <thead>
            <tr>
                <th>Actividad</th><th>Tipo</th><th>Respuestas</th><th>Valoración</th>
                <th>Objetivos cumplidos</th><th>Tiempo adecuado</th><th>Con dudas</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $hay = false;
            while ($fila = mysqli_fetch_assoc($result8)) {
                $hay = true;
                echo "<tr>";
                echo "<td>" . htmlspecialchars($fila['actividad']) . "</td>";
                echo "<td>" . htmlspecialchars($fila['tipo']) . "</td>";
                echo "<td>" . $fila['respuestas'] . "</td>";
                echo "<td>" . ($fila['valoracion'] ?? '—') . "</td>";
                echo "<td>" . $fila['objetivos_cumplidos'] . "</td>";
                echo "<td>" . $fila['tiempo_adecuado'] . "</td>";
                echo "<td>" . $fila['tienen_dudas'] . "</td>";
                echo "</tr>";
            }
            if (!$hay) echo "<tr><td colspan='7'>Sin actividades en este grupo para el ciclo seleccionado.</td></tr>";
            ?>
        </tbody>
    </table>

</div>

</body>
</html>
