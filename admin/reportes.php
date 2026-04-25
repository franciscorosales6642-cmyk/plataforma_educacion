<?php
session_start();
if (!isset($_SESSION["rol"]) || $_SESSION["rol"] != "ADMIN") {
    header("Location: ../login.php");
    exit;
}
include "../config/conexion.php";

$reporte = $conn->query("SELECT c.nombre, IFNULL(AVG(cal.estrellas),0) promedio, COUNT(cal.id) total FROM cursos c LEFT JOIN calificaciones cal ON c.id=cal.curso_id GROUP BY c.id ORDER BY promedio DESC,total DESC");
$comentarios = $conn->query("SELECT c.nombre curso,u.nombre usuario,cal.estrellas,cal.comentario,cal.fecha FROM calificaciones cal INNER JOIN cursos c ON cal.curso_id=c.id INNER JOIN usuarios u ON cal.usuario_id=u.id ORDER BY cal.fecha DESC");

$rankingCursos = [];
while ($fila = $reporte->fetch_assoc()) {
    $rankingCursos[] = $fila;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes</title>
    <link rel="stylesheet" href="../estilos.css">
</head>
<body class="admin-body">
    <div class="container admin-shell">
        <section class="page-header">
            <div>
                <span class="eyebrow">Analitica</span>
                <h1>Reportes del sistema</h1>
                <p class="muted-text">Consulta el rendimiento de los cursos y la opinion de la comunidad.</p>
            </div>
            <a class="btn btn-light" href="dashboard.php">Volver al panel</a>
        </section>

        <section class="card admin-card">
            <div class="section-heading">
                <div>
                    <span class="eyebrow">Ranking</span>
                    <h2>Cursos mejor valorados</h2>
                </div>
            </div>
            <div class="ranking-cards-grid">
                <?php foreach (array_slice($rankingCursos, 0, 3) as $indice => $curso) { ?>
                <article class="ranking-card <?= $indice === 0 ? 'ranking-card-top' : '' ?>">
                    <span class="ranking-position">#<?= $indice + 1 ?></span>
                    <h3><?= htmlspecialchars($curso['nombre']) ?></h3>
                    <div class="rating-row">
                        <strong class="rating-value"><?= round($curso['promedio'], 2) ?></strong>
                        <span class="rating-stars"><?= str_repeat('&#9733;', (int) round($curso['promedio'])) ?><?= str_repeat('&#9734;', max(0, 5 - (int) round($curso['promedio']))) ?></span>
                    </div>
                    <p><?= (int) $curso['total'] ?> calificaciones registradas</p>
                </article>
                <?php } ?>
            </div>
            <div class="table-wrap">
                <table>
                    <tr>
                        <th>Posicion</th>
                        <th>Curso</th>
                        <th>Promedio</th>
                        <th>Total calificaciones</th>
                    </tr>
                    <?php foreach ($rankingCursos as $indice => $r) { ?>
                    <tr>
                        <td><span class="table-badge">#<?= $indice + 1 ?></span></td>
                        <td><?= htmlspecialchars($r['nombre']) ?></td>
                        <td><?= round($r['promedio'], 2) ?></td>
                        <td><?= $r['total'] ?></td>
                    </tr>
                    <?php } ?>
                </table>
            </div>
        </section>

        <section class="card admin-card">
            <div class="section-heading">
                <div>
                    <span class="eyebrow">Feedback</span>
                    <h2>Comentarios recientes</h2>
                </div>
            </div>
            <div class="table-wrap">
                <table>
                    <tr>
                        <th>Curso</th>
                        <th>Usuario</th>
                        <th>Estrellas</th>
                        <th>Comentario</th>
                        <th>Fecha</th>
                    </tr>
                    <?php while ($c = $comentarios->fetch_assoc()) { ?>
                    <tr>
                        <td><?= htmlspecialchars($c['curso']) ?></td>
                        <td><?= htmlspecialchars($c['usuario']) ?></td>
                        <td><span class="table-badge table-badge-gold"><?= $c['estrellas'] ?> estrellas</span></td>
                        <td><?= htmlspecialchars($c['comentario']) ?></td>
                        <td><?= htmlspecialchars($c['fecha']) ?></td>
                    </tr>
                    <?php } ?>
                </table>
            </div>
        </section>
    </div>
</body>
</html>
