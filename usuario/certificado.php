<?php
session_start();
if (!isset($_SESSION["rol"]) || $_SESSION["rol"] != "USUARIO") {
    header("Location: ../login.php");
    exit;
}
include "../config/conexion.php";

$usuario_id = (int) $_SESSION['id'];
$curso_id = (int) ($_GET['curso_id'] ?? 0);
$usuario = $conn->query("SELECT nombre, correo, tema FROM usuarios WHERE id=$usuario_id")->fetch_assoc();
$curso = $conn->query("SELECT c.nombre, p.nombre profesor FROM cursos c INNER JOIN profesores p ON p.id = c.profesor_id WHERE c.id=$curso_id")->fetch_assoc();
$reconocimiento = $conn->query("SELECT * FROM reconocimientos WHERE usuario_id=$usuario_id AND curso_id=$curso_id LIMIT 1")->fetch_assoc();

if (!$curso || !$reconocimiento) {
    header("Location: inicio.php");
    exit;
}

$fecha = date('d/m/Y', strtotime($reconocimiento['fecha']));
$folio = sprintf('CERT-%04d-%04d-%s', $usuario_id, $curso_id, date('Ymd', strtotime($reconocimiento['fecha'])));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Certificado</title>
    <link rel="stylesheet" href="../estilos.css">
</head>
<body class="<?= ($usuario['tema'] ?? 'claro') === 'oscuro' ? 'oscuro' : '' ?>">
    <div class="container user-dashboard-shell">
        <section class="page-header">
            <div>
                <span class="eyebrow">Certificado oficial</span>
                <h1>Reconocimiento de finalizacion</h1>
                <p class="muted-text">Este certificado acredita la finalizacion satisfactoria del curso.</p>
            </div>
            <a class="btn btn-light" href="inicio.php">Volver al inicio</a>
        </section>

        <section class="certificate-stage">
            <span class="confetti confetti-1"></span>
            <span class="confetti confetti-2"></span>
            <span class="confetti confetti-3"></span>
            <span class="confetti confetti-4"></span>

            <article class="certificate-card">
                <div class="certificate-border">
                    <span class="certificate-ribbon">Plataforma Educativa</span>
                    <span class="eyebrow">Certificado de finalizacion</span>
                    <h2>Se otorga el presente certificado a</h2>
                    <h1 class="certificate-name"><?= htmlspecialchars($usuario['nombre']) ?></h1>
                    <p class="certificate-text">Por haber completado satisfactoriamente el curso</p>
                    <h3 class="certificate-course"><?= htmlspecialchars($curso['nombre']) ?></h3>
                    <p class="certificate-text">Impartido por <?= htmlspecialchars($curso['profesor']) ?> y registrado en nuestra plataforma academica.</p>

                    <div class="certificate-meta-grid">
                        <div class="certificate-meta-box">
                            <span class="eyebrow">Fecha de emision</span>
                            <strong><?= htmlspecialchars($fecha) ?></strong>
                        </div>
                        <div class="certificate-meta-box">
                            <span class="eyebrow">Folio</span>
                            <strong><?= htmlspecialchars($folio) ?></strong>
                        </div>
                    </div>

                    <div class="certificate-signature-row">
                        <div class="certificate-signature">
                            <span class="certificate-sign-line"></span>
                            <strong>Direccion Academica</strong>
                        </div>
                        <div class="certificate-signature">
                            <span class="certificate-sign-line"></span>
                            <strong>Plataforma Educativa</strong>
                        </div>
                    </div>
                </div>
            </article>
        </section>

        <div class="certificate-actions">
            <a class="btn" href="certificado_pdf.php?curso_id=<?= $curso_id ?>">Descargar PDF</a>
            <a class="btn btn-light-solid" href="calificar.php?curso_id=<?= $curso_id ?>">Volver al curso</a>
        </div>
    </div>
</body>
</html>
