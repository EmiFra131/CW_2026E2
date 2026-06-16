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

// KPI 1 — Tasa de entrega por alumno
$query1 = "SELECT c.nombre,
    COUNT(ag.id_actividad_grupo) AS total_actividades,
    COUNT(e.id_entrega) AS entregadas,
    ROUND(COUNT(e.id_entrega) * 100.0 / NULLIF(COUNT(ag.id_actividad_grupo), 0), 1) AS porcentaje
FROM ciclo_cuenta cc
    INNER JOIN cuenta c ON cc.id_cuenta = c.id_cuenta
    INNER JOIN actividad_grupo ag ON ag.id_grupo = cc.id_grupo AND ag.id_ciclo = cc.id_ciclo
    LEFT JOIN entrega e ON e.id_actividad_grupo = ag.id_actividad_grupo AND e.id_cuenta = cc.id_cuenta
WHERE cc.id_ciclo = $ciclo AND c.id_tipo_usuario = 1
GROUP BY c.id_cuenta, c.nombre
ORDER BY porcentaje ASC";
$result1 = mysqli_query($con, $query1);

// KPI 2 — Promedio de calificación por alumno
$query2 = "SELECT c.nombre,
    ROUND(AVG(e.calificacion), 1) AS promedio,
    COUNT(e.id_entrega) AS calificadas,
    COUNT(ag.id_actividad_grupo) AS total_actividades
FROM ciclo_cuenta cc
    INNER JOIN cuenta c ON cc.id_cuenta = c.id_cuenta
    INNER JOIN actividad_grupo ag ON ag.id_grupo = cc.id_grupo AND ag.id_ciclo = cc.id_ciclo
    LEFT JOIN entrega e ON e.id_actividad_grupo = ag.id_actividad_grupo AND e.id_cuenta = cc.id_cuenta
WHERE cc.id_ciclo = $ciclo AND c.id_tipo_usuario = 1
GROUP BY c.id_cuenta, c.nombre
ORDER BY promedio ASC";
$result2 = mysqli_query($con, $query2);

// KPI 3 — Alumnos en riesgo (menos del 60% de entrega y promedio menor a 6)
$query3 = "SELECT c.nombre,
    ROUND(COUNT(e.id_entrega) * 100.0 / NULLIF(COUNT(ag.id_actividad_grupo), 0), 1) AS porcentaje,
    ROUND(AVG(e.calificacion), 1) AS promedio
FROM ciclo_cuenta cc
    INNER JOIN cuenta c ON cc.id_cuenta = c.id_cuenta
    INNER JOIN actividad_grupo ag ON ag.id_grupo = cc.id_grupo AND ag.id_ciclo = cc.id_ciclo
    LEFT JOIN entrega e ON e.id_actividad_grupo = ag.id_actividad_grupo AND e.id_cuenta = cc.id_cuenta
WHERE cc.id_ciclo = $ciclo AND c.id_tipo_usuario = 1
GROUP BY c.id_cuenta, c.nombre
HAVING porcentaje < 60 AND (promedio < 6 OR promedio IS NULL)
ORDER BY porcentaje ASC";
$result3 = mysqli_query($con, $query3);

// KPI 6 — Rendimiento por tipo de aprendizaje
$query6 = "SELECT ta.tipo AS estilo,
    COUNT(DISTINCT ac.id_cuenta) AS total_alumnos,
    ROUND(AVG(e.calificacion), 1) AS promedio,
    ROUND(COUNT(e.id_entrega) * 100.0 / NULLIF(COUNT(ag.id_actividad_grupo), 0), 1) AS porcentaje
FROM aprendizaje_cuenta ac
    INNER JOIN tipo_aprendizaje ta ON ac.id_tipo_aprendizaje = ta.id_tipo_aprendizaje
    INNER JOIN ciclo_cuenta cc ON cc.id_cuenta = ac.id_cuenta
    INNER JOIN actividad_grupo ag ON ag.id_grupo = cc.id_grupo AND ag.id_ciclo = cc.id_ciclo
    LEFT JOIN entrega e ON e.id_actividad_grupo = ag.id_actividad_grupo AND e.id_cuenta = ac.id_cuenta
WHERE cc.id_ciclo = $ciclo
GROUP BY ta.id_tipo_aprendizaje, ta.tipo
ORDER BY promedio ASC";
$result6 = mysqli_query($con, $query6);

// KPI 9 — Alumnos sin estilo de aprendizaje registrado
$query9 = "SELECT g.nombre_grupo,
    COUNT(cc.id_cuenta) AS total,
    COUNT(ac.id_cuenta) AS con_estilo,
    COUNT(cc.id_cuenta) - COUNT(ac.id_cuenta) AS sin_estilo,
    ROUND((COUNT(cc.id_cuenta) - COUNT(ac.id_cuenta)) * 100.0 / NULLIF(COUNT(cc.id_cuenta), 0), 1) AS porcentaje
FROM ciclo_cuenta cc
    INNER JOIN grupo g ON cc.id_grupo = g.id_grupo
    LEFT JOIN aprendizaje_cuenta ac ON ac.id_cuenta = cc.id_cuenta
WHERE cc.id_ciclo = $ciclo
GROUP BY g.id_grupo, g.nombre_grupo
ORDER BY porcentaje DESC";
$result9 = mysqli_query($con, $query9);

// KPI 10 — Entregas tardías por alumno
$query10 = "SELECT c.nombre,
    COUNT(e.id_entrega) AS total_entregas,
    SUM(CASE WHEN e.fecha_de_entrega > ag.fecha_de_entrega THEN 1 ELSE 0 END) AS tardias,
    ROUND(SUM(CASE WHEN e.fecha_de_entrega > ag.fecha_de_entrega THEN 1 ELSE 0 END) * 100.0 / NULLIF(COUNT(e.id_entrega), 0), 1) AS porcentaje
