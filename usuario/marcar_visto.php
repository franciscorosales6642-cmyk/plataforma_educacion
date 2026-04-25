<?php
session_start();
if (!isset($_SESSION["rol"]) || $_SESSION["rol"] != "USUARIO") {
    header("Location: ../login.php");
    exit;
}
include "../config/conexion.php";

$usuario_id = (int) $_SESSION['id'];
$curso_id = (int) $_POST['curso_id'];
$video_id = (int) $_POST['video_id'];
$video = $conn->query("SELECT id, orden FROM videos WHERE id=$video_id AND curso_id=$curso_id")->fetch_assoc();

if (!$video) {
    $_SESSION['flash_tipo'] = 'error';
    $_SESSION['flash_mensaje'] = 'La leccion solicitada no existe en este curso.';
    header("Location: ver_curso.php?id=$curso_id");
    exit;
}

$ordenVideo = (int) $video['orden'];
$previosTotales = (int) $conn->query("SELECT COUNT(*) total FROM videos WHERE curso_id=$curso_id AND orden < $ordenVideo")->fetch_assoc()['total'];
$previosVistos = (int) $conn->query("
    SELECT COUNT(*) total
    FROM videos v
    INNER JOIN progreso p ON p.video_id = v.id AND p.usuario_id = $usuario_id AND p.visto = 1
    WHERE v.curso_id = $curso_id AND v.orden < $ordenVideo
")->fetch_assoc()['total'];
$existe = $conn->query("SELECT id FROM progreso WHERE usuario_id=$usuario_id AND video_id=$video_id AND visto=1")->num_rows;

if ($previosTotales !== $previosVistos) {
    $_SESSION['flash_tipo'] = 'error';
    $_SESSION['flash_mensaje'] = 'Debes marcar la leccion anterior como vista antes de avanzar.';
} elseif ($existe == 0) {
    $insertado = $conn->query("INSERT INTO progreso(usuario_id, curso_id, video_id, visto) VALUES($usuario_id,$curso_id,$video_id,1)");
    if ($insertado) {
        $conn->query("UPDATE videos SET visualizaciones=visualizaciones+1 WHERE id=$video_id");
        $_SESSION['flash_tipo'] = 'success';
        $_SESSION['flash_mensaje'] = 'Leccion marcada como vista correctamente.';
    } else {
        $_SESSION['flash_tipo'] = 'error';
        $_SESSION['flash_mensaje'] = 'No se pudo registrar el progreso.';
    }
} else {
    $_SESSION['flash_tipo'] = 'success';
    $_SESSION['flash_mensaje'] = 'Esta leccion ya estaba marcada como vista.';
}

header("Location: ver_curso.php?id=$curso_id");
exit;
?>
