<?php
session_start();
if (!isset($_SESSION["rol"]) || $_SESSION["rol"] != "USUARIO") {
    header("Location: ../login.php");
    exit;
}
include "../config/conexion.php";

$usuario_id = (int) $_SESSION['id'];
$usuario = $conn->query("SELECT * FROM usuarios WHERE id=$usuario_id")->fetch_assoc();
$totalCursos = (int) $conn->query("SELECT COUNT(*) total FROM cursos")->fetch_assoc()['total'];
$videosVistos = (int) $conn->query("SELECT COUNT(*) total FROM progreso WHERE usuario_id=$usuario_id AND visto=1")->fetch_assoc()['total'];
$preguntasRespondidas = (int) $conn->query("SELECT COUNT(*) total FROM respuestas_preguntas WHERE usuario_id=$usuario_id AND correcta=1")->fetch_assoc()['total'];
$contenidosCompletados = $videosVistos + $preguntasRespondidas;
$cursosCompletados = (int) $conn->query("
    SELECT COUNT(*) total
    FROM (
        SELECT
            c.id,
            COALESCE(v.total_videos, 0) + COALESCE(q.total_preguntas, 0) AS total_contenidos,
            COALESCE(p.vistos, 0) + COALESCE(r.correctas, 0) AS completados
        FROM cursos c
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
        ) p ON p.curso_id = c.id
        LEFT JOIN (
            SELECT pr.curso_id, COUNT(*) correctas
            FROM respuestas_preguntas rp
            INNER JOIN preguntas pr ON pr.id = rp.pregunta_id
            WHERE rp.usuario_id = $usuario_id AND rp.correcta = 1
            GROUP BY pr.curso_id
        ) r ON r.curso_id = c.id
    ) completados
    WHERE total_contenidos > 0 AND total_contenidos = completados
")->fetch_assoc()['total'];
$reconocimientos = (int) $conn->query("SELECT COUNT(*) total FROM reconocimientos WHERE usuario_id=$usuario_id")->fetch_assoc()['total'];

