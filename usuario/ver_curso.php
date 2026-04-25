<?php
session_start();
if (!isset($_SESSION["rol"]) || $_SESSION["rol"] != "USUARIO") {
    header("Location: ../login.php");
    exit;
}
include "../config/conexion.php";

function obtenerYoutubeEmbedUrl($url)
{
    $url = trim($url);
    if ($url === '') {
        return '';
    }

    $partes = parse_url($url);
    if (!$partes || empty($partes['host'])) {
        return '';
    }

    $host = strtolower($partes['host']);
    $videoId = '';

    if (strpos($host, 'youtu.be') !== false) {
        $videoId = trim($partes['path'] ?? '', '/');
    } elseif (strpos($host, 'youtube.com') !== false || strpos($host, 'youtube-nocookie.com') !== false) {
        if (!empty($partes['query'])) {
            parse_str($partes['query'], $query);
            if (!empty($query['v'])) {
                $videoId = $query['v'];
            }
        }

        if ($videoId === '' && !empty($partes['path'])) {
            $segmentos = explode('/', trim($partes['path'], '/'));
            if (count($segmentos) >= 2 && in_array($segmentos[0], ['embed', 'shorts', 'live'], true)) {
                $videoId = $segmentos[1];
            }
        }
    }

    if ($videoId === '') {
        return '';
    }

    return 'https://www.youtube.com/embed/' . rawurlencode($videoId) . '?rel=0&modestbranding=1';
}

$curso_id = (int) $_GET['id'];
$usuario_id = (int) $_SESSION['id'];
$videoSolicitado = isset($_GET['video']) ? (int) $_GET['video'] : 0;
$preguntaSolicitada = isset($_GET['pregunta']) ? (int) $_GET['pregunta'] : 0;

$usuario = $conn->query("SELECT tema FROM usuarios WHERE id=$usuario_id")->fetch_assoc();
$curso = $conn->query("SELECT c.*, p.nombre profesor FROM cursos c INNER JOIN profesores p ON p.id = c.profesor_id WHERE c.id=$curso_id")->fetch_assoc();