FROM entrega e
    INNER JOIN cuenta c ON e.id_cuenta = c.id_cuenta
    INNER JOIN actividad_grupo ag ON e.id_actividad_grupo = ag.id_actividad_grupo
WHERE ag.id_ciclo = $ciclo AND c.id_tipo_usuario = 1
GROUP BY c.id_cuenta, c.nombre
ORDER BY porcentaje DESC";
$result10 = mysqli_query($con, $query10);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KPIs de alumnos | SATEC</title>
    <link rel="stylesheet" href="../../statics/styles/style.css">
</head>
<body>

<nav>
    <img src="../../statics/img/logos/unam.png" alt="UNAM">
    <img src="../../statics/img/logos/enp.svg" alt="ENP">
    <img src="../../statics/img/logos/ete.png" alt="ETE">
    <a href="../../dashboard/index.php">Inicio</a>
    <a href="../../grupos/index.php">Grupos</a>
    <a href="../kpi.php">Reportes globales</a>
    <a href="kpi.php">Alumnos</a>
    <a href="../profesor/kpi.php">Actividades</a>
</nav>

<div id="contenido">

    <h1>KPIs de alumnos</h1>

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

    <h2>① Tasa de entrega por alumno</h2>
    <table>
        <thead>
            <tr><th>Alumno</th><th>Actividades</th><th>Entregadas</th><th>% Entrega</th></tr>
        </thead>
        <tbody>
            <?php while ($fila = mysqli_fetch_assoc($result1)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($fila['nombre']); ?></td>
                    <td><?php echo $fila['total_actividades']; ?></td>
                    <td><?php echo $fila['entregadas']; ?></td>
                    <td>
                        <progress value="<?php echo $fila['porcentaje'] ?? 0; ?>" max="100"></progress>
                        <?php echo $fila['porcentaje'] ?? 0; ?>%
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <h2>② Promedio de calificación por alumno</h2>
    <table>
        <thead>
            <tr><th>Alumno</th><th>Promedio</th><th>Calificadas</th><th>Total actividades</th></tr>
        </thead>
        <tbody>
            <?php while ($fila = mysqli_fetch_assoc($result2)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($fila['nombre']); ?></td>
                    <td><?php echo $fila['promedio'] ?? '—'; ?></td>
                    <td><?php echo $fila['calificadas']; ?></td>
                    <td><?php echo $fila['total_actividades']; ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <h2>③ Alumnos en riesgo de deserción</h2>
    <p>Entrega menor al 60% y promedio menor a 6.</p>
    <table>
        <thead>
            <tr><th>Alumno</th><th>% Entrega</th><th>Promedio</th></tr>
        </thead>
        <tbody>
            <?php
            $hay = false;
            while ($fila = mysqli_fetch_assoc($result3)) {
                $hay = true;
                echo "<tr>";
                echo "<td>" . htmlspecialchars($fila['nombre']) . "</td>";
                echo "<td>" . ($fila['porcentaje'] ?? 0) . "%</td>";
                echo "<td>" . ($fila['promedio'] ?? '—') . "</td>";
                echo "</tr>";
            }
            if (!$hay) echo "<tr><td colspan='3'>Ningún alumno en riesgo detectado.</td></tr>";
            ?>
        </tbody>
    </table>

    <h2>⑥ Rendimiento por tipo de aprendizaje</h2>
    <table>
        <thead>
            <tr><th>Estilo</th><th>Alumnos</th><th>Promedio</th><th>% Entrega</th></tr>
        </thead>
        <tbody>
            <?php while ($fila = mysqli_fetch_assoc($result6)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($fila['estilo']); ?></td>
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

    <h2>⑨ Alumnos sin estilo de aprendizaje registrado</h2>
    <table>
        <thead>
            <tr><th>Grupo</th><th>Total</th><th>Con estilo</th><th>Sin estilo</th><th>% Sin registrar</th></tr>
        </thead>
        <tbody>
            <?php while ($fila = mysqli_fetch_assoc($result9)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($fila['nombre_grupo']); ?></td>
                    <td><?php echo $fila['total']; ?></td>
                    <td><?php echo $fila['con_estilo']; ?></td>
                    <td><?php echo $fila['sin_estilo']; ?></td>
                    <td>
                        <progress value="<?php echo $fila['porcentaje'] ?? 0; ?>" max="100"></progress>
                        <?php echo $fila['porcentaje'] ?? 0; ?>%
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <h2>⑩ Entregas tardías por alumno</h2>
    <table>
        <thead>
            <tr><th>Alumno</th><th>Total entregas</th><th>Tardías</th><th>% Tardías</th></tr>
        </thead>
        <tbody>
            <?php
            $hay = false;
            while ($fila = mysqli_fetch_assoc($result10)) {
                $hay = true;
                echo "<tr>";
                echo "<td>" . htmlspecialchars($fila['nombre']) . "</td>";
                echo "<td>" . $fila['total_entregas'] . "</td>";
                echo "<td>" . $fila['tardias'] . "</td>";
                echo "<td><progress value='" . ($fila['porcentaje'] ?? 0) . "' max='100'></progress> " . ($fila['porcentaje'] ?? 0) . "%</td>";
                echo "</tr>";
            }
            if (!$hay) echo "<tr><td colspan='4'>Sin entregas registradas aún.</td></tr>";
            ?>
        </tbody>
    </table>

</div>

</body>
</html>
