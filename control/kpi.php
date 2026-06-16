<?php
session_start();
include '../include/db.php';

if (!isset($_SESSION["id_cuenta"]) || $_SESSION["rol"] != "Administrador") {
    header("Location: ../index.php");
    exit();
}

$con   = connect();
$ciclo = isset($_GET["ciclo"]) ? (int)$_GET["ciclo"] : ciclo_activo($con);

// Selector de ciclos
$query_ciclos = "SELECT DISTINCT g.id_ciclo, ce.periodo
    FROM grupo g
    INNER JOIN ciclo_escolar ce ON g.id_ciclo = ce.id_ciclo
    ORDER BY g.id_ciclo DESC";
$result_ciclos = mysqli_query($con, $query_ciclos);
$ciclos = [];
while ($fila = mysqli_fetch_assoc($result_ciclos)) {
    $ciclos[] = $fila;
}

// KPI 4 — Tasa de entrega por grupo
$query_grupos = "SELECT g.nombre_grupo, t.turno,
    COUNT(DISTINCT cc.id_cuenta) AS total_alumnos,
    COUNT(e.id_entrega) AS entregas,
    COUNT(ag.id_actividad_grupo) AS actividades,
    ROUND(COUNT(e.id_entrega) * 100.0 / NULLIF(COUNT(ag.id_actividad_grupo), 0), 1) AS porcentaje
FROM grupo g
    INNER JOIN turno t ON g.id_turno = t.id_turno
    INNER JOIN actividad_grupo ag ON ag.id_grupo = g.id_grupo
    INNER JOIN ciclo_cuenta cc ON cc.id_grupo = g.id_grupo AND cc.id_ciclo = ag.id_ciclo
    LEFT JOIN entrega e ON e.id_actividad_grupo = ag.id_actividad_grupo AND e.id_cuenta = cc.id_cuenta
WHERE g.id_ciclo = $ciclo
GROUP BY g.id_grupo, g.nombre_grupo, t.turno
ORDER BY porcentaje ASC";
$result_grupos = mysqli_query($con, $query_grupos);

// KPI 5 — Comparativa matutino vs vespertino
$query_turnos = "SELECT t.turno,
    COUNT(DISTINCT cc.id_cuenta) AS total_alumnos,
    ROUND(AVG(e.calificacion), 1) AS promedio,
    ROUND(COUNT(e.id_entrega) * 100.0 / NULLIF(COUNT(ag.id_actividad_grupo), 0), 1) AS porcentaje
FROM grupo g
    INNER JOIN turno t ON g.id_turno = t.id_turno
    INNER JOIN actividad_grupo ag ON ag.id_grupo = g.id_grupo
    INNER JOIN ciclo_cuenta cc ON cc.id_grupo = g.id_grupo AND cc.id_ciclo = ag.id_ciclo
    LEFT JOIN entrega e ON e.id_actividad_grupo = ag.id_actividad_grupo AND e.id_cuenta = cc.id_cuenta
WHERE g.id_ciclo = $ciclo
GROUP BY t.id_turno, t.turno";
$result_turnos = mysqli_query($con, $query_turnos);

// KPI 7 — Actividades en clase vs Tareas en casa
$query_tipos = "SELECT tt.tipo,
    COUNT(DISTINCT ag.id_actividad_grupo) AS total,
    ROUND(AVG(e.calificacion), 1) AS promedio,
    ROUND(COUNT(e.id_entrega) * 100.0 / NULLIF(COUNT(ag.id_actividad_grupo), 0), 1) AS porcentaje
FROM actividad_grupo ag
    INNER JOIN actividad a ON ag.id_actividad = a.id_actividad
    INNER JOIN tipo_tarea tt ON a.id_tipo_tarea = tt.id_tipo_tarea
    LEFT JOIN entrega e ON e.id_actividad_grupo = ag.id_actividad_grupo
WHERE ag.id_ciclo = $ciclo
GROUP BY tt.id_tipo_tarea, tt.tipo";
$result_tipos = mysqli_query($con, $query_tipos);

// KPI 11 — Actividades sin ninguna entrega
$query_sin_entrega = "SELECT a.nombre AS actividad, g.nombre_grupo, ag.fecha_de_entrega, tt.tipo
FROM actividad_grupo ag
    INNER JOIN actividad a ON ag.id_actividad = a.id_actividad
    INNER JOIN tipo_tarea tt ON a.id_tipo_tarea = tt.id_tipo_tarea
    INNER JOIN grupo g ON ag.id_grupo = g.id_grupo
    LEFT JOIN entrega e ON e.id_actividad_grupo = ag.id_actividad_grupo
WHERE ag.id_ciclo = $ciclo AND e.id_entrega IS NULL
ORDER BY ag.fecha_de_entrega ASC";
$result_sin_entrega = mysqli_query($con, $query_sin_entrega);

// KPI 12 — Entregas por semana
$query_semanas = "SELECT WEEK(e.fecha_de_entrega) AS semana,
    COUNT(e.id_entrega) AS total_entregas,
    COUNT(DISTINCT e.id_cuenta) AS alumnos_activos,
    ROUND(AVG(e.calificacion), 1) AS promedio