$resultadoVideos = $conn->query("
    SELECT
        v.*,
        EXISTS(
            SELECT 1
            FROM progreso pr
            WHERE pr.usuario_id = $usuario_id AND pr.video_id = v.id AND pr.visto = 1
        ) AS visto
    FROM videos v
    WHERE v.curso_id = $curso_id
    ORDER BY v.orden ASC
");

$resultadoPreguntas = $conn->query("
    SELECT
        p.*,
        COALESCE(rp.respuesta, '') AS respuesta_usuario,
        COALESCE(rp.correcta, 0) AS respondida_correcta
    FROM preguntas p
    LEFT JOIN respuestas_preguntas rp ON rp.pregunta_id = p.id AND rp.usuario_id = $usuario_id
    WHERE p.curso_id = $curso_id
    ORDER BY p.orden ASC
");

$videos = [];
while ($video = $resultadoVideos->fetch_assoc()) {
    $videos[] = [
        'tipo' => 'video',
        'id' => (int) $video['id'],
        'titulo' => $video['titulo'],
        'url_video' => $video['url_video'],
        'orden' => (int) $video['orden'],
        'completado' => (bool) $video['visto']
    ];
}

$cantidadVideos = count($videos);
$preguntas = [];
while ($pregunta = $resultadoPreguntas->fetch_assoc()) {
    $preguntas[] = [
        'tipo' => 'pregunta',
        'id' => (int) $pregunta['id'],
        'titulo' => 'Pregunta ' . (int) $pregunta['orden'],
        'pregunta' => $pregunta['pregunta'],
        'opcion_a' => $pregunta['opcion_a'],
        'opcion_b' => $pregunta['opcion_b'],
        'opcion_c' => $pregunta['opcion_c'],
        'opcion_d' => $pregunta['opcion_d'],
        'respuesta_correcta' => $pregunta['respuesta_correcta'],
        'respuesta_usuario' => $pregunta['respuesta_usuario'],
        'orden' => $cantidadVideos + (int) $pregunta['orden'],
        'completado' => (bool) $pregunta['respondida_correcta']
    ];
}

$contenidos = array_merge($videos, $preguntas);
usort($contenidos, function ($a, $b) {
    return $a['orden'] <=> $b['orden'];
});

$siguienteDisponible = null;
$bloquearRestantes = false;
foreach ($contenidos as &$contenido) {
    $contenido['bloqueado'] = $bloquearRestantes;
    if (!$contenido['completado'] && $siguienteDisponible === null) {
        $siguienteDisponible = ['tipo' => $contenido['tipo'], 'id' => $contenido['id']];
        $bloquearRestantes = true;
    }
}
unset($contenido);

if ($siguienteDisponible === null && !empty($contenidos)) {
    $siguienteDisponible = ['tipo' => $contenidos[0]['tipo'], 'id' => $contenidos[0]['id']];
}

$contenidoActivo = null;
foreach ($contenidos as $contenido) {
    $esSolicitado = ($contenido['tipo'] === 'video' && $videoSolicitado === $contenido['id'])
        || ($contenido['tipo'] === 'pregunta' && $preguntaSolicitada === $contenido['id']);
    $puedeAbrirse = !$contenido['bloqueado'] || $contenido['completado'];
    if ($esSolicitado && $puedeAbrirse) {
        $contenidoActivo = $contenido;
        break;
    }
}

if ($contenidoActivo === null && $siguienteDisponible !== null) {
    foreach ($contenidos as $contenido) {
        if ($contenido['tipo'] === $siguienteDisponible['tipo'] && $contenido['id'] === $siguienteDisponible['id']) {
            $contenidoActivo = $contenido;
            break;
        }
    }
}

if ($contenidoActivo === null && !empty($contenidos)) {
    $contenidoActivo = $contenidos[0];
}

$totalContenidos = count($contenidos);
$contenidosCompletados = 0;
foreach ($contenidos as $contenido) {
    if ($contenido['completado']) {
        $contenidosCompletados++;
    }
}

$porcentaje = $totalContenidos > 0 ? (int) round(($contenidosCompletados / $totalContenidos) * 100) : 0;
$flashTipo = $_SESSION['flash_tipo'] ?? '';
$flashMensaje = $_SESSION['flash_mensaje'] ?? '';
unset($_SESSION['flash_tipo'], $_SESSION['flash_mensaje']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Curso</title>
    <link rel="stylesheet" href="../estilos.css">
</head>
<body class="<?= ($usuario['tema'] ?? 'claro') === 'oscuro' ? 'oscuro' : '' ?>">
    <div class="container user-dashboard-shell">
        <section class="page-header">
            <div>
                <span class="eyebrow">Curso en progreso</span>
                <h1><?= htmlspecialchars($curso['nombre']) ?></h1>
                <p class="muted-text">Profesor: <?= htmlspecialchars($curso['profesor']) ?>. Completa videos y preguntas en orden para desbloquear el siguiente contenido.</p>
            </div>
            <a class="btn btn-light" href="cursos.php">Volver a cursos</a>
        </section>

        <?php if ($flashMensaje) { ?>
        <div class="alert <?= $flashTipo === 'success' ? 'alert-success' : 'alert-error' ?>"><?= htmlspecialchars($flashMensaje) ?></div>
        <?php } ?>

        <section class="course-layout">
            <div class="course-main">
                <section class="card admin-card course-player-card">
                    <div class="course-player-head">
                        <div>
                            <span class="eyebrow"><?= $contenidoActivo && $contenidoActivo['tipo'] === 'pregunta' ? 'Pregunta actual' : 'Leccion actual' ?></span>
                            <h2><?= htmlspecialchars($contenidoActivo['titulo'] ?? 'Sin contenido disponible') ?></h2>
                        </div>
                        <span class="progress-badge"><?= $porcentaje ?>% completado</span>
                    </div>

                    <?php if ($contenidoActivo) { ?>
                    <?php if ($contenidoActivo['tipo'] === 'video') { ?>
                    <?php
                    $urlVideo = $contenidoActivo['url_video'];
                    $youtubeEmbedUrl = obtenerYoutubeEmbedUrl($urlVideo);
                    $esUrlExterna = preg_match('/^https?:\/\//i', $urlVideo) === 1;
                    $srcVideo = $esUrlExterna ? $urlVideo : '../' . ltrim($urlVideo, '/');
                    ?>
                    <div class="video-frame">
                        <?php if ($youtubeEmbedUrl !== '') { ?>
                        <iframe
                            src="<?= htmlspecialchars($youtubeEmbedUrl) ?>"
                            title="<?= htmlspecialchars($contenidoActivo['titulo']) ?>"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            allowfullscreen
                            referrerpolicy="strict-origin-when-cross-origin"></iframe>
                        <?php } else { ?>
                        <video controls preload="metadata">
                            <source src="<?= htmlspecialchars($srcVideo) ?>" type="video/mp4">
                            Tu navegador no soporta video.
                        </video>
                        <?php } ?>
                    </div>
                    <?php } else { ?>
                    <div class="question-card">
                        <span class="eyebrow">Opcion multiple</span>
                        <h3><?= htmlspecialchars($contenidoActivo['pregunta']) ?></h3>
                        <?php if ($contenidoActivo['completado']) { ?>
                        <div class="alert alert-success">Pregunta respondida correctamente.</div>
                        <div class="question-options">
                            <div class="question-option is-correct">A. <?= htmlspecialchars($contenidoActivo['opcion_a']) ?></div>
                            <div class="question-option is-correct-answer <?= $contenidoActivo['respuesta_correcta'] === 'B' ? 'is-correct' : '' ?>">B. <?= htmlspecialchars($contenidoActivo['opcion_b']) ?></div>
                            <div class="question-option is-correct-answer <?= $contenidoActivo['respuesta_correcta'] === 'C' ? 'is-correct' : '' ?>">C. <?= htmlspecialchars($contenidoActivo['opcion_c']) ?></div>
                            <div class="question-option is-correct-answer <?= $contenidoActivo['respuesta_correcta'] === 'D' ? 'is-correct' : '' ?>">D. <?= htmlspecialchars($contenidoActivo['opcion_d']) ?></div>
                        </div>
                        <?php } else { ?>
                        <form method="POST" action="responder_pregunta.php" class="admin-form">
                            <input type="hidden" name="curso_id" value="<?= $curso_id ?>">
                            <input type="hidden" name="pregunta_id" value="<?= $contenidoActivo['id'] ?>">
                            <label class="question-option">
                                <input type="radio" name="respuesta" value="A" required>
                                <span>A. <?= htmlspecialchars($contenidoActivo['opcion_a']) ?></span>
                            </label>
                            <label class="question-option">
                                <input type="radio" name="respuesta" value="B" required>
                                <span>B. <?= htmlspecialchars($contenidoActivo['opcion_b']) ?></span>
                            </label>
                            <label class="question-option">
                                <input type="radio" name="respuesta" value="C" required>
                                <span>C. <?= htmlspecialchars($contenidoActivo['opcion_c']) ?></span>
                            </label>
                            <label class="question-option">
                                <input type="radio" name="respuesta" value="D" required>
                                <span>D. <?= htmlspecialchars($contenidoActivo['opcion_d']) ?></span>
                            </label>
                            <button type="submit">Responder pregunta</button>
                        </form>
                        <?php } ?>
                    </div>
                    <?php } ?>

                    <div class="course-meta-row">
                        <div class="info-panel">
                            <span class="eyebrow">Progreso del curso</span>
                            <p><?= $contenidosCompletados ?> de <?= $totalContenidos ?> contenidos completados.</p>
                            <div class="progress-bar">
                                <span style="width: <?= $porcentaje ?>%"></span>
                            </div>
                        </div>
                        <div class="info-panel">
                            <span class="eyebrow">Estado del contenido</span>
                            <?php if ($contenidoActivo['completado']) { ?>
                            <p>Este contenido ya fue completado correctamente.</p>
                            <?php } elseif (!$contenidoActivo['bloqueado']) { ?>
                            <p>Este es tu siguiente contenido disponible. Completa este paso para avanzar.</p>
                            <?php } else { ?>
                            <p>Este contenido esta bloqueado hasta terminar el anterior.</p>
                            <?php } ?>
                        </div>
                    </div>

                    <div class="course-actions">
                        <?php if ($contenidoActivo['tipo'] === 'video') { ?>
                            <?php if ($contenidoActivo['completado']) { ?>
                            <span class="table-badge">Leccion completada</span>
                            <?php } elseif (!$contenidoActivo['bloqueado']) { ?>
                            <form method="POST" action="marcar_visto.php" class="inline-form">
                                <input type="hidden" name="curso_id" value="<?= $curso_id ?>">
                                <input type="hidden" name="video_id" value="<?= $contenidoActivo['id'] ?>">
                                <button type="submit" class="btn-green">Marcar como visto</button>
                            </form>
                            <?php } else { ?>
                            <span class="table-badge table-badge-gold">Leccion bloqueada</span>
                            <?php } ?>
                        <?php } else { ?>
                            <?php if ($contenidoActivo['completado']) { ?>
                            <span class="table-badge">Pregunta completada</span>
                            <?php } elseif ($contenidoActivo['bloqueado']) { ?>
                            <span class="table-badge table-badge-gold">Pregunta bloqueada</span>
                            <?php } ?>
                        <?php } ?>
                        <a class="btn btn-light-solid" href="calificar.php?curso_id=<?= $curso_id ?>">Finalizar y calificar</a>
                    </div>
                    <?php } else { ?>
                    <div class="info-panel">
                        <p>Este curso aun no tiene contenidos registrados.</p>
                    </div>
                    <?php } ?>
                </section>
            </div>

            <aside class="course-sidebar">
                <section class="card admin-card course-sidebar-card">
                    <div class="section-heading">
                        <div>
                            <span class="eyebrow">Contenido del curso</span>
                            <h2>Lecciones y preguntas</h2>
                        </div>
                    </div>
                    <div class="lesson-list">
                        <?php foreach ($contenidos as $indice => $contenido) {
                            $activa = $contenidoActivo && $contenidoActivo['tipo'] === $contenido['tipo'] && (int) $contenidoActivo['id'] === (int) $contenido['id'];
                            $puedeAbrirse = !$contenido['bloqueado'] || $contenido['completado'];
                            $clases = 'lesson-item';
                            if ($activa) {
                                $clases .= ' lesson-item-active';
                            }
                            if ($contenido['completado']) {
                                $clases .= ' lesson-item-done';
                            } elseif ($contenido['bloqueado']) {
                                $clases .= ' lesson-item-locked';
                            }
                            $etiqueta = $contenido['tipo'] === 'video' ? 'Video' : 'Pregunta';
                            $estado = $contenido['completado'] ? 'Completado' : ($activa ? 'En curso' : ($contenido['bloqueado'] ? 'Bloqueado' : 'Disponible'));
                            $href = 'ver_curso.php?id=' . $curso_id . ($contenido['tipo'] === 'video' ? '&video=' . $contenido['id'] : '&pregunta=' . $contenido['id']);
                        ?>
                        <?php if ($puedeAbrirse) { ?>
                        <a class="<?= $clases ?>" href="<?= $href ?>">
                            <span class="lesson-order"><?= $indice + 1 ?></span>
                            <div class="lesson-content">
                                <strong><?= htmlspecialchars($contenido['titulo']) ?></strong>
                                <span><?= $etiqueta ?> • <?= $estado ?></span>
                            </div>
                        </a>
                        <?php } else { ?>
                        <div class="<?= $clases ?>">
                            <span class="lesson-order"><?= $indice + 1 ?></span>
                            <div class="lesson-content">
                                <strong><?= htmlspecialchars($contenido['titulo']) ?></strong>
                                <span><?= $etiqueta ?> • Bloqueado</span>
                            </div>
                        </div>
                        <?php } ?>
                        <?php } ?>
                    </div>
                </section>
            </aside>
        </section>
    </div>
</body>
</html>
