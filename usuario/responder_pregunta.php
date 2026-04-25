<?php
session_start();
if (!isset($_SESSION["rol"]) || $_SESSION["rol"] != "USUARIO") {
    header("Location: ../login.php");
    exit;
}
include "../config/conexion.php";

$usuario_id = (int) $_SESSION['id'];
$curso_id = (int) $_POST['curso_id'];
$pregunta_id = (int) $_POST['pregunta_id'];
$respuesta = strtoupper(trim($_POST['respuesta'] ?? ''));

$pregunta = $conn->query("SELECT id, curso_id, respuesta_correcta, orden FROM preguntas WHERE id=$pregunta_id AND curso_id=$curso_id")->fetch_assoc();
if (!$pregunta) {
    $_SESSION['flash_tipo'] = 'error';
    $_SESSION['flash_mensaje'] = 'La pregunta solicitada no existe en este curso.';
    header("Location: ver_curso.php?id=$curso_id");
    exit;
}

$totalVideos = (int) $conn->query("SELECT COUNT(*) total FROM videos WHERE curso_id=$curso_id")->fetch_assoc()['total'];
$ordenPregunta = (int) $pregunta['orden'];
$previosTotales = $totalVideos + max(0, $ordenPregunta - 1);
$previosCompletados = (int) $conn->query("
    SELECT
        (SELECT COUNT(*) FROM progreso WHERE usuario_id = $usuario_id AND curso_id = $curso_id AND visto = 1) +
        (
            SELECT COUNT(*)
            FROM respuestas_preguntas rp
            INNER JOIN preguntas p ON p.id = rp.pregunta_id
            WHERE rp.usuario_id = $usuario_id
              AND p.curso_id = $curso_id
              AND rp.correcta = 1
              AND p.orden < $ordenPregunta
        ) AS total
")->fetch_assoc()['total'];

if ($previosTotales !== $previosCompletados) {
    $_SESSION['flash_tipo'] = 'error';
    $_SESSION['flash_mensaje'] = 'Debes completar el contenido anterior antes de responder esta pregunta.';
    header("Location: ver_curso.php?id=$curso_id");
    exit;
}

$correcta = $respuesta === $pregunta['respuesta_correcta'] ? 1 : 0;
$respuestaEscapada = $conn->real_escape_string($respuesta);
$conn->query("
    INSERT INTO respuestas_preguntas(usuario_id, pregunta_id, respuesta, correcta)
    VALUES($usuario_id, $pregunta_id, '$respuestaEscapada', $correcta)
    ON DUPLICATE KEY UPDATE respuesta = VALUES(respuesta), correcta = VALUES(correcta), fecha = CURRENT_TIMESTAMP
");

if ($correcta) {
    $_SESSION['flash_tipo'] = 'success';
    $_SESSION['flash_mensaje'] = 'Respuesta correcta. Puedes continuar con el siguiente contenido.';
} else {
    $_SESSION['flash_tipo'] = 'error';
    $_SESSION['flash_mensaje'] = 'Respuesta incorrecta. Intenta nuevamente.';
}

header("Location: ver_curso.php?id=$curso_id&pregunta=$pregunta_id");
exit;
?>
