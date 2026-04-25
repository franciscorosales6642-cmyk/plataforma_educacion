<?php
session_start();
if (!isset($_SESSION["rol"]) || $_SESSION["rol"] != "USUARIO") {
    header("Location: ../login.php");
    exit;
}
include "../config/conexion.php";

$usuario_id = (int) $_SESSION['id'];
$curso_id = (int) $_GET['curso_id'];
$usuario = $conn->query("SELECT tema, nombre FROM usuarios WHERE id=$usuario_id")->fetch_assoc();
$curso = $conn->query("SELECT nombre FROM cursos WHERE id=$curso_id")->fetch_assoc();
$totalVideos = (int) $conn->query("SELECT COUNT(*) total FROM videos WHERE curso_id=$curso_id")->fetch_assoc()['total'];
$totalPreguntas = (int) $conn->query("SELECT COUNT(*) total FROM preguntas WHERE curso_id=$curso_id")->fetch_assoc()['total'];
$totalContenidos = $totalVideos + $totalPreguntas;
$totalVistos = (int) $conn->query("SELECT COUNT(*) total FROM progreso WHERE usuario_id=$usuario_id AND curso_id=$curso_id AND visto=1")->fetch_assoc()['total'];
$totalRespuestasOk = (int) $conn->query("
    SELECT COUNT(*) total
    FROM respuestas_preguntas rp
    INNER JOIN preguntas p ON p.id = rp.pregunta_id
    WHERE rp.usuario_id = $usuario_id AND p.curso_id = $curso_id AND rp.correcta = 1
")->fetch_assoc()['total'];
$totalCompletados = $totalVistos + $totalRespuestasOk;

if ($totalCompletados < $totalContenidos) {
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Calificar</title>
        <link rel="stylesheet" href="../estilos.css">
    </head>
    <body class="<?= ($usuario['tema'] ?? 'claro') === 'oscuro' ? 'oscuro' : '' ?>">
        <div class="container user-dashboard-shell">
            <section class="page-header">
                <div>
                    <span class="eyebrow">Curso incompleto</span>
                    <h1>Aun no puedes calificar este curso</h1>
                    <p class="muted-text">Debes terminar todos los contenidos antes de enviar una valoracion.</p>
                </div>
                <a class="btn btn-light" href="ver_curso.php?id=<?= $curso_id ?>">Volver al curso</a>
            </section>
            <div class="alert alert-error">Completa los <?= $totalContenidos ?> contenidos del curso para habilitar la calificacion final.</div>
        </div>
    </body>
    </html>
    <?php
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $estrellas = (int) $_POST['estrellas'];
    $comentario = $conn->real_escape_string($_POST['comentario']);
    $conn->query("INSERT INTO calificaciones(usuario_id, curso_id, estrellas, comentario) VALUES($usuario_id,$curso_id,$estrellas,'$comentario')");

    $archivo = "certificado_usuario_{$usuario_id}_curso_{$curso_id}.pdf";
    $reconocimientoExistente = $conn->query("SELECT id FROM reconocimientos WHERE usuario_id=$usuario_id AND curso_id=$curso_id LIMIT 1")->fetch_assoc();
    if ($reconocimientoExistente) {
        $conn->query("UPDATE reconocimientos SET archivo='$archivo' WHERE id=" . (int) $reconocimientoExistente['id']);
    } else {
        $conn->query("INSERT INTO reconocimientos(usuario_id, curso_id, archivo) VALUES($usuario_id,$curso_id,'$archivo')");
    }
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Curso completado</title>
        <link rel="stylesheet" href="../estilos.css">
    </head>
    <body class="<?= ($usuario['tema'] ?? 'claro') === 'oscuro' ? 'oscuro' : '' ?>">
        <div class="container user-dashboard-shell">
            <section class="page-header">
                <div>
                    <span class="eyebrow">Curso finalizado</span>
                    <h1>Felicidades, terminaste el curso</h1>
                    <p class="muted-text"><?= htmlspecialchars($curso['nombre'] ?? 'Curso') ?> fue completado correctamente y ya registramos tu valoracion.</p>
                </div>
                <a class="btn btn-light" href="inicio.php">Volver al inicio</a>
            </section>

            <section class="certificate-stage">
                <span class="confetti confetti-1"></span>
                <span class="confetti confetti-2"></span>
                <span class="confetti confetti-3"></span>
                <span class="confetti confetti-4"></span>
                <section class="card admin-card certificate-success-card">
                    <span class="eyebrow">Reconocimiento emitido</span>
                    <h2>Tu certificado profesional ya esta listo</h2>
                    <p class="muted-text">Generamos un certificado real en PHP con tus datos, el nombre del curso y la fecha de emision.</p>
                    <div class="certificate-actions">
                        <a class="btn" href="certificado.php?curso_id=<?= $curso_id ?>">Ver certificado</a>
                        <a class="btn btn-light-solid" href="certificado_pdf.php?curso_id=<?= $curso_id ?>">Descargar PDF</a>
                    </div>
                </section>
            </section>
        </div>
    </body>
    </html>
    <?php
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calificar</title>
    <link rel="stylesheet" href="../estilos.css">
</head>
<body class="<?= ($usuario['tema'] ?? 'claro') === 'oscuro' ? 'oscuro' : '' ?>">
    <div class="container user-dashboard-shell">
        <section class="page-header">
            <div>
                <span class="eyebrow">Valoracion final</span>
                <h1>Calificar curso</h1>
                <p class="muted-text">Comparte tu experiencia sobre <?= htmlspecialchars($curso['nombre'] ?? 'este curso') ?> para ayudar a otros estudiantes.</p>
            </div>
            <a class="btn btn-light" href="ver_curso.php?id=<?= $curso_id ?>">Volver al curso</a>
        </section>

        <section class="card admin-card review-card">
            <form method="POST" class="admin-form">
                <label class="field-group">
                    <span>Puntuacion</span>
                    <select name="estrellas" required>
                        <option value="5">&#9733;&#9733;&#9733;&#9733;&#9733; Excelente</option>
                        <option value="4">&#9733;&#9733;&#9733;&#9733; Muy bueno</option>
                        <option value="3">&#9733;&#9733;&#9733; Bueno</option>
                        <option value="2">&#9733;&#9733; Regular</option>
                        <option value="1">&#9733; Malo</option>
                    </select>
                </label>
                <label class="field-group">
                    <span>Comentario</span>
                    <textarea name="comentario" placeholder="Escribe una opinion breve sobre el curso" required></textarea>
                </label>
                <button type="submit">Enviar calificacion</button>
            </form>
        </section>
    </div>
</body>
</html>