FROM entrega e
    INNER JOIN actividad_grupo ag ON e.id_actividad_grupo = ag.id_actividad_grupo
WHERE ag.id_ciclo = $ciclo AND e.fecha_de_entrega IS NOT NULL
GROUP BY WEEK(e.fecha_de_entrega)
ORDER BY semana ASC";
$result_semanas = mysqli_query($con, $query_semanas);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes globales | SATEC</title>
    <link rel="stylesheet" href="../statics/styles/style.css">
</head>
<body>

<nav>
    <img src="../statics/img/logos/unam.png" alt="UNAM">
    <img src="../statics/img/logos/enp.svg" alt="ENP">
    <img src="../statics/img/logos/ete.png" alt="ETE">
    <a href="../dashboard/index.php">Inicio</a>
    <a href="kpi.php">Reportes globales</a>
    <a href="alumno/kpi.php">Alumnos</a>
    <a href="profesor/kpi.php">Actividades</a>
</nav>

<div id="contenido">

    <h1>Reportes globales</h1>

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

    <h2>④ Tasa de entrega por grupo</h2>
    <table>
        <thead>
            <tr>
                <th>Grupo</th><th>Turno</th><th>Alumnos</th><th>Actividades</th><th>Entregas</th><th>% Entrega</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($fila = mysqli_fetch_assoc($result_grupos)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($fila['nombre_grupo']); ?></td>
                    <td><?php echo htmlspecialchars($fila['turno']); ?></td>
                    <td><?php echo $fila['total_alumnos']; ?></td>
                    <td><?php echo $fila['actividades']; ?></td>
                    <td><?php echo $fila['entregas']; ?></td>
                    <td>
                        <progress value="<?php echo $fila['porcentaje'] ?? 0; ?>" max="100"></progress>
                        <?php echo $fila['porcentaje'] ?? 0; ?>%
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <h2>⑤ Comparativa matutino vs vespertino</h2>
    <table>
        <thead>
            <tr><th>Turno</th><th>Alumnos</th><th>Promedio</th><th>% Entrega</th></tr>
        </thead>
        <tbody>
            <?php while ($fila = mysqli_fetch_assoc($result_turnos)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($fila['turno']); ?></td>
                    <td><?php echo $fila['total_alumnos']; ?></td>
                    <td><?php echo $fila['promedio'] ?? '—'; ?></td>
                    <td>
                        <progress value="<?php echo $fila['porcentaje'] ?? 0; ?>" max="100"></progress>
                        <?php echo $fila['porcentaje'] ?? 0; ?>%
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <h2>⑦ Actividades en clase vs Tareas en casa</h2>
    <table>
        <thead>
            <tr><th>Tipo</th><th>Total asignadas</th><th>Promedio</th><th>% Entrega</th></tr>
        </thead>
        <tbody>
            <?php while ($fila = mysqli_fetch_assoc($result_tipos)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($fila['tipo']); ?></td>
                    <td><?php echo $fila['total']; ?></td>
                    <td><?php echo $fila['promedio'] ?? '—'; ?></td>
                    <td>
                        <progress value="<?php echo $fila['porcentaje'] ?? 0; ?>" max="100"></progress>
                        <?php echo $fila['porcentaje'] ?? 0; ?>%
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <h2>⑪ Actividades sin ninguna entrega</h2>
    <table>
        <thead>
            <tr><th>Actividad</th><th>Grupo</th><th>Tipo</th><th>Fecha límite</th></tr>
        </thead>
        <tbody>
            <?php
            $hay = false;
            while ($fila = mysqli_fetch_assoc($result_sin_entrega)) {
                $hay = true;
                echo "<tr>";
                echo "<td>" . htmlspecialchars($fila['actividad']) . "</td>";
                echo "<td>" . htmlspecialchars($fila['nombre_grupo']) . "</td>";
                echo "<td>" . htmlspecialchars($fila['tipo']) . "</td>";
                echo "<td>" . date('d/m/Y H:i', strtotime($fila['fecha_de_entrega'])) . "</td>";
                echo "</tr>";
            }
            if (!$hay) echo "<tr><td colspan='4'>Todas las actividades tienen al menos una entrega.</td></tr>";
            ?>
        </tbody>
    </table>

    <h2>⑫ Progreso del ciclo: entregas por semana</h2>
    <table>
        <thead>
            <tr><th>Semana</th><th>Entregas</th><th>Alumnos activos</th><th>Promedio</th></tr>
        </thead>
        <tbody>
            <?php
            $hay = false;
            while ($fila = mysqli_fetch_assoc($result_semanas)) {
                $hay = true;
                echo "<tr>";
                echo "<td>" . $fila['semana'] . "</td>";
                echo "<td>" . $fila['total_entregas'] . "</td>";
                echo "<td>" . $fila['alumnos_activos'] . "</td>";
                echo "<td>" . ($fila['promedio'] ?? '—') . "</td>";
                echo "</tr>";
            }
            if (!$hay) echo "<tr><td colspan='4'>Sin entregas registradas aún.</td></tr>";
            ?>
        </tbody>
    </table>

</div>

</body>
</html>