$avanceCursos = $conn->query("
    SELECT
        c.id,
        c.nombre,
        c.descripcion,
        p.nombre AS profesor,
        COALESCE(v.total_videos, 0) + COALESCE(q.total_preguntas, 0) AS total_contenidos,
        COALESCE(pr.vistos, 0) + COALESCE(rp.correctas, 0) AS completados
    FROM cursos c
    INNER JOIN profesores p ON p.id = c.profesor_id
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
    ORDER BY completados DESC, c.id DESC
    LIMIT 3
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio del Usuario</title>
    <link rel="stylesheet" href="../estilos.css">
</head>
<body class="<?= $usuario['tema'] == 'oscuro' ? 'oscuro' : '' ?>">
    <div class="container user-dashboard-shell">
        <section class="user-hero-panel">
            <div>
                <span class="eyebrow">Plataforma de aprendizaje</span>
                <h1>Tu panel de progreso</h1>
                <p class="hero-text">Consulta tu avance, retoma cursos en proceso y gestiona tu cuenta desde un dashboard mas claro y moderno.</p>
            </div>
            <div class="hero-user-card">
                <?php
                $rutaImagenPerfil = '../' . $usuario['imagen'];
                $mostrarImagenPerfil = $usuario['imagen'] !== 'default.png' && is_file(dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $usuario['imagen']));
                ?>
                <div class="hero-avatar-shell">
                    <?php if ($mostrarImagenPerfil) { ?>
                    <img class="hero-avatar-image" src="<?= htmlspecialchars($rutaImagenPerfil) ?>" alt="Foto de perfil">
                    <?php } else { ?>
                    <div class="hero-avatar-placeholder"><?= strtoupper(substr($_SESSION['nombre'], 0, 1)) ?></div>
                    <?php } ?>
                </div>
                <span class="hero-user-label">Estudiante</span>
                <strong><?= htmlspecialchars($_SESSION['nombre']) ?></strong>
                <span class="hero-user-role"><?= htmlspecialchars($usuario['correo']) ?></span>
            </div>
        </section>

        <nav class="admin-nav">
            <a class="btn" href="cursos.php">Explorar cursos</a>
            <a class="btn btn-light-solid" href="perfil.php">Configurar perfil</a>
            <a class="btn btn-danger" href="../logout.php">Cerrar sesion</a>
        </nav>

        <section class="stats-grid">
            <article class="stat-card">
                <span class="stat-label">Cursos disponibles</span>
                <strong class="stat-value"><?= $totalCursos ?></strong>
                <p>Catalogo total listo para comenzar o continuar.</p>
            </article>
            <article class="stat-card">
                <span class="stat-label">Contenidos completados</span>
                <strong class="stat-value"><?= $contenidosCompletados ?></strong>
                <p>Videos vistos y preguntas respondidas correctamente dentro de tu ruta de estudio.</p>
            </article>
            <article class="stat-card">
                <span class="stat-label">Cursos completados</span>
                <strong class="stat-value"><?= $cursosCompletados ?></strong>
                <p>Programas finalizados con todos sus contenidos revisados.</p>
            </article>
            <article class="stat-card">
                <span class="stat-label">Reconocimientos</span>
                <strong class="stat-value"><?= $reconocimientos ?></strong>
                <p>Constancias o logros emitidos por tu actividad en la plataforma.</p>
            </article>
        </section>

        <div class="dashboard-grid">
            <section class="card admin-card">
                <div class="section-heading">
                    <div>
                        <span class="eyebrow">Seguimiento</span>
                        <h2>Tus cursos destacados</h2>
                    </div>
                </div>
                <div class="progress-list">
                    <?php while ($curso = $avanceCursos->fetch_assoc()) {
                        $totalContenidosCurso = (int) $curso['total_contenidos'];
                        $vistosCurso = (int) $curso['completados'];
                        $porcentaje = $totalContenidosCurso > 0 ? min(100, round(($vistosCurso / $totalContenidosCurso) * 100)) : 0;
                    ?>
                    <article class="progress-card">
                        <div class="progress-card-head">
                            <div>
                                <h3><?= htmlspecialchars($curso['nombre']) ?></h3>
                                <p class="muted-text">Profesor: <?= htmlspecialchars($curso['profesor']) ?></p>
                            </div>
                            <span class="progress-badge"><?= $porcentaje ?>%</span>
                        </div>
                        <p class="muted-text"><?= htmlspecialchars($curso['descripcion']) ?></p>
                        <div class="progress-bar">
                            <span style="width: <?= $porcentaje ?>%"></span>
                        </div>
                        <div class="progress-meta">
                            <span><?= $vistosCurso ?> de <?= $totalContenidosCurso ?> contenidos completados</span>
                            <a class="btn" href="ver_curso.php?id=<?= $curso['id'] ?>">Continuar</a>
                        </div>
                    </article>
                    <?php } ?>
                </div>
            </section>

            <aside class="card admin-card">
                <div class="section-heading">
                    <div>
                        <span class="eyebrow">Acceso rapido</span>
                        <h2>Tu espacio</h2>
                    </div>
                </div>
                <div class="quick-actions">
                    <a class="quick-action-card" href="cursos.php">
                        <strong>Ver catalogo</strong>
                        <p>Explora cursos disponibles y entra a tu siguiente leccion.</p>
                    </a>
                    <a class="quick-action-card" href="perfil.php">
                        <strong>Actualizar perfil</strong>
                        <p>Modifica tu contrasena, foto y tema visual de la cuenta.</p>
                    </a>
                    <div class="info-panel">
                        <span class="eyebrow">Estado actual</span>
                        <p>Tu tema activo es <strong><?= $usuario['tema'] == 'oscuro' ? 'oscuro' : 'claro' ?></strong>. Puedes cambiarlo desde configuracion de perfil.</p>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</body>
</html>
