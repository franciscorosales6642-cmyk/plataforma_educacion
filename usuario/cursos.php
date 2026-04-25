<?php
session_start();
if (!isset($_SESSION["rol"]) || $_SESSION["rol"] != "USUARIO") {
    header("Location: ../login.php");
    exit;
}
include "../config/conexion.php";

$usuario_id = (int) $_SESSION['id'];
$usuario = $conn->query("SELECT tema FROM usuarios WHERE id=$usuario_id")->fetch_assoc();
$cursos = $conn->query("
    SELECT
        c.*,
        p.nombre profesor,
        COALESCE(v.total_videos, 0) + COALESCE(q.total_preguntas, 0) total_contenidos,
        COALESCE(pr.vistos, 0) + COALESCE(rp.correctas, 0) completados
    FROM cursos c
    INNER JOIN profesores p ON c.profesor_id = p.id
    LEFT JOIN (
        SELECT curso_id, COUNT(*) total_videos
        FROM videos
        GROUP BY curso_id
    ) v ON v.curso_id = c.id
    LEFT JOIN (
        SELECT curso_id, COUNT(*) total_preguntas
        FROM preguntas
        GROUP BY curso_id
    ) q ON q.curso_id = c.id
    LEFT JOIN (
        SELECT curso_id, COUNT(*) vistos
        FROM progreso
        WHERE usuario_id = $usuario_id AND visto = 1
        GROUP BY curso_id
    ) pr ON pr.curso_id = c.id
    LEFT JOIN (
        SELECT pr.curso_id, COUNT(*) correctas
        FROM respuestas_preguntas rp
        INNER JOIN preguntas pr ON pr.id = rp.pregunta_id
        WHERE rp.usuario_id = $usuario_id AND rp.correcta = 1
        GROUP BY pr.curso_id
    ) rp ON rp.curso_id = c.id
    ORDER BY c.id DESC
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cursos</title>
    <link rel="stylesheet" href="../estilos.css">
</head>
<body class="<?= ($usuario['tema'] ?? 'claro') === 'oscuro' ? 'oscuro' : '' ?>">
    <div class="container user-dashboard-shell">
        <section class="page-header">
            <div>
                <span class="eyebrow">Catalogo</span>
                <h1>Cursos disponibles</h1>
                <p class="muted-text">Explora el catalogo, revisa tu avance y entra directamente a la siguiente leccion de cada curso.</p>
            </div>
            <a class="btn btn-light" href="inicio.php">Volver al panel</a>
        </section>

        <section class="catalog-grid">
            <?php while ($c = $cursos->fetch_assoc()) {
                $totalContenidos = (int) $c['total_contenidos'];
                $vistos = (int) $c['completados'];
                $porcentaje = $totalContenidos > 0 ? (int) round(($vistos / $totalContenidos) * 100) : 0;
            ?>
            <article class="catalog-card">
                <div class="catalog-card-head">
                    <span class="action-card-tag">Curso</span>
                    <span class="table-badge"><?= $porcentaje ?>%</span>
                </div>
                <h3><?= htmlspecialchars($c['nombre']) ?></h3>
                <p class="muted-text"><?= htmlspecialchars($c['descripcion']) ?></p>
                <div class="catalog-meta">
                    <span>Profesor: <?= htmlspecialchars($c['profesor']) ?></span>
                    <span><?= $vistos ?> de <?= $totalContenidos ?> contenidos completados</span>
                </div>
                <div class="progress-bar">
                    <span style="width: <?= $porcentaje ?>%"></span>
                </div>
                <a class="btn" href="ver_curso.php?id=<?= $c['id'] ?>">Entrar al curso</a>
            </article>
            <?php } ?>
        </section>
    </div>
</body>
</html>
